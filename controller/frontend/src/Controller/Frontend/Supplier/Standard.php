<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2018
 * @package Controller
 * @subpackage Frontend
 */


namespace Aimeos\Controller\Frontend\Supplier;


/**
 * Default implementation of the supplier frontend controller
 *
 * @package Controller
 * @subpackage Frontend
 */
class Standard
	extends \Aimeos\Controller\Frontend\Base
	implements Iface, \Aimeos\Controller\Frontend\Common\Iface
{
	private $conditions = [];
	private $domains = [];
	private $filter;
	private $manager;


	/**
	 * Common initialization for controller classes
	 *
	 * @param \Aimeos\MShop\Context\Item\Iface $context Common MShop context object
	 */
	public function __construct( \Aimeos\MShop\Context\Item\Iface $context )
	{
		parent::__construct( $context );

		$this->manager = \Aimeos\MShop::create( $context, 'supplier' );
		$this->filter = $this->manager->createSearch( true );
		$this->conditions[] = $this->filter->getConditions();
	}


	/**
	 * Clones objects in controller and resets values
	 */
	public function __clone()
	{
		$this->filter = clone $this->filter;
	}


	/**
	 * Adds generic condition for filtering
	 *
	 * @param string $operator Comparison operator, e.g. "==", "!=", "<", "<=", ">=", ">", "=~", "~="
	 * @param string $key Search key defined by the supplier manager, e.g. "supplier.status"
	 * @param array|string $value Value or list of values to compare to
	 * @return \Aimeos\Controller\Frontend\Supplier\Iface Supplier controller for fluent interface
	 * @since 2019.04
	 */
	public function compare( $operator, $key, $value )
	{
		$this->conditions[] = $this->filter->compare( $operator, $key, $value );
		return $this;
	}


	/**
	 * Returns the supplier for the given supplier code
	 *
	 * @param string $code Unique supplier code
	 * @return \Aimeos\MShop\Supplier\Item\Iface Supplier item including the referenced domains items
	 * @since 2019.04
	 */
	public function find( $code )
	{
		return $this->manager->findItem( $code, $this->domains, null, null, true );
	}


	/**
	 * Returns the supplier for the given supplier ID
	 *
	 * @param string $id Unique supplier ID
	 * @return \Aimeos\MShop\Supplier\Item\Iface Supplier item including the referenced domains items
	 * @since 2019.04
	 */
	public function get( $id )
	{
		return $this->manager->getItem( $id, $this->domains, true );
	}


	/**
	 * Parses the given array and adds the conditions to the list of conditions
	 *
	 * @param array $conditions List of conditions, e.g. ['=~' => ['supplier.label' => 'test']]
	 * @return \Aimeos\Controller\Frontend\Supplier\Iface Supplier controller for fluent interface
	 * @since 2019.04
	 */
	public function parse( array $conditions )
	{
		$this->conditions[] = $this->filter->toConditions( $conditions );
		return $this;
	}


	/**
	 * Returns the suppliers filtered by the previously assigned conditions
	 *
	 * @param integer &$total Parameter where the total number of found suppliers will be stored in
	 * @return \Aimeos\MShop\Supplier\Item\Iface[] Ordered list of supplier items
	 * @since 2019.04
	 */
	public function search( &$total = null )
	{
		$this->filter->setConditions( $this->filter->combine( '&&', $this->conditions ) );
		return $this->manager->searchItems( $this->filter, $this->domains, $total );
	}


	/**
	 * Sets the start value and the number of returned supplier items for slicing the list of found supplier items
	 *
	 * @param integer $start Start value of the first supplier item in the list
	 * @param integer $limit Number of returned supplier items
	 * @return \Aimeos\Controller\Frontend\Supplier\Iface Supplier controller for fluent interface
	 * @since 2019.04
	 */
	public function slice( $start, $limit )
	{
		$this->filter->setSlice( $start, $limit );
		return $this;
	}


	/**
	 * Sets the sorting of the result list
	 *
	 * @param string|null $key Sorting key of the result list like "supplier.label", null for no sorting
	 * @return \Aimeos\Controller\Frontend\Supplier\Iface Supplier controller for fluent interface
	 * @since 2019.04
	 */
	public function sort( $key = null )
	{
		$direction = '+';

		if( $key != null && $key[0] === '-' )
		{
			$key = substr( $key, 1 );
			$direction = '-';
		}

		switch( $key )
		{
			case null:
				$this->filter->setSortations( [] );
				break;
			default:
				$this->filter->setSortations( [$this->filter->sort( $direction, $key )] );
		}

		return $this;
	}


	/**
	 * Sets the referenced domains that will be fetched too when retrieving items
	 *
	 * @param array $domains Domain names of the referenced items that should be fetched too
	 * @return \Aimeos\Controller\Frontend\Supplier\Iface Supplier controller for fluent interface
	 * @since 2019.04
	 */
	public function uses( array $domains )
	{
		$this->domains = $domains;
		return $this;
	}
}
