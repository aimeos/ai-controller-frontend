<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2017-2018
 * @package Controller
 * @subpackage Frontend
 */


namespace Aimeos\Controller\Frontend\Locale\Decorator;


/**
 * Base for locale frontend controller decorators
 *
 * @package Controller
 * @subpackage Frontend
 */
abstract class Base
	extends \Aimeos\Controller\Frontend\Base
	implements \Aimeos\Controller\Frontend\Common\Decorator\Iface, \Aimeos\Controller\Frontend\Locale\Iface
{
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

		$iface = \Aimeos\Controller\Frontend\Locale\Iface::class;
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
	public function __call( $name, array $param )
	{
		return @call_user_func_array( array( $this->controller, $name ), $param );
	}


	/**
	 * Clones objects in controller and resets values
	 */
	public function __clone()
	{
		$this->controller = clone $this->controller;
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
		$this->controller->compare( $operator, $key, $value );
		return $this;
	}


	/**
	 * Returns the subscription for the given subscription ID
	 *
	 * @param string $id Unique subscription ID
	 * @return \Aimeos\MShop\Subscription\Item\Iface Subscription item including the referenced domains items
	 * @since 2019.04
	 */
	public function get( $id )
	{
		return $this->controller->get( $id );
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
		$this->controller->parse( $conditions );
		return $this;
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
		return $this->controller->search( $total );
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
		$this->controller->slice( $start, $limit );
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
		$this->controller->sort( $key );
		return $this;
	}


	/**
	 * Injects the reference of the outmost object
	 *
	 * @param \Aimeos\Controller\Frontend\Iface $object Reference to the outmost controller or decorator
	 * @return \Aimeos\Controller\Frontend\Iface Controller object for chaining method calls
	 */
	public function setObject( \Aimeos\Controller\Frontend\Iface $object )
	{
		parent::setObject( $object );

		$this->controller->setObject( $object );

		return $this;
	}


	/**
	 * Returns the frontend controller
	 *
	 * @return \Aimeos\Controller\Frontend\Locale\Iface Frontend controller object
	 */
	protected function getController()
	{
		return $this->controller;
	}
}
