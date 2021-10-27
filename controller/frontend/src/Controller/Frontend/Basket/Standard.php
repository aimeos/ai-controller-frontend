<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Metaways Infosystems GmbH, 2012
 * @copyright Aimeos (aimeos.org), 2015-2021
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
	private $manager;
	private $baskets = [];
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

		$this->manager = \Aimeos\MShop::create( $context, 'order/base' );
	}


	/**
	 * Adds values like comments to the basket
	 *
	 * @param array $values Order base values like comment
	 * @return \Aimeos\Controller\Frontend\Basket\Iface Basket frontend object for fluent interface
	 */
	public function add( array $values ) : Iface
	{
		$this->baskets[$this->type] = $this->get()->fromArray( $values );
		return $this;
	}


	/**
	 * Empties the basket and removing all products, addresses, services, etc.
	 *
	 * @return \Aimeos\Controller\Frontend\Basket\Iface Basket frontend object for fluent interface
	 */
	public function clear() : Iface
	{
		$this->baskets[$this->type] = $this->manager->create();
		$this->manager->setSession( $this->baskets[$this->type], $this->type );

		return $this;
	}


	/**
	 * Returns the basket object.
	 *
	 * @return \Aimeos\MShop\Order\Item\Base\Iface Basket holding products, addresses and delivery/payment options
	 */
	public function get() : \Aimeos\MShop\Order\Item\Base\Iface
	{
		if( !isset( $this->baskets[$this->type] ) )
		{
			$this->baskets[$this->type] = $this->manager->getSession( $this->type );
			$this->checkLocale( $this->baskets[$this->type]->getLocale(), $this->type );
			$this->baskets[$this->type]->setCustomerId( (string) $this->getContext()->getUserId() );
		}

		return $this->baskets[$this->type];
	}


	/**
	 * Explicitely persists the basket content
	 *
	 * @return \Aimeos\Controller\Frontend\Basket\Iface Basket frontend object for fluent interface
	 */
	public function save() : Iface
	{
		if( isset( $this->baskets[$this->type] ) && $this->baskets[$this->type]->isModified() ) {
			$this->manager->setSession( $this->baskets[$this->type], $this->type );
		}

		return $this;
	}


	/**
	 * Sets the new basket type
	 *
	 * @param string $type Basket type
	 * @return \Aimeos\Controller\Frontend\Basket\Iface Basket frontend object for fluent interface
	 */
	public function setType( string $type ) : Iface
	{
		$this->type = $type;
		return $this;
	}


	/**
	 * Creates a new order base object from the current basket
	 *
	 * @return \Aimeos\MShop\Order\Item\Base\Iface Order base object including products, addresses and services
	 */
	public function store() : \Aimeos\MShop\Order\Item\Base\Iface
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
		$seconds = $config->get( 'controller/frontend/basket/limit-seconds', 900 );

		$search = $this->manager->filter()->slice( 0, 0 );
		$expr = [
			$search->compare( '==', 'order.base.editor', $context->getEditor() ),
			$search->compare( '>=', 'order.base.ctime', date( 'Y-m-d H:i:s', time() - $seconds ) ),
		];
		$search->setConditions( $search->and( $expr ) );

		$this->manager->search( $search, [], $total );

		if( $total >= $count )
		{
			$msg = $context->translate( 'controller/frontend', 'Temporary order limit reached' );
			throw new \Aimeos\Controller\Frontend\Basket\Exception( $msg );
		}


		$basket = $this->get()->setCustomerId( (string) $context->getUserId() )->finish()->check();

		$this->manager->begin();
		$this->manager->store( $basket );
		$this->manager->commit();

		$this->save(); // for reusing unpaid orders, might have side effects (!)
		$this->createSubscriptions( $basket );

		return $basket;
	}


	/**
	 * Returns the order base object for the given ID
	 *
	 * @param string $id Unique ID of the order base object
	 * @param array $ref References items that should be fetched too
	 * @param bool $default True to add default criteria (user logged in), false if not
	 * @return \Aimeos\MShop\Order\Item\Base\Iface Order base object including the given parts
	 * @todo 2021.01 Use array type hint for $ref
	 */
	public function load( string $id, $ref = \Aimeos\MShop\Order\Item\Base\Base::PARTS_ALL,
		bool $default = true ) : \Aimeos\MShop\Order\Item\Base\Iface
	{
		if( is_int( $ref ) ) {
			return $this->manager->load( $id, $ref, false, $default );
		}

		return $this->manager->get( $id, $ref, $default );
	}


	/**
	 * Adds a product to the basket of the customer stored in the session
	 *
	 * @param \Aimeos\MShop\Product\Item\Iface $product Product to add including texts, media, prices, attributes, etc.
	 * @param float $quantity Amount of products that should by added
	 * @param array $variant List of variant-building attribute IDs that identify an article in a selection product
	 * @param array $config List of configurable attribute IDs the customer has chosen from
	 * @param array $custom Associative list of attribute IDs as keys and arbitrary values that will be added to the ordered product
	 * @param string $stocktype Unique code of the stock type to deliver the products from
	 * @param string|null $supplierid Unique supplier ID the product is from
	 * @param string|null $siteid Unique site ID the product is from or null for siteid of the product item
	 * @return \Aimeos\Controller\Frontend\Basket\Iface Basket frontend object for fluent interface
	 * @throws \Aimeos\Controller\Frontend\Basket\Exception If the product isn't available
	 */
	public function addProduct( \Aimeos\MShop\Product\Item\Iface $product,
		float $quantity = 1, array $variant = [], array $config = [], array $custom = [],
		string $stocktype = 'default', string $supplierid = null, string $siteid = null ) : Iface
	{
		$quantity = $this->call( 'checkQuantity', $product, $quantity );
		$this->call( 'checkAttributes', [$product], 'custom', array_keys( $custom ) );
		$this->call( 'checkAttributes', [$product], 'config', array_keys( $config ) );

		$prices = $product->getRefItems( 'price', 'default', 'default' );
		$hidden = $product->getRefItems( 'attribute', null, 'hidden' );

		$custAttr = $this->call( 'getOrderProductAttributes', 'custom', array_keys( $custom ), $custom );
		$confAttr = $this->call( 'getOrderProductAttributes', 'config', array_keys( $config ), [], $config );
		$hideAttr = $this->call( 'getOrderProductAttributes', 'hidden', $hidden->keys()->toArray() );

		$orderBaseProductItem = \Aimeos\MShop::create( $this->getContext(), 'order/base/product' )->create()
			->copyFrom( $product )->setQuantity( $quantity )->setStockType( $stocktype )
			->setAttributeItems( array_merge( $custAttr, $confAttr, $hideAttr ) );

		$orderBaseProductItem = $orderBaseProductItem
			->setPrice( $this->call( 'calcPrice', $orderBaseProductItem, $prices, $quantity ) );

		if( $siteid ) {
			$orderBaseProductItem->setSiteId( $siteid );
		}

		if( $supplierid )
		{
			$name = \Aimeos\MShop::create( $this->getContext(), 'supplier' )->get( $supplierid, ['text'] )->getName();
			$orderBaseProductItem->setSupplierId( $supplierid )->setSupplierName( $name );
		}

		$this->baskets[$this->type] = $this->get()->addProduct( $orderBaseProductItem );
		return $this->save();
	}


	/**
	 * Deletes a product item from the basket.
	 *
	 * @param int $position Position number (key) of the order product item
	 * @return \Aimeos\Controller\Frontend\Basket\Iface Basket frontend object for fluent interface
	 */
	public function deleteProduct( int $position ) : Iface
	{
		$product = $this->get()->getProduct( $position );

		if( $product->getFlags() === \Aimeos\MShop\Order\Item\Base\Product\Base::FLAG_IMMUTABLE )
		{
			$msg = $this->getContext()->translate( 'controller/frontend', 'Basket item at position "%1$d" cannot be deleted manually' );
			throw new \Aimeos\Controller\Frontend\Basket\Exception( sprintf( $msg, $position ) );
		}

		$this->baskets[$this->type] = $this->get()->deleteProduct( $position );
		return $this->save();
	}


	/**
	 * Edits the quantity of a product item in the basket.
	 *
	 * @param int $position Position number (key) of the order product item
	 * @param float $quantity New quantiy of the product item
	 * @return \Aimeos\Controller\Frontend\Basket\Iface Basket frontend object for fluent interface
	 */
	public function updateProduct( int $position, float $quantity ) : Iface
	{
		$context = $this->getContext();
		$orderProduct = $this->get()->getProduct( $position );

		if( $orderProduct->getFlags() & \Aimeos\MShop\Order\Item\Base\Product\Base::FLAG_IMMUTABLE )
		{
			$msg = $context->translate( 'controller/frontend', 'Basket item at position "%1$d" cannot be changed' );
			throw new \Aimeos\Controller\Frontend\Basket\Exception( sprintf( $msg, $position ) );
		}

		$manager = \Aimeos\MShop::create( $context, 'product' );
		$product = $manager->find( $orderProduct->getProductCode(), ['price', 'text'], true );
		$product = \Aimeos\MShop::create( $context, 'rule' )->apply( $product, 'catalog' );

		$quantity = $this->call( 'checkQuantity', $product, $quantity );
		$price = $this->call( 'calcPrice', $orderProduct, $product->getRefItems( 'price', 'default' ), $quantity );
		$orderProduct = $orderProduct->setQuantity( $quantity )->setPrice( $price );

		$this->baskets[$this->type] = $this->get()->addProduct( $orderProduct, $position );
		return $this->save();
	}


	/**
	 * Adds the given coupon code and updates the basket.
	 *
	 * @param string $code Coupon code entered by the user
	 * @return \Aimeos\Controller\Frontend\Basket\Iface Basket frontend object for fluent interface
	 * @throws \Aimeos\Controller\Frontend\Basket\Exception if the coupon code is invalid or not allowed
	 */
	public function addCoupon( string $code ) : Iface
	{
		$context = $this->getContext();

		/** controller/frontend/basket/coupon/allowed
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
		$allowed = $context->getConfig()->get( 'controller/frontend/basket/coupon/allowed', 1 );

		if( $allowed <= count( $this->get()->getCoupons() ) )
		{
			$msg = $context->translate( 'controller/frontend', 'Number of coupon codes exceeds the limit' );
			throw new \Aimeos\Controller\Frontend\Basket\Exception( $msg );
		}

		$this->baskets[$this->type] = $this->get()->addCoupon( $code );
		return $this->save();
	}


	/**
	 * Removes the given coupon code and its effects from the basket.
	 *
	 * @param string $code Coupon code entered by the user
	 * @return \Aimeos\Controller\Frontend\Basket\Iface Basket frontend object for fluent interface
	 * @throws \Aimeos\Controller\Frontend\Basket\Exception if the coupon code is invalid
	 */
	public function deleteCoupon( string $code ) : Iface
	{
		$this->baskets[$this->type] = $this->get()->deleteCoupon( $code );
		return $this->save();
	}


	/**
	 * Adds an address of the customer to the basket
	 *
	 * @param string $type Address type code like 'payment' or 'delivery'
	 * @param array $values Associative list of key/value pairs with address details
	 * @param int|null $position Position number (key) of the order address item
	 * @return \Aimeos\Controller\Frontend\Basket\Iface Basket frontend object for fluent interface
	 */
	public function addAddress( string $type, array $values = [], int $position = null ) : Iface
	{
		foreach( $values as $key => $value )
		{
			if( is_scalar( $value ) ) {
				$values[$key] = strip_tags( (string) $value ); // prevent XSS
			}
		}

		$context = $this->getContext();
		$address = \Aimeos\MShop::create( $context, 'order/base/address' )->create()->fromArray( $values );
		$address->set( 'nostore', ( $values['nostore'] ?? false ) ? true : false );

		$this->baskets[$this->type] = $this->get()->addAddress( $address, $type, $position );
		return $this->save();
	}


	/**
	 * Removes the address of the given type and position if available
	 *
	 * @param string $type Address type code like 'payment' or 'delivery'
	 * @param int|null $position Position of the address in the list to overwrite
	 * @return \Aimeos\Controller\Frontend\Basket\Iface Basket frontend object for fluent interface
	 */
	public function deleteAddress( string $type, int $position = null ) : Iface
	{
		$this->baskets[$this->type] = $this->get()->deleteAddress( $type, $position );
		return $this->save();
	}


	/**
	 * Adds the delivery/payment service including the given configuration
	 *
	 * @param \Aimeos\MShop\Service\Item\Iface $service Service item selected by the customer
	 * @param array $config Associative list of key/value pairs with the options selected by the customer
	 * @param int|null $position Position of the address in the list to overwrite
	 * @return \Aimeos\Controller\Frontend\Basket\Iface Basket frontend object for fluent interface
	 * @throws \Aimeos\Controller\Frontend\Basket\Exception If given service attributes are invalid
	 */
	public function addService( \Aimeos\MShop\Service\Item\Iface $service, array $config = [], int $position = null ) : Iface
	{
		$context = $this->getContext();
		$manager = \Aimeos\MShop::create( $context, 'service' );

		$provider = $manager->getProvider( $service, $service->getType() );
		$errors = $provider->checkConfigFE( $config );
		$unknown = array_diff_key( $config, $errors );

		if( count( $unknown ) > 0 )
		{
			$msg = $context->translate( 'controller/frontend', 'Unknown service attributes' );
			throw new \Aimeos\Controller\Frontend\Basket\Exception( $msg, -1, null, $unknown );
		}

		if( count( array_filter( $errors ) ) > 0 )
		{
			$msg = $context->translate( 'controller/frontend', 'Invalid service attributes' );
			throw new \Aimeos\Controller\Frontend\Basket\Exception( $msg, -1, null, array_filter( $errors ) );
		}

		// remove service rebate of original price
		$price = $provider->calcPrice( $this->get() )->setRebate( '0.00' );

		$orderBaseServiceManager = \Aimeos\MShop::create( $context, 'order/base/service' );

		$orderServiceItem = $orderBaseServiceManager->create()->copyFrom( $service )->setPrice( $price );
		$orderServiceItem = $provider->setConfigFE( $orderServiceItem, $config );

		$this->baskets[$this->type] = $this->get()->addService( $orderServiceItem, $service->getType(), $position );
		return $this->save();
	}


	/**
	 * Removes the delivery or payment service items from the basket
	 *
	 * @param string $type Service type code like 'payment' or 'delivery'
	 * @param int|null $position Position of the address in the list to overwrite
	 * @return \Aimeos\Controller\Frontend\Basket\Iface Basket frontend object for fluent interface
	 */
	public function deleteService( string $type, int $position = null ) : Iface
	{
		$this->baskets[$this->type] = $this->get()->deleteService( $type, $position );
		return $this->save();
	}


	/**
	 * Returns the manager used by the controller
	 *
	 * @return \Aimeos\MShop\Common\Manager\Iface Manager object
	 */
	protected function getManager() : \Aimeos\MShop\Common\Manager\Iface
	{
		return $this->manager;
	}
}
