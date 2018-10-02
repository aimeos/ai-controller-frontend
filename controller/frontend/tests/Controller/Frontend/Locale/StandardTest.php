<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2017-2018
 */


namespace Aimeos\Controller\Frontend\Locale;


class StandardTest extends \PHPUnit\Framework\TestCase
{
	private $object;


	protected function setUp()
	{
		$this->object = new \Aimeos\Controller\Frontend\Locale\Standard( \TestHelperFrontend::getContext() );
	}


	protected function tearDown()
	{
		unset( $this->object );
	}


	public function testCreateFilter()
	{
		$filter = $this->object->createFilter();

		$this->assertInstanceOf( '\\Aimeos\\MW\\Criteria\\Iface', $filter );
		$this->assertEquals( 1, count( $filter->getSortations() ) );
		$this->assertEquals( 0, $filter->getSliceStart() );
		$this->assertEquals( 100, $filter->getSliceSize() );
	}


	public function testGetItem()
	{
		$localeManager = \Aimeos\MShop\Factory::createManager( \TestHelperFrontend::getContext(), 'locale' );
		$search = $localeManager->createSearch( true );
		$search->setSortations( [$search->sort( '+', 'locale.position' )] );
		$search->setSlice( 0, 1 );
		$localeItems = $localeManager->searchItems( $search );

		if( ( $localeItem = reset( $localeItems ) ) === false ) {
			throw new \Exception( 'No locale item found' );
		}


		$result = $this->object->getItem( $localeItem->getId() );

		$this->assertInstanceOf( '\Aimeos\MShop\Locale\Item\Iface', $result );
	}


	public function testSearchItems()
	{
		$total = 0;
		$filter = $this->object->createFilter();
		$results = $this->object->searchItems( $filter, [], $total );

		$this->assertEquals( 1, $total );
		$this->assertEquals( 1, count( $results ) );
	}
}
