<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2017-2018
 * @package Controller
 * @subpackage Frontend
 */


namespace Aimeos\Controller\Frontend\Attribute;


/**
 * Default implementation of the attribute frontend controller
 *
 * @package Controller
 * @subpackage Frontend
 */
class Standard
	extends \Aimeos\Controller\Frontend\Base
	implements Iface, \Aimeos\Controller\Frontend\Common\Iface
{
	private $conditions = [];
	private $domain = 'product';
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

		$this->manager = \Aimeos\MShop::create( $context, 'attribute' );
		$this->filter = $this->manager->createSearch( true );
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
	 * Adds attribute IDs for filtering
	 *
	 * @param array|string $attrIds Attribute ID or list of IDs
	 * @return \Aimeos\Controller\Frontend\Attribute\Iface Attribute controller for fluent interface
	 * @since 2019.04
	 */
	public function attribute( $attrIds )
	{
		if( !empty( $attrIds ) ) {
			$this->conditions[] = $this->filter->compare( '==', 'attribute.id', $attrIds );
		}

		return $this;
	}


	/**
	 * Adds generic condition for filtering attributes
	 *
	 * @param string $operator Comparison operator, e.g. "==", "!=", "<", "<=", ">=", ">", "=~", "~="
	 * @param string $key Search key defined by the attribute manager, e.g. "attribute.status"
	 * @param array|string $value Value or list of values to compare to
	 * @return \Aimeos\Controller\Frontend\Attribute\Iface Attribute controller for fluent interface
	 * @since 2019.04
	 */
	public function compare( $operator, $key, $value )
	{
		$this->conditions[] = $this->filter->compare( $operator, $key, $value );
		return $this;
	}


	/**
	 * Adds the domain of the attributes for filtering
	 *
	 * @param string $domain Domain of the attributes
	 * @return \Aimeos\Controller\Frontend\Attribute\Iface Attribute controller for fluent interface
	 * @since 2019.04
	 */
	public function domain( $domain )
	{
		$this->domain = $domain;
		return $this;
	}


	/**
	 * Returns the attribute for the given attribute ID
	 *
	 * @param string $id Unique attribute ID
	 * @param string[] $domains Domain names of items that are associated with the attributes and that should be fetched too
	 * @return \Aimeos\MShop\Attribute\Item\Iface Attribute item including the referenced domains items
	 * @since 2019.04
	 */
	public function get( $id, $domains = ['media', 'price', 'text'] )
	{
		return $this->manager->getItem( $id, $domains, true );
	}


	/**
	 * Returns the attribute for the given attribute code
	 *
	 * @param string $code Unique attribute code
	 * @param string[] $domains Domain names of items that are associated with the attributes and that should be fetched too
	 * @param string $type Type assigned to the attribute
	 * @return \Aimeos\MShop\Attribute\Item\Iface Attribute item including the referenced domains items
	 * @since 2019.04
	 */
	public function find( $code, $domains = ['media', 'price', 'text'], $type = 'product' )
	{
		return $this->manager->findItem( $code, $domains, $this->domain, $type, true );
	}


	/**
	 * Parses the given array and adds the conditions to the list of conditions
	 *
	 * @param array $conditions List of conditions, e.g. ['&&' => [['>' => ['attribute.status' => 0]], ['==' => ['attribute.type' => 'color']]]]
	 * @return \Aimeos\Controller\Frontend\Attribute\Iface Attribute controller for fluent interface
	 * @since 2019.04
	 */
	public function parse( array $conditions )
	{
		$this->conditions[] = $this->filter->toConditions( $conditions );
		return $this;
	}


	/**
	 * Returns the attributes filtered by the previously assigned conditions
	 *
	 * @param string[] $domains Domain names of items that are associated with the attributes and that should be fetched too
	 * @param integer &$total Parameter where the total number of found attributes will be stored in
	 * @return \Aimeos\MShop\Attribute\Item\Iface[] Ordered list of attribute items
	 * @since 2019.04
	 */
	public function search( $domains = ['media', 'price', 'text'], &$total = null )
	{
		$expr = array_merge( $this->conditions, [$this->filter->compare( '==', 'attribute.domain', $this->domain )] );
		$this->filter->setConditions( $this->filter->combine( '&&', $expr ) );

		return $this->manager->searchItems( $this->filter, $domains, $total );
	}


	/**
	 * Sets the start value and the number of returned attributes for slicing the list of found attributes
	 *
	 * @param integer $start Start value of the first attribute in the list
	 * @param integer $limit Number of returned attributes
	 * @return \Aimeos\Controller\Frontend\Attribute\Iface Attribute controller for fluent interface
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
	 * @param string|null $key Sorting of the result list like "position", null for no sorting
	 * @return \Aimeos\Controller\Frontend\Attribute\Iface Attribute controller for fluent interface
	 * @since 2019.04
	 */
	public function sort( $key = null )
	{
		$direction = '+';

		if( $key != null && $key[0] === '-' )
		{
			$key = substr( $key, 1 );
			$direction = '-';
		}

		switch( $key )
		{
			case null:
				$this->filter->setSortations( [] );
				break;
			case 'position':
				$this->filter->setSortations( [
					$this->filter->sort( $direction, 'attribute.type' ),
					$this->filter->sort( $direction, 'attribute.position' )
				] );
				break;
			default:
				$this->filter->setSortations( [$this->filter->sort( $direction, $key )] );
		}

		return $this;
	}


	/**
	 * Adds attribute types for filtering
	 *
	 * @param array|string $codes Attribute ID or list of IDs
	 * @return \Aimeos\Controller\Frontend\Attribute\Iface Attribute controller for fluent interface
	 * @since 2019.04
	 */
	public function type( $codes )
	{
		if( !empty( $codes ) ) {
			$this->conditions[] = $this->filter->compare( '==', 'attribute.type', $codes );
		}

		return $this;
	}
}
