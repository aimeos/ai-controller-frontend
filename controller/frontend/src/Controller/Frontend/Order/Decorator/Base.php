<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2016-2021
 * @package Controller
 * @subpackage Frontend
 */


namespace Aimeos\Controller\Frontend\Order\Decorator;


/**
 * Base for order frontend controller decorators
 *
 * @package Controller
 * @subpackage Frontend
 */
abstract class Base
	extends \Aimeos\Controller\Frontend\Base
	implements \Aimeos\Controller\Frontend\Common\Decorator\Iface, \Aimeos\Controller\Frontend\Order\Iface
{
	use \Aimeos\Controller\Frontend\Common\Decorator\Traits;


	private $controller;


	/**
	 * Initializes the controller decorator.
	 *
	 * @param \Aimeos\Controller\Frontend\Iface $controller Controller object
	 * @param \Aimeos\MShop\Context\Item\Iface $context Context object with required objects
	 */
	public function __construct( \Aimeos\Controller\Frontend\Iface $controller, \Aimeos\MShop\Context\Item\Iface $context )
	{
		parent::__construct( $context );

		$iface = \Aimeos\Controller\Frontend\Order\Iface::class;
		$this->controller = \Aimeos\MW\Common\Base::checkClass( $iface, $controller );
	}


	/**
	 * Passes unknown methods to wrapped objects.
	 *
	 * @param string $name Name of the method
	 * @param array $param List of method parameter
	 * @return mixed Returns the value of the called method
	 * @throws \Aimeos\Controller\Frontend\Exception If method call failed
	 */
	public function __call( string $name, array $param )
	{
		return @call_user_func_array( array( $this->controller, $name ), $param );
	}


	/**
	 * Adds the values to the order object (not yet stored)
	 *
	 * @param string $baseId ID of the stored basket
	 * @param array $values Values added to the order item (new or existing) like "order.type"
	 * @return \Aimeos\Controller\Frontend\Order\Iface Order controller for fluent interface
	 * @since 2019.04
	 */
	public function add( string $baseId, array $values = [] ) : \Aimeos\Controller\Frontend\Order\Iface
	{
		$this->controller->add( $baseId, $values );
		return $this;
	}


	/**
	 * Adds generic condition for filtering orders
	 *
	 * @param string $operator Comparison operator, e.g. "==", "!=", "<", "<=", ">=", ">", "=~", "~="
	 * @param string $key Search key defined by the order manager, e.g. "order.status"
	 * @param array|string $value Value or list of values to compare to
	 * @return \Aimeos\Controller\Frontend\Order\Iface Order controller for fluent interface
	 * @since 2019.04
	 */
	public function compare( string $operator, string $key, $value ) : \Aimeos\Controller\Frontend\Order\Iface
	{
		$this->controller->compare( $operator, $key, $value );
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
		return $this->controller->get( $id, $default );
	}


	/**
	 * Parses the given array and adds the conditions to the list of conditions
	 *
	 * @param array $conditions List of conditions, e.g. ['&&' => [['>' => ['order.statuspayment' => 0]], ['==' => ['order.type' => 'web']]]]
	 * @return \Aimeos\Controller\Frontend\Order\Iface Order controller for fluent interface
	 * @since 2019.04
	 */
	public function parse( array $conditions ) : \Aimeos\Controller\Frontend\Order\Iface
	{
		$this->controller->parse( $conditions );
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
		return $this->controller->save( $orderItem );
	}


	/**
	 * Returns the orders filtered by the previously assigned conditions
	 *
	 * @param int &$total Parameter where the total number of found attributes will be stored in
	 * @return \Aimeos\Map Ordered list of items implementing \Aimeos\MShop\Order\Item\Iface
	 * @since 2019.04
	 */
	public function search( int &$total = null ) : \Aimeos\Map
	{
		return $this->controller->search( $total );
	}


	/**
	 * Sets the start value and the number of returned orders for slicing the list of found orders
	 *
	 * @param int $start Start value of the first order in the list
	 * @param int $limit Number of returned orders
	 * @return \Aimeos\Controller\Frontend\Order\Iface Order controller for fluent interface
	 * @since 2019.04
	 */
	public function slice( int $start, int $limit ) : \Aimeos\Controller\Frontend\Order\Iface
	{
		$this->controller->slice( $start, $limit );
		return $this;
	}


	/**
	 * Sets the sorting of the result list
	 *
	 * @param string|null $key Sorting of the result list like "-order.id", null for no sorting
	 * @return \Aimeos\Controller\Frontend\Order\Iface Order controller for fluent interface
	 * @since 2019.04
	 */
	public function sort( string $key = null ) : \Aimeos\Controller\Frontend\Order\Iface
	{
		$this->controller->sort( $key );
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
		return $this->controller->store();
	}


	/**
	 * Sets the referenced domains that will be fetched too when retrieving items
	 *
	 * @param array $domains Domain names of the referenced items that should be fetched too
	 * @return \Aimeos\Controller\Frontend\Order\Iface Order controller for fluent interface
	 * @since 2020.04
	 */
	public function uses( array $domains ) : \Aimeos\Controller\Frontend\Order\Iface
	{
		$this->controller->uses( $domains );
		return $this;
	}


	/**
	 * Injects the reference of the outmost object
	 *
	 * @param \Aimeos\Controller\Frontend\Iface $object Reference to the outmost controller or decorator
	 * @return \Aimeos\Controller\Frontend\Iface Controller object for chaining method calls
	 */
	public function setObject( \Aimeos\Controller\Frontend\Iface $object ) : \Aimeos\Controller\Frontend\Iface
	{
		parent::setObject( $object );

		$this->controller->setObject( $object );

		return $this;
	}


	/**
	 * Returns the frontend controller
	 *
	 * @return \Aimeos\Controller\Frontend\Iface Frontend controller object
	 */
	protected function getController() : \Aimeos\Controller\Frontend\Iface
	{
		return $this->controller;
	}
}
