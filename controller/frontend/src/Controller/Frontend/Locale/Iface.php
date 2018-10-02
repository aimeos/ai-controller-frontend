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
	 * Returns the default locale filter
	 *
	 * @return \Aimeos\MW\Criteria\Iface Criteria object containing the conditions for searching
	 * @since 2017.03
	 */
	public function createFilter();


	/**
	 * Returns the locale item for the given locale ID
	 *
	 * @param string $id Unique locale ID
	 * @param string[] $domains Domain names of items that are associated with the locales and that should be fetched too
	 * @return \Aimeos\MShop\Locale\Item\Iface Locale item including the referenced domains items
	 * @since 2017.03
	 */
	public function getItem( $id, array $domains = [] );


	/**
	 * Returns the locales filtered by the given criteria object
	 *
	 * @param \Aimeos\MW\Criteria\Iface $filter Critera object which contains the filter conditions
	 * @param string[] $domains Domain names of items that are associated with the locales and that should be fetched too
	 * @param integer &$total Parameter where the total number of found locales will be stored in
	 * @return array Ordered list of locale items implementing \Aimeos\MShop\Locale\Item\Iface
	 * @since 2017.03
	 */
	public function searchItems( \Aimeos\MW\Criteria\Iface $filter, array $domains = [], &$total = null );
}
