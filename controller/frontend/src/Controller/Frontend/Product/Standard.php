<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2017-2019
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
	private $conditions = [];
	private $filter;
	private $manager;
	private $sort;


	/**
	 * Common initialization for controller classes.
	 *
	 * @param \Aimeos\MShop\Context\Item\Iface $context Common MShop context object
	 */
	public function __construct( \Aimeos\MShop\Context\Item\Iface $context )
	{
		parent::__construct( $context );

		$this->manager = \Aimeos\MShop::create( $context, 'index' );
		$this->filter = $this->manager->createSearch( true );
		$this->conditions[] = $this->filter->compare( '!=', 'index.catalog.id', null );

		/** controller/frontend/order/ignore-dates
		 * Ignore start and end dates of products
		 *
		 * Usually, products are only shown in the product list if their start/end
		 * dates are not set or if the current date is withing the start/end date
		 * range of the product. This settings will list all products that wouldn't
		 * be shown due to their start/end dates but they still can't be bought.
		 *
		 * @param boolean True to show products whose start/end date range doesn't match the current date, false to hide them
		 * @since 2017.08
		 * @category Developer
		 */
		if( $context->getConfig()->get( 'controller/frontend/product/ignore-dates', false ) ) {
			$this->conditions[] = $this->filter->compare( '>', 'product.status', 0 );
		} else {
			$this->conditions[] = $this->filter->getConditions();
		}
	}


	/**
	 * Returns the aggregated count of products for the given key.
	 *
	 * @param \Aimeos\MW\Criteria\Iface $filter Critera object which contains the filter conditions
	 * @param string $key Search key to aggregate for, e.g. "index.attribute.id"
	 * @return array Associative list of key values as key and the product count for this key as value
	 * @since 2019.04
	 */
	public function aggregate( $key )
	{
		$this->filter->setConditions( $this->filter->combine( '&&', $this->conditions ) );
		return $this->manager->aggregate( $this->filter, $key );
	}


	/**
	 * Adds attribute IDs for filtering where products must reference all IDs
	 *
	 * @param array|string $attrIds Attribute ID or list of IDs
	 * @return \Aimeos\Controller\Frontend\Product\Iface Product controller for fluent interface
	 * @since 2019.04
	 */
	public function allOf( $attrIds )
	{
		if( ( $ids = array_unique( $this->validateIds( (array) $attrIds ) ) ) !== [] )
		{
			$func = $this->filter->createFunction( 'index.attribute:all', [$ids] );
			$this->conditions[] = $this->filter->compare( '!=', $func, null );
		}

		return $this;
	}


	/**
	 * Adds catalog IDs for filtering
	 *
	 * @param array|string $catIds Catalog ID or list of IDs
	 * @param string $listtype List type of the products referenced by the categories
	 * @param integer $level Constant from \Aimeos\MW\Tree\Manager\Base if products in subcategories are matched too
	 * @return \Aimeos\Controller\Frontend\Product\Iface Product controller for fluent interface
	 * @since 2019.04
	 */
	public function category( $catIds, $listtype = 'default', $level = \Aimeos\MW\Tree\Manager\Base::LEVEL_ONE )
	{
		if( ( $ids = $this->validateIds( (array) $catIds ) ) !== [] )
		{
			if( $level != \Aimeos\MW\Tree\Manager\Base::LEVEL_ONE )
			{
				$list = [];
				$cntl = \Aimeos\Controller\Frontend::create( $this->getContext(), 'catalog' );

				foreach( $ids as $catId )
				{
					$tree = $cntl->getTree( $catId, [], $level );
					$list = array_merge( $list, $this->getCatalogIdsFromTree( $tree ) );
				}

				$ids = array_unique( $list );
			}

			$func = $this->filter->createFunction( 'index.catalog:position', [$listtype, $ids] );

			$this->conditions[] = $this->filter->compare( '==', 'index.catalog.id', $ids );
			$this->conditions[] = $this->filter->compare( '>=', $func, 0 );

			$func = $this->filter->createFunction( 'sort:index.catalog:position', [$listtype, $ids] );
			$this->sort = $this->filter->sort( '+', $func );
		}

		return $this;
	}


	/**
	 * Adds generic condition for filtering products
	 *
	 * @param string $operator Comparison operator, e.g. "==", "!=", "<", "<=", ">=", ">", "=~", "~="
	 * @param string $key Search key defined by the product manager, e.g. "product.status"
	 * @param array|string $value Value or list of values to compare to
	 * @return \Aimeos\Controller\Frontend\Product\Iface Product controller for fluent interface
	 * @since 2019.04
	 */
	public function compare( $operator, $key, $value )
	{
		$this->conditions[] = $this->filter->compare( $operator, $key, $value );
		return $this;
	}


	/**
	 * Returns the product for the given product ID
	 *
	 * @param string $id Unique product ID
	 * @param string[] $domains Domain names of items that are associated with the products and that should be fetched too
	 * @return \Aimeos\MShop\Product\Item\Iface Product item including the referenced domains items
	 * @since 2019.04
	 */
	public function get( $id, $domains = ['media', 'price', 'text'] )
	{
		return $this->manager->getItem( $id, $domains );
	}


	/**
	 * Returns the product for the given product code
	 *
	 * @param string $code Unique product code
	 * @param string[] $domains Domain names of items that are associated with the products and that should be fetched too
	 * @return \Aimeos\MShop\Product\Item\Iface Product item including the referenced domains items
	 * @since 2019.04
	 */
	public function find( $code, $domains = ['media', 'price', 'text'] )
	{
		return $this->manager->findItem( $code, $domains );
	}


	/**
	 * Adds attribute IDs for filtering where products must reference at least one ID
	 *
	 * @param array|string $attrIds Attribute ID or list of IDs
	 * @return \Aimeos\Controller\Frontend\Product\Iface Product controller for fluent interface
	 * @since 2019.04
	 */
	public function oneOf( $attrIds )
	{
		if( ( $ids = array_unique( $this->validateIds( (array) $attrIds ) ) ) !== [] ) {
			$this->conditions[] = $this->filter->compare( '==', 'index.attribute.id', $ids );
		}

		return $this;
	}


	/**
	 * Parses the given array and adds the conditions to the list of conditions
	 *
	 * @param array $conditions List of conditions, e.g. [['>' => ['product.status' => 0]], ['==' => ['product.type' => 'default']]]
	 * @return \Aimeos\Controller\Frontend\Product\Iface Product controller for fluent interface
	 * @since 2019.04
	 */
	public function parse( array $conditions )
	{
		$this->conditions[] = $this->filter->toConditions( $conditions );
		return $this;
	}


	/**
	 * Adds product IDs for filtering
	 *
	 * @param array|string $prodIds Product ID or list of IDs
	 * @return \Aimeos\Controller\Frontend\Product\Iface Product controller for fluent interface
	 * @since 2019.04
	 */
	public function product( $prodIds )
	{
		if( ( $ids = array_unique( $this->validateIds( (array) $prodIds ) ) ) !== [] ) {
			$this->conditions[] = $this->filter->compare( '==', 'product.id', $ids );
		}

		return $this;
	}


	/**
	 * Returns the products filtered by the previously assigned conditions
	 *
	 * @param string[] $domains Domain names of items that are associated with the products and that should be fetched too
	 * @param integer &$total Parameter where the total number of found products will be stored in
	 * @return array Ordered list of product items implementing \Aimeos\MShop\Product\Item\Iface
	 * @since 2019.04
	 */
	public function search( $domains = ['media', 'price', 'text'], &$total = null )
	{
		$this->filter->setConditions( $this->filter->combine( '&&', $this->conditions ) );
		return $this->manager->searchItems( $this->filter, $domains, $total );
	}


	/**
	 * Sets the start value and the number of returned products for slicing the list of found products
	 *
	 * @param integer $start Start value of the first product in the list
	 * @param integer $limit Number of returned products
	 * @return \Aimeos\Controller\Frontend\Product\Iface Product controller for fluent interface
	 * @since 2019.04
	 */
	public function slice( $start, $limit )
	{
		$this->filter->setSlice( $start, $limit );
		return $this;
	}


	/**
	 * Sets the sorting of the product list
	 *
	 * @param string|null $sort Sortation of the product list like "name", "-name", "price", "-price", "code", "-code", "ctime, "-ctime" and "relevance", null for no sortation
	 * @return \Aimeos\Controller\Frontend\Product\Iface Product controller for fluent interface
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
			case 'code':
				$this->sort = $this->filter->sort( $direction, 'product.code' );
				break;

			case 'ctime':
				$this->sort = $this->filter->sort( $direction, 'product.ctime' );
				break;

			case 'name':
				$langid = $this->getContext()->getLocale()->getLanguageId();

				$cmpfunc = $this->filter->createFunction( 'index.text:name', [$langid] );
				$this->conditions[] = $this->filter->compare( '!=', $cmpfunc, null );

				$sortfunc = $this->filter->createFunction( 'sort:index.text:name', [$langid] );
				$this->sort = $this->filter->sort( $direction, $sortfunc );
				break;

			case 'price':
				$currencyid = $this->getContext()->getLocale()->getCurrencyId();

				$cmpfunc = $this->filter->createFunction( 'index.price:value', [$currencyid] );
				$this->conditions[] = $this->filter->compare( '!=', $cmpfunc, null );

				$sortfunc = $this->filter->createFunction( 'sort:index.price:value', [$currencyid] );
				$this->sort = $this->filter->sort( $direction, $sortfunc );
				break;

			case null:
				$this->sort = null;
				break;
		}

		if( $this->sort ) {
			$this->filter->setSortations( [$this->sort] );
		}

		return $this;
	}


	/**
	 * Adds supplier IDs for filtering
	 *
	 * @param array|string $supIds Supplier ID or list of IDs
	 * @param string $listtype List type of the products referenced by the suppliers
	 * @return \Aimeos\Controller\Frontend\Product\Iface Product controller for fluent interface
	 * @since 2019.04
	 */
	public function supplier( $supIds, $listtype = 'default' )
	{
		if( ( $ids = array_unique( $this->validateIds( (array) $supIds ) ) ) !== [] )
		{
			$func = $this->filter->createFunction( 'index.supplier:position', [$listtype, $ids] );

			$this->conditions[] = $this->filter->compare( '==', 'index.supplier.id', $ids );
			$this->conditions[] = $this->filter->compare( '>=', $func, 0 );

			$func = $this->filter->createFunction( 'sort:index.supplier:position', [$listtype, $ids] );
			$this->sort = $this->filter->sort( '+', $func );
		}

		return $this;
	}


	/**
	 * Adds input string for full text search
	 *
	 * @param string|null $text User input for full text search
	 * @return \Aimeos\Controller\Frontend\Product\Iface Product controller for fluent interface
	 * @since 2019.04
	 */
	public function text( $text )
	{
		if( $text )
		{
			$langid = $this->getContext()->getLocale()->getLanguageId();
			$func = $this->filter->createFunction( 'index.text:relevance', [$langid, $text] );

			$this->conditions[] = $this->filter->compare( '>', $func, 0 );
		}

		return $this;
	}


	/**
	 * Returns the list of catalog IDs for the given catalog tree
	 *
	 * @param \Aimeos\MShop\Catalog\Item\Iface $item Catalog item with children
	 * @return array List of catalog IDs
	 */
	protected function getCatalogIdsFromTree( \Aimeos\MShop\Catalog\Item\Iface $item )
	{
		if( $item->getStatus() < 1 ) {
			return [];
		}

		$list = [ $item->getId() ];

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
			if( $id != '' && preg_match( '/^[A-Za-z0-9\-\_]+$/', $id ) === 1 ) {
				$list[] = (string) $id;
			}
		}

		return $list;
	}
}
