<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2017-2018
 * @package Controller
 * @subpackage Frontend
 */


namespace Aimeos\Controller\Frontend\Supplier;


/**
 * Interface for supplier frontend controllers
 *
 * @package Controller
 * @subpackage Frontend
 */
interface Iface
{
	/**
	 * Returns the default supplier filter
	 *
	 * @return \Aimeos\MW\Criteria\Iface Criteria object containing the conditions for searching
	 * @since 2018.07
	 */
	public function createFilter();


	/**
	 * Returns the supplier item for the given supplier ID
	 *
	 * @param string $id Unique supplier ID
	 * @param string[] $domains Domain names of items that are associated with the suppliers and that should be fetched too
	 * @return \Aimeos\MShop\Supplier\Item\Iface Supplier item including the referenced domains items
	 * @since 2018.07
	 */
	public function getItem( $id, array $domains = array( 'media', 'text' ) );


	/**
	 * Returns the supplier items for the given supplier IDs
	 *
	 * @param string $ids Unique supplier IDs
	 * @param string[] $domains Domain names of items that are associated with the suppliers and that should be fetched too
	 * @return \Aimeos\MShop\Supplier\Item\Iface[] Associative list of supplier item including the referenced domains items
	 * @since 2018.07
	 */
	public function getItems( array $ids, array $domains = array( 'media', 'text' ) );


	/**
	 * Returns the suppliers filtered by the given criteria object
	 *
	 * @param \Aimeos\MW\Criteria\Iface $filter Critera object which contains the filter conditions
	 * @param string[] $domains Domain names of items that are associated with the suppliers and that should be fetched too
	 * @param integer &$total Parameter where the total number of found suppliers will be stored in
	 * @return array Ordered list of supplier items implementing \Aimeos\MShop\Supplier\Item\Iface
	 * @since 2018.07
	 */
	public function searchItems( \Aimeos\MW\Criteria\Iface $filter, array $domains = array( 'media', 'text' ), &$total = null );
}
