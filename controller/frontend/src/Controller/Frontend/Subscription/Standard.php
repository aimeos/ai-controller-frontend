<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2018-2022
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
	private $domains = [];
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
		$this->filter = $this->manager->filter();
		$this->addExpression( $this->filter->compare( '==', 'order.base.customerid', $context->user() ) );
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
	public function cancel( string $id ) : \Aimeos\MShop\Subscription\Item\Iface
	{
		$item = $this->manager->get( $id );
		$item = $item->setDateEnd( $item->getDateNext() ?: date( 'Y-m-d' ) )
			->setReason( \Aimeos\MShop\Subscription\Item\Iface::REASON_CANCEL );

		return $this->manager->save( $item );
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
	public function compare( string $operator, string $key, $value ) : Iface
	{
		$this->addExpression( $this->filter->compare( $operator, $key, $value ) );
		return $this;
	}


	/**
	 * Returns the subscription item for the given ID
	 *
	 * @param string $id Unique subscription ID
	 * @return \Aimeos\MShop\Subscription\Item\Iface Subscription object
	 */
	public function get( string $id ) : \Aimeos\MShop\Subscription\Item\Iface
	{
		$userId = $this->context()->user();

		$filter = $this->manager->filter( true )->add( [
			'order.base.customerid' => $userId,
			'subscription.id' => $id
		] );

		return $this->manager->search( $filter, $this->domains )->first( function() use ( $id, $userId ) {
			$msg = 'Invalid subscription ID "%1$s" for customer ID "%2$s"';
			throw new \Aimeos\Controller\Frontend\Subscription\Exception( sprintf( $msg, $id, $userId ) );
		} );
	}


	/**
	 * Returns the available interval attribute items
	 *
	 * @return \Aimeos\Map Associative list of intervals as keys and items implementing \Aimeos\MShop\Attribute\Item\Iface
	 */
	public function getIntervals() : \Aimeos\Map
	{
		$manager = \Aimeos\MShop::create( $this->context(), 'attribute' );

		$search = $manager->filter( true )->add( [
			'attribute.domain' => 'product',
			'attribute.type' => 'interval'
		] )->slice( 0, 10000 );

		return $manager->search( $search, ['text'] )->col( null, 'attribute.code' );
	}


	/**
	 * Parses the given array and adds the conditions to the list of conditions
	 *
	 * @param array $conditions List of conditions, e.g. ['>' => ['subscription.interval' => 'P0Y1M0W0D']]
	 * @return \Aimeos\Controller\Frontend\Subscription\Iface Subscription controller for fluent interface
	 * @since 2019.04
	 */
	public function parse( array $conditions ) : Iface
	{
		if( ( $cond = $this->filter->parse( $conditions ) ) !== null ) {
			$this->addExpression( $cond );
		}

		return $this;
	}


	/**
	 * Saves the modified subscription item
	 *
	 * @param \Aimeos\MShop\Subscription\Item\Iface $item Subscription object
	 * @return \Aimeos\MShop\Subscription\Item\Iface Saved subscription item
	 */
	public function save( \Aimeos\MShop\Subscription\Item\Iface $item ) : \Aimeos\MShop\Subscription\Item\Iface
	{
		return $this->manager->save( $item );
	}

	/**
	 * Returns the subscriptions filtered by the previously assigned conditions
	 *
	 * @param int &$total Parameter where the total number of found subscriptions will be stored in
	 * @return \Aimeos\Map Ordered list of subscription items implementing \Aimeos\MShop\Subscription\Item\Iface
	 * @since 2019.04
	 */
	public function search( int &$total = null ) : \Aimeos\Map
	{
		$this->filter->setSortations( $this->getSortations() );
		$this->filter->setConditions( $this->filter->and( $this->getConditions() ) );

		return $this->manager->search( $this->filter, $this->domains, $total );
	}


	/**
	 * Sets the start value and the number of returned subscription items for slicing the list of found subscription items
	 *
	 * @param int $start Start value of the first subscription item in the list
	 * @param int $limit Number of returned subscription items
	 * @return \Aimeos\Controller\Frontend\Subscription\Iface Subscription controller for fluent interface
	 * @since 2019.04
	 */
	public function slice( int $start, int $limit ) : Iface
	{
		$maxsize = $this->context()->config()->get( 'controller/frontend/common/max-size', 500 );
		$this->filter->slice( $start, min( $limit, $maxsize ) );
		return $this;
	}


	/**
	 * Sets the sorting of the result list
	 *
	 * @param string|null $key Sorting key of the result list like "interval", null for no sorting
	 * @return \Aimeos\Controller\Frontend\Subscription\Iface Subscription controller for fluent interface
	 * @since 2019.04
	 */
	public function sort( string $key = null ) : Iface
	{
		$list = $this->splitKeys( $key );

		foreach( $list as $sortkey )
		{
			$direction = ( $sortkey[0] === '-' ? '-' : '+' );
			$sortkey = ltrim( $sortkey, '+-' );

			switch( $sortkey )
			{
				case 'interval':
					$this->addExpression( $this->filter->sort( $direction, 'subscription.interval' ) ); break;
				default:
					$this->addExpression( $this->filter->sort( $direction, $sortkey ) );
			}
		}

		return $this;
	}


	/**
	 * Sets the referenced domains that will be fetched too when retrieving items
	 *
	 * @param array $domains Domain names of the referenced items that should be fetched too
	 * @return \Aimeos\Controller\Frontend\Subscription\Iface Subscription controller for fluent interface
	 * @since 2022.04
	 */
	public function uses( array $domains ) : Iface
	{
		$this->domains = $domains;
		return $this;
	}


	/**
	 * Returns the manager used by the controller
	 *
	 * @return \Aimeos\MShop\Common\Manager\Iface Manager object
	 */
	protected function getManager() : \Aimeos\MShop\Common\Manager\Iface
	{
		return $this->manager;
	}
}
