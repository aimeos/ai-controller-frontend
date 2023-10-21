<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2018-2023
 * @package Controller
 * @subpackage Frontend
 */


namespace Aimeos\Controller\Frontend\Review;


/**
 * Default implementation of the review frontend controller.
 *
 * @package Controller
 * @subpackage Frontend
 */
class Standard
	extends \Aimeos\Controller\Frontend\Base
	implements Iface, \Aimeos\Controller\Frontend\Common\Iface
{
	/** controller/frontend/review/name
	 * Class name of the used review frontend controller implementation
	 *
	 * Each default frontend controller can be replace by an alternative imlementation.
	 * To use this implementation, you have to set the last part of the class
	 * name as configuration value so the controller factory knows which class it
	 * has to instantiate.
	 *
	 * For example, if the name of the default class is
	 *
	 *  \Aimeos\Controller\Frontend\Review\Standard
	 *
	 * and you want to replace it with your own version named
	 *
	 *  \Aimeos\Controller\Frontend\Review\Myreview
	 *
	 * then you have to set the this configuration option:
	 *
	 *  controller/frontend/review/name = Myreview
	 *
	 * The value is the last part of your own class name and it's case sensitive,
	 * so take care that the configuration value is exactly named like the last
	 * part of the class name.
	 *
	 * The allowed characters of the class name are A-Z, a-z and 0-9. No other
	 * characters are possible! You should always start the last part of the class
	 * name with an upper case character and continue only with lower case characters
	 * or numbers. Avoid chamel case names like "MyReview"!
	 *
	 * @param string Last part of the class name
	 * @since 2020.10
	 * @category Developer
	 */

	/** controller/frontend/review/decorators/excludes
	 * Excludes decorators added by the "common" option from the review frontend controllers
	 *
	 * Decorators extend the functionality of a class by adding new aspects
	 * (e.g. log what is currently done), executing the methods of the underlying
	 * class only in certain conditions (e.g. only for logged in users) or
	 * modify what is returned to the caller.
	 *
	 * This option allows you to remove a decorator added via
	 * "controller/frontend/common/decorators/default" before they are wrapped
	 * around the frontend controller.
	 *
	 *  controller/frontend/review/decorators/excludes = array( 'decorator1' )
	 *
	 * This would remove the decorator named "decorator1" from the list of
	 * common decorators ("\Aimeos\Controller\Frontend\Common\Decorator\*") added via
	 * "controller/frontend/common/decorators/default" for the review frontend controller.
	 *
	 * @param array List of decorator names
	 * @since 2020.10
	 * @category Developer
	 * @see controller/frontend/common/decorators/default
	 * @see controller/frontend/review/decorators/global
	 * @see controller/frontend/review/decorators/local
	 */

	/** controller/frontend/review/decorators/global
	 * Adds a list of globally available decorators only to the review frontend controllers
	 *
	 * Decorators extend the functionality of a class by adding new aspects
	 * (e.g. log what is currently done), executing the methods of the underlying
	 * class only in certain conditions (e.g. only for logged in users) or
	 * modify what is returned to the caller.
	 *
	 * This option allows you to wrap global decorators
	 * ("\Aimeos\Controller\Frontend\Common\Decorator\*") around the frontend controller.
	 *
	 *  controller/frontend/review/decorators/global = array( 'decorator1' )
	 *
	 * This would add the decorator named "decorator1" defined by
	 * "\Aimeos\Controller\Frontend\Common\Decorator\Decorator1" only to the frontend controller.
	 *
	 * @param array List of decorator names
	 * @since 2020.10
	 * @category Developer
	 * @see controller/frontend/common/decorators/default
	 * @see controller/frontend/review/decorators/excludes
	 * @see controller/frontend/review/decorators/local
	 */

	/** controller/frontend/review/decorators/local
	 * Adds a list of local decorators only to the review frontend controllers
	 *
	 * Decorators extend the functionality of a class by adding new aspects
	 * (e.g. log what is currently done), executing the methods of the underlying
	 * class only in certain conditions (e.g. only for logged in users) or
	 * modify what is returned to the caller.
	 *
	 * This option allows you to wrap local decorators
	 * ("\Aimeos\Controller\Frontend\Review\Decorator\*") around the frontend controller.
	 *
	 *  controller/frontend/review/decorators/local = array( 'decorator2' )
	 *
	 * This would add the decorator named "decorator2" defined by
	 * "\Aimeos\Controller\Frontend\Catalog\Decorator\Decorator2" only to the frontend
	 * controller.
	 *
	 * @param array List of decorator names
	 * @since 2020.10
	 * @category Developer
	 * @see controller/frontend/common/decorators/default
	 * @see controller/frontend/review/decorators/excludes
	 * @see controller/frontend/review/decorators/global
	 */


	private \Aimeos\Base\Criteria\Iface $filter;
	private \Aimeos\MShop\Common\Manager\Iface $manager;


	/**
	 * Common initialization for controller classes
	 *
	 * @param \Aimeos\MShop\ContextIface $context Common MShop context object
	 */
	public function __construct( \Aimeos\MShop\ContextIface $context )
	{
		parent::__construct( $context );

		$this->manager = \Aimeos\MShop::create( $context, 'review' );
		$this->filter = $this->manager->filter( true );
	}


	/**
	 * Clones objects in controller
	 */
	public function __clone()
	{
		$this->filter = clone $this->filter;
		parent::__clone();
	}


	/**
	 * Returns the aggregated count of products for the given key.
	 *
	 * @param string $key Search key to aggregate for, e.g. "review.rating"
	 * @param string|null $value Search key for aggregating the value column
	 * @param string|null $type Type of the aggregation, empty string for count or "sum"
	 * @return \Aimeos\Map Associative list of key values as key and the product count for this key as value
	 * @since 2020.10
	 */
	public function aggregate( string $key, string $value = null, string $type = null ) : \Aimeos\Map
	{
		$filter = clone $this->filter;
		$cond = $filter->is( 'review.status', '>', 0 );

		$filter->setConditions( $filter->and( array_merge( $this->getConditions(), [$cond] ) ) );
		return $this->manager->aggregate( $filter, $key, $value, $type );
	}


	/**
	 * Adds generic condition for filtering
	 *
	 * @param string $operator Comparison operator, e.g. "==", "!=", "<", "<=", ">=", ">", "=~", "~="
	 * @param string $key Search key defined by the review manager, e.g. "review.status"
	 * @param array|string $value Value or list of values to compare to
	 * @return \Aimeos\Controller\Frontend\Review\Iface Review controller for fluent interface
	 * @since 2020.10
	 */
	public function compare( string $operator, string $key, $value ) : Iface
	{
		$this->addExpression( $this->filter->compare( $operator, $key, $value ) );
		return $this;
	}


	/**
	 * Returns a new rating item
	 *
	 * @param array $vals Associative list of key/value pairs to initialize the item
	 * @return \Aimeos\MShop\Review\Item\Iface New review item
	 */
	public function create( array $vals = [] ) : \Aimeos\MShop\Review\Item\Iface
	{
		return $this->manager->create()->setOrderProductId( $vals['review.orderproductid'] ?? '' )->fromArray( $vals );
	}


	/**
	 * Deletes the review item for the given ID or IDs
	 *
	 * @param array|string $id Unique review ID or list of IDs
	 * @return \Aimeos\Controller\Frontend\Review\Iface Review controller for fluent interface
	 * @since 2020.10
	 */
	public function delete( $ids ) : Iface
	{
		$ids = (array) $ids;
		$filter = $this->manager->filter()->add( ['review.id' => $ids] );
		$this->manager->delete( $this->manager->search( $filter->slice( 0, count( $ids ) ) )->toArray() );

		return $this;
	}


	/**
	 * Sets the review domain for filtering
	 *
	 * @param string $domain Domain (e.g. "product") of the reviewed items
	 * @return \Aimeos\Controller\Frontend\Review\Iface Review controller for fluent interface
	 * @since 2020.10
	 */
	public function domain( string $domain ) : Iface
	{
		$this->addExpression( $this->filter->compare( '==', 'review.domain', $domain ) );
		return $this;
	}


	/**
	 * Restricts the reviews to a specific domain item
	 *
	 * @param string $domain Domain the reviews belong to (e.g. "product")
	 * @param array|string|null $refid Id of the item the reviews belong to, list of or NULL for all reviews from the domain
	 * @return \Aimeos\Controller\Frontend\Review\Iface Review controller for fluent interface
	 * @since 2020.10
	 */
	public function for( string $domain, $refid ) : Iface
	{
		$this->addExpression( $this->filter->compare( '==', 'review.domain', $domain ) );

		if( $refid !== null ) {
			$this->addExpression( $this->filter->compare( '==', 'review.refid', $refid ) );
		}

		return $this;
	}


	/**
	 * Returns the review item for the given ID
	 *
	 * @param string $id Unique review ID
	 * @return \Aimeos\MShop\Review\Item\Iface Review object
	 */
	public function get( string $id ) : \Aimeos\MShop\Review\Item\Iface
	{
		return $this->manager->get( $id, [], null );
	}


	/**
	 * Returns the reviews for the logged-in user
	 *
	 * @param int &$total Parameter where the total number of found reviews will be stored in
	 * @return \Aimeos\Map Ordered list of review items implementing \Aimeos\MShop\Review\Item\Iface
	 * @since 2020.10
	 */
	public function list( int &$total = null ) : \Aimeos\Map
	{
		$filter = clone $this->filter;
		$cond = $filter->is( 'review.customerid', '==', $this->context()->user() );

		$filter->setConditions( $filter->and( array_merge( $this->getConditions(), [$cond] ) ) );
		$filter->setSortations( $this->getSortations() );

		return $this->manager->search( $filter, [], $total );
	}


	/**
	 * Parses the given array and adds the conditions to the list of conditions
	 *
	 * @param array $conditions List of conditions, e.g. ['>' => ['review.rating' => 3]]
	 * @return \Aimeos\Controller\Frontend\Review\Iface Review controller for fluent interface
	 * @since 2020.10
	 */
	public function parse( array $conditions ) : Iface
	{
		if( ( $cond = $this->filter->parse( $conditions ) ) !== null ) {
			$this->addExpression( $cond );
		}

		return $this;
	}


	/**
	 * Adds or updates a review
	 *
	 * @param \Aimeos\MShop\Review\Item\Iface $item Review item including required data
	 * @return \Aimeos\Controller\Frontend\Review\Iface Review controller for fluent interface
	 * @since 2020.10
	 */
	public function save( \Aimeos\MShop\Review\Item\Iface $item ) : \Aimeos\MShop\Review\Item\Iface
	{
		$domain = $item->getDomain();

		if( !in_array( $domain, ['product', 'locale/site'] ) )
		{
			$msg = sprintf( 'Domain "%1$s" is not supported', $domain );
			throw new \Aimeos\Controller\Frontend\Review\Exception( $msg );
		}

		$context = $this->context();
		$manager = \Aimeos\MShop::create( $context, 'order' );

		$filter = $manager->filter( true )->add( [
			'order.product.id' => $item->getOrderProductId(),
			'order.customerid' => $context->user()
		] );
		$manager->search( $filter->slice( 0, 1 ) )->first( new \Aimeos\Controller\Frontend\Review\Exception(
			sprintf( 'You can only add a review if you have ordered a product' )
		) );

		$ordProdItem = \Aimeos\MShop::create( $context, 'order/product' )->get( $item->getOrderProductId() );

		$filter = $this->manager->filter()->add( [
			'review.customerid' => $context->user(),
			'review.id' => $item->getId()
		] );

		/** controller/frontend/review/status
		 * Default status for new reviews
		 *
		 * By default, new reviews are stored with the status "in review" so they
		 * need to be approved by an admin or editor. Possible status values are:
		 *
		 * * 1 : enabled
		 * * 0 : disabled
		 * * -1 : in review
		 *
		 * @param integer Review status value
		 * @since 2020.10
		 */
		$status = $context->config()->get( 'controller/frontend/review/status', -1 );

		$real = $this->manager->search( $filter->slice( 0, 1 ) )->first( $this->manager->create() );

		$real = $real->setCustomerId( $context->user() )
			->setRefId( $ordProdItem->getType() === 'select' ? $ordProdItem->getParentProductId() : $ordProdItem->getProductId() )
			->setOrderProductId( $ordProdItem->getId() )
			->setComment( $item->getComment() )
			->setRating( $item->getRating() )
			->setName( $item->getName() )
			->setDomain( 'product' )
			->setStatus( $status );

		$item = $this->manager->save( $real );

		$filter = $this->manager->filter( true )->add( [
			'review.refid' => $item->getRefId(),
			'review.domain' => $domain,
		] );

		if( $status > 0
			&& ( $entry = $this->manager->aggregate( $filter, 'review.refid', 'review.rating', 'rate' )->first( [] ) ) !== []
			&& !empty( $cnt = current( $entry ) )
		) {
			$rateManager = \Aimeos\MShop::create( $context, $domain === 'product' ? 'index' : $domain );
			$rateManager->rate( $item->getRefId(), key( $entry ) / $cnt, $cnt );

			$context->cache()->deleteByTags( [$domain, $domain . '-' . $item->getRefId()] );
		}

		return $item;
	}


	/**
	 * Returns the reviews filtered by the previously assigned conditions
	 *
	 * @param int &$total Parameter where the total number of found reviews will be stored in
	 * @return \Aimeos\Map Ordered list of review items implementing \Aimeos\MShop\Review\Item\Iface
	 * @since 2020.10
	 */
	public function search( int &$total = null ) : \Aimeos\Map
	{
		$filter = clone $this->filter;
		$cond = $filter->is( 'review.status', '>', 0 );

		$maxsize = $this->context()->config()->get( 'controller/frontend/common/max-size', 500 );
		$filter->slice( $filter->getOffset(), min( $filter->getLimit(), $maxsize ) );

		$filter->setSortations( $this->getSortations() );
		$filter->setConditions( $filter->and( array_merge( $this->getConditions(), [$cond] ) ) );

		return $this->manager->search( $filter, [], $total );
	}


	/**
	 * Sets the start value and the number of returned review items for slicing the list of found review items
	 *
	 * @param int $start Start value of the first review item in the list
	 * @param int $limit Number of returned review items
	 * @return \Aimeos\Controller\Frontend\Review\Iface Review controller for fluent interface
	 * @since 2020.10
	 */
	public function slice( int $start, int $limit ) : Iface
	{
		$this->filter->slice( $start, $limit );
		return $this;
	}


	/**
	 * Sets the sorting of the result list
	 *
	 * @param string|null $key Sorting key of the result list like "mtime" or "rating", null for no sorting
	 * @return \Aimeos\Controller\Frontend\Review\Iface Review controller for fluent interface
	 * @since 2020.10
	 */
	public function sort( string $key = null ) : Iface
	{
		$list = $this->splitKeys( $key );

		foreach( $list as $sortkey )
		{
			$direction = ( $sortkey[0] === '-' ? '-' : '+' );
			$sortkey = ltrim( $sortkey, '+-' );

			switch( $sortkey )
			{
				case 'ctime':
					$this->addExpression( $this->filter->sort( $direction, 'review.ctime' ) );
					break;
				case 'rating':
					$this->addExpression( $this->filter->sort( $direction, 'review.rating' ) );
					break;
				default:
					$this->addExpression( $this->filter->sort( $direction, $sortkey ) );
			}
		}

		return $this;
	}


	/**
	 * Returns the manager used by the controller
	 *
	 * @return \Aimeos\MShop\Common\Manager\Iface Manager object
	 */
	protected function getManager() : \Aimeos\MShop\Common\Manager\Iface
	{
		return $this->manager;
	}
}
