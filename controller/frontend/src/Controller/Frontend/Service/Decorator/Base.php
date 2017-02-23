<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2016
 * @package Controller
 * @subpackage Frontend
 */


namespace Aimeos\Controller\Frontend\Service\Decorator;


/**
 * Base for service frontend controller decorators
 *
 * @package Controller
 * @subpackage Frontend
 */
abstract class Base
	implements \Aimeos\Controller\Frontend\Common\Decorator\Iface
{
	private $context;
	private $controller;


	/**
	 * Initializes the controller decorator.
	 *
	 * @param \Aimeos\Controller\Frontend\Iface $controller Controller object
	 * @param \Aimeos\MShop\Context\Item\Iface $context Context object with required objects
	 */
	public function __construct( \Aimeos\Controller\Frontend\Iface $controller, \Aimeos\MShop\Context\Item\Iface $context )
	{
		$this->context = $context;
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
	public function __call( $name, array $param )
	{
		return @call_user_func_array( array( $this->controller, $name ), $param );
	}


	/**
	 * Returns the service items that are available for the service type and the content of the basket.
	 *
	 * @param string $type Service type, e.g. "delivery" (shipping related) or "payment" (payment related)
	 * @param \Aimeos\MShop\Order\Item\Base\Iface $basket Basket of the user
	 * @param array $ref List of domains for which the items referenced by the services should be fetched too
	 * @return array List of service items implementing \Aimeos\MShop\Service\Item\Iface with referenced items
	 */
	public function getServices( $type, \Aimeos\MShop\Order\Item\Base\Iface $basket,
		$ref = array( 'media', 'price', 'text' ) )
	{
		return $this->getController()->getServices( $type, $basket, $ref );
	}


	/**
	 * Returns the list of attribute definitions which must be used to render the input form where the customer can
	 * enter or chose the required data necessary by the service provider.
	 *
	 * @param string $type Service type, e.g. "delivery" (shipping related) or "payment" (payment related)
	 * @param string $serviceId Identifier of one of the service option returned by getService()
	 * @param \Aimeos\MShop\Order\Item\Base\Iface $basket Basket object
	 * @return array List of attribute definitions implementing \Aimeos\MW\Criteria\Attribute\Iface
	 */
	public function getServiceAttributes( $type, $serviceId, \Aimeos\MShop\Order\Item\Base\Iface $basket )
	{
		return $this->getController()->getServiceAttributes( $type, $serviceId, $basket );
	}


	/**
	 * Returns the price of the service.
	 *
	 * @param string $type Service type, e.g. "delivery" (shipping related) or "payment" (payment related)
	 * @param string $serviceId Identifier of one of the service option returned by getService()
	 * @param \Aimeos\MShop\Order\Item\Base\Iface $basket Basket with products
	 * @return \Aimeos\MShop\Price\Item\Iface Price item
	 * @throws \Aimeos\Controller\Frontend\Service\Exception If no active service provider for this ID is available
	 * @throws \Aimeos\MShop\Exception If service provider isn't available
	 * @throws \Exception If an error occurs
	 */
	public function getServicePrice( $type, $serviceId, \Aimeos\MShop\Order\Item\Base\Iface $basket )
	{
		return $this->getController()->getServicePrice( $type, $serviceId, $basket );
	}


	/**
	 * Returns a list of attributes that are invalid.
	 *
	 * @param string $type Service type, e.g. "delivery" (shipping related) or "payment" (payment related)
	 * @param string $serviceId Identifier of the service option chosen by the customer
	 * @param array $attributes List of key/value pairs with name of the attribute from attribute definition object as
	 * 	key and the string entered by the customer as value
	 * @return array List of key/value pairs of attributes keys and an error message for values that are invalid or
	 * 	missing
	 */
	public function checkServiceAttributes( $type, $serviceId, array $attributes )
	{
		return $this->getController()->checkServiceAttributes( $type, $serviceId, $attributes );
	}


	/**
	 * Returns the context item
	 *
	 * @return \Aimeos\MShop\Context\Item\Iface Context item object
	 */
	protected function getContext()
	{
		return $this->context;
	}


	/**
	 * Returns the frontend controller
	 *
	 * @return \Aimeos\Controller\Frontend\Service\Iface Frontend controller object
	 */
	protected function getController()
	{
		return $this->controller;
	}
}
