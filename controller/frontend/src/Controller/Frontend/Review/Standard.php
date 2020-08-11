<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2018-2020
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
	private $conditions = [];
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
		$this->filter = $this->manager->createSearch();
	}


	/**
	 * Clones objects in controller and resets values
	 */
	public function __clone()
	{
		$this->filter = clone $this->filter;
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
		$this->conditions[] = $this->filter->compare( $operator, $key, $value );
		return $this;
	}


	/**
	 * Deletes the review item for the given ID
	 *
	 * @param string $id Unique review ID
	 * @return \Aimeos\Controller\Frontend\Review\Iface Review controller for fluent interface
	 * @since 2020.10
	 */
	public function delete( string $id ) : Iface
	{
		$item = $this->manager->get( $id );

		$manager = \Aimeos\MShop::create( $this->getContext(), 'order/base' );
		$filter = $manager->filter( true )->add( ['order.base.product.id' => $item->getOrderProductId()] );

		if( $manager->search( $filter->slice( 0, 1 ) )->empty() )
		{
			$msg = sprintf( 'You are not allowed to delete the review' );
			throw new \Aimeos\Controller\Frontend\Review\Exception( $msg );
		}

		$this->manager->delete( $id );
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
		$this->conditions['domain'] = $this->filter->compare( '==', 'review.domain', $domain );
		return $this;
	}


	/**
	 * Restricts the reviews to a specific domain item
	 *
	 * @param string $domain Domain the reviews belong to (e.g. "product")
	 * @param string|null $refid Id of the item the reviews belong to or NULL for all reviews from the domain
	 * @return \Aimeos\Controller\Frontend\Review\Iface Review controller for fluent interface
	 * @since 2020.10
	 */
	public function for( string $domain, ?string $refid ) : Iface
	{
		$this->conditions['domain'] = $this->filter->compare( '==', 'review.domain', $domain );

		if( $refid !== null ) {
			$this->conditions['refid'] = $this->filter->compare( '==', 'review.refid', $refid );
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
		return $this->manager->getItem( $id, [], true );
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

		$filter->setConditions( $filter->combine( '&&', array_merge( $this->conditions, [$cond] ) ) );
		return $this->manager->searchItems( $filter, [], $total );
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
		if( ( $cond = $this->filter->toConditions( $conditions ) ) !== null ) {
			$this->conditions[] = $cond;
		}

		return $this;
	}


	/**
	 * Saves the modified review item
	 *
	 * @param \Aimeos\MShop\Review\Item\Iface $item Review object
	 * @return \Aimeos\MShop\Review\Item\Iface Saved review item
	 */
	public function save( \Aimeos\MShop\Review\Item\Iface $item ) : \Aimeos\MShop\Review\Item\Iface
	{
		$manager = \Aimeos\MShop::create( $this->getContext(), 'order/base' );
		$filter = $manager->filter( true )->add( ['order.base.product.id' => $item->getOrderProductId()] );

		if( $manager->search( $filter->slice( 0, 1 ) )->empty() )
		{
			$msg = sprintf( 'You are not allowed to add or change the review' );
			throw new \Aimeos\Controller\Frontend\Review\Exception( $msg );
		}

		return $this->manager->saveItem( $item );
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

		$filter->setConditions( $filter->combine( '&&', array_merge( $this->conditions, [$cond] ) ) );
		return $this->manager->searchItems( $filter, [], $total );
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
		$this->filter->setSlice( $start, $limit );
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
		$sort = [];
		$list = ( $key ? explode( ',', $key ) : [] );

		foreach( $list as $sortkey )
		{
			$direction = ( $sortkey[0] === '-' ? '-' : '+' );
			$sortkey = ltrim( $sortkey, '+-' );

			switch( $sortkey )
			{
				case 'ctime':
					$sort[] = $this->filter->sort( $direction, 'review.ctime' );
					break;
				case 'rating':
					$sort[] = $this->filter->sort( $direction, 'review.rating' );
					break;
				default:
					$sort[] = $this->filter->sort( $direction, $sortkey );
			}
		}

		$this->filter->setSortations( $sort );
		return $this;
	}
}
