<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Metaways Infosystems GmbH, 2012
 * @copyright Aimeos (aimeos.org), 2015-2016
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
class Standard extends Base implements Iface, \Aimeos\Controller\Frontend\Common\Iface
{
	private $basket;
	private $domainManager;


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
		$this->basket = $this->domainManager->getSession();

		$this->checkLocale();
	}


	/**
	 * Empties the basket and removing all products, addresses, services, etc.
	 */
	public function clear()
	{
		$this->basket = $this->domainManager->createItem();
		$this->domainManager->setSession( $this->basket );
	}


	/**
	 * Returns the basket object.
	 *
	 * @return \Aimeos\MShop\Order\Item\Base\Iface Basket holding products, addresses and delivery/payment options
	 */
	public function get()
	{
		return $this->basket;
	}


	/**
	 * Adds a categorized product to the basket of the user stored in the session.
	 *
	 * @param string $prodid ID of the base product to add
	 * @param integer $quantity Amount of products that should by added
	 * @param array $options Possible options are: 'stock'=>true|false and 'variant'=>true|false
	 * 	The 'stock'=>false option allows adding products without being in stock.
	 * 	The 'variant'=>false option allows adding the selection product to the basket
	 * 	instead of the specific sub-product if the variant-building attribute IDs
	 * 	doesn't match a specific sub-product or if the attribute IDs are missing.
	 * @param array $variantAttributeIds List of variant-building attribute IDs that identify a specific product
	 * 	in a selection products
	 * @param array $configAttributeIds  List of attribute IDs that doesn't identify a specific product in a
	 * 	selection of products but are stored together with the product (e.g. for configurable products)
	 * @param array $hiddenAttributeIds List of attribute IDs that should be stored along with the product in the order
	 * @param array $customAttributeValues Associative list of attribute IDs and arbitrary values that should be stored
	 * 	along with the product in the order
	 * @param string $warehouse Unique code of the warehouse to deliver the products from
	 * @throws \Aimeos\Controller\Frontend\Basket\Exception If the product isn't available
	 */
	public function addProduct( $prodid, $quantity = 1, array $options = array(), array $variantAttributeIds = array(),
		array $configAttributeIds = array(), array $hiddenAttributeIds = array(), array $customAttributeValues = array(),
		$warehouse = 'default' )
	{
		$context = $this->getContext();

		$productItem = $this->getDomainItem( 'product', 'product.id', $prodid, array( 'media', 'supplier', 'price', 'product', 'text' ) );

		$orderBaseProductItem = \Aimeos\MShop\Factory::createManager( $context, 'order/base/product' )->createItem();
		$orderBaseProductItem->copyFrom( $productItem );
		$orderBaseProductItem->setQuantity( $quantity );
		$orderBaseProductItem->setWarehouseCode( $warehouse );

		$attr = array();
		$prices = $productItem->getRefItems( 'price', 'default', 'default' );

		switch( $productItem->getType() )
		{
			case 'select':
				$attr = $this->getVariantDetails( $orderBaseProductItem, $productItem, $prices, $variantAttributeIds, $options );
				break;
			case 'bundle':
				$this->addBundleProducts( $orderBaseProductItem, $productItem, $variantAttributeIds, $warehouse );
				break;
		}

		$priceManager = \Aimeos\MShop\Factory::createManager( $context, 'price' );
		$price = $priceManager->getLowestPrice( $prices, $quantity );

		$attr = array_merge( $attr, $this->createOrderProductAttributes( $price, $prodid, $quantity, $configAttributeIds, 'config' ) );
		$attr = array_merge( $attr, $this->createOrderProductAttributes( $price, $prodid, $quantity, $hiddenAttributeIds, 'hidden' ) );
		$attr = array_merge( $attr, $this->createOrderProductAttributes( $price, $prodid, $quantity, array_keys( $customAttributeValues ), 'custom', $customAttributeValues ) );

		// remove product rebate of original price in favor to rebates granted for the order
		$price->setRebate( '0.00' );

		$orderBaseProductItem->setPrice( $price );
		$orderBaseProductItem->setAttributes( $attr );

		$this->basket->addProduct( $orderBaseProductItem );
		$this->domainManager->setSession( $this->basket );
	}


	/**
	 * Deletes a product item from the basket.
	 *
	 * @param integer $position Position number (key) of the order product item
	 */
	public function deleteProduct( $position )
	{
		$product = $this->basket->getProduct( $position );

		if( $product->getFlags() === \Aimeos\MShop\Order\Item\Base\Product\Base::FLAG_IMMUTABLE )
		{
			$msg = sprintf( 'Basket item at position "%1$d" cannot be deleted manually', $position );
			throw new \Aimeos\Controller\Frontend\Basket\Exception( $msg );
		}

		$this->basket->deleteProduct( $position );
		$this->domainManager->setSession( $this->basket );
	}


	/**
	 * Edits the quantity of a product item in the basket.
	 *
	 * @param integer $position Position number (key) of the order product item
	 * @param integer $quantity New quantiy of the product item
	 * @param array $options Possible options are: 'stock'=>true|false
	 * 	The 'stock'=>false option allows adding products without being in stock.
	 * @param string[] $configAttributeCodes Codes of the product config attributes that should be REMOVED
	 */
	public function editProduct( $position, $quantity, array $options = array(),
		array $configAttributeCodes = array() )
	{
		$product = $this->basket->getProduct( $position );
		$product->setQuantity( $quantity ); // Enforce check immediately

		if( $product->getFlags() & \Aimeos\MShop\Order\Item\Base\Product\Base::FLAG_IMMUTABLE )
		{
			$msg = sprintf( 'Basket item at position "%1$d" cannot be changed', $position );
			throw new \Aimeos\Controller\Frontend\Basket\Exception( $msg );
		}

		$attributes = $product->getAttributes();
		foreach( $attributes as $key => $attribute )
		{
			if( in_array( $attribute->getCode(), $configAttributeCodes ) ) {
				unset( $attributes[$key] );
			}
		}
		$product->setAttributes( $attributes );

		$productItem = $this->getDomainItem( 'product', 'product.code', $product->getProductCode(), array( 'price', 'text' ) );
		$prices = $productItem->getRefItems( 'price', 'default' );

		$product->setPrice( $this->calcPrice( $product, $prices, $quantity ) );

		$this->basket->deleteProduct( $position );
		$this->basket->addProduct( $product, $position );

		$this->domainManager->setSession( $this->basket );
	}


	/**
	 * Adds the given coupon code and updates the basket.
	 *
	 * @param string $code Coupon code entered by the user
	 * @throws \Aimeos\Controller\Frontend\Basket\Exception if the coupon code is invalid or not allowed
	 */
	public function addCoupon( $code )
	{
		$manager = \Aimeos\MShop\Factory::createManager( $this->getContext(), 'coupon' );
		$codeManager = \Aimeos\MShop\Factory::createManager( $this->getContext(), 'coupon/code' );


		$search = $codeManager->createSearch( true );
		$expr = array(
			$search->compare( '==', 'coupon.code.code', $code ),
			$search->getConditions(),
		);
		$search->setConditions( $search->combine( '&&', $expr ) );
		$search->setSlice( 0, 1 );

		$result = $codeManager->searchItems( $search );

		if( ( $codeItem = reset( $result ) ) === false ) {
			throw new \Aimeos\Controller\Frontend\Basket\Exception( sprintf( 'Coupon code "%1$s" is invalid or not available any more', $code ) );
		}


		$search = $manager->createSearch( true );
		$expr = array(
			$search->compare( '==', 'coupon.id', $codeItem->getParentId() ),
			$search->getConditions(),
		);
		$search->setConditions( $search->combine( '&&', $expr ) );
		$search->setSlice( 0, 1 );

		$result = $manager->searchItems( $search );

		if( ( $item = reset( $result ) ) === false ) {
			throw new \Aimeos\Controller\Frontend\Basket\Exception( sprintf( 'Coupon for code "%1$s" is not available any more', $code ) );
		}


		$provider = $manager->getProvider( $item, $code );

		if( $provider->isAvailable( $this->basket ) !== true ) {
			throw new \Aimeos\Controller\Frontend\Basket\Exception( sprintf( 'Requirements for coupon code "%1$s" aren\'t met', $code ) );
		}

		$provider->addCoupon( $this->basket );
		$this->domainManager->setSession( $this->basket );
	}


	/**
	 * Removes the given coupon code and its effects from the basket.
	 *
	 * @param string $code Coupon code entered by the user
	 * @throws \Aimeos\Controller\Frontend\Basket\Exception if the coupon code is invalid
	 */
	public function deleteCoupon( $code )
	{
		$manager = \Aimeos\MShop\Factory::createManager( $this->getContext(), 'coupon' );

		$search = $manager->createSearch();
		$search->setConditions( $search->compare( '==', 'coupon.code.code', $code ) );
		$search->setSlice( 0, 1 );

		$result = $manager->searchItems( $search );

		if( ( $item = reset( $result ) ) === false ) {
			throw new \Aimeos\Controller\Frontend\Basket\Exception( sprintf( 'Coupon code "%1$s" is invalid', $code ) );
		}

		$manager->getProvider( $item, $code )->deleteCoupon( $this->basket );
		$this->domainManager->setSession( $this->basket );
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
		$address = \Aimeos\MShop\Factory::createManager( $this->getContext(), 'order/base/address' )->createItem();
		$address->setType( $type );

		if( $value instanceof \Aimeos\MShop\Common\Item\Address\Iface )
		{
			$address->copyFrom( $value );
			$this->basket->setAddress( $address, $type );
		}
		else if( is_array( $value ) )
		{
			$this->setAddressFromArray( $address, $value );
			$this->basket->setAddress( $address, $type );
		}
		else if( $value === null )
		{
			$this->basket->deleteAddress( $type );
		}
		else
		{
			throw new \Aimeos\Controller\Frontend\Basket\Exception( sprintf( 'Invalid value for address type "%1$s"', $type ) );
		}

		$this->domainManager->setSession( $this->basket );
	}


	/**
	 * Sets the delivery/payment service item based on the service ID.
	 *
	 * @param string $type Service type code like 'payment' or 'delivery'
	 * @param string $id Unique ID of the service item
	 * @param array $attributes Associative list of key/value pairs containing the attributes selected or
	 * 	entered by the customer when choosing one of the delivery or payment options
	 * @throws \Aimeos\Controller\Frontend\Basket\Exception If there is no price to the service item attached
	 */
	public function setService( $type, $id, array $attributes = array() )
	{
		$context = $this->getContext();

		$serviceManager = \Aimeos\MShop\Factory::createManager( $context, 'service' );
		$serviceItem = $this->getDomainItem( 'service', 'service.id', $id, array( 'media', 'price', 'text' ) );

		$provider = $serviceManager->getProvider( $serviceItem );
		$result = $provider->checkConfigFE( $attributes );
		$unknown = array_diff_key( $attributes, $result );

		if( count( $unknown ) > 0 )
		{
			$msg = sprintf( 'Unknown attributes "%1$s"', implode( '","', array_keys( $unknown ) ) );
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

		$price = $provider->calcPrice( $this->basket );
		// remove service rebate of original price
		$price->setRebate( '0.00' );
		$orderServiceItem->setPrice( $price );

		$provider->setConfigFE( $orderServiceItem, $attributes );

		$this->basket->setService( $orderServiceItem, $type );
		$this->domainManager->setSession( $this->basket );
	}


	/**
	 * Adds the bundled products to the order product item.
	 *
	 * @param \Aimeos\MShop\Order\Item\Base\Product\Iface $orderBaseProductItem Order product item
	 * @param \Aimeos\MShop\Product\Item\Iface $productItem Bundle product item
	 * @param array $variantAttributeIds List of product variant attribute IDs
	 * @param string $warehouse
	 */
	protected function addBundleProducts( \Aimeos\MShop\Order\Item\Base\Product\Iface $orderBaseProductItem,
		\Aimeos\MShop\Product\Item\Iface $productItem, array $variantAttributeIds, $warehouse )
	{
		$quantity = $orderBaseProductItem->getQuantity();
		$products = $subProductIds = $orderProducts = array();
		$orderProductManager = \Aimeos\MShop\Factory::createManager( $this->getContext(), 'order/base/product' );

		foreach( $productItem->getRefItems( 'product', null, 'default' ) as $item ) {
			$subProductIds[] = $item->getId();
		}

		if( count( $subProductIds ) > 0 )
		{
			$productManager = \Aimeos\MShop\Factory::createManager( $this->getContext(), 'product' );

			$search = $productManager->createSearch( true );
			$expr = array(
				$search->compare( '==', 'product.id', $subProductIds ),
				$search->getConditions(),
			);
			$search->setConditions( $search->combine( '&&', $expr ) );

			$products = $productManager->searchItems( $search, array( 'attribute', 'media', 'price', 'text' ) );
		}

		foreach( $products as $product )
		{
			$prices = $product->getRefItems( 'price', 'default', 'default' );

			$orderProduct = $orderProductManager->createItem();
			$orderProduct->copyFrom( $product );
			$orderProduct->setWarehouseCode( $warehouse );
			$orderProduct->setPrice( $this->calcPrice( $orderProduct, $prices, $quantity ) );

			$orderProducts[] = $orderProduct;
		}

		$orderBaseProductItem->setProducts( $orderProducts );
	}


	/**
	 * Checks for a locale mismatch and migrates the products to the new basket if necessary.
	 */
	protected function checkLocale()
	{
		$errors = array();
		$context = $this->getContext();
		$session = $context->getSession();
		$locale = $this->basket->getLocale();

		$localeStr = $session->get( 'aimeos/basket/locale' );
		$localeKey = $locale->getSite()->getCode() . '|' . $locale->getLanguageId() . '|' . $locale->getCurrencyId();

		if( $localeStr !== null && $localeStr !== $localeKey )
		{
			$locParts = explode( '|', $localeStr );
			$locSite = ( isset( $locParts[0] ) ? $locParts[0] : '' );
			$locLanguage = ( isset( $locParts[1] ) ? $locParts[1] : '' );
			$locCurrency = ( isset( $locParts[2] ) ? $locParts[2] : '' );

			$localeManager = \Aimeos\MShop\Factory::createManager( $context, 'locale' );
			$locale = $localeManager->bootstrap( $locSite, $locLanguage, $locCurrency, false );

			$context = clone $context;
			$context->setLocale( $locale );

			$manager = \Aimeos\MShop\Order\Manager\Factory::createManager( $context )->getSubManager( 'base' );
			$basket = $manager->getSession();

			$this->copyAddresses( $basket, $errors, $localeKey );
			$this->copyServices( $basket, $errors );
			$this->copyProducts( $basket, $errors, $localeKey );
			$this->copyCoupons( $basket, $errors, $localeKey );

			$manager->setSession( $basket );
		}

		$session->set( 'aimeos/basket/locale', $localeKey );
	}


	/**
	 * Migrates the addresses from the old basket to the current one.
	 *
	 * @param \Aimeos\MShop\Order\Item\Base\Iface $basket Basket object
	 * @param array $errors Associative list of previous errors
	 * @param string $localeKey Unique identifier of the site, language and currency
	 * @return array Associative list of errors occured
	 */
	protected function copyAddresses( \Aimeos\MShop\Order\Item\Base\Iface $basket, array $errors, $localeKey )
	{
		foreach( $basket->getAddresses() as $type => $item )
		{
			try
			{
				$this->setAddress( $type, $item->toArray() );
				$basket->deleteAddress( $type );
			}
			catch( \Exception $e )
			{
				$logger = $this->getContext()->getLogger();
				$str = 'Error migrating address with type "%1$s" in basket to locale "%2$s": %3$s';
				$logger->log( sprintf( $str, $type, $localeKey, $e->getMessage() ), \Aimeos\MW\Logger\Base::INFO );
				$errors['address'][$type] = $e->getMessage();
			}
		}

		return $errors;
	}


	/**
	 * Migrates the coupons from the old basket to the current one.
	 *
	 * @param \Aimeos\MShop\Order\Item\Base\Iface $basket Basket object
	 * @param array $errors Associative list of previous errors
	 * @param string $localeKey Unique identifier of the site, language and currency
	 * @return array Associative list of errors occured
	 */
	protected function copyCoupons( \Aimeos\MShop\Order\Item\Base\Iface $basket, array $errors, $localeKey )
	{
		foreach( $basket->getCoupons() as $code => $list )
		{
			try
			{
				$this->addCoupon( $code );
				$basket->deleteCoupon( $code, true );
			}
			catch( \Exception $e )
			{
				$logger = $this->getContext()->getLogger();
				$str = 'Error migrating coupon with code "%1$s" in basket to locale "%2$s": %3$s';
				$logger->log( sprintf( $str, $code, $localeKey, $e->getMessage() ), \Aimeos\MW\Logger\Base::INFO );
				$errors['coupon'][$code] = $e->getMessage();
			}
		}

		return $errors;
	}


	/**
	 * Migrates the products from the old basket to the current one.
	 *
	 * @param \Aimeos\MShop\Order\Item\Base\Iface $basket Basket object
	 * @param array $errors Associative list of previous errors
	 * @param string $localeKey Unique identifier of the site, language and currency
	 * @return array Associative list of errors occured
	 */
	protected function copyProducts( \Aimeos\MShop\Order\Item\Base\Iface $basket, array $errors, $localeKey )
	{
		foreach( $basket->getProducts() as $pos => $product )
		{
			if( $product->getFlags() & \Aimeos\MShop\Order\Item\Base\Product\Base::FLAG_IMMUTABLE ) {
				continue;
			}

			try
			{
				$attrIds = array();

				foreach( $product->getAttributes() as $attrItem ) {
					$attrIds[$attrItem->getType()][] = $attrItem->getAttributeId();
				}

				$this->addProduct(
					$product->getProductId(),
					$product->getQuantity(),
					array(),
					$this->getValue( $attrIds, 'variant', array() ),
					$this->getValue( $attrIds, 'config', array() ),
					$this->getValue( $attrIds, 'hidden', array() ),
					$this->getValue( $attrIds, 'custom', array() ),
					$product->getWarehouseCode()
				);

				$basket->deleteProduct( $pos );
			}
			catch( \Exception $e )
			{
				$code = $product->getProductCode();
				$logger = $this->getContext()->getLogger();
				$str = 'Error migrating product with code "%1$s" in basket to locale "%2$s": %3$s';
				$logger->log( sprintf( $str, $code, $localeKey, $e->getMessage() ), \Aimeos\MW\Logger\Base::INFO );
				$errors['product'][$pos] = $e->getMessage();
			}
		}

		return $errors;
	}


	/**
	 * Migrates the services from the old basket to the current one.
	 *
	 * @param \Aimeos\MShop\Order\Item\Base\Iface $basket Basket object
	 * @param array $errors Associative list of previous errors
	 * @return array Associative list of errors occured
	 */
	protected function copyServices( \Aimeos\MShop\Order\Item\Base\Iface $basket, array $errors )
	{
		foreach( $basket->getServices() as $type => $item )
		{
			try
			{
				$attributes = array();

				foreach( $item->getAttributes() as $attrItem ) {
					$attributes[$attrItem->getCode()] = $attrItem->getValue();
				}

				$this->setService( $type, $item->getServiceId(), $attributes );
				$basket->deleteService( $type );
			}
			catch( \Exception $e ) {; } // Don't notify the user as appropriate services can be added automatically
		}

		return $errors;
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
			$msg = sprintf( 'Invalid address properties, please check your input' );
			throw new \Aimeos\Controller\Frontend\Basket\Exception( $msg, 0, null, $errors );
		}
	}


	/**
	 * Returns the variant attributes and updates the price list if necessary.
	 *
	 * @param \Aimeos\MShop\Order\Item\Base\Product\Iface $orderBaseProductItem Order product item
	 * @param \Aimeos\MShop\Product\Item\Iface &$productItem Product item which is replaced if necessary
	 * @param array &$prices List of product prices that will be updated if necessary
	 * @param array $variantAttributeIds List of product variant attribute IDs
	 * @param array $options Associative list of options
	 * @return \Aimeos\MShop\Order\Item\Base\Product\Attribute\Iface[] List of order product attributes
	 * @throws \Aimeos\Controller\Frontend\Basket\Exception If no product variant is found
	 */
	protected function getVariantDetails( \Aimeos\MShop\Order\Item\Base\Product\Iface $orderBaseProductItem,
		\Aimeos\MShop\Product\Item\Iface &$productItem, array &$prices, array $variantAttributeIds, array $options )
	{
		$attr = array();
		$productItems = $this->getProductVariants( $productItem, $variantAttributeIds );

		if( count( $productItems ) > 1 )
		{
			$msg = sprintf( 'No unique article found for selected attributes and product ID "%1$s"', $productItem->getId() );
			throw new \Aimeos\Controller\Frontend\Basket\Exception( $msg );
		}
		else if( ( $result = reset( $productItems ) ) !== false ) // count == 1
		{
			$productItem = $result;
			$orderBaseProductItem->setProductCode( $productItem->getCode() );

			$subprices = $productItem->getRefItems( 'price', 'default', 'default' );

			if( count( $subprices ) > 0 ) {
				$prices = $subprices;
			}

			$orderProductAttrManager = \Aimeos\MShop\Factory::createManager( $this->getContext(), 'order/base/product/attribute' );
			$variantAttributes = $productItem->getRefItems( 'attribute', null, 'variant' );

			foreach( $this->getAttributes( array_keys( $variantAttributes ), array( 'text' ) ) as $attrItem )
			{
				$orderAttributeItem = $orderProductAttrManager->createItem();
				$orderAttributeItem->copyFrom( $attrItem );
				$orderAttributeItem->setType( 'variant' );

				$attr[] = $orderAttributeItem;
			}
		}
		else if( !isset( $options['variant'] ) || $options['variant'] != false ) // count == 0
		{
			$msg = sprintf( 'No article found for selected attributes and product ID "%1$s"', $productItem->getId() );
			throw new \Aimeos\Controller\Frontend\Basket\Exception( $msg );
		}

		return $attr;
	}
}
