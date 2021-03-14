<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Metaways Infosystems GmbH, 2012
 * @copyright Aimeos (aimeos.org), 2015-2021
 * @package Controller
 * @subpackage Frontend
 */


namespace Aimeos\Controller\Frontend;


/**
 * Common interface for controller
 *
 * @package Controller
 * @subpackage Frontend
 */
interface Iface
{
	/**
	 * Adds the given compare, combine or sort expression to the list of expressions
	 *
	 * @param \Aimeos\MW\Criteria\Expression\Iface|null $expr Compare, combine or sort expression
	 * @return \Aimeos\Controller\Frontend\Iface Controller object for chaining method calls
	 */
	public function addExpression( \Aimeos\MW\Criteria\Expression\Iface $expr = null ) : Iface;

	/**
	 * Returns the compare and combine expressions added by addExpression()
	 *
	 * @return array List of compare and combine expressions
	 */
	public function getConditions() : array;

	/**
	 * Returns the compare and combine expressions added by addExpression()
	 *
	 * @return array List of sort expressions
	 */
	public function getSortations() : array;

	/**
	 * Injects the reference of the outmost object
	 *
	 * @param \Aimeos\Controller\Frontend\Iface $object Reference to the outmost controller or decorator
	 * @return \Aimeos\Controller\Frontend\Iface Controller object for chaining method calls
	 */
	public function setObject( Iface $object ) : Iface;
}
