<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Metaways Infosystems GmbH, 2012
 * @copyright Aimeos (aimeos.org), 2015-2016
 * @package Controller
 * @subpackage Frontend
 */


namespace Aimeos\Controller\Frontend\Service;

use \Psr\Http\Message\ServerRequestInterface;
use \Psr\Http\Message\ResponseInterface;


/**
 * Interface for service frontend controllers.
 *
 * @package Controller
 * @subpackage Frontend
 */
interface Iface
{
	/**
	 * Returns a list of attributes that are invalid
	 *
	 * @param string $serviceId Unique service ID
	 * @param string[] $attributes List of attribute codes as keys and strings entered by the customer as value
	 * @return string[] List of attributes codes as keys and error messages as values for invalid or missing values
	 */
	public function checkAttributes( $serviceId, array $attributes );

	/**
	 * Returns the service item for the given ID
	 *
	 * @param string $serviceId Unique service ID
	 * @param array $ref List of domains for which the items referenced by the services should be fetched too
	 * @return \Aimeos\MShop\Service\Provider\Iface Service provider object
	 */
	public function getProvider( $serviceId, $ref = ['media', 'price', 'text'] );

	/**
	 * Returns the service providers for the given type
	 *
	 * @param string|null $type Service type, e.g. "delivery" (shipping related), "payment" (payment related) or null for all
	 * @param array $ref List of domains for which the items referenced by the services should be fetched too
	 * @return \Aimeos\MShop\Service\Provider\Iface[] List of service IDs as keys and service provider objects as values
	 */
	public function getProviders( $type = null, $ref = ['media', 'price', 'text'] );

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
	public function process( \Aimeos\MShop\Order\Item\Iface $orderItem, $serviceId, array $urls, array $params );

	/**
	 * Updates the payment or delivery status for the given request
	 *
	 * @param ServerRequestInterface $request Request object with parameters and request body
	 * @param ResponseInterface $response Response object that will contain HTTP status and response body
	 * @param array $urls Associative list of keys and the corresponding URLs
	 * 	(keys are <type>.url-self, <type>.url-success, <type>.url-update where type can be "delivery" or "payment")
	 * @return \Aimeos\MShop\Order\Item\Iface $orderItem Order item that has been updated
	 */
	public function updateSync( ServerRequestInterface $request, ResponseInterface $response, array $urls );
}
