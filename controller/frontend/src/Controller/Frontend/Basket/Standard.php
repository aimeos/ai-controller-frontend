<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Metaways Infosystems GmbH, 2012
 * @copyright Aimeos (aimeos.org), 2015-2018
 * @package Controller
 * @subpackage Frontend
 */


namespace Aimeos\Controller\Frontend\Basket;


/**
 * Default implementation of the basket frontend controller.
 *
 * @package Controller
 * @subpackage Frontend
 */
class Standard
	extends Base
	implements Iface, \Aimeos\Controller\Frontend\Common\Iface
{
	private $baskets = [];
	private $domainManager;
	private $type = 'default';


	/**
	 * Initializes the frontend controller.
	 *
	 * @param \Aimeos\MShop\Context\Item\Iface $context Object storing the required instances for manaing databases
	 *  connections, logger, session, etc.
	 */
	public function __construct( \Aimeos\MShop\Context\Item\Iface $context )
	{
		parent::__construct( $context );

		$this->domainManager = \Aimeos\MShop\Factory::createManager( $context, 'order/base' );
	}


	/**
	 * Empties the basket and removing all products, addresses, services, etc.
	 *
	 * @return \Aimeos\Controller\Frontend\Basket\Iface Basket frontend object
	 */
	public function clear()
	{
		$this->baskets[$this->type] = $this->domainManager->createItem();
		$this->domainManager->setSession( $this->baskets[$this->type], $this->type );

		return $this;
	}


	/**
	 * Returns the basket object.
	 *
	 * @return \Aimeos\MShop\Order\Item\Base\Iface Basket holding products, addresses and delivery/payment options
	 */
	public function get()
	{
		if( !isset( $this->baskets[$this->type] ) )
		{
			$this->baskets[$this->type] = $this->domainManager->getSession( $this->type );
			$this->checkLocale( $this->baskets[$this->type]->getLocale(), $this->type );
		}

		return $this->baskets[$this->type];
	}


	/**
	 * Explicitely persists the basket content
	 *
	 * @return \Aimeos\Controller\Frontend\Basket\Iface Basket frontend object
	 */
	public function save()
	{
		if( isset( $this->baskets[$this->type] ) && $this->baskets[$this->type]->isModified() ) {
			$this->domainManager->setSession( $this->baskets[$this->type], $this->type );
		}

		return $this;
	}


	/**
	 * Sets the new basket type
	 *
	 * @param string $type Basket type
	 * @return \Aimeos\Controller\Frontend\Basket\Iface Basket frontend object
	 */
	public function setType( $type )
	{
		$this->type = $type;
		return $this;
	}


	/**
	 * Creates a new order base object from the current basket
	 *
	 * @return \Aimeos\MShop\Order\Item\Base\Iface Order base object including products, addresses and services
	 */
	public function store()
	{
		$total = 0;
		$context = $this->getContext();
		$config = $context->getConfig();

		/** controller/frontend/basket/limit-count
		 * Maximum number of orders within the time frame
		 *
		 * Creating new orders is limited to avoid abuse and mitigate denial of
		 * service attacks. The number of orders created within the time frame
		 * configured by "controller/frontend/basket/limit-seconds" are counted
		 * before a new order of the same user (either logged in or identified
		 * by the IP address) is created. If the number of orders is higher than
		 * the configured value, an error message will be shown to the user
		 * instead of creating a new order.
		 *
		 * @param integer Number of orders allowed within the time frame
		 * @since 2017.05
		 * @category Developer
		 * @see controller/frontend/basket/limit-seconds
		 */
		$count = $config->get( 'controller/frontend/basket/limit-count', 5 );

		/** controller/frontend/basket/limit-seconds
		 * Order limitation time frame in seconds
		 *
		 * Creating new orders is limited to avoid abuse and mitigate denial of
		 * service attacks. Within the configured time frame, only a limited
		 * number of orders can be created. All orders of the current user
		 * (either logged in or identified by the IP address) within the last X
		 * seconds are counted. If the total value is higher then the number
		 * configured in "controller/frontend/basket/limit-count", an error
		 * message will be shown to the user instead of creating a new order.
		 *
		 * @param integer Number of seconds to check orders within
		 * @since 2017.05
		 * @category Developer
		 * @see controller/frontend/basket/limit-count
		 */
		$seconds = $config->get( 'controller/frontend/basket/limit-seconds', 300 );

		$search = $this->domainManager->createSearch();
		$expr = [
			$search->compare( '==', 'order.base.editor', $context->getEditor() ),
			$search->compare( '>=', 'order.base.ctime', date( 'Y-m-d H:i:s', time() - $seconds ) ),
		];
		$search->setConditions( $search->combine( '&&', $expr ) );
		$search->setSlice( 0, 0 );

		$this->domainManager->searchItems( $search, [], $total );

		if( $total > $count )
		{
			$msg = $context->getI18n()->dt( 'controller/frontend', 'Temporary order limit reached' );
			throw new \Aimeos\Controller\Frontend\Basket\Exception( $msg );
		}


		$basket = $this->get()->finish();
		$basket->setCustomerId( (string) $context->getUserId() );

		$this->domainManager->begin();
		$this->domainManager->store( $basket );
		$this->domainManager->commit();

		$this->createSubscriptions( $basket );

		return $basket;
	}


	/**
	 * Returns the order base object for the given ID
	 *
	 * @param string $id Unique ID of the order base object
	 * @param integer $parts Constants which parts of the order base object should be loaded
	 * @param boolean $default True to add default criteria (user logged in), false if not
	 * @return \Aimeos\MShop\Order\Item\Base\Iface Order base object including the given parts
	 */
	public function load( $id, $parts = \Aimeos\MShop\Order\Item\Base\Base::PARTS_ALL, $default = true )
	{
		return $this->domainManager->load( $id, $parts, false, $default );
	}


	/**
	 * Adds a categorized product to the basket of the user stored in the session.
	 *
	 * @param string $prodid ID of the base product to add
	 * @param integer $quantity Amount of products that should by added
	 * @param array $variantAttributeIds List of variant-building attribute IDs that identify a specific product
	 * 	in a selection products
	 * @param array $configAttributeIds  List of attribute IDs that doesn't identify a specific product in a
	 * 	selection of products but are stored together with the product (e.g. for configurable products)
	 * @param array $hiddenAttributeIds Deprecated
	 * @param array $customAttributeValues Associative list of attribute IDs and arbitrary values that should be stored
	 * 	along with the product in the order
	 * @param string $stocktype Unique code of the stock type to deliver the products from
	 * @throws \Aimeos\Controller\Frontend\Basket\Exception If the product isn't available
	 */
	public function addProduct( $prodid, $quantity = 1, $stocktype = 'default', array $variantAttributeIds = [],
		array $configAttributeIds = [], array $hiddenAttributeIds = [], array $customAttributeValues = [] )
	{
		$attributeMap = [
			'custom' => array_keys( $customAttributeValues ),
			'config' => array_keys( $configAttributeIds ),
		];
		$this->checkListRef( $prodid, 'attribute', $attributeMap );


		$context = $this->getContext();
		$productManager = \Aimeos\MShop\Factory::createManager( $context, 'product' );

		$productItem = $productManager->getItem( $prodid, ['attribute', 'media', 'supplier', 'price', 'product', 'text'], true );
		$prices = $productItem->getRefItems( 'price', 'default', 'default' );
		$hidden = $productItem->getRefItems( 'attribute', null, 'hidden' );

		$orderBaseProductItem = \Aimeos\MShop\Factory::createManager( $context, 'order/base/product' )->createItem();
		$orderBaseProductItem->copyFrom( $productItem )->setQuantity( $quantity )->setStockType( $stocktype );

		$custAttr = $this->getOrderProductAttributes( 'custom', array_keys( $customAttributeValues ), $customAttributeValues );
		$confAttr = $this->getOrderProductAttributes( 'config', array_keys( $configAttributeIds ), [], $configAttributeIds );
		$attr = array_merge( $custAttr, $confAttr, $this->getOrderProductAttributes( 'hidden', array_keys( $hidden ) ) );

		$orderBaseProductItem->setAttributes( $attr );
		$orderBaseProductItem->setPrice( $this->calcPrice( $orderBaseProductItem, $prices, $quantity ) );

		$this->get()->addProduct( $orderBaseProductItem );
		$this->save();
	}


	/**
	 * Deletes a product item from the basket.
	 *
	 * @param integer $position Position number (key) of the order product item
	 */
	public function deleteProduct( $position )
	{
		$product = $this->get()->getProduct( $position );

		if( $product->getFlags() === \Aimeos\MShop\Order\Item\Base\Product\Base::FLAG_IMMUTABLE )
		{
			$msg = $this->getContext()->getI18n()->dt( 'controller/frontend', 'Basket item at position "%1$d" cannot be deleted manually' );
			throw new \Aimeos\Controller\Frontend\Basket\Exception( sprintf( $msg, $position ) );
		}

		$this->get()->deleteProduct( $position );
		$this->save();
	}


	/**
	 * Edits the quantity of a product item in the basket.
	 *
	 * @param integer $position Position number (key) of the order product item
	 * @param integer $quantity New quantiy of the product item
	 * @param string[] $configAttributeCodes Codes of the product config attributes that should be REMOVED
	 */
	public function editProduct( $position, $quantity, array $configAttributeCodes = [] )
	{
		$product = $this->get()->getProduct( $position );

		if( $product->getFlags() & \Aimeos\MShop\Order\Item\Base\Product\Base::FLAG_IMMUTABLE )
		{
			$msg = $this->getContext()->getI18n()->dt( 'controller/frontend', 'Basket item at position "%1$d" cannot be changed' );
			throw new \Aimeos\Controller\Frontend\Basket\Exception( sprintf( $msg, $position ) );
		}

		$product->setQuantity( $quantity );

		$attributes = $product->getAttributes();
		foreach( $attributes as $key => $attribute )
		{
			if( in_array( $attribute->getCode(), $configAttributeCodes ) ) {
				unset( $attributes[$key] );
			}
		}
		$product->setAttributes( $attributes );

		$manager = \Aimeos\MShop\Factory::createManager( $this->getContext(), 'product' );
		$productItem = $manager->findItem( $product->getProductCode(), array( 'price', 'text' ), true );
		$product->setPrice( $this->calcPrice( $product, $productItem->getRefItems( 'price', 'default' ), $quantity ) );

		$this->get()->editProduct( $product, $position );

		$this->save();
	}


	/**
	 * Adds the given coupon code and updates the basket.
	 *
	 * @param string $code Coupon code entered by the user
	 * @throws \Aimeos\Controller\Frontend\Basket\Exception if the coupon code is invalid or not allowed
	 */
	public function addCoupon( $code )
	{
		$context = $this->getContext();

		/** controller/frontend/basket/standard/coupon/allowed
		 * Number of coupon codes a customer is allowed to enter
		 *
		 * This configuration option enables shop owners to limit the number of coupon
		 * codes that can be added by a customer to his current basket. By default, only
		 * one coupon code is allowed per order.
		 *
		 * Coupon codes are valid until a payed order is placed by the customer. The
		 * "count" of the codes is decreased afterwards. If codes are not personalized
		 * the codes can be reused in the next order until their "count" reaches zero.
		 *
		 * @param integer Positive number of coupon codes including zero
		 * @since 2017.08
		 * @category User
		 * @category Developer
		 */
		$allowed = $context->getConfig()->get( 'controller/frontend/basket/standard/coupon/allowed', 1 );

		if( $allowed <= count( $this->get()->getCoupons() ) )
		{
			$msg = $context->getI18n()->dt( 'controller/frontend', 'Number of coupon codes exceeds the limit' );
			throw new \Aimeos\Controller\Frontend\Basket\Exception( $msg );
		}


		$manager = \Aimeos\MShop\Factory::createManager( $context, 'coupon' );

		$search = $manager->createSearch();
		$search->setConditions( $search->compare( '==', 'coupon.code.code', $code ) );
		$search->setSlice( 0, 1 );

		$result = $manager->searchItems( $search );

		if( ( $item = reset( $result ) ) === false )
		{
			$msg = sprintf( $context->getI18n()->dt( 'controller/frontend', 'Coupon code "%1$s" is invalid or not available any more' ), $code );
			throw new \Aimeos\Controller\Frontend\Basket\Exception( $msg );
		}


		$provider = $manager->getProvider( $item, strtolower( $code ) );

		if( $provider->isAvailable( $this->get() ) !== true )
		{
			$msg = sprintf( $context->getI18n()->dt( 'controller/frontend', 'Requirements for coupon code "%1$s" aren\'t met' ), $code );
			throw new \Aimeos\Controller\Frontend\Basket\Exception( $msg );
		}

		$provider->addCoupon( $this->get() );
		$this->save();
	}


	/**
	 * Removes the given coupon code and its effects from the basket.
	 *
	 * @param string $code Coupon code entered by the user
	 * @throws \Aimeos\Controller\Frontend\Basket\Exception if the coupon code is invalid
	 */
	public function deleteCoupon( $code )
	{
		$context = $this->getContext();
		$manager = \Aimeos\MShop\Factory::createManager( $context, 'coupon' );

		$search = $manager->createSearch();
		$search->setConditions( $search->compare( '==', 'coupon.code.code', $code ) );
		$search->setSlice( 0, 1 );

		$result = $manager->searchItems( $search );

		if( ( $item = reset( $result ) ) === false )
		{
			$msg = $context->getI18n()->dt( 'controller/frontend', 'Coupon code "%1$s" is invalid' );
			throw new \Aimeos\Controller\Frontend\Basket\Exception( sprintf( $msg, $code ) );
		}

		$manager->getProvider( $item, strtolower( $code ) )->deleteCoupon( $this->get() );
		$this->save();
	}


	/**
	 * Sets the address of the customer in the basket.
	 *
	 * @param string $type Address type constant from \Aimeos\MShop\Order\Item\Base\Address\Base
	 * @param \Aimeos\MShop\Common\Item\Address\Iface|array|null $value Address object or array with key/value pairs of address or null to remove address from basket
	 * @throws \Aimeos\Controller\Frontend\Basket\Exception If the billing or delivery address is not of any required type of
	 * 	if one of the keys is invalid when using an array with key/value pairs
	 */
	public function setAddress( $type, $value )
	{
		$context = $this->getContext();
		$address = \Aimeos\MShop\Factory::createManager( $context, 'order/base/address' )->createItem();
		$address->setType( $type );

		if( $value instanceof \Aimeos\MShop\Common\Item\Address\Iface )
		{
			$address->copyFrom( $value );
			$this->get()->setAddress( $address, $type );
		}
		else if( is_array( $value ) )
		{
			$this->setAddressFromArray( $address, $value );
			$this->get()->setAddress( $address, $type );
		}
		else if( $value === null )
		{
			$this->get()->deleteAddress( $type );
		}
		else
		{
			$msg = $context->getI18n()->dt( 'controller/frontend', 'Invalid value for address type "%1$s"' );
			throw new \Aimeos\Controller\Frontend\Basket\Exception( sprintf( $msg, $type ) );
		}

		$this->save();
	}


	/**
	 * Adds the delivery/payment service item based on the service ID.
	 *
	 * @param string $type Service type code like 'payment' or 'delivery'
	 * @param string $id|null Unique ID of the service item or null to remove it
	 * @param array $attributes Associative list of key/value pairs containing the attributes selected or
	 * 	entered by the customer when choosing one of the delivery or payment options
	 * @throws \Aimeos\Controller\Frontend\Basket\Exception If there is no price to the service item attached
	 */
	public function addService( $type, $id, array $attributes = [] )
	{
		$context = $this->getContext();

		$serviceManager = \Aimeos\MShop\Factory::createManager( $context, 'service' );
		$serviceItem = $serviceManager->getItem( $id, array( 'media', 'price', 'text' ) );

		$provider = $serviceManager->getProvider( $serviceItem, $serviceItem->getType() );
		$result = $provider->checkConfigFE( $attributes );
		$unknown = array_diff_key( $attributes, $result );

		if( count( $unknown ) > 0 )
		{
			$msg = $context->getI18n()->dt( 'controller/frontend', 'Unknown attributes "%1$s"' );
			$msg = sprintf( $msg, implode( '","', array_keys( $unknown ) ) );
			throw new \Aimeos\Controller\Frontend\Basket\Exception( $msg );
		}

		foreach( $result as $key => $value )
		{
			if( $value !== null ) {
				throw new \Aimeos\Controller\Frontend\Basket\Exception( $value );
			}
		}

		$orderBaseServiceManager = \Aimeos\MShop\Factory::createManager( $context, 'order/base/service' );
		$orderServiceItem = $orderBaseServiceManager->createItem();
		$orderServiceItem->copyFrom( $serviceItem );

		// remove service rebate of original price
		$price = $provider->calcPrice( $this->get() )->setRebate( '0.00' );
		$orderServiceItem->setPrice( $price );

		$provider->setConfigFE( $orderServiceItem, $attributes );

		$this->get()->addService( $orderServiceItem, $type );
		$this->save();
	}


	/**
	 * Removes the delivery or payment service items from the basket
	 *
	 * @param string $type Service type code like 'payment' or 'delivery'
	 */
	public function deleteService( $type )
	{
		$this->get()->deleteService( $type );
		$this->save();
	}


	/**
	 * Fills the order address object with the values from the array.
	 *
	 * @param \Aimeos\MShop\Order\Item\Base\Address\Iface $address Address item to store the values into
	 * @param array $map Associative array of key/value pairs. The keys must be the same as when calling toArray() from
	 * 	an address item.
	 * @throws \Aimeos\Controller\Frontend\Basket\Exception
	 */
	protected function setAddressFromArray( \Aimeos\MShop\Order\Item\Base\Address\Iface $address, array $map )
	{
		foreach( $map as $key => $value ) {
			$map[$key] = strip_tags( $value ); // prevent XSS
		}

		$errors = $address->fromArray( $map );

		if( count( $errors ) > 0 )
		{
			$msg = $this->getContext()->getI18n()->dt( 'controller/frontend', 'Invalid address properties, please check your input' );
			throw new \Aimeos\Controller\Frontend\Basket\Exception( $msg, 0, null, $errors );
		}
	}
}
