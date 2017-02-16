<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Metaways Infosystems GmbH, 2012
 * @copyright Aimeos (aimeos.org), 2017
 * @package Controller
 * @subpackage Frontend
 */


namespace Aimeos\Controller\Frontend\Index;


/**
 * Interface for catalog frontend controllers.
 *
 * @package Controller
 * @subpackage Frontend
 */
interface Iface
{
	/**
	 * Returns the given search filter with the conditions attached for filtering by category.
	 *
	 * @param \Aimeos\MW\Criteria\Iface $filter Criteria object used for product search
	 * @param string $catid Selected category by the user
	 * @return \Aimeos\MW\Criteria\Iface Criteria object containing the conditions for searching
	 * @since 2017.03
	 */
	public function addFilterCategory( \Aimeos\MW\Criteria\Iface $filter, $catid );


	/**
	 * Returns the given search filter with the conditions attached for filtering texts.
	 *
	 * @param \Aimeos\MW\Criteria\Iface $filter Criteria object used for product search
	 * @param string $input Search string entered by the user
	 * @param string $listtype List type of the text associated to the product, usually "default"
	 * @return \Aimeos\MW\Criteria\Iface Criteria object containing the conditions for searching
	 * @since 2017.03
	 */
	public function addFilterText( \Aimeos\MW\Criteria\Iface $filter, $input, $listtype = 'default' );


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
	 * Returns the default index filter.
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
	 * Returns text filter for the given search string.
	 *
	 * @param string $input Search string entered by the user
	 * @param string|null $sort Sortation of the product list like "name" and "relevance", null for no sortation
	 * @param string $direction Sort direction of the product list ("+", "-")
	 * @param integer $start Position in the list of found products where to begin retrieving the items
	 * @param integer $size Number of products that should be returned
	 * @param string $listtype List type of the text associated to the product, usually "default"
	 * @param string $type Type of the text like "name", "short", "long", etc.
	 * @return \Aimeos\MW\Criteria\Iface Criteria object containing the conditions for searching
	 */
	public function createTextFilter( $input, $sort = null, $direction = '-', $start = 0, $size = 25, $listtype = 'default', $type = 'name' );


	/**
	 * Returns the products from the index filtered by the given criteria object.
	 *
	 * @param \Aimeos\MW\Criteria\Iface $filter Critera object which contains the filter conditions
	 * @param string[] $domains Domain names of items that are associated with the products and that should be fetched too
	 * @param integer &$total Parameter where the total number of found products will be stored in
	 * @return array Ordered list of product items implementing \Aimeos\MShop\Product\Item\Iface
	 * @since 2017.03
	 */
	public function getItems( \Aimeos\MW\Criteria\Iface $filter, array $domains = array( 'media', 'price', 'text' ), &$total = null );


	/**
	 * Returns an list of product text strings matched by the filter.
	 *
	 * @param \Aimeos\MW\Criteria\Iface $filter Critera object which contains the filter conditions
	 * @return array Associative list of the product ID as key and the product text as value
	 */
	public function getTextList( \Aimeos\MW\Criteria\Iface $filter );
}
