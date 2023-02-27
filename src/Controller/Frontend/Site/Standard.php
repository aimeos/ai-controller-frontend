<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2021-2023
 * @package Controller
 * @subpackage Frontend
 */


namespace Aimeos\Controller\Frontend\Site;


/**
 * Default implementation of the site frontend controller.
 *
 * @package Controller
 * @subpackage Frontend
 */
class Standard
	extends \Aimeos\Controller\Frontend\Base
	implements Iface, \Aimeos\Controller\Frontend\Common\Iface
{
	/** controller/frontend/site/name
	 * Class name of the used site frontend controller implementation
	 *
	 * Each default frontend controller can be replace by an alternative imlementation.
	 * To use this implementation, you have to set the last part of the class
	 * name as configuration value so the controller factory knows which class it
	 * has to instantiate.
	 *
	 * For example, if the name of the default class is
	 *
	 *  \Aimeos\Controller\Frontend\Site\Standard
	 *
	 * and you want to replace it with your own version named
	 *
	 *  \Aimeos\Controller\Frontend\Site\Mysite
	 *
	 * then you have to set the this configuration option:
	 *
	 *  controller/jobs/frontend/site/name = Mysite
	 *
	 * The value is the last part of your own class name and it's case sensitive,
	 * so take care that the configuration value is exactly named like the last
	 * part of the class name.
	 *
	 * The allowed characters of the class name are A-Z, a-z and 0-9. No other
	 * characters are possible! You should always start the last part of the class
	 * name with an upper case character and continue only with lower case characters
	 * or numbers. Avoid chamel case names like "MySite"!
	 *
	 * @param string Last part of the class name
	 * @since 2021.04
	 * @category Developer
	 */

	/** controller/frontend/site/decorators/excludes
	 * Excludes decorators added by the "common" option from the site frontend controllers
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
	 *  controller/frontend/site/decorators/excludes = array( 'decorator1' )
	 *
	 * This would remove the decorator named "decorator1" from the list of
	 * common decorators ("\Aimeos\Controller\Frontend\Common\Decorator\*") added via
	 * "controller/frontend/common/decorators/default" for the site frontend controller.
	 *
	 * @param array List of decorator names
	 * @since 2021.04
	 * @category Developers
	 * @see controller/frontend/common/decorators/default
	 * @see controller/frontend/site/decorators/global
	 * @see controller/frontend/site/decorators/local
	 */

	/** controller/frontend/site/decorators/global
	 * Adds a list of globally available decorators only to the site frontend controllers
	 *
	 * Decorators extend the functionality of a class by adding new aspects
	 * (e.g. log what is currently done), executing the methods of the underlying
	 * class only in certain conditions (e.g. only for logged in users) or
	 * modify what is returned to the caller.
	 *
	 * This option allows you to wrap global decorators
	 * ("\Aimeos\Controller\Frontend\Common\Decorator\*") around the frontend controller.
	 *
	 *  controller/frontend/site/decorators/global = array( 'decorator1' )
	 *
	 * This would add the decorator named "decorator1" defined by
	 * "\Aimeos\Controller\Frontend\Common\Decorator\Decorator1" only to the frontend controller.
	 *
	 * @param array List of decorator names
	 * @since 2021.04
	 * @category Developers
	 * @see controller/frontend/common/decorators/default
	 * @see controller/frontend/site/decorators/excludes
	 * @see controller/frontend/site/decorators/local
	 */

	/** controller/frontend/site/decorators/local
	 * Adds a list of local decorators only to the site frontend controllers
	 *
	 * Decorators extend the functionality of a class by adding new aspects
	 * (e.g. log what is currently done), executing the methods of the underlying
	 * class only in certain conditions (e.g. only for logged in users) or
	 * modify what is returned to the caller.
	 *
	 * This option allows you to wrap local decorators
	 * ("\Aimeos\Controller\Frontend\Site\Decorator\*") around the frontend controller.
	 *
	 *  controller/frontend/site/decorators/local = array( 'decorator2' )
	 *
	 * This would add the decorator named "decorator2" defined by
	 * "\Aimeos\Controller\Frontend\Site\Decorator\Decorator2" only to the frontend
	 * controller.
	 *
	 * @param array List of decorator names
	 * @since 2021.04
	 * @category Developers
	 * @see controller/frontend/common/decorators/default
	 * @see controller/frontend/site/decorators/excludes
	 * @see controller/frontend/site/decorators/global
	 */


	 private \Aimeos\MShop\Common\Manager\Iface $manager;
	 private \Aimeos\Base\Criteria\Iface $filter;
	private ?string $root = null;


	/**
	 * Common initialization for controller classes
	 *
	 * @param \Aimeos\MShop\ContextIface $context Common MShop context object
	 */
	public function __construct( \Aimeos\MShop\ContextIface $context )
	{
		parent::__construct( $context );

		$this->manager = \Aimeos\MShop::create( $context, 'locale/site' );
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
	 * Adds generic condition for filtering attributes
	 *
	 * @param string $operator Comparison operator, e.g. "==", "!=", "<", "<=", ">=", ">", "=~", "~="
	 * @param string $key Search key defined by the site manager, e.g. "site.status"
	 * @param array|string $value Value or list of values to compare to
	 * @return \Aimeos\Controller\Frontend\Site\Iface Site controller for fluent interface
	 * @since 2021.04
	 */
	public function compare( string $operator, string $key, $value ) : Iface
	{
		$this->addExpression( $this->filter->compare( $operator, $key, $value ) );
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
		return $this->manager->find( $code, [], null, null, null );
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
		return $this->manager->get( $id, [], null );
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
		$list = $this->manager->getPath( $id, [] );

		if( $list->isAvailable()->search( false ) ) {
			throw new \Aimeos\Controller\Frontend\Exception( sprintf( 'Site is not available' ) );
		}

		if( $this->root )
		{
			foreach( $list as $key => $item )
			{
				if( $key == $this->root ) {
					break;
				}
				unset( $list[$key] );
			}
		}

		return $list;
	}


	/**
	 * Returns the sites filtered by the previously assigned conditions
	 *
	 * @param int $level Tree level constant, e.g. ONE, LIST or TREE
	 * @return \Aimeos\MShop\Locale\Item\Site\Iface Site tree
	 * @since 2021.04
	 */
	public function getTree( int $level = Iface::TREE ) : \Aimeos\MShop\Locale\Item\Site\Iface
	{
		$this->addExpression( $this->filter->getConditions() );
		$this->filter->setConditions( $this->filter->and( $this->getConditions() ) );

		return $this->manager->getTree( $this->root, [], $level, $this->filter );
	}


	/**
	 * Parses the given array and adds the conditions to the list of conditions
	 *
	 * @param array $conditions List of conditions, e.g. ['>' => ['site.status' => 0]]
	 * @return \Aimeos\Controller\Frontend\Site\Iface Site controller for fluent interface
	 * @since 2021.04
	 */
	public function parse( array $conditions ) : Iface
	{
		if( ( $cond = $this->filter->parse( $conditions ) ) !== null ) {
			$this->addExpression( $cond );
		}

		return $this;
	}


	/**
	 * Sets the site ID of node that is used as root node
	 *
	 * @param string|null $id Site ID
	 * @return \Aimeos\Controller\Frontend\Site\Iface Site controller for fluent interface
	 * @since 2021.04
	 */
	public function root( string $id = null ) : Iface
	{
		$this->root = ( $id ? $id : null );
		return $this;
	}


	/**
	 * Returns the sites filtered by the previously assigned conditions
	 *
	 * @param int &$total Parameter where the total number of found sites will be stored in
	 * @return \Aimeos\Map Ordered list of site items implementing \Aimeos\MShop\Locale\Item\Site\Iface
	 * @since 2021.04
	 */
	public function search( int &$total = null ) : \Aimeos\Map
	{
		$this->addExpression( $this->filter->getConditions() );

		$this->filter->setConditions( $this->filter->and( $this->getConditions() ) );
		$this->filter->setSortations( $this->getSortations() );

		return $this->manager->search( $this->filter, [], $total );
	}


	/**
	 * Sets the start value and the number of returned products for slicing the list of found products
	 *
	 * @param int $start Start value of the first product in the list
	 * @param int $limit Number of returned products
	 * @return \Aimeos\Controller\Frontend\Site\Iface Site controller for fluent interface
	 * @since 2021.04
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
	 * @param string|null $key Search key for sorting of the result list and null for no sorting
	 * @return \Aimeos\Controller\Frontend\Site\Iface Site controller for fluent interface
	 * @since 2021.04
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
	 * Returns the manager used by the controller
	 *
	 * @return \Aimeos\MShop\Common\Manager\Iface Manager object
	 */
	protected function getManager() : \Aimeos\MShop\Common\Manager\Iface
	{
		return $this->manager;
	}
}
