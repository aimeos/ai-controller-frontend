<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2017-2018
 * @package Controller
 * @subpackage Frontend
 */


namespace Aimeos\Controller\Frontend\Customer;


/**
 * Interface for customer frontend controllers
 *
 * @package Controller
 * @subpackage Frontend
 */
interface Iface
{
	/**
	 * Adds and returns a new customer item object
	 *
	 * @param array $values Values added to the newly created customer item like "customer.birthday"
	 * @return \Aimeos\MShop\Customer\Item\Iface Customer item
	 * @since 2017.04
	 */
	public function addItem( array $values );


	/**
	 * Creates a new customer item object pre-filled with the given values but not yet stored
	 *
	 * @return \Aimeos\MShop\Customer\Item\Iface Customer item
	 * @since 2017.04
	 */
	public function createItem( array $values = [] );


	/**
	 * Deletes a customer item that belongs to the current authenticated user
	 *
	 * @param string $id Unique customer ID
	 * @since 2017.04
	 */
	public function deleteItem( $id );


	/**
	 * Updates the customer item identified by its ID
	 *
	 * @param string $id Unique customer ID
	 * @param array $values Values added to the customer item like "customer.birthday" or "customer.city"
	 * @return \Aimeos\MShop\Customer\Item\Iface Customer item
	 * @since 2017.04
	 */
	public function editItem( $id, array $values );


	/**
	 * Returns the customer item for the given customer ID
	 *
	 * @param string|null $id Unique customer ID or null for current customer item
	 * @param string[] $domains Domain names of items that are associated with the customers and that should be fetched too
	 * @return \Aimeos\MShop\Customer\Item\Iface Customer item including the referenced domains items
	 * @since 2017.04
	 */
	public function getItem( $id = null, array $domains = [] );


	/**
	 * Returns the customer item for the given customer code (usually e-mail address)
	 *
	 * This method doesn't check if the customer item belongs to the logged in user!
	 *
	 * @param string $code Unique customer code
	 * @param string[] $domains Domain names of items that are associated with the customers and that should be fetched too
	 * @return \Aimeos\MShop\Customer\Item\Iface Customer item including the referenced domains items
	 * @since 2017.04
	 */
	public function findItem( $code, array $domains = [] );


	/**
	 * Stores a modified customer item
	 *
	 * @param \Aimeos\MShop\Customer\Item\Iface $item Customer item
	 * @return \Aimeos\MShop\Customer\Item\Address\Iface Customer address item including the generated ID
	 * @since 2017.04
	 */
	public function saveItem( \Aimeos\MShop\Customer\Item\Iface $item );


	/**
	 * Creates and returns a new address item object
	 *
	 * @param array $values Values added to the newly created customer item like "customer.birthday"
	 * @return \Aimeos\MShop\Customer\Item\Iface Customer address item
	 * @since 2017.04
	 */
	public function addAddressItem( array $values );


	/**
	 * Creates a new customer address item object pre-filled with the given values but not yet stored
	 *
	 * @return \Aimeos\MShop\Customer\Item\Address\Iface Customer address item
	 * @since 2017.04
	 */
	public function createAddressItem( array $values = [] );


	/**
	 * Deletes a address item that belongs to the current authenticated user
	 *
	 * @param string $id Unique customer address ID
	 * @since 2017.04
	 */
	public function deleteAddressItem( $id );


	/**
	 * Saves a modified address item object
	 *
	 * @param string $id Unique customer address ID
	 * @param array $values Values added to the address item like "customer.address.city"
	 * @return \Aimeos\MShop\Customer\Item\Iface Customer address item
	 * @since 2017.04
	 */
	public function editAddressItem( $id, array $values );


	/**
	 * Returns the address item for the given ID
	 *
	 * @param string $id Unique customer address ID
	 * @return \Aimeos\MShop\Customer\Item\Address\Iface Customer address item
	 * @since 2017.04
	 */
	public function getAddressItem( $id );


	/**
	 * Stores a modified customer address item
	 *
	 * @param \Aimeos\MShop\Customer\Item\Address\Iface $item Customer address item
	 * @return \Aimeos\MShop\Customer\Item\Address\Iface Customer address item including the generated ID
	 * @since 2017.04
	 */
	public function saveAddressItem( \Aimeos\MShop\Customer\Item\Address\Iface $item );

	/**
	 * Creates and returns a new list item object
	 *
	 * @param array $values Values added to the newly created customer item like "customer.lists.refid"
	 * @return \Aimeos\MShop\Common\Item\Lists\Iface Customer lists item
	 * @since 2017.06
	 */
	public function addListItem( array $values );

	/**
	 * Returns a new customer lists filter criteria object
	 *
	 * @return \Aimeos\MW\Criteria\Iface New filter object
	 * @since 2017.06
	 */
	public function createListsFilter();

	/**
	 * Deletes a customer item that belongs to the current authenticated user
	 *
	 * @param string $id Unique customer address ID
	 * @since 2017.06
	 */
	public function deleteListItem( $id );

	/**
	 * Saves a modified customer lists item object
	 *
	 * @param string $id Unique customer lists ID
	 * @param array $values Values added to the customer lists item like "customer.lists.refid"
	 * @return \Aimeos\MShop\Common\Item\Lists\Iface Customer lists item
	 * @since 2017.06
	 */
	public function editListItem( $id, array $values );

	/**
	 * Returns the customer item for the given customer ID
	 *
	 * @param string $id Unique customer address ID
	 * @return \Aimeos\MShop\Customer\Item\Address\Iface Customer address item
	 * @since 2017.06
	 */
	public function getListItem( $id );

	/**
	 * Returns the customer lists items filtered by the given criteria
	 *
	 * @param \Aimeos\MW\Criteria\Iface $filter Criteria object which contains the filter conditions
	 * @param integer &$total Parameter where the total number of found attributes will be stored in
	 * @return \Aimeos\MShop\Common\Item\Lists\Iface[] Customer list items
	 * @since 2017.06
	 */
	public function searchListItems( \Aimeos\MW\Criteria\Iface $filter, &$total = null );
}
