<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2016-2018
 * @package Controller
 * @subpackage Frontend
 */


namespace Aimeos\Controller\Frontend\Product\Decorator;


/**
 * Base for product frontend controller decorators
 *
 * @package Controller
 * @subpackage Frontend
 */
abstract class Base
	extends \Aimeos\Controller\Frontend\Base
	implements \Aimeos\Controller\Frontend\Common\Decorator\Iface, \Aimeos\Controller\Frontend\Product\Iface
{
	private $controller;


	/**
	 * Initializes the controller decorator.
	 *
	 * @param \Aimeos\Controller\Frontend\Iface $controller Controller object
	 * @param \Aimeos\MShop\Context\Item\Iface $context Context object with required objects
	 */
	public function __construct( \Aimeos\Controller\Frontend\Iface $controller, \Aimeos\MShop\Context\Item\Iface $context )
	{
		\Aimeos\MW\Common\Base::checkClass( '\\Aimeos\\Controller\\Frontend\\Product\\Iface', $controller );

		$this->controller = $controller;

		parent::__construct( $context );
	}


	/**
	 * Passes unknown methods to wrapped objects.
	 *
	 * @param string $name Name of the method
	 * @param array $param List of method parameter
	 * @return mixed Returns the value of the called method
	 * @throws \Aimeos\Controller\Frontend\Exception If method call failed
	 */
	public function __call( $name, array $param )
	{
		return @call_user_func_array( array( $this->controller, $name ), $param );
	}


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
		return $this->controller->addFilterAttribute( $filter, $attrIds, $optIds, $oneIds );
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
		return $this->controller->addFilterCategory( $filter, $catId, $level, $sort, $direction, $listtype );
	}


	/**
	 * Returns the given search filter with the conditions attached for filtering by suppliers.
	 *
	 * @param \Aimeos\MW\Criteria\Iface $filter Criteria object used for product search
	 * @param array $supIds List of supplier IDs for faceted search
	 * @return \Aimeos\MW\Criteria\Iface Criteria object containing the conditions for searching
	 * @since 2018.07
	 */
	public function addFilterSupplier( \Aimeos\MW\Criteria\Iface $filter, array $supIds )
	{
		return $this->controller->addFilterSupplier( $filter, $supIds );
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
		return $this->controller->addFilterText( $filter, $input, $sort, $direction, $listtype );
	}


	/**
	 * Returns the aggregated count of products from the product for the given key.
	 *
	 * @param \Aimeos\MW\Criteria\Iface $filter Critera object which contains the filter conditions
	 * @param string $key Search key to aggregate for, e.g. "product.attribute.id"
	 * @return array Associative list of key values as key and the product count for this key as value
	 * @since 2015.08
	 */
	public function aggregate( \Aimeos\MW\Criteria\Iface $filter, $key )
	{
		return $this->controller->aggregate( $filter, $key );
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
	 * @since 2015.08
	 */
	public function createFilter( $sort = null, $direction = '+', $start = 0, $size = 100, $listtype = 'default' )
	{
		return $this->controller->createFilter( $sort, $direction, $start, $size, $listtype );
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
		return $this->controller->getItem( $productId, $domains );
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
		return $this->controller->getItems( $productIds, $domains );
	}


	/**
	 * Returns the products from the product filtered by the given criteria object.
	 *
	 * @param \Aimeos\MW\Criteria\Iface $filter Critera object which contains the filter conditions
	 * @param string[] $domains Domain names of items that are associated with the products and that should be fetched too
	 * @param integer &$total Parameter where the total number of found products will be stored in
	 * @return array Ordered list of product items implementing \Aimeos\MShop\Product\Item\Iface
	 * @since 2015.08
	 */
	public function searchItems( \Aimeos\MW\Criteria\Iface $filter, array $domains = array( 'media', 'price', 'text' ), &$total = null )
	{
		return $this->controller->searchItems( $filter, $domains, $total );
	}


	/**
	 * Returns the frontend controller
	 *
	 * @return \Aimeos\Controller\Frontend\Product\Iface Frontend controller object
	 */
	protected function getController()
	{
		return $this->controller;
	}
}
