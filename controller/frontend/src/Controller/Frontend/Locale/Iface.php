<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2017-2018
 * @package Controller
 * @subpackage Frontend
 */


namespace Aimeos\Controller\Frontend\Locale;


/**
 * Interface for locale frontend controllers
 *
 * @package Controller
 * @subpackage Frontend
 */
interface Iface
{
	/**
	 * Adds generic condition for filtering
	 *
	 * @param string $operator Comparison operator, e.g. "==", "!=", "<", "<=", ">=", ">", "=~", "~="
	 * @param string $key Search key defined by the locale manager, e.g. "locale.status"
	 * @param array|string $value Value or list of values to compare to
	 * @return \Aimeos\Controller\Frontend\Locale\Iface Locale controller for fluent interface
	 * @since 2019.04
	 */
	public function compare( $operator, $key, $value );

	/**
	 * Returns the locale for the given locale ID
	 *
	 * @param string $id Unique locale ID
	 * @return \Aimeos\MShop\Locale\Item\Iface Locale item including the referenced domains items
	 * @since 2019.04
	 */
	public function get( $id );

	/**
	 * Parses the given array and adds the conditions to the list of conditions
	 *
	 * @param array $conditions List of conditions, e.g. ['>' => ['locale.languageid' => 'de']]
	 * @return \Aimeos\Controller\Frontend\Locale\Iface Locale controller for fluent interface
	 * @since 2019.04
	 */
	public function parse( array $conditions );

	/**
	 * Returns the locales filtered by the previously assigned conditions
	 *
	 * @param integer &$total Parameter where the total number of found locales will be stored in
	 * @return \Aimeos\MShop\Locale\Item\Iface[] Ordered list of locale items
	 * @since 2019.04
	 */
	public function search( &$total = null );

	/**
	 * Sets the start value and the number of returned locale items for slicing the list of found locale items
	 *
	 * @param integer $start Start value of the first locale item in the list
	 * @param integer $limit Number of returned locale items
	 * @return \Aimeos\Controller\Frontend\Locale\Iface Locale controller for fluent interface
	 * @since 2019.04
	 */
	public function slice( $start, $limit );

	/**
	 * Sets the sorting of the result list
	 *
	 * @param string|null $key Sorting key of the result list like "position", null for no sorting
	 * @return \Aimeos\Controller\Frontend\Locale\Iface Locale controller for fluent interface
	 * @since 2019.04
	 */
	public function sort( $key = null );
}
