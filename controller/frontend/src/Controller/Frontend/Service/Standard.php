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

		$item = $manager->getItem( $serviceId, [], true );
		$provider = $manager->getProvider( $item, $item->getType() );

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
		$item = $manager->getItem( $serviceId, $ref, true );

		return $manager->getProvider( $item, $item->getType() );
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
		$search->setSortations( [$search->sort( '+', 'service.type.position' ), $search->sort( '+', 'service.position' )] );

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
			$list[$id] = $manager->getProvider( $item, $item->getType() );
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
		$item = $manager->getItem( $serviceId, [], true );

		$provider = $manager->getProvider( $item, $item->getType() );
		$provider->injectGlobalConfigBE( $urls );

		return $provider->process( $orderItem, $params );
	}


	/**
	 * Updates the order status sent by payment gateway notifications
	 *
	 * @param ServerRequestInterface $request Request object
	 * @param ResponseInterface $response Response object that will contain HTTP status and response body
	 * @param string $code Unique code of the service used for the current order
	 * @return \Psr\Http\Message\ResponseInterface Response object
	 */
	public function updatePush( ServerRequestInterface $request, ResponseInterface $response, $code )
	{
		$manager = \Aimeos\MShop\Factory::createManager( $this->getContext(), 'service' );
		$item = $manager->findItem( $code );

		$provider = $manager->getProvider( $item, $item->getType() );

		return $provider->updatePush( $request, $response );
	}


	/**
	 * Updates the payment or delivery status for the given request
	 *
	 * @param ServerRequestInterface $request Request object with parameters and request body
	 * @param string $code Unique code of the service used for the current order
	 * @param string $orderid ID of the order whose payment status should be updated
	 * @return \Aimeos\MShop\Order\Item\Iface $orderItem Order item that has been updated
	 */
	public function updateSync( ServerRequestInterface $request, $code, $orderid )
	{
		$context = $this->getContext();
		$orderManager = \Aimeos\MShop\Factory::createManager( $context, 'order' );
		$serviceManager = \Aimeos\MShop\Factory::createManager( $context, 'service' );

		$orderItem = $orderManager->getItem( $orderid );
		$serviceItem = $serviceManager->findItem( $code );

		$provider = $serviceManager->getProvider( $serviceItem, $serviceItem->getType() );


		if( ( $orderItem = $provider->updateSync( $request, $orderItem ) ) !== null )
		{
			if( $orderItem->getPaymentStatus() === \Aimeos\MShop\Order\Item\Base::PAY_UNFINISHED
				&& $provider->isImplemented( \Aimeos\MShop\Service\Provider\Payment\Base::FEAT_QUERY )
			) {
				$provider->query( $orderItem );
			}
		}

		return $orderItem;
	}
}
