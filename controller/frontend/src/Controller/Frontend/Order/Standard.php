<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Metaways Infosystems GmbH, 2014
 * @copyright Aimeos (aimeos.org), 2015-2021
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
	private $domains = [];
	private $manager;
	private $filter;
	private $item;


	/**
	 * Initializes the controller
	 *
	 * @param \Aimeos\MShop\Context\Item\Iface $context Common MShop context object
	 */
	public function __construct( \Aimeos\MShop\Context\Item\Iface $context )
	{
		parent::__construct( $context );

		$this->manager = \Aimeos\MShop::create( $context, 'order' );
		$this->item = $this->manager->create();

		$this->filter = $this->manager->filter( true );
		$this->addExpression( $this->filter->compare( '==', 'order.base.customerid', $context->getUserId() ) );
		$this->addExpression( $this->filter->getConditions() );
	}


	/**
	 * Clones objects in controller and resets values
	 */
	public function __clone()
	{
		$this->item = clone $this->item;
	}


	/**
	 * Adds the values to the order object (not yet stored)
	 *
	 * @param string $baseId ID of the stored basket
	 * @param array $values Values added to the order item (new or existing) like "order.type"
	 * @return \Aimeos\Controller\Frontend\Order\Iface Order controller for fluent interface
	 * @since 2019.04
	 */
	public function add( string $baseId, array $values = [] ) : Iface
	{
		$this->item = $this->item->fromArray( $values )->setBaseId( $baseId );
		return $this;
	}


	/**
	 * Adds generic condition for filtering orders
	 *
	 * @param string $operator Comparison operator, e.g. "==", "!=", "<", "<=", ">=", ">", "=~", "~="
	 * @param string $key Search key defined by the order manager, e.g. "order.type"
	 * @param array|string $value Value or list of values to compare to
	 * @return \Aimeos\Controller\Frontend\Order\Iface Order controller for fluent interface
	 * @since 2019.04
	 */
	public function compare( string $operator, string $key, $value ) : Iface
	{
		$this->addExpression( $this->filter->compare( $operator, $key, $value ) );
		return $this;
	}


	/**
	 * Returns the order for the given order ID
	 *
	 * @param string $id Unique order ID
	 * @param bool $default Use default criteria to limit orders
	 * @return \Aimeos\MShop\Order\Item\Iface Order item object
	 * @since 2019.04
	 */
	public function get( string $id, bool $default = true ) : \Aimeos\MShop\Order\Item\Iface
	{
		return $this->manager->get( $id, $this->domains, $default );
	}


	/**
	 * Parses the given array and adds the conditions to the list of conditions
	 *
	 * @param array $conditions List of conditions, e.g. ['&&' => [['>' => ['order.statuspayment' => 0]], ['==' => ['order.type' => 'web']]]]
	 * @return \Aimeos\Controller\Frontend\Order\Iface Order controller for fluent interface
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
	 * Updates the given order item in the storage
	 *
	 * @param \Aimeos\MShop\Order\Item\Iface $orderItem Order item object
	 * @return \Aimeos\MShop\Order\Item\Iface $orderItem Saved order item object
	 * @since 2019.04
	 */
	public function save( \Aimeos\MShop\Order\Item\Iface $orderItem ) : \Aimeos\MShop\Order\Item\Iface
	{
		return $this->manager->save( $orderItem );
	}

	/**
	 * Returns the orders filtered by the previously assigned conditions
	 *
	 * @param int &$total Parameter where the total number of found attributes will be stored in
	 * @return \Aimeos\Map Ordered list of order items implementing \Aimeos\MShop\Order\Item\Iface
	 * @since 2019.04
	 */
	public function search( int &$total = null ) : \Aimeos\Map
	{
		$this->filter->setConditions( $this->filter->and( $this->getConditions() ) );
		$this->filter->setSortations( $this->getSortations() );

		return $this->manager->search( $this->filter, $this->domains, $total );
	}


	/**
	 * Sets the start value and the number of returned orders for slicing the list of found orders
	 *
	 * @param int $start Start value of the first order in the list
	 * @param int $limit Number of returned orders
	 * @return \Aimeos\Controller\Frontend\Order\Iface Order controller for fluent interface
	 * @since 2019.04
	 */
	public function slice( int $start, int $limit ) : Iface
	{
		$maxsize = $this->getContext()->config()->get( 'controller/frontend/common/max-size', 500 );
		$this->filter->slice( $start, min( $limit, $maxsize ) );
		return $this;
	}


	/**
	 * Sets the sorting of the result list
	 *
	 * @param string|null $key Sorting of the result list like "-order.id", null for no sorting
	 * @return \Aimeos\Controller\Frontend\Order\Iface Order controller for fluent interface
	 * @since 2019.04
	 */
	public function sort( string $key = null ) : Iface
	{
		$list = $this->splitKeys( $key );

		foreach( $list as $sortkey )
		{
			$direction = ( $sortkey[0] === '-' ? '-' : '+' );
			$this->addExpression( $this->filter->sort( $direction, ltrim( $sortkey, '+-' ) ) );
		}

		return $this;
	}


	/**
	 * Saves the modified order item in the storage and blocks the stock and coupon codes
	 *
	 * @return \Aimeos\MShop\Order\Item\Iface New or updated order item object
	 * @since 2019.04
	 */
	public function store() : \Aimeos\MShop\Order\Item\Iface
	{
		$this->checkLimit( $this->item->getBaseId() );

		$cntl = \Aimeos\Controller\Common\Order\Factory::create( $this->getContext() );
		$this->item = $this->manager->save( $this->item );

		return $cntl->block( $this->item );
	}


	/**
	 * Sets the referenced domains that will be fetched too when retrieving items
	 *
	 * @param array $domains Domain names of the referenced items that should be fetched too
	 * @return \Aimeos\Controller\Frontend\Order\Iface Order controller for fluent interface
	 * @since 2019.04
	 */
	public function uses( array $domains ) : Iface
	{
		$this->domains = $domains;
		return $this;
	}


	/**
	 * Checks if more orders than allowed have been created by the user
	 *
	 * @param string $baseId Unique ID of the order base item (basket)
	 * @return \Aimeos\Controller\Frontend\Order\Iface Order controller for fluent interface
	 * @throws \Aimeos\Controller\Frontend\Order\Exception If limit is exceeded
	 */
	protected function checkLimit( string $baseId ) : Iface
	{
		$config = $this->getContext()->getConfig();

		/** controller/frontend/order/limit-count
		 * Maximum number of invoices within the time frame
		 *
		 * Creating new invoices is limited to avoid abuse and mitigate denial of
		 * service attacks. The number of invoices created within the time frame
		 * configured by "controller/frontend/invoices/limit-seconds" are counted
		 * before a new invoice of the same user (either logged in or identified
		 * by the IP address) is created. If the number of invoices is higher than
		 * the configured value, an error message will be shown to the user
		 * instead of creating a new invoice.
		 *
		 * @param integer Number of orders allowed within the time frame
		 * @since 2020.10
		 * @category Developer
		 * @see controller/frontend/order/limit-seconds
		 * @see controller/frontend/basket/limit-count
		 * @see controller/frontend/basket/limit-seconds
		 */
		 $count = $config->get( 'controller/frontend/order/limit-count', 3 );

		/** controller/frontend/order/limit-seconds
		 * Invoice limitation time frame in seconds
		 *
		 * Creating new invoices is limited to avoid abuse and mitigate denial of
		 * service attacks. Within the configured time frame, only one invoice
		 * item can be created per order base item. All invoices for the order
		 * base item within the last X seconds are counted.  If there's already
		 * one available, an error message will be shown to the user instead of
		 * creating the new order item.
		 *
		 * @param integer Number of seconds to check order items within
		 * @since 2017.05
		 * @category Developer
		 * @see controller/frontend/order/limit-count
		 * @see controller/frontend/basket/limit-count
		 * @see controller/frontend/basket/limit-seconds
		 */
		$seconds = $config->get( 'controller/frontend/order/limit-seconds', 900 );

		$search = $this->manager->filter()->slice( 0, 0 );
		$search->setConditions( $search->and( [
			$search->compare( '==', 'order.baseid', $baseId ),
			$search->compare( '>=', 'order.ctime', date( 'Y-m-d H:i:s', time() - $seconds ) ),
		] ) );

		$total = 0;
		$this->manager->search( $search, [], $total );

		if( $total >= $count ) {
			throw new \Aimeos\Controller\Frontend\Order\Exception( sprintf( 'The order has already been created' ) );
		}

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
