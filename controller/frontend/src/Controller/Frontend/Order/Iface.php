<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Metaways Infosystems GmbH, 2014
 * @copyright Aimeos (aimeos.org), 2015-2018
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
	 * Creates and adds a new order for the given order base ID
	 *
	 * @param string $baseId Unique ID of the saved basket
	 * @param string $type Arbitrary order type (max. eight chars)
	 * @return \Aimeos\MShop\Order\Item\Iface Created order object
	 */
	public function addItem( $baseId, $type );


	/**
	 * Returns the filter for searching items
	 *
	 * @return \Aimeos\MW\Criteria\Iface Filter object
	 */
	public function createFilter();


	/**
	 * Returns the order item for the given ID
	 *
	 * @param string $id Unique order ID
	 * @param boolean $default Use default criteria to limit orders
	 * @return \Aimeos\MShop\Order\Item\Iface Order object
	 */
	public function getItem( $id, $default = true );


	/**
	 * Saves the modified order item
	 *
	 * @param \Aimeos\MShop\Order\Item\Iface $item Order object
	 * @return \Aimeos\MShop\Order\Item\Iface Saved order item
	 */
	public function saveItem( \Aimeos\MShop\Order\Item\Iface $item );


	/**
	 * Returns the order items based on the given filter that belong to the current user
	 *
	 * @param \Aimeos\MW\Criteria\Iface Filter object
	 * @param integer &$total|null Variable that will contain the total number of available items
	 * @return \Aimeos\MShop\Order\Item\Iface[] Associative list of IDs as keys and order objects as values
	 */
	public function searchItems( \Aimeos\MW\Criteria\Iface $filter, &$total = null );


	/**
	 * Blocks the resources listed in the order.
	 *
	 * Every order contains resources like products or redeemed coupon codes
	 * that must be blocked so they can't be used by another customer in a
	 * later order. This method reduces the the stock level of products, the
	 * counts of coupon codes and others.
	 *
	 * It's save to call this method multiple times for one order. In this case,
	 * the actions will be executed only once. All subsequent calls will do
	 * nothing as long as the resources haven't been unblocked in the meantime.
	 *
	 * You can also block and unblock resources several times. Please keep in
	 * mind that unblocked resources may be reused by other orders in the
	 * meantime. This can lead to an oversell of products!
	 *
	 * @param \Aimeos\MShop\Order\Item\Iface $orderItem Order item object
	 * @return void
	 */
	public function block( \Aimeos\MShop\Order\Item\Iface $orderItem );


	/**
	 * Frees the resources listed in the order.
	 *
	 * If customers created orders but didn't pay for them, the blocked resources
	 * like products and redeemed coupon codes must be unblocked so they can be
	 * ordered again or used by other customers. This method increased the stock
	 * level of products, the counts of coupon codes and others.
	 *
	 * It's save to call this method multiple times for one order. In this case,
	 * the actions will be executed only once. All subsequent calls will do
	 * nothing as long as the resources haven't been blocked in the meantime.
	 *
	 * You can also unblock and block resources several times. Please keep in
	 * mind that unblocked resources may be reused by other orders in the
	 * meantime. This can lead to an oversell of products!
	 *
	 * @param \Aimeos\MShop\Order\Item\Iface $orderItem Order item object
	 * @return void
	 */
	public function unblock( \Aimeos\MShop\Order\Item\Iface $orderItem );


	/**
	 * Blocks or frees the resources listed in the order if necessary.
	 *
	 * After payment status updates, the resources like products or coupon
	 * codes listed in the order must be blocked or unblocked. This method
	 * cares about executing the appropriate action depending on the payment
	 * status.
	 *
	 * It's save to call this method multiple times for one order. In this case,
	 * the actions will be executed only once. All subsequent calls will do
	 * nothing as long as the payment status hasn't changed in the meantime.
	 *
	 * @param \Aimeos\MShop\Order\Item\Iface $orderItem Order item object
	 * @return void
	 */
	public function update( \Aimeos\MShop\Order\Item\Iface $orderItem );
}
