<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Metaways Infosystems GmbH, 2012
 * @copyright Aimeos (aimeos.org), 2015-2023
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
	implements \Aimeos\Macro\Iface
{
	use \Aimeos\Macro\Macroable;


	private \Aimeos\MShop\ContextIface $context;
	private ?Iface $object = null;
	private array $cond = [];
	private array $sort = [];


	/**
	 * Common initialization for controller classes.
	 *
	 * @param \Aimeos\MShop\ContextIface $context Common MShop context object
	 */
	public function __construct( \Aimeos\MShop\ContextIface $context )
	{
		$this->context = $context;
	}


	/**
	 * Clones objects in controller
	 */
	public function __clone()
	{
		foreach( $this->cond as $key => $cond )
		{
			if( is_object( $cond ) ) {
				$this->cond[$key] = clone $cond;
			}
		}

		foreach( $this->sort as $key => $sort )
		{
			if( is_object( $sort ) ) {
				$this->sort[$key] = clone $sort;
			}
		}
	}


	/**
	 * Adds the given compare, combine or sort expression to the list of expressions
	 *
	 * @param \Aimeos\Base\Criteria\Expression\Iface|null $expr Compare, combine or sort expression
	 * @return \Aimeos\Controller\Frontend\Iface Controller object for chaining method calls
	 */
	public function addExpression( \Aimeos\Base\Criteria\Expression\Iface $expr = null ) : Iface
	{
		if( $expr instanceof \Aimeos\Base\Criteria\Expression\Sort\Iface ) {
			$this->sort[] = $expr;
		} elseif( $expr ) {
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
	 * @return \Aimeos\MShop\ContextIface Context object implementing \Aimeos\MShop\ContextIface
	 */
	protected function context() : \Aimeos\MShop\ContextIface
	{
		return $this->context;
	}


	/**
	 * Returns the outmost decorator of the decorator stack
	 *
	 * @return \Aimeos\Controller\Frontend\Iface Outmost decorator object
	 */
	protected function object() : Iface
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


	/**
	 * Splits search keys by comma
	 *
	 * @param string|null $keys Comma separated string of search keys
	 * @return array List of search keys
	 */
	protected function splitKeys( ?string $keys ) : array
	{
		$list = [];

		if( preg_match_all( '/(?P<key>[^(,]+(\(("([^"]|\")*")?[^)]*\))?),?/', (string) $keys, $list ) !== false ) {
			return $list['key'] ?? [];
		}

		return [];
	}
}
