<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2017
 * @package Controller
 * @subpackage Frontend
 */


namespace Aimeos\Controller\Frontend\Product;


/**
 * Default implementation of the product frontend controller.
 *
 * @package Controller
 * @subpackage Frontend
 */
class Standard
	extends \Aimeos\Controller\Frontend\Base
	implements Iface, \Aimeos\Controller\Frontend\Common\Iface
{
	/**
	 * Returns the given search filter with the conditions attached for filtering by attribute.
	 *
	 * @param \Aimeos\MW\Criteria\Iface $filter Criteria object used for product search
	 * @param array $attrIds List of attribute IDs for faceted search
	 * @param array $optIds List of OR-combined attribute IDs for faceted search
	 * @param array $attrIds Associative list of OR-combined attribute IDs per attribute type for faceted search
	 * @return \Aimeos\MW\Criteria\Iface Criteria object containing the conditions for searching
	 * @since 2017.03
	 */
	public function addFilterAttribute( \Aimeos\MW\Criteria\Iface $filter, array $attrIds, array $optIds, array $oneIds )
	{
		if( !empty( $attrIds ) )
		{
			$attrIds = $this->validateIds( $attrIds );

			$func = $filter->createFunction( 'index.attributeaggregate', array( $attrIds ) );
			$expr = array(
				$filter->compare( '==', $func, count( $attrIds ) ),
				$filter->getConditions(),
			);
			$filter->setConditions( $filter->combine( '&&', $expr ) );
		}

		if( !empty( $optIds ) )
		{
			$optIds = $this->validateIds( $optIds );

			$func = $filter->createFunction( 'index.attributeaggregate', array( $optIds ) );
			$expr = array(
				$filter->compare( '>', $func, 0 ),
				$filter->getConditions(),
			);
			$filter->setConditions( $filter->combine( '&&', $expr ) );
		}

		foreach( $oneIds as $type => $list )
		{
			if( ( $list = $this->validateIds( (array) $list ) ) !== [] )
			{
				$func = $filter->createFunction( 'index.attributeaggregate', array( $list ) );
				$expr = array(
					$filter->compare( '>', $func, 0 ),
					$filter->getConditions(),
				);
				$filter->setConditions( $filter->combine( '&&', $expr ) );
			}
		}

		return $filter;
	}


	/**
	 * Returns the given search filter with the conditions attached for filtering by category.
	 *
	 * @param \Aimeos\MW\Criteria\Iface $filter Criteria object used for product search
	 * @param string|array $catId Selected category by the user
	 * @param integer $level Constant for current category only, categories of next level (LEVEL_LIST) or whole subtree (LEVEL_SUBTREE)
	 * @param string|null $sort Sortation of the product list like "name", "code", "price" and "position", null for no sortation
	 * @param string $direction Sort direction of the product list ("+", "-")
	 * @param string $listtype List type of the product associated to the category, usually "default"
	 * @return \Aimeos\MW\Criteria\Iface Criteria object containing the conditions for searching
	 * @since 2017.03
	 */
	public function addFilterCategory( \Aimeos\MW\Criteria\Iface $filter, $catId,
		$level = \Aimeos\MW\Tree\Manager\Base::LEVEL_ONE, $sort = null, $direction = '+', $listtype = 'default' )
	{
		$catIds = ( !is_array( $catId ) ? explode( ',', $catId ) : $catId );

		if( $level != \Aimeos\MW\Tree\Manager\Base::LEVEL_ONE )
		{
			$list = [];
			$cntl = \Aimeos\Controller\Frontend\Factory::createController( $this->getContext(), 'catalog' );

			foreach( $catIds as $catId )
			{
				$tree = $cntl->getCatalogTree( $catId, [], $level );
				$list = array_merge( $list, $this->getCatalogIdsFromTree( $tree ) );
			}

			$catIds = $list;
		}

		$expr = array( $filter->compare( '==', 'index.catalog.id', array_unique( $catIds ) ) );
		$expr[] = $filter->getConditions();

		if( $sort === 'relevance' )
		{
			$cmpfunc = $filter->createFunction( 'index.catalog.position', array( $listtype, $catIds ) );
			$expr[] = $filter->compare( '>=', $cmpfunc, 0 );

			$sortfunc = $filter->createFunction( 'sort:index.catalog.position', array( $listtype, $catIds ) );
			$filter->setSortations( array( $filter->sort( $direction, $sortfunc ) ) );
		}

		$filter->setConditions( $filter->combine( '&&', $expr ) );

		return $filter;
	}


	/**
	 * Returns the given search filter with the conditions attached for filtering by text.
	 *
	 * @param \Aimeos\MW\Criteria\Iface $filter Criteria object used for product search
	 * @param string $input Search string entered by the user
	 * @param string|null $sort Sortation of the product list like "name", "code", "price" and "position", null for no sortation
	 * @param string $direction Sort direction of the product list ("+", "-")
	 * @param string $listtype List type of the text associated to the product, usually "default"
	 * @return \Aimeos\MW\Criteria\Iface Criteria object containing the conditions for searching
	 * @since 2017.03
	 */
	public function addFilterText( \Aimeos\MW\Criteria\Iface $filter, $input, $sort = null, $direction = '+', $listtype = 'default' )
	{
		$langid = $this->getContext()->getLocale()->getLanguageId();
		$cmpfunc = $filter->createFunction( 'index.text.relevance', array( $listtype, $langid, $input ) );
		$expr = array( $filter->compare( '>', $cmpfunc, 0 ), $filter->getConditions() );

		return $filter->setConditions( $filter->combine( '&&', $expr ) );
	}


	/**
	 * Returns the aggregated count of products for the given key.
	 *
	 * @param \Aimeos\MW\Criteria\Iface $filter Critera object which contains the filter conditions
	 * @param string $key Search key to aggregate for, e.g. "index.attribute.id"
	 * @return array Associative list of key values as key and the product count for this key as value
	 * @since 2017.03
	 */
	public function aggregate( \Aimeos\MW\Criteria\Iface $filter, $key )
	{
		return \Aimeos\MShop\Factory::createManager( $this->getContext(), 'index' )->aggregate( $filter, $key );
	}


	/**
	 * Returns the default product filter.
	 *
	 * @param string|null $sort Sortation of the product list like "name", "code", "price" and "position", null for no sortation
	 * @param string $direction Sort direction of the product list ("+", "-")
	 * @param integer $start Position in the list of found products where to begin retrieving the items
	 * @param integer $size Number of products that should be returned
	 * @param string $listtype Type of the product list, e.g. default, promotion, etc.
	 * @return \Aimeos\MW\Criteria\Iface Criteria object containing the conditions for searching
	 * @since 2017.03
	 */
	public function createFilter( $sort = null, $direction = '+', $start = 0, $size = 100, $listtype = 'default' )
	{
		$sortations = [];
		$context = $this->getContext();

		$search = \Aimeos\MShop\Factory::createManager( $context, 'index' )->createSearch( true );
		$expr = array( $search->compare( '!=', 'index.catalog.id', null ) );

		switch( $sort )
		{
			case 'code':
				$sortations[] = $search->sort( $direction, 'product.code' );
				break;

			case 'name':
				$langid = $context->getLocale()->getLanguageId();

				$cmpfunc = $search->createFunction( 'index.text.value', array( $listtype, $langid, 'name', 'product' ) );
				$expr[] = $search->compare( '>=', $cmpfunc, '' );

				$sortfunc = $search->createFunction( 'sort:index.text.value', array( $listtype, $langid, 'name' ) );
				$sortations[] = $search->sort( $direction, $sortfunc );
				break;

			case 'price':
				$currencyid = $context->getLocale()->getCurrencyId();

				$cmpfunc = $search->createFunction( 'index.price.value', array( $listtype, $currencyid, 'default' ) );
				$expr[] = $search->compare( '>=', $cmpfunc, '0.00' );

				$sortfunc = $search->createFunction( 'sort:index.price.value', array( $listtype, $currencyid, 'default' ) );
				$sortations[] = $search->sort( $direction, $sortfunc );
				break;
		}

		$expr[] = $search->getConditions();

		$search->setConditions( $search->combine( '&&', $expr ) );
		$search->setSortations( $sortations );
		$search->setSlice( $start, $size );

		return $search;
	}


	/**
	 * Returns the product for the given product ID from the product
	 *
	 * @param string $productId Unique product ID
	 * @param string[] $domains Domain names of items that are associated with the products and that should be fetched too
	 * @return \Aimeos\MShop\Product\Item\Iface Product item including the referenced domains items
	 * @since 2017.03
	 */
	public function getItem( $productId, array $domains = array( 'attribute', 'media', 'price', 'product', 'product/property', 'text' ) )
	{
		return \Aimeos\MShop\Factory::createManager( $this->getContext(), 'product' )->getItem( $productId, $domains, true );
	}


	/**
	 * Returns the product for the given product ID from the product
	 *
	 * @param string[] $productIds List of unique product ID
	 * @param string[] $domains Domain names of items that are associated with the products and that should be fetched too
	 * @return \Aimeos\MShop\Product\Item\Iface[] Associative list of product IDs as keys and product items as values
	 * @since 2017.03
	 */
	public function getItems( array $productIds, array $domains = array( 'media', 'price', 'text' ) )
	{
		$manager = \Aimeos\MShop\Factory::createManager( $this->getContext(), 'product' );

		$search = $manager->createSearch( true );
		$expr = array(
			$search->compare( '==', 'product.id', $productIds ),
			$search->getConditions(),
		);
		$search->setConditions( $search->combine( '&&', $expr ) );
		$search->setSlice( 0, count( $productIds ) );

		return $manager->searchItems( $search, $domains );
	}


	/**
	 * Returns the products from the product filtered by the given criteria object.
	 *
	 * @param \Aimeos\MW\Criteria\Iface $filter Critera object which contains the filter conditions
	 * @param string[] $domains Domain names of items that are associated with the products and that should be fetched too
	 * @param integer &$total Parameter where the total number of found products will be stored in
	 * @return array Ordered list of product items implementing \Aimeos\MShop\Product\Item\Iface
	 * @since 2017.03
	 */
	public function searchItems( \Aimeos\MW\Criteria\Iface $filter, array $domains = array( 'media', 'price', 'text' ), &$total = null )
	{
		return \Aimeos\MShop\Factory::createManager( $this->getContext(), 'index' )->searchItems( $filter, $domains, $total );
	}


	/**
	 * Returns the list of catalog IDs for the given catalog tree
	 *
	 * @param \Aimeos\MShop\Catalog\Item\Iface $item Catalog item with children
	 * @return array List of catalog IDs
	 */
	protected function getCatalogIdsFromTree( \Aimeos\MShop\Catalog\Item\Iface $item )
	{
		$list = array( $item->getId() );

		foreach( $item->getChildren() as $child ) {
			$list = array_merge( $list, $this->getCatalogIdsFromTree( $child ) );
		}

		return $list;
	}


	/**
	 * Validates the given IDs as integers
	 *
	 * @param array $ids List of IDs to validate
	 * @return array List of validated IDs
	 */
	protected function validateIds( array $ids )
	{
		$list = [];

		foreach( $ids as $id )
		{
			if( $id != '' ) {
				$list[] = (int) $id;
			}
		}

		return $ids;
	}
}
