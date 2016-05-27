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
	private $listTypeAttributes = array();


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
			$parentItem = $this->getDomainItem( 'product', 'product.id', $product->getProductId(), array( 'price' ) );
			$prices = $parentItem->getRefItems( 'price', 'default' );
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
	 * @param string $prodId Unique ID of the product
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

		if( count( $productManager->searchItems( $search, array() ) ) === 0 )
		{
			$msg = sprintf( 'Invalid "%1$s" references for product with ID "%2$s"', $domain, $prodId );
			throw new \Aimeos\Controller\Frontend\Basket\Exception( $msg );
		}
	}


	/**
	 * Creates the order product attribute items from the given attribute IDs and updates the price item if necessary.
	 *
	 * @param \Aimeos\MShop\Price\Item\Iface $price Price item of the ordered product
	 * @param string $prodid Unique product ID where the given attributes must be attached to
	 * @param integer $quantity Number of products that should be added to the basket
	 * @param array $attributeIds List of attributes IDs of the given type
	 * @param string $type Attribute type
	 * @param array $attributeValues Associative list of attribute IDs as keys and their codes as values
	 * @return array List of items implementing \Aimeos\MShop\Order\Item\Product\Attribute\Iface
	 */
	protected function createOrderProductAttributes( \Aimeos\MShop\Price\Item\Iface $price, $prodid, $quantity,
			array $attributeIds, $type, array $attributeValues = array() )
	{
		if( empty( $attributeIds ) ) {
			return array();
		}

		$attrTypeId = $this->getProductListTypeItem( 'attribute', $type )->getId();
		$this->checkReferences( $prodid, 'attribute', $attrTypeId, $attributeIds );

		$list = array();
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
			return array();
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
			return array();
		}

		$attributeManager = \Aimeos\MShop\Factory::createManager( $this->getContext(), 'attribute' );
		$search = $attributeManager->createSearch( true );
		$expr = array();

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
		$subProductIds = array();
		foreach( $productItem->getRefItems( 'product', 'default', 'default' ) as $item ) {
			$subProductIds[] = $item->getId();
		}

		if( count( $subProductIds ) === 0 ) {
			return array();
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


	/**
	 * Checks if the product is part of at least one category in the product catalog.
	 *
	 * @param string $prodid Unique ID of the product
	 * @throws \Aimeos\Controller\Frontend\Basket\Exception If product is not associated to at least one category
	 * @deprecated 2016.05
	 */
	protected function checkCategory( $prodid )
	{
		$catalogListManager = \Aimeos\MShop\Factory::createManager( $this->getContext(), 'catalog/lists' );

		$search = $catalogListManager->createSearch( true );
		$expr = array(
				$search->compare( '==', 'catalog.lists.refid', $prodid ),
				$search->getConditions()
		);
		$search->setConditions( $search->combine( '&&', $expr ) );
		$search->setSlice( 0, 1 );

		$result = $catalogListManager->searchItems( $search );

		if( reset( $result ) === false )
		{
			$msg = sprintf( 'Adding product with ID "%1$s" is not allowed', $prodid );
			throw new \Aimeos\Controller\Frontend\Basket\Exception( $msg );
		}
	}


	/**
	 * Edits the changed product to the basket if it's in stock.
	 *
	 * @param \Aimeos\MShop\Order\Item\Base\Product\Iface $orderBaseProductItem Old order product from basket
	 * @param string $productId Unique ID of the product item that belongs to the order product
	 * @param integer $quantity Number of products to add to the basket
	 * @param array $options Associative list of options
	 * @param string $warehouse Warehouse code for retrieving the stock level
	 * @throws \Aimeos\Controller\Frontend\Basket\Exception If there's not enough stock available
	 * @deprecated 2016.05
	 */
	protected function addProductInStock( \Aimeos\MShop\Order\Item\Base\Product\Iface $orderBaseProductItem,
			$productId, $quantity, array $options, $warehouse )
	{
		$stocklevel = null;
		if( !isset( $options['stock'] ) || $options['stock'] != false ) {
			$stocklevel = $this->getStockLevel( $productId, $warehouse );
		}

		if( $stocklevel === null || $stocklevel > 0 )
		{
			$position = $this->get()->addProduct( $orderBaseProductItem );
			$orderBaseProductItem = clone $this->get()->getProduct( $position );
			$quantity = $orderBaseProductItem->getQuantity();

			if( $stocklevel > 0 && $stocklevel < $quantity )
			{
				$this->get()->deleteProduct( $position );
				$orderBaseProductItem->setQuantity( $stocklevel );
				$this->get()->addProduct( $orderBaseProductItem, $position );
			}
		}

		if( $stocklevel !== null && $stocklevel < $quantity )
		{
			$msg = sprintf( 'There are not enough products "%1$s" in stock', $orderBaseProductItem->getName() );
			throw new \Aimeos\Controller\Frontend\Basket\Exception( $msg );
		}
	}


	/**
	 * Edits the changed product to the basket if it's in stock.
	 *
	 * @param \Aimeos\MShop\Order\Item\Base\Product\Iface $product Old order product from basket
	 * @param \Aimeos\MShop\Product\Item\Iface $productItem Product item that belongs to the order product
	 * @param integer $quantity New product quantity
	 * @param integer $position Position of the old order product in the basket
	 * @param array Associative list of options
	 * @throws \Aimeos\Controller\Frontend\Basket\Exception If there's not enough stock available
	 * @deprecated 2016.05
	 */
	protected function editProductInStock( \Aimeos\MShop\Order\Item\Base\Product\Iface $product,
			\Aimeos\MShop\Product\Item\Iface $productItem, $quantity, $position, array $options )
	{
		$stocklevel = null;
		if( !isset( $options['stock'] ) || $options['stock'] != false ) {
			$stocklevel = $this->getStockLevel( $productItem->getId(), $product->getWarehouseCode() );
		}

		$product->setQuantity( ( $stocklevel !== null && $stocklevel > 0 ? min( $stocklevel, $quantity ) : $quantity ) );

		$this->get()->deleteProduct( $position );

		if( $stocklevel === null || $stocklevel > 0 ) {
			$this->get()->addProduct( $product, $position );
		}

		if( $stocklevel !== null && $stocklevel < $quantity )
		{
			$msg = sprintf( 'There are not enough products "%1$s" in stock', $productItem->getName() );
			throw new \Aimeos\Controller\Frontend\Basket\Exception( $msg );
		}
	}


	/**
	 * Returns the highest stock level for the product.
	 *
	 * @param string $prodid Unique ID of the product
	 * @param string $warehouse Unique code of the warehouse
	 * @return integer|null Number of available items in stock (null for unlimited stock)
	 * @deprecated 2016.04 Use basket stock decorator instead
	 */
	protected function getStockLevel( $prodid, $warehouse )
	{
		$manager = \Aimeos\MShop\Factory::createManager( $this->getContext(), 'product/stock' );

		$search = $manager->createSearch( true );
		$expr = array(
				$search->compare( '==', 'product.stock.parentid', $prodid ),
				$search->getConditions(),
				$search->compare( '==', 'product.stock.warehouse.code', $warehouse ),
		);
		$search->setConditions( $search->combine( '&&', $expr ) );

		$result = $manager->searchItems( $search );

		if( empty( $result ) )
		{
			$msg = sprintf( 'No stock for product ID "%1$s" and warehouse "%2$s" available', $prodid, $warehouse );
			throw new \Aimeos\Controller\Frontend\Basket\Exception( $msg );
		}

		$stocklevel = null;

		foreach( $result as $item )
		{
			if( ( $stock = $item->getStockLevel() ) === null ) {
				return null;
			}

			$stocklevel = max( (int) $stocklevel, $item->getStockLevel() );
		}

		return $stocklevel;
	}
}
