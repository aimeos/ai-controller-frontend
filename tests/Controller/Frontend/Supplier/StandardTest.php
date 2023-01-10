<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2018-2023
 */


namespace Aimeos\Controller\Frontend\Supplier;


class StandardTest extends \PHPUnit\Framework\TestCase
{
	private $object;
	private $context;


	protected function setUp() : void
	{
		$this->context = \TestHelper::context();
		$this->object = new \Aimeos\Controller\Frontend\Supplier\Standard( $this->context );
	}


	protected function tearDown() : void
	{
		unset( $this->object, $this->context );
	}


	public function testCompare()
	{
		$this->assertEquals( 2, count( $this->object->compare( '=~', 'supplier.label', 'Unit' )->search() ) );
	}


	public function testFind()
	{
		$item = $this->object->uses( ['text'] )->find( 'unitSupplier001' );

		$this->assertInstanceOf( \Aimeos\MShop\Supplier\Item\Iface::class, $item );
		$this->assertEquals( 3, count( $item->getRefItems( 'text' ) ) );
	}


	public function testFunction()
	{
		$str = $this->object->function( 'supplier:has', ['domain', 'type', 'refid'] );
		$this->assertEquals( 'supplier:has("domain","type","refid")', $str );
	}


	public function testGet()
	{
		$item = \Aimeos\MShop::create( $this->context, 'supplier' )->find( 'unitSupplier001' );
		$item = $this->object->uses( ['text'] )->get( $item->getId() );

		$this->assertInstanceOf( \Aimeos\MShop\Supplier\Item\Iface::class, $item );
		$this->assertEquals( 3, count( $item->getRefItems( 'text' ) ) );
	}


	public function testHas()
	{
		$manager = \Aimeos\MShop::create( $this->context, 'text' );
		$filter = $manager->filter()->add( ['text.domain' => 'supplier'] )->slice( 0, 1 );
		$id = $manager->search( $filter )->first()->getId();

		$this->assertEquals( 1, count( $this->object->has( 'text', 'default', $id )->search() ) );
	}


	public function testParse()
	{
		$cond = ['&&' => [['==' => ['supplier.status' => 1]], ['=~' => ['supplier.label' => 'Unit']]]];
		$this->assertEquals( 2, count( $this->object->parse( $cond )->search() ) );
	}


	public function testSearch()
	{
		$total = 0;
		$items = $this->object->uses( ['text'] )->compare( '=~', 'supplier.code', 'unit' )
			->sort( 'supplier.code' )->search( $total );

		$this->assertGreaterThanOrEqual( 2, count( $items ) );
		$this->assertGreaterThanOrEqual( 2, $total );
		$this->assertEquals( 3, count( $items->first()->getRefItems( 'text' ) ) );
	}


	public function testSlice()
	{
		$this->assertEquals( 1, count( $this->object->slice( 0, 1 )->search() ) );
	}


	public function testSort()
	{
		$this->assertGreaterThanOrEqual( 2, count( $this->object->sort()->search() ) );
	}


	public function testSortGeneric()
	{
		$this->assertGreaterThanOrEqual( 2, count( $this->object->sort( 'supplier.label' )->search() ) );
	}


	public function testSortMultiple()
	{
		$this->assertGreaterThanOrEqual( 2, count( $this->object->sort( 'supplier.label,-supplier.id' )->search() ) );
	}


	public function testUses()
	{
		$this->assertSame( $this->object, $this->object->uses( ['text'] ) );
	}
}
