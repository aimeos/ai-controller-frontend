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
 * Controller factory interface.
 *
 * @package Controller
 * @subpackage Frontend
 * @deprecated 2023.01
 */
interface Iface
{
	/**
	 * Creates a new controller based on the name.
	 *
	 * @param \Aimeos\MShop\ContextIface $context MShop context object
	 * @param string|null $name Name of the controller implementation (Default if null)
	 * @return \Aimeos\Controller\Frontend\Iface Controller object
	 */
	public static function create( \Aimeos\MShop\ContextIface $context, string $name = null ) : \Aimeos\Controller\Frontend\Iface;
}
