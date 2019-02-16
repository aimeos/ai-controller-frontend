<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2016-2018
 * @package Controller
 * @subpackage Frontend
 */


namespace Aimeos\Controller\Frontend\Catalog\Decorator;


/**
 * Base for catalog frontend controller decorators
 *
 * @package Controller
 * @subpackage Frontend
 */
abstract class Base
	extends \Aimeos\Controller\Frontend\Base
	implements \Aimeos\Controller\Frontend\Common\Decorator\Iface, \Aimeos\Controller\Frontend\Catalog\Iface
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
		$iface = \Aimeos\Controller\Frontend\Catalog\Iface::class;
		$this->controller = \Aimeos\MW\Common\Base::checkClass( $iface, $controller );

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
	 * @param string $key Search key defined by the catalog manager, e.g. "catalog.status"
	 * @param array|string $value Value or list of values to compare to
	 * @return \Aimeos\Controller\Frontend\Catalog\Iface Catalog controller for fluent interface
	 * @since 2019.04
	 */
	public function compare( $operator, $key, $value )
	{
		$this->controller->compare( $operator, $key, $value );
		return $this;
	}


	/**
	 * Returns the category for the given catalog code
	 *
	 * @param string $code Unique catalog code
	 * @param string[] $domains Domain names of items that are associated to the categories and should be fetched too
	 * @return \Aimeos\MShop\Catalog\Item\Iface Catalog item including the referenced domains items
	 * @since 2019.04
	 */
	public function find( $code, array $domains = ['media', 'text'] )
	{
		return $this->controller->find( $code, $domains );
	}


	/**
	 * Returns the category for the given catalog ID
	 *
	 * @param string $id Unique catalog ID
	 * @param string[] $domains Domain names of items that are associated with the category and should be fetched too
	 * @return \Aimeos\MShop\Catalog\Item\Iface Catalog item including the referenced domains items
	 * @since 2019.04
	 */
	public function get( $id, array $domains = ['media', 'text'] )
	{
		return $this->controller->get( $id, $domains );
	}


	/**
	 * Returns the list of categories up to the root node including the node given by its ID
	 *
	 * @param integer $id Current category ID
	 * @param string[] $domains Domain names of items that are associated to the categories and should be fetched too
	 * @return \Aimeos\MShop\Catalog\Item\Iface[] Associative list of categories
	 * @since 2017.03
	 */
	public function getPath( $id, array $domains = ['text', 'media'] )
	{
		return $this->controller->getPath( $id, $domains );
	}


	/**
	 * Returns the categories filtered by the previously assigned conditions
	 *
	 * @param string[] $domains Domain names of items that are associated to the categories and should be fetched too
	 * @param integer $level Constant from \Aimeos\MW\Tree\Manager\Base, e.g. LEVEL_ONE, LEVEL_LIST or LEVEL_TREE
	 * @return \Aimeos\MShop\Catalog\Item\Iface Category tree
	 * @since 2019.04
	 */
	public function getTree( array $domains = ['media', 'text'], $level = \Aimeos\MW\Tree\Manager\Base::LEVEL_TREE )
	{
		return $this->controller->getTree( $domains, $level );
	}


	/**
	 * Parses the given array and adds the conditions to the list of conditions
	 *
	 * @param array $conditions List of conditions, e.g. ['>' => ['catalog.status' => 0]]
	 * @return \Aimeos\Controller\Frontend\Catalog\Iface Catalog controller for fluent interface
	 * @since 2019.04
	 */
	public function parse( array $conditions )
	{
		$this->controller->parse( $conditions );
		return $this;
	}


	/**
	 * Sets the catalog ID of node that is used as root node
	 *
	 * @param string|null $id Catalog ID
	 * @return \Aimeos\Controller\Frontend\Catalog\Iface Catalog controller for fluent interface
	 * @since 2019.04
	 */
	public function root( $id )
	{
		$this->controller->root( $id );
		return $this;
	}


	/**
	 * Limits categories returned to only visible ones depending on the given category IDs
	 *
	 * @param array $catIds List of category IDs
	 * @return \Aimeos\Controller\Frontend\Catalog\Iface Catalog controller for fluent interface
	 */
	public function visible( array $catIds )
	{
		$this->controller->visible( $catIds );
		return $this;
	}


	/**
	 * Returns the frontend controller
	 *
	 * @return \Aimeos\Controller\Frontend\Catalog\Iface Frontend controller object
	 */
	protected function getController()
	{
		return $this->controller;
	}
}
