<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2018
 * @package Controller
 * @subpackage Frontend
 */


namespace Aimeos\Controller\Frontend\Supplier;


/**
 * Default implementation of the supplier frontend controller
 *
 * @package Controller
 * @subpackage Frontend
 */
class Standard
	extends \Aimeos\Controller\Frontend\Base
	implements Iface, \Aimeos\Controller\Frontend\Common\Iface
{
	/**
	 * Returns the default supplier filter
	 *
	 * @param boolean True to add default criteria
	 * @return \Aimeos\MW\Criteria\Iface Criteria object containing the conditions for searching
	 * @since 2018.07
	 */
	public function createFilter()
	{
		return \Aimeos\MShop\Factory::createManager( $this->getContext(), 'supplier' )->createSearch( true );
	}


	/**
	 * Returns the supplier item for the given supplier ID
	 *
	 * @param string $id Unique supplier ID
	 * @param string[] $domains Domain names of items that are associated with the suppliers and that should be fetched too
	 * @return \Aimeos\MShop\Supplier\Item\Iface Supplier item including the referenced domains items
	 * @since 2018.07
	 */
	public function getItem( $id, array $domains = array( 'media', 'text' ) )
	{
		return \Aimeos\MShop\Factory::createManager( $this->getContext(), 'supplier' )->getItem( $id, $domains, true );
	}


	/**
	 * Returns the supplier items for the given supplier IDs
	 *
	 * @param string $ids Unique supplier IDs
	 * @param string[] $domains Domain names of items that are associated with the suppliers and that should be fetched too
	 * @return \Aimeos\MShop\Supplier\Item\Iface[] Associative list of supplier item including the referenced domains items
	 * @since 2018.07
	 */
	public function getItems( array $ids, array $domains = array( 'media', 'text' ) )
	{
		$manager = \Aimeos\MShop\Factory::createManager( $this->getContext(), 'supplier' );

		$filter = $manager->createSearch( true );
		$expr = [
			$filter->compare( '==', 'supplier.id', $ids ),
			$filter->getConditions(),
		];
		$filter->setConditions( $filter->combine( '&&', $expr ) );

		return $manager->searchItems( $filter, $domains );
	}


	/**
	 * Returns the suppliers filtered by the given criteria object
	 *
	 * @param \Aimeos\MW\Criteria\Iface $filter Critera object which contains the filter conditions
	 * @param string[] $domains Domain names of items that are associated with the suppliers and that should be fetched too
	 * @param integer &$total Parameter where the total number of found suppliers will be stored in
	 * @return array Ordered list of supplier items implementing \Aimeos\MShop\Supplier\Item\Iface
	 * @since 2018.07
	 */
	public function searchItems( \Aimeos\MW\Criteria\Iface $filter, array $domains = array( 'media', 'text' ), &$total = null )
	{
		return \Aimeos\MShop\Factory::createManager( $this->getContext(), 'supplier' )->searchItems( $filter, $domains, $total );
	}
}
