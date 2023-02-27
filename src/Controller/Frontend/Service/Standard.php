<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Metaways Infosystems GmbH, 2012
 * @copyright Aimeos (aimeos.org), 2015-2023
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
	/** controller/frontend/service/name
	 * Class name of the used service frontend controller implementation
	 *
	 * Each default frontend controller can be replace by an alternative imlementation.
	 * To use this implementation, you have to set the last part of the class
	 * name as configuration value so the controller factory knows which class it
	 * has to instantiate.
	 *
	 * For example, if the name of the default class is
	 *
	 *  \Aimeos\Controller\Frontend\Service\Standard
	 *
	 * and you want to replace it with your own version named
	 *
	 *  \Aimeos\Controller\Frontend\Service\Myservice
	 *
	 * then you have to set the this configuration option:
	 *
	 *  controller/jobs/frontend/service/name = Myservice
	 *
	 * The value is the last part of your own class name and it's case sensitive,
	 * so take care that the configuration value is exactly named like the last
	 * part of the class name.
	 *
	 * The allowed characters of the class name are A-Z, a-z and 0-9. No other
	 * characters are possible! You should always start the last part of the class
	 * name with an upper case character and continue only with lower case characters
	 * or numbers. Avoid chamel case names like "MyService"!
	 *
	 * @param string Last part of the class name
	 * @since 2014.03
	 * @category Developer
	 */

	/** controller/frontend/service/decorators/excludes
	 * Excludes decorators added by the "common" option from the service frontend controllers
	 *
	 * Decorators extend the functionality of a class by adding new aspects
	 * (e.g. log what is currently done), executing the methods of the underlying
	 * class only in certain conditions (e.g. only for logged in users) or
	 * modify what is returned to the caller.
	 *
	 * This option allows you to remove a decorator added via
	 * "controller/frontend/common/decorators/default" before they are wrapped
	 * around the frontend controller.
	 *
	 *  controller/frontend/service/decorators/excludes = array( 'decorator1' )
	 *
	 * This would remove the decorator named "decorator1" from the list of
	 * common decorators ("\Aimeos\Controller\Frontend\Common\Decorator\*") added via
	 * "controller/frontend/common/decorators/default" for the service frontend controller.
	 *
	 * @param array List of decorator names
	 * @since 2014.03
	 * @category Developer
	 * @see controller/frontend/common/decorators/default
	 * @see controller/frontend/service/decorators/global
	 * @see controller/frontend/service/decorators/local
	 */

	/** controller/frontend/service/decorators/global
	 * Adds a list of globally available decorators only to the service frontend controllers
	 *
	 * Decorators extend the functionality of a class by adding new aspects
	 * (e.g. log what is currently done), executing the methods of the underlying
	 * class only in certain conditions (e.g. only for logged in users) or
	 * modify what is returned to the caller.
	 *
	 * This option allows you to wrap global decorators
	 * ("\Aimeos\Controller\Frontend\Common\Decorator\*") around the frontend controller.
	 *
	 *  controller/frontend/service/decorators/global = array( 'decorator1' )
	 *
	 * This would add the decorator named "decorator1" defined by
	 * "\Aimeos\Controller\Frontend\Common\Decorator\Decorator1" only to the frontend controller.
	 *
	 * @param array List of decorator names
	 * @since 2014.03
	 * @category Developer
	 * @see controller/frontend/common/decorators/default
	 * @see controller/frontend/service/decorators/excludes
	 * @see controller/frontend/service/decorators/local
	 */

	/** controller/frontend/service/decorators/local
	 * Adds a list of local decorators only to the service frontend controllers
	 *
	 * Decorators extend the functionality of a class by adding new aspects
	 * (e.g. log what is currently done), executing the methods of the underlying
	 * class only in certain conditions (e.g. only for logged in users) or
	 * modify what is returned to the caller.
	 *
	 * This option allows you to wrap local decorators
	 * ("\Aimeos\Controller\Frontend\Service\Decorator\*") around the frontend controller.
	 *
	 *  controller/frontend/service/decorators/local = array( 'decorator2' )
	 *
	 * This would add the decorator named "decorator2" defined by
	 * "\Aimeos\Controller\Frontend\Catalog\Decorator\Decorator2" only to the frontend
	 * controller.
	 *
	 * @param array List of decorator names
	 * @since 2014.03
	 * @category Developer
	 * @see controller/frontend/common/decorators/default
	 * @see controller/frontend/service/decorators/excludes
	 * @see controller/frontend/service/decorators/global
	 */


	private array $domains = [];
	private \Aimeos\Base\Criteria\Iface $filter;
	private \Aimeos\MShop\Common\Manager\Iface $manager;


	/**
	 * Common initialization for controller classes
	 *
	 * @param \Aimeos\MShop\ContextIface $context Common MShop context object
	 */
	public function __construct( \Aimeos\MShop\ContextIface $context )
	{
		parent::__construct( $context );

		$this->manager = \Aimeos\MShop::create( $context, 'service' );
		$this->filter = $this->manager->filter( true );

		$this->addExpression( $this->filter->sort( '+', 'service.position' ) );
	}


	/**
	 * Clones objects in controller
	 */
	public function __clone()
	{
		$this->filter = clone $this->filter;
		parent::__clone();
	}


	/**
	 * Adds generic condition for filtering services
	 *
	 * @param string $operator Comparison operator, e.g. "==", "!=", "<", "<=", ">=", ">", "=~", "~="
	 * @param string $key Search key defined by the service manager, e.g. "service.status"
	 * @param array|string $value Value or list of values to compare to
	 * @return \Aimeos\Controller\Frontend\Service\Iface Service controller for fluent interface
	 * @since 2019.04
	 */
	public function compare( string $operator, string $key, $value ) : Iface
	{
		$this->addExpression( $this->filter->compare( $operator, $key, $value ) );
		return $this;
	}


	/**
	 * Returns the service for the given code
	 *
	 * @param string $code Unique service code
	 * @return \Aimeos\MShop\Service\Item\Iface Service item including the referenced domains items
	 * @since 2019.04
	 */
	public function find( string $code ) : \Aimeos\MShop\Service\Item\Iface
	{
		return $this->manager->find( $code, $this->domains, null, null, null );
	}


	/**
	 * Creates a search function string for the given name and parameters
	 *
	 * @param string $name Name of the search function without parenthesis, e.g. "service:has"
	 * @param array $params List of parameters for the search function with numeric keys starting at 0
	 * @return string Search function string that can be used in compare()
	 */
	public function function( string $name, array $params ) : string
	{
		return $this->filter->make( $name, $params );
	}


	/**
	 * Returns the service for the given ID
	 *
	 * @param string $id Unique service ID
	 * @return \Aimeos\MShop\Service\Item\Iface Service item including the referenced domains items
	 * @since 2019.04
	 */
	public function get( string $id ) : \Aimeos\MShop\Service\Item\Iface
	{
		return $this->manager->get( $id, $this->domains, null );
	}


	/**
	 * Returns the service item for the given ID
	 *
	 * @param string $serviceId Unique service ID
	 * @return \Aimeos\MShop\Service\Provider\Iface Service provider object
	 */
	public function getProvider( string $serviceId ) : \Aimeos\MShop\Service\Provider\Iface
	{
		$item = $this->manager->get( $serviceId, $this->domains, true );
		return $this->manager->getProvider( $item, $item->getType() );
	}


	/**
	 * Returns the service providers of the given type
	 *
	 * @return \Aimeos\Map List of service IDs as keys and service provider objects as values
	 */
	public function getProviders() : \Aimeos\Map
	{
		$list = [];
		$filter = clone $this->filter;

		$this->addExpression( $filter->getConditions() );
		$filter->setConditions( $filter->and( $this->getConditions() ) );

		foreach( $this->manager->search( $filter, $this->domains ) as $id => $item ) {
			$list[$id] = $this->manager->getProvider( $item, $item->getType() );
		}

		return map( $list );
	}


	/**
	 * Parses the given array and adds the conditions to the list of conditions
	 *
	 * @param array $conditions List of conditions, e.g. ['&&' => [['>' => ['service.status' => 0]], ['==' => ['service.type' => 'default']]]]
	 * @return \Aimeos\Controller\Frontend\Service\Iface Service controller for fluent interface
	 * @since 2019.04
	 */
	public function parse( array $conditions ) : Iface
	{
		if( ( $cond = $this->filter->parse( $conditions ) ) !== null ) {
			$this->addExpression( $cond );
		}

		return $this;
	}


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
		string $serviceId, array $urls, array $params ) : ?\Aimeos\MShop\Common\Helper\Form\Iface
	{
		$item = $this->manager->get( $serviceId, [], true );

		$provider = $this->manager->getProvider( $item, $item->getType() );
		$provider->injectGlobalConfigBE( $urls );

		return $provider->process( $orderItem, $params );
	}


	/**
	 * Returns the services filtered by the previously assigned conditions
	 *
	 * @param int &$total Parameter where the total number of found services will be stored in
	 * @return \Aimeos\Map Ordered list of service items implementing \Aimeos\MShop\Service\Item\Iface
	 * @since 2019.04
	 */
	public function search( int &$total = null ) : \Aimeos\Map
	{
		$filter = clone $this->filter;
		$this->addExpression( $filter->getConditions() );

		$filter->setSortations( $this->getSortations() );
		$filter->setConditions( $filter->and( $this->getConditions() ) );

		return $this->manager->search( $filter, $this->domains, $total );
	}


	/**
	 * Sets the start value and the number of returned services for slicing the list of found services
	 *
	 * @param int $start Start value of the first attribute in the list
	 * @param int $limit Number of returned services
	 * @return \Aimeos\Controller\Frontend\Service\Iface Service controller for fluent interface
	 * @since 2019.04
	 */
	public function slice( int $start, int $limit ) : Iface
	{
		$maxsize = $this->context()->config()->get( 'controller/frontend/common/max-size', 500 );
		$this->filter->slice( $start, min( $limit, $maxsize ) );
		return $this;
	}


	/**
	 * Sets the sorting of the result list
	 *
	 * @param string|null $key Sorting of the result list like "position", null for no sorting
	 * @return \Aimeos\Controller\Frontend\Service\Iface Service controller for fluent interface
	 * @since 2019.04
	 */
	public function sort( string $key = null ) : Iface
	{
		$list = $this->splitKeys( $key );

		foreach( $list as $sortkey )
		{
			$direction = ( $sortkey[0] === '-' ? '-' : '+' );
			$sortkey = ltrim( $sortkey, '+-' );

			switch( $sortkey )
			{
				case 'type':
					$this->addExpression( $this->filter->sort( $direction, 'service.type' ) ); break;
				default:
					$this->addExpression( $this->filter->sort( $direction, $sortkey ) );
			}
		}

		return $this;
	}


	/**
	 * Adds attribute types for filtering
	 *
	 * @param array|string $code Service type or list of types
	 * @return \Aimeos\Controller\Frontend\Service\Iface Service controller for fluent interface
	 * @since 2019.04
	 */
	public function type( $code ) : Iface
	{
		if( $code ) {
			$this->addExpression( $this->filter->compare( '==', 'service.type', $code ) );
		}

		return $this;
	}


	/**
	 * Updates the order status sent by payment gateway notifications
	 *
	 * @param ServerRequestInterface $request Request object
	 * @param ResponseInterface $response Response object that will contain HTTP status and response body
	 * @param string $code Unique code of the service used for the current order
	 * @return \Psr\Http\Message\ResponseInterface Response object
	 */
	public function updatePush( ServerRequestInterface $request, ResponseInterface $response,
		string $code ) : \Psr\Http\Message\ResponseInterface
	{
		$item = $this->manager->find( $code );
		$provider = $this->manager->getProvider( $item, $item->getType() );

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
	public function updateSync( ServerRequestInterface $request,
		string $code, string $orderid ) : \Aimeos\MShop\Order\Item\Iface
	{
		$ref = $this->context()->config()->get( 'mshop/order/manager/subdomains', [] );
		$orderItem = \Aimeos\MShop::create( $this->context(), 'order' )->get( $orderid, $ref );
		$serviceItem = $this->manager->find( $code );

		$provider = $this->manager->getProvider( $serviceItem, $serviceItem->getType() );


		if( ( $orderItem = $provider->updateSync( $request, $orderItem ) ) !== null )
		{
			if( $orderItem->getStatusPayment() === \Aimeos\MShop\Order\Item\Base::PAY_UNFINISHED
				&& $provider->isImplemented( \Aimeos\MShop\Service\Provider\Payment\Base::FEAT_QUERY )
			) {
				$provider->query( $orderItem );
			}
		}

		return $orderItem;
	}


	/**
	 * Sets the referenced domains that will be fetched too when retrieving items
	 *
	 * @param array $domains Domain names of the referenced items that should be fetched too
	 * @return \Aimeos\Controller\Frontend\Service\Iface Service controller for fluent interface
	 * @since 2019.04
	 */
	public function uses( array $domains ) : Iface
	{
		$this->domains = $domains;
		return $this;
	}


	/**
	 * Returns the manager used by the controller
	 *
	 * @return \Aimeos\MShop\Common\Manager\Iface Manager object
	 */
	protected function getManager() : \Aimeos\MShop\Common\Manager\Iface
	{
		return $this->manager;
	}
}
