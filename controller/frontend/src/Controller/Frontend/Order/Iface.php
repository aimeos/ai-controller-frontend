<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Metaways Infosystems GmbH, 2014
 * @copyright Aimeos (aimeos.org), 2015-2021
 * @package Controller
 * @subpackage Frontend
 */


namespace Aimeos\Controller\Frontend\Order;


/**
 * Interface for order frontend controllers.
 *
 * @package Controller
 * @subpackage Frontend
 */
interface Iface
{
	/**
	 * Adds the values to the order object (not yet stored)
	 *
	 * @param string $baseId ID of the stored basket
	 * @param array $values Values added to the order item (new or existing) like "order.type"
	 * @return \Aimeos\Controller\Frontend\Order\Iface Order controller for fluent interface
	 * @since 2019.04
	 */
	public function add( string $baseId, array $values = [] ) : Iface;

	/**
	 * Adds generic condition for filtering orders
	 *
	 * @param string $operator Comparison operator, e.g. "==", "!=", "<", "<=", ">=", ">", "=~", "~="
	 * @param string $key Search key defined by the order manager, e.g. "order.status"
	 * @param array|string $value Value or list of values to compare to
	 * @return \Aimeos\Controller\Frontend\Order\Iface Order controller for fluent interface
	 * @since 2019.04
	 */
	public function compare( string $operator, string $key, $value ) : Iface;

	/**
	 * Returns the order for the given order ID
	 *
	 * @param string $id Unique order ID
	 * @param bool $default Use default criteria to limit orders
	 * @return \Aimeos\MShop\Order\Item\Iface Order item object
	 * @since 2019.04
	 */
	public function get( string $id, bool $default = true ) : \Aimeos\MShop\Order\Item\Iface;

	/**
	 * Parses the given array and adds the conditions to the list of conditions
	 *
	 * @param array $conditions List of conditions, e.g. ['&&' => [['>' => ['order.statuspayment' => 0]], ['==' => ['order.type' => 'web']]]]
	 * @return \Aimeos\Controller\Frontend\Order\Iface Order controller for fluent interface
	 * @since 2019.04
	 */
	public function parse( array $conditions ) : Iface;

	/**
	 * Updates the given order item in the storage
	 *
	 * @param \Aimeos\MShop\Order\Item\Iface $orderItem Order item object
	 * @return \Aimeos\MShop\Order\Item\Iface $orderItem Saved order item object
	 * @since 2019.04
	 */
	public function save( \Aimeos\MShop\Order\Item\Iface $orderItem ) : \Aimeos\MShop\Order\Item\Iface;

	/**
	 * Returns the orders filtered by the previously assigned conditions
	 *
	 * @param int &$total Parameter where the total number of found attributes will be stored in
	 * @return \Aimeos\Map Ordered list of items implementing \Aimeos\MShop\Order\Item\Iface
	 * @since 2019.04
	 */
	public function search( int &$total = null ) : \Aimeos\Map;

	/**
	 * Sets the start value and the number of returned orders for slicing the list of found orders
	 *
	 * @param int $start Start value of the first order in the list
	 * @param int $limit Number of returned orders
	 * @return \Aimeos\Controller\Frontend\Order\Iface Order controller for fluent interface
	 * @since 2019.04
	 */
	public function slice( int $start, int $limit ) : Iface;

	/**
	 * Sets the sorting of the result list
	 *
	 * @param string|null $key Sorting of the result list like "-order.id", null for no sorting
	 * @return \Aimeos\Controller\Frontend\Order\Iface Order controller for fluent interface
	 * @since 2019.04
	 */
	public function sort( string $key = null ) : Iface;

	/**
	 * Saves the modified order item in the storage and blocks the stock and coupon codes
	 *
	 * @return \Aimeos\MShop\Order\Item\Iface New or updated order item object
	 * @since 2019.04
	 */
	public function store() : \Aimeos\MShop\Order\Item\Iface;

	/**
	 * Sets the referenced domains that will be fetched too when retrieving items
	 *
	 * @param array $domains Domain names of the referenced items that should be fetched too
	 * @return \Aimeos\Controller\Frontend\Order\Iface Order controller for fluent interface
	 * @since 2020.04
	 */
	public function uses( array $domains ) : Iface;
}
