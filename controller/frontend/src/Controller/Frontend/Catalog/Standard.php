<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2015-2018
 * @package Controller
 * @subpackage Frontend
 */


namespace Aimeos\Controller\Frontend\Catalog;


/**
 * Default implementation of the catalog frontend controller.
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
	private $root;


	/**
	 * Common initialization for controller classes
	 *
	 * @param \Aimeos\MShop\Context\Item\Iface $context Common MShop context object
	 */
	public function __construct( \Aimeos\MShop\Context\Item\Iface $context )
	{
		parent::__construct( $context );

		$this->manager = \Aimeos\MShop::create( $context, 'catalog' );
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
		$this->conditions[] = $this->filter->compare( $operator, $key, $value );
		return $this;
	}


	/**
	 * Returns the category for the given catalog code
	 *
	 * @param string $code Unique catalog code
	 * @return \Aimeos\MShop\Catalog\Item\Iface Catalog item including the referenced domains items
	 * @since 2019.04
	 */
	public function find( $code )
	{
		return $this->manager->findItem( $code, $this->domains, null, null, true );
	}


	/**
	 * Returns the category for the given catalog ID
	 *
	 * @param string $id Unique catalog ID
	 * @return \Aimeos\MShop\Catalog\Item\Iface Catalog item including the referenced domains items
	 * @since 2019.04
	 */
	public function get( $id )
	{
		return $this->manager->getItem( $id, $this->domains, true );
	}


	/**
	 * Returns the list of categories up to the root node including the node given by its ID
	 *
	 * @param integer $id Current category ID
	 * @return \Aimeos\MShop\Catalog\Item\Iface[] Associative list of categories
	 * @since 2017.03
	 */
	public function getPath( $id )
	{
		$list = $this->manager->getPath( $id, $this->domains );

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
	 * Returns the categories filtered by the previously assigned conditions
	 *
	 * @param integer $level Constant from \Aimeos\MW\Tree\Manager\Base, e.g. LEVEL_ONE, LEVEL_LIST or LEVEL_TREE
	 * @return \Aimeos\MShop\Catalog\Item\Iface Category tree
	 * @since 2019.04
	 */
	public function getTree( $level = \Aimeos\MW\Tree\Manager\Base::LEVEL_TREE )
	{
		$this->filter->setConditions( $this->filter->combine( '&&', $this->conditions ) );
		return $this->manager->getTree( $this->root, $this->domains, $level, $this->filter );
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
		if( ($cond = $this->filter->toConditions( $conditions ) ) !== null ) {
			$this->conditions[] = $cond;
		}

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
		$this->root = ( $id ? $id : null );
		return $this;
	}


	/**
	 * Returns the categories filtered by the previously assigned conditions
	 *
	 * @param int &$total Parameter where the total number of found categories will be stored in
	 * @return \Aimeos\MShop\Catalog\Item\Iface[] Ordered list of catalog items
	 * @since 2019.10
	 */
	public function search( &$total = null )
	{
		$this->filter->setConditions( $this->filter->combine( '&&', $this->conditions ) );
		return $this->manager->searchItems( $this->filter, $this->domains, $total );
	}


	/**
	 * Sets the referenced domains that will be fetched too when retrieving items
	 *
	 * @param array $domains Domain names of the referenced items that should be fetched too
	 * @return \Aimeos\Controller\Frontend\Catalog\Iface Catalog controller for fluent interface
	 * @since 2019.04
	 */
	public function uses( array $domains )
	{
		$this->domains = $domains;
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
		if( empty( $catIds ) ) {
			return $this;
		}

		$config = $this->getContext()->getConfig();

		$expr = [
			$this->filter->compare( '==', 'catalog.parentid', $catIds ),
			$this->filter->compare( '==', 'catalog.id', $catIds )
		];

		/** controller/frontend/catalog/levels-always
		 * The number of levels in the category tree that should be always displayed
		 *
		 * Usually, only the root node and the first level of the category
		 * tree is shown in the frontend. Only if the user clicks on a
		 * node in the first level, the page reloads and the sub-nodes of
		 * the chosen category are rendered as well.
		 *
		 * Using this configuration option you can enforce the given number
		 * of levels to be always displayed. The root node uses level 0, the
		 * categories below level 1 and so on.
		 *
		 * In most cases you can set this value via the administration interface
		 * of the shop application. In that case you often can configure the
		 * levels individually for each catalog filter.
		 *
		 * Note: This setting was available between 2014.03 and 2019.04 as
		 * client/html/catalog/filter/tree/levels-always
		 *
		 * @param integer Number of tree levels
		 * @since 2019.04
		 * @category User
		 * @category Developer
		 * @see controller/frontend/catalog/levels-only
		*/
		if( ( $levels = $config->get( 'controller/frontend/catalog/levels-always' ) ) != null ) {
			$expr[] = $this->filter->compare( '<=', 'catalog.level', $levels );
		}

		/** controller/frontend/catalog/levels-only
		 * No more than this number of levels in the category tree should be displayed
		 *
		 * If the user clicks on a category node, the page reloads and the
		 * sub-nodes of the chosen category are rendered as well.
		 * Using this configuration option you can enforce that no more than
		 * the given number of levels will be displayed at all. The root
		 * node uses level 0, the categories below level 1 and so on.
		 *
		 * In most cases you can set this value via the administration interface
		 * of the shop application. In that case you often can configure the
		 * levels individually for each catalog filter.
		 *
		 * Note: This setting was available between 2014.03 and 2019.04 as
		 * client/html/catalog/filter/tree/levels-only
		 *
		 * @param integer Number of tree levels
		 * @since 2014.03
		 * @category User
		 * @category Developer
		 * @see controller/frontend/catalog/levels-always
		 */
		if( ( $levels = $config->get( 'controller/frontend/catalog/levels-only' ) ) != null ) {
			$this->conditions[] = $this->filter->compare( '<=', 'catalog.level', $levels );
		}

		$this->conditions[] = $this->filter->combine( '||', $expr );
		return $this;
	}
}
