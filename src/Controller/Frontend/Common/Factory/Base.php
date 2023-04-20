<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Metaways Infosystems GmbH, 2012
 * @copyright Aimeos (aimeos.org), 2015-2023
 * @package Controller
 * @subpackage Frontend
 */


namespace Aimeos\Controller\Frontend\Common\Factory;


/**
 * Common methods for all factories.
 *
 * @package Controller
 * @subpackage Frontend
 * @deprecated 2023.01
 */
class Base
{
	private static $objects = [];


	/**
	 * Injects a controller object.
	 * The object is returned via create() if an instance of the class
	 * with the name name is requested.
	 *
	 * @param string $classname Full name of the class for which the object should be returned
	 * @param \Aimeos\Controller\Frontend\Iface|null $controller Frontend controller object
	 */
	public static function injectController( string $classname, \Aimeos\Controller\Frontend\Iface $controller = null )
	{
		self::$objects[$classname] = $controller;
	}


	/**
	 * Adds the decorators to the controller object.
	 *
	 * @param \Aimeos\MShop\ContextIface $context Context instance with necessary objects
	 * @param \Aimeos\Controller\Frontend\Common\Iface $controller Controller object
	 * @param array $decorators List of decorator names that should be wrapped around the controller object
	 * @param string $classprefix Decorator class prefix, e.g. "\Aimeos\Controller\Frontend\Basket\Decorator\"
	 * @return \Aimeos\Controller\Frontend\Iface Controller object
	 * @throws \LogicException If class can't be instantiated
	 */
	protected static function addDecorators( \Aimeos\MShop\ContextIface $context,
		\Aimeos\Controller\Frontend\Iface $controller, array $decorators, string $classprefix ) : \Aimeos\Controller\Frontend\Iface
	{
		$interface = \Aimeos\Controller\Frontend\Common\Decorator\Iface::class;

		foreach( $decorators as $name )
		{
			if( ctype_alnum( $name ) === false ) {
				throw new \LogicException( sprintf( 'Invalid characters in class name "%1$s"', $name ), 400 );
			}

			$controller = \Aimeos\Utils::create( $classprefix . $name, [$controller, $context], $interface );
		}

		return $controller;
	}


	/**
	 * Adds the decorators to the controller object.
	 *
	 * @param \Aimeos\MShop\ContextIface $context Context instance with necessary objects
	 * @param \Aimeos\Controller\Frontend\Common\Iface $controller Controller object
	 * @param string $domain Domain name in lower case, e.g. "product"
	 * @return \Aimeos\Controller\Frontend\Iface Controller object
	 */
	protected static function addControllerDecorators( \Aimeos\MShop\ContextIface $context,
		\Aimeos\Controller\Frontend\Iface $controller, string $domain ) : \Aimeos\Controller\Frontend\Iface
	{
		if( !is_string( $domain ) || $domain === '' ) {
			throw new \Aimeos\Controller\Frontend\Exception( sprintf( 'Invalid domain "%1$s"', $domain ) );
		}

		$localClass = str_replace( '/', '\\', ucwords( $domain, '/' ) );
		$config = $context->config();

		$decorators = $config->get( 'controller/frontend/common/decorators/default', [] );
		$excludes = $config->get( 'controller/frontend/' . $domain . '/decorators/excludes', [] );

		foreach( $decorators as $key => $name )
		{
			if( in_array( $name, $excludes ) ) {
				unset( $decorators[$key] );
			}
		}

		$classprefix = '\\Aimeos\\Controller\\Frontend\\Common\\Decorator\\';
		$controller = self::addDecorators( $context, $controller, $decorators, $classprefix );

		$classprefix = '\\Aimeos\\Controller\\Frontend\\Common\\Decorator\\';
		$decorators = $config->get( 'controller/frontend/' . $domain . '/decorators/global', [] );
		$controller = self::addDecorators( $context, $controller, $decorators, $classprefix );

		$classprefix = '\\Aimeos\\Controller\\Frontend\\' . ucfirst( $localClass ) . '\\Decorator\\';
		$decorators = $config->get( 'controller/frontend/' . $domain . '/decorators/local', [] );
		$controller = self::addDecorators( $context, $controller, $decorators, $classprefix );

		return $controller->setObject( $controller );
	}


	/**
	 * Creates a controller object.
	 *
	 * @param \Aimeos\MShop\ContextIface $context Context instance with necessary objects
	 * @param string $classname Name of the controller class
	 * @param string $interface Name of the controller interface
	 * @return \Aimeos\Controller\Frontend\Iface Controller object
	 */
	protected static function createController( \Aimeos\MShop\ContextIface $context,
		string $classname, string $interface ) : \Aimeos\Controller\Frontend\Iface
	{
		if( isset( self::$objects[$classname] ) ) {
			return self::$objects[$classname];
		}

		return \Aimeos\Utils::create( $classname, [$context], $interface );
	}

}
