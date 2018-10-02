<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2017-2018
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
	 * Adds a selection product to the basket of the user stored in the session.
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
		$productItem = $productManager->getItem( $prodid, [], true );

		if( $productManager->getItem( $prodid, [], true )->getType() !== 'select' )
		{
			return $this->getController()->addProduct(
				$prodid, $quantity, $stocktype, $variantAttributeIds,
				$configAttributeIds, $hiddenAttributeIds, $customAttributeValues
			);
		}

		$productItem = $productManager->getItem( $prodid, ['attribute', 'media', 'supplier', 'price', 'product', 'text'], true );
		$prices = $productItem->getRefItems( 'price', 'default', 'default' );
		$hidden = $productItem->getRefItems( 'attribute', null, 'hidden' );

		$orderBaseProductItem = \Aimeos\MShop\Factory::createManager( $context, 'order/base/product' )->createItem();
		$orderBaseProductItem->copyFrom( $productItem )->setQuantity( $quantity )->setStockType( $stocktype );

		$attr = $this->getVariantDetails( $orderBaseProductItem, $productItem, $prices, $variantAttributeIds );
		$hidden += $productItem->getRefItems( 'attribute', null, 'hidden' );

		$attributeMap = [
			'custom' => array_keys( $customAttributeValues ),
			'config' => array_keys( $configAttributeIds ),
		];
		$this->checkListRef( array( $prodid, $productItem->getId() ), 'attribute', $attributeMap );

		$custAttr = $this->getOrderProductAttributes( 'custom', array_keys( $customAttributeValues ), $customAttributeValues );
		$confAttr = $this->getOrderProductAttributes( 'config', array_keys( $configAttributeIds ), [], $configAttributeIds );
		$attr = array_merge( $attr, $custAttr, $confAttr, $this->getOrderProductAttributes( 'hidden', array_keys( $hidden ) ) );

		$orderBaseProductItem->setAttributes( $attr );
		$orderBaseProductItem->setPrice( $this->calcPrice( $orderBaseProductItem, $prices, $quantity ) );

		$this->getController()->get()->addProduct( $orderBaseProductItem );
		$this->getController()->save();
	}


	/**
	 * Returns the variant attributes and updates the price list if necessary.
	 *
	 * @param \Aimeos\MShop\Order\Item\Base\Product\Iface $orderBaseProductItem Order product item
	 * @param \Aimeos\MShop\Product\Item\Iface &$productItem Product item which is replaced if necessary
	 * @param array &$prices List of product prices that will be updated if necessary
	 * @param array $variantAttributeIds List of product variant attribute IDs
	 * @return \Aimeos\MShop\Order\Item\Base\Product\Attribute\Iface[] List of order product attributes
	 * @throws \Aimeos\Controller\Frontend\Basket\Exception If no product variant is found
	 */
	protected function getVariantDetails( \Aimeos\MShop\Order\Item\Base\Product\Iface $orderBaseProductItem,
		\Aimeos\MShop\Product\Item\Iface &$productItem, array &$prices, array $variantAttributeIds )
	{
		$attr = [];
		$context = $this->getContext();
		$productItems = $this->getProductVariants( $productItem, $variantAttributeIds );

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


		if( count( $productItems ) > 1 )
		{
			$msg = $context->getI18n()->dt( 'controller/frontend', 'No unique article found for selected attributes and product ID "%1$s"' );
			throw new \Aimeos\Controller\Frontend\Basket\Exception( sprintf( $msg, $productItem->getId() ) );
		}
		else if( ( $result = reset( $productItems ) ) !== false ) // count == 1
		{
			$productItem = $result;
			$orderBaseProductItem->setProductCode( $productItem->getCode() );

			$subprices = $productItem->getRefItems( 'price', 'default', 'default' );

			if( !empty( $subprices ) ) {
				$prices = $subprices;
			}

			$submedia = $productItem->getRefItems( 'media', 'default', 'default' );

			if( ( $mediaItem = reset( $submedia ) ) !== false ) {
				$orderBaseProductItem->setMediaUrl( $mediaItem->getPreview() );
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
		else if( $requireVariant != false ) // count == 0
		{
			$msg = $context->getI18n()->dt( 'controller/frontend', 'No article found for selected attributes and product ID "%1$s"' );
			throw new \Aimeos\Controller\Frontend\Basket\Exception( sprintf( $msg, $productItem->getId() ) );
		}

		return $attr;
	}
}
