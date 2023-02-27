<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2018-2023
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
	/** controller/frontend/subscription/name
	 * Class name of the used subscription frontend controller implementation
	 *
	 * Each default frontend controller can be replace by an alternative imlementation.
	 * To use this implementation, you have to set the last part of the class
	 * name as configuration value so the controller factory knows which class it
	 * has to instantiate.
	 *
	 * For example, if the name of the default class is
	 *
	 *  \Aimeos\Controller\Frontend\Subscription\Standard
	 *
	 * and you want to replace it with your own version named
	 *
	 *  \Aimeos\Controller\Frontend\Subscription\Mysubscription
	 *
	 * then you have to set the this configuration option:
	 *
	 *  controller/frontend/subscription/name = Mysubscription
	 *
	 * The value is the last part of your own class name and it's case sensitive,
	 * so take care that the configuration value is exactly named like the last
	 * part of the class name.
	 *
	 * The allowed characters of the class name are A-Z, a-z and 0-9. No other
	 * characters are possible! You should always start the last part of the class
	 * name with an upper case character and continue only with lower case characters
	 * or numbers. Avoid chamel case names like "MySubscription"!
	 *
	 * @param string Last part of the class name
	 * @since 2018.04
	 * @category Developer
	 */

	/** controller/frontend/subscription/decorators/excludes
	 * Excludes decorators added by the "common" option from the subscription frontend controllers
	 *
	 * Decorators extend the functionality of a class by adding new aspects
	 * (e.g. log what is currently done), executing the methods of the underlying
	 * class only in certain conditions (e.g. only for logged in users) or
	 * modify what is returned to the caller.
	 *
	 * This option allows you to remove a decorator added via
	 * "controller/frontend/common/decorators/default" before they are wrapped
	 * around the frontend controller.
	 *
	 *  controller/frontend/subscription/decorators/excludes = array( 'decorator1' )
	 *
	 * This would remove the decorator named "decorator1" from the list of
	 * common decorators ("\Aimeos\Controller\Frontend\Common\Decorator\*") added via
	 * "controller/frontend/common/decorators/default" for the subscription frontend controller.
	 *
	 * @param array List of decorator names
	 * @since 2018.04
	 * @category Developer
	 * @see controller/frontend/common/decorators/default
	 * @see controller/frontend/subscription/decorators/global
	 * @see controller/frontend/subscription/decorators/local
	 */

	/** controller/frontend/subscription/decorators/global
	 * Adds a list of globally available decorators only to the subscription frontend controllers
	 *
	 * Decorators extend the functionality of a class by adding new aspects
	 * (e.g. log what is currently done), executing the methods of the underlying
	 * class only in certain conditions (e.g. only for logged in users) or
	 * modify what is returned to the caller.
	 *
	 * This option allows you to wrap global decorators
	 * ("\Aimeos\Controller\Frontend\Common\Decorator\*") around the frontend controller.
	 *
	 *  controller/frontend/subscription/decorators/global = array( 'decorator1' )
	 *
	 * This would add the decorator named "decorator1" defined by
	 * "\Aimeos\Controller\Frontend\Common\Decorator\Decorator1" only to the frontend controller.
	 *
	 * @param array List of decorator names
	 * @since 2018.04
	 * @category Developer
	 * @see controller/frontend/common/decorators/default
	 * @see controller/frontend/subscription/decorators/excludes
	 * @see controller/frontend/subscription/decorators/local
	 */

	/** controller/frontend/subscription/decorators/local
	 * Adds a list of local decorators only to the subscription frontend controllers
	 *
	 * Decorators extend the functionality of a class by adding new aspects
	 * (e.g. log what is currently done), executing the methods of the underlying
	 * class only in certain conditions (e.g. only for logged in users) or
	 * modify what is returned to the caller.
	 *
	 * This option allows you to wrap local decorators
	 * ("\Aimeos\Controller\Frontend\Subscription\Decorator\*") around the frontend controller.
	 *
	 *  controller/frontend/subscription/decorators/local = array( 'decorator2' )
	 *
	 * This would add the decorator named "decorator2" defined by
	 * "\Aimeos\Controller\Frontend\Catalog\Decorator\Decorator2" only to the frontend
	 * controller.
	 *
	 * @param array List of decorator names
	 * @since 2018.04
	 * @category Developer
	 * @see controller/frontend/common/decorators/default
	 * @see controller/frontend/subscription/decorators/excludes
	 * @see controller/frontend/subscription/decorators/global
	 */


	private array $domains = [];
	private \Aimeos\Base\Criteria\Iface $filter;
	private \Aimeos\MShop\Common\Manager\Iface $manager;


	/**
	 * Common initialization for controller classes
	 *
	 * @param \Aimeos\MShop\ContextIface $context Common MShop context object
	 */
	public function __construct( \Aimeos\MShop\ContextIface $context )
	{
		parent::__construct( $context );

		$this->manager = \Aimeos\MShop::create( $context, 'subscription' );
		$this->filter = $this->manager->filter();
		$this->addExpression( $this->filter->compare( '==', 'order.customerid', $context->user() ) );
	}


	/**
	 * Clones objects in controller
	 */
	public function __clone()
	{
		$this->filter = clone $this->filter;
		parent::__clone();
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

		$filter = $this->manager->filter( null )->add( [
			'order.customerid' => $userId,
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
