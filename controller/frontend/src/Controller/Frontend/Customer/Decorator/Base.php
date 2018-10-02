<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2017-2018
 * @package Controller
 * @subpackage Frontend
 */


namespace Aimeos\Controller\Frontend\Customer\Decorator;


/**
 * Base for customer frontend controller decorators
 *
 * @package Controller
 * @subpackage Frontend
 */
abstract class Base
	extends \Aimeos\Controller\Frontend\Base
	implements \Aimeos\Controller\Frontend\Common\Decorator\Iface, \Aimeos\Controller\Frontend\Customer\Iface
{
	private $controller;


	/**
	 * Initializes the controller decorator.
	 *
	 * @param \Aimeos\Controller\Frontend\Iface $controller Controller object
	 * @param \Aimeos\MShop\Context\Item\Iface $context Context object with required objects
	 */
	public function __construct( \Aimeos\Controller\Frontend\Iface $controller, \Aimeos\MShop\Context\Item\Iface $context )
	{
		\Aimeos\MW\Common\Base::checkClass( '\\Aimeos\\Controller\\Frontend\\Customer\\Iface', $controller );

		$this->controller = $controller;

		parent::__construct( $context );
	}


	/**
	 * Passes unknown methods to wrapped objects.
	 *
	 * @param string $name Name of the method
	 * @param array $param List of method parameter
	 * @return mixed Returns the value of the called method
	 * @throws \Aimeos\Controller\Frontend\Exception If method call failed
	 */
	public function __call( $name, array $param )
	{
		return @call_user_func_array( array( $this->controller, $name ), $param );
	}


	/**
	 * Adds and returns a new customer item object
	 *
	 * @param array $values Values added to the newly created customer item like "customer.birthday"
	 * @return \Aimeos\MShop\Customer\Item\Iface Customer item
	 * @since 2017.04
	 */
	public function addItem( array $values )
	{
		return $this->controller->addItem( $values );
	}


	/**
	 * Creates a new customer item object pre-filled with the given values but not yet stored
	 *
	 * @return \Aimeos\MShop\Customer\Item\Iface Customer item
	 * @since 2017.04
	 */
	public function createItem( array $values = [] )
	{
		return $this->controller->createItem( $values );
	}


	/**
	 * Deletes a customer item that belongs to the current authenticated user
	 *
	 * @param string $id Unique customer ID
	 * @since 2017.04
	 */
	public function deleteItem( $id )
	{
		return $this->controller->deleteItem( $id );
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
		return $this->controller->editItem( $id, $values );
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
		return $this->controller->getItem( $id, $domains );
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
		return $this->controller->findItem( $code, $domains );
	}


	/**
	 * Stores a modified customer item
	 *
	 * @param \Aimeos\MShop\Customer\Item\Iface Customer item
	 * @since 2017.04
	 */
	public function saveItem( \Aimeos\MShop\Customer\Item\Iface $item )
	{
		return $this->controller->saveItem( $item );
	}


	/**
	 * Creates and returns a new address item object
	 *
	 * @param array $values Values added to the newly created customer item like "customer.birthday"
	 * @return \Aimeos\MShop\Customer\Item\Iface Customer address item
	 * @since 2017.04
	 */
	public function addAddressItem( array $values )
	{
		return $this->controller->addAddressItem( $values );
	}


	/**
	 * Creates a new customer address item object pre-filled with the given values but not yet stored
	 *
	 * @return \Aimeos\MShop\Customer\Item\Address\Iface Customer address item
	 * @since 2017.04
	 */
	public function createAddressItem( array $values = [] )
	{
		return $this->controller->createAddressItem( $values );
	}


	/**
	 * Deletes a address item that belongs to the current authenticated user
	 *
	 * @param string $id Unique customer address ID
	 * @since 2017.04
	 */
	public function deleteAddressItem( $id )
	{
		$this->controller->deleteAddressItem( $id );
	}


	/**
	 * Saves a modified address item object
	 *
	 * @param string $id Unique customer address ID
	 * @param array $values Values added to the address item like "customer.address.city"
	 * @return \Aimeos\MShop\Customer\Item\Iface Customer address item
	 * @since 2017.04
	 */
	public function editAddressItem( $id, array $values )
	{
		return $this->controller->editAddressItem( $id, $values );
	}


	/**
	 * Returns the address item for the given ID
	 *
	 * @param string $id Unique customer address ID
	 * @return \Aimeos\MShop\Customer\Item\Address\Iface Customer address item
	 * @since 2017.04
	 */
	public function getAddressItem( $id )
	{
		return $this->controller->getAddressItem( $id );
	}


	/**
	 * Stores a modified customer address item
	 *
	 * @param \Aimeos\MShop\Customer\Item\Address\Iface Customer address item
	 * @since 2017.04
	 */
	public function saveAddressItem( \Aimeos\MShop\Customer\Item\Address\Iface $item )
	{
		$this->controller->saveAddressItem( $item );
	}


	/**
	 * Returns the frontend controller
	 *
	 * @return \Aimeos\Controller\Frontend\Customer\Iface Frontend controller object
	 * @since 2017.04
	 */
	protected function getController()
	{
		return $this->controller;
	}
}
