<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2018-2021
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
	private $filter;
	private $manager;


	/**
	 * Common initialization for controller classes
	 *
	 * @param \Aimeos\MShop\Context\Item\Iface $context Common MShop context object
	 */
	public function __construct( \Aimeos\MShop\Context\Item\Iface $context )
	{
		parent::__construct( $context );

		$this->manager = \Aimeos\MShop::create( $context, 'review' );
		$this->filter = $this->manager->filter( true );
	}


	/**
	 * Clones objects in controller and resets values
	 */
	public function __clone()
	{
		$this->filter = clone $this->filter;
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

		$filter->setSortations( $this->getSortations() );
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
		return $this->manager->get( $id, [], true );
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
		$cond = $filter->is( 'review.customerid', '==', $this->getContext()->getUserId() );

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

		if( !in_array( $domain, ['product'] ) )
		{
			$msg = sprintf( 'Domain "%1$s" is not supported', $domain );
			throw new \Aimeos\Controller\Frontend\Review\Exception( $msg );
		}

		$context = $this->getContext();
		$manager = \Aimeos\MShop::create( $context, 'order/base' );

		$filter = $manager->filter( true )->add( [
			'order.base.product.id' => $item->getOrderProductId(),
			'order.base.customerid' => $context->getUserId()
		] );
		$manager->search( $filter->slice( 0, 1 ) )->first( new \Aimeos\Controller\Frontend\Review\Exception(
			sprintf( 'You can only add a review if you have ordered a product' )
		) );

		$orderProductItem = \Aimeos\MShop::create( $context, 'order/base/product' )->get( $item->getOrderProductId() );

		$filter = $this->manager->filter()->add( [
			'review.customerid' => $context->getUserId(),
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
		$status = $context->getConfig()->get( 'controller/frontend/review/status', -1 );

		$real = $this->manager->search( $filter->slice( 0, 1 ) )->first( $this->manager->create() );

		$real = $real->setCustomerId( $context->getUserId() )
			->setOrderProductId( $orderProductItem->getId() )
			->setRefId( $orderProductItem->getProductId() )
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

		$maxsize = $this->getContext()->config()->get( 'controller/frontend/common/max-size', 500 );
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
