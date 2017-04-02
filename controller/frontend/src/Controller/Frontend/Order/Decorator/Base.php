<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2016
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
	private $controller;


	/**
	 * Initializes the controller decorator.
	 *
	 * @param \Aimeos\Controller\Frontend\Iface $controller Controller object
	 * @param \Aimeos\MShop\Context\Item\Iface $context Context object with required objects
	 */
	public function __construct( \Aimeos\Controller\Frontend\Iface $controller, \Aimeos\MShop\Context\Item\Iface $context )
	{
		$iface = '\Aimeos\Controller\Frontend\Order\Iface';
		if( !( $controller instanceof $iface ) )
		{
			$msg = sprintf( 'Class "%1$s" does not implement interface "%2$s"', get_class( $controller ), $iface );
			throw new \Aimeos\Controller\Frontend\Exception( $msg );
		}

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
	 * Creates and adds a new order for the given order base ID
	 *
	 * @param string $baseId Unique ID of the saved basket
	 * @param string $type Arbitrary order type (max. eight chars)
	 * @return \Aimeos\MShop\Order\Item\Iface Created order object
	 */
	public function addItem( $baseId, $type )
	{
		return $this->controller->addItem( $baseId, $type );
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
	 * Returns the order item for the given ID
	 *
	 * @param string $id Unique order ID
	 * @return \Aimeos\MShop\Order\Item\Iface Order object
	 */
	public function getItem( $id )
	{
		return $this->controller->getItem( $id );
	}


	/**
	 * Returns the order items based on the given filter that belong to the current user
	 *
	 * @param \Aimeos\MW\Criteria\Iface Filter object
	 * @param integer &$total|null Variable that will contain the total number of available items
	 * @return \Aimeos\MShop\Order\Item\Iface[] Associative list of IDs as keys and order objects as values
	 */
	public function searchItems( \Aimeos\MW\Criteria\Iface $filter, &$total = null )
	{
		return $this->controller->searchItems( $filter, $total );
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
	 * @return void
	 */
	public function block( \Aimeos\MShop\Order\Item\Iface $orderItem )
	{
		$this->getController()->block( $orderItem );
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
	 * @return void
	 */
	public function unblock( \Aimeos\MShop\Order\Item\Iface $orderItem )
	{
		$this->getController()->unblock( $orderItem );
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
	 * @return void
	 */
	public function update( \Aimeos\MShop\Order\Item\Iface $orderItem )
	{
		$this->getController()->update( $orderItem );
	}


	/**
	 * Creates a new order from the given basket.
	 *
	 * Saves the given basket to the storage including the addresses, coupons,
	 * products, services, etc. and creates/stores a new order item for that
	 * order.
	 *
	 * @param \Aimeos\MShop\Order\Item\Base\Iface $basket Basket object to be stored
	 * @return \Aimeos\MShop\Order\Item\Iface Order item that belongs to the stored basket
	 * @deprecated 2017.04 Use store() from basket controller instead
	 */
	public function store( \Aimeos\MShop\Order\Item\Base\Iface $basket )
	{
		return $this->getController()->store( $basket );
	}


	/**
	 * Returns the frontend controller
	 *
	 * @return \Aimeos\Controller\Frontend\Order\Iface Frontend controller object
	 */
	protected function getController()
	{
		return $this->controller;
	}
}
