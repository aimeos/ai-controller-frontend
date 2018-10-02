<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2017-2018
 * @package Controller
 * @subpackage Frontend
 */


namespace Aimeos\Controller\Frontend\Stock;


/**
 * Default implementation of the stock frontend controller
 *
 * @package Controller
 * @subpackage Frontend
 */
class Standard
	extends \Aimeos\Controller\Frontend\Base
	implements Iface, \Aimeos\Controller\Frontend\Common\Iface
{
	/**
	 * Returns the given search filter with the conditions attached for filtering by product code
	 *
	 * @param \Aimeos\MW\Criteria\Iface $filter Criteria object used for stock search
	 * @param array $codes List of product codes
	 * @return \Aimeos\MW\Criteria\Iface Criteria object containing the conditions for searching
	 * @since 2017.03
	 */
	public function addFilterCodes( \Aimeos\MW\Criteria\Iface $filter, array $codes )
	{
		$expr = [
			$filter->compare( '==', 'stock.productcode', $codes ),
			$filter->getConditions(),
		];
		$filter->setConditions( $filter->combine( '&&', $expr ) );

		return $filter;
	}


	/**
	 * Returns the given search filter with the conditions attached for filtering by type code
	 *
	 * @param \Aimeos\MW\Criteria\Iface $filter Criteria object used for stock search
	 * @param array $codes List of stock type codes
	 * @return \Aimeos\MW\Criteria\Iface Criteria object containing the conditions for searching
	 * @since 2017.03
	 */
	public function addFilterTypes( \Aimeos\MW\Criteria\Iface $filter, array $codes )
	{
		if( !empty( $codes ) )
		{
			$expr = [
				$filter->compare( '==', 'stock.type.code', $codes ),
				$filter->getConditions(),
			];
			$filter->setConditions( $filter->combine( '&&', $expr ) );
		}

		return $filter;
	}


	/**
	 * Returns the default stock filter
	 *
	 * @param boolean True to add default criteria
	 * @return \Aimeos\MW\Criteria\Iface Criteria object containing the conditions for searching
	 * @since 2017.03
	 */
	public function createFilter()
	{
		$manager = \Aimeos\MShop\Factory::createManager( $this->getContext(), 'stock' );
		$search = $manager->createSearch( true );

		return $search->setSortations( [$search->sort( '+', 'stock.type.position' )] );
	}


	/**
	 * Returns the stock item for the given stock ID
	 *
	 * @param string $id Unique stock ID
	 * @return \Aimeos\MShop\Stock\Item\Iface Stock item including the referenced domains items
	 * @since 2017.03
	 */
	public function getItem( $id )
	{
		return \Aimeos\MShop\Factory::createManager( $this->getContext(), 'stock' )->getItem( $id, [], true );
	}


	/**
	 * Returns the stocks filtered by the given criteria object
	 *
	 * @param \Aimeos\MW\Criteria\Iface $filter Critera object which contains the filter conditions
	 * @param integer &$total Parameter where the total number of found stocks will be stored in
	 * @return array Ordered list of stock items implementing \Aimeos\MShop\Stock\Item\Iface
	 * @since 2017.03
	 */
	public function searchItems( \Aimeos\MW\Criteria\Iface $filter, &$total = null )
	{
		return \Aimeos\MShop\Factory::createManager( $this->getContext(), 'stock' )->searchItems( $filter, [], $total );
	}
}
