<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2017-2021
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
	private $domains = [];
	private $filter;
	private $manager;
	private $rules;


	/**
	 * Common initialization for controller classes
	 *
	 * @param \Aimeos\MShop\Context\Item\Iface $context Common MShop context object
	 */
	public function __construct( \Aimeos\MShop\Context\Item\Iface $context )
	{
		parent::__construct( $context );

		$this->manager = \Aimeos\MShop::create( $context, 'index' );
		$this->filter = $this->manager->filter( true );

		/** controller/frontend/product/show-all
		 * Require products to be assigned to categories
		 *
		 * By default, products that are shown in the frontend must be assigned to
		 * at least one category. When changing this setting to TRUE, also products
		 * without categories will be shown in the frontend.
		 *
		 * Caution: If you have discount products or variant articles in selection
		 * products, these products/articles will be displayed in the frontend too
		 * when disabling this setting!
		 *
		 * @param bool FALSE if products must be assigned to categories, TRUE if not
		 * @since 2010.10
		 */
		if( $context->getConfig()->get( 'controller/frontend/product/show-all', false ) == false ) {
			$this->addExpression( $this->filter->compare( '!=', 'index.catalog.id', null ) );
		}

		$this->addExpression( $this->filter->getConditions() );
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
	 * @param string|null $value Search key for aggregating the value column
	 * @param string|null $type Type of the aggregation, empty string for count or "sum" or "avg" (average)
	 * @return \Aimeos\Map Associative list of key values as key and the product count for this key as value
	 * @since 2019.04
	 */
	public function aggregate( string $key, string $value = null, string $type = null ) : \Aimeos\Map
	{
		$this->filter->setConditions( $this->filter->and( $this->getConditions() ) );
		$this->filter->setSortations( $this->getSortations() );

		return $this->manager->aggregate( $this->filter, $key, $value, $type );
	}


	/**
	 * Adds attribute IDs for filtering where products must reference all IDs
	 *
	 * @param array|string $attrIds Attribute ID or list of IDs
	 * @return \Aimeos\Controller\Frontend\Product\Iface Product controller for fluent interface
	 * @since 2019.04
	 */
	public function allOf( $attrIds ) : Iface
	{
		if( !empty( $attrIds ) && ( $ids = $this->validateIds( (array) $attrIds ) ) !== [] )
		{
			$func = $this->filter->make( 'index.attribute:allof', [$ids] );
			$this->addExpression( $this->filter->compare( '!=', $func, null ) );
		}

		return $this;
	}


	/**
	 * Adds catalog IDs for filtering
	 *
	 * @param array|string $catIds Catalog ID or list of IDs
	 * @param string $listtype List type of the products referenced by the categories
	 * @param int $level Constant from \Aimeos\MW\Tree\Manager\Base if products in subcategories are matched too
	 * @return \Aimeos\Controller\Frontend\Product\Iface Product controller for fluent interface
	 * @since 2019.04
	 */
	public function category( $catIds, string $listtype = 'default', int $level = \Aimeos\MW\Tree\Manager\Base::LEVEL_ONE ) : Iface
	{
		if( !empty( $catIds ) && ( $ids = $this->validateIds( (array) $catIds ) ) !== [] )
		{
			if( $level != \Aimeos\MW\Tree\Manager\Base::LEVEL_ONE )
			{
				$list = map();
				$cntl = \Aimeos\Controller\Frontend::create( $this->getContext(), 'catalog' );

				foreach( $ids as $catId ) {
					$list->union( $cntl->root( $catId )->getTree( $level )->toList() );
				}

				$ids = $this->validateIds( $list->keys()->toArray() );
			}

			$func = $this->filter->make( 'index.catalog:position', [$listtype, $ids] );

			$this->addExpression( $this->filter->compare( '==', 'index.catalog.id', $ids ) );
			$this->addExpression( $this->filter->compare( '>=', $func, 0 ) );

			$func = $this->filter->make( 'sort:index.catalog:position', [$listtype, $ids] );
			$this->addExpression( $this->filter->sort( '+', $func ) );
			$this->addExpression( $this->filter->sort( '+', 'product.id' ) ); // prevent flaky order if products have same position
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
	public function compare( string $operator, string $key, $value ) : Iface
	{
		$this->addExpression( $this->filter->compare( $operator, $key, $value ) );
		return $this;
	}


	/**
	 * Returns the product for the given product code
	 *
	 * @param string $code Unique product code
	 * @return \Aimeos\MShop\Product\Item\Iface Product item including the referenced domains items
	 * @since 2019.04
	 */
	public function find( string $code ) : \Aimeos\MShop\Product\Item\Iface
	{
		$item = $this->manager->find( $code, $this->domains, 'product', null, null );
		return \Aimeos\MShop::create( $this->getContext(), 'rule' )->apply( $item, 'catalog' );
	}


	/**
	 * Creates a search function string for the given name and parameters
	 *
	 * @param string $name Name of the search function without parenthesis, e.g. "product:has"
	 * @param array $params List of parameters for the search function with numeric keys starting at 0
	 * @return string Search function string that can be used in compare()
	 */
	public function function( string $name, array $params ) : string
	{
		return $this->filter->make( $name, $params );
	}


	/**
	 * Returns the product for the given product ID
	 *
	 * @param string $id Unique product ID
	 * @return \Aimeos\MShop\Product\Item\Iface Product item including the referenced domains items
	 * @since 2019.04
	 */
	public function get( string $id ) : \Aimeos\MShop\Product\Item\Iface
	{
		$item = $this->manager->get( $id, $this->domains, null );
		return \Aimeos\MShop::create( $this->getContext(), 'rule' )->apply( $item, 'catalog' );
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
	public function has( string $domain, string $type = null, string $refId = null ) : Iface
	{
		$params = [$domain];
		!$type ?: $params[] = $type;
		!$refId ?: $params[] = $refId;

		$func = $this->filter->make( 'product:has', $params );
		$this->addExpression( $this->filter->compare( '!=', $func, null ) );
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
	public function oneOf( $attrIds ) : Iface
	{
		$attrIds = (array) $attrIds;

		foreach( $attrIds as $key => $entry )
		{
			if( is_array( $entry ) && ( $ids = $this->validateIds( $entry ) ) !== [] )
			{
				$func = $this->filter->make( 'index.attribute:oneof', [$ids] );
				$this->addExpression( $this->filter->compare( '!=', $func, null ) );
				unset( $attrIds[$key] );
			}
		}

		if( ( $ids = $this->validateIds( $attrIds ) ) !== [] )
		{
			$func = $this->filter->make( 'index.attribute:oneof', [$ids] );
			$this->addExpression( $this->filter->compare( '!=', $func, null ) );
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
	public function parse( array $conditions ) : Iface
	{
		if( ( $cond = $this->filter->parse( $conditions ) ) !== null ) {
			$this->addExpression( $cond );
		}

		return $this;
	}


	/**
	 * Adds price restrictions for filtering
	 *
	 * @param array|string $value Upper price limit, list of lower and upper price or NULL for no restrictions
	 * @return \Aimeos\Controller\Frontend\Product\Iface Product controller for fluent interface
	 * @since 2020.10
	 */
	public function price( $value = null ) : Iface
	{
		if( $value )
		{
			$value = (array) $value;
			$func = $this->filter->make( 'index.price:value', [$this->getContext()->getLocale()->getCurrencyId()] );

			$this->addExpression( $this->filter->compare( '<=', $func, sprintf( '%013.2F', end( $value ) ) ) );

			if( count( $value ) > 1 ) {
				$this->addExpression( $this->filter->compare( '>=', $func, sprintf( '%013.2F', reset( $value ) ) ) );
			}
		}

		return $this;
	}


	/**
	 * Adds product IDs for filtering
	 *
	 * @param array|string $prodIds Product ID or list of IDs
	 * @return \Aimeos\Controller\Frontend\Product\Iface Product controller for fluent interface
	 * @since 2019.04
	 */
	public function product( $prodIds ) : Iface
	{
		if( !empty( $prodIds ) && ( $ids = array_unique( $this->validateIds( (array) $prodIds ) ) ) !== [] ) {
			$this->addExpression( $this->filter->compare( '==', 'product.id', $ids ) );
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
	public function property( string $type, string $value = null, string $langid = null ) : Iface
	{
		$func = $this->filter->make( 'product:prop', [$type, $langid, $value] );
		$this->addExpression( $this->filter->compare( '!=', $func, null ) );
		return $this;
	}


	/**
	 * Returns the product for the given product URL name
	 *
	 * @param string $name Product URL name
	 * @return \Aimeos\MShop\Product\Item\Iface Product item including the referenced domains items
	 * @since 2019.04
	 */
	public function resolve( string $name ) : \Aimeos\MShop\Product\Item\Iface
	{
		$search = $this->manager->filter( null )->slice( 0, 1 )->add( ['index.text:url()' => $name] );

		if( ( $item = $this->manager->search( $search, $this->domains )->first() ) === null )
		{
			$msg = $this->getContext()->translate( 'controller/frontend', 'Unable to find product "%1$s"' );
			throw new \Aimeos\Controller\Frontend\Product\Exception( sprintf( $msg, $name ), 404 );
		}

		return \Aimeos\MShop::create( $this->getContext(), 'rule' )->apply( $item, 'catalog' );
	}


	/**
	 * Returns the products filtered by the previously assigned conditions
	 *
	 * @param int &$total Parameter where the total number of found products will be stored in
	 * @return \Aimeos\Map Ordered list of product items implementing \Aimeos\MShop\Product\Item\Iface
	 * @since 2019.04
	 */
	public function search( int &$total = null ) : \Aimeos\Map
	{
		$filter = clone $this->filter;

		/** controller/frontend/common/max-size
		 * Maximum number of items that can be fetched at once
		 *
		 * This setting limits the number of items that is returned to the frontend.
		 * The frontend can request any number of items up to that hard limit to
		 * prevent denial of service attacks by requesting large amount of data.
		 *
		 * @param int Number of items
		 */
		$maxsize = $this->getContext()->config()->get( 'controller/frontend/common/max-size', 500 );
		$filter->slice( $filter->getOffset(), min( $filter->getLimit(), $maxsize ) );

		$filter->setSortations( $this->getSortations() );
		$filter->setConditions( $filter->and( $this->getConditions() ) );

		$items = $this->manager->search( $filter, $this->domains, $total );
		return \Aimeos\MShop::create( $this->getContext(), 'rule' )->apply( $items, 'catalog' );
	}


	/**
	 * Sets the start value and the number of returned products for slicing the list of found products
	 *
	 * @param int $start Start value of the first product in the list
	 * @param int $limit Number of returned products
	 * @return \Aimeos\Controller\Frontend\Product\Iface Product controller for fluent interface
	 * @since 2019.04
	 */
	public function slice( int $start, int $limit ) : Iface
	{
		$this->filter->slice( $start, $limit );
		return $this;
	}


	/**
	 * Sets the sorting of the result list
	 *
	 * @param string|null $key Sorting of the result list like "name", "-name", "price", "-price", "code", "-code",
	 * 	"ctime, "-ctime", "relevance" or comma separated combinations and null for no sorting
	 * @return \Aimeos\Controller\Frontend\Product\Iface Product controller for fluent interface
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
				case 'relevance':
					break;

				case 'code':
					$this->addExpression( $this->filter->sort( $direction, 'product.code' ) );
					break;

				case 'ctime':
					$this->addExpression( $this->filter->sort( $direction, 'product.ctime' ) );
					break;

				case 'name':
					$langid = $this->getContext()->getLocale()->getLanguageId();

					$cmpfunc = $this->filter->make( 'index.text:name', [$langid] );
					$this->addExpression( $this->filter->compare( '!=', $cmpfunc, null ) );

					$sortfunc = $this->filter->make( 'sort:index.text:name', [$langid] );
					$this->addExpression( $this->filter->sort( $direction, $sortfunc ) );
					break;

				case 'price':
					$currencyid = $this->getContext()->getLocale()->getCurrencyId();
					$sortfunc = $this->filter->make( 'sort:index.price:value', [$currencyid] );

					$cmpfunc = $this->filter->make( 'index.price:value', [$currencyid] );
					$this->addExpression( $this->filter->compare( '!=', $cmpfunc, null ) );

					$this->addExpression( $this->filter->sort( $direction, $sortfunc ) );
					break;

				default:
					$this->addExpression( $this->filter->sort( $direction, $sortkey ) );
			}
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
	public function supplier( $supIds, string $listtype = 'default' ) : Iface
	{
		if( !empty( $supIds ) && ( $ids = array_unique( $this->validateIds( (array) $supIds ) ) ) !== [] )
		{
			$func = $this->filter->make( 'index.supplier:position', [$listtype, $ids] );

			$this->addExpression( $this->filter->compare( '==', 'index.supplier.id', $ids ) );
			$this->addExpression( $this->filter->compare( '>=', $func, 0 ) );

			$func = $this->filter->make( 'sort:index.supplier:position', [$listtype, $ids] );
			$this->addExpression( $this->filter->sort( '+', $func ) );
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
	public function text( string $text = null ) : Iface
	{
		if( !empty( $text ) )
		{
			$langid = $this->getContext()->getLocale()->getLanguageId();
			$func = $this->filter->make( 'index.text:relevance', [$langid, $text] );
			$sortfunc = $this->filter->make( 'sort:index.text:relevance', [$langid, $text] );

			$this->addExpression( $this->filter->or( [
				$this->filter->compare( '>', $func, 0 ),
				$this->filter->compare( '=~', 'product.code', $text ),
			] ) );
			$this->addExpression( $this->filter->sort( '-', $sortfunc ) );
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
	public function uses( array $domains ) : Iface
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
	protected function getCatalogIdsFromTree( \Aimeos\MShop\Catalog\Item\Iface $item ) : array
	{
		if( $item->getStatus() < 1 ) {
			return [];
		}

		$list = [$item->getId()];

		foreach( $item->getChildren() as $child ) {
			$list = array_merge( $list, $this->getCatalogIdsFromTree( $child ) );
		}

		return $list;
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


	/**
	 * Validates the given IDs as integers
	 *
	 * @param array $ids List of IDs to validate
	 * @return array List of validated IDs
	 */
	protected function validateIds( array $ids ) : array
	{
		$list = [];

		foreach( $ids as $id )
		{
			if( is_array( $id ) ) {
				$list[] = $this->validateIds( $id );
			} elseif( $id != '' && preg_match( '/^[A-Za-z0-9\-\_]+$/', $id ) === 1 ) {
				$list[] = (string) $id;
			}
		}

		return $list;
	}
}
