<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2017-2018
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
		$list = [];
		$context = $this->getContext();
		$config = $context->getConfig();

		// Show only generated passwords in account creation e-mails
		$pass = ( isset( $values['customer.password'] ) ? false : true );

		foreach( $values as $key => $val ) {
			$list[str_replace( 'order.base.address', 'customer', $key )] = $val;
		}

		$manager = \Aimeos\MShop\Factory::createManager( $context, 'customer' );
		$list = $this->addItemDefaults( $list );

		try
		{
			$item = $manager->findItem( $list['customer.code'], [], true );
		}
		catch( \Aimeos\MShop\Exception $e )
		{
			$this->checkLimit( $list );

			$item = $manager->createItem();
			$item->fromArray( $list );
			$item->setId( null );

			/** controller/frontend/customer/groupids
			 * List of groups new customers should be assigned to
			 *
			 * Newly created customers will be assigned automatically to the groups
			 * given by their IDs. This is especially useful if those groups limit
			 * functionality for those users.
			 *
			 * @param array List of group IDs
			 * @since 2017.07
			 * @category User
			 * @category Developer
			 */
			$item->setGroups( (array) $config->get( 'controller/frontend/customer/groupids', [] ) );

			$item = $manager->saveItem( $item );

			$msg = $item->toArray();
			$msg['customer.password'] = ( $pass ? $list['customer.password'] : null );
			$context->getMessageQueue( 'mq-email', 'customer/email/account' )->add( json_encode( $msg ) );
		}

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
		$item->setId( null );
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
		$item = $manager->getItem( $id, ['customer/group'], true );

		unset( $values['customer.id'] );
		$item->fromArray( $values );

		return $manager->saveItem( $item );
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
		return \Aimeos\MShop\Factory::createManager( $this->getContext(), 'customer' )->findItem( $code, $domains, true );
	}


	/**
	 * Stores a modified customer item
	 *
	 * @param \Aimeos\MShop\Customer\Item\Iface $item Customer item
	 * @return \Aimeos\MShop\Customer\Item\Address\Iface Customer address item including the generated ID
	 */
	public function saveItem( \Aimeos\MShop\Customer\Item\Iface $item )
	{
		return \Aimeos\MShop\Factory::createManager( $this->getContext(), 'customer' )->saveItem( $item );
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
		$item->setId( null );
		$item->setParentId( $context->getUserId() );

		return $manager->saveItem( $item );
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
		$item->setId( null );

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

		unset( $values['customer.address.id'] );
		$item->fromArray( $values );

		return $manager->saveItem( $item );
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
	 * @param \Aimeos\MShop\Customer\Item\Address\Iface $item Customer address item
	 * @return \Aimeos\MShop\Customer\Item\Address\Iface Customer address item including the generated ID
	 */
	public function saveAddressItem( \Aimeos\MShop\Customer\Item\Address\Iface $item )
	{
		return \Aimeos\MShop\Factory::createManager( $this->getContext(), 'customer/address' )->saveItem( $item );
	}


	/**
	 * Creates and returns a new list item object
	 *
	 * @param array $values Values added to the newly created customer item like "customer.lists.refid"
	 * @return \Aimeos\MShop\Common\Item\Lists\Iface Customer lists item
	 * @since 2017.06
	 */
	public function addListItem( array $values )
	{
		$context = $this->getContext();
		$manager = \Aimeos\MShop\Factory::createManager( $context, 'customer/lists' );

		if( !isset( $values['customer.lists.typeid'] ) )
		{
			if( !isset( $values['customer.lists.type'] ) ) {
				throw new \Aimeos\Controller\Frontend\Customer\Exception( sprintf( 'No customer lists type code' ) );
			}

			if( !isset( $values['customer.lists.domain'] ) ) {
				throw new \Aimeos\Controller\Frontend\Customer\Exception( sprintf( 'No customer lists domain' ) );
			}

			$typeManager = \Aimeos\MShop\Factory::createManager( $context, 'customer/lists/type' );
			$typeItem = $typeManager->findItem( $values['customer.lists.type'], [], $values['customer.lists.domain'] );
			$values['customer.lists.typeid'] = $typeItem->getId();
		}

		$item = $manager->createItem();
		$item->fromArray( $values );
		$item->setId( null );
		$item->setParentId( $context->getUserId() );

		return $manager->saveItem( $item );
	}


	/**
	 * Returns a new customer lists filter criteria object
	 *
	 * @return \Aimeos\MW\Criteria\Iface New filter object
	 * @since 2017.06
	 */
	public function createListsFilter()
	{
		$context = $this->getContext();
		$manager = \Aimeos\MShop\Factory::createManager( $context, 'customer/lists' );

		$filter = $manager->createSearch();
		$filter->setConditions( $filter->compare( '==', 'customer.lists.parentid', $context->getUserId() ) );

		return $filter;
	}


	/**
	 * Deletes a customer item that belongs to the current authenticated user
	 *
	 * @param string $id Unique customer address ID
	 * @since 2017.06
	 */
	public function deleteListItem( $id )
	{
		$manager = \Aimeos\MShop\Factory::createManager( $this->getContext(), 'customer/lists' );

		$this->checkUser( $manager->getItem( $id )->getParentId() );

		$manager->deleteItem( $id );
	}


	/**
	 * Saves a modified customer lists item object
	 *
	 * @param string $id Unique customer lists ID
	 * @param array $values Values added to the customer lists item like "customer.lists.refid"
	 * @return \Aimeos\MShop\Common\Item\Lists\Iface Customer lists item
	 * @since 2017.06
	 */
	public function editListItem( $id, array $values )
	{
		$context = $this->getContext();
		$manager = \Aimeos\MShop\Factory::createManager( $context, 'customer/lists' );

		$item = $manager->getItem( $id, [], true );
		$this->checkUser( $item->getParentId() );

		if( !isset( $values['customer.lists.typeid'] ) )
		{
			if( !isset( $values['customer.lists.type'] ) ) {
				throw new \Aimeos\Controller\Frontend\Customer\Exception( sprintf( 'No customer lists type code' ) );
			}

			if( !isset( $values['customer.lists.domain'] ) ) {
				throw new \Aimeos\Controller\Frontend\Customer\Exception( sprintf( 'No customer lists domain' ) );
			}

			$typeManager = \Aimeos\MShop\Factory::createManager( $context, 'customer/lists/type' );
			$typeItem = $typeManager->findItem( $values['customer.lists.type'], [], $values['customer.lists.domain'], true );
			$values['customer.lists.typeid'] = $typeItem->getId();
		}

		unset( $values['customer.lists.id'] );
		$item->fromArray( $values );

		return $manager->saveItem( $item );
	}


	/**
	 * Returns the customer item for the given customer ID
	 *
	 * @param string $id Unique customer address ID
	 * @return \Aimeos\MShop\Customer\Item\Address\Iface Customer address item
	 * @since 2017.06
	 */
	public function getListItem( $id )
	{
		$manager = \Aimeos\MShop\Factory::createManager( $this->getContext(), 'customer/lists' );
		$item = $manager->getItem( $id );

		$this->checkUser( $item->getParentId() );

		return $item;
	}


	/**
	 * Returns the customer lists items filtered by the given criteria
	 *
	 * @param \Aimeos\MW\Criteria\Iface $filter Criteria object which contains the filter conditions
	 * @param integer &$total Parameter where the total number of found attributes will be stored in
	 * @return \Aimeos\MShop\Common\Item\Lists\Iface[] Customer list items
	 * @since 2017.06
	 */
	public function searchListItems( \Aimeos\MW\Criteria\Iface $filter, &$total = null )
	{
		$manager = \Aimeos\MShop\Factory::createManager( $this->getContext(), 'customer/lists' );

		return $manager->searchItems( $filter, [], $total );
	}


	/**
	 * Adds the default values for customer items if not yet available
	 *
	 * @param string[] $values Associative list of customer keys (e.g. "customer.label") and their values
	 * @return string[] Associative list of customer key/value pairs with default values set
	 */
	protected function addItemDefaults( array $values )
	{
		if( !isset( $values['customer.label'] ) || $values['customer.label'] == '' )
		{
			$label = '';

			if( isset( $values['customer.lastname'] ) ) {
				$label = $values['customer.lastname'];
			}

			if( isset( $values['customer.firstname'] ) && $values['customer.firstname'] != '' ) {
				$label = $values['customer.firstname'] . ' ' . $label;
			}

			if( isset( $values['customer.company'] ) && $values['customer.company'] != '' ) {
				$label .= ' (' . $values['customer.company'] . ')';
			}

			$values['customer.label'] = $label;
		}

		if( !isset( $values['customer.code'] ) && isset( $values['customer.email'] ) ) {
			$values['customer.code'] = $values['customer.email'];
		}

		if( !isset( $values['customer.status'] ) ) {
			$values['customer.status'] = 1;
		}

		if( !isset( $values['customer.password'] ) ) {
			$values['customer.password'] = substr( md5( microtime( true ) . getmypid() . rand() ), -8 );
		}

		return $values;
	}


	/**
	 * Checks if the current user is allowed to create more customer accounts
	 *
	 * @param string[] $values Associative list of customer keys (e.g. "customer.label") and their values
	 * @throws \Aimeos\Controller\Frontend\Customer\Exception If access isn't allowed
	 */
	protected function checkLimit( array $values )
	{
		$total = 0;
		$context = $this->getContext();
		$config = $context->getConfig();

		/** controller/frontend/customer/limit-count
		 * Maximum number of customers within the time frame
		 *
		 * Creating new customers is limited to avoid abuse and mitigate denial of
		 * service attacks. The number of customer accountss created within the
		 * time frame configured by "controller/frontend/customer/limit-seconds"
		 * are counted before a new customer account (identified by the IP address)
		 * is created. If the number of accounts is higher than the configured value,
		 * an error message will be shown to the user instead of creating a new account.
		 *
		 * @param integer Number of customer accounts allowed within the time frame
		 * @since 2017.07
		 * @category Developer
		 * @see controller/frontend/customer/limit-seconds
		 */
		$count = $config->get( 'controller/frontend/customer/limit-count', 5 );

		/** controller/frontend/customer/limit-seconds
		 * Customer account limitation time frame in seconds
		 *
		 * Creating new customer accounts is limited to avoid abuse and mitigate
		 * denial of service attacks. Within the configured time frame, only a
		 * limited number of customer accounts can be created. All accounts from
		 * the same source (identified by the IP address) within the last X
		 * seconds are counted. If the total value is higher then the number
		 * configured in "controller/frontend/customer/limit-count", an error
		 * message will be shown to the user instead of creating a new account.
		 *
		 * @param integer Number of seconds to check customer accounts within
		 * @since 2017.07
		 * @category Developer
		 * @see controller/frontend/customer/limit-count
		 */
		$seconds = $config->get( 'controller/frontend/customer/limit-seconds', 300 );

		$manager = \Aimeos\MShop\Factory::createManager( $context, 'customer' );

		$search = $manager->createSearch();
		$expr = [
			$search->compare( '==', 'customer.editor', $context->getEditor() ),
			$search->compare( '>=', 'customer.ctime', date( 'Y-m-d H:i:s', time() - $seconds ) ),
		];
		$search->setConditions( $search->combine( '&&', $expr ) );
		$search->setSlice( 0, 0 );

		$manager->searchItems( $search, [], $total );

		if( $total > $count ) {
			throw new \Aimeos\Controller\Frontend\Basket\Exception( sprintf( 'Temporary limit reached' ) );
		}
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
