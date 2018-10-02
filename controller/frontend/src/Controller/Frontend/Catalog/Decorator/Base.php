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
		\Aimeos\MW\Common\Base::checkClass( '\\Aimeos\\Controller\\Frontend\\Catalog\\Iface', $controller );

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
	 * Returns the default catalog filter
	 *
	 * @param boolean True to add default criteria, e.g. status > 0
	 * @return \Aimeos\MW\Criteria\Iface Criteria object for filtering
	 * @since 2017.03
	 */
	public function createFilter()
	{
		return $this->controller->createFilter();
	}



	/**
	 * Returns the list of categries that are in the path to the root node including the one specified by its ID.
	 *
	 * @param integer $id Category ID to start from, null for root node
	 * @param string[] $domains Domain names of items that are associated with the categories and that should be fetched too
	 * @return array Associative list of items implementing \Aimeos\MShop\Catalog\Item\Iface with their IDs as keys
	 * @since 2017.03
	 */
	public function getPath( $id, array $domains = array( 'text', 'media' ) )
	{
		return $this->controller->getPath( $id, $domains );
	}


	/**
	 * Returns the hierarchical catalog tree starting from the given ID.
	 *
	 * @param integer|null $id Category ID to start from, null for root node
	 * @param string[] $domains Domain names of items that are associated with the categories and that should be fetched too
	 * @param integer $level Constant from \Aimeos\MW\Tree\Manager\Base for the depth of the returned tree, LEVEL_ONE for
	 * 	specific node only, LEVEL_LIST for node and all direct child nodes, LEVEL_TREE for the whole tree
	 * @param \Aimeos\MW\Criteria\Iface|null $search Optional criteria object with conditions
	 * @return \Aimeos\MShop\Catalog\Item\Iface Catalog node, maybe with children depending on the level constant
	 * @since 2017.03
	 */
	public function getTree( $id = null, array $domains = array( 'text', 'media' ),
		$level = \Aimeos\MW\Tree\Manager\Base::LEVEL_TREE, \Aimeos\MW\Criteria\Iface $search = null )
	{
		return $this->controller->getTree( $id, $domains, $level, $search );
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
