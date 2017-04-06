<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2017
 */


namespace Aimeos\Controller\Frontend\Stock;


class StandardTest extends \PHPUnit_Framework_TestCase
{
	private $object;


	protected function setUp()
	{
		$this->object = new \Aimeos\Controller\Frontend\Stock\Standard( \TestHelperFrontend::getContext() );
	}


	protected function tearDown()
	{
		unset( $this->object );
	}


	public function testCreateFilter()
	{
		$filter = $this->object->createFilter();

		$this->assertInstanceOf( '\\Aimeos\\MW\\Criteria\\Iface', $filter );
		$this->assertEquals( [], $filter->getSortations() );
		$this->assertEquals( 0, $filter->getSliceStart() );
		$this->assertEquals( 100, $filter->getSliceSize() );
	}


	public function testAddFilterCodes()
	{
		$filter = $this->object->createFilter();
		$filter = $this->object->addFilterCodes( $filter, ['CNC', 'CNE'] );

		$list = $filter->getConditions()->getExpressions();

		if( !isset( $list[0] ) || !( $list[0] instanceof \Aimeos\MW\Criteria\Expression\Compare\Iface ) ) {
			throw new \RuntimeException( 'Wrong expression' );
		}

		$this->assertEquals( 'stock.productcode', $list[0]->getName() );
		$this->assertEquals( 2, count( $list[0]->getValue() ) );
	}


	public function testAddFilterTypes()
	{
		$filter = $this->object->createFilter();
		$filter = $this->object->addFilterTypes( $filter, ['default'] );

		$list = $filter->getConditions()->getExpressions();

		if( !isset( $list[0] ) || !( $list[0] instanceof \Aimeos\MW\Criteria\Expression\Compare\Iface ) ) {
			throw new \RuntimeException( 'Wrong expression' );
		}

		$this->assertEquals( 'stock.type.code', $list[0]->getName() );
		$this->assertEquals( 1, count( $list[0]->getValue() ) );
	}


	public function testGetItem()
	{
		$context = \TestHelperFrontend::getContext();
		$manager = \Aimeos\MShop\Factory::createManager( $context, 'stock' );
		$id = $manager->findItem( 'CNC', [], 'product', 'default' )->getId();

		$result = $this->object->getItem( $id );

		$this->assertInstanceOf( '\Aimeos\MShop\Stock\Item\Iface', $result );
	}


	public function testSearchItems()
	{
		$filter = $this->object->createFilter();
		$filter = $this->object->addFilterCodes( $filter, ['CNC', 'CNE'] );

		$total = 0;
		$results = $this->object->searchItems( $filter, $total );

		$this->assertEquals( 2, $total );
		$this->assertEquals( 2, count( $results ) );
	}
}
