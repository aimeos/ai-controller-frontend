<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2017-2020
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
	 * Adds a product to the basket of the customer stored in the session
	 *
	 * @param \Aimeos\MShop\Product\Item\Iface $product Product to add including texts, media, prices, attributes, etc.
	 * @param float $quantity Amount of products that should by added
	 * @param array $variant List of variant-building attribute IDs that identify an article in a selection product
	 * @param array $config List of configurable attribute IDs the customer has chosen from
	 * @param array $custom Associative list of attribute IDs as keys and arbitrary values that will be added to the ordered product
	 * @param string $stocktype Unique code of the stock type to deliver the products from
	 * @param string $supplier Unique supplier code the product is from
	 * @param string|null $siteid Unique site ID the product is from or null for siteid of the product item
	 * @return \Aimeos\Controller\Frontend\Basket\Iface Basket frontend object for fluent interface
	 * @throws \Aimeos\Controller\Frontend\Basket\Exception If the product isn't available
	 */
	public function addProduct( \Aimeos\MShop\Product\Item\Iface $product,
		float $quantity = 1, array $variant = [], array $config = [], array $custom = [],
		string $stocktype = 'default', string $supplier = '', string $siteid = null ) : \Aimeos\Controller\Frontend\Basket\Iface
	{
		if( $product->getType() !== 'bundle' )
		{
			$this->getController()->addProduct( $product, $quantity, $variant, $config, $custom, $stocktype, $supplier, $siteid );
			return $this;
		}

		$quantity = $this->checkQuantity( $product, $quantity );
		$this->checkAttributes( [$product], 'custom', array_keys( $custom ) );
		$this->checkAttributes( [$product], 'config', array_keys( $config ) );

		$prices = $product->getRefItems( 'price', 'default', 'default' );
		$hidden = $product->getRefItems( 'attribute', null, 'hidden' );

		$custAttr = $this->getOrderProductAttributes( 'custom', array_keys( $custom ), $custom );
		$confAttr = $this->getOrderProductAttributes( 'config', array_keys( $config ), [], $config );
		$hideAttr = $this->getOrderProductAttributes( 'hidden', $hidden->keys()->toArray() );

		$orderBaseProductItem = \Aimeos\MShop::create( $this->getContext(), 'order/base/product' )->createItem();

		$orderBaseProductItem = $orderBaseProductItem->copyFrom( $product )
			->setAttributeItems( array_merge( $custAttr, $confAttr, $hideAttr ) )
			->setProducts( $this->getBundleProducts( $product, $quantity, $stocktype, $supplier ) )
			->setQuantity( $quantity )->setStockType( $stocktype )->setSupplierCode( $supplier )
			->setPrice( $this->calcPrice( $orderBaseProductItem, $prices, $quantity ) );

		if( $siteid ) {
			$orderBaseProductItem->setSiteId( $siteid );
		}

		$this->getController()->get()->addProduct( $orderBaseProductItem );
		$this->getController()->save();

		return $this;
	}


	/**
	 * Adds the bundled products to the order product item.
	 *
	 * @param \Aimeos\MShop\Product\Item\Iface $product Bundle product item
	 * @param float $quantity Amount of products that should by added
	 * @param string $stocktype Unique code of the stock type to deliver the products from
	 * @param string|null $supplier Unique supplier code the product is from
	 * @return \Aimeos\MShop\Order\Item\Base\Product\Iface[] List of order product item from bundle
	 */
	protected function getBundleProducts( \Aimeos\MShop\Product\Item\Iface $product, float $quantity,
		string $stocktype, string $supplier = null ) : array
	{
		$orderProducts = [];
		$orderProductManager = \Aimeos\MShop::create( $this->getContext(), 'order/base/product' );

		foreach( $product->getRefItems( 'product', null, 'default' ) as $item )
		{
			$orderProduct = $orderProductManager->createItem()->copyFrom( $item );
			$prices = $item->getRefItems( 'price', 'default', 'default' );

			$orderProducts[] = $orderProduct->setStockType( $stocktype )->setSupplierCode( $supplier )
				->setPrice( $this->calcPrice( $orderProduct, $prices, $quantity ) );
		}

		return $orderProducts;
	}
}
