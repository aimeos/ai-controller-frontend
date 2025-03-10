<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Metaways Infosystems GmbH, 2012
 * @copyright Aimeos (aimeos.org), 2015-2025
 * @package Controller
 * @subpackage Frontend
 */


namespace Aimeos\Controller\Frontend\Common;


/**
 * Frontend controller interface.
 *
 * @package Controller
 * @subpackage Frontend
 */
interface Iface
	extends \Aimeos\Controller\Frontend\Iface
{
	/**
	 * Initializes the controller.
	 *
	 * @param \Aimeos\MShop\ContextIface $context MShop context object
	 */
	public function __construct( \Aimeos\MShop\ContextIface $context );

}
