<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2018-2024
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
	/** controller/frontend/supplier/name
	 * Class name of the used supplier frontend controller implementation
	 *
	 * Each default frontend controller can be replace by an alternative imlementation.
	 * To use this implementation, you have to set the last part of the class
	 * name as configuration value so the controller factory knows which class it
	 * has to instantiate.
	 *
	 * For example, if the name of the default class is
	 *
	 *  \Aimeos\Controller\Frontend\Supplier\Standard
	 *
	 * and you want to replace it with your own version named
	 *
	 *  \Aimeos\Controller\Frontend\Supplier\Mysupplier
	 *
	 * then you have to set the this configuration option:
	 *
	 *  controller/jobs/frontend/supplier/name = Mysupplier
	 *
	 * The value is the last part of your own class name and it's case sensitive,
	 * so take care that the configuration value is exactly named like the last
	 * part of the class name.
	 *
	 * The allowed characters of the class name are A-Z, a-z and 0-9. No other
	 * characters are possible! You should always start the last part of the class
	 * name with an upper case character and continue only with lower case characters
	 * or numbers. Avoid chamel case names like "MySupplier"!
	 *
	 * @param string Last part of the class name
	 * @since 2018.07
	 * @category Developer
	 */

	/** controller/frontend/supplier/decorators/excludes
	 * Excludes decorators added by the "common" option from the supplier frontend controllers
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
	 *  controller/frontend/supplier/decorators/excludes = array( 'decorator1' )
	 *
	 * This would remove the decorator named "decorator1" from the list of
	 * common decorators ("\Aimeos\Controller\Frontend\Common\Decorator\*") added via
	 * "controller/frontend/common/decorators/default" for the supplier frontend controller.
	 *
	 * @param array List of decorator names
	 * @since 2018.07
	 * @category Developer
	 * @see controller/frontend/common/decorators/default
	 * @see controller/frontend/supplier/decorators/global
	 * @see controller/frontend/supplier/decorators/local
	 */

	/** controller/frontend/supplier/decorators/global
	 * Adds a list of globally available decorators only to the supplier frontend controllers
	 *
	 * Decorators extend the functionality of a class by adding new aspects
	 * (e.g. log what is currently done), executing the methods of the underlying
	 * class only in certain conditions (e.g. only for logged in users) or
	 * modify what is returned to the caller.
	 *
	 * This option allows you to wrap global decorators
	 * ("\Aimeos\Controller\Frontend\Common\Decorator\*") around the frontend controller.
	 *
	 *  controller/frontend/supplier/decorators/global = array( 'decorator1' )
	 *
	 * This would add the decorator named "decorator1" defined by
	 * "\Aimeos\Controller\Frontend\Common\Decorator\Decorator1" only to the frontend controller.
	 *
	 * @param array List of decorator names
	 * @since 2018.07
	 * @category Developer
	 * @see controller/frontend/common/decorators/default
	 * @see controller/frontend/supplier/decorators/excludes
	 * @see controller/frontend/supplier/decorators/local
	 */

	/** controller/frontend/supplier/decorators/local
	 * Adds a list of local decorators only to the supplier frontend controllers
	 *
	 * Decorators extend the functionality of a class by adding new aspects
	 * (e.g. log what is currently done), executing the methods of the underlying
	 * class only in certain conditions (e.g. only for logged in users) or
	 * modify what is returned to the caller.
	 *
	 * This option allows you to wrap local decorators
	 * ("\Aimeos\Controller\Frontend\Supplier\Decorator\*") around the frontend controller.
	 *
	 *  controller/frontend/supplier/decorators/local = array( 'decorator2' )
	 *
	 * This would add the decorator named "decorator2" defined by
	 * "\Aimeos\Controller\Frontend\Supplier\Decorator\Decorator2" only to the frontend
	 * controller.
	 *
	 * @param array List of decorator names
	 * @since 2018.07
	 * @category Developer
	 * @see controller/frontend/common/decorators/default
	 * @see controller/frontend/supplier/decorators/excludes
	 * @see controller/frontend/supplier/decorators/global
	 */


	private array $domains = [];
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

		$this->manager = \Aimeos\MShop::create( $context, 'supplier' );
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
	 * Adds generic condition for filtering
	 *
	 * @param string $operator Comparison operator, e.g. "==", "!=", "<", "<=", ">=", ">", "=~", "~="
	 * @param string $key Search key defined by the supplier manager, e.g. "supplier.status"
	 * @param array|string $value Value or list of values to compare to
	 * @return \Aimeos\Controller\Frontend\Supplier\Iface Supplier controller for fluent interface
	 * @since 2019.04
	 */
	public function compare( string $operator, string $key, $value ) : Iface
	{
		$this->addExpression( $this->filter->compare( $operator, $key, $value ) );
		return $this;
	}


	/**
	 * Returns the supplier for the given supplier code
	 *
	 * @param string $code Unique supplier code
	 * @return \Aimeos\MShop\Supplier\Item\Iface Supplier item including the referenced domains items
	 * @since 2019.04
	 */
	public function find( string $code ) : \Aimeos\MShop\Supplier\Item\Iface
	{
		return $this->manager->find( $code, $this->domains, null, null, null );
	}


	/**
	 * Creates a search function string for the given name and parameters
	 *
	 * @param string $name Name of the search function without parenthesis, e.g. "supplier:has"
	 * @param array $params List of parameters for the search function with numeric keys starting at 0
	 * @return string Search function string that can be used in compare()
	 */
	public function function( string $name, array $params ) : string
	{
		return $this->filter->make( $name, $params );
	}


	/**
	 * Returns the supplier for the given supplier ID
	 *
	 * @param string $id Unique supplier ID
	 * @return \Aimeos\MShop\Supplier\Item\Iface Supplier item including the referenced domains items
	 * @since 2019.04
	 */
	public function get( string $id ) : \Aimeos\MShop\Supplier\Item\Iface
	{
		return $this->manager->get( $id, $this->domains, null );
	}


	/**
	 * Adds a filter to return only items containing a reference to the given ID
	 *
	 * @param string $domain Domain name of the referenced item, e.g. "media"
	 * @param string|null $type Type code of the reference, e.g. "default" or null for all types
	 * @param string|null $refId ID of the referenced item of the given domain or null for all references
	 * @return \Aimeos\Controller\Frontend\Supplier\Iface Supplier controller for fluent interface
	 * @since 2019.10
	 */
	public function has( string $domain, ?string $type = null, ?string $refId = null ) : Iface
	{
		$params = [$domain];
		!$type ?: $params[] = $type;
		!$refId ?: $params[] = $refId;

		$func = $this->filter->make( 'supplier:has', $params );
		$this->addExpression( $this->filter->compare( '!=', $func, null ) );
		return $this;
	}


	/**
	 * Parses the given array and adds the conditions to the list of conditions
	 *
	 * @param array $conditions List of conditions, e.g. ['=~' => ['supplier.label' => 'test']]
	 * @return \Aimeos\Controller\Frontend\Supplier\Iface Supplier controller for fluent interface
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
	 * Returns the supplier for the given supplier URL name
	 *
	 * @param string $name Supplier URL name
	 * @return \Aimeos\MShop\Supplier\Item\Iface Supplier item including the referenced domains items
	 * @since 2023.10
	 */
	public function resolve( string $name ) : \Aimeos\MShop\Supplier\Item\Iface
	{
		$search = $this->manager->filter( null )->add( 'supplier.code', '==', $name )->slice( 0, 1 );

		if( ( $item = $this->manager->search( $search, $this->domains )->first() ) === null )
		{
			$msg = $this->context()->translate( 'controller/frontend', 'Unable to find supplier "%1$s"' );
			throw new \Aimeos\Controller\Frontend\Supplier\Exception( sprintf( $msg, $name ), 404 );
		}

		return $item;
	}


	/**
	 * Returns the suppliers filtered by the previously assigned conditions
	 *
	 * @param int &$total Parameter where the total number of found suppliers will be stored in
	 * @return \Aimeos\Map Ordered list of supplier items implementing \Aimeos\MShop\Supplier\Item\Iface
	 * @since 2019.04
	 */
	public function search( ?int &$total = null ) : \Aimeos\Map
	{
		$filter = clone $this->filter;

		$this->addExpression( $filter->getConditions() );

		$filter->setSortations( $this->getSortations() );
		$filter->add( $filter->and( $this->getConditions() ) );

		return $this->manager->search( $filter, $this->domains, $total );
	}


	/**
	 * Sets the start value and the number of returned supplier items for slicing the list of found supplier items
	 *
	 * @param int $start Start value of the first supplier item in the list
	 * @param int $limit Number of returned supplier items
	 * @return \Aimeos\Controller\Frontend\Supplier\Iface Supplier controller for fluent interface
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
	 * @param string|null $key Sorting key of the result list like "supplier.label", null for no sorting
	 * @return \Aimeos\Controller\Frontend\Supplier\Iface Supplier controller for fluent interface
	 * @since 2019.04
	 */
	public function sort( ?string $key = null ) : Iface
	{
		$list = $this->splitKeys( $key );

		foreach( $list as $sortkey )
		{
			$direction = ( $sortkey[0] === '-' ? '-' : '+' );
			$this->addExpression( $this->filter->sort( $direction, ltrim( $sortkey, '+-' ) ) );
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
	public function uses( array $domains ) : Iface
	{
		$this->domains = $domains;
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
