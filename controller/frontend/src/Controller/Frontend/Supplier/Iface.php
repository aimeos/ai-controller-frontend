<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2017-2018
 * @package Controller
 * @subpackage Frontend
 */


namespace Aimeos\Controller\Frontend\Supplier;


/**
 * Interface for supplier frontend controllers
 *
 * @package Controller
 * @subpackage Frontend
 */
interface Iface
{
	/**
	 * Adds generic condition for filtering
	 *
	 * @param string $operator Comparison operator, e.g. "==", "!=", "<", "<=", ">=", ">", "=~", "~="
	 * @param string $key Search key defined by the supplier manager, e.g. "supplier.status"
	 * @param array|string $value Value or list of values to compare to
	 * @return \Aimeos\Controller\Frontend\Supplier\Iface Supplier controller for fluent interface
	 * @since 2019.04
	 */
	public function compare( $operator, $key, $value );

	/**
	 * Returns the supplier for the given supplier code
	 *
	 * @param string $code Unique supplier code
	 * @param string[] $domains Domain names of items that are associated with the suppliers and that should be fetched too
	 * @return \Aimeos\MShop\Supplier\Item\Iface Supplier item including the referenced domains items
	 * @since 2019.04
	 */
	public function find( $code );

	/**
	 * Returns the supplier for the given supplier ID
	 *
	 * @param string $id Unique supplier ID
	 * @param string[] $domains Domain names of items that are associated with the suppliers and that should be fetched too
	 * @return \Aimeos\MShop\Supplier\Item\Iface Supplier item including the referenced domains items
	 * @since 2019.04
	 */
	public function get( $id );

	/**
	 * Adds a filter to return only items containing a reference to the given ID
	 *
	 * @param string $domain Domain name of the referenced item, e.g. "attribute"
	 * @param string|null $type Type code of the reference, e.g. "variant" or null for all types
	 * @param string|null $refId ID of the referenced item of the given domain or null for all references
	 * @return \Aimeos\Controller\Frontend\Product\Iface Product controller for fluent interface
	 * @since 2019.10
	 */
	public function has( $domain, $type = null, $refId = null );

	/**
	 * Parses the given array and adds the conditions to the list of conditions
	 *
	 * @param array $conditions List of conditions, e.g. ['>' => ['supplier.dateback' => '2000-01-01 00:00:00']]
	 * @return \Aimeos\Controller\Frontend\Supplier\Iface Supplier controller for fluent interface
	 * @since 2019.04
	 */
	public function parse( array $conditions );

	/**
	 * Returns the suppliers filtered by the previously assigned conditions
	 *
	 * @param integer &$total Parameter where the total number of found suppliers will be stored in
	 * @return \Aimeos\MShop\Supplier\Item\Iface[] Ordered list of supplier items
	 * @since 2019.04
	 */
	public function search( &$total = null );

	/**
	 * Sets the start value and the number of returned supplier items for slicing the list of found supplier items
	 *
	 * @param integer $start Start value of the first supplier item in the list
	 * @param integer $limit Number of returned supplier items
	 * @return \Aimeos\Controller\Frontend\Supplier\Iface Supplier controller for fluent interface
	 * @since 2019.04
	 */
	public function slice( $start, $limit );

	/**
	 * Sets the sorting of the result list
	 *
	 * @param string|null $key Sorting key of the result list like "supplier.label", null for no sorting
	 * @return \Aimeos\Controller\Frontend\Supplier\Iface Supplier controller for fluent interface
	 * @since 2019.04
	 */
	public function sort( $key = null );

	/**
	 * Sets the referenced domains that will be fetched too when retrieving items
	 *
	 * @param array $domains Domain names of the referenced items that should be fetched too
	 * @return \Aimeos\Controller\Frontend\Supplier\Iface Supplier controller for fluent interface
	 * @since 2019.04
	 */
	public function uses( array $domains );
}
