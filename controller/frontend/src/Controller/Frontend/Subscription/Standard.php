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
	private $conditions = [];
	private $filter;
	private $manager;


	/**
	 * Common initialization for controller classes
	 *
	 * @param \Aimeos\MShop\Context\Item\Iface $context Common MShop context object
	 */
	public function __construct( \Aimeos\MShop\Context\Item\Iface $context )
	{
		parent::__construct( $context );

		$this->manager = \Aimeos\MShop::create( $context, 'subscription' );
		$this->filter = $this->manager->createSearch();
		$this->conditions[] = $this->filter->compare( '==', 'order.base.customerid', $context->getUserId() );
	}


	/**
	 * Clones objects in controller and resets values
	 */
	public function __clone()
	{
		$this->filter = clone $this->filter;
	}


	/**
	 * Cancels an active subscription
	 *
	 * @param string $id Unique subscription ID
	 * @return \Aimeos\MShop\Subscription\Item\Iface Canceled subscription item
	 */
	public function cancel( $id )
	{
		$item = $this->manager->getItem( $id );
		$item = $item->setDateEnd( $item->getDateNext() )
			->setReason( \Aimeos\MShop\Subscription\Item\Iface::REASON_CANCEL );

		return $this->manager->saveItem( $item );
	}


	/**
	 * Adds generic condition for filtering
	 *
	 * @param string $operator Comparison operator, e.g. "==", "!=", "<", "<=", ">=", ">", "=~", "~="
	 * @param string $key Search key defined by the subscription manager, e.g. "subscription.status"
	 * @param array|string $value Value or list of values to compare to
	 * @return \Aimeos\Controller\Frontend\Subscription\Iface Subscription controller for fluent interface
	 * @since 2019.04
	 */
	public function compare( $operator, $key, $value )
	{
		$this->conditions[] = $this->filter->compare( $operator, $key, $value );
		return $this;
	}


	/**
	 * Returns the subscription item for the given ID
	 *
	 * @param string $id Unique subscription ID
	 * @return \Aimeos\MShop\Subscription\Item\Iface Subscription object
	 */
	public function get( $id )
	{
		$context = $this->getContext();

		$filter = $this->manager->createSearch( true );
		$expr = [
			$filter->compare( '==', 'subscription.id', $id ),
			$filter->compare( '==', 'order.base.customerid', $context->getUserId() ),
			$filter->getConditions(),
		];
		$filter->setConditions( $filter->combine( '&&', $expr ) );

		$items = $this->manager->searchItems( $filter );

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
		$manager = \Aimeos\MShop::create( $this->getContext(), 'attribute' );

		$search = $manager->createSearch( true );
		$expr = array(
			$search->compare( '==', 'attribute.domain', 'product' ),
			$search->compare( '==', 'attribute.type', 'interval' ),
			$search->getConditions(),
		);
		$search->setConditions( $search->combine( '&&', $expr ) );
		$search->setSlice( 0, 10000 );

		$list = [];

		foreach( $manager->searchItems( $search, ['text'] ) as $attrItem ) {
			$list[$attrItem->getCode()] = $attrItem;
		}

		return $list;
	}


	/**
	 * Parses the given array and adds the conditions to the list of conditions
	 *
	 * @param array $conditions List of conditions, e.g. ['>' => ['subscription.interval' => 'P0Y1M0W0D']]
	 * @return \Aimeos\Controller\Frontend\Subscription\Iface Subscription controller for fluent interface
	 * @since 2019.04
	 */
	public function parse( array $conditions )
	{
		if( ($cond = $this->filter->toConditions( $conditions ) ) !== null ) {
			$this->conditions[] = $cond;
		}

		return $this;
	}


	/**
	 * Saves the modified subscription item
	 *
	 * @param \Aimeos\MShop\Subscription\Item\Iface $item Subscription object
	 * @return \Aimeos\MShop\Subscription\Item\Iface Saved subscription item
	 */
	public function save( \Aimeos\MShop\Subscription\Item\Iface $item )
	{
		return $this->manager->saveItem( $item );
	}

	/**
	 * Returns the subscriptions filtered by the previously assigned conditions
	 *
	 * @param integer &$total Parameter where the total number of found subscriptions will be stored in
	 * @return \Aimeos\MShop\Subscription\Item\Iface[] Ordered list of subscription items
	 * @since 2019.04
	 */
	public function search( &$total = null )
	{
		$this->filter->setConditions( $this->filter->combine( '&&', $this->conditions ) );
		return $this->manager->searchItems( $this->filter, [], $total );
	}


	/**
	 * Sets the start value and the number of returned subscription items for slicing the list of found subscription items
	 *
	 * @param integer $start Start value of the first subscription item in the list
	 * @param integer $limit Number of returned subscription items
	 * @return \Aimeos\Controller\Frontend\Subscription\Iface Subscription controller for fluent interface
	 * @since 2019.04
	 */
	public function slice( $start, $limit )
	{
		$this->filter->setSlice( $start, $limit );
		return $this;
	}


	/**
	 * Sets the sorting of the result list
	 *
	 * @param string|null $key Sorting key of the result list like "interval", null for no sorting
	 * @return \Aimeos\Controller\Frontend\Subscription\Iface Subscription controller for fluent interface
	 * @since 2019.04
	 */
	public function sort( $key = null )
	{
		$sort = [];
		$list = ( $key ? explode( ',', $key ) : [] );

		foreach( $list as $sortkey )
		{
			$direction = ( $sortkey[0] === '-' ? '-' : '+' );
			$sortkey = ltrim( $sortkey, '+-' );

			switch( $sortkey )
			{
				case 'interval':
					$sort[] = $this->filter->sort( $direction, 'subscription.interval' );
					break;
				default:
					$sort[] = $this->filter->sort( $direction, $sortkey );
			}
		}

		$this->filter->setSortations( $sort );
		return $this;
	}
}
