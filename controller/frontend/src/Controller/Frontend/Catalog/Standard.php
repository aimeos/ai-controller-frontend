<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Metaways Infosystems GmbH, 2012
 * @copyright Aimeos (aimeos.org), 2015-2017
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
	/**
	 * Returns the manager for the given name
	 *
	 * @param string $name Name of the manager
	 * @return \Aimeos\MShop\Common\Manager\Iface Manager object
	 * @since 2015.08
	 */
	public function createManager( $name )
	{
		return \Aimeos\MShop\Factory::createManager( $this->getContext(), $name );
	}


	/**
	 * Returns the default catalog filter
	 *
	 * @return \Aimeos\MW\Criteria\Iface Criteria object for filtering
	 * @since 2017.03
	 */
	public function createFilter()
	{
		return \Aimeos\MShop\Factory::createManager( $this->getContext(), 'catalog' )->createSearch( true );
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
		return \Aimeos\MShop\Factory::createManager( $this->getContext(), 'catalog' )->getPath( $id, $domains );
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
		return \Aimeos\MShop\Factory::createManager( $this->getContext(), 'catalog' )->getTree( $id, $domains, $level, $search );
	}


	/**
	 * Returns the default catalog filter
	 *
	 * @param boolean True to add default criteria, e.g. status > 0
	 * @return \Aimeos\MW\Criteria\Iface Criteria object for filtering
	 * @since 2015.08
	 * @deprecated Use createFilter() instead
	 */
	public function createCatalogFilter( $default = true )
	{
		return $this->createFilter();
	}


	/**
	 * Returns the list of categries that are in the path to the root node including the one specified by its ID.
	 *
	 * @param integer $id Category ID to start from, null for root node
	 * @param string[] $domains Domain names of items that are associated with the categories and that should be fetched too
	 * @return array Associative list of items implementing \Aimeos\MShop\Catalog\Item\Iface with their IDs as keys
	 * @since 2015.08
	 * @deprecated Use getPath() instead
	 */
	public function getCatalogPath( $id, array $domains = array( 'text', 'media' ) )
	{
		return $this->getPath( $id, $domains );
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
	 * @since 2015.08
	 * @deprecated Use getTree() instead
	 */
	public function getCatalogTree( $id = null, array $domains = array( 'text', 'media' ),
		$level = \Aimeos\MW\Tree\Manager\Base::LEVEL_TREE, \Aimeos\MW\Criteria\Iface $search = null )
	{
		return $this->getTree( $id, $domains, $level, $search );
	}


	/**
	 * Returns the product item for the given ID if it's available
	 *
	 * @param array $ids List of product IDs
	 * @param array $domains Domain names of items that are associated with the products and that should be fetched too
	 * @return string[] List of product items implementing \Aimeos\MShop\Product\Item\Iface
	 * @throws \Aimeos\Controller\Frontend\Catalog\Exception If product isn't available
	 * @since 2015.08
	 * @deprecated Use getItems() method in index controller instead
	 */
	public function getProductItems( array $ids, array $domains = array( 'media', 'price', 'text' ) )
	{
		$cntl = \Aimeos\Controller\Frontend\Factory::createController( $this->getContext(), 'product' );
		return $cntl->getItems( $ids, $domains );
	}


	/**
	 * Returns the aggregated count of products for the given key.
	 *
	 * @param \Aimeos\MW\Criteria\Iface $filter Critera object which contains the filter conditions
	 * @param string $key Search key to aggregate for, e.g. "index.attribute.id"
	 * @return array Associative list of key values as key and the product count for this key as value
	 * @since 2015.08
	 * @deprecated Use method in index controller instead
	 */
	public function aggregateIndex( \Aimeos\MW\Criteria\Iface $filter, $key )
	{
		$cntl = \Aimeos\Controller\Frontend\Factory::createController( $this->getContext(), 'product' );
		return $cntl->aggregate( $filter, $key );
	}


	/**
	 * Returns the default index filter.
	 *
	 * @param string|null $sort Sortation of the product list like "name", "code", "price" and "position", null for no sortation
	 * @param string $direction Sort direction of the product list ("+", "-")
	 * @param integer $start Position in the list of found products where to begin retrieving the items
	 * @param integer $size Number of products that should be returned
	 * @param string $listtype Type of the product list, e.g. default, promotion, etc.
	 * @return \Aimeos\MW\Criteria\Iface Criteria object containing the conditions for searching
	 * @since 2015.08
	 * @deprecated Use method in index controller instead
	 */
	public function createIndexFilter( $sort = null, $direction = '+', $start = 0, $size = 100, $listtype = 'default' )
	{
		$cntl = \Aimeos\Controller\Frontend\Factory::createController( $this->getContext(), 'product' );
		return $cntl->createFilter( $sort, $direction, $start, $size, $listtype );
	}


	/**
	 * Returns the index filter for the given category ID.
	 *
	 * @param integer $catid ID of the category to get the product list from
	 * @param string|null $sort Sortation of the product list like "name", "code", "price" and "position", null for no sortation
	 * @param string $direction Sort direction of the product list ("+", "-")
	 * @param integer $start Position in the list of found products where to begin retrieving the items
	 * @param integer $size Number of products that should be returned
	 * @param string $listtype Type of the product list, e.g. default, promotion, etc.
	 * @return \Aimeos\MW\Criteria\Iface Criteria object containing the conditions for searching
	 * @since 2015.08
	 * @deprecated Use createIndexFilter() and addIndexFilterCategory() instead
	 */
	public function createIndexFilterCategory( $catid, $sort = null, $direction = '+', $start = 0, $size = 100, $listtype = 'default' )
	{
		$cntl = \Aimeos\Controller\Frontend\Factory::createController( $this->getContext(), 'product' );

		$filter = $cntl->createFilter( $sort, $direction, $start, $size, $listtype );
		$filter = $cntl->addFilterCategory( $filter, $catid, \Aimeos\MW\Tree\Manager\Base::LEVEL_ONE, $sort, $direction, $listtype );

		return $filter;
	}


	/**
	 * Returns the index filter for the given search string.
	 *
	 * @param string $input Search string entered by the user
	 * @param string|null $sort Sortation of the product list like "name", "price" and "relevance", null for no sortation
	 * @param string $direction Sort direction of the product list ("+", "-")
	 * @param integer $start Position in the list of found products where to begin retrieving the items
	 * @param integer $size Number of products that should be returned
	 * @param string $listtype List type of the text associated to the product, usually "default"
	 * @return \Aimeos\MW\Criteria\Iface Criteria object containing the conditions for searching
	 * @since 2015.08
	 * @deprecated Use createIndexFilter() and addIndexFilterText() instead
	 */
	public function createIndexFilterText( $input, $sort = null, $direction = '+', $start = 0, $size = 100, $listtype = 'default' )
	{
		$cntl = \Aimeos\Controller\Frontend\Factory::createController( $this->getContext(), 'product' );

		$filter = $cntl->createFilter( $sort, $direction, $start, $size, $listtype );
		$filter = $cntl->addFilterText( $filter, $input, $sort, $direction, $listtype );

		return $filter;
	}


	/**
	 * Returns the given search filter with the conditions attached for filtering by category.
	 *
	 * @param \Aimeos\MW\Criteria\Iface $search Criteria object used for product search
	 * @param string $catid Selected category by the user
	 * @return \Aimeos\MW\Criteria\Iface Criteria object containing the conditions for searching
	 * @since 2015.08
	 * @deprecated Use method in index controller instead
	 */
	public function addIndexFilterCategory( \Aimeos\MW\Criteria\Iface $search, $catid )
	{
		$cntl = \Aimeos\Controller\Frontend\Factory::createController( $this->getContext(), 'product' );
		return $cntl->addFilterCategory( $search, $catid );
	}


	/**
	 * Returns the given search filter with the conditions attached for filtering by text.
	 *
	 * @param \Aimeos\MW\Criteria\Iface $search Criteria object used for product search
	 * @param string $input Search string entered by the user
	 * @param string $listtype List type of the text associated to the product, usually "default"
	 * @return \Aimeos\MW\Criteria\Iface Criteria object containing the conditions for searching
	 * @since 2015.08
	 * @deprecated Use method in index controller instead
	 */
	public function addIndexFilterText( \Aimeos\MW\Criteria\Iface $search, $input, $listtype = 'default' )
	{
		$cntl = \Aimeos\Controller\Frontend\Factory::createController( $this->getContext(), 'product' );
		return $cntl->addFilterText( $search, $input, $listtype );
	}


	/**
	 * Returns the products from the index filtered by the given criteria object.
	 *
	 * @param \Aimeos\MW\Criteria\Iface $filter Critera object which contains the filter conditions
	 * @param string[] $domains Domain names of items that are associated with the products and that should be fetched too
	 * @param integer &$total Parameter where the total number of found products will be stored in
	 * @return array Ordered list of product items implementing \Aimeos\MShop\Product\Item\Iface
	 * @since 2015.08
	 * @deprecated Use method in index controller instead
	 */
	public function getIndexItems( \Aimeos\MW\Criteria\Iface $filter, array $domains = array( 'media', 'price', 'text' ), &$total = null )
	{
		$cntl = \Aimeos\Controller\Frontend\Factory::createController( $this->getContext(), 'product' );
		return $cntl->searchItems( $filter, $domains, $total );
	}


	/**
	 * Returns text filter for the given search string.
	 *
	 * @param string $input Search string entered by the user
	 * @param string|null $sort Sortation of the product list like "name" and "relevance", null for no sortation
	 * @param string $direction Sort direction of the product list ("asc", "desc")
	 * @param integer $start Position in the list of found products where to begin retrieving the items
	 * @param integer $size Number of products that should be returned
	 * @param string $listtype List type of the text associated to the product, usually "default"
	 * @param string $type Type of the text like "name", "short", "long", etc.
	 * @return \Aimeos\MW\Criteria\Iface Criteria object containing the conditions for searching
	 * @deprecated Use createFilter() method in index controller instead
	 */
	public function createTextFilter( $input, $sort = null, $direction = '+', $start = 0, $size = 25, $listtype = 'default', $type = 'name' )
	{
		$locale = $this->getContext()->getLocale();
		$langid = $locale->getLanguageId();

		$search = \Aimeos\MShop\Factory::createManager( $this->getContext(), 'index/text' )->createSearch( true );

		$expr = array(
			$search->compare( '>', $search->createFunction( 'index.text.relevance', array( $listtype, $langid, $input ) ), 0 ),
			$search->compare( '>', $search->createFunction( 'index.text.value', array( $listtype, $langid, $type, 'product' ) ), '' ),
		);

		$sortations = [];

		switch( $sort )
		{
			case 'name':
				$cmpfunc = $search->createFunction( 'index.text.value', array( $listtype, $langid, 'name', 'product' ) );
				$expr[] = $search->compare( '>=', $cmpfunc, '' );

				$sortfunc = $search->createFunction( 'sort:index.text.value', array( $listtype, $langid, 'name' ) );
				$sortations[] = $search->sort( $direction, $sortfunc );
				break;

			case 'relevance':
				// we don't need to sort by 'sort:index.text.relevance' because it's a boolean match (relevance is either 0 or 1)
		}

		$search->setConditions( $search->combine( '&&', $expr ) );
		$search->setSortations( $sortations );
		$search->setSlice( $start, $size );

		return $search;
	}


	/**
	 * Returns an list of product text strings matched by the filter.
	 *
	 * @param \Aimeos\MW\Criteria\Iface $filter Critera object which contains the filter conditions
	 * @return array Associative list of the product ID as key and the product text as value
	 * @deprecated Use searchItems() method in index controller to retrieve product items instead
	 */
	public function getTextList( \Aimeos\MW\Criteria\Iface $filter )
	{
		return \Aimeos\MShop\Factory::createManager( $this->getContext(), 'index/text' )->searchTexts( $filter );
	}
}
