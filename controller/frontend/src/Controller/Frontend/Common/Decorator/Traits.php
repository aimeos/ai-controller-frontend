<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2021
 * @package Controller
 * @subpackage Frontend
 */


namespace Aimeos\Controller\Frontend\Common\Decorator;


/**
 * Decorator trait class for controllers
 *
 * @package Controller
 * @subpackage Frontend
 */
trait Traits
{
	/**
	 * Adds the given compare, combine or sort expression to the list of expressions
	 *
	 * @param \Aimeos\MW\Criteria\Expression\Iface|null $expr Compare, combine or sort expression
	 * @return \Aimeos\Controller\Frontend\Iface Controller object for chaining method calls
	 */
	public function addExpression( \Aimeos\MW\Criteria\Expression\Iface $expr = null ) : \Aimeos\Controller\Frontend\Iface
	{
		$this->getController()->addExpression( $expr );
		return $this;
	}


	/**
	 * Returns the compare and combine expressions added by addExpression()
	 *
	 * @return array List of compare and combine expressions
	 */
	public function getConditions() : array
	{
		$this->getController()->getConditions();
	}


	/**
	 * Returns the compare and combine expressions added by addExpression()
	 *
	 * @return array List of sort expressions
	 */
	public function getSortations() : array
	{
		$this->getController()->getSortations();
	}


	/**
	 * Returns the frontend controller
	 *
	 * @return \Aimeos\Controller\Frontend\Iface Frontend controller object
	 */
	abstract protected function getController() : \Aimeos\Controller\Frontend\Iface;
}
