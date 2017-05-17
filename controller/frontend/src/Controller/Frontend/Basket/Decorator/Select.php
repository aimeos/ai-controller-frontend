<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2017
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
	 * @param array $options Possible options are: 'stock'=>true|false and 'variant'=>true|false
	 * 	The 'stock'=>false option allows adding products without being in stock.
	 * 	The 'variant'=>false option allows adding the selection product to the basket
	 * 	instead of the specific sub-product if the variant-building attribute IDs
	 * 	doesn't match a specific sub-product or if the attribute IDs are missing.
	 * @param array $variantAttributeIds List of variant-building attribute IDs that identify a specific product
	 * 	in a selection products
	 * @param array $configAttributeIds  List of attribute IDs that doesn't identify a specific product in a
	 * 	selection of products but are stored together with the product (e.g. for configurable products)
	 * @param array $hiddenAttributeIds List of attribute IDs that should be stored along with the product in the order
	 * @param array $customAttributeValues Associative list of attribute IDs and arbitrary values that should be stored
	 * 	along with the product in the order
	 * @param string $stocktype Unique code of the stock type to deliver the products from
	 * @throws \Aimeos\Controller\Frontend\Basket\Exception If the product isn't available
	 */
	public function addProduct( $prodid, $quantity = 1, array $options = [], array $variantAttributeIds = [],
		array $configAttributeIds = [], array $hiddenAttributeIds = [], array $customAttributeValues = [],
		$stocktype = 'default' )
	{
		$context = $this->getContext();
		$productManager = \Aimeos\MShop\Factory::createManager( $context, 'product' );
		$productItem = $productManager->getItem( $prodid, [], true );

		if( $productManager->getItem( $prodid, [], true )->getType() !== 'select' )
		{
			return $this->getController()->addProduct(
				$prodid, $quantity, $options, $variantAttributeIds, $configAttributeIds,
				$hiddenAttributeIds, $customAttributeValues, $stocktype
			);
		}

		$productItem = $productManager->getItem( $prodid, array( 'media', 'supplier', 'price', 'product', 'text' ), true );
		$prices = $productItem->getRefItems( 'price', 'default', 'default' );

		$orderBaseProductItem = \Aimeos\MShop\Factory::createManager( $context, 'order/base/product' )->createItem();
		$orderBaseProductItem->copyFrom( $productItem )->setQuantity( $quantity )->setStockType( $stocktype );

		$attr = $this->getVariantDetails( $orderBaseProductItem, $productItem, $prices, $variantAttributeIds, $options );

		$attributeMap = [
			'custom' => array_keys( $customAttributeValues ),
			'config' => $configAttributeIds,
			'hidden' => $hiddenAttributeIds,
		];
		$this->checkListRef( array( $prodid, $productItem->getId() ), 'attribute', $attributeMap );

		$attr = array_merge( $attr, $this->getOrderProductAttributes( 'custom', array_keys( $customAttributeValues ), $customAttributeValues ) );
		$attr = array_merge( $attr, $this->getOrderProductAttributes( 'config', $configAttributeIds ) );
		$attr = array_merge( $attr, $this->getOrderProductAttributes( 'hidden', $hiddenAttributeIds ) );

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
	 * @param array $options Associative list of options
	 * @return \Aimeos\MShop\Order\Item\Base\Product\Attribute\Iface[] List of order product attributes
	 * @throws \Aimeos\Controller\Frontend\Basket\Exception If no product variant is found
	 */
	protected function getVariantDetails( \Aimeos\MShop\Order\Item\Base\Product\Iface $orderBaseProductItem,
		\Aimeos\MShop\Product\Item\Iface &$productItem, array &$prices, array $variantAttributeIds, array $options )
	{
		$attr = [];
		$productItems = $this->getProductVariants( $productItem, $variantAttributeIds );

		if( count( $productItems ) > 1 )
		{
			$msg = sprintf( 'No unique article found for selected attributes and product ID "%1$s"', $productItem->getId() );
			throw new \Aimeos\Controller\Frontend\Basket\Exception( $msg );
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
		else if( !isset( $options['variant'] ) || $options['variant'] != false ) // count == 0
		{
			$msg = sprintf( 'No article found for selected attributes and product ID "%1$s"', $productItem->getId() );
			throw new \Aimeos\Controller\Frontend\Basket\Exception( $msg );
		}

		return $attr;
	}
}
