<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Metaways Infosystems GmbH, 2012
 * @copyright Aimeos (aimeos.org), 2015-2016
 * @package Controller
 * @subpackage Frontend
 */


namespace Aimeos\Controller\Frontend\Common\Decorator;


/**
 * Example decorator for frontend controllers
 *
 * @package Controller
 * @subpackage Frontend
 */
class Example
	extends \Aimeos\Controller\Frontend\Common\Decorator\Base
	implements \Aimeos\Controller\Frontend\Common\Decorator\Iface
{
	public function getContext()
	{
		return parent::getContext();
	}


	public function getController()
	{
		return parent::getController();
	}
}
