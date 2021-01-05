<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2020-2021
 * @package Controller
 * @subpackage Frontend
 */


namespace Aimeos\Controller\Frontend\Review;


/**
 * Interface for review frontend controllers
 *
 * @package Controller
 * @subpackage Frontend
 */
interface Iface
{
	/**
	 * Returns the aggregated count of products for the given key.
	 *
	 * @param string $key Search key to aggregate for, e.g. "review.rating"
	 * @param string|null $value Search key for aggregating the value column
	 * @param string|null $type Type of the aggregation, empty string for count or "sum"
	 * @return \Aimeos\Map Associative list of key values as key and the product count for this key as value
	 * @since 2020.10
	 */
	public function aggregate( string $key, string $value = null, string $type = null ) : \Aimeos\Map;

	/**
	 * Adds generic condition for filtering
	 *
	 * @param string $operator Comparison operator, e.g. "==", "!=", "<", "<=", ">=", ">", "=~", "~="
	 * @param string $key Search key defined by the review manager, e.g. "review.status"
	 * @param array|string $value Value or list of values to compare to
	 * @return \Aimeos\Controller\Frontend\Review\Iface Review controller for fluent interface
	 * @since 2020.10
	 */
	public function compare( string $operator, string $key, $value ) : Iface;

	/**
	 * Deletes the review item for the given ID
	 *
	 * @param array|string $id Unique review ID or list of IDs
	 * @return \Aimeos\Controller\Frontend\Review\Iface Review controller for fluent interface
	 * @since 2020.10
	 */
	public function delete( $ids ) : Iface;

	/**
	 * Sets the review domain for filtering
	 *
	 * @param string $domain Domain ("customer" or "product") of the reviewed items
	 * @return \Aimeos\Controller\Frontend\Review\Iface Review controller for fluent interface
	 * @since 2020.10
	 */
	public function domain( string $domain ) : Iface;

	/**
	 * Restricts the reviews to a specific domain item
	 *
	 * @param string $domain Domain the reviews belong to (e.g. "product")
	 * @param array|string|null $refid Id of the item the reviews belong to, list of or NULL for all reviews from the domain
	 * @return \Aimeos\Controller\Frontend\Review\Iface Review controller for fluent interface
	 * @since 2020.10
	 */
	 public function for( string $domain, $refid ) : Iface;

	/**
	 * Returns the review for the given review ID
	 *
	 * @param string $id Unique review ID
	 * @return \Aimeos\MShop\Review\Item\Iface Review item including the referenced domains items
	 * @since 2020.10
	 */
	public function get( string $id ) : \Aimeos\MShop\Review\Item\Iface;

	/**
	 * Returns the reviews for the logged-in user
	 *
	 * @param int &$total Parameter where the total number of found reviews will be stored in
	 * @return \Aimeos\Map Ordered list of review items implementing \Aimeos\MShop\Review\Item\Iface
	 * @since 2020.10
	 */
	public function list( int &$total = null ) : \Aimeos\Map;

	/**
	 * Parses the given array and adds the conditions to the list of conditions
	 *
	 * @param array $conditions List of conditions, e.g. ['>' => ['review.interval' => 'P0Y1M0W0D']]
	 * @return \Aimeos\Controller\Frontend\Review\Iface Review controller for fluent interface
	 * @since 2020.10
	 */
	public function parse( array $conditions ) : Iface;

	/**
	 * Saves the modified review item
	 *
	 * @param \Aimeos\MShop\Review\Item\Iface $item Review object
	 * @return \Aimeos\MShop\Review\Item\Iface Saved review item
	 */
	public function save( \Aimeos\MShop\Review\Item\Iface $item ) : \Aimeos\MShop\Review\Item\Iface;

	/**
	 * Returns the reviews filtered by the previously assigned conditions
	 *
	 * @param int &$total Parameter where the total number of found reviews will be stored in
	 * @return \Aimeos\Map Ordered list of items implementing \Aimeos\MShop\Review\Item\Iface
	 * @since 2020.10
	 */
	public function search( int &$total = null ) : \Aimeos\Map;

	/**
	 * Sets the start value and the number of returned review items for slicing the list of found review items
	 *
	 * @param int $start Start value of the first review item in the list
	 * @param int $limit Number of returned review items
	 * @return \Aimeos\Controller\Frontend\Review\Iface Review controller for fluent interface
	 * @since 2020.10
	 */
	public function slice( int $start, int $limit ) : Iface;

	/**
	 * Sets the sorting of the result list
	 *
	 * @param string|null $key Sorting key of the result list like "interval", null for no sorting
	 * @return \Aimeos\Controller\Frontend\Review\Iface Review controller for fluent interface
	 * @since 2020.10
	 */
	public function sort( string $key = null ) : Iface;
}
