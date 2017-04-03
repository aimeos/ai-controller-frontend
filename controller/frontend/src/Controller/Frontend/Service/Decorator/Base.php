<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2016
 * @package Controller
 * @subpackage Frontend
 */


namespace Aimeos\Controller\Frontend\Service\Decorator;

use \Psr\Http\Message\ServerRequestInterface;
use \Psr\Http\Message\ResponseInterface;


/**
 * Base for service frontend controller decorators
 *
 * @package Controller
 * @subpackage Frontend
 */
abstract class Base
	extends \Aimeos\Controller\Frontend\Base
	implements \Aimeos\Controller\Frontend\Common\Decorator\Iface, \Aimeos\Controller\Frontend\Service\Iface
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
		$iface = '\Aimeos\Controller\Frontend\Service\Iface';
		if( !( $controller instanceof $iface ) )
		{
			$msg = sprintf( 'Class "%1$s" does not implement interface "%2$s"', get_class( $controller ), $iface );
			throw new \Aimeos\Controller\Frontend\Exception( $msg );
		}

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
	 * Returns a list of attributes that are invalid
	 *
	 * @param string $serviceId Unique service ID
	 * @param string[] $attributes List of attribute codes as keys and strings entered by the customer as value
	 * @return string[] List of attributes codes as keys and error messages as values for invalid or missing values
	 */
	public function checkAttributes( $serviceId, array $attributes )
	{
		return $this->controller->checkAttributes( $serviceId, $attributes );
	}


	/**
	 * Returns the service item for the given ID
	 *
	 * @param string $serviceId Unique service ID
	 * @param array $ref List of domains for which the items referenced by the services should be fetched too
	 * @return \Aimeos\MShop\Service\Provider\Iface Service provider object
	 */
	public function getProvider( $serviceId, $ref = ['media', 'price', 'text'] )
	{
		return $this->controller->getProvider( $serviceId, $ref );
	}


	/**
	 * Returns the service providers for the given type
	 *
	 * @param string|null $type Service type, e.g. "delivery" (shipping related), "payment" (payment related) or null for all
	 * @param array $ref List of domains for which the items referenced by the services should be fetched too
	 * @return \Aimeos\MShop\Service\Provider\Iface[] List of service IDs as keys and service provider objects as values
	 */
	public function getProviders( $type = null, $ref = ['media', 'price', 'text'] )
	{
		return $this->controller->getProviders( $type, $ref );
	}


	/**
	 * Processes the service for the given order, e.g. payment and delivery services
	 *
	 * @param \Aimeos\MShop\Order\Item\Iface $orderItem Order which should be processed
	 * @param string $serviceId Unique service item ID
	 * @param array $urls Associative list of keys and the corresponding URLs
	 * 	(keys are <type>.url-self, <type>.url-success, <type>.url-update where type can be "delivery" or "payment")
	 * @param array $params Request parameters and order service attributes
	 * @return \Aimeos\MShop\Common\Item\Helper\Form\Iface|null Form object with URL, parameters, etc.
	 * 	or null if no form data is required
	 */
	public function process( \Aimeos\MShop\Order\Item\Iface $orderItem, $serviceId, array $urls, array $params )
	{
		return $this->controller->process( $orderItem, $serviceId, $urls, $params );
	}


	/**
	 * Updates the payment or delivery status for the given request
	 *
	 * @param ServerRequestInterface $request Request object with parameters and request body
	 * @param ResponseInterface $response Response object that will contain HTTP status and response body
	 * @param array $urls Associative list of keys and the corresponding URLs
	 * 	(keys are <type>.url-self, <type>.url-success, <type>.url-update where type can be "delivery" or "payment")
	 * @return \Aimeos\MShop\Order\Item\Iface $orderItem Order item that has been updated
	 */
	public function updateSync( ServerRequestInterface $request, ResponseInterface $response, array $urls )
	{
		return $this->controller->updateSync( $request, $response, $urls );
	}


	/**
	 * Returns the service items that are available for the service type and the content of the basket.
	 *
	 * @param string $type Service type, e.g. "delivery" (shipping related) or "payment" (payment related)
	 * @param \Aimeos\MShop\Order\Item\Base\Iface $basket Basket of the user
	 * @param array $ref List of domains for which the items referenced by the services should be fetched too
	 * @return array List of service items implementing \Aimeos\MShop\Service\Item\Iface with referenced items
	 * @deprecated Use getProviders() instead
	 */
	public function getServices( $type, \Aimeos\MShop\Order\Item\Base\Iface $basket,
		$ref = array( 'media', 'price', 'text' ) )
	{
		return $this->controller->getServices( $type, $basket, $ref );
	}


	/**
	 * Returns the list of attribute definitions which must be used to render the input form where the customer can
	 * enter or chose the required data necessary by the service provider.
	 *
	 * @param string $type Service type, e.g. "delivery" (shipping related) or "payment" (payment related)
	 * @param string $serviceId Identifier of one of the service option returned by getService()
	 * @param \Aimeos\MShop\Order\Item\Base\Iface $basket Basket object
	 * @return array List of attribute definitions implementing \Aimeos\MW\Criteria\Attribute\Iface
	 * @deprecated Use getProvider() instead
	 */
	public function getServiceAttributes( $type, $serviceId, \Aimeos\MShop\Order\Item\Base\Iface $basket )
	{
		return $this->controller->getServiceAttributes( $type, $serviceId, $basket );
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
	 * @deprecated Use getProvider() instead
	 */
	public function getServicePrice( $type, $serviceId, \Aimeos\MShop\Order\Item\Base\Iface $basket )
	{
		return $this->controller->getServicePrice( $type, $serviceId, $basket );
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
	 * @deprecated Use checkAttributes() instead
	 */
	public function checkServiceAttributes( $type, $serviceId, array $attributes )
	{
		return $this->controller->checkServiceAttributes( $type, $serviceId, $attributes );
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
