<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2018
 * @package Controller
 * @subpackage Frontend
 */


namespace Aimeos\Controller\Frontend\Subscription;


/**
 * Interface for subscription frontend controllers
 *
 * @package Controller
 * @subpackage Frontend
 */
interface Iface
{
	/**
	 * Cancels an active subscription
	 *
	 * @param string $id Unique subscription ID
	 * @return \Aimeos\MShop\Subscription\Item\Iface Canceled subscription item
	 */
	public function cancel( $id );

	/**
	 * Adds generic condition for filtering
	 *
	 * @param string $operator Comparison operator, e.g. "==", "!=", "<", "<=", ">=", ">", "=~", "~="
	 * @param string $key Search key defined by the subscription manager, e.g. "subscription.status"
	 * @param array|string $value Value or list of values to compare to
	 * @return \Aimeos\Controller\Frontend\Subscription\Iface Subscription controller for fluent interface
	 * @since 2019.04
	 */
	public function compare( $operator, $key, $value );

	/**
	 * Returns the subscription for the given subscription ID
	 *
	 * @param string $id Unique subscription ID
	 * @return \Aimeos\MShop\Subscription\Item\Iface Subscription item including the referenced domains items
	 * @since 2019.04
	 */
	public function get( $id );

	/**
	 * Returns the available interval attribute items
	 *
	 * @return \Aimeos\MShop\Attribute\Item\Iface[] Associative list of intervals as keys and interval attribute items as values
	 */
	public function getIntervals();

	/**
	 * Parses the given array and adds the conditions to the list of conditions
	 *
	 * @param array $conditions List of conditions, e.g. ['>' => ['subscription.interval' => 'P0Y1M0W0D']]
	 * @return \Aimeos\Controller\Frontend\Subscription\Iface Subscription controller for fluent interface
	 * @since 2019.04
	 */
	public function parse( array $conditions );

	/**
	 * Saves the modified subscription item
	 *
	 * @param \Aimeos\MShop\Subscription\Item\Iface $item Subscription object
	 * @return \Aimeos\MShop\Subscription\Item\Iface Saved subscription item
	 */
	public function save( \Aimeos\MShop\Subscription\Item\Iface $item );

	/**
	 * Returns the subscriptions filtered by the previously assigned conditions
	 *
	 * @param integer &$total Parameter where the total number of found subscriptions will be stored in
	 * @return \Aimeos\MShop\Subscription\Item\Iface[] Ordered list of subscription items
	 * @since 2019.04
	 */
	public function search( &$total = null );

	/**
	 * Sets the start value and the number of returned subscription items for slicing the list of found subscription items
	 *
	 * @param integer $start Start value of the first subscription item in the list
	 * @param integer $limit Number of returned subscription items
	 * @return \Aimeos\Controller\Frontend\Subscription\Iface Subscription controller for fluent interface
	 * @since 2019.04
	 */
	public function slice( $start, $limit );

	/**
	 * Sets the sorting of the result list
	 *
	 * @param string|null $key Sorting key of the result list like "interval", null for no sorting
	 * @return \Aimeos\Controller\Frontend\Subscription\Iface Subscription controller for fluent interface
	 * @since 2019.04
	 */
	public function sort( $key = null );
}
