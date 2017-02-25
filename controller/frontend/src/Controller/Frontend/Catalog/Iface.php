<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Metaways Infosystems GmbH, 2012
 * @copyright Aimeos (aimeos.org), 2015-2016
 * @package Controller
 * @subpackage Frontend
 */


namespace Aimeos\Controller\Frontend\Catalog;


/**
 * Interface for catalog frontend controllers.
 *
 * @package Controller
 * @subpackage Frontend
 */
interface Iface
{
	/**
	 * Returns the manager for the given name
	 *
	 * @param string $name Name of the manager
	 * @return \Aimeos\MShop\Common\Manager\Iface Manager object
	 */
	public function createManager( $name );


	/**
	 * Returns the default catalog filter
	 *
	 * @return \Aimeos\MW\Criteria\Iface Criteria object for filtering
	 * @since 2017.03
	 */
	public function createFilter();


	/**
	 * Returns the list of categries that are in the path to the root node including the one specified by its ID.
	 *
	 * @param integer $id Category ID to start from, null for root node
	 * @param string[] $domains Domain names of items that are associated with the categories and that should be fetched too
	 * @return array Associative list of items implementing \Aimeos\MShop\Catalog\Item\Iface with their IDs as keys
	 * @since 2017.03
	 */
	public function getPath( $id, array $domains = array( 'text', 'media' ) );


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
		$level = \Aimeos\MW\Tree\Manager\Base::LEVEL_TREE, \Aimeos\MW\Criteria\Iface $search = null );
}
