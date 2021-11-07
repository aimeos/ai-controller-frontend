<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2017-2021
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
	 * @param string|null $supplierid Unique supplier ID the product is from
	 * @param string|null $siteid Unique site ID the product is from or null for siteid of the product item
	 * @return \Aimeos\Controller\Frontend\Basket\Iface Basket frontend object for fluent interface
	 * @throws \Aimeos\Controller\Frontend\Basket\Exception If the product isn't available
	 */
	public function addProduct( \Aimeos\MShop\Product\Item\Iface $product,
		float $quantity = 1, array $variant = [], array $config = [], array $custom = [],
		string $stocktype = 'default', string $supplierid = null, string $siteid = null ) : \Aimeos\Controller\Frontend\Basket\Iface
	{
		if( $product->getType() !== 'bundle' )
		{
			$this->getController()->addProduct( $product, $quantity, $variant, $config, $custom, $stocktype, $supplierid, $siteid );
			return $this;
		}

		$quantity = $this->call( 'checkQuantity', $product, $quantity );
		$this->call( 'checkAttributes', [$product], 'custom', array_keys( $custom ) );
		$this->call( 'checkAttributes', [$product], 'config', array_keys( $config ) );

		$prices = $product->getRefItems( 'price', 'default', 'default' );
		$hidden = $product->getRefItems( 'attribute', null, 'hidden' );

		$custAttr = $this->call( 'getOrderProductAttributes', 'custom', array_keys( $custom ), $custom );
		$confAttr = $this->call( 'getOrderProductAttributes', 'config', array_keys( $config ), [], $config );
		$hideAttr = $this->call( 'getOrderProductAttributes', 'hidden', $hidden->keys()->toArray() );

		$orderBaseProductItem = \Aimeos\MShop::create( $this->getContext(), 'order/base/product' )->create();
		$name = '';

		if( $supplierid )
		{
			$name = \Aimeos\MShop::create( $this->getContext(), 'supplier' )->get( $supplierid, ['text' => ['name']] )->getName();
			$orderBaseProductItem->setSupplierId( $supplierid )->setSupplierName( $name );
		}

		$orderBaseProductItem = $orderBaseProductItem->copyFrom( $product )
			->setAttributeItems( array_merge( $custAttr, $confAttr, $hideAttr ) )
			->setProducts( $this->getBundleProducts( $product, $quantity, $stocktype, $supplierid, $name ) )
			->setPrice( $this->call( 'calcPrice', $orderBaseProductItem, $prices, $quantity ) )
			->setQuantity( $quantity )->setStockType( $stocktype );

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
	 * @param string|null $supplierid Unique supplier ID the product is from
	 * @param string $suppliername Name of the supplier the product is from
	 * @return \Aimeos\MShop\Order\Item\Base\Product\Iface[] List of order product item from bundle
	 */
	protected function getBundleProducts( \Aimeos\MShop\Product\Item\Iface $product, float $quantity,
		string $stocktype, ?string $supplierid, string $suppliername ) : array
	{
		$orderProducts = [];
		$orderProductManager = \Aimeos\MShop::create( $this->getContext(), 'order/base/product' );

		foreach( $product->getRefItems( 'product', null, 'default' ) as $item )
		{
			$orderProduct = $orderProductManager->create()->copyFrom( $item )->setParentProductId( $product->getId() );
			$prices = $item->getRefItems( 'price', 'default', 'default' );

			$orderProducts[] = $orderProduct->setStockType( $stocktype )
				->setSupplierId( $supplierid )->setSupplierName( $suppliername )
				->setPrice( $this->call( 'calcPrice', $orderProduct, $prices, $quantity ) );
		}

		return $orderProducts;
	}
}
