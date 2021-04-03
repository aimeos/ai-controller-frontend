<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2021
 * @package Controller
 * @subpackage Frontend
 */


namespace Aimeos\Controller\Frontend\Site;


/**
 * Interface for site frontend controllers
 *
 * @package Controller
 * @subpackage Frontend
 */
interface Iface
{
	public const ONE = 1;
	public const LIST = 2;
	public const TREE = 3;

	/**
	 * Adds generic condition for filtering attributes
	 *
	 * @param string $operator Comparison operator, e.g. "==", "!=", "<", "<=", ">=", ">", "=~", "~="
	 * @param string $key Search key defined by the site manager, e.g. "site.status"
	 * @param array|string $value Value or list of values to compare to
	 * @return \Aimeos\Controller\Frontend\Site\Iface Site controller for fluent interface
	 * @since 2021.04
	 */
	public function compare( string $operator, string $key, $value ) : Iface;

	/**
	 * Returns the category for the given site code
	 *
	 * @param string $code Unique site code
	 * @return \Aimeos\MShop\Locale\Item\Site\Iface Site item including the referenced domains items
	 * @since 2021.04
	 */
	public function find( string $code ) : \Aimeos\MShop\Locale\Item\Site\Iface;

	/**
	 * Returns the category for the given site ID
	 *
	 * @param string $id Unique site ID
	 * @return \Aimeos\MShop\Locale\Item\Site\Iface Site item including the referenced domains items
	 * @since 2021.04
	 */
	public function get( string $id ) : \Aimeos\MShop\Locale\Item\Site\Iface;

	/**
	 * Returns the list of sites up to the root node including the node given by its ID
	 *
	 * @param string $id Current category ID
	 * @return \Aimeos\MShop\Locale\Item\Site\Iface[] Associative list of sites
	 * @since 2021.04
	 */
	public function getPath( string $id );

	/**
	 * Returns the sites filtered by the previously assigned conditions
	 *
	 * @param int $level Tree level constant, e.g. ONE, LIST or TREE
	 * @return \Aimeos\MShop\Locale\Item\Site\Iface Site tree
	 * @since 2021.04
	 */
	public function getTree( int $level = Iface::TREE ) : \Aimeos\MShop\Locale\Item\Site\Iface;

	/**
	 * Parses the given array and adds the conditions to the list of conditions
	 *
	 * @param array $conditions List of conditions, e.g. ['>' => ['site.status' => 0]]
	 * @return \Aimeos\Controller\Frontend\Site\Iface Site controller for fluent interface
	 * @since 2021.04
	 */
	public function parse( array $conditions ) : Iface;

	/**
	 * Sets the site ID of node that is used as root node
	 *
	 * @param string|null $id Site ID
	 * @return \Aimeos\Controller\Frontend\Site\Iface Site controller for fluent interface
	 * @since 2021.04
	 */
	public function root( string $id = null ) : Iface;

	/**
	 * Returns the sites filtered by the previously assigned conditions
	 *
	 * @param int &$total Parameter where the total number of found sites will be stored in
	 * @return \Aimeos\Map Ordered list of items implementing \Aimeos\MShop\Locale\Item\Site\Iface
	 * @since 2021.04
	 */
	public function search( int &$total = null ) : \Aimeos\Map;

	/**
	 * Sets the start value and the number of returned products for slicing the list of found products
	 *
	 * @param int $start Start value of the first product in the list
	 * @param int $limit Number of returned products
	 * @return \Aimeos\Controller\Frontend\Site\Iface Site controller for fluent interface
	 * @since 2021.04
	 */
	 public function slice( int $start, int $limit ) : Iface;

	 /**
	  * Sets the sorting of the result list
	  *
	  * @param string|null $key Search key for sorting of the result list and null for no sorting
	  * @return \Aimeos\Controller\Frontend\Site\Iface Site controller for fluent interface
	  * @since 2021.04
	  */
	 public function sort( ?string $key = null ) : Iface;
}
