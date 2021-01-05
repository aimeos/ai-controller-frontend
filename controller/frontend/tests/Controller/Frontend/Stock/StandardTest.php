<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2017-2021
 */


namespace Aimeos\Controller\Frontend\Stock;


class StandardTest extends \PHPUnit\Framework\TestCase
{
	private $object;
	private $context;


	protected function setUp() : void
	{
		$this->context = \TestHelperFrontend::getContext();
		$this->object = new \Aimeos\Controller\Frontend\Stock\Standard( $this->context );
	}


	protected function tearDown() : void
	{
		unset( $this->object, $this->context );
	}


	public function testProduct()
	{
		$item = \Aimeos\MShop::create( $this->context, 'product' )->find( 'CNC' );
		$this->assertEquals( 1, count( $this->object->product( $item->getId() )->search() ) );
	}


	public function testCompare()
	{
		$this->assertEquals( 7, count( $this->object->compare( '==', 'stock.stocklevel', null )->search() ) );
	}


	public function testGet()
	{
		$iface = \Aimeos\MShop\Stock\Item\Iface::class;
		$manager = \Aimeos\MShop::create( $this->context, 'stock' );
		$item = $manager->search( $manager->filter()->slice( 0, 1 ) )->first();

		$this->assertInstanceOf( $iface, $this->object->get( $item->getId() ) );
	}


	public function testParse()
	{
		$cond = ['||' => [['==' => ['stock.dateback' => null]], ['>=' => ['stock.dateback' => '2010-01-01 00:00:00']]]];
		$this->assertEquals( 19, count( $this->object->parse( $cond )->search() ) );
	}


	public function testSearch()
	{
		$total = 0;
		$this->assertGreaterThanOrEqual( 15, count( $this->object->search( $total ) ) );
		$this->assertGreaterThanOrEqual( 15, $total );
	}


	public function testSlice()
	{
		$this->assertEquals( 2, count( $this->object->slice( 0, 2 )->search() ) );
	}


	public function testSort()
	{
		$this->assertGreaterThanOrEqual( 15, count( $this->object->sort()->search() ) );
	}


	public function testSortGeneric()
	{
		$this->assertGreaterThanOrEqual( 15, count( $this->object->sort( 'stock.dateback' )->search() ) );
	}


	public function testSortMultiple()
	{
		$this->assertGreaterThanOrEqual( 15, count( $this->object->sort( 'stock.type,-stock.dateback' )->search() ) );
	}


	public function testSortStock()
	{
		$result = $this->object->sort( 'stock' )->search();
		$this->assertStringStartsWith( 100, $result->first()->getStocklevel() );
	}


	public function testSortStockDesc()
	{
		$result = $this->object->sort( '-stock' )->search();
		$this->assertStringStartsWith( 100, $result->last()->getStocklevel() );
	}


	public function testType()
	{
		$this->assertEquals( 8, count( $this->object->type( 'default' )->search() ) );
	}
}
