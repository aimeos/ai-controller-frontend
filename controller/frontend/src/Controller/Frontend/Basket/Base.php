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
			$item = \Aimeos\MShop::create( $context, 'product' )->getItem( $product->getProductId(), ['price'] );
			$prices = $item->getRefItems( 'price', 'default', 'default' );
		}


		$priceManager = \Aimeos\MShop::create( $context, 'price' );
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

			$price = $price->setValue( $amount );
		}

		$orderAttributes = $product->getAttributeItems();
		$attrItems = $this->getAttributeItems( $orderAttributes );

		// add prices of (optional) attributes
		foreach( $orderAttributes as $orderAttrItem )
		{
			$attrId = $orderAttrItem->getAttributeId();

			if( isset( $attrItems[$attrId] )
				&& ( $prices = $attrItems[$attrId]->getRefItems( 'price', 'default', 'default' ) ) !== []
			) {
				$attrPrice = $priceManager->getLowestPrice( $prices, $orderAttrItem->getQuantity() );
				$price = $price->addItem( $attrPrice, $orderAttrItem->getQuantity() );
			}
		}

		// remove product rebate of original price in favor to rebates granted for the order
		return $price->setRebate( '0.00' );
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
		$productManager = \Aimeos\MShop::create( $context, 'product' );
		$search = $productManager->createSearch( true );

		$expr = [$search->getConditions()];
		$expr[] = $search->compare( '==', 'product.id', $prodId );

		foreach( $refMap as $listType => $refIds )
		{
			if( !empty( $refIds ) )
			{
				$cmpfunc = $search->createFunction( 'product:has', [$domain, $listType, $refIds] );
				$expr[2] = $search->compare( '!=', $cmpfunc, null );

				$search->setConditions( $search->combine( '&&', $expr ) );

				if( count( $productManager->searchItems( $search, [] ) ) === 0 )
				{
					$msg = $context->getI18n()->dt( 'controller/frontend', 'Invalid "%1$s" references for product with ID %2$s' );
					throw new \Aimeos\Controller\Frontend\Basket\Exception( sprintf( $msg, $domain, json_encode( $prodId ) ) );
				}
			}
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

			$localeManager = \Aimeos\MShop::create( $context, 'locale' );
			$locale = $localeManager->bootstrap( $locSite, $locLanguage, $locCurrency, false );

			$context = clone $context;
			$context->setLocale( $locale );

			$manager = \Aimeos\MShop\Order\Manager\Factory::create( $context )->getSubManager( 'base' );
			$basket = $manager->getSession( $type )->off();

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
		foreach( $basket->getAddresses() as $type => $items )
		{
			foreach( $items as $pos => $item )
			{
				try
				{
					$this->getObject()->get()->addAddress( $item, $type, $pos );
				}
				catch( \Exception $e )
				{
					$logger = $this->getContext()->getLogger();
					$errors['address'][$type] = $e->getMessage();

					$str = 'Error migrating address with type "%1$s" in basket to locale "%2$s": %3$s';
					$logger->log( sprintf( $str, $type, $localeKey, $e->getMessage() ), \Aimeos\MW\Logger\Base::INFO );
				}
			}

			$basket->deleteAddress( $type );
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
				$this->getObject()->addCoupon( $code );
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
		$domains = ['attribute', 'media', 'price', 'product', 'text'];
		$manager = \Aimeos\MShop::create( $this->getContext(), 'product' );

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

				$item = $manager->getItem( $product->getProductId(), $domains );

				$this->getObject()->addProduct(
					$item, $product->getQuantity(), $variantIds, $configIds, $customIds,
					$product->getStockType(), $product->getSupplierCode()
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
		$manager = \Aimeos\MShop::create( $this->getContext(), 'service' );

		foreach( $basket->getServices() as $type => $list )
		{
			foreach( $list as $item )
			{
				try
				{
					$attributes = [];

					foreach( $item->getAttributeItems() as $attrItem ) {
						$attributes[$attrItem->getCode()] = $attrItem->getValue();
					}

					$service = $manager->getItem( $item->getServiceId(), ['media', 'price', 'text'] );
					$this->getObject()->addService( $service, $attributes );
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
		$types = ['config', 'custom', 'hidden', 'variant'];
		$manager = \Aimeos\MShop::create( $this->getContext(), 'subscription' );

		foreach( $basket->getProducts() as $orderProduct )
		{
			if( ( $interval = $orderProduct->getAttribute( 'interval', $types ) ) !== null )
			{
				$interval = is_array( $interval ) ? reset( $interval ) : $interval;

				$item = $manager->createItem()->setInterval( $interval )
					->setProductId( $orderProduct->getProductId() )
					->setOrderProductId( $orderProduct->getId() )
					->setOrderBaseId( $basket->getId() );

				if( ( $end = $orderProduct->getAttribute( 'intervalend', $types ) ) !== null ) {
					$item = $item->setDateEnd( $end );
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
	protected function getAttributes( array $attributeIds, array $domains = ['text'] )
	{
		if( empty( $attributeIds ) ) {
			return [];
		}

		$attributeManager = \Aimeos\MShop::create( $this->getContext(), 'attribute' );

		$search = $attributeManager->createSearch( true );
		$expr = array(
			$search->compare( '==', 'attribute.id', $attributeIds ),
			$search->getConditions(),
		);
		$search->setConditions( $search->combine( '&&', $expr ) );
		$search->setSlice( 0, count( $attributeIds ) );

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

		$attributeManager = \Aimeos\MShop::create( $this->getContext(), 'attribute' );
		$search = $attributeManager->createSearch( true );
		$expr = [];

		foreach( $orderAttributes as $item )
		{
			$tmp = array(
				$search->compare( '==', 'attribute.domain', 'product' ),
				$search->compare( '==', 'attribute.code', $item->getValue() ),
				$search->compare( '==', 'attribute.type', $item->getCode() ),
				$search->compare( '>', 'attribute.status', 0 ),
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
		$list = [];

		if( !empty( $ids ) )
		{
			$manager = \Aimeos\MShop::create( $this->getContext(), 'order/base/product/attribute' );

			foreach( $this->getAttributes( $ids ) as $id => $attrItem )
			{
				$list[] = $manager->createItem()->copyFrom( $attrItem )->setType( $type )
					->setValue( isset( $values[$id] ) ? $values[$id] : $attrItem->getCode() )
					->setQuantity( isset( $quantities[$id] ) ? $quantities[$id] : 1 );
			}
		}

		return $list;
	}
}
