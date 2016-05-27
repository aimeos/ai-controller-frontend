<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2016
 * @package Controller
 * @subpackage Frontend
 */


namespace Aimeos\Controller\Frontend\Basket\Decorator;


/**
 * Stock level check for basket controller
 *
 * @package Controller
 * @subpackage Frontend
 */
class Stock
	extends \Aimeos\Controller\Frontend\Basket\Decorator\Base
	implements \Aimeos\Controller\Frontend\Basket\Iface, \Aimeos\Controller\Frontend\Common\Decorator\Iface
{
	/**
	 * Adds a categorized product to the basket of the user stored in the session.
	 *
	 * @param string $prodid ID of the base product to add
	 * @param integer $quantity Amount of products that should by added
	 * @param array $options Possible options are: 'stock'=>true|false and 'variant'=>true|false
	 * 	The 'stock'=>false option allows adding products without being in stock.
	 * 	The 'variant'=>false option allows adding the selection product to the basket
	 * 	instead of the specific sub-product if the variant-building attribute IDs
	 * 	doesn't match a specific sub-product or if the attribute IDs are missing.
	 * @param array $variantIds List of variant-building attribute IDs that identify a specific product
	 * 	in a selection products
	 * @param array $configIds  List of attribute IDs that doesn't identify a specific product in a
	 * 	selection of products but are stored together with the product (e.g. for configurable products)
	 * @param array $hiddenIds List of attribute IDs that should be stored along with the product in the order
	 * @param array $customValues Associative list of attribute IDs and arbitrary values that should be stored
	 * 	along with the product in the order
	 * @param string $warehouse Unique code of the warehouse to deliver the products from
	 * @throws \Aimeos\Controller\Frontend\Basket\Exception If the product isn't available
	 */
	public function addProduct( $prodid, $quantity = 1, array $options = array(), array $variantIds = array(),
		array $configIds = array(), array $hiddenIds = array(), array $customValues = array(), $warehouse = 'default' )
	{
		$stocklevel = null;

		if( !isset( $options['stock'] ) || $options['stock'] != false ) {
			$stocklevel = $this->getStockLevel( $prodid, $warehouse );
		}

		$qty = ( $stocklevel !== null ? min( $stocklevel, $quantity ) : $quantity );

		if( $qty > 0 )
		{
			$this->getController()->addProduct(
				$prodid, $qty, $options, $variantIds, $configIds, $hiddenIds, $customValues, $warehouse
			);
		}

		if( $qty < $quantity )
		{
			$product = \Aimeos\MShop\Factory::createManager( $this->getContext(), 'product' )->getItem( $prodid );
			$msg = sprintf( 'There are not enough products "%1$s" in stock', $product->getName() );
			throw new \Aimeos\Controller\Frontend\Basket\Exception( $msg );
		}
	}


	/**
	 * Edits the quantity of a product item in the basket.
	 *
	 * @param integer $position Position number (key) of the order product item
	 * @param integer $quantity New quantiy of the product item
	 * @param array $options Possible options are: 'stock'=>true|false
	 * 	The 'stock'=>false option allows adding products without being in stock.
	 * @param string[] $configCodes Codes of the product config attributes that should be REMOVED
	 */
	public function editProduct( $position, $quantity, array $options = array(), array $configCodes = array() )
	{
		$stocklevel = null;
		$product = $this->getController()->get()->getProduct( $position );

		if( !isset( $options['stock'] ) || $options['stock'] != false ) {
			$stocklevel = $this->getStockLevel( $product->getProductId(), $product->getWarehouseCode() );
		}

		$qty = ( $stocklevel !== null ? min( $stocklevel, $quantity ) : $quantity );

		$this->getController()->editProduct( $position, $qty, $options, $configCodes );

		if( $qty < $quantity )
		{
			$msg = sprintf( 'There are not enough products "%1$s" in stock', $product->getName() );
			throw new \Aimeos\Controller\Frontend\Basket\Exception( $msg );
		}
	}


	/**
	 * Returns the highest stock level for the product.
	 *
	 * @param string $prodid Unique ID of the product
	 * @param string $warehouse Unique code of the warehouse
	 * @return integer|null Number of available items in stock (null for unlimited stock)
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
