<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Metaways Infosystems GmbH, 2012
 * @copyright Aimeos (aimeos.org), 2015-2026
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
	 * @return static Controller object for chaining method calls
	 */
	public function addExpression( ?\Aimeos\Base\Criteria\Expression\Iface $expr = null ) : static
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
	 * Removes the domains whose items customers are not allowed to retrieve
	 *
	 * @param array $domains Domain names or domain names as keys and list types as values
	 * @param array $extra Additional domain names allowed by the controller
	 * @return array Allowed domain names or domain names as keys and list types as values
	 */
	protected function checkDomains( array $domains, array $extra = [] ) : array
	{
		/** controller/frontend/domains
		 * List of domains whose items can be fetched by the frontend controllers
		 *
		 * The domains passed to the uses() methods of the frontend controllers
		 * are often taken from the request, e.g. the "include" parameter of the
		 * JSON REST API. Referenced items are fetched by their IDs without
		 * checking if they belong to the current customer, so only domains
		 * whose items are public are allowed. Domains which are not in the list
		 * are silently removed. Controllers can allow own sub-domains in
		 * addition, e.g. "customer/address" in the customer controller.
		 *
		 * SECURITY: Only add domains whose items are public in your shop, i.e.
		 * catalog data like products, texts, media or prices. Never add domains
		 * like "order", "basket", "subscription", "review", "customer", "group",
		 * "service", "coupon", "plugin", "rule" or "locale" because customers
		 * would be able to read data of other customers or the shop configuration,
		 * e.g. customers referenced by products to limit their visibility.
		 *
		 * @param array List of domain names
		 * @since 2026.10
		 * @see controller/frontend/customer/listdomains
		 */
		$default = [
			'attribute', 'attribute/property', 'catalog', 'locale/site', 'media', 'media/property', 'price',
			'product', 'product/property', 'stock', 'supplier', 'supplier/address', 'text'
		];
		$allowed = (array) $this->context->config()->get( 'controller/frontend/domains', $default );
		$allowed = array_merge( $allowed, $extra );
		$list = [];

		foreach( $domains as $key => $value )
		{
			if( in_array( is_string( $key ) ? $key : $value, $allowed, true ) ) {
				$list[$key] = $value;
			}
		}

		return $list;
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
	protected function object() : \Aimeos\Controller\Frontend\Iface
	{
		if( $this->object !== null ) {
			return $this->object;
		}

		return $this; // @phpstan-ignore return.type
	}


	/**
	 * Injects the reference of the outmost object
	 *
	 * @param \Aimeos\Controller\Frontend\Iface $object Reference to the outmost controller or decorator
	 * @return static Controller object for chaining method calls
	 */
	public function setObject( \Aimeos\Controller\Frontend\Iface $object ) : static
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
			return $list['key'];
		}

		return [];
	}
}
