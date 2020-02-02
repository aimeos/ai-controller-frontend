<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2017-2020
 * @package Controller
 * @subpackage Frontend
 */


namespace Aimeos\Controller\Frontend\Stock;


/**
 * Default implementation of the stock frontend controller
 *
 * @package Controller
 * @subpackage Frontend
 */
class Standard
	extends \Aimeos\Controller\Frontend\Base
	implements Iface, \Aimeos\Controller\Frontend\Common\Iface
{
	private $conditions = [];
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

		$this->manager = \Aimeos\MShop::create( $context, 'stock' );
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
	 * Adds the SKUs of the products for filtering
	 *
	 * @param array|string $codes Codes of the products
	 * @return \Aimeos\Controller\Frontend\Stock\Iface Stock controller for fluent interface
	 * @since 2019.04
	 */
	public function code( $codes ) : Iface
	{
		if( !empty( $codes ) ) {
			$this->conditions[] = $this->filter->compare( '==', 'stock.productcode', $codes );
		}

		return $this;
	}


	/**
	 * Adds generic condition for filtering
	 *
	 * @param string $operator Comparison operator, e.g. "==", "!=", "<", "<=", ">=", ">", "=~", "~="
	 * @param string $key Search key defined by the stock manager, e.g. "stock.dateback"
	 * @param array|string $value Value or list of values to compare to
	 * @return \Aimeos\Controller\Frontend\Stock\Iface Stock controller for fluent interface
	 * @since 2019.04
	 */
	public function compare( string $operator, string $key, $value ) : Iface
	{
		$this->conditions[] = $this->filter->compare( $operator, $key, $value );
		return $this;
	}


	/**
	 * Returns the stock item for the given SKU and type
	 *
	 * @param string $code Unique stock code
	 * @param string $type Type assigned to the stock item
	 * @return \Aimeos\MShop\Stock\Item\Iface Stock item
	 * @since 2019.04
	 */
	public function find( string $code, string $type ) : \Aimeos\MShop\Stock\Item\Iface
	{
		return $this->manager->findItem( $code, [], 'product', $type, true );
	}


	/**
	 * Returns the stock item for the given stock ID
	 *
	 * @param string $id Unique stock ID
	 * @return \Aimeos\MShop\Stock\Item\Iface Stock item
	 * @since 2019.04
	 */
	public function get( string $id ) : \Aimeos\MShop\Stock\Item\Iface
	{
		return $this->manager->getItem( $id, [], true );
	}


	/**
	 * Parses the given array and adds the conditions to the list of conditions
	 *
	 * @param array $conditions List of conditions, e.g. ['>' => ['stock.dateback' => '2000-01-01 00:00:00']]
	 * @return \Aimeos\Controller\Frontend\Stock\Iface Stock controller for fluent interface
	 * @since 2019.04
	 */
	public function parse( array $conditions ) : Iface
	{
		if( ( $cond = $this->filter->toConditions( $conditions ) ) !== null ) {
			$this->conditions[] = $cond;
		}

		return $this;
	}


	/**
	 * Returns the stock items filtered by the previously assigned conditions
	 *
	 * @param int &$total Parameter where the total number of found stock items will be stored in
	 * @return \Aimeos\Map Ordered list of stock items implementing \Aimeos\MShop\Stock\Item\Iface
	 * @since 2019.04
	 */
	public function search( int &$total = null ) : \Aimeos\Map
	{
		$this->filter->setConditions( $this->filter->combine( '&&', $this->conditions ) );
		return $this->manager->searchItems( $this->filter, [], $total );
	}


	/**
	 * Sets the start value and the number of returned stock items for slicing the list of found stock items
	 *
	 * @param integer $start Start value of the first stock item in the list
	 * @param integer $limit Number of returned stock items
	 * @return \Aimeos\Controller\Frontend\Stock\Iface Stock controller for fluent interface
	 * @since 2019.04
	 */
	public function slice( int $start, int $limit ) : Iface
	{
		$this->filter->setSlice( $start, $limit );
		return $this;
	}


	/**
	 * Sets the sorting of the result list
	 *
	 * @param string|null $key Sorting of the result list like "stock.type", null for no sorting
	 * @return \Aimeos\Controller\Frontend\Stock\Iface Stock controller for fluent interface
	 * @since 2019.04
	 */
	public function sort( string $key = null ) : Iface
	{
		$sort = [];
		$list = ( $key ? explode( ',', $key ) : [] );

		foreach( $list as $sortkey )
		{
			$direction = ( $sortkey[0] === '-' ? '-' : '+' );
			$sortkey = ltrim( $sortkey, '+-' );

			switch( $sortkey )
			{
				case 'stock':
					$sort[] = $this->filter->sort( $direction, 'stock.type' );
					$sort[] = $this->filter->sort( $direction, 'stock.stocklevel' );
					break;
				default:
					$sort[] = $this->filter->sort( $direction, $sortkey );
				}
		}

		$this->filter->setSortations( $sort );
		return $this;
	}


	/**
	 * Adds stock types for filtering
	 *
	 * @param array|string $types Stock type codes
	 * @return \Aimeos\Controller\Frontend\Stock\Iface Stock controller for fluent interface
	 * @since 2019.04
	 */
	public function type( $types ) : Iface
	{
		if( !empty( $types ) ) {
			$this->conditions[] = $this->filter->compare( '==', 'stock.type', $types );
		}

		return $this;
	}
}
