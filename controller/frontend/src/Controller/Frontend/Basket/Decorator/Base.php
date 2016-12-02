<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2016
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
abstract class Base extends \Aimeos\Controller\Frontend\Common\Decorator\Base
{
	/**
	 * Empties the basket and removing all products, addresses, services, etc.
	 * @return void
	 */
	public function clear()
	{
		$this->getController()->clear();
	}


	/**
	 * Returns the basket object.
	 *
	 * @return \Aimeos\MShop\Order\Item\Base\Iface Basket holding products, addresses and delivery/payment options
	 */
	public function get()
	{
		return $this->getController()->get();
	}


	/**
	 * Explicitely persists the basket content
	 */
	public function save()
	{
		$this->getController()->save();
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
	 * @param array $hiddenAttributeIds List of attribute IDs that should be stored along with the product in the order
	 * @param array $customAttributeValues Associative list of attribute IDs and arbitrary values that should be stored
	 * 	along with the product in the order
	 * @param string $stocktype Unique code of the stock type to deliver the products from
	 * @throws \Aimeos\Controller\Frontend\Basket\Exception If the product isn't available
	 * @return void
	 */
	public function addProduct( $prodid, $quantity = 1, array $options = array(), array $variantAttributeIds = array(),
		array $configAttributeIds = array(), array $hiddenAttributeIds = array(), array $customAttributeValues = array(),
		$stocktype = 'default' )
	{
		$this->getController()->addProduct(
			$prodid, $quantity, $options, $variantAttributeIds, $configAttributeIds,
			$hiddenAttributeIds, $customAttributeValues, $stocktype
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
		$this->getController()->deleteProduct( $position );
	}


	/**
	 * Edits the quantity of a product item in the basket.
	 *
	 * @param integer $position Position number (key) of the order product item
	 * @param integer $quantity New quantiy of the product item
	 * @param array $options Possible options are: 'stock'=>true|false
	 * @param array $configAttributeCodes Codes of the product config attributes that should be REMOVED
	 * @return void
	 */
	public function editProduct( $position, $quantity, array $options = array(), array $configAttributeCodes = array() )
	{
		$this->getController()->editProduct( $position, $quantity, $options, $configAttributeCodes );
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
		$this->getController()->addCoupon( $code );
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
		$this->getController()->deleteCoupon( $code );
	}


	/**
	 * Sets the address of the customer in the basket.
	 *
	 * @param string $type Address type constant from \Aimeos\MShop\Order\Item\Base\Address\Base
	 * @param \Aimeos\MShop\Common\Item\Address\Iface|array|null $value Address object or array with key/value pairs of address or null to remove address from basket
	 * @throws \Aimeos\Controller\Frontend\Basket\Exception If the billing or delivery address is not of any required type of
	 * 	if one of the keys is invalid when using an array with key/value pairs
	 * @return void
	 */
	public function setAddress( $type, $value )
	{
		$this->getController()->setAddress( $type, $value );
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
	public function setService( $type, $id, array $attributes = array() )
	{
		$this->getController()->setService( $type, $id, $attributes );
	}
}
