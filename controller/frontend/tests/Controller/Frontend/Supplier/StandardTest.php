<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2018
 */


namespace Aimeos\Controller\Frontend\Supplier;


class StandardTest extends \PHPUnit\Framework\TestCase
{
	private $object;


	protected function setUp()
	{
		$this->object = new \Aimeos\Controller\Frontend\Supplier\Standard( \TestHelperFrontend::getContext() );
	}


	protected function tearDown()
	{
		unset( $this->object );
	}


	public function testCreateFilter()
	{
		$filter = $this->object->createFilter();

		$this->assertInstanceOf( '\\Aimeos\\MW\\Criteria\\Iface', $filter );
		$this->assertEquals( 0, $filter->getSliceStart() );
		$this->assertEquals( 100, $filter->getSliceSize() );
	}


	public function testGetItem()
	{
		$context = \TestHelperFrontend::getContext();
		$manager = \Aimeos\MShop\Factory::createManager( $context, 'supplier' );
		$id = $manager->findItem( 'unitCode001' )->getId();

		$result = $this->object->getItem( $id );

		$this->assertInstanceOf( '\Aimeos\MShop\Supplier\Item\Iface', $result );
	}


	public function testGetItems()
	{
		$context = \TestHelperFrontend::getContext();
		$manager = \Aimeos\MShop\Factory::createManager( $context, 'supplier' );
		$id = $manager->findItem( 'unitCode001' )->getId();

		$result = $this->object->getItems( [$id] );

		$this->assertInternalType( 'array', $result );
		$this->assertEquals( 1, count( $result ) );
	}


	public function testSearchItems()
	{
		$filter = $this->object->createFilter();

		$total = 0;
		$results = $this->object->searchItems( $filter, [], $total );

		$this->assertGreaterThanOrEqual( 2, $total );
		$this->assertGreaterThanOrEqual( 2, count( $results ) );
	}
}
