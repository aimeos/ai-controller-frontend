<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2020-2021
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
	 * Returns the aggregated count of products for the given key.
	 *
	 * @param string $key Search key to aggregate for, e.g. "review.rating"
	 * @param string|null $value Search key for aggregating the value column
	 * @param string|null $type Type of the aggregation, empty string for count or "sum"
	 * @return \Aimeos\Map Associative list of key values as key and the product count for this key as value
	 * @since 2020.10
	 */
	public function aggregate( string $key, string $value = null, string $type = null ) : \Aimeos\Map
	{
		return $this->controller->aggregate( $key, $value, $type );
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
	 * Returns a new rating item
	 *
	 * @param array $values Associative list of key/value pairs to initialize the item
	 * @return \Aimeos\MShop\Review\Item\Iface New review item
	 */
	public function create( array $values = [] ) : \Aimeos\MShop\Review\Item\Iface
	{
		return $this->controller->create( $values );
	}


	/**
	 * Deletes the review item for the given ID
	 *
	 * @param array|string $id Unique review ID or list of IDs
	 * @return \Aimeos\Controller\Frontend\Review\Iface Review controller for fluent interface
	 * @since 2020.10
	 */
	public function delete( $ids ) : \Aimeos\Controller\Frontend\Review\Iface
	{
		$this->controller->delete( $ids );
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
	 * Restricts the reviews to a specific domain item
	 *
	 * @param string $domain Domain the reviews belong to (e.g. "product")
	 * @param array|string|null $refid Id of the item the reviews belong to, list of or NULL for all reviews from the domain
	 * @return \Aimeos\Controller\Frontend\Review\Iface Review controller for fluent interface
	 * @since 2020.10
	 */
	 public function for( string $domain, $refid ) : \Aimeos\Controller\Frontend\Review\Iface
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
	 * @return \Aimeos\Controller\Frontend\Iface Frontend controller object
	 */
	protected function getController() : \Aimeos\Controller\Frontend\Iface
	{
		return $this->controller;
	}
}
