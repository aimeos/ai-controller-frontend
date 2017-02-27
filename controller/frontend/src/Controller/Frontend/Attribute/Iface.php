<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2017
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
	 * Returns the given search filter with the conditions attached for filtering by type code
	 *
	 * @param \Aimeos\MW\Criteria\Iface $filter Criteria object used for attribute search
	 * @param array $codes List of attribute type codes
	 * @return \Aimeos\MW\Criteria\Iface Criteria object containing the conditions for searching
	 * @since 2017.03
	 */
	public function addFilterTypes( \Aimeos\MW\Criteria\Iface $filter, array $codes );


	/**
	 * Returns the default attribute filter
	 *
	 * @return \Aimeos\MW\Criteria\Iface Criteria object containing the conditions for searching
	 * @since 2017.03
	 */
	public function createFilter();


	/**
	 * Returns the attribute item for the given attribute ID
	 *
	 * @param string $id Unique attribute ID
	 * @param string[] $domains Domain names of items that are associated with the attributes and that should be fetched too
	 * @return \Aimeos\MShop\Attribute\Item\Iface Attribute item including the referenced domains items
	 * @since 2017.03
	 */
	public function getItem( $id, array $domains = array( 'media', 'price', 'text' ) );


	/**
	 * Returns the attribute items for the given attribute IDs
	 *
	 * @param string $ids Unique attribute IDs
	 * @param string[] $domains Domain names of items that are associated with the attributes and that should be fetched too
	 * @return \Aimeos\MShop\Attribute\Item\Iface[] Associative list of attribute item including the referenced domains items
	 * @since 2017.03
	 */
	public function getItems( array $ids, array $domains = array( 'media', 'price', 'text' ) );


	/**
	 * Returns the attributes filtered by the given criteria object
	 *
	 * @param \Aimeos\MW\Criteria\Iface $filter Critera object which contains the filter conditions
	 * @param string[] $domains Domain names of items that are associated with the attributes and that should be fetched too
	 * @param integer &$total Parameter where the total number of found attributes will be stored in
	 * @return array Ordered list of attribute items implementing \Aimeos\MShop\Attribute\Item\Iface
	 * @since 2017.03
	 */
	public function searchItems( \Aimeos\MW\Criteria\Iface $filter, array $domains = array( 'media', 'price', 'text' ), &$total = null );
}
