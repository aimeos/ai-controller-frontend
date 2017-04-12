<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Metaways Infosystems GmbH, 2012
 * @copyright Aimeos (aimeos.org), 2015-2017
 * @package Controller
 * @subpackage Frontend
 */


namespace Aimeos\Controller\Frontend\Service;

use \Psr\Http\Message\ServerRequestInterface;
use \Psr\Http\Message\ResponseInterface;


/**
 * Default implementation of the service frontend controller.
 *
 * @package Controller
 * @subpackage Frontend
 */
class Standard
	extends \Aimeos\Controller\Frontend\Base
	implements Iface, \Aimeos\Controller\Frontend\Common\Iface
{
	private $providers = [];


	/**
	 * Returns a list of attributes that are invalid
	 *
	 * @param string $serviceId Unique service ID
	 * @param string[] $attributes List of attribute codes as keys and strings entered by the customer as value
	 * @return string[] List of attributes codes as keys and error messages as values for invalid or missing values
	 */
	public function checkAttributes( $serviceId, array $attributes )
	{
		$manager = \Aimeos\MShop\Factory::createManager( $this->getContext(), 'service' );
		$provider = $manager->getProvider( $manager->getItem( $serviceId, [], true ) );

		return array_filter( $provider->checkConfigFE( $attributes ) );
	}


	/**
	 * Returns the service item for the given ID
	 *
	 * @param string $serviceId Unique service ID
	 * @param string[] $ref List of domain names whose items should be fetched too
	 * @return \Aimeos\MShop\Service\Provider\Iface Service provider object
	 */
	public function getProvider( $serviceId, $ref = ['media', 'price', 'text'] )
	{
		$manager = \Aimeos\MShop\Factory::createManager( $this->getContext(), 'service' );
		return $manager->getProvider( $manager->getItem( $serviceId, $ref, true ) );
	}


	/**
	 * Returns the service providers of the given type
	 *
	 * @param string|null $type Service type, e.g. "delivery" (shipping related), "payment" (payment related) or null for all
	 * @param string[] $ref List of domain names whose items should be fetched too
	 * @return \Aimeos\MShop\Service\Provider\Iface[] List of service IDs as keys and service provider objects as values
	 */
	public function getProviders( $type = null, $ref = ['media', 'price', 'text'] )
	{
		$list = [];
		$manager = \Aimeos\MShop\Factory::createManager( $this->getContext(), 'service' );

		$search = $manager->createSearch( true );
		$search->setSortations( array( $search->sort( '+', 'service.position' ) ) );

		if( $type != null )
		{
			$expr = array(
				$search->getConditions(),
				$search->compare( '==', 'service.type.code', $type ),
				$search->compare( '==', 'service.type.domain', 'service' ),
			);
			$search->setConditions( $search->combine( '&&', $expr ) );
		}

		foreach( $manager->searchItems( $search, $ref ) as $id => $item ) {
			$list[$id] = $manager->getProvider( $item );
		}

		return $list;
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
		$manager = \Aimeos\MShop\Factory::createManager( $this->getContext(), 'service' );

		$provider = $manager->getProvider( $manager->getItem( $serviceId, [], true ) );
		$provider->injectGlobalConfigBE( $urls );

		return $provider->process( $orderItem, $params );
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
		$params = (array) $request->getAttributes() + (array) $request->getParsedBody() + (array) $request->getQueryParams();

		if( !isset( $params['code'] ) ) {
			return;
		}

		$context = $this->getContext();
		$manager = \Aimeos\MShop\Factory::createManager( $context, 'service' );

		$provider = $manager->getProvider( $manager->findItem( $params['code'] ) );
		$provider->injectGlobalConfigBE( $urls );

		$body = (string) $request->getBody();
		$response = null;
		$headers = [];

		if( ( $orderItem = $provider->updateSync( $params, $body, $response, $headers ) ) !== null )
		{
			if( $orderItem->getPaymentStatus() === \Aimeos\MShop\Order\Item\Base::PAY_UNFINISHED
				&& $provider->isImplemented( \Aimeos\MShop\Service\Provider\Payment\Base::FEAT_QUERY )
			) {
				$provider->query( $orderItem );
			}

			// update stock, coupons, etc.
			\Aimeos\Controller\Frontend\Factory::createController( $context, 'order' )->update( $orderItem );
		}

		return $orderItem;
	}


	/**
	 * Returns the service items that are available for the service type and the content of the basket.
	 *
	 * @param string $type Service type, e.g. "delivery" (shipping related) or "payment" (payment related)
	 * @param \Aimeos\MShop\Order\Item\Base\Iface $basket Basket of the user
	 * @param array $ref List of domains for which the items referenced by the services should be fetched too
	 * @return array List of service items implementing \Aimeos\MShop\Service\Item\Iface with referenced items
	 * @throws \Exception If an error occurs
	 * @deprecated Use getProviders() instead
	 */
	public function getServices( $type, \Aimeos\MShop\Order\Item\Base\Iface $basket, $ref = ['media', 'price', 'text'] )
	{
		$serviceManager = \Aimeos\MShop\Factory::createManager( $this->getContext(), 'service' );

		$search = $serviceManager->createSearch( true );
		$expr = array(
			$search->getConditions(),
			$search->compare( '==', 'service.type.domain', 'service' ),
			$search->compare( '==', 'service.type.code', $type ),
		);
		$search->setConditions( $search->combine( '&&', $expr ) );
		$search->setSortations( array( $search->sort( '+', 'service.position' ) ) );

		$items = $serviceManager->searchItems( $search, $ref );


		foreach( $items as $id => $service )
		{
			try
			{
				$provider = $serviceManager->getProvider( $service );

				if( $provider->isAvailable( $basket ) ) {
					$this->providers[$type][$id] = $provider;
				} else {
					unset( $items[$id] );
				}
			}
			catch( \Aimeos\MShop\Service\Exception $e )
			{
				$msg = sprintf( 'Unable to create provider "%1$s" for service with ID "%2$s"', $service->getCode(), $id );
				$this->getContext()->getLogger()->log( $msg, \Aimeos\MW\Logger\Base::WARN );
			}
		}

		return $items;
	}


	/**
	 * Returns the list of attribute definitions which must be used to render the input form where the customer can
	 * enter or chose the required data necessary by the service provider.
	 *
	 * @param string $type Service type, e.g. "delivery" (shipping related) or "payment" (payment related)
	 * @param string $serviceId Identifier of one of the service option returned by getService()
	 * @param \Aimeos\MShop\Order\Item\Base\Iface $basket Basket object
	 * @return array List of attribute definitions implementing \Aimeos\MW\Criteria\Attribute\Iface
	 * @throws \Aimeos\Controller\Frontend\Service\Exception If no active service provider for this ID is available
	 * @throws \Aimeos\MShop\Exception If service provider isn't available
	 * @throws \Exception If an error occurs
	 * @deprecated Use getProvider() instead
	 */
	public function getServiceAttributes( $type, $serviceId, \Aimeos\MShop\Order\Item\Base\Iface $basket )
	{
		if( isset( $this->providers[$type][$serviceId] ) ) {
			return $this->providers[$type][$serviceId]->getConfigFE( $basket );
		}

		$serviceManager = \Aimeos\MShop\Factory::createManager( $this->getContext(), 'service' );
		$item = $serviceManager->getItem( $serviceId, ['price'], true );

		return $serviceManager->getProvider( $item )->getConfigFE( $basket );
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
		if( isset( $this->providers[$type][$serviceId] ) ) {
			return $this->providers[$type][$serviceId]->calcPrice( $basket );
		}

		$serviceManager = \Aimeos\MShop\Factory::createManager( $this->getContext(), 'service' );
		$item = $serviceManager->getItem( $serviceId, ['price'], true );

		return $serviceManager->getProvider( $item )->calcPrice( $basket );
	}


	/**
	 * Returns a list of attributes that are invalid.
	 *
	 * @param string $type Service type, e.g. "delivery" (shipping related) or "payment" (payment related)
	 * @param string $serviceId Identifier of the service option chosen by the customer
	 * @param array $attributes List of key/value pairs with name of the attribute from attribute definition object as
	 * 	key and the string entered by the customer as value
	 * @return array An array with the attribute keys as key and an error message as values for all attributes that are
	 * 	known by the provider but aren't valid resp. null for attributes whose values are OK
	 * @throws \Aimeos\Controller\Frontend\Service\Exception If no active service provider for this ID is available
	 * @deprecated Use checkAttributes() instead
	 */
	public function checkServiceAttributes( $type, $serviceId, array $attributes )
	{
		if( !isset( $this->providers[$type][$serviceId] ) )
		{
			$serviceManager = \Aimeos\MShop\Factory::createManager( $this->getContext(), 'service' );
			$item = $serviceManager->getItem( $serviceId, ['price'], true );

			$this->providers[$type][$serviceId] = $serviceManager->getProvider( $item );
		}

		$errors = $this->providers[$type][$serviceId]->checkConfigFE( $attributes );

		foreach( $errors as $key => $msg )
		{
			if( $msg === null ) {
				unset( $errors[$key] );
			}
		}

		return $errors;
	}
}
