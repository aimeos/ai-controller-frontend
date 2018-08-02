<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2018
 * @package Controller
 * @subpackage Frontend
 */


namespace Aimeos\Controller\Frontend\Subscription\Decorator;


/**
 * Base for subscription frontend controller decorators
 *
 * @package Controller
 * @subpackage Frontend
 */
abstract class Base
	extends \Aimeos\Controller\Frontend\Base
	implements \Aimeos\Controller\Frontend\Common\Decorator\Iface, \Aimeos\Controller\Frontend\Subscription\Iface
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
		\Aimeos\MW\Common\Base::checkClass( '\\Aimeos\\Controller\\Frontend\\Subscription\\Iface', $controller );

		$this->controller = $controller;

		parent::__construct( $context );
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
	 * Cancels an active subscription
	 *
	 * @param string $id Unique subscription ID
	 * @return \Aimeos\MShop\Subscription\Item\Iface Canceled subscription item
	 */
	public function cancel( $id )
	{
		return $this->controller->cancel( $id );
	}


	/**
	 * Returns the filter for searching items
	 *
	 * @return \Aimeos\MW\Criteria\Iface Filter object
	 */
	public function createFilter()
	{
		return $this->controller->createFilter();
	}


	/**
	 * Returns the available interval attribute items
	 *
	 * @return \Aimeos\MShop\Attribute\Item\Iface[] Associative list of intervals as keys and interval attribute items as values
	 */
	public function getIntervals()
	{
		return $this->controller->getIntervals();
	}


	/**
	 * Returns the subscription item for the given ID
	 *
	 * @param string $id Unique subscription ID
	 * @return \Aimeos\MShop\Subscription\Item\Iface Subscription object
	 */
	public function getItem( $id )
	{
		return $this->controller->getItem( $id );
	}


	/**
	 * Saves the modified subscription item
	 *
	 * @param \Aimeos\MShop\Subscription\Item\Iface $item Subscription object
	 * @return \Aimeos\MShop\Subscription\Item\Iface Saved subscription item
	 */
	public function saveItem( \Aimeos\MShop\Subscription\Item\Iface $item )
	{
		return $this->controller->saveItem( $item );
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
		return $this->controller->searchItems( $filter, $total );
	}


	/**
	 * Returns the frontend controller
	 *
	 * @return \Aimeos\Controller\Frontend\Subscription\Iface Frontend controller object
	 */
	protected function getController()
	{
		return $this->controller;
	}
}
