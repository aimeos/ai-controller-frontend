<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Metaways Infosystems GmbH, 2012
 * @copyright Aimeos (aimeos.org), 2017-2018
 * @package Controller
 * @subpackage Frontend
 */


namespace Aimeos\Controller\Frontend\Product;


/**
 * Interface for product frontend controllers.
 *
 * @package Controller
 * @subpackage Frontend
 */
interface Iface
{
	/**
	 * Returns the given search filter with the conditions attached for filtering by attribute.
	 *
	 * @param \Aimeos\MW\Criteria\Iface $filter Criteria object used for product search
	 * @param array $attrIds List of attribute IDs for faceted search
	 * @param array $optIds List of OR-combined attribute IDs for faceted search
	 * @param array $attrIds Associative list of OR-combined attribute IDs per attribute type for faceted search
	 * @return \Aimeos\MW\Criteria\Iface Criteria object containing the conditions for searching
	 * @since 2017.03
	 */
	public function addFilterAttribute( \Aimeos\MW\Criteria\Iface $filter, array $attrIds, array $optIds, array $oneIds );


	/**
	 * Returns the given search filter with the conditions attached for filtering by category.
	 *
	 * @param \Aimeos\MW\Criteria\Iface $filter Criteria object used for product search
	 * @param string|array $catId Selected category by the user
	 * @param integer $level Constant for current category only, categories of next level (LEVEL_LIST) or whole subtree (LEVEL_SUBTREE)
	 * @param string|null $sort Sortation of the product list like "name", "code", "price" and "position", null for no sortation
	 * @param string $direction Sort direction of the product list ("+", "-")
	 * @param string $listtype List type of the product associated to the category, usually "default"
	 * @return \Aimeos\MW\Criteria\Iface Criteria object containing the conditions for searching
	 * @since 2017.03
	 */
	public function addFilterCategory( \Aimeos\MW\Criteria\Iface $filter, $catId,
		$level = \Aimeos\MW\Tree\Manager\Base::LEVEL_ONE, $sort = null, $direction = '+', $listtype = 'default' );


	/**
	 * Returns the given search filter with the conditions attached for filtering by suppliers.
	 *
	 * @param \Aimeos\MW\Criteria\Iface $filter Criteria object used for product search
	 * @param array $supIds List of supplier IDs for faceted search
	 * @return \Aimeos\MW\Criteria\Iface Criteria object containing the conditions for searching
	 * @since 2018.07
	 */
	public function addFilterSupplier( \Aimeos\MW\Criteria\Iface $filter, array $supIds );


	/**
	 * Returns the given search filter with the conditions attached for filtering by text.
	 *
	 * @param \Aimeos\MW\Criteria\Iface $filter Criteria object used for product search
	 * @param string $input Search string entered by the user
	 * @param string|null $sort Sortation of the product list like "name", "code", "price" and "position", null for no sortation
	 * @param string $direction Sort direction of the product list ("+", "-")
	 * @param string $listtype List type of the text associated to the product, usually "default"
	 * @return \Aimeos\MW\Criteria\Iface Criteria object containing the conditions for searching
	 * @since 2017.03
	 */
	public function addFilterText( \Aimeos\MW\Criteria\Iface $filter, $input, $sort = null, $direction = '+', $listtype = 'default' );


	/**
	 * Returns the aggregated count of products from the index for the given key.
	 *
	 * @param \Aimeos\MW\Criteria\Iface $filter Critera object which contains the filter conditions
	 * @param string $key Search key to aggregate for, e.g. "index.attribute.id"
	 * @return array Associative list of key values as key and the product count for this key as value
	 * @since 2017.03
	 */
	public function aggregate( \Aimeos\MW\Criteria\Iface $filter, $key );


	/**
	 * Returns the default product filter.
	 *
	 * @param string|null $sort Sortation of the product list like "name", "code", "price" and "position", null for no sortation
	 * @param string $direction Sort direction of the product list ("+", "-")
	 * @param integer $start Position in the list of found products where to begin retrieving the items
	 * @param integer $size Number of products that should be returned
	 * @param string $listtype Type of the product list, e.g. default, promotion, etc.
	 * @return \Aimeos\MW\Criteria\Iface Criteria object containing the conditions for searching
	 * @since 2017.03
	 */
	public function createFilter( $sort = null, $direction = '+', $start = 0, $size = 100, $listtype = 'default' );


	/**
	 * Returns the product for the given product ID from the index
	 *
	 * @param string $productId Unique product ID
	 * @param string[] $domains Domain names of items that are associated with the products and that should be fetched too
	 * @return \Aimeos\MShop\Product\Item\Iface Product item including the referenced domains items
	 * @since 2017.03
	 */
	public function getItem( $productId, array $domains = array( 'attribute', 'media', 'price', 'product', 'product/property', 'text' ) );


	/**
	 * Returns the product for the given product ID from the index
	 *
	 * @param string[] $productIds List of unique product ID
	 * @param string[] $domains Domain names of items that are associated with the products and that should be fetched too
	 * @return \Aimeos\MShop\Product\Item\Iface[] Associative list of product IDs as keys and product items as values
	 * @since 2017.03
	 */
	public function getItems( array $productIds, array $domains = array( 'media', 'price', 'text' ) );


	/**
	 * Returns the products from the index filtered by the given criteria object.
	 *
	 * @param \Aimeos\MW\Criteria\Iface $filter Critera object which contains the filter conditions
	 * @param string[] $domains Domain names of items that are associated with the products and that should be fetched too
	 * @param integer &$total Parameter where the total number of found products will be stored in
	 * @return array Ordered list of product items implementing \Aimeos\MShop\Product\Item\Iface
	 * @since 2017.03
	 */
	public function searchItems( \Aimeos\MW\Criteria\Iface $filter, array $domains = array( 'media', 'price', 'text' ), &$total = null );
}
