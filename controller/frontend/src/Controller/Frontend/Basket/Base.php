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
 * Base class for the basket frontend controller
 *
 * @package Controller
 * @subpackage Frontend
 */
abstract class Base extends \Aimeos\Controller\Frontend\Base implements Iface
{
	private $listTypeAttributes = [];


	/**
	 * Calculates and returns the current price for the given order product and product prices.
	 *
	 * @param \Aimeos\MShop\Order\Item\Base\Product\Iface $product Ordered product item
	 * @param \Aimeos\MShop\Price\Item\Iface[] $prices List of price items
	 * @param integer $quantity New product quantity
	 * @return \Aimeos\MShop\Price\Item\Iface Price item with calculated price
	 */
	protected function calcPrice( \Aimeos\MShop\Order\Item\Base\Product\Iface $product, array $prices, $quantity )
	{
		$context = $this->getContext();

		if( empty( $prices ) )
		{
			$manager = \Aimeos\MShop\Factory::createManager( $this->getContext(), 'product' );
			$prices = $manager->getItem( $product->getProductId(), array( 'price' ) )->getRefItems( 'price', 'default' );
		}

		$priceManager = \Aimeos\MShop\Factory::createManager( $context, 'price' );
		$price = $priceManager->getLowestPrice( $prices, $quantity );

		foreach( $this->getAttributeItems( $product->getAttributes() ) as $attrItem )
		{
			$prices = $attrItem->getRefItems( 'price', 'default' );

			if( count( $prices ) > 0 )
			{
				$attrPrice = $priceManager->getLowestPrice( $prices, $quantity );
				$price->addItem( $attrPrice );
			}
		}

		// remove product rebate of original price in favor to rebates granted for the order
		$price->setRebate( '0.00' );

		return $price;
	}


	/**
	 * Checks if the IDs of the given items are really associated to the product.
	 *
	 * @param string|array $prodId Unique ID of the product or list of product IDs
	 * @param string $domain Domain the references must be of
	 * @param integer $listTypeId ID of the list type the referenced items must be
	 * @param array $refIds List of IDs that must be associated to the product
	 * @throws \Aimeos\Controller\Frontend\Basket\Exception If one or more of the IDs are not associated
	 */
	protected function checkReferences( $prodId, $domain, $listTypeId, array $refIds )
	{
		$productManager = \Aimeos\MShop\Factory::createManager( $this->getContext(), 'product' );
		$search = $productManager->createSearch( true );

		$expr = array(
			$search->compare( '==', 'product.id', $prodId ),
			$search->getConditions(),
		);

		if( count( $refIds ) > 0 )
		{
			foreach( $refIds as $key => $refId ) {
				$refIds[$key] = (string) $refId;
			}

			$param = array( $domain, $listTypeId, $refIds );
			$cmpfunc = $search->createFunction( 'product.contains', $param );

			$expr[] = $search->compare( '==', $cmpfunc, count( $refIds ) );
		}

		$search->setConditions( $search->combine( '&&', $expr ) );

		if( count( $productManager->searchItems( $search, [] ) ) === 0 )
		{
			$msg = sprintf( 'Invalid "%1$s" references for product with ID %2$s', $domain, json_encode( $prodId ) );
			throw new \Aimeos\Controller\Frontend\Basket\Exception( $msg );
		}
	}


