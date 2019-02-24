<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2017-2018
 * @package Controller
 * @subpackage Frontend
 */


namespace Aimeos\Controller\Frontend\Attribute;


/**
 * Interface for attribute frontend controllers
 *
 * @package Controller
 * @subpackage Frontend
 */
interface Iface
{
	/**
	 * Adds attribute IDs for filtering
	 *
	 * @param array|string $attrIds Attribute ID or list of IDs
	 * @return \Aimeos\Controller\Frontend\Attribute\Iface Attribute controller for fluent interface
	 * @since 2019.04
	 */
	public function attribute( $attrIds );

	/**
	 * Adds generic condition for filtering attributes
	 *
	 * @param string $operator Comparison operator, e.g. "==", "!=", "<", "<=", ">=", ">", "=~", "~="
	 * @param string $key Search key defined by the attribute manager, e.g. "attribute.status"
	 * @param array|string $value Value or list of values to compare to
	 * @return \Aimeos\Controller\Frontend\Attribute\Iface Attribute controller for fluent interface
	 * @since 2019.04
	 */
	public function compare( $operator, $key, $value );

	/**
	 * Adds the domain of the attributes for filtering
	 *
	 * @param string $domain Domain of the attributes
	 * @return \Aimeos\Controller\Frontend\Attribute\Iface Attribute controller for fluent interface
	 * @since 2019.04
	 */
	public function domain( $domain );

	/**
	 * Returns the attribute for the given attribute code
	 *
	 * @param string $code Unique attribute code
	 * @param string $type Type assigned to the attribute
	 * @return \Aimeos\MShop\Attribute\Item\Iface Attribute item including the referenced domains items
	 * @since 2019.04
	 */
	public function find( $code, $type );

	/**
	 * Returns the attribute for the given attribute ID
	 *
	 * @param string $id Unique attribute ID
	 * @return \Aimeos\MShop\Attribute\Item\Iface Attribute item including the referenced domains items
	 * @since 2019.04
	 */
	public function get( $id );

	/**
	 * Adds a filter to return only items containing a reference to the given ID
	 *
	 * @param string $domain Domain name of the referenced item, e.g. "price"
	 * @param string|null $type Type code of the reference, e.g. "default" or null for all types
	 * @param string|null $refId ID of the referenced item of the given domain or null for all references
	 * @return \Aimeos\Controller\Frontend\Product\Iface Product controller for fluent interface
	 * @since 2019.04
	 */
	public function has( $domain, $type = null, $refId = null );

	/**
	 * Parses the given array and adds the conditions to the list of conditions
	 *
	 * @param array $conditions List of conditions, e.g. ['&&' => [['>' => ['attribute.status' => 0]], ['==' => ['attribute.type' => 'color']]]]
	 * @return \Aimeos\Controller\Frontend\Attribute\Iface Attribute controller for fluent interface
	 * @since 2019.04
	 */
	public function parse( array $conditions );

	/**
	 * Adds a filter to return only items containing the property
	 *
	 * @param string $type Type code of the property, e.g. "isbn"
	 * @param string|null $value Exact value of the property
	 * @param string|null $langId ISO country code (en or en_US) or null if not language specific
	 * @return \Aimeos\Controller\Frontend\Attribute\Iface Product controller for fluent interface
	 * @since 2019.04
	 */
	public function property( $type, $value = null, $langId = null );

	/**
	 * Returns the attributes filtered by the previously assigned conditions
	 *
	 * @param integer &$total Parameter where the total number of found attributes will be stored in
	 * @return \Aimeos\MShop\Attribute\Item\Iface[] Ordered list of attribute items
	 * @since 2019.04
	 */
	public function search( &$total = null );

	/**
	 * Sets the start value and the number of returned attributes for slicing the list of found attributes
	 *
	 * @param integer $start Start value of the first attribute in the list
	 * @param integer $limit Number of returned attributes
	 * @return \Aimeos\Controller\Frontend\Attribute\Iface Attribute controller for fluent interface
	 * @since 2019.04
	 */
	public function slice( $start, $limit );

	/**
	 * Sets the sorting of the result list
	 *
	 * @param string|null $key Sorting of the result list like "position", null for no sorting
	 * @return \Aimeos\Controller\Frontend\Attribute\Iface Attribute controller for fluent interface
	 * @since 2019.04
	 */
	public function sort( $key = null );

	/**
	 * Adds attribute types for filtering
	 *
	 * @param array|string $codes Attribute type or list of types
	 * @return \Aimeos\Controller\Frontend\Attribute\Iface Attribute controller for fluent interface
	 * @since 2019.04
	 */
	public function type( $codes );

	/**
	 * Sets the referenced domains that will be fetched too when retrieving items
	 *
	 * @param array $domains Domain names of the referenced items that should be fetched too
	 * @return \Aimeos\Controller\Frontend\Attribute\Iface Attribute controller for fluent interface
	 * @since 2019.04
	 */
	public function uses( array $domains );
}
