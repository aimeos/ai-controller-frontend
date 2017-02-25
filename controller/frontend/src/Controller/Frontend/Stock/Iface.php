<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2017
 * @package Controller
 * @subpackage Frontend
 */


namespace Aimeos\Controller\Frontend\Stock;


/**
 * Interface for stock frontend controllers
 *
 * @package Controller
 * @subpackage Frontend
 */
interface Iface
{
	/**
	 * Returns the given search filter with the conditions attached for filtering by product code
	 *
	 * @param \Aimeos\MW\Criteria\Iface $filter Criteria object used for stock search
	 * @param array $codes List of product codes
	 * @return \Aimeos\MW\Criteria\Iface Criteria object containing the conditions for searching
	 * @since 2017.03
	 */
	public function addFilterCodes( \Aimeos\MW\Criteria\Iface $filter, array $codes );


	/**
	 * Returns the given search filter with the conditions attached for filtering by type code
	 *
	 * @param \Aimeos\MW\Criteria\Iface $filter Criteria object used for stock search
	 * @param array $codes List of stock type codes
	 * @return \Aimeos\MW\Criteria\Iface Criteria object containing the conditions for searching
	 * @since 2017.03
	 */
	public function addFilterTypes( \Aimeos\MW\Criteria\Iface $filter, array $codes );


	/**
	 * Returns the default stock filter
	 *
	 * @return \Aimeos\MW\Criteria\Iface Criteria object containing the conditions for searching
	 * @since 2017.03
	 */
	public function createFilter();


	/**
	 * Returns the stock item for the given stock ID
	 *
	 * @param string $id Unique stock ID
	 * @return \Aimeos\MShop\Stock\Item\Iface Stock item including the referenced domains items
	 * @since 2017.03
	 */
	public function getItem( $id );


	/**
	 * Returns the stocks filtered by the given criteria object
	 *
	 * @param \Aimeos\MW\Criteria\Iface $filter Critera object which contains the filter conditions
	 * @param integer &$total Parameter where the total number of found stocks will be stored in
	 * @return array Ordered list of stock items implementing \Aimeos\MShop\Stock\Item\Iface
	 * @since 2017.03
	 */
	public function searchItems( \Aimeos\MW\Criteria\Iface $filter, &$total = null );
}
