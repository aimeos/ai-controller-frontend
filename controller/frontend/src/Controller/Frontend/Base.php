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
 * Common methods for frontend controller classes.
 *
 * @package Controller
 * @subpackage Frontend
 */
abstract class Base
{
	private $object;
	private $context;
	private $cond = [];
	private $sort = [];


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
	 * Catch unknown methods
	 *
	 * @param string $name Name of the method
	 * @param array $param List of method parameter
	 * @throws \Aimeos\Controller\Frontend\Exception If method call failed
	 */
	public function __call( string $name, array $param )
	{
		throw new \Aimeos\Controller\Frontend\Exception( sprintf( 'Unable to call method "%1$s"', $name ) );
	}


	/**
	 * Adds the given compare, combine or sort expression to the list of expressions
	 *
	 * @param \Aimeos\MW\Criteria\Expression\Iface $expr Compare, combine or sort expression
	 * @return \Aimeos\Controller\Frontend\Iface Controller object for chaining method calls
	 */
	public function addExpression( \Aimeos\MW\Criteria\Expression\Iface $expr ) : Iface
	{
		if( $expr instanceof \Aimeos\MW\Criteria\Expression\Sort\Iface ) {
			$this->sort[] = $expr;
		} else {
			$this->cond[] = $expr;
		}

		return $this;
	}


	/**
	 * Returns the compare and combine expressions added by addExpression()
	 *
	 * @return array List of compare and combine expressions
	 */
	public function getConditions() : array
	{
		return $this->cond;
	}


	/**
	 * Returns the compare and combine expressions added by addExpression()
	 *
	 * @return array List of sort expressions
	 */
	public function getSortations() : array
	{
		return $this->sort;
	}


	/**
	 * Returns the context object.
	 *
	 * @return \Aimeos\MShop\Context\Item\Iface Context object implementing \Aimeos\MShop\Context\Item\Iface
	 */
	protected function getContext() : \Aimeos\MShop\Context\Item\Iface
	{
		return $this->context;
	}


	/**
	 * Returns the outmost decorator of the decorator stack
	 *
	 * @return \Aimeos\Controller\Frontend\Iface Outmost decorator object
	 */
	protected function getObject() : Iface
	{
		if( $this->object !== null ) {
			return $this->object;
		}

		return $this;
	}


	/**
	 * Injects the reference of the outmost object
	 *
	 * @param \Aimeos\Controller\Frontend\Iface $object Reference to the outmost controller or decorator
	 * @return \Aimeos\Controller\Frontend\Iface Controller object for chaining method calls
	 */
	public function setObject( \Aimeos\Controller\Frontend\Iface $object ) : Iface
	{
		$this->object = $object;
		return $this;
	}
}
