<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2017-2021
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
	private $domains = [];
	private $manager;
	private $item;


	/**
	 * Initializes the controller
	 *
	 * @param \Aimeos\MShop\Context\Item\Iface $context Common MShop context object
	 */
	public function __construct( \Aimeos\MShop\Context\Item\Iface $context )
	{
		parent::__construct( $context );

		$this->manager = \Aimeos\MShop::create( $context, 'customer' );

		if( ( $userid = $context->getUserId() ) === null )
		{
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
			$groupIds = (array) $context->getConfig()->get( 'controller/frontend/customer/groupids', [] );
			$this->item = $this->manager->create()->setGroups( $groupIds );
		}
		else
		{
			$this->item = $this->manager->get( $userid, [], true );
		}
	}


	/**
	 * Clones objects in controller and resets values
	 */
	public function __clone()
	{
		$this->item = clone $this->item;
	}


	/**
	 * Creates a new customer item object pre-filled with the given values but not yet stored
	 *
	 * @param array $values Values added to the customer item (new or existing) like "customer.code"
	 * @return \Aimeos\Controller\Frontend\Customer\Iface Customer controller for fluent interface
	 * @since 2019.04
	 */
	public function add( array $values ) : Iface
	{
		foreach( $values as $key => $value )
		{
			if( is_scalar( $value ) ) {
				$values[$key] = strip_tags( (string) $value ); // prevent XSS
			}
		}

		$addrItem = $this->item->getPaymentAddress();

		if( $code = $values['customer.code'] ?? null ) {
			$this->item->setCode( $code );
		}

		if( $password = $values['customer.password'] ?? null ) {
			$this->item = $item->setPassword( $password );
		}

		if( $this->item->getLabel() === '' )
		{
			$label = $addrItem->getLastname();

			if( ( $firstName = $addrItem->getFirstname() ) !== '' ) {
				$label = $firstName . ' ' . $label;
			}

			if( ( $company = $addrItem->getCompany() ) !== '' ) {
				$label .= ' (' . $company . ')';
			}

			$this->item->setLabel( $label );
		}

		$this->item->fromArray( $values );
		return $this;
	}


	/**
	 * Adds the given address item to the customer object (not yet stored)
	 *
	 * @param \Aimeos\MShop\Common\Item\Address\Iface $item Address item to add
	 * @param int|null $idx Key in the list of address items or null to add the item at the end
	 * @return \Aimeos\Controller\Frontend\Customer\Iface Customer controller for fluent interface
	 * @since 2019.04
	 */
	public function addAddressItem( \Aimeos\MShop\Common\Item\Address\Iface $item, int $idx = null ) : Iface
	{
		$this->item = $this->item->addAddressItem( $item, $idx );
		return $this;
	}


	/**
	 * Adds the given list item to the customer object (not yet stored)
	 *
	 * @param string $domain Domain name the referenced item belongs to
	 * @param \Aimeos\MShop\Common\Item\Lists\Iface $item List item to add
	 * @param \Aimeos\MShop\Common\Item\Iface|null $refItem Referenced item to add or null if list item contains refid value
	 * @return \Aimeos\Controller\Frontend\Customer\Iface Customer controller for fluent interface
	 * @since 2019.04
	 */
	public function addListItem( string $domain, \Aimeos\MShop\Common\Item\Lists\Iface $item,
		\Aimeos\MShop\Common\Item\Iface $refItem = null ) : Iface
	{
		if( $domain === 'customer/group' ) {
			throw new Exception( sprintf( 'You are not allowed to manage groups' ) );
		}

		$this->item = $this->item->addListItem( $domain, $item, $refItem );
		return $this;
	}


	/**
	 * Adds the given property item to the customer object (not yet stored)
	 *
	 * @param \Aimeos\MShop\Common\Item\Property\Iface $item Property item to add
	 * @return \Aimeos\Controller\Frontend\Customer\Iface Customer controller for fluent interface
	 * @since 2019.04
	 */
	public function addPropertyItem( \Aimeos\MShop\Common\Item\Property\Iface $item ) : Iface
	{
		$this->item = $this->item->addPropertyItem( $item );
		return $this;
	}


	/**
	 * Creates a new address item object pre-filled with the given values
	 *
	 * @param array $values Associative list of key/value pairs for populating the item
	 * @return \Aimeos\MShop\Customer\Item\Address\Iface Address item
	 * @since 2019.04
	 */
	public function createAddressItem( array $values = [] ) : \Aimeos\MShop\Customer\Item\Address\Iface
	{
		return $this->manager->createAddressItem()->fromArray( $values );
	}


	/**
	 * Creates a new list item object pre-filled with the given values
	 *
	 * @param array $values Associative list of key/value pairs for populating the item
	 * @return \Aimeos\MShop\Common\Item\Lists\Iface List item
	 * @since 2019.04
	 */
	public function createListItem( array $values = [] ) : \Aimeos\MShop\Common\Item\Lists\Iface
	{
		return $this->manager->createListItem()->fromArray( $values );
	}


	/**
	 * Creates a new property item object pre-filled with the given values
	 *
	 * @param array $values Associative list of key/value pairs for populating the item
	 * @return \Aimeos\MShop\Common\Item\Property\Iface Property item
	 * @since 2019.04
	 */
	public function createPropertyItem( array $values = [] ) : \Aimeos\MShop\Common\Item\Property\Iface
	{
		return $this->manager->createPropertyItem()->fromArray( $values );
	}


	/**
	 * Deletes a customer item that belongs to the current authenticated user
	 *
	 * @return \Aimeos\Controller\Frontend\Customer\Iface Customer controller for fluent interface
	 * @since 2019.04
	 */
	public function delete() : Iface
	{
		if( $this->item && $this->item->getId() ) {
			\Aimeos\MShop::create( $this->getContext(), 'customer' )->delete( $this->item->getId() );
		}

		return $this;
	}


	/**
	 * Removes the given address item from the customer object (not yet stored)
	 *
	 * @param \Aimeos\MShop\Common\Item\Address\Iface $item Address item to remove
	 * @return \Aimeos\Controller\Frontend\Customer\Iface Customer controller for fluent interface
	 */
	public function deleteAddressItem( \Aimeos\MShop\Common\Item\Address\Iface $item ) : Iface
	{
		$this->item = $this->item->deleteAddressItem( $item );
		return $this;
	}


	/**
	 * Removes the given list item from the customer object (not yet stored)
	 *
	 * @param string $domain Domain name the referenced item belongs to
	 * @param \Aimeos\MShop\Common\Item\Lists\Iface $item List item to remove
	 * @param \Aimeos\MShop\Common\Item\Iface|null $refItem Referenced item to remove or null if only list item should be removed
	 * @return \Aimeos\Controller\Frontend\Customer\Iface Customer controller for fluent interface
	 */
	public function deleteListItem( string $domain, \Aimeos\MShop\Common\Item\Lists\Iface $listItem,
		\Aimeos\MShop\Common\Item\Iface $refItem = null ) : Iface
	{
		if( $domain === 'customer/group' ) {
			throw new Exception( sprintf( 'You are not allowed to manage groups' ) );
		}

		$this->item = $this->item->deleteListItem( $domain, $listItem, $refItem );
		return $this;
	}


	/**
	 * Removes the given property item from the customer object (not yet stored)
	 *
	 * @param \Aimeos\MShop\Common\Item\Property\Iface $item Property item to remove
	 * @return \Aimeos\Controller\Frontend\Customer\Iface Customer controller for fluent interface
	 */
	public function deletePropertyItem( \Aimeos\MShop\Common\Item\Property\Iface $item ) : Iface
	{
		$this->item = $this->item->deletePropertyItem( $item );
		return $this;
	}


	/**
	 * Returns the customer item for the given customer code (usually e-mail address)
	 *
	 * This method doesn't check if the customer item belongs to the logged in user!
	 *
	 * @param string $code Unique customer code
	 * @return \Aimeos\MShop\Customer\Item\Iface Customer item including the referenced domains items
	 * @since 2019.04
	 */
	public function find( string $code ) : \Aimeos\MShop\Customer\Item\Iface
	{
		return $this->manager->find( $code, $this->domains, 'customer', null, true );
	}


	/**
	 * Returns the customer item for the current authenticated user
	 *
	 * @return \Aimeos\MShop\Customer\Item\Iface Customer item including the referenced domains items
	 * @since 2019.04
	 */
	public function get() : \Aimeos\MShop\Customer\Item\Iface
	{
		return $this->item;
	}


	/**
	 * Adds or updates a modified customer item in the storage
	 *
	 * @return \Aimeos\Controller\Frontend\Customer\Iface Customer controller for fluent interface
	 * @since 2019.04
	 */
	public function store() : Iface
	{
		( $id = $this->item->getId() ) !== null ? $this->checkId( $id ) : $this->checkLimit();
		$context = $this->getContext();

		if( $id === null )
		{
			$msg = $this->item->toArray();

			// Show only generated passwords in account creation e-mails
			if( $this->item->getPassword() === '' )
			{
				$msg['customer.password'] = substr( sha1( microtime( true ) . getmypid() . rand() ), -8 );
				$this->item->setPassword( $msg['customer.password'] );
			}

			$context->getMessageQueue( 'mq-email', 'customer/email/account' )->add( json_encode( $msg ) );
		}

		$this->item = $this->manager->save( $this->item );
		return $this;
	}


	/**
	 * Sets the domains that will be used when working with the customer item
	 *
	 * @param array $domains Domain names of the referenced items that should be fetched too
	 * @return \Aimeos\Controller\Frontend\Customer\Iface Customer controller for fluent interface
	 * @since 2019.04
	 */
	public function uses( array $domains ) : Iface
	{
		$this->domains = $domains;

		if( ( $id = $this->getContext()->getUserId() ) !== null ) {
			$this->item = $this->manager->get( $id, $domains, true );
		}

		return $this;
	}


	/**
	 * Checks if the current user is allowed to create more customer accounts
	 *
	 * @throws \Aimeos\Controller\Frontend\Customer\Exception If access isn't allowed
	 */
	protected function checkLimit()
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
		$count = $config->get( 'controller/frontend/customer/limit-count', 3 );

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
		$seconds = $config->get( 'controller/frontend/customer/limit-seconds', 14400 );

		$search = $this->manager->filter()->slice( 0, 0 );
		$expr = [
			$search->compare( '==', 'customer.editor', $context->getEditor() ),
			$search->compare( '>=', 'customer.ctime', date( 'Y-m-d H:i:s', time() - $seconds ) ),
		];
		$search->setConditions( $search->and( $expr ) );

		$this->manager->search( $search, [], $total );

		if( $total >= $count ) {
			throw new \Aimeos\Controller\Frontend\Customer\Exception( sprintf( 'Temporary limit reached' ) );
		}
	}


	/**
	 * Checks if the current user is allowed to retrieve the customer data for the given ID
	 *
	 * @param string $id Unique customer ID
	 * @return string Unique customer ID
	 * @throws \Aimeos\Controller\Frontend\Customer\Exception If access isn't allowed
	 */
	protected function checkId( string $id ) : string
	{
		if( $id != $this->getContext()->getUserId() )
		{
			$msg = sprintf( 'Not allowed to access customer data for ID "%1$s"', $id );
			throw new \Aimeos\Controller\Frontend\Customer\Exception( $msg );
		}

		return $id;
	}


	/**
	 * Returns the manager used by the controller
	 *
	 * @return \Aimeos\MShop\Common\Manager\Iface Manager object
	 */
	protected function getManager() : \Aimeos\MShop\Common\Manager\Iface
	{
		return $this->manager;
	}
}
