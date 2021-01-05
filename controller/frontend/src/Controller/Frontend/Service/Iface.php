<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Metaways Infosystems GmbH, 2012
 * @copyright Aimeos (aimeos.org), 2015-2021
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
	 * Adds generic condition for filtering services
	 *
	 * @param string $operator Comparison operator, e.g. "==", "!=", "<", "<=", ">=", ">", "=~", "~="
	 * @param string $key Search key defined by the service manager, e.g. "service.status"
	 * @param array|string $value Value or list of values to compare to
	 * @return \Aimeos\Controller\Frontend\Service\Iface Service controller for fluent interface
	 * @since 2019.04
	 */
	public function compare( string $operator, string $key, $value ) : \Aimeos\Controller\Frontend\Service\Iface;

	/**
	 * Returns the service for the given code
	 *
	 * @param string $code Unique service code
	 * @return \Aimeos\MShop\Service\Item\Iface Service item including the referenced domains items
	 * @since 2019.04
	 */
	public function find( string $code ) : \Aimeos\MShop\Service\Item\Iface;

	/**
	 * Creates a search function string for the given name and parameters
	 *
	 * @param string $name Name of the search function without parenthesis, e.g. "service:has"
	 * @param array $params List of parameters for the search function with numeric keys starting at 0
	 * @return string Search function string that can be used in compare()
	 */
	public function function( string $name, array $params ) : string;

	/**
	 * Returns the service for the given ID
	 *
	 * @param string $id Unique service ID
	 * @return \Aimeos\MShop\Service\Item\Iface Service item including the referenced domains items
	 * @since 2019.04
	 */
	public function get( string $id ) : \Aimeos\MShop\Service\Item\Iface;

	/**
	 * Returns the service item for the given ID
	 *
	 * @param string $serviceId Unique service ID
	 * @return \Aimeos\MShop\Service\Provider\Iface Service provider object
	 */
	public function getProvider( string $id ) : \Aimeos\MShop\Service\Provider\Iface;

	/**
	 * Returns the service providers
	 *
	 * @return \Aimeos\Map List of service IDs as keys and service provider objects as values
	 */
	public function getProviders() : \Aimeos\Map;

	/**
	 * Parses the given array and adds the conditions to the list of conditions
	 *
	 * @param array $conditions List of conditions, e.g. ['&&' => [['>' => ['service.status' => 0]], ['==' => ['service.type' => 'default']]]]
	 * @return \Aimeos\Controller\Frontend\Service\Iface Service controller for fluent interface
	 * @since 2019.04
	 */
	public function parse( array $conditions ) : \Aimeos\Controller\Frontend\Service\Iface;

	/**
	 * Processes the payment service for the given order
	 *
	 * @param \Aimeos\MShop\Order\Item\Iface $orderItem Order which should be processed
	 * @param string $serviceId Unique service item ID
	 * @param array $urls Associative list of keys and the corresponding URLs
	 * 	(keys are payment.url-self, payment.url-success, payment.url-update)
	 * @param array $params Request parameters and order service attributes
	 * @return \Aimeos\MShop\Common\Helper\Form\Iface|null Form object with URL, parameters, etc.
	 * 	or null if no form data is required
	 */
	public function process( \Aimeos\MShop\Order\Item\Iface $orderItem,
		string $id, array $urls, array $params ) : ?\Aimeos\MShop\Common\Helper\Form\Iface;

	/**
	 * Returns the services filtered by the previously assigned conditions
	 *
	 * @param int &$total Parameter where the total number of found services will be stored in
	 * @return \Aimeos\Map Ordered list of items implementing \Aimeos\MShop\Service\Item\Iface
	 * @since 2019.04
	 */
	public function search( int &$total = null ) : \Aimeos\Map;

	/**
	 * Sets the start value and the number of returned services for slicing the list of found services
	 *
	 * @param int $start Start value of the first attribute in the list
	 * @param int $limit Number of returned services
	 * @return \Aimeos\Controller\Frontend\Service\Iface Service controller for fluent interface
	 * @since 2019.04
	 */
	public function slice( int $start, int $limit ) : \Aimeos\Controller\Frontend\Service\Iface;

	/**
	 * Sets the sorting of the result list
	 *
	 * @param string|null $key Sorting of the result list like "position", null for no sorting
	 * @return \Aimeos\Controller\Frontend\Service\Iface Service controller for fluent interface
	 * @since 2019.04
	 */
	public function sort( string $key = null ) : \Aimeos\Controller\Frontend\Service\Iface;

	/**
	 * Adds attribute types for filtering
	 *
	 * @param array|string $code Service type or list of types
	 * @return \Aimeos\Controller\Frontend\Service\Iface Service controller for fluent interface
	 * @since 2019.04
	 */
	public function type( $code ) : \Aimeos\Controller\Frontend\Service\Iface;

	/**
	 * Updates the order status sent by payment gateway notifications
	 *
	 * @param ServerRequestInterface $request Request object
	 * @param ResponseInterface $response Response object that will contain HTTP status and response body
	 * @param string $code Unique code of the service used for the current order
	 * @return \Psr\Http\Message\ResponseInterface Response object
	 */
	public function updatePush( ServerRequestInterface $request, ResponseInterface $response,
		string $code ) : \Psr\Http\Message\ResponseInterface;

	/**
	 * Updates the payment or delivery status for the given request
	 *
	 * @param ServerRequestInterface $request Request object with parameters and request body
	 * @param string $code Unique code of the service used for the current order
	 * @param string $orderid ID of the order whose payment status should be updated
	 * @return \Aimeos\MShop\Order\Item\Iface $orderItem Order item that has been updated
	 */
	public function updateSync( ServerRequestInterface $request,
		string $code, string $orderid ) : \Aimeos\MShop\Order\Item\Iface;

	/**
	 * Sets the referenced domains that will be fetched too when retrieving items
	 *
	 * @param array $domains Domain names of the referenced items that should be fetched too
	 * @return \Aimeos\Controller\Frontend\Service\Iface Service controller for fluent interface
	 * @since 2019.04
	 */
	public function uses( array $domains ) : \Aimeos\Controller\Frontend\Service\Iface;
}
