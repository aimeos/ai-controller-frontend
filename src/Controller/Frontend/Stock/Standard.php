<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2017-2023
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
	/** controller/frontend/stock/name
	 * Class name of the used stock frontend controller implementation
	 *
	 * Each default frontend controller can be replace by an alternative imlementation.
	 * To use this implementation, you have to set the last part of the class
	 * name as configuration value so the controller factory knows which class it
	 * has to instantiate.
	 *
	 * For example, if the name of the default class is
	 *
	 *  \Aimeos\Controller\Frontend\Stock\Standard
	 *
	 * and you want to replace it with your own version named
	 *
	 *  \Aimeos\Controller\Frontend\Stock\Mystock
	 *
	 * then you have to set the this configuration option:
	 *
	 *  controller/jobs/frontend/stock/name = Mystock
	 *
	 * The value is the last part of your own class name and it's case sensitive,
	 * so take care that the configuration value is exactly named like the last
	 * part of the class name.
	 *
	 * The allowed characters of the class name are A-Z, a-z and 0-9. No other
	 * characters are possible! You should always start the last part of the class
	 * name with an upper case character and continue only with lower case characters
	 * or numbers. Avoid chamel case names like "MyStock"!
	 *
	 * @param string Last part of the class name
	 * @since 2017.03
	 * @category Developer
	 */

	/** controller/frontend/stock/decorators/excludes
	 * Excludes decorators added by the "common" option from the stock frontend controllers
	 *
	 * Decorators extend the functionality of a class by adding new aspects
	 * (e.g. log what is currently done), executing the methods of the underlying
	 * class only in certain conditions (e.g. only for logged in users) or
	 * modify what is returned to the caller.
	 *
	 * This option allows you to remove a decorator added via
	 * "controller/frontend/common/decorators/default" before they are wrapped
	 * around the frontend controller.
	 *
	 *  controller/frontend/stock/decorators/excludes = array( 'decorator1' )
	 *
	 * This would remove the decorator named "decorator1" from the list of
	 * common decorators ("\Aimeos\Controller\Frontend\Common\Decorator\*") added via
	 * "controller/frontend/common/decorators/default" for the stock frontend controller.
	 *
	 * @param array List of decorator names
	 * @since 2017.03
	 * @category Developers
	 * @see controller/frontend/common/decorators/default
	 * @see controller/frontend/stock/decorators/global
	 * @see controller/frontend/stock/decorators/local
	 */

	/** controller/frontend/stock/decorators/global
	 * Adds a list of globally available decorators only to the stock frontend controllers
	 *
	 * Decorators extend the functionality of a class by adding new aspects
	 * (e.g. log what is currently done), executing the methods of the underlying
	 * class only in certain conditions (e.g. only for logged in users) or
	 * modify what is returned to the caller.
	 *
	 * This option allows you to wrap global decorators
	 * ("\Aimeos\Controller\Frontend\Common\Decorator\*") around the frontend controller.
	 *
	 *  controller/frontend/stock/decorators/global = array( 'decorator1' )
	 *
	 * This would add the decorator named "decorator1" defined by
	 * "\Aimeos\Controller\Frontend\Common\Decorator\Decorator1" only to the frontend controller.
	 *
	 * @param array List of decorator names
	 * @since 2017.03
	 * @category Developers
	 * @see controller/frontend/common/decorators/default
	 * @see controller/frontend/stock/decorators/excludes
	 * @see controller/frontend/stock/decorators/local
	 */

	/** controller/frontend/stock/decorators/local
	 * Adds a list of local decorators only to the stock frontend controllers
	 *
	 * Decorators extend the functionality of a class by adding new aspects
	 * (e.g. log what is currently done), executing the methods of the underlying
	 * class only in certain conditions (e.g. only for logged in users) or
	 * modify what is returned to the caller.
	 *
	 * This option allows you to wrap local decorators
	 * ("\Aimeos\Controller\Frontend\Stock\Decorator\*") around the frontend controller.
	 *
	 *  controller/frontend/stock/decorators/local = array( 'decorator2' )
	 *
	 * This would add the decorator named "decorator2" defined by
	 * "\Aimeos\Controller\Frontend\Stock\Decorator\Decorator2" only to the frontend
	 * controller.
	 *
	 * @param array List of decorator names
	 * @since 2017.03
	 * @category Developers
	 * @see controller/frontend/common/decorators/default
	 * @see controller/frontend/stock/decorators/excludes
	 * @see controller/frontend/stock/decorators/global
	 */


	private \Aimeos\Base\Criteria\Iface $filter;
	private \Aimeos\MShop\Common\Manager\Iface $manager;


	/**
	 * Common initialization for controller classes
	 *
	 * @param \Aimeos\MShop\ContextIface $context Common MShop context object
	 */
	public function __construct( \Aimeos\MShop\ContextIface $context )
	{
		parent::__construct( $context );

		$this->manager = \Aimeos\MShop::create( $context, 'stock' );
		$this->filter = $this->manager->filter( true );
	}


	/**
	 * Clones objects in controller
	 */
	public function __clone()
	{
		$this->filter = clone $this->filter;
		parent::__clone();
	}


	/**
	 * Adds the IDs of the products for filtering
	 *
	 * @param array|string $ids Codes of the products
	 * @return \Aimeos\Controller\Frontend\Stock\Iface Stock controller for fluent interface
	 * @since 2021.01
	 */
	public function product( $ids ) : Iface
	{
		if( !empty( $ids ) ) {
			$this->addExpression( $this->filter->compare( '==', 'stock.productid', $ids ) );
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
		$this->addExpression( $this->filter->compare( $operator, $key, $value ) );
		return $this;
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
		return $this->manager->get( $id, [], null );
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
		if( ( $cond = $this->filter->parse( $conditions ) ) !== null ) {
			$this->addExpression( $cond );
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
		$this->addExpression( $this->filter->getConditions() );

		$this->filter->setSortations( $this->getSortations() );
		$this->filter->setConditions( $this->filter->and( $this->getConditions() ) );

		return $this->manager->search( $this->filter, [], $total );
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
		$maxsize = $this->context()->config()->get( 'controller/frontend/common/max-size', 500 );
		$this->filter->slice( $start, min( $limit, $maxsize ) );
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
		$list = $this->splitKeys( $key );

		foreach( $list as $sortkey )
		{
			$direction = ( $sortkey[0] === '-' ? '-' : '+' );
			$sortkey = ltrim( $sortkey, '+-' );

			switch( $sortkey )
			{
				case 'stock':
					$this->addExpression( $this->filter->sort( $direction, 'stock.type' ) );
					$this->addExpression( $this->filter->sort( $direction, 'stock.stocklevel' ) );
					break;
				default:
					$this->addExpression( $this->filter->sort( $direction, $sortkey ) );
				}
		}

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
			$this->addExpression( $this->filter->compare( '==', 'stock.type', $types ) );
		}

		return $this;
	}


	/**
	 * Returns the manager used by the controller
	 *
	 * @return \Aimeos\MShop\Common\Manager\Iface Manager object
	 */
	protected function getManager() : \Aimeos\MShop\Common\Manager\Iface
	{
		return $this->manager;
	}
}
