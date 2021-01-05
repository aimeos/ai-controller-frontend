<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2017-2021
 * @package Controller
 * @subpackage Frontend
 */


namespace Aimeos\Controller\Frontend\Stock;


/**
 * Interface for stock frontend controllers
 *
 * @package Controller
 * @subpackage Frontend
 */
interface Iface
{
	/**
	 * Adds the IDs of the products for filtering
	 *
	 * @param array|string $ids IDs of the products
	 * @return \Aimeos\Controller\Frontend\Stock\Iface Stock controller for fluent interface
	 * @since 2021.01
	 */
	public function product( $ids ) : Iface;

	/**
	 * Adds generic condition for filtering
	 *
	 * @param string $operator Comparison operator, e.g. "==", "!=", "<", "<=", ">=", ">", "=~", "~="
	 * @param string $key Search key defined by the stock manager, e.g. "stock.dateback"
	 * @param array|string $value Value or list of values to compare to
	 * @return \Aimeos\Controller\Frontend\Stock\Iface Stock controller for fluent interface
	 * @since 2019.04
	 */
	public function compare( string $operator, string $key, $value ) : Iface;

	/**
	 * Returns the stock item for the given stock ID
	 *
	 * @param string $id Unique stock ID
	 * @return \Aimeos\MShop\Stock\Item\Iface Stock item
	 * @since 2019.04
	 */
	public function get( string $id ) : \Aimeos\MShop\Stock\Item\Iface;

	/**
	 * Parses the given array and adds the conditions to the list of conditions
	 *
	 * @param array $conditions List of conditions, e.g. ['>' => ['stock.dateback' => '2000-01-01 00:00:00']]
	 * @return \Aimeos\Controller\Frontend\Stock\Iface Stock controller for fluent interface
	 * @since 2019.04
	 */
	public function parse( array $conditions ) : Iface;

	/**
	 * Returns the stock items filtered by the previously assigned conditions
	 *
	 * @param int &$total Parameter where the total number of found stock items will be stored in
	 * @return \Aimeos\Map Ordered list of items implementing \Aimeos\MShop\Stock\Item\Iface
	 * @since 2019.04
	 */
	public function search( int &$total = null ) : \Aimeos\Map;

	/**
	 * Sets the start value and the number of returned stock items for slicing the list of found stock items
	 *
	 * @param int $start Start value of the first stock item in the list
	 * @param int $limit Number of returned stock items
	 * @return \Aimeos\Controller\Frontend\Stock\Iface Stock controller for fluent interface
	 * @since 2019.04
	 */
	public function slice( int $start, int $limit ) : Iface;

	/**
	 * Sets the sorting of the result list
	 *
	 * @param string|null $key Sorting of the result list like "stock.type", null for no sorting
	 * @return \Aimeos\Controller\Frontend\Stock\Iface Stock controller for fluent interface
	 * @since 2019.04
	 */
	public function sort( string $key = null ) : Iface;

	/**
	 * Adds stock types for filtering
	 *
	 * @param array|string $types Stock type codes
	 * @return \Aimeos\Controller\Frontend\Stock\Iface Stock controller for fluent interface
	 * @since 2019.04
	 */
	public function type( $types ) : Iface;
}