	/**
	 * Checks for a locale mismatch and migrates the products to the new basket if necessary.
	 *
	 * @param string $type Basket type
	 */
	protected function checkLocale( $type )
	{
		$errors = [];
		$context = $this->getContext();
		$session = $context->getSession();
		$locale = $this->get()->getLocale();

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
			$basket = $manager->getSession( $type );

			$this->copyAddresses( $basket, $errors, $localeKey );
			$this->copyServices( $basket, $errors );
			$this->copyProducts( $basket, $errors, $localeKey );
			$this->copyCoupons( $basket, $errors, $localeKey );

			$manager->setSession( $basket, $type );
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
				$attrIds = [];

				foreach( $product->getAttributes() as $attrItem ) {
					$attrIds[$attrItem->getType()][] = $attrItem->getAttributeId();
				}

				$this->addProduct(
					$product->getProductId(),
					$product->getQuantity(),
					[],
					$this->getValue( $attrIds, 'variant', [] ),
					$this->getValue( $attrIds, 'config', [] ),
					$this->getValue( $attrIds, 'hidden', [] ),
					$this->getValue( $attrIds, 'custom', [] ),
					$product->getStockType()
				);

				$basket->deleteProduct( $pos );
			}
			catch( \Exception $e )
			{
				$code = $product->getProductCode();
				$logger = $this->getContext()->getLogger();
				$errors['product'][$pos] = $e->getMessage();

				$str = 'Error migrating product with code "%1$s" in basket to locale "%2$s": %3$s';
				$logger->log( sprintf( $str, $code, $localeKey, $e->getMessage() ), \Aimeos\MW\Logger\Base::INFO );
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
				$attributes = [];

				foreach( $item->getAttributes() as $attrItem ) {
					$attributes[$attrItem->getCode()] = $attrItem->getValue();
				}

				$this->setService( $type, $item->getServiceId(), $attributes );
				$basket->deleteService( $type );
			}
			catch( \Exception $e ) { ; } // Don't notify the user as appropriate services can be added automatically
		}

		return $errors;
	}


	/**
	 * Creates the order product attribute items from the given attribute IDs and updates the price item if necessary.
	 *
	 * @param \Aimeos\MShop\Price\Item\Iface $price Price item of the ordered product
	 * @param string|array $prodid Unique product ID or list of product IDs where the given attributes must be attached to
	 * @param integer $quantity Number of products that should be added to the basket
	 * @param array $attributeIds List of attributes IDs of the given type
	 * @param string $type Attribute type
	 * @param array $attributeValues Associative list of attribute IDs as keys and their codes as values
	 * @return array List of items implementing \Aimeos\MShop\Order\Item\Product\Attribute\Iface
	 */
	protected function createOrderProductAttributes( \Aimeos\MShop\Price\Item\Iface $price, $prodid, $quantity,
			array $attributeIds, $type, array $attributeValues = [] )
	{
		if( empty( $attributeIds ) ) {
			return [];
		}

		$attrTypeId = $this->getProductListTypeItem( 'attribute', $type )->getId();
		$this->checkReferences( $prodid, 'attribute', $attrTypeId, $attributeIds );

		$list = [];
		$context = $this->getContext();

		$priceManager = \Aimeos\MShop\Factory::createManager( $context, 'price' );
		$orderProductAttributeManager = \Aimeos\MShop\Factory::createManager( $context, 'order/base/product/attribute' );

		foreach( $this->getAttributes( $attributeIds ) as $id => $attrItem )
		{
			$prices = $attrItem->getRefItems( 'price', 'default', 'default' );

			if( !empty( $prices ) ) {
				$price->addItem( $priceManager->getLowestPrice( $prices, $quantity ) );
			}

			$item = $orderProductAttributeManager->createItem();
			$item->copyFrom( $attrItem );
			$item->setType( $type );

			if( isset( $attributeValues[$id] ) ) {
				$item->setValue( $attributeValues[$id] );
			}

			$list[] = $item;
		}

		return $list;
	}


	/**
	 * Returns the attribute items for the given attribute IDs.
	 *
	 * @param array $attributeIds List of attribute IDs
	 * @param string[] $domains Names of the domain items that should be fetched too
	 * @return array List of items implementing \Aimeos\MShop\Attribute\Item\Iface
	 * @throws \Aimeos\Controller\Frontend\Basket\Exception If the actual attribute number doesn't match the expected one
	 */
	protected function getAttributes( array $attributeIds, array $domains = array( 'price', 'text' ) )
	{
		if( empty( $attributeIds ) ) {
			return [];
		}

		$attributeManager = \Aimeos\MShop\Factory::createManager( $this->getContext(), 'attribute' );

		$search = $attributeManager->createSearch( true );
		$expr = array(
				$search->compare( '==', 'attribute.id', $attributeIds ),
				$search->getConditions(),
		);
		$search->setConditions( $search->combine( '&&', $expr ) );
		$search->setSlice( 0, 0x7fffffff );

		$attrItems = $attributeManager->searchItems( $search, $domains );

		if( count( $attrItems ) !== count( $attributeIds ) )
		{
			$expected = implode( ',', $attributeIds );
			$actual = implode( ',', array_keys( $attrItems ) );
			$msg = sprintf( 'Available attribute IDs "%1$s" do not match the given attribute IDs "%2$s"', $actual, $expected );

			throw new \Aimeos\Controller\Frontend\Basket\Exception( $msg );
		}

		return $attrItems;
	}


	/**
	 * Returns the attribute items using the given order attribute items.
	 *
	 * @param \Aimeos\MShop\Order\Item\Base\Product\Attribute\Item[] $orderAttributes List of order product attribute items
	 * @return \Aimeos\MShop\Attribute\Item\Iface[] Associative list of attribute IDs as key and attribute items as values
	 */
	protected function getAttributeItems( array $orderAttributes )
	{
		if( empty( $orderAttributes ) ) {
			return [];
		}

		$attributeManager = \Aimeos\MShop\Factory::createManager( $this->getContext(), 'attribute' );
		$search = $attributeManager->createSearch( true );
		$expr = [];

		foreach( $orderAttributes as $item )
		{
			$tmp = array(
				$search->compare( '==', 'attribute.domain', 'product' ),
				$search->compare( '==', 'attribute.code', $item->getValue() ),
				$search->compare( '==', 'attribute.type.domain', 'product' ),
				$search->compare( '==', 'attribute.type.code', $item->getCode() ),
				$search->compare( '>', 'attribute.type.status', 0 ),
				$search->getConditions(),
			);
			$expr[] = $search->combine( '&&', $tmp );
		}

		$search->setConditions( $search->combine( '||', $expr ) );
		return $attributeManager->searchItems( $search, array( 'price' ) );
	}


	/**
	 * Retrieves the domain item specified by the given key and value.
	 *
	 * @param string $domain Product manager search key
	 * @param string $key Domain manager search key
	 * @param string $value Unique domain identifier
	 * @param string[] $ref List of referenced items that should be fetched too
	 * @return \Aimeos\MShop\Common\Item\Iface Domain item object
	 * @throws \Aimeos\Controller\Frontend\Basket\Exception
	 */
	protected function getDomainItem( $domain, $key, $value, array $ref )
	{
		$manager = \Aimeos\MShop\Factory::createManager( $this->getContext(), $domain );

		$search = $manager->createSearch( true );
		$expr = array(
			$search->compare( '==', $key, $value ),
			$search->getConditions(),
		);
		$search->setConditions( $search->combine( '&&', $expr ) );

		$result = $manager->searchItems( $search, $ref );

		if( ( $item = reset( $result ) ) === false )
		{
			$msg = sprintf( 'No item for "%1$s" (%2$s) found', $value, $key );
			throw new \Aimeos\Controller\Frontend\Basket\Exception( $msg );
		}

		return $item;
	}


	/**
	 * Returns the list type item for the given domain and code.
	 *
	 * @param string $domain Domain name of the list type
	 * @param string $code Code of the list type
	 * @return \Aimeos\MShop\Common\Item\Type\Iface List type item
	 */
	protected function getProductListTypeItem( $domain, $code )
	{
		if( !isset( $this->listTypeAttributes[$domain][$code] ) )
		{
			$listTypeManager = \Aimeos\MShop\Factory::createManager( $this->getContext(), 'product/lists/type' );

			$listTypeSearch = $listTypeManager->createSearch( true );
			$expr = array(
				$listTypeSearch->compare( '==', 'product.lists.type.domain', $domain ),
				$listTypeSearch->compare( '==', 'product.lists.type.code', $code ),
				$listTypeSearch->getConditions(),
			);
			$listTypeSearch->setConditions( $listTypeSearch->combine( '&&', $expr ) );

			$listTypeItems = $listTypeManager->searchItems( $listTypeSearch );

			if( ( $listTypeItem = reset( $listTypeItems ) ) === false )
			{
				$msg = sprintf( 'List type for domain "%1$s" and code "%2$s" not found', $domain, $code );
				throw new \Aimeos\Controller\Frontend\Basket\Exception( $msg );
			}

			$this->listTypeAttributes[$domain][$code] = $listTypeItem;
		}

		return $this->listTypeAttributes[$domain][$code];
	}


	/**
	 * Returns the product variants of a selection product that match the given attributes.
	 *
	 * @param \Aimeos\MShop\Product\Item\Iface $productItem Product item including sub-products
	 * @param array $variantAttributeIds IDs for the variant-building attributes
	 * @param array $domains Names of the domain items that should be fetched too
	 * @return array List of products matching the given attributes
	 */
	protected function getProductVariants( \Aimeos\MShop\Product\Item\Iface $productItem, array $variantAttributeIds,
			array $domains = array( 'attribute', 'media', 'price', 'text' ) )
	{
		$subProductIds = [];
		foreach( $productItem->getRefItems( 'product', 'default', 'default' ) as $item ) {
			$subProductIds[] = $item->getId();
		}

		if( count( $subProductIds ) === 0 ) {
			return [];
		}

		$productManager = \Aimeos\MShop\Factory::createManager( $this->getContext(), 'product' );
		$search = $productManager->createSearch( true );

		$expr = array(
			$search->compare( '==', 'product.id', $subProductIds ),
			$search->getConditions(),
		);

		if( count( $variantAttributeIds ) > 0 )
		{
			foreach( $variantAttributeIds as $key => $id ) {
				$variantAttributeIds[$key] = (string) $id;
			}

			$listTypeItem = $this->getProductListTypeItem( 'attribute', 'variant' );

			$param = array( 'attribute', $listTypeItem->getId(), $variantAttributeIds );
			$cmpfunc = $search->createFunction( 'product.contains', $param );

			$expr[] = $search->compare( '==', $cmpfunc, count( $variantAttributeIds ) );
		}

		$search->setConditions( $search->combine( '&&', $expr ) );

		return $productManager->searchItems( $search, $domains );
	}


	/**
	 * Returns the value of an array or the default value if it's not available.
	 *
	 * @param array $values Associative list of key/value pairs
	 * @param string $name Name of the key to return the value for
	 * @param mixed $default Default value if no value is available for the given name
	 * @return mixed Value from the array or default value
	 */
	protected function getValue( array $values, $name, $default = null )
	{
		if( isset( $values[$name] ) ) {
			return $values[$name];
		}

		return $default;
	}
}
