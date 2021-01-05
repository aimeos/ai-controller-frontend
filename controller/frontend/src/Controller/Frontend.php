<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2015-2021
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
	 * @param \Aimeos\MShop\Context\Item\Iface $context Context object required by managers
	 * @param string $path Name of the domain (and sub-managers) separated by slashes, e.g "basket"
	 * @return \Aimeos\Controller\Frontend\Iface New frontend controller
	 * @throws \Aimeos\Controller\Frontend\Exception If the given path is invalid or the manager wasn't found
	 */
	public static function create( \Aimeos\MShop\Context\Item\Iface $context, string $path )
	{
		if( empty( $path ) ) {
			throw new \Aimeos\Controller\Frontend\Exception( sprintf( 'Controller path is empty' ) );
		}

		if( self::$cache === false || !isset( self::$objects[$path] ) )
		{
			if( ctype_alnum( $path ) === false ) {
				throw new \Aimeos\Controller\Frontend\Exception( sprintf( 'Invalid characters in controller name "%1$s"', $path ) );
			}

			$factory = '\\Aimeos\\Controller\\Frontend\\' . ucfirst( $path ) . '\\Factory';

			if( class_exists( $factory ) === false ) {
				throw new \Aimeos\Controller\Frontend\Exception( sprintf( 'Class "%1$s" not available', $factory ) );
			}

			if( ( $controller = call_user_func_array( [$factory, 'create'], [$context] ) ) === false ) {
				throw new \Aimeos\Controller\Frontend\Exception( sprintf( 'Invalid factory "%1$s"', $factory ) );
			}

			self::$objects[$path] = $controller;
		}

		return clone self::$objects[$path];
	}


	/**
	 * Injects a manager object for the given path of manager names
	 *
	 * This method is for testing only and you must call \Aimeos\MShop::cache( false )
	 * afterwards!
	 *
	 * @param string $path Name of the domain (and sub-controllers) separated by slashes, e.g "product"
	 * @param \Aimeos\Controller\Frontend\Iface|null $object Frontend controller object for the given name or null to clear
	 */
	public static function inject( string $path, \Aimeos\Controller\Frontend\Iface $object = null )
	{
		self::$objects[$path] = $object;
	}
}
