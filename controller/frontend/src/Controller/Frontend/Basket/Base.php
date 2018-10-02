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
 * Base class for the basket frontend controller
 *
 * @package Controller
 * @subpackage Frontend
 */
abstract class Base extends \Aimeos\Controller\Frontend\Base implements Iface
{
	private $listTypeItems = [];


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
			$manager = \Aimeos\MShop\Factory::createManager( $context, 'product' );
			$prices = $manager->getItem( $product->getProductId(), array( 'price' ) )->getRefItems( 'price', 'default' );
		}


		$priceManager = \Aimeos\MShop\Factory::createManager( $context, 'price' );
		$price = $priceManager->getLowestPrice( $prices, $quantity );

		// customers can pay what they would like to pay
		if( ( $attr = $product->getAttributeItem( 'price', 'custom' ) ) !== null )
		{
			$amount = $attr->getValue();

			if( preg_match( '/^[0-9]*(\.[0-9]+)?$/', $amount ) !== 1 || ((double) $amount) < 0.01 )
			{
				$msg = $context->getI18n()->dt( 'controller/frontend', 'Invalid price value "%1$s"' );
				throw new \Aimeos\Controller\Frontend\Basket\Exception( sprintf( $msg, $amount ) );
			}

			$price->setValue( $amount );
		}

		$orderAttributes = $product->getAttributes();
		$attrItems = $this->getAttributeItems( $orderAttributes );

		// add prices of (optional) attributes
		foreach( $orderAttributes as $orderAttrItem )
		{
			$attrId = $orderAttrItem->getAttributeId();

			if( isset( $attrItems[$attrId] )
				&& ( $prices = $attrItems[$attrId]->getRefItems( 'price', 'default' ) ) !== []
			) {
				$attrPrice = $priceManager->getLowestPrice( $prices, $orderAttrItem->getQuantity() );
				$price->addItem( $attrPrice, $orderAttrItem->getQuantity() );
			}
		}

		// remove product rebate of original price in favor to rebates granted for the order
		$price->setRebate( '0.00' );

		return $price;
	}


	/**
	 * Checks if the reference IDs are really associated to the product
	 *
	 * @param string|array $prodId Unique ID of the product or list of product IDs
	 * @param string $domain Domain the references must be of
	 * @param array $refMap Associative list of list type codes as keys and lists of reference IDs as values
	 * @throws \Aimeos\Controller\Frontend\Basket\Exception If one or more of the IDs are not associated
	 */
	protected function checkListRef( $prodId, $domain, array $refMap )
	{
		if( empty( $refMap ) ) {
			return;
		}

		$context = $this->getContext();
		$productManager = \Aimeos\MShop\Factory::createManager( $context, 'product' );
		$search = $productManager->createSearch( true );

		$expr = array(
			$search->compare( '==', 'product.id', $prodId ),
			$search->getConditions(),
		);

		foreach( $refMap as $listType => $refIds )
		{
			foreach( $refIds as $key => $refId )
			{
				$cmpfunc = $search->createFunction( 'product:has', [$domain, $listType, (string) $refId] );
				$expr[] = $search->compare( '!=', $cmpfunc, null );
			}
		}

		$search->setConditions( $search->combine( '&&', $expr ) );

		if( count( $productManager->searchItems( $search, [] ) ) === 0 )
		{
			$msg = $context->getI18n()->dt( 'controller/frontend', 'Invalid "%1$s" references for product with ID %2$s' );
			throw new \Aimeos\Controller\Frontend\Basket\Exception( sprintf( $msg, $domain, json_encode( $prodId ) ) );
		}
	}


	/**
	 * Checks for a locale mismatch and migrates the products to the new basket if necessary.
	 *
	 * @param \Aimeos\MShop\Locale\Item\Iface $locale Locale object from current basket
	 * @param string $type Basket type
	 */
	protected function checkLocale( \Aimeos\MShop\Locale\Item\Iface $locale, $type )
	{
		$errors = [];
		$context = $this->getContext();
		$session = $context->getSession();

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

			$manager = \Aimeos\MShop\Factory::createManager( $context, 'order/base' );
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
				$errors['address'][$type] = $e->getMessage();

				$str = 'Error migrating address with type "%1$s" in basket to locale "%2$s": %3$s';
				$logger->log( sprintf( $str, $type, $localeKey, $e->getMessage() ), \Aimeos\MW\Logger\Base::INFO );
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
				$errors['coupon'][$code] = $e->getMessage();

				$str = 'Error migrating coupon with code "%1$s" in basket to locale "%2$s": %3$s';
				$logger->log( sprintf( $str, $code, $localeKey, $e->getMessage() ), \Aimeos\MW\Logger\Base::INFO );
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
				$variantIds = $configIds = $customIds = [];

				foreach( $product->getAttributeItems() as $attrItem )
				{
					switch( $attrItem->getType() )
					{
						case 'variant': $variantIds[] = $attrItem->getAttributeId(); break;
						case 'config': $configIds[$attrItem->getAttributeId()] = $attrItem->getQuantity(); break;
						case 'custom': $customIds[$attrItem->getAttributeId()] = $attrItem->getValue(); break;
					}
				}

				$this->addProduct(
					$product->getProductId(), $product->getQuantity(), $product->getStockType(),
					$variantIds, $configIds, [], $customIds
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
		foreach( $basket->getServices() as $type => $list )
		{
			foreach( $list as $item )
			{
				try
				{
					$attributes = [];

					foreach( $item->getAttributes() as $attrItem ) {
						$attributes[$attrItem->getCode()] = $attrItem->getValue();
					}

					$this->addService( $type, $item->getServiceId(), $attributes );
					$basket->deleteService( $type );
				}
				catch( \Exception $e ) { ; } // Don't notify the user as appropriate services can be added automatically
			}
		}

		return $errors;
	}


	/**
	 * Creates the subscription entries for the ordered products with interval attributes
	 *
	 * @param \Aimeos\MShop\Order\Item\Base\Iface $basket Basket object
	 */
	protected function createSubscriptions( \Aimeos\MShop\Order\Item\Base\Iface $basket )
	{
		$manager = \Aimeos\MShop\Factory::createManager( $this->getContext(), 'subscription' );

		foreach( $basket->getProducts() as $orderProduct )
		{
			if( ( $interval = $orderProduct->getAttribute( 'interval', 'config' ) ) !== null )
			{
				$item = $manager->createItem();
				$item->setOrderBaseId( $basket->getId() );
				$item->setOrderProductId( $orderProduct->getId() );
				$item->setInterval( $interval );
				$item->setStatus( 1 );

				if( ( $end = $orderProduct->getAttribute( 'intervalend', 'custom' ) ) !== null
					|| ( $end = $orderProduct->getAttribute( 'intervalend', 'config' ) ) !== null
					|| ( $end = $orderProduct->getAttribute( 'intervalend', 'hidden' ) ) !== null
				) {
					$item->setDateEnd( $end );
				}

				$manager->saveItem( $item, false );
			}
		}
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
			$i18n = $this->getContext()->getI18n();
			$expected = implode( ',', $attributeIds );
			$actual = implode( ',', array_keys( $attrItems ) );
			$msg = $i18n->dt( 'controller/frontend', 'Available attribute IDs "%1$s" do not match the given attribute IDs "%2$s"' );

			throw new \Aimeos\Controller\Frontend\Basket\Exception( sprintf( $msg, $actual, $expected ) );
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
	 * Returns the order product attribute items for the given IDs and values
	 *
	 * @param string $type Attribute type code
	 * @param array $ids List of attributes IDs of the given type
	 * @param array $values Associative list of attribute IDs as keys and their codes as values
	 * @param array $quantities Associative list of attribute IDs as keys and their quantities as values
	 * @return array List of items implementing \Aimeos\MShop\Order\Item\Product\Attribute\Iface
	 */
	protected function getOrderProductAttributes( $type, array $ids, array $values = [], array $quantities = [] )
	{
		if( empty( $ids ) ) {
			return [];
		}

		$list = [];
		$manager = \Aimeos\MShop\Factory::createManager( $this->getContext(), 'order/base/product/attribute' );

		foreach( $this->getAttributes( $ids ) as $id => $attrItem )
		{
			$item = $manager->createItem();
			$item->copyFrom( $attrItem );
			$item->setType( $type );
			$item->setQuantity( isset( $quantities[$id] ) ? $quantities[$id] : 1 );

			if( isset( $values[$id] ) ) {
				$item->setValue( $values[$id] );
			}

			$list[] = $item;
		}

		return $list;
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
		$context = $this->getContext();

		if( empty( $this->listTypeItems ) )
		{
			$manager = \Aimeos\MShop\Factory::createManager( $context, 'product/lists/type' );

			foreach( $manager->searchItems( $manager->createSearch( true ) ) as $item ) {
				$this->listTypeItems[ $item->getDomain() ][ $item->getCode() ] = $item;
			}
		}

		if( !isset( $this->listTypeItems[$domain][$code] ) )
		{
			$msg = $context->getI18n()->dt( 'controller/frontend', 'List type for domain "%1$s" and code "%2$s" not found' );
			throw new \Aimeos\Controller\Frontend\Basket\Exception( sprintf( $msg, $domain, $code ) );
		}

		return $this->listTypeItems[$domain][$code];
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

		foreach( $variantAttributeIds as $id )
		{
			$cmpfunc = $search->createFunction( 'product:has', ['attribute', 'variant', (string) $id] );
			$expr[] = $search->compare( '!=', $cmpfunc, null );
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
