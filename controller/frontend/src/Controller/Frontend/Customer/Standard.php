<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2017
 * @package Controller
 * @subpackage Frontend
 */


namespace Aimeos\Controller\Frontend\Customer;


/**
 * Default implementation of the customer frontend controller
 *
 * @package Controller
 * @subpackage Frontend
 */
class Standard
	extends \Aimeos\Controller\Frontend\Base
	implements Iface, \Aimeos\Controller\Frontend\Common\Iface
{
	/**
	 * Adds and returns a new customer item object
	 *
	 * @param array $values Values added to the newly created customer item like "customer.birthday"
	 * @return \Aimeos\MShop\Customer\Item\Iface Customer item
	 * @since 2017.04
	 */
	public function addItem( array $values )
	{
		$manager = \Aimeos\MShop\Factory::createManager( $this->getContext(), 'customer' );

		$item = $manager->createItem();
		$item->fromArray( $values );
		$manager->saveItem( $item );

		return $item;
	}


	/**
	 * Creates a new customer item object pre-filled with the given values but not yet stored
	 *
	 * @return \Aimeos\MShop\Customer\Item\Iface Customer item
	 */
	public function createItem( array $values = [] )
	{
		$manager = \Aimeos\MShop\Factory::createManager( $this->getContext(), 'customer' );

		$item = $manager->createItem();
		$item->fromArray( $values );
		$item->setStatus( 1 );

		return $item;
	}


	/**
	 * Deletes a customer item that belongs to the current authenticated user
	 *
	 * @param string $id Unique customer ID
	 * @since 2017.04
	 */
	public function deleteItem( $id )
	{
		$this->checkUser( $id );

		$manager = \Aimeos\MShop\Factory::createManager( $this->getContext(), 'customer' );
		$manager->deleteItem( $id );
	}


	/**
	 * Updates the customer item identified by its ID
	 *
	 * @param string $id Unique customer ID
	 * @param array $values Values added to the customer item like "customer.birthday" or "customer.city"
	 * @return \Aimeos\MShop\Customer\Item\Iface Customer item
	 * @since 2017.04
	 */
	public function editItem( $id, array $values )
	{
		$this->checkUser( $id );

		$manager = \Aimeos\MShop\Factory::createManager( $this->getContext(), 'customer' );

		$item = $manager->getItem( $id, [], true );
		$item->fromArray( $values );
		$manager->saveItem( $item );

		return $item;
	}


	/**
	 * Returns the customer item for the given customer ID
	 *
	 * @param string|null $id Unique customer ID or null for current customer item
	 * @param string[] $domains Domain names of items that are associated with the customers and that should be fetched too
	 * @return \Aimeos\MShop\Customer\Item\Iface Customer item including the referenced domains items
	 * @since 2017.04
	 */
	public function getItem( $id = null, array $domains = [] )
	{
		$manager = \Aimeos\MShop\Factory::createManager( $this->getContext(), 'customer' );

		if( $id == null ) {
			return $manager->getItem( $this->getContext()->getUserId(), $domains, true );
		}

		$this->checkUser( $id );

		return $manager->getItem( $id, $domains, true );
	}


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
	public function findItem( $code, array $domains = [] )
	{
		return \Aimeos\MShop\Factory::createManager( $this->getContext(), 'customer' )->findItem( $code, $domains );
	}


	/**
	 * Stores a modified customer item
	 *
	 * @param \Aimeos\MShop\Customer\Item\Iface Customer item
	 */
	public function saveItem( \Aimeos\MShop\Customer\Item\Iface $item )
	{
		\Aimeos\MShop\Factory::createManager( $this->getContext(), 'customer' )->saveItem( $item );
	}


	/**
	 * Creates and returns a new item object
	 *
	 * @param array $values Values added to the newly created customer item like "customer.birthday"
	 * @return \Aimeos\MShop\Customer\Item\Address\Iface Customer address item
	 * @since 2017.04
	 */
	public function addAddressItem( array $values )
	{
		$context = $this->getContext();
		$manager = \Aimeos\MShop\Factory::createManager( $context, 'customer/address' );

		$item = $manager->createItem();
		$item->fromArray( $values );
		$item->setParentId( $context->getUserId() );
		$manager->saveItem( $item );

		return $item;
	}


	/**
	 * Creates a new customer address item object pre-filled with the given values but not yet stored
	 *
	 * @return \Aimeos\MShop\Customer\Item\Address\Iface Customer address item
	 */
	public function createAddressItem( array $values = [] )
	{
		$context = $this->getContext();
		$manager = \Aimeos\MShop\Factory::createManager( $context, 'customer/address' );

		$item = $manager->createItem();
		$item->fromArray( $values );
		$item->setParentId( $context->getUserId() );

		return $item;
	}


	/**
	 * Deletes a customer item that belongs to the current authenticated user
	 *
	 * @param string $id Unique customer address ID
	 * @since 2017.04
	 */
	public function deleteAddressItem( $id )
	{
		$manager = \Aimeos\MShop\Factory::createManager( $this->getContext(), 'customer/address' );

		$this->checkUser( $manager->getItem( $id, [], true )->getParentId() );

		$manager->deleteItem( $id );
	}


	/**
	 * Saves a modified customer item object
	 *
	 * @param string $id Unique customer address ID
	 * @param array $values Values added to the customer item like "customer.address.city"
	 * @return \Aimeos\MShop\Customer\Item\Address\Iface Customer address item
	 * @since 2017.04
	 */
	public function editAddressItem( $id, array $values )
	{
		$manager = \Aimeos\MShop\Factory::createManager( $this->getContext(), 'customer/address' );

		$item = $manager->getItem( $id, [], true );
		$this->checkUser( $item->getParentId() );

		$item->fromArray( $values );
		$manager->saveItem( $item );

		return $item;
	}


	/**
	 * Returns the customer item for the given customer ID
	 *
	 * @param string $id Unique customer address ID
	 * @return \Aimeos\MShop\Customer\Item\Address\Iface Customer address item
	 * @since 2017.04
	 */
	public function getAddressItem( $id )
	{
		$manager = \Aimeos\MShop\Factory::createManager( $this->getContext(), 'customer/address' );
		$item = $manager->getItem( $id );

		$this->checkUser( $item->getParentId() );

		return $item;
	}


	/**
	 * Stores a modified customer address item
	 *
	 * @param \Aimeos\MShop\Customer\Item\Address\Iface Customer address item
	 */
	public function saveAddressItem( \Aimeos\MShop\Customer\Item\Address\Iface $item )
	{
		\Aimeos\MShop\Factory::createManager( $this->getContext(), 'customer/address' )->saveItem( $item );
	}


	/**
	 * Checks if the current user is allowed to retrieve the customer data for the given ID
	 *
	 * @param string $id Unique customer ID
	 * @throws \Aimeos\Controller\Frontend\Customer\Exception If access isn't allowed
	 */
	protected function checkUser( $id )
	{
		if( $id != $this->getContext()->getUserId() )
		{
			$msg = sprintf( 'Not allowed to access customer data for ID "%1$s"', $id );
			throw new \Aimeos\Controller\Frontend\Customer\Exception( $msg );
		}
	}
}
