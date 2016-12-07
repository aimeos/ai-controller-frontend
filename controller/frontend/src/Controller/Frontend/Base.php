<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Metaways Infosystems GmbH, 2012
 * @copyright Aimeos (aimeos.org), 2015-2016
 * @package Controller
 * @subpackage Frontend
 */


namespace Aimeos\Controller\Frontend;


/**
 * Common methods for frontend controller classes.
 *
 * @package Controller
 * @subpackage Frontend
 */
abstract class Base
{
	private $context = null;


	/**
	 * Common initialization for controller classes.
	 *
	 * @param \Aimeos\MShop\Context\Item\Iface $context Common MShop context object
	 */
	public function __construct( \Aimeos\MShop\Context\Item\Iface $context )
	{
		$this->context = $context;
	}


	/**
	 * Catches unknown methods
	 *
	 * @param string $name Name of the method
	 * @param array $param List of method parameter
	 * @return boolean Returns always false
	 */
	public function __call( $name, array $param )
	{
		return false;
	}


	/**
	 * Returns the context object.
	 *
	 * @return \Aimeos\MShop\Context\Item\Iface context object implementing \Aimeos\MShop\Context\Item\Iface
	 */
	protected function getContext()
	{
		return $this->context;
	}
}
