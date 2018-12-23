<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Metaways Infosystems GmbH, 2013
 * @copyright Aimeos (aimeos.org), 2015-2018
 * @package MShop
 */


namespace Aimeos\Controller\Frontend;


/**
 * Factory which can create all Frontend controllers.
 *
 * @package \Aimeos\Controller\Frontend
 */
class Factory extends \Aimeos\Controller\Frontend
{
	/**
	 * Creates the required controller specified by the given path of controller names.
	 *
	 * @param \Aimeos\MShop\Context\Item\Iface $context Context object required by managers
	 * @param string $path Name of the domain (and sub-managers) separated by slashes, e.g "basket"
	 * @return \Aimeos\Controller\Frontend\Iface New frontend controller
	 * @throws \Aimeos\Controller\Frontend\Exception If the given path is invalid or the manager wasn't found
	 */
	static public function createController( \Aimeos\MShop\Context\Item\Iface $context, $path )
	{
		return self::create( $context, $path );
	}


	/**
	 * Enables or disables caching of class instances.
	 *
	 * @param boolean $value True to enable caching, false to disable it.
	 * @return boolean Previous cache setting
	 */
	static public function setCache( $value )
	{
		return self::cache( $value );
	}
}
