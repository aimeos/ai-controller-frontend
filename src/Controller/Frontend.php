<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2015-2022
 * @package Controller
 * @subpackage Frontend
 */


namespace Aimeos\Controller;


/**
 * Factory which can create all Frontend controllers
 *
 * @package Controller
 * @subpackage Frontend
 */
class Frontend
{
	private static $cache = true;
	private static $objects = [];


	/**
	 * Enables or disables caching of class instances
	 *
	 * @param bool $value True to enable caching, false to disable it.
	 */
	public static function cache( bool $value )
	{
		self::$cache = (boolean) $value;
		self::$objects = [];
	}


	/**
	 * Creates the required controller specified by the given path of controller names
	 *
	 * Controllers are created by providing only the domain name, e.g.
	 * "basket" for the \Aimeos\Controller\Frontend\Basket\Standard or a path of names to
	 * retrieve a specific sub-controller if available.
	 * Please note, that only the default controllers can be created. If you need
	 * a specific implementation, you need to use the factory class of the
	 * controller to hand over specifc implementation names.
	 *
	 * @param \Aimeos\MShop\ContextIface $context Context object required by managers
	 * @param string $path Name of the domain (and sub-managers) separated by slashes, e.g "basket"
	 * @return \Aimeos\Controller\Frontend\Iface New frontend controller
	 * @throws \Aimeos\Controller\Frontend\Exception If the given path is invalid or the manager wasn't found
	 */
	public static function create( \Aimeos\MShop\ContextIface $context, string $path )
	{
		if( empty( $path ) ) {
			throw new \Aimeos\Controller\Frontend\Exception( 'Controller path is empty', 400 );
		}

		if( empty( $name ) ) {
			$name = $context->config()->get( 'controller/frontend/' . $path . '/name', 'Standard' );
		}

		$iface = '\\Aimeos\\Controller\\Frontend\\' . ucfirst( $path ) . '\\Iface';
		$classname = '\\Aimeos\\Controller\\Frontend\\' . ucfirst( $path ) . '\\' . $name;

		if( self::$cache === false || !isset( self::$objects[$classname] ) )
		{
			if( class_exists( $classname ) === false ) {
				throw new \Aimeos\Controller\Frontend\Exception( sprintf( 'Class "%1$s" not found', $classname, 404 ) );
			}

			$cntl = self::createController( $context, $classname, $iface );
			$cntl = self::addControllerDecorators( $context, $cntl, $path );

			self::$objects[$classname] = $cntl;
		}

		return clone self::$objects[$classname];
	}


	/**
	 * Injects a manager object for the given path of manager names
	 *
	 * This method is for testing only and you must call \Aimeos\MShop::cache( false )
	 * afterwards!
	 *
	 * @param string $classname Full name of the class for which the object should be returned
	 * @param \Aimeos\Controller\Frontend\Iface|null $object Frontend controller object for the given name or null to clear
	 */
	public static function inject( string $classname, \Aimeos\Controller\Frontend\Iface $object = null )
	{
		self::$objects['\\' . ltrim( $classname, '\\' )] = $object;
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
		$localClass = str_replace( '/', '\\', ucwords( $domain, '/' ) );
		$config = $context->config();

		/** controller/frontend/common/decorators/default
		 * Configures the list of decorators applied to all frontend controllers
		 *
		 * Decorators extend the functionality of a class by adding new aspects
		 * (e.g. log what is currently done), executing the methods of the underlying
		 * class only in certain conditions (e.g. only for logged in users) or
		 * modify what is returned to the caller.
		 *
		 * This option allows you to configure a list of decorator names that should
		 * be wrapped around the original instance of all created controllers:
		 *
		 *  controller/frontend/common/decorators/default = array( 'decorator1', 'decorator2' )
		 *
		 * This would wrap the decorators named "decorator1" and "decorator2" around
		 * all controller instances in that order. The decorator classes would be
		 * "\Aimeos\Controller\Frontend\Common\Decorator\Decorator1" and
		 * "\Aimeos\Controller\Frontend\Common\Decorator\Decorator2".
		 *
		 * @param array List of decorator names
		 * @since 2014.03
		 * @category Developer
		 */
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
	 * Adds the decorators to the controller object.
	 *
	 * @param \Aimeos\MShop\ContextIface $context Context instance with necessary objects
	 * @param \Aimeos\Controller\Frontend\Common\Iface $controller Controller object
	 * @param array $decorators List of decorator names that should be wrapped around the controller object
	 * @param string $classprefix Decorator class prefix, e.g. "\Aimeos\Controller\Frontend\Basket\Decorator\"
	 * @return \Aimeos\Controller\Frontend\Iface Controller object
	 */
	protected static function addDecorators( \Aimeos\MShop\ContextIface $context, \Aimeos\Controller\Frontend\Iface $controller,
		array $decorators, string $classprefix ) : \Aimeos\Controller\Frontend\Iface
	{
		foreach( $decorators as $name )
		{
			if( ctype_alnum( $name ) === false )
			{
				$classname = is_string( $name ) ? $classprefix . $name : '<not a string>';
				throw new \Aimeos\Controller\Frontend\Exception( sprintf( 'Invalid class name "%1$s"', $classname ), 400 );
			}

			$classname = $classprefix . $name;

			if( class_exists( $classname ) === false ) {
				throw new \Aimeos\Controller\Frontend\Exception( sprintf( 'Class "%1$s" not found', $classname ), 404 );
			}

			$interface = '\\Aimeos\\Controller\\Frontend\\Common\\Decorator\\Iface';
			$controller = new $classname( $controller, $context );

			if( !( $controller instanceof $interface ) )
			{
				$msg = sprintf( 'Class "%1$s" does not implement "%2$s"', $classname, $interface );
				throw new \Aimeos\Controller\Frontend\Exception( $msg, 400 );
			}
		}

		return $controller;
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

		if( class_exists( $classname ) === false ) {
			throw new \Aimeos\Controller\Frontend\Exception( sprintf( 'Class "%1$s" not found', $classname ), 404 );
		}

		$controller = new $classname( $context );

		if( !( $controller instanceof $interface ) )
		{
			$msg = sprintf( 'Class "%1$s" does not implement "%2$s"', $classname, $interface );
			throw new \Aimeos\Controller\Frontend\Exception( $msg, 400 );
		}

		return $controller;
	}
}
