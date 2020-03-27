<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2017-2020
 * @package Controller
 * @subpackage Frontend
 */


namespace Aimeos\Controller\Frontend\Basket\Decorator;


/**
 * Selection product handling
 *
 * @package Controller
 * @subpackage Frontend
 */
class Select
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
		if( $product->getType() !== 'select' )
		{
			$this->getController()->addProduct( $product, $quantity, $variant, $config, $custom, $stocktype, $supplier, $siteid );
			return $this;
		}

		$attr = [];
		$quantity = $this->checkQuantity( $product, $quantity );
		$prices = $product->getRefItems( 'price', 'default', 'default' );
		$hidden = $product->getRefItems( 'attribute', null, 'hidden' );

		$orderBaseProductItem = \Aimeos\MShop::create( $this->getContext(), 'order/base/product' )->createItem();
		$orderBaseProductItem = $orderBaseProductItem->copyFrom( $product );

		$productItem = $this->getArticle( $product, $variant );
		$orderBaseProductItem->setProductCode( $productItem->getCode() );

		$this->checkAttributes( [$product, $productItem], 'custom', array_keys( $custom ) );
		$this->checkAttributes( [$product, $productItem], 'config', array_keys( $config ) );

		if( !( $subprices = $productItem->getRefItems( 'price', 'default', 'default' ) )->isEmpty() ) {
			$prices = $subprices;
		}

		if( $mediaItem = $productItem->getRefItems( 'media', 'default', 'default' )->first() ) {
			$orderBaseProductItem->setMediaUrl( $mediaItem->getPreview() );
		}

		$hidden->union( $productItem->getRefItems( 'attribute', null, 'hidden' ) );

		$orderProductAttrManager = \Aimeos\MShop::create( $this->getContext(), 'order/base/product/attribute' );
		$attributes = $productItem->getRefItems( 'attribute', null, 'variant' );

		foreach( $this->getAttributes( $attributes->keys()->toArray(), ['text'] ) as $attrItem ) {
			$attr[] = $orderProductAttrManager->createItem()->copyFrom( $attrItem )->setType( 'variant' );
		}

		$custAttr = $this->getOrderProductAttributes( 'custom', array_keys( $custom ), $custom );
		$confAttr = $this->getOrderProductAttributes( 'config', array_keys( $config ), [], $config );
		$hideAttr = $this->getOrderProductAttributes( 'hidden', $hidden->keys()->toArray() );

		$orderBaseProductItem = $orderBaseProductItem->setQuantity( $quantity )
			->setAttributeItems( array_merge( $attr, $custAttr, $confAttr, $hideAttr ) )
			->setPrice( $this->calcPrice( $orderBaseProductItem, $prices, $quantity ) )
			->setStockType( $stocktype )->setSupplierCode( $supplier );

		if( $siteid ) {
			$orderBaseProductItem->setSiteId( $siteid );
		}

		$this->getController()->get()->addProduct( $orderBaseProductItem );
		$this->getController()->save();

		return $this;
	}


	/**
	 * Returns the variant attributes and updates the price list if necessary.
	 *
	 * @param \Aimeos\MShop\Product\Item\Iface $productItem Product item which is replaced if necessary
	 * @param array $variantAttributeIds List of product variant attribute IDs
	 * @return \Aimeos\MShop\Product\Item\Iface Product variant article
	 * @throws \Aimeos\Controller\Frontend\Basket\Exception If no product variant is found
	 */
	protected function getArticle( \Aimeos\MShop\Product\Item\Iface $productItem, array $variant ) : \Aimeos\MShop\Product\Item\Iface
	{
		$items = [];
		$context = $this->getContext();

		/** controller/frontend/basket/require-variant
		 * A variant of a selection product must be chosen
		 *
		 * Selection products normally consist of several article variants and
		 * by default exactly one article variant of a selection product can be
		 * put into the basket.
		 *
		 * By setting this option to false, the selection product including the
		 * chosen attributes (if any attribute values were selected) can be put
		 * into the basket as well. This makes it possible to get all articles
		 * or a subset of articles (e.g. all of a color) at once.
		 *
		 * This option replace the "client/html/basket/require-variant" setting.
		 *
		 * @param boolean True if a variant must be chosen, false if also the selection product with attributes can be added
		 * @since 2018.01
		 * @category Developer
		 * @category User
		 */
		$requireVariant = $context->getConfig()->get( 'controller/frontend/basket/require-variant', true );

		foreach( $productItem->getRefItems( 'product', 'default', 'default' ) as $item )
		{
			foreach( $variant as $id )
			{
				if( $item->getListItem( 'attribute', 'variant', $id ) === null ) {
					continue 2;
				}
			}

			$items[] = $item;
		}

		if( count( $items ) > 1 )
		{
			$msg = $context->getI18n()->dt( 'controller/frontend', 'No unique article found for selected attributes and product ID "%1$s"' );
			throw new \Aimeos\Controller\Frontend\Basket\Exception( sprintf( $msg, $productItem->getId() ) );
		}

		if( empty( $items ) && $requireVariant != false ) // count == 0
		{
			$msg = $context->getI18n()->dt( 'controller/frontend', 'No article found for selected attributes and product ID "%1$s"' );
			throw new \Aimeos\Controller\Frontend\Basket\Exception( sprintf( $msg, $productItem->getId() ) );
		}

		return current( $items ) ?: $productItem;
	}
}
