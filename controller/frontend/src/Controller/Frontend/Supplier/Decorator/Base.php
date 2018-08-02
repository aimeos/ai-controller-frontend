<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2018
 * @package Controller
 * @subpackage Frontend
 */


namespace Aimeos\Controller\Frontend\Supplier\Decorator;


/**
 * Base for supplier frontend controller decorators
 *
 * @package Controller
 * @subpackage Frontend
 */
abstract class Base
	extends \Aimeos\Controller\Frontend\Base
	implements \Aimeos\Controller\Frontend\Common\Decorator\Iface, \Aimeos\Controller\Frontend\Supplier\Iface
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
		\Aimeos\MW\Common\Base::checkClass( '\\Aimeos\\Controller\\Frontend\\Supplier\\Iface', $controller );

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
	 * Returns the default supplier filter
	 *
	 * @param boolean True to add default criteria
	 * @return \Aimeos\MW\Criteria\Iface Criteria object containing the conditions for searching
	 * @since 2018.07
	 */
	public function createFilter()
	{
		return $this->controller->createFilter();
	}


	/**
	 * Returns the supplier item for the given supplier ID
	 *
	 * @param string $id Unique supplier ID
	 * @param string[] $domains Domain names of items that are associated with the suppliers and that should be fetched too
	 * @return \Aimeos\MShop\Supplier\Item\Iface Supplier item including the referenced domains items
	 * @since 2018.07
	 */
	public function getItem( $id, array $domains = array( 'media', 'text' ) )
	{
		return $this->controller->getItem( $id, $domains );
	}


	/**
	 * Returns the supplier items for the given supplier IDs
	 *
	 * @param string $ids Unique supplier IDs
	 * @param string[] $domains Domain names of items that are associated with the suppliers and that should be fetched too
	 * @return \Aimeos\MShop\Supplier\Item\Iface[] Associative list of supplier item including the referenced domains items
	 * @since 2018.07
	 */
	public function getItems( array $ids, array $domains = array( 'media', 'text' ) )
	{
		return $this->controller->getItems( $ids, $domains );
	}


	/**
	 * Returns the suppliers filtered by the given criteria object
	 *
	 * @param \Aimeos\MW\Criteria\Iface $filter Critera object which contains the filter conditions
	 * @param string[] $domains Domain names of items that are associated with the suppliers and that should be fetched too
	 * @param integer &$total Parameter where the total number of found suppliers will be stored in
	 * @return array Ordered list of supplier items implementing \Aimeos\MShop\Supplier\Item\Iface
	 * @since 2018.07
	 */
	public function searchItems( \Aimeos\MW\Criteria\Iface $filter, array $domains = array( 'media', 'text' ), &$total = null )
	{
		return $this->controller->searchItems( $filter, $domains, $total );
	}


	/**
	 * Returns the frontend controller
	 *
	 * @return \Aimeos\Controller\Frontend\Supplier\Iface Frontend controller object
	 */
	protected function getController()
	{
		return $this->controller;
	}
}
