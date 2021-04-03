<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2021
 * @package Controller
 * @subpackage Frontend
 */


namespace Aimeos\Controller\Frontend\Site\Decorator;


/**
 * Base for site frontend controller decorators
 *
 * @package Controller
 * @subpackage Frontend
 */
abstract class Base
	extends \Aimeos\Controller\Frontend\Base
	implements \Aimeos\Controller\Frontend\Common\Decorator\Iface, \Aimeos\Controller\Frontend\Site\Iface
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

		$iface = \Aimeos\Controller\Frontend\Site\Iface::class;
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
	 * Adds generic condition for filtering attributes
	 *
	 * @param string $operator Comparison operator, e.g. "==", "!=", "<", "<=", ">=", ">", "=~", "~="
	 * @param string $key Search key defined by the site manager, e.g. "site.status"
	 * @param array|string $value Value or list of values to compare to
	 * @return \Aimeos\Controller\Frontend\Site\Iface Site controller for fluent interface
	 * @since 2021.04
	 */
	public function compare( string $operator, string $key, $value ) : \Aimeos\Controller\Frontend\Site\Iface
	{
		$this->controller->compare( $operator, $key, $value );
		return $this;
	}


	/**
	 * Returns the category for the given site code
	 *
	 * @param string $code Unique site code
	 * @return \Aimeos\MShop\Locale\Item\Site\Iface Site item
	 * @since 2021.04
	 */
	public function find( string $code ) : \Aimeos\MShop\Locale\Item\Site\Iface
	{
		return $this->controller->find( $code );
	}


	/**
	 * Returns the category for the given site ID
	 *
	 * @param string $id Unique site ID
	 * @return \Aimeos\MShop\Locale\Item\Site\Iface Site item
	 * @since 2021.04
	 */
	public function get( string $id ) : \Aimeos\MShop\Locale\Item\Site\Iface
	{
		return $this->controller->get( $id );
	}


	/**
	 * Returns the list of sites up to the root node including the node given by its ID
	 *
	 * @param string $id Current category ID
	 * @return \Aimeos\MShop\Locale\Item\Site\Iface[] Associative list of sites
	 * @since 2021.04
	 */
	public function getPath( string $id )
	{
		return $this->controller->getPath( $id );
	}


	/**
	 * Returns the sites filtered by the previously assigned conditions
	 *
	 * @param int $level Tree level constant, e.g. ONE, LIST or TREE
	 * @return \Aimeos\MShop\Locale\Item\Site\Iface Site tree
	 * @since 2021.04
	 */
	public function getTree( int $level = \Aimeos\Controller\Frontend\Site\Iface::TREE ) : \Aimeos\MShop\Locale\Item\Site\Iface
	{
		return $this->controller->getTree( $level );
	}


	/**
	 * Parses the given array and adds the conditions to the list of conditions
	 *
	 * @param array $conditions List of conditions, e.g. ['>' => ['site.status' => 0]]
	 * @return \Aimeos\Controller\Frontend\Site\Iface Site controller for fluent interface
	 * @since 2021.04
	 */
	public function parse( array $conditions ) : \Aimeos\Controller\Frontend\Site\Iface
	{
		$this->controller->parse( $conditions );
		return $this;
	}


	/**
	 * Sets the site ID of node that is used as root node
	 *
	 * @param string|null $id Site ID
	 * @return \Aimeos\Controller\Frontend\Site\Iface Site controller for fluent interface
	 * @since 2021.04
	 */
	public function root( string $id = null ) : \Aimeos\Controller\Frontend\Site\Iface
	{
		$this->controller->root( $id );
		return $this;
	}

	/**
	 * Returns the sites filtered by the previously assigned conditions
	 *
	 * @param int &$total Parameter where the total number of found sites will be stored in
	 * @return \Aimeos\Map Ordered list of items implementing \Aimeos\MShop\Locale\Item\Site\Iface
	 * @since 2021.04
	 */
	 public function search( int &$total = null ) : \Aimeos\Map
	 {
		return $this->controller->search( $total );
	 }


	/**
	 * Sets the start value and the number of returned products for slicing the list of found products
	 *
	 * @param int $start Start value of the first product in the list
	 * @param int $limit Number of returned products
	 * @return \Aimeos\Controller\Frontend\Site\Iface Site controller for fluent interface
	 * @since 2021.04
	 */
	public function slice( int $start, int $limit ) : \Aimeos\Controller\Frontend\Site\Iface
	{
		$this->controller->slice( $start, $limit );
		return $this;
	}


	/**
	 * Sets the sorting of the result list
	 *
	 * @param string|null $key Search key for sorting of the result list and null for no sorting
	 * @return \Aimeos\Controller\Frontend\Site\Iface Site controller for fluent interface
	 * @since 2021.04
	 */
	public function sort( ?string $key = null ) : \Aimeos\Controller\Frontend\Site\Iface
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
