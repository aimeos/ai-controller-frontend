<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Metaways Infosystems GmbH, 2012
 * @copyright Aimeos (aimeos.org), 2015-2018
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
	public function compare( $operator, $key, $value );

	/**
	 * Returns the service for the given code
	 *
	 * @param string $code Unique service code
	 * @return \Aimeos\MShop\Service\Item\Iface Service item including the referenced domains items
	 * @since 2019.04
	 */
	public function find( $code );

	/**
	 * Returns the service for the given ID
	 *
	 * @param string $id Unique service ID
	 * @return \Aimeos\MShop\Service\Item\Iface Service item including the referenced domains items
	 * @since 2019.04
	 */
	public function get( $id );

	/**
	 * Returns the service item for the given ID
	 *
	 * @param string $serviceId Unique service ID
	 * @return \Aimeos\MShop\Service\Provider\Iface Service provider object
	 */
	public function getProvider( $id );

	/**
	 * Returns the service providers
	 *
	 * @return \Aimeos\MShop\Service\Provider\Iface[] List of service IDs as keys and service provider objects as values
	 */
	public function getProviders();

	/**
	 * Parses the given array and adds the conditions to the list of conditions
	 *
	 * @param array $conditions List of conditions, e.g. ['&&' => [['>' => ['service.status' => 0]], ['==' => ['service.type' => 'default']]]]
	 * @return \Aimeos\Controller\Frontend\Service\Iface Service controller for fluent interface
	 * @since 2019.04
	 */
	public function parse( array $conditions );

	/**
	 * Processes the service for the given order, e.g. payment and delivery services
	 *
	 * @param \Aimeos\MShop\Order\Item\Iface $orderItem Order which should be processed
	 * @param string $id Unique service item ID
	 * @param array $urls Associative list of keys and the corresponding URLs
	 * 	(keys are <type>.url-self, <type>.url-success, <type>.url-update where type can be "delivery" or "payment")
	 * @param array $params Request parameters and order service attributes
	 * @return \Aimeos\MShop\Common\Helper\Form\Iface|null Form object with URL, parameters, etc.
	 * 	or null if no form data is required
	 */
	public function process( \Aimeos\MShop\Order\Item\Iface $orderItem, $id, array $urls, array $params );

	/**
	 * Returns the services filtered by the previously assigned conditions
	 *
	 * @param integer &$total Parameter where the total number of found services will be stored in
	 * @return \Aimeos\MShop\Service\Item\Iface[] Ordered list of service items
	 * @since 2019.04
	 */
	public function search( &$total = null );

	/**
	 * Sets the start value and the number of returned services for slicing the list of found services
	 *
	 * @param integer $start Start value of the first attribute in the list
	 * @param integer $limit Number of returned services
	 * @return \Aimeos\Controller\Frontend\Service\Iface Service controller for fluent interface
	 * @since 2019.04
	 */
	public function slice( $start, $limit );

	/**
	 * Sets the sorting of the result list
	 *
	 * @param string|null $key Sorting of the result list like "position", null for no sorting
	 * @return \Aimeos\Controller\Frontend\Service\Iface Service controller for fluent interface
	 * @since 2019.04
	 */
	public function sort( $key = null );

	/**
	 * Adds attribute types for filtering
	 *
	 * @param array|string $code Service type or list of types
	 * @return \Aimeos\Controller\Frontend\Service\Iface Service controller for fluent interface
	 * @since 2019.04
	 */
	public function type( $code );

	/**
	 * Updates the order status sent by payment gateway notifications
	 *
	 * @param ServerRequestInterface $request Request object
	 * @param ResponseInterface $response Response object that will contain HTTP status and response body
	 * @param string $code Unique code of the service used for the current order
	 * @return \Psr\Http\Message\ResponseInterface Response object
	 */
	public function updatePush( ServerRequestInterface $request, ResponseInterface $response, $code );

	/**
	 * Updates the payment or delivery status for the given request
	 *
	 * @param ServerRequestInterface $request Request object with parameters and request body
	 * @param string $code Unique code of the service used for the current order
	 * @param string $orderid ID of the order whose payment status should be updated
	 * @return \Aimeos\MShop\Order\Item\Iface $orderItem Order item that has been updated
	 */
	public function updateSync( ServerRequestInterface $request, $code, $orderid );

	/**
	 * Sets the referenced domains that will be fetched too when retrieving items
	 *
	 * @param array $domains Domain names of the referenced items that should be fetched too
	 * @return \Aimeos\Controller\Frontend\Service\Iface Service controller for fluent interface
	 * @since 2019.04
	 */
	public function uses( array $domains );
}
