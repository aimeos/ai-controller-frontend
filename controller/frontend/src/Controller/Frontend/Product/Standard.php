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
	private $domains = [];
	private $filter;
	private $manager;
	private $sort;


	/**
	 * Common initialization for controller classes
	 *
	 * @param \Aimeos\MShop\Context\Item\Iface $context Common MShop context object
	 */
	public function __construct( \Aimeos\MShop\Context\Item\Iface $context )
	{
		parent::__construct( $context );

		$this->manager = \Aimeos\MShop::create( $context, 'index' );
		$this->filter = $this->manager->createSearch( true );
		$this->conditions[] = $this->filter->compare( '!=', 'index.catalog.id', null );
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
	 * Returns the aggregated count of products for the given key.
	 *
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
		if( !empty( $attrIds ) && ( $ids = array_unique( $this->validateIds( (array) $attrIds ) ) ) !== [] )
		{
			$func = $this->filter->createFunction( 'index.attribute:allof', [$ids] );
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
		if( !empty( $catIds ) && ( $ids = $this->validateIds( (array) $catIds ) ) !== [] )
		{
			if( $level != \Aimeos\MW\Tree\Manager\Base::LEVEL_ONE )
			{
				$list = [];
				$cntl = \Aimeos\Controller\Frontend::create( $this->getContext(), 'catalog' );

				foreach( $ids as $catId ) {
					$list += $cntl->root( $catId )->getTree( $level )->toList();
				}

				$ids = array_keys( $list );
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
	 * Returns the product for the given product code
	 *
	 * @param string $code Unique product code
	 * @return \Aimeos\MShop\Product\Item\Iface Product item including the referenced domains items
	 * @since 2019.04
	 */
	public function find( $code )
	{
		return $this->manager->findItem( $code, $this->domains, 'product', null, true );
	}


	/**
	 * Returns the product for the given product ID
	 *
	 * @param string $id Unique product ID
	 * @return \Aimeos\MShop\Product\Item\Iface Product item including the referenced domains items
	 * @since 2019.04
	 */
	public function get( $id )
	{
		return $this->manager->getItem( $id, $this->domains, true );
	}


	/**
	 * Adds a filter to return only items containing a reference to the given ID
	 *
	 * @param string $domain Domain name of the referenced item, e.g. "attribute"
	 * @param string|null $type Type code of the reference, e.g. "variant" or null for all types
	 * @param string|null $refId ID of the referenced item of the given domain or null for all references
	 * @return \Aimeos\Controller\Frontend\Product\Iface Product controller for fluent interface
	 * @since 2019.04
	 */
	public function has( $domain, $type = null, $refId = null )
	{
		$params = [$domain];
		!$type ?: $params[] = $type;
		!$refId ?: $params[] = $refId;

		$func = $this->filter->createFunction( 'product:has', $params );
		$this->conditions[] = $this->filter->compare( '!=', $func, null );
		return $this;
	}


	/**
	 * Adds attribute IDs for filtering where products must reference at least one ID
	 *
	 * If an array of ID lists is given, each ID list is added separately as condition.
	 *
	 * @param array|string $attrIds Attribute ID, list of IDs or array of lists with IDs
	 * @return \Aimeos\Controller\Frontend\Product\Iface Product controller for fluent interface
	 * @since 2019.04
	 */
	public function oneOf( $attrIds )
	{
		$attrIds = (array) $attrIds;

		foreach( $attrIds as $key => $entry )
		{
			if( is_array( $entry ) && ( $ids = array_unique( $this->validateIds( $entry ) ) ) !== [] )
			{
				$func = $this->filter->createFunction( 'index.attribute:oneof', [$ids] );
				$this->conditions[] = $this->filter->compare( '!=', $func, null );
				unset( $attrIds[$key] );
			}
		}

		if( ( $ids = array_unique( $this->validateIds( $attrIds ) ) ) !== [] )
		{
			$func = $this->filter->createFunction( 'index.attribute:oneof', [$ids] );
			$this->conditions[] = $this->filter->compare( '!=', $func, null );
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
		if( !empty( $prodIds ) && ( $ids = array_unique( $this->validateIds( (array) $prodIds ) ) ) !== [] ) {
			$this->conditions[] = $this->filter->compare( '==', 'product.id', $ids );
		}

		return $this;
	}


	/**
	 * Adds a filter to return only items containing the property
	 *
	 * @param string $type Type code of the property, e.g. "isbn"
	 * @param string|null $value Exact value of the property
	 * @param string|null $langid ISO country code (en or en_US) or null if not language specific
	 * @return \Aimeos\Controller\Frontend\Product\Iface Product controller for fluent interface
	 * @since 2019.04
	 */
	public function property( $type, $value = null, $langid = null )
	{
		$func = $this->filter->createFunction( 'product:prop', [$type, $langid, $value] );
		$this->conditions[] = $this->filter->compare( '!=', $func, null );
		return $this;
	}


	/**
	 * Returns the product for the given product URL name
	 *
	 * @param string $name Product URL name
	 * @return \Aimeos\MShop\Product\Item\Iface Product item including the referenced domains items
	 * @since 2019.04
	 */
	public function resolve( $name )
	{
		$langid = $this->getContext()->getLocale()->getLanguageId();

		$search = $this->manager->createSearch();
		$func = $search->createFunction( 'index.text:url', [$langid] );
		$search->setConditions( $search->compare( '==', $func, $name ) );

		$items = $this->manager->searchItems( $search, $this->domains );

		if( ( $item = reset( $items ) ) !== false ) {
			return $item;
		}

		$msg = $this->getContext()->getI18n()->dt( 'controller/frontend', 'Unable to find product "%1$s"' );
		throw new \Aimeos\Controller\Frontend\Product\Exception( sprintf( $msg, $name ) );
	}


	/**
	 * Returns the products filtered by the previously assigned conditions
	 *
	 * @param integer &$total Parameter where the total number of found products will be stored in
	 * @return \Aimeos\MShop\Product\Item\Iface[] Ordered list of product items
	 * @since 2019.04
	 */
	public function search( &$total = null )
	{
		$this->filter->setConditions( $this->filter->combine( '&&', $this->conditions ) );
		return $this->manager->searchItems( $this->filter, $this->domains, $total );
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
	 * Sets the sorting of the result list
	 *
	 * @param string|null $key Sorting of the result list like "name", "-name", "price", "-price", "code", "-code", "ctime, "-ctime" and "relevance", null for no sorting
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
			case null:
				$this->sort = null;
				break;

			case 'relevance':
				break;

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
				$expr = [];
				$context = $this->getContext();

				/** controller/frontend/product/price-types
				 * Use different product prices types for sorting by price
				 *
				 * In some cases, prices are stored with different types, eg. price per kg.
				 * This configuration option defines which types are incorporated when sorting
				 * the product list by price.
				 *
				 * @param array List of price types codes
				 * @since 2018.10
				 * @category Developer
				 */
				$types = $context->getConfig()->get( 'controller/frontend/product/price-types', ['default'] );
				$currencyid = $context->getLocale()->getCurrencyId();

				foreach( $types as $type )
				{
					$cmpfunc = $this->filter->createFunction( 'index.price:value', [$currencyid] );
					$expr[] = $this->filter->compare( '!=', $cmpfunc, null );
				}

				$this->conditions[] = $this->filter->combine( '||', $expr );

				$sortfunc = $this->filter->createFunction( 'sort:index.price:value', [$currencyid] );
				$this->sort = $this->filter->sort( $direction, $sortfunc );
				break;

			default:
				$this->sort = $this->filter->sort( $direction, $key );
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
		if( !empty( $supIds ) && ( $ids = array_unique( $this->validateIds( (array) $supIds ) ) ) !== [] )
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
		if( !empty( $text ) )
		{
			$langid = $this->getContext()->getLocale()->getLanguageId();
			$func = $this->filter->createFunction( 'index.text:relevance', [$langid, $text] );

			$this->conditions[] = $this->filter->compare( '>', $func, 0 );
		}

		return $this;
	}


	/**
	 * Sets the referenced domains that will be fetched too when retrieving items
	 *
	 * @param array $domains Domain names of the referenced items that should be fetched too
	 * @return \Aimeos\Controller\Frontend\Product\Iface Product controller for fluent interface
	 * @since 2019.04
	 */
	public function uses( array $domains )
	{
		$this->domains = $domains;
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
