<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2018
 * @package Controller
 * @subpackage Frontend
 */


namespace Aimeos\Controller\Frontend\Subscription;


/**
 * Default implementation of the subscription frontend controller.
 *
 * @package Controller
 * @subpackage Frontend
 */
class Standard
	extends \Aimeos\Controller\Frontend\Base
	implements Iface, \Aimeos\Controller\Frontend\Common\Iface
{
	/**
	 * Cancels an active subscription
	 *
	 * @param string $id Unique subscription ID
	 * @return \Aimeos\MShop\Subscription\Item\Iface Canceled subscription item
	 */
	public function cancel( $id )
	{
		$item = $this->getItem( $id );
		$item->setDateEnd( $item->getDateNext() );
		$item->setReason( \Aimeos\MShop\Subscription\Item\Iface::REASON_CANCEL );

		return $this->saveItem( $item );
	}


	/**
	 * Returns the filter for searching items
	 *
	 * @return \Aimeos\MW\Criteria\Iface Filter object
	 */
	public function createFilter()
	{
		return \Aimeos\MShop\Factory::createManager( $this->getContext(), 'subscription' )->createSearch();
	}


	/**
	 * Returns the subscription item for the given ID
	 *
	 * @param string $id Unique subscription ID
	 * @return \Aimeos\MShop\Subscription\Item\Iface Subscription object
	 */
	public function getItem( $id )
	{
		$context = $this->getContext();
		$manager = \Aimeos\MShop\Factory::createManager( $context, 'subscription' );

		$filter = $manager->createSearch();
		$expr = [
			$filter->compare( '==', 'subscription.id', $id ),
			$filter->compare( '==', 'order.base.customerid', $context->getUserId() ),
			$filter->getConditions(),
		];
		$filter->setConditions( $filter->combine( '&&', $expr ) );

		$items = $this->searchItems( $filter );

		if( ( $item = reset( $items ) ) === false )
		{
			$msg = 'Invalid subscription ID "%1$s" for customer ID "%2$s"';
			throw new \Aimeos\Controller\Frontend\Subscription\Exception( sprintf( $msg, $id, $context->getUserId() ) );
		}

		return $item;
	}


	/**
	 * Returns the available interval attribute items
	 *
	 * @return \Aimeos\MShop\Attribute\Item\Iface[] Associative list of intervals as keys and interval attribute items as values
	 */
	public function getIntervals()
	{
		$intervals = [];
		$manager = \Aimeos\MShop\Factory::createManager( $this->getContext(), 'attribute' );

		$search = $manager->createSearch( true );
		$expr = array(
			$search->getConditions(),
			$search->compare( '==', 'attribute.domain', 'product' ),
			$search->compare( '==', 'attribute.type.code', 'interval' ),
		);
		$search->setConditions( $search->combine( '&&', $expr ) );
		$search->setSlice( 0, 0x7fffffff );

		foreach( $manager->searchItems( $search, ['text'] ) as $attrItem ) {
			$intervals[$attrItem->getCode()] = $attrItem;
		}

		return $intervals;
	}

	/**
	 * Saves the modified subscription item
	 *
	 * @param \Aimeos\MShop\Subscription\Item\Iface $item Subscription object
	 * @return \Aimeos\MShop\Subscription\Item\Iface Saved subscription item
	 */
	public function saveItem( \Aimeos\MShop\Subscription\Item\Iface $item )
	{
		return \Aimeos\MShop\Factory::createManager( $this->getContext(), 'subscription' )->saveItem( $item );
	}


	/**
	 * Returns the subscription items based on the given filter that belong to the current user
	 *
	 * @param \Aimeos\MW\Criteria\Iface Filter object
	 * @param integer &$total|null Variable that will contain the total number of available items
	 * @return \Aimeos\MShop\Subscription\Item\Iface[] Associative list of IDs as keys and subscription objects as values
	 */
	public function searchItems( \Aimeos\MW\Criteria\Iface $filter, &$total = null )
	{
		$context = $this->getContext();
		$manager = \Aimeos\MShop\Factory::createManager( $context, 'subscription' );

		$expr = [
			$filter->compare( '==', 'order.base.customerid', $context->getUserId() ),
			$filter->getConditions(),
		];
		$filter->setConditions( $filter->combine( '&&', $expr ) );

		return $manager->searchItems( $filter, [], $total );
	}
}
