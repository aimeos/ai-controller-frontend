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
	 * Returns the filter for searching items
	 *
	 * @return \Aimeos\MW\Criteria\Iface Filter object
	 */
	public function createFilter();

	/**
	 * Returns the available interval attribute items
	 *
	 * @return \Aimeos\MShop\Attribute\Item\Iface[] Associative list of intervals as keys and interval attribute items as values
	 */
	public function getIntervals();

	/**
	 * Returns the subscription item for the given ID
	 *
	 * @param string $id Unique subscription ID
	 * @return \Aimeos\MShop\Subscription\Item\Iface Subscription object
	 */
	public function getItem( $id );

	/**
	 * Saves the modified subscription item
	 *
	 * @param \Aimeos\MShop\Subscription\Item\Iface $item Subscription object
	 * @return \Aimeos\MShop\Subscription\Item\Iface Saved subscription item
	 */
	public function saveItem( \Aimeos\MShop\Subscription\Item\Iface $item );

	/**
	 * Returns the subscription items based on the given filter that belong to the current user
	 *
	 * @param \Aimeos\MW\Criteria\Iface Filter object
	 * @param integer &$total|null Variable that will contain the total number of available items
	 * @return \Aimeos\MShop\Subscription\Item\Iface[] Associative list of IDs as keys and subscription objects as values
	 */
	public function searchItems( \Aimeos\MW\Criteria\Iface $filter, &$total = null );
}
