<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2017
 * @package Controller
 * @subpackage Frontend
 */


namespace Aimeos\Controller\Frontend\Stock\Decorator;


/**
 * Base for stock frontend controller decorators
 *
 * @package Controller
 * @subpackage Frontend
 */
abstract class Base
	extends \Aimeos\Controller\Frontend\Base
	implements \Aimeos\Controller\Frontend\Common\Decorator\Iface, \Aimeos\Controller\Frontend\Stock\Iface
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
		$iface = '\Aimeos\Controller\Frontend\Stock\Iface';
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
	 * Returns the given search filter with the conditions attached for filtering by product code
	 *
	 * @param \Aimeos\MW\Criteria\Iface $filter Criteria object used for stock search
	 * @param array $codes List of product codes
	 * @return \Aimeos\MW\Criteria\Iface Criteria object containing the conditions for searching
	 * @since 2017.03
	 */
	public function addFilterCodes( \Aimeos\MW\Criteria\Iface $filter, array $codes )
	{
		return $this->controller->addFilterCodes( $filter, $codes );
	}


	/**
	 * Returns the given search filter with the conditions attached for filtering by type code
	 *
	 * @param \Aimeos\MW\Criteria\Iface $filter Criteria object used for stock search
	 * @param array $codes List of stock type codes
	 * @return \Aimeos\MW\Criteria\Iface Criteria object containing the conditions for searching
	 * @since 2017.03
	 */
	public function addFilterTypes( \Aimeos\MW\Criteria\Iface $filter, array $codes )
	{
		return $this->controller->addFilterTypes( $filter, $codes );
	}


	/**
	 * Returns the default stock filter
	 *
	 * @param boolean True to add default criteria
	 * @return \Aimeos\MW\Criteria\Iface Criteria object containing the conditions for searching
	 * @since 2017.03
	 */
	public function createFilter()
	{
		return $this->controller->createFilter();
	}


	/**
	 * Returns the stock item for the given stock ID
	 *
	 * @param string $id Unique stock ID
	 * @return \Aimeos\MShop\Stock\Item\Iface Stock item including the referenced domains items
	 * @since 2017.03
	 */
	public function getItem( $id )
	{
		return $this->controller->getItem( $id );
	}


	/**
	 * Returns the stocks filtered by the given criteria object
	 *
	 * @param \Aimeos\MW\Criteria\Iface $filter Critera object which contains the filter conditions
	 * @param integer &$total Parameter where the total number of found stocks will be stored in
	 * @return array Ordered list of stock items implementing \Aimeos\MShop\Stock\Item\Iface
	 * @since 2017.03
	 */
	public function searchItems( \Aimeos\MW\Criteria\Iface $filter, &$total = null )
	{
		return $this->controller->searchItems( $filter, $total );
	}


	/**
	 * Returns the frontend controller
	 *
	 * @return \Aimeos\Controller\Frontend\Stock\Iface Frontend controller object
	 */
	protected function getController()
	{
		return $this->controller;
	}
}
