<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Metaways Infosystems GmbH, 2012
 * @copyright Aimeos (aimeos.org), 2015-2023
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
	 * @param \Aimeos\MShop\Order\Item\Product\Iface $orderProduct Ordered product item
	 * @param \Aimeos\Map $prices List of price items implementing \Aimeos\MShop\Price\Item\Iface
	 * @param float $quantity New product quantity
	 * @return \Aimeos\MShop\Price\Item\Iface Price item with calculated price
	 */
	protected function calcPrice( \Aimeos\MShop\Order\Item\Product\Iface $orderProduct,
		\Aimeos\Map $prices, float $quantity ) : \Aimeos\MShop\Price\Item\Iface
	{
		$context = $this->context();
		$priceManager = \Aimeos\MShop::create( $context, 'price' );
		$price = $priceManager->getLowestPrice( $prices, $quantity, null, $orderProduct->getSiteId() );

		// customers can pay what they would like to pay
		if( ( $attr = $orderProduct->getAttributeItem( 'price', 'custom' ) ) !== null )
		{
			$amount = $attr->getValue();

			if( preg_match( '/^[0-9]*(\.[0-9]+)?$/', $amount ) !== 1 || ( (double) $amount ) < 0.01 )
			{
				$msg = $context->translate( 'controller/frontend', 'Invalid price value "%1$s"' );
				throw new \Aimeos\Controller\Frontend\Basket\Exception( sprintf( $msg, $amount ) );
			}

			$price = $price->setValue( $amount );
		}

		$orderAttributes = $orderProduct->getAttributeItems();
		$attrItems = $this->getAttributeItems( $orderAttributes );

		// add prices of (optional) attributes
		foreach( $orderAttributes as $orderAttrItem )
		{
			if( !( $attrItem = $attrItems->get( $orderAttrItem->getAttributeId() ) ) ) {
				continue;
			}

			$prices = $attrItem->getRefItems( 'price', 'default', 'default' );

			if( !$prices->isEmpty() )
			{
				$attrPrice = $priceManager->getLowestPrice( $prices, $orderAttrItem->getQuantity(), null, $orderProduct->getSiteId() );
				$price = $price->addItem( clone $attrPrice, $orderAttrItem->getQuantity() );
				$orderAttrItem->setPrice( $attrPrice->addItem( $attrPrice, $orderAttrItem->getQuantity() - 1 )->getValue() );
			}
		}

		// remove product rebate of original price in favor to rebates granted for the order
		return $price->setRebate( '0.00' );
	}


	/**
	 * Returns the allowed quantity for the given product
	 *
	 * @param \Aimeos\MShop\Product\Item\Iface $product Product item including referenced items
	 * @param float $quantity New product quantity
	 * @return float Updated quantity value
	 */
	protected function checkQuantity( \Aimeos\MShop\Product\Item\Iface $product, float $quantity ) : float
	{
		$scale = $product->getScale();

		if( fmod( $quantity, $scale ) >= 0.0005 ) {
			return round( ceil( $quantity / $scale ) * $scale, 4 );
		}

		return $quantity;
	}


	/**
	 * Checks if the attribute IDs are really associated to the product
	 *
	 * @param \Aimeos\MShop\Product\Item\Iface $product Product item with referenced items
	 * @param string $domain Domain the references must be of
	 * @param array $refMap Associative list of list type codes as keys and lists of reference IDs as values
	 * @throws \Aimeos\Controller\Frontend\Basket\Exception If one or more of the IDs are not associated
	 */
	protected function checkAttributes( array $products, string $listType, array $refIds )
	{
		$attrIds = map();

		foreach( $products as $product ) {
			$attrIds->merge( $product->getRefItems( 'attribute', null, $listType )->keys() );
		}

		if( $attrIds->intersect( $refIds )->count() !== count( $refIds ) )
		{
			$i18n = $this->context()->i18n();
			$prodIds = map( $products )->getId()->join( ', ' );
			$msg = $i18n->dt( 'controller/frontend', 'Invalid "%1$s" references for product with ID %2$s' );
			throw new \Aimeos\Controller\Frontend\Basket\Exception( sprintf( $msg, 'attribute', $prodIds ) );
		}
	}


	/**
	 * Checks for a locale mismatch and migrates the products to the new basket if necessary.
	 *
	 * @param \Aimeos\MShop\Locale\Item\Iface $locale Locale object from current basket
	 * @param string $type Basket type
	 */
	protected function checkLocale( \Aimeos\MShop\Locale\Item\Iface $locale, string $type )
	{
		$errors = [];
		$context = $this->context();
		$session = $context->session();

		$localeStr = $session->get( 'aimeos/basket/locale' );
		$localeKey = $locale->getSiteItem()->getCode() . '|' . $locale->getLanguageId() . '|' . $locale->getCurrencyId();

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

			$manager = \Aimeos\MShop::create( $context, 'order' );
			$basket = $manager->getSession( $type )->off();

			$this->copyAddresses( $basket, $errors, $localeKey );
			$this->copyServices( $basket, $errors );
			$this->copyProducts( $basket, $errors, $localeKey );
			$this->copyCoupons( $basket, $errors, $localeKey );

			$this->object()->get()->setCustomerId( $basket->getCustomerId() )
				->setCustomerReference( $basket->getCustomerReference() )
				->setComment( $basket->getComment() )
				->setLocale( $locale );

			$manager->setSession( $basket, $type );
		}

		$session->set( 'aimeos/basket/locale', $localeKey );
	}


	/**
	 * Migrates the addresses from the old basket to the current one.
	 *
	 * @param \Aimeos\MShop\Order\Item\Iface $basket Basket object
	 * @param array $errors Associative list of previous errors
	 * @param string $localeKey Unique identifier of the site, language and currency
	 * @return array Associative list of errors occured
	 */
	protected function copyAddresses( \Aimeos\MShop\Order\Item\Iface $basket, array $errors, string $localeKey ) : array
	{
		foreach( $basket->getAddresses() as $type => $items )
		{
			foreach( $items as $pos => $item )
			{
				try
				{
					$this->object()->get()->addAddress( $item, $type, $pos );
				}
				catch( \Exception $e )
				{
					$logger = $this->context()->logger();
					$errors['address'][$type] = $e->getMessage();

					$str = 'Error migrating address with type "%1$s" in basket to locale "%2$s": %3$s';
					$logger->info( sprintf( $str, $type, $localeKey, $e->getMessage() ), 'controller/frontend' );
				}
			}

			$basket->deleteAddress( $type );
		}

		return $errors;
	}


	/**
	 * Migrates the coupons from the old basket to the current one.
	 *
	 * @param \Aimeos\MShop\Order\Item\Iface $basket Basket object
	 * @param array $errors Associative list of previous errors
	 * @param string $localeKey Unique identifier of the site, language and currency
	 * @return array Associative list of errors occured
	 */
	protected function copyCoupons( \Aimeos\MShop\Order\Item\Iface $basket, array $errors, string $localeKey ) : array
	{
		foreach( $basket->getCoupons() as $code => $list )
		{
			try
			{
				$this->object()->addCoupon( $code );
				$basket->deleteCoupon( $code );
			}
			catch( \Exception $e )
			{
				$logger = $this->context()->logger();
				$errors['coupon'][$code] = $e->getMessage();

				$str = 'Error migrating coupon with code "%1$s" in basket to locale "%2$s": %3$s';
				$logger->info( sprintf( $str, $code, $localeKey, $e->getMessage() ), 'controller/frontend' );
			}
		}

		return $errors;
	}


	/**
	 * Migrates the products from the old basket to the current one.
	 *
	 * @param \Aimeos\MShop\Order\Item\Iface $basket Basket object
	 * @param array $errors Associative list of previous errors
	 * @param string $localeKey Unique identifier of the site, language and currency
	 * @return array Associative list of errors occured
	 */
	protected function copyProducts( \Aimeos\MShop\Order\Item\Iface $basket, array $errors, string $localeKey ) : array
	{
		$context = $this->context();
		$manager = \Aimeos\MShop::create( $context, 'product' );
		$ruleManager = \Aimeos\MShop::create( $context, 'rule' );
		$domains = ['attribute', 'media', 'price', 'product', 'text'];

		foreach( $basket->getProducts() as $pos => $product )
		{
			if( $product->getFlags() & \Aimeos\MShop\Order\Item\Product\Base::FLAG_IMMUTABLE ) {
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

				$item = $manager->get( $product->getProductId(), $domains );
				$item = $ruleManager->apply( $item, 'catalog' );
				$qty = $product->getQuantity();

				$this->object()->addProduct( $item, $qty, $variantIds, $configIds, $customIds, $product->getStockType() );

				$basket->deleteProduct( $pos );
			}
			catch( \Exception $e )
			{
				$code = $product->getProductCode();
				$logger = $this->context()->logger();
				$errors['product'][$pos] = $e->getMessage();

				$str = 'Error migrating product with code "%1$s" in basket to locale "%2$s": %3$s';
				$logger->info( sprintf( $str, $code, $localeKey, $e->getMessage() ), 'controller/frontend' );
			}
		}

		return $errors;
	}


	/**
	 * Migrates the services from the old basket to the current one.
	 *
	 * @param \Aimeos\MShop\Order\Item\Iface $basket Basket object
	 * @param array $errors Associative list of previous errors
	 * @return array Associative list of errors occured
	 */
	protected function copyServices( \Aimeos\MShop\Order\Item\Iface $basket, array $errors ) : array
	{
		$newBasket = $this->object();
		$manager = \Aimeos\MShop::create( $this->context(), 'service' );

		foreach( $basket->getServices() as $type => $list )
		{
			foreach( $list as $item )
			{
				try
				{
					foreach( $newBasket->get()->getService( $type ) as $pos => $ordService )
					{
						if( $item->getCode() === $ordService->getCode() ) {
							$newBasket->get()->deleteService( $type, $pos );
						}
					}

					$attributes = [];

					foreach( $item->getAttributeItems() as $attrItem ) {
						$attributes[$attrItem->getCode()] = $attrItem->getValue();
					}

					$service = $manager->get( $item->getServiceId(), ['media', 'price', 'text'] );
					$newBasket->addService( $service, $attributes );
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
	 * @param \Aimeos\MShop\Order\Item\Iface $order Basket object
	 */
	protected function createSubscriptions( \Aimeos\MShop\Order\Item\Iface $order )
	{
		$types = ['config', 'custom', 'hidden', 'variant'];
		$manager = \Aimeos\MShop::create( $this->context(), 'subscription' );

		foreach( $order->getProducts() as $orderProduct )
		{
			if( ( $interval = $orderProduct->getAttribute( 'interval', $types ) ) !== null )
			{
				$interval = is_array( $interval ) ? reset( $interval ) : $interval;

				$item = $manager->create()->setInterval( $interval )
					->setProductId( $orderProduct->getProductId() )
					->setOrderProductId( $orderProduct->getId() )
					->setOrderId( $order->getId() );

				if( ( $end = $orderProduct->getAttribute( 'intervalend', $types ) ) !== null ) {
					$item = $item->setDateEnd( $end );
				}

				$manager->save( $item, false );
			}
		}
	}


	/**
	 * Returns the attribute items for the given attribute IDs.
	 *
	 * @param array $attributeIds List of attribute IDs
	 * @param string[] $domains Names of the domain items that should be fetched too
	 * @return \Aimeos\Map List of items implementing \Aimeos\MShop\Attribute\Item\Iface
	 * @throws \Aimeos\Controller\Frontend\Basket\Exception If the actual attribute number doesn't match the expected one
	 */
	protected function getAttributes( array $attributeIds, array $domains = ['text'] ) : \Aimeos\Map
	{
		if( empty( $attributeIds ) ) {
			return map();
		}

		$attributeManager = \Aimeos\MShop::create( $this->context(), 'attribute' );

		$search = $attributeManager->filter( true )
			->add( ['attribute.id' => $attributeIds] )
			->slice( 0, count( $attributeIds ) );

		$attrItems = $attributeManager->search( $search, $domains );

		if( $attrItems->count() !== count( $attributeIds ) )
		{
			$i18n = $this->context()->i18n();
			$expected = implode( ',', $attributeIds );
			$actual = $attrItems->keys()->join( ',' );
			$msg = $i18n->dt( 'controller/frontend', 'Available attribute IDs "%1$s" do not match the given attribute IDs "%2$s"' );

			throw new \Aimeos\Controller\Frontend\Basket\Exception( sprintf( $msg, $actual, $expected ) );
		}

		return $attrItems;
	}


	/**
	 * Returns the attribute items using the given order attribute items.
	 *
	 * @param \Aimeos\Map $orderAttributes List of items implementing \Aimeos\MShop\Order\Item\Product\Attribute\Iface
	 * @return \Aimeos\Map List of attribute IDs as key and attribute items implementing \Aimeos\MShop\Attribute\Item\Iface
	 */
	protected function getAttributeItems( \Aimeos\Map $orderAttributes ) : \Aimeos\Map
	{
		if( $orderAttributes->isEmpty() ) {
			return map();
		}

		$attributeManager = \Aimeos\MShop::create( $this->context(), 'attribute' );
		$search = $attributeManager->filter( true );
		$expr = [];

		foreach( $orderAttributes as $item )
		{
			if( is_scalar( $item->getValue() ) )
			{
				$tmp = array(
					$search->compare( '==', 'attribute.domain', 'product' ),
					$search->compare( '==', 'attribute.code', $item->getValue() ),
					$search->compare( '==', 'attribute.type', $item->getCode() ),
					$search->compare( '>', 'attribute.status', 0 ),
					$search->getConditions(),
				);
				$expr[] = $search->and( $tmp );
			}
		}

		$search->setConditions( $search->or( $expr ) );
		return $attributeManager->search( $search, array( 'price' ) );
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
	protected function getOrderProductAttributes( string $type, array $ids, array $values = [], array $quantities = [] ) : array
	{
		if( empty( $ids ) ) {
			return [];
		}

		$list = [];
		$context = $this->context();

		$priceManager = \Aimeos\MShop::create( $context, 'price' );
		$manager = \Aimeos\MShop::create( $context, 'order/product/attribute' );

		foreach( $this->getAttributes( $ids, ['price', 'text'] ) as $id => $attrItem )
		{
			$qty = $quantities[$id] ?? 1;
			$item = $manager->create()->copyFrom( $attrItem )->setType( $type )
				->setValue( $values[$id] ?? $attrItem->getCode() )
				->setQuantity( $qty );

			if( !( $prices = $attrItem->getRefItems( 'price', 'default', 'default' ) )->isEmpty() )
			{
				$attrPrice = $priceManager->getLowestPrice( $prices, $qty );
				$item->setPrice( $attrPrice->addItem( $attrPrice, $qty - 1 )->getValue() );
			}

			$list[] = $item;
		}

		return $list;
	}


	/**
	 * Returns the site ID of the ordered product
	 *
	 * @param \Aimeos\MShop\Product\Item\Iface $product Product item
	 * @param string|null $siteId Unique ID of the site the product should be bought from or NULL for site the product is from
	 * @return string Site ID for the ordered product
	 */
	protected function getSiteId( \Aimeos\MShop\Product\Item\Iface $product, string $siteId = null ) : string
	{
		if( $siteId ) {
			return $siteId;
		}

		// if product is inherited, use site ID of current site
		$siteIds = $this->context()->locale()->getSitePath();

		return  in_array( $product->getSiteId(), $siteIds ) ? end( $siteIds ) : $product->getSiteId();
	}
}
