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
 * Default implementation of the order frontend controller.
 *
 * @package Controller
 * @subpackage Frontend
 */
class Standard
	extends \Aimeos\Controller\Frontend\Base
	implements Iface, \Aimeos\Controller\Frontend\Common\Iface
{
	/**
	 * Creates and adds a new order for the given order base ID
	 *
	 * @param string $baseId Unique ID of the saved basket
	 * @param string $type Arbitrary order type (max. eight chars)
	 * @return \Aimeos\MShop\Order\Item\Iface Created order object
	 */
	public function addItem( $baseId, $type )
	{
		$total = 0;
		$context = $this->getContext();
		$manager = \Aimeos\MShop\Factory::createManager( $context, 'order' );

		/** controller/frontend/order/limit-seconds
		 * Order limitation time frame in seconds
		 *
		 * Creating new orders is limited to avoid abuse and mitigate denial of
		 * service attacks. Within the configured time frame, only one order
		 * item can be created per order base item. All orders for the order
		 * base item within the last X seconds are counted.  If there's already
		 * one available, an error message will be shown to the user instead of
		 * creating the new order item.
		 *
		 * @param integer Number of seconds to check order items within
		 * @since 2017.05
		 * @category Developer
		 * @see controller/frontend/basket/limit-count
		 * @see controller/frontend/basket/limit-seconds
		 */
		$seconds = $context->getConfig()->get( 'controller/frontend/order/limit-seconds', 300 );

		$search = $manager->createSearch();
		$expr = [
			$search->compare( '==', 'order.baseid', $baseId ),
			$search->compare( '>=', 'order.ctime', date( 'Y-m-d H:i:s', time() - $seconds ) ),
		];
		$search->setConditions( $search->combine( '&&', $expr ) );
		$search->setSlice( 0, 0 );

		$manager->searchItems( $search, [], $total );

		if( $total > 0 ) {
			throw new \Aimeos\Controller\Frontend\Order\Exception( sprintf( 'The order has already been created' ) );
		}

		$item = $manager->createItem()->setBaseId( $baseId )->setType( $type );
		return $manager->saveItem( $item );
	}


	/**
	 * Returns the filter for searching items
	 *
	 * @return \Aimeos\MW\Criteria\Iface Filter object
	 */
	public function createFilter()
	{
		return \Aimeos\MShop\Factory::createManager( $this->getContext(), 'order' )->createSearch( true );
	}


	/**
	 * Returns the order item for the given ID
	 *
	 * @param string $id Unique order ID
	 * @param boolean $default Use default criteria to limit orders
	 * @return \Aimeos\MShop\Order\Item\Iface Order object
	 */
	public function getItem( $id, $default = true )
	{
		$context = $this->getContext();
		$manager = \Aimeos\MShop\Factory::createManager( $context, 'order' );

		$search = $manager->createSearch( true );
		$expr = [
			$search->compare( '==', 'order.id', $id ),
			$search->getConditions(),
		];

		if( $default !== false ) {
			$expr[] = $search->compare( '==', 'order.editor', $context->getEditor() );
		}

		$search->setConditions( $search->combine( '&&', $expr ) );

		$items = $manager->searchItems( $search );

		if( ( $item = reset( $items ) ) !== false ) {
			return $item;
		}

		throw new \Aimeos\Controller\Frontend\Order\Exception( sprintf( 'No order item for ID "%1$s" found', $id ) );
	}


	/**
	 * Saves the modified order item
	 *
	 * @param \Aimeos\MShop\Order\Item\Iface $item Order object
	 * @return \Aimeos\MShop\Order\Item\Iface Saved order item
	 */
	public function saveItem( \Aimeos\MShop\Order\Item\Iface $item )
	{
		$manager = \Aimeos\MShop\Factory::createManager( $this->getContext(), 'order' );
		return $manager->saveItem( $item );
	}


	/**
	 * Returns the order items based on the given filter that belong to the current user
	 *
	 * @param \Aimeos\MW\Criteria\Iface Filter object
	 * @param integer|null &$total Variable that will contain the total number of available items
	 * @return \Aimeos\MShop\Order\Item\Iface[] Associative list of IDs as keys and order objects as values
	 */
	public function searchItems( \Aimeos\MW\Criteria\Iface $filter, &$total = null )
	{
		$context = $this->getContext();
		$manager = \Aimeos\MShop\Factory::createManager( $context, 'order' );

		$expr = [
			$filter->getConditions(),
			$filter->compare( '==', 'order.base.customerid', $context->getUserId() ),
		];
		$filter->setConditions( $filter->combine( '&&', $expr ) );

		return $manager->searchItems( $filter, [], $total );
	}


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
	 */
	public function block( \Aimeos\MShop\Order\Item\Iface $orderItem )
	{
		\Aimeos\Controller\Common\Order\Factory::createController( $this->getContext() )->block( $orderItem );
	}


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
	 */
	public function unblock( \Aimeos\MShop\Order\Item\Iface $orderItem )
	{
		\Aimeos\Controller\Common\Order\Factory::createController( $this->getContext() )->unblock( $orderItem );
	}


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
	 */
	public function update( \Aimeos\MShop\Order\Item\Iface $orderItem )
	{
		\Aimeos\Controller\Common\Order\Factory::createController( $this->getContext() )->update( $orderItem );
	}
}
