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
	 * Adds the values to the customer object (not yet stored)
	 *
	 * @param array $values Values added to the customer item (new or existing) like "customer.code"
	 * @return \Aimeos\Controller\Frontend\Customer\Iface Customer controller for fluent interface
	 * @since 2019.04
	 */
	public function add( array $values );

	/**
	 * Adds the given address item to the customer object (not yet stored)
	 *
	 * @param \Aimeos\MShop\Common\Item\Address\Iface $item Address item to add
	 * @param integer|null $idx Key in the list of address items or null to add the item at the end
	 * @return \Aimeos\Controller\Frontend\Customer\Iface Customer controller for fluent interface
	 * @since 2019.04
	 */
	public function addAddressItem( \Aimeos\MShop\Common\Item\Address\Iface $item, $idx = null );

	/**
	 * Adds the given list item to the customer object (not yet stored)
	 *
	 * @param string $domain Domain name the referenced item belongs to
	 * @param \Aimeos\MShop\Common\Item\Lists\Iface $item List item to add
	 * @param \Aimeos\MShop\Common\Item\Iface|null $refItem Referenced item to add or null if list item contains refid value
	 * @return \Aimeos\Controller\Frontend\Customer\Iface Customer controller for fluent interface
	 * @since 2019.04
	 */
	public function addListItem( $domain, \Aimeos\MShop\Common\Item\Lists\Iface $item, \Aimeos\MShop\Common\Item\Iface $refItem = null );

	/**
	 * Adds the given property item to the customer object (not yet stored)
	 *
	 * @param \Aimeos\MShop\Common\Item\Property\Iface $item Property item to add
	 * @return \Aimeos\Controller\Frontend\Customer\Iface Customer controller for fluent interface
	 * @since 2019.04
	 */
	public function addPropertyItem( \Aimeos\MShop\Common\Item\Property\Iface $item );

	/**
	 * Creates a new address item object pre-filled with the given values
	 *
	 * @return \Aimeos\MShop\Customer\Item\Address\Iface Address item
	 * @since 2019.04
	 */
	public function createAddressItem( array $values = [] );

	/**
	 * Creates a new list item object pre-filled with the given values
	 *
	 * @return \Aimeos\MShop\Common\Item\Lists\Iface List item
	 * @since 2019.04
	 */
	public function createListItem( array $values = [] );

	/**
	 * Creates a new property item object pre-filled with the given values
	 *
	 * @return \Aimeos\MShop\Common\Item\Property\Iface Property item
	 * @since 2019.04
	 */
	public function createPropertyItem( array $values = [] );

	/**
	 * Deletes a customer item that belongs to the current authenticated user
	 *
	 * @return \Aimeos\Controller\Frontend\Customer\Iface Customer controller for fluent interface
	 * @since 2019.04
	 */
	public function delete();

	/**
	 * Removes the given address item from the customer object (not yet stored)
	 *
	 * @param \Aimeos\MShop\Common\Item\Address\Iface $item Address item to remove
	 * @return \Aimeos\Controller\Frontend\Customer\Iface Customer controller for fluent interface
	 */
	public function deleteAddressItem( \Aimeos\MShop\Common\Item\Address\Iface $item );

	/**
	 * Removes the given list item from the customer object (not yet stored)
	 *
	 * @param string $domain Domain name the referenced item belongs to
	 * @param \Aimeos\MShop\Common\Item\Lists\Iface $item List item to remove
	 * @param \Aimeos\MShop\Common\Item\Iface|null $refItem Referenced item to remove or null if only list item should be removed
	 * @return \Aimeos\Controller\Frontend\Customer\Iface Customer controller for fluent interface
	 */
	public function deleteListItem( $domain, \Aimeos\MShop\Common\Item\Lists\Iface $listItem, \Aimeos\MShop\Common\Item\Iface $refItem = null );

	/**
	 * Removes the given property item from the customer object (not yet stored)
	 *
	 * @param \Aimeos\MShop\Common\Item\Property\Iface $item Property item to remove
	 * @return \Aimeos\Controller\Frontend\Customer\Iface Customer controller for fluent interface
	 */
	public function deletePropertyItem( \Aimeos\MShop\Common\Item\Property\Iface $item );

	/**
	 * Returns the customer item for the given code
	 *
	 * This method doesn't check if the customer item belongs to the logged in user!
	 *
	 * @param string|null $code Unique customer code
	 * @return \Aimeos\MShop\Customer\Item\Iface Customer item
	 * @since 2019.04
	 */
	public function find( $code );

	/**
	 * Returns the customer item for the current authenticated user
	 *
	 * @return \Aimeos\MShop\Customer\Item\Iface Customer item
	 * @since 2019.04
	 */
	public function get();

	/**
	 * Adds or updates the modified customer item in the storage
	 *
	 * @return \Aimeos\Controller\Frontend\Customer\Iface Customer controller for fluent interface
	 * @since 2019.04
	 */
	public function store();

	/**
	 * Sets the domains that will be used when working with the customer item
	 *
	 * @param array $domains Domain names of the referenced items that should be fetched too
	 * @return \Aimeos\Controller\Frontend\Customer\Iface Customer controller for fluent interface
	 * @since 2019.04
	 */
	public function uses( array $domains );
}
