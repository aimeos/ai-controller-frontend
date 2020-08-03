<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2020
 * @package Controller
 * @subpackage Frontend
 */


namespace Aimeos\Controller\Frontend\Review\Decorator;


/**
 * Base for review frontend controller decorators
 *
 * @package Controller
 * @subpackage Frontend
 */
abstract class Base
	extends \Aimeos\Controller\Frontend\Base
	implements \Aimeos\Controller\Frontend\Common\Decorator\Iface, \Aimeos\Controller\Frontend\Review\Iface
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

		$iface = \Aimeos\Controller\Frontend\Review\Iface::class;
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
	 * @param string $key Search key defined by the review manager, e.g. "review.status"
	 * @param array|string $value Value or list of values to compare to
	 * @return \Aimeos\Controller\Frontend\Review\Iface Review controller for fluent interface
	 * @since 2020.10
	 */
	public function compare( string $operator, string $key, $value ) : \Aimeos\Controller\Frontend\Review\Iface
	{
		$this->controller->compare( $operator, $key, $value );
		return $this;
	}


	/**
	 * Deletes the review item for the given ID
	 *
	 * @param string $id Unique review ID
	 * @return \Aimeos\Controller\Frontend\Review\Iface Review controller for fluent interface
	 * @since 2020.10
	 */
	public function delete( string $id ) : \Aimeos\Controller\Frontend\Review\Iface
	{
		$this->controller->delete( $id );
		return $this;
	}


	/**
	 * Sets the review domain for filtering
	 *
	 * @param string $domain Domain ("customer" or "product") of the reviewed items
	 * @return \Aimeos\Controller\Frontend\Review\Iface Review controller for fluent interface
	 * @since 2020.10
	 */
	public function domain( string $domain ) : \Aimeos\Controller\Frontend\Review\Iface
	{
		$this->controller->domain( $domain );
		return $this;
	}


	/**
	 * Restricts the ratings to a specific domain item
	 *
	 * @param string $domain Domain the ratings belong to ("customer" or "product")
	 * @param string $refid Id of the item the ratings belong to
	 * @return \Aimeos\Controller\Frontend\Review\Iface Review controller for fluent interface
	 * @since 2020.10
	 */
	public function for( string $domain, string $refid ) : \Aimeos\Controller\Frontend\Review\Iface
	{
		$this->controller->for( $domain, $refid );
		return $this;
	}


	/**
	 * Returns the review for the given review ID
	 *
	 * @param string $id Unique review ID
	 * @return \Aimeos\MShop\Review\Item\Iface Review item including the referenced domains items
	 * @since 2020.10
	 */
	public function get( string $id ) : \Aimeos\MShop\Review\Item\Iface
	{
		return $this->controller->get( $id );
	}


	/**
	 * Returns the reviews for the logged-in user
	 *
	 * @param int &$total Parameter where the total number of found reviews will be stored in
	 * @return \Aimeos\Map Ordered list of review items implementing \Aimeos\MShop\Review\Item\Iface
	 * @since 2020.10
	 */
	public function list( int &$total = null ) : \Aimeos\Map
	{
		return $this->controller->list( $total );
	}


	/**
	 * Parses the given array and adds the conditions to the list of conditions
	 *
	 * @param array $conditions List of conditions, e.g. ['>' => ['review.rating' => 3]]
	 * @return \Aimeos\Controller\Frontend\Review\Iface Review controller for fluent interface
	 * @since 2020.10
	 */
	public function parse( array $conditions ) : \Aimeos\Controller\Frontend\Review\Iface
	{
		$this->controller->parse( $conditions );
		return $this;
	}


	/**
	 * Saves the modified review item
	 *
	 * @param \Aimeos\MShop\Review\Item\Iface $item Review object
	 * @return \Aimeos\MShop\Review\Item\Iface Saved review item
	 */
	public function save( \Aimeos\MShop\Review\Item\Iface $item ) : \Aimeos\MShop\Review\Item\Iface
	{
		return $this->controller->save( $item );
	}


	/**
	 * Returns the reviews filtered by the previously assigned conditions
	 *
	 * @param int &$total Parameter where the total number of found reviews will be stored in
	 * @return \Aimeos\Map Ordered list of items implementing \Aimeos\MShop\Review\Item\Iface
	 * @since 2020.10
	 */
	public function search( int &$total = null ) : \Aimeos\Map
	{
		return $this->controller->search( $total );
	}


	/**
	 * Sets the start value and the number of returned review items for slicing the list of found review items
	 *
	 * @param int $start Start value of the first review item in the list
	 * @param int $limit Number of returned review items
	 * @return \Aimeos\Controller\Frontend\Review\Iface Review controller for fluent interface
	 * @since 2020.10
	 */
	public function slice( int $start, int $limit ) : \Aimeos\Controller\Frontend\Review\Iface
	{
		$this->controller->slice( $start, $limit );
		return $this;
	}


	/**
	 * Sets the sorting of the result list
	 *
	 * @param string|null $key Sorting key of the result list like "interval", null for no sorting
	 * @return \Aimeos\Controller\Frontend\Review\Iface Review controller for fluent interface
	 * @since 2020.10
	 */
	public function sort( string $key = null ) : \Aimeos\Controller\Frontend\Review\Iface
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
	public function setObject( \Aimeos\Controller\Frontend\Iface $object ) : \Aimeos\Controller\Frontend\Iface
	{
		parent::setObject( $object );

		$this->controller->setObject( $object );

		return $this;
	}


	/**
	 * Returns the frontend controller
	 *
	 * @return \Aimeos\Controller\Frontend\Review\Iface Frontend controller object
	 */
	protected function getController() : \Aimeos\Controller\Frontend\Review\Iface
	{
		return $this->controller;
	}
}
