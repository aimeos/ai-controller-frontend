<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2016-2018
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
	private $controller;


	/**
	 * Initializes the controller decorator.
	 *
	 * @param \Aimeos\Controller\Frontend\Iface $controller Controller object
	 * @param \Aimeos\MShop\Context\Item\Iface $context Context object with required objects
	 */
	public function __construct( \Aimeos\Controller\Frontend\Iface $controller, \Aimeos\MShop\Context\Item\Iface $context )
	{
		\Aimeos\MW\Common\Base::checkClass( '\\Aimeos\\Controller\\Frontend\\Basket\\Iface', $controller );

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
	 * Empties the basket and removing all products, addresses, services, etc.
	 * @return void
	 */
	public function clear()
	{
		$this->controller->clear();
		return $this;
	}


	/**
	 * Returns the basket object.
	 *
	 * @return \Aimeos\MShop\Order\Item\Base\Iface Basket holding products, addresses and delivery/payment options
	 */
	public function get()
	{
		return $this->controller->get();
	}


	/**
	 * Explicitely persists the basket content
	 */
	public function save()
	{
		$this->controller->save();
		return $this;
	}


	/**
	 * Sets the new basket type
	 *
	 * @param string $type Basket type
	 * @return \Aimeos\Controller\Frontend\Basket\Iface Basket frontend object
	 */
	public function setType( $type )
	{
		$this->controller->setType( $type );
		return $this;
	}


	/**
	 * Creates a new order base object from the current basket
	 *
	 * @return \Aimeos\MShop\Order\Item\Base\Iface Order base object including products, addresses and services
	 */
	public function store()
	{
		return $this->controller->store();
	}


	/**
	 * Returns the order base object for the given ID
	 *
	 * @param string $id Unique ID of the order base object
	 * @param integer $parts Constants which parts of the order base object should be loaded
	 * @param boolean $default True to add default criteria (user logged in), false if not
	 * @return \Aimeos\MShop\Order\Item\Base\Iface Order base object including the given parts
	 */
	public function load( $id, $parts = \Aimeos\MShop\Order\Item\Base\Base::PARTS_ALL, $default = true )
	{
		return $this->controller->load( $id, $parts, $default );
	}


	/**
	 * Adds a categorized product to the basket of the user stored in the session.
	 *
	 * @param string $prodid ID of the base product to add
	 * @param integer $quantity Amount of products that should by added
	 * @param array $options Possible options are: 'stock'=>true|false and 'variant'=>true|false
	 * 	The 'stock'=>false option allows adding products without being in stock.
	 * 	The 'variant'=>false option allows adding the selection product to the basket
	 * 	instead of the specific sub-product if the variant-building attribute IDs
	 * 	doesn't match a specific sub-product or if the attribute IDs are missing.
	 * @param array $variantAttributeIds List of variant-building attribute IDs that identify a specific product
	 * 	in a selection products
	 * @param array $configAttributeIds  List of attribute IDs that doesn't identify a specific product in a
	 * 	selection of products but are stored together with the product (e.g. for configurable products)
	 * @param array $hiddenAttributeIds Deprecated
	 * @param array $customAttributeValues Associative list of attribute IDs and arbitrary values that should be stored
	 * 	along with the product in the order
	 * @param string $stocktype Unique code of the stock type to deliver the products from
	 * @throws \Aimeos\Controller\Frontend\Basket\Exception If the product isn't available
	 * @return void
	 */
	public function addProduct( $prodid, $quantity = 1, $stocktype = 'default', array $variantAttributeIds = [],
		array $configAttributeIds = [], array $hiddenAttributeIds = [], array $customAttributeValues = [] )
	{
		$this->controller->addProduct(
			$prodid, $quantity, $stocktype, $variantAttributeIds,
			$configAttributeIds, $hiddenAttributeIds, $customAttributeValues
		);
	}


	/**
	 * Deletes a product item from the basket.
	 *
	 * @param integer $position Position number (key) of the order product item
	 * @return void
	 */
	public function deleteProduct( $position )
	{
		$this->controller->deleteProduct( $position );
	}


	/**
	 * Edits the quantity of a product item in the basket.
	 *
	 * @param integer $position Position number (key) of the order product item
	 * @param integer $quantity New quantiy of the product item
	 * @param array $configAttributeCodes Codes of the product config attributes that should be REMOVED
	 * @return void
	 */
	public function editProduct( $position, $quantity, array $configAttributeCodes = [] )
	{
		$this->controller->editProduct( $position, $quantity, $configAttributeCodes );
	}


	/**
	 * Adds the given coupon code and updates the basket.
	 *
	 * @param string $code Coupon code entered by the user
	 * @throws \Aimeos\Controller\Frontend\Basket\Exception if the coupon code is invalid or not allowed
	 * @return void
	 */
	public function addCoupon( $code )
	{
		$this->controller->addCoupon( $code );
	}


	/**
	 * Removes the given coupon code and its effects from the basket.
	 *
	 * @param string $code Coupon code entered by the user
	 * @throws \Aimeos\Controller\Frontend\Basket\Exception if the coupon code is invalid
	 * @return void
	 */
	public function deleteCoupon( $code )
	{
		$this->controller->deleteCoupon( $code );
	}


	/**
	 * Adds the delivery/payment service item based on the service ID.
	 *
	 * @param string $type Address type constant from \Aimeos\MShop\Order\Item\Base\Address\Base
	 * @param \Aimeos\MShop\Common\Item\Address\Iface|array|null $value Address object or array with key/value pairs of address or null to remove address from basket
	 * @throws \Aimeos\Controller\Frontend\Basket\Exception If the billing or delivery address is not of any required type of
	 * 	if one of the keys is invalid when using an array with key/value pairs
	 * @return void
	 */
	public function setAddress( $type, $value )
	{
		$this->controller->setAddress( $type, $value );
	}


	/**
	 * Sets the delivery/payment service item based on the service ID.
	 *
	 * @param string $type Service type code like 'payment' or 'delivery'
	 * @param string $id Unique ID of the service item
	 * @param array $attributes Associative list of key/value pairs containing the attributes selected or
	 * 	entered by the customer when choosing one of the delivery or payment options
	 * @throws \Aimeos\Controller\Frontend\Basket\Exception If there is no price to the service item attached
	 * @return void
	 */
	public function addService( $type, $id, array $attributes = [] )
	{
		$this->controller->addService( $type, $id, $attributes );
	}


	/**
	 * Removes the delivery or payment service items from the basket
	 *
	 * @param string $type Service type code like 'payment' or 'delivery'
	 */
	public function deleteService( $type )
	{
		$this->controller->deleteService( $type );
	}


	/**
	 * Returns the frontend controller
	 *
	 * @return \Aimeos\Controller\Frontend\Basket\Iface Frontend controller object
	 */
	protected function getController()
	{
		return $this->controller;
	}
}
