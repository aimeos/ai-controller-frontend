<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2016-2023
 * @package Controller
 * @subpackage Frontend
 */


namespace Aimeos\Controller\Frontend\Basket\Decorator;


/**
 * Base for basket frontend controller decorators
 *
 * @package Controller
 * @subpackage Frontend
 */
abstract class Base
	extends \Aimeos\Controller\Frontend\Basket\Base
	implements \Aimeos\Controller\Frontend\Common\Decorator\Iface, \Aimeos\Controller\Frontend\Basket\Iface
{
	use \Aimeos\Controller\Frontend\Common\Decorator\Traits;


	private \Aimeos\Controller\Frontend\Basket\Iface $controller;


	/**
	 * Initializes the controller decorator.
	 *
	 * @param \Aimeos\Controller\Frontend\Iface $controller Controller object
	 * @param \Aimeos\MShop\ContextIface $context Context object with required objects
	 */
	public function __construct( \Aimeos\Controller\Frontend\Iface $controller, \Aimeos\MShop\ContextIface $context )
	{
		parent::__construct( $context );

		$this->controller = $controller;
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
	 * Adds values like comments to the basket
	 *
	 * @param array $values Order values like comment
	 * @return \Aimeos\Controller\Frontend\Basket\Iface Basket frontend object for fluent interface
	 */
	public function add( array $values ) : \Aimeos\Controller\Frontend\Basket\Iface
	{
		$this->controller->add( $values );
		return $this;
	}


	/**
	 * Empties the basket and removing all products, addresses, services, etc.
	 *
	 * @return \Aimeos\Controller\Frontend\Basket\Iface Basket frontend object for fluent interface
	 */
	public function clear() : \Aimeos\Controller\Frontend\Basket\Iface
	{
		$this->controller->clear();
		return $this;
	}


	/**
	 * Returns the basket object.
	 *
	 * @return \Aimeos\MShop\Order\Item\Iface Basket holding products, addresses and delivery/payment options
	 */
	public function get() : \Aimeos\MShop\Order\Item\Iface
	{
		return $this->controller->get();
	}


	/**
	 * Explicitely persists the basket content
	 *
	 * @return \Aimeos\Controller\Frontend\Basket\Iface Basket frontend object for fluent interface
	 */
	public function save() : \Aimeos\Controller\Frontend\Basket\Iface
	{
		$this->controller->save();
		return $this;
	}


	/**
	 * Sets the new basket type
	 *
	 * @param string $type Basket type
	 * @return \Aimeos\Controller\Frontend\Basket\Iface Basket frontend object for fluent interface
	 */
	public function setType( string $type ) : \Aimeos\Controller\Frontend\Basket\Iface
	{
		$this->controller->setType( $type );
		return $this;
	}


	/**
	 * Creates a new order object from the current basket
	 *
	 * @return \Aimeos\MShop\Order\Item\Iface Order object including products, addresses and services
	 */
	public function store() : \Aimeos\MShop\Order\Item\Iface
	{
		return $this->controller->store();
	}


	/**
	 * Returns the order object for the given ID
	 *
	 * @param string $id Unique ID of the order object
	 * @param array $ref References items that should be fetched too
	 * @param bool $default True to add default criteria (user logged in), false if not
	 * @return \Aimeos\MShop\Order\Item\Iface Order object including the given parts
	 */
	public function load( string $id, array $ref = ['order/address', 'order/coupon', 'order/product', 'order/service'],
		bool $default = true ) : \Aimeos\MShop\Order\Item\Iface
	{
		return $this->controller->load( $id, $ref, $default );
	}


	/**
	 * Adds a product to the basket of the customer stored in the session
	 *
	 * @param \Aimeos\MShop\Product\Item\Iface $product Product to add including texts, media, prices, attributes, etc.
	 * @param float $quantity Amount of products that should by added
	 * @param array $variant List of variant-building attribute IDs that identify an article in a selection product
	 * @param array $config List of configurable attribute IDs the customer has chosen from
	 * @param array $custom Associative list of attribute IDs as keys and arbitrary values that will be added to the ordered product
	 * @param string $stocktype Unique code of the stock type to deliver the products from
	 * @param string|null $siteId Unique ID of the site the product should be bought from or NULL for site the product is from
	 * @return \Aimeos\Controller\Frontend\Basket\Iface Basket frontend object for fluent interface
	 * @throws \Aimeos\Controller\Frontend\Basket\Exception If the product isn't available
	 */
	public function addProduct( \Aimeos\MShop\Product\Item\Iface $product, float $quantity = 1,
		array $variant = [], array $config = [], array $custom = [], string $stocktype = 'default', string $siteId = null
	) : \Aimeos\Controller\Frontend\Basket\Iface
	{
		$this->controller->addProduct( $product, $quantity, $variant, $config, $custom, $stocktype, $siteId );
		return $this;
	}


	/**
	 * Deletes a product item from the basket.
	 *
	 * @param int $position Position number (key) of the order product item
	 * @return \Aimeos\Controller\Frontend\Basket\Iface Basket frontend object for fluent interface
	 */
	public function deleteProduct( int $position ) : \Aimeos\Controller\Frontend\Basket\Iface
	{
		$this->controller->deleteProduct( $position );
		return $this;
	}


	/**
	 * Edits the quantity of a product item in the basket.
	 *
	 * @param int $position Position number (key) of the order product item
	 * @param float $quantity New quantiy of the product item
	 * @return \Aimeos\Controller\Frontend\Basket\Iface Basket frontend object for fluent interface
	 */
	public function updateProduct( int $position, float $quantity ) : \Aimeos\Controller\Frontend\Basket\Iface
	{
		$this->controller->updateProduct( $position, $quantity );
		return $this;
	}


	/**
	 * Adds the given coupon code and updates the basket.
	 *
	 * @param string $code Coupon code entered by the user
	 * @return \Aimeos\Controller\Frontend\Basket\Iface Basket frontend object for fluent interface
	 * @throws \Aimeos\Controller\Frontend\Basket\Exception if the coupon code is invalid or not allowed
	 */
	public function addCoupon( string $code ) : \Aimeos\Controller\Frontend\Basket\Iface
	{
		$this->controller->addCoupon( $code );
		return $this;
	}


	/**
	 * Removes the given coupon code and its effects from the basket.
	 *
	 * @param string $code Coupon code entered by the user
	 * @return \Aimeos\Controller\Frontend\Basket\Iface Basket frontend object for fluent interface
	 * @throws \Aimeos\Controller\Frontend\Basket\Exception if the coupon code is invalid
	 */
	public function deleteCoupon( string $code ) : \Aimeos\Controller\Frontend\Basket\Iface
	{
		$this->controller->deleteCoupon( $code );
		return $this;
	}


	/**
	 * Adds an address of the customer to the basket
	 *
	 * @param string $type Address type code like 'payment' or 'delivery'
	 * @param array $values Associative list of key/value pairs with address details
	 * @param int|null $position Position number (key) of the order address item
	 * @return \Aimeos\Controller\Frontend\Basket\Iface Basket frontend object for fluent interface
	 */
	public function addAddress( string $type, array $values = [], int $position = null ) : \Aimeos\Controller\Frontend\Basket\Iface
	{
		$this->controller->addAddress( $type, $values, $position );
		return $this;
	}

	/**
	 * Removes the address of the given type and position if available
	 *
	 * @param string $type Address type code like 'payment' or 'delivery'
	 * @param int|null $position Position of the address in the list to overwrite
	 * @return \Aimeos\Controller\Frontend\Basket\Iface Basket frontend object for fluent interface
	 */
	public function deleteAddress( string $type, int $position = null ) : \Aimeos\Controller\Frontend\Basket\Iface
	{
		$this->controller->deleteAddress( $type, $position );
		return $this;
	}


	/**
	 * Adds the delivery/payment service including the given configuration
	 *
	 * @param \Aimeos\MShop\Service\Item\Iface $service Service item selected by the customer
	 * @param array $config Associative list of key/value pairs with the options selected by the customer
	 * @param int|null $position Position of the address in the list to overwrite
	 * @return \Aimeos\Controller\Frontend\Basket\Iface Basket frontend object for fluent interface
	 * @throws \Aimeos\Controller\Frontend\Basket\Exception If given service attributes are invalid
	 */
	public function addService( \Aimeos\MShop\Service\Item\Iface $service, array $config = [], int $position = null ) : \Aimeos\Controller\Frontend\Basket\Iface
	{
		$this->controller->addService( $service, $config, $position );
		return $this;
	}


	/**
	 * Removes the delivery or payment service items from the basket
	 *
	 * @param string $type Service type code like 'payment' or 'delivery'
	 * @param int|null $position Position of the address in the list to overwrite
	 * @return \Aimeos\Controller\Frontend\Basket\Iface Basket frontend object for fluent interface
	 */
	public function deleteService( string $type, int $position = null ) : \Aimeos\Controller\Frontend\Basket\Iface
	{
		$this->controller->deleteService( $type, $position );
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
	 */
	protected function getController() : \Aimeos\Controller\Frontend\Iface
	{
		return $this->controller;
	}
}
