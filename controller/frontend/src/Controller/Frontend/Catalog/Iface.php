<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2015-2018
 * @package Controller
 * @subpackage Frontend
 */


namespace Aimeos\Controller\Frontend\Catalog;


/**
 * Interface for catalog frontend controllers
 *
 * @package Controller
 * @subpackage Frontend
 */
interface Iface
{
	/**
	 * Adds generic condition for filtering attributes
	 *
	 * @param string $operator Comparison operator, e.g. "==", "!=", "<", "<=", ">=", ">", "=~", "~="
	 * @param string $key Search key defined by the catalog manager, e.g. "catalog.status"
	 * @param array|string $value Value or list of values to compare to
	 * @return \Aimeos\Controller\Frontend\Catalog\Iface Catalog controller for fluent interface
	 * @since 2019.04
	 */
	public function compare( $operator, $key, $value );

	/**
	 * Returns the category for the given catalog code
	 *
	 * @param string $code Unique catalog code
	 * @return \Aimeos\MShop\Catalog\Item\Iface Catalog item including the referenced domains items
	 * @since 2019.04
	 */
	public function find( $code );

	/**
	 * Returns the category for the given catalog ID
	 *
	 * @param string $id Unique catalog ID
	 * @return \Aimeos\MShop\Catalog\Item\Iface Catalog item including the referenced domains items
	 * @since 2019.04
	 */
	public function get( $id );

	/**
	 * Returns the list of categories up to the root node including the node given by its ID
	 *
	 * @param integer $id Current category ID
	 * @return \Aimeos\MShop\Catalog\Item\Iface[] Associative list of categories
	 * @since 2017.03
	 */
	public function getPath( $id );

	/**
	 * Returns the categories filtered by the previously assigned conditions
	 *
	 * @param string[] $domains Domain names of items that are associated to the categories and should be fetched too
	 * @param integer $level Constant from \Aimeos\MW\Tree\Manager\Base, e.g. LEVEL_ONE, LEVEL_LIST or LEVEL_TREE
	 * @return \Aimeos\MShop\Catalog\Item\Iface Category tree
	 * @since 2019.04
	 */
	public function getTree( $level = \Aimeos\MW\Tree\Manager\Base::LEVEL_TREE );

	/**
	 * Parses the given array and adds the conditions to the list of conditions
	 *
	 * @param array $conditions List of conditions, e.g. ['>' => ['catalog.status' => 0]]
	 * @return \Aimeos\Controller\Frontend\Catalog\Iface Catalog controller for fluent interface
	 * @since 2019.04
	 */
	public function parse( array $conditions );

	/**
	 * Sets the catalog ID of node that is used as root node
	 *
	 * @param string|null $id Catalog ID
	 * @return \Aimeos\Controller\Frontend\Catalog\Iface Catalog controller for fluent interface
	 * @since 2019.04
	 */
	public function root( $id );

	/**
	 * Returns the categories filtered by the previously assigned conditions
	 *
	 * @param int &$total Parameter where the total number of found categories will be stored in
	 * @return \Aimeos\MShop\Catalog\Item\Iface[] Ordered list of catalog items
	 * @since 2019.10
	 */
	public function search( &$total = null );

	/**
	 * Sets the referenced domains that will be fetched too when retrieving items
	 *
	 * @param array $domains Domain names of the referenced items that should be fetched too
	 * @return \Aimeos\Controller\Frontend\Catalog\Iface Catalog controller for fluent interface
	 * @since 2019.04
	 */
	public function uses( array $domains );

	/**
	 * Limits categories returned to only visible ones depending on the given category IDs
	 *
	 * @param array $catIds List of category IDs
	 * @return \Aimeos\Controller\Frontend\Catalog\Iface Catalog controller for fluent interface
	 */
	public function visible( array $catIds );
}
