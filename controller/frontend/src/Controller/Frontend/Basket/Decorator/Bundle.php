<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2017-2018
 * @package Controller
 * @subpackage Frontend
 */


namespace Aimeos\Controller\Frontend\Basket\Decorator;


/**
 * Bundle product handling
 *
 * @package Controller
 * @subpackage Frontend
 */
class Bundle
	extends \Aimeos\Controller\Frontend\Basket\Decorator\Base
	implements \Aimeos\Controller\Frontend\Basket\Iface, \Aimeos\Controller\Frontend\Common\Decorator\Iface
{
	/**
	 * Adds a bundle product to the basket of the user stored in the session.
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
		$context = $this->getContext();
		$productManager = \Aimeos\MShop\Factory::createManager( $context, 'product' );

		if( $productManager->getItem( $prodid, [], true )->getType() !== 'bundle' )
		{
			return $this->getController()->addProduct(
				$prodid, $quantity, $stocktype, $variantAttributeIds,
				$configAttributeIds, $hiddenAttributeIds, $customAttributeValues
			);
		}

		$attributeMap = [
			'custom' => array_keys( $customAttributeValues ),
			'config' => array_keys( $configAttributeIds ),
		];
		$this->checkListRef( $prodid, 'attribute', $attributeMap );


		$productItem = $productManager->getItem( $prodid, ['attribute', 'media', 'supplier', 'price', 'product', 'text'], true );
		$prices = $productItem->getRefItems( 'price', 'default', 'default' );
		$hidden = $productItem->getRefItems( 'attribute', null, 'hidden' );

		$orderBaseProductItem = \Aimeos\MShop\Factory::createManager( $context, 'order/base/product' )->createItem();
		$orderBaseProductItem->copyFrom( $productItem )->setQuantity( $quantity )->setStockType( $stocktype );

		$this->addBundleProducts( $orderBaseProductItem, $productItem, $variantAttributeIds, $stocktype );

		$custAttr = $this->getOrderProductAttributes( 'custom', array_keys( $customAttributeValues ), $customAttributeValues );
		$confAttr = $this->getOrderProductAttributes( 'config', array_keys( $configAttributeIds ), [], $configAttributeIds );
		$attr = array_merge( $custAttr, $confAttr, $this->getOrderProductAttributes( 'hidden', array_keys( $hidden ) ) );

		$orderBaseProductItem->setAttributes( $attr );
		$orderBaseProductItem->setPrice( $this->calcPrice( $orderBaseProductItem, $prices, $quantity ) );

		$this->getController()->get()->addProduct( $orderBaseProductItem );
		$this->getController()->save();
	}


	/**
	 * Adds the bundled products to the order product item.
	 *
	 * @param \Aimeos\MShop\Order\Item\Base\Product\Iface $orderBaseProductItem Order product item
	 * @param \Aimeos\MShop\Product\Item\Iface $productItem Bundle product item
	 * @param array $variantAttributeIds List of product variant attribute IDs
	 * @param string $stocktype
	 */
	protected function addBundleProducts( \Aimeos\MShop\Order\Item\Base\Product\Iface $orderBaseProductItem,
		\Aimeos\MShop\Product\Item\Iface $productItem, array $variantAttributeIds, $stocktype )
	{
		$quantity = $orderBaseProductItem->getQuantity();
		$products = $subProductIds = $orderProducts = [];
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
			$orderProduct->setStockType( $stocktype );
			$orderProduct->setPrice( $this->calcPrice( $orderProduct, $prices, $quantity ) );

			$orderProducts[] = $orderProduct;
		}

		$orderBaseProductItem->setProducts( $orderProducts );
	}
}
