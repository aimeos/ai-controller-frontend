<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2017-2021
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
	use \Aimeos\Controller\Frontend\Common\Decorator\Traits;


	private $controller;


	/**
	 * Initializes the controller decorator.
	 *
	 * @param \Aimeos\Controller\Frontend\Iface $controller Controller object
	 * @param \Aimeos\MShop\Context\Item\Iface $context Context object with required objects
	 */
	public function __construct( \Aimeos\Controller\Frontend\Iface $controller, \Aimeos\MShop\Context\Item\Iface $context )
	{
		parent::__construct( $context );

		$iface = \Aimeos\Controller\Frontend\Customer\Iface::class;
		$this->controller = \Aimeos\MW\Common\Base::checkClass( $iface, $controller );
	}


	/**
	 * Passes unknown methods to wrapped objects.
	 *
	 * @param string $name Name of the method
	 * @param array $param List of method parameter
	 * @return mixed Returns the value of the called method
	 * @throws \Aimeos\Controller\Frontend\Exception If method call failed
	 */
	public function __call( string $name, array $param )
	{
		return @call_user_func_array( array( $this->controller, $name ), $param );
	}


	/**
	 * Adds and returns a new customer item object
	 *
	 * @param array $values Values added to the customer item (new or existing) like "customer.code"
	 * @return \Aimeos\Controller\Frontend\Customer\Iface Customer controller for fluent interface
	 * @since 2019.04
	 */
	public function add( array $values ) : \Aimeos\Controller\Frontend\Customer\Iface
	{
		$this->controller->add( $values );
		return $this;
	}


	/**
	 * Adds the given address item to the customer object (not yet stored)
	 *
	 * @param \Aimeos\MShop\Common\Item\Address\Iface $item Address item to add
	 * @param int|null $pos Position (key) in the list of address items or null to add the item at the end
	 * @return \Aimeos\Controller\Frontend\Customer\Iface Customer controller for fluent interface
	 * @since 2019.04
	 */
	public function addAddressItem( \Aimeos\MShop\Common\Item\Address\Iface $item,
		int $position = null ) : \Aimeos\Controller\Frontend\Customer\Iface
	{
		$this->controller->addAddressItem( $item, $position );
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
		\Aimeos\MShop\Common\Item\Iface $refItem = null ) : \Aimeos\Controller\Frontend\Customer\Iface
	{
		$this->controller->addListItem( $domain, $item, $refItem );
		return $this;
	}


	/**
	 * Adds the given property item to the customer object (not yet stored)
	 *
	 * @param \Aimeos\MShop\Common\Item\Property\Iface $item Property item to add
	 * @return \Aimeos\Controller\Frontend\Customer\Iface Customer controller for fluent interface
	 * @since 2019.04
	 */
	public function addPropertyItem( \Aimeos\MShop\Common\Item\Property\Iface $item ) : \Aimeos\Controller\Frontend\Customer\Iface
	{
		$this->controller->addPropertyItem( $item );
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
		return $this->controller->createAddressItem( $values );
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
		return $this->controller->createListItem( $values );
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
		return $this->controller->createPropertyItem( $values );
	}



	/**
	 * Deletes a customer item that belongs to the current authenticated user
	 *
	 * @return \Aimeos\Controller\Frontend\Customer\Iface Customer controller for fluent interface
	 * @since 2017.04
	 */
	public function delete() : \Aimeos\Controller\Frontend\Customer\Iface
	{
		$this->controller->delete();
		return $this;
	}


	/**
	 * Removes the given address item from the customer object (not yet stored)
	 *
	 * @param \Aimeos\MShop\Common\Item\Address\Iface $item Address item to remove
	 * @return \Aimeos\Controller\Frontend\Customer\Iface Customer controller for fluent interface
	 */
	public function deleteAddressItem( \Aimeos\MShop\Common\Item\Address\Iface $item ) : \Aimeos\Controller\Frontend\Customer\Iface
	{
		$this->controller->deleteAddressItem( $item );
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
		\Aimeos\MShop\Common\Item\Iface $refItem = null ) : \Aimeos\Controller\Frontend\Customer\Iface
	{
		$this->controller->deleteListItem( $domain, $listItem, $refItem );
		return $this;
	}


	/**
	 * Removes the given property item from the customer object (not yet stored)
	 *
	 * @param \Aimeos\MShop\Common\Item\Property\Iface $item Property item to remove
	 * @return \Aimeos\Controller\Frontend\Customer\Iface Customer controller for fluent interface
	 */
	public function deletePropertyItem( \Aimeos\MShop\Common\Item\Property\Iface $item ) : \Aimeos\Controller\Frontend\Customer\Iface
	{
		$this->controller->deletePropertyItem( $item );
		return $this;
	}


	/**
	 * Returns the customer item for the given code
	 *
	 * This method doesn't check if the customer item belongs to the logged in user!
	 *
	 * @param string $code Unique customer code
	 * @return \Aimeos\MShop\Customer\Item\Iface Customer item
	 * @since 2019.04
	 */
	public function find( string $code ) : \Aimeos\MShop\Customer\Item\Iface
	{
		return $this->controller->find( $code );
	}


	/**
	 * Returns the customer item for the current authenticated user
	 *
	 * @return \Aimeos\MShop\Customer\Item\Iface Customer item
	 * @since 2019.04
	 */
	public function get() : \Aimeos\MShop\Customer\Item\Iface
	{
		return $this->controller->get();
	}


	/**
	 * Adds or updates the modified customer item in the storage
	 *
	 * @return \Aimeos\Controller\Frontend\Customer\Iface Customer controller for fluent interface
	 * @since 2019.04
	 */
	public function store() : \Aimeos\Controller\Frontend\Customer\Iface
	{
		$this->controller->store();
		return $this;
	}


	/**
	 * Sets the domains that will be used when working with the customer item
	 *
	 * @param array $domains Domain names of the referenced items that should be fetched too
	 * @return \Aimeos\Controller\Frontend\Customer\Iface Customer controller for fluent interface
	 * @since 2019.04
	 */
	public function uses( array $domains ) : \Aimeos\Controller\Frontend\Customer\Iface
	{
		$this->controller->uses( $domains );
		return $this;
	}


	/**
	 * Injects the reference of the outmost object
	 *
	 * @param \Aimeos\Controller\Frontend\Iface $object Reference to the outmost controller or decorator
	 * @return \Aimeos\Controller\Frontend\Iface Controller object for chaining method calls
	 */
	public function setObject( \Aimeos\Controller\Frontend\Iface $object ) : \Aimeos\Controller\Frontend\Iface
	{
		parent::setObject( $object );

		$this->controller->setObject( $object );

		return $this;
	}


	/**
	 * Returns the frontend controller
	 *
	 * @return \Aimeos\Controller\Frontend\Iface Frontend controller object
	 * @since 2017.04
	 */
	protected function getController() : \Aimeos\Controller\Frontend\Iface
	{
		return $this->controller;
	}
}
