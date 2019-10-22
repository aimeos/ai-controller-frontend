<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2017-2018
 * @package Controller
 * @subpackage Frontend
 */


namespace Aimeos\Controller\Frontend\Locale;


/**
 * Default implementation of the locale frontend controller
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

		$this->manager = \Aimeos\MShop::create( $context, 'locale' );
		$this->filter = $this->manager->createSearch( true );

		$this->conditions[] = $this->filter->compare( '==', 'locale.siteid', $context->getLocale()->getSitePath() );
		$this->conditions[] = $this->filter->getConditions();
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
	 * @param string $key Search key defined by the locale manager, e.g. "locale.status"
	 * @param array|string $value Value or list of values to compare to
	 * @return \Aimeos\Controller\Frontend\Locale\Iface Locale controller for fluent interface
	 * @since 2019.04
	 */
	public function compare( $operator, $key, $value )
	{
		$this->conditions[] = $this->filter->compare( $operator, $key, $value );
		return $this;
	}


	/**
	 * Returns the locale for the given locale ID
	 *
	 * @param string $id Unique locale ID
	 * @return \Aimeos\MShop\Locale\Item\Iface Locale item including the referenced domains items
	 * @since 2019.04
	 */
	public function get( $id )
	{
		return $this->manager->getItem( $id, [], true );
	}


	/**
	 * Parses the given array and adds the conditions to the list of conditions
	 *
	 * @param array $conditions List of conditions, e.g. ['>' => ['locale.languageid' => 'de']]
	 * @return \Aimeos\Controller\Frontend\Locale\Iface Locale controller for fluent interface
	 * @since 2019.04
	 */
	public function parse( array $conditions )
	{
		if( ($cond = $this->filter->toConditions( $conditions ) ) !== null ) {
			$this->conditions[] = $cond;
		}

		return $this;
	}


	/**
	 * Returns the locales filtered by the previously assigned conditions
	 *
	 * @param integer &$total Parameter where the total number of found locales will be stored in
	 * @return \Aimeos\MShop\Locale\Item\Iface[] Ordered list of locale items
	 * @since 2019.04
	 */
	public function search( &$total = null )
	{
		$this->filter->setConditions( $this->filter->combine( '&&', $this->conditions ) );
		return $this->manager->searchItems( $this->filter, [], $total );
	}


	/**
	 * Sets the start value and the number of returned locale items for slicing the list of found locale items
	 *
	 * @param integer $start Start value of the first locale item in the list
	 * @param integer $limit Number of returned locale items
	 * @return \Aimeos\Controller\Frontend\Locale\Iface Locale controller for fluent interface
	 * @since 2019.04
	 */
	public function slice( $start, $limit )
	{
		$this->filter->setSlice( $start, $limit );
		return $this;
	}


	/**
	 * Sets the sorting of the result list
	 *
	 * @param string|null $key Sorting key of the result list like "position", null for no sorting
	 * @return \Aimeos\Controller\Frontend\Locale\Iface Locale controller for fluent interface
	 * @since 2019.04
	 */
	public function sort( $key = null )
	{
		$sort = [];
		$list = ( $key ? explode( ',', $key ) : [] );

		foreach( $list as $sortkey )
		{
			$direction = ( $sortkey[0] === '-' ? '-' : '+' );
			$sortkey = ltrim( $sortkey, '+-' );

			switch( $sortkey )
			{
				case 'position':
					$sort[] = $this->filter->sort( $direction, 'locale.position' );
					break;
				default:
					$sort[] = $this->filter->sort( $direction, $sortkey );
			}
		}

		$this->filter->setSortations( $sort );
		return $this;
	}
}
