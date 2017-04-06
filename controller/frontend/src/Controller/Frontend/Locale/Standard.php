<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2017
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
	/**
	 * Returns the default locale filter
	 *
	 * @param boolean True to add default criteria
	 * @return \Aimeos\MW\Criteria\Iface Criteria object containing the conditions for searching
	 * @since 2017.03
	 */
	public function createFilter()
	{
		$context = $this->getContext();
		$filter = \Aimeos\MShop\Factory::createManager( $context, 'locale' )->createSearch( true );

		$expr = array(
			$filter->compare( '==', 'locale.siteid', $context->getLocale()->getSitePath() ),
			$filter->getConditions(),
		);

		$filter->setConditions( $filter->combine( '&&', $expr ) );
		$filter->setSortations( array( $filter->sort( '+', 'locale.position' ) ) );

		return $filter;
	}


	/**
	 * Returns the locale item for the given locale ID
	 *
	 * @param string $id Unique locale ID
	 * @param string[] $domains Domain names of items that are associated with the locales and that should be fetched too
	 * @return \Aimeos\MShop\Locale\Item\Iface Locale item including the referenced domains items
	 * @since 2017.03
	 */
	public function getItem( $id, array $domains = [] )
	{
		return \Aimeos\MShop\Factory::createManager( $this->getContext(), 'locale' )->getItem( $id, $domains, true );
	}


	/**
	 * Returns the locales filtered by the given criteria object
	 *
	 * @param \Aimeos\MW\Criteria\Iface $filter Critera object which contains the filter conditions
	 * @param string[] $domains Domain names of items that are associated with the locales and that should be fetched too
	 * @param integer &$total Parameter where the total number of found locales will be stored in
	 * @return array Ordered list of locale items implementing \Aimeos\MShop\Locale\Item\Iface
	 * @since 2017.03
	 */
	public function searchItems( \Aimeos\MW\Criteria\Iface $filter, array $domains = [], &$total = null )
	{
		return \Aimeos\MShop\Factory::createManager( $this->getContext(), 'locale' )->searchItems( $filter, $domains, $total );
	}
}
