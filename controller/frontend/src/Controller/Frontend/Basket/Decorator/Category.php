<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2016-2018
 * @package Controller
 * @subpackage Frontend
 */


namespace Aimeos\Controller\Frontend\Basket\Decorator;


/**
 * Category check for basket controller
 *
 * @package Controller
 * @subpackage Frontend
 */
class Category
	extends Base
	implements \Aimeos\Controller\Frontend\Basket\Iface, \Aimeos\Controller\Frontend\Common\Decorator\Iface
{
	/**
	 * Adds a categorized product to the basket of the user stored in the session.
	 *
	 * @param string $prodid ID of the base product to add
	 * @param integer $quantity Amount of products that should by added
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
	public function addProduct( $prodid, $quantity = 1, $stocktype = 'default', array $variantAttributeIds = [],
		array $configAttributeIds = [], array $hiddenAttributeIds = [], array $customAttributeValues = [] )
	{
		$context = $this->getContext();
		$catalogListManager = \Aimeos\MShop\Factory::createManager( $context, 'catalog/lists' );

		$search = $catalogListManager->createSearch( true );
		$expr = array(
			$search->compare( '==', 'catalog.lists.domain', 'product' ),
			$search->compare( '==', 'catalog.lists.refid', $prodid ),
			$search->getConditions()
		);
		$search->setConditions( $search->combine( '&&', $expr ) );
		$search->setSlice( 0, 1 );

		$result = $catalogListManager->searchItems( $search );

		if( reset( $result ) === false )
		{
			$msg = $context->getI18n()->dt( 'controller/frontend', 'Adding product with ID "%1$s" is not allowed' );
			throw new \Aimeos\Controller\Frontend\Basket\Exception( sprintf( $msg, $prodid ) );
		}

		$this->getController()->addProduct(
			$prodid, $quantity, $stocktype, $variantAttributeIds,
			$configAttributeIds, $hiddenAttributeIds, $customAttributeValues
		);
	}
}
