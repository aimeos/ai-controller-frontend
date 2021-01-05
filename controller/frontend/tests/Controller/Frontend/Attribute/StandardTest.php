<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2017-2021
 */


namespace Aimeos\Controller\Frontend\Attribute;


class StandardTest extends \PHPUnit\Framework\TestCase
{
	private $context;
	private $object;


	protected function setUp() : void
	{
		$this->context = \TestHelperFrontend::getContext();
		$this->object = new \Aimeos\Controller\Frontend\Attribute\Standard( $this->context );
	}


	protected function tearDown() : void
	{
		unset( $this->object, $this->context );
	}


	public function testAttribute()
	{
		$manager = \Aimeos\MShop::create( $this->context, 'attribute' );

		$blueId = $manager->find( 'blue', [], 'product', 'color' )->getId();
		$whiteId = $manager->find( 'white', [], 'product', 'color' )->getId();

		$this->assertEquals( 2, count( $this->object->attribute( [$blueId, $whiteId] )->search() ) );
	}


	public function testDomain()
	{
		$this->assertEquals( 5, count( $this->object->domain( 'media' )->search() ) );
	}


	public function testCompare()
	{
		$this->assertEquals( 5, count( $this->object->compare( '==', 'attribute.type', 'color' )->search() ) );
	}


	public function testFind()
	{
		$item = $this->object->uses( ['text'] )->find( 'white', 'color' );

		$this->assertInstanceOf( \Aimeos\MShop\Attribute\Item\Iface::class, $item );
		$this->assertEquals( 1, count( $item->getRefItems( 'text' ) ) );
	}


	public function testFunction()
	{
		$str = $this->object->function( 'attribute:prop', ['type', null, 'value'] );
		$this->assertEquals( 'attribute:prop("type",null,"value")', $str );
	}


	public function testGet()
	{
		$item = \Aimeos\MShop::create( $this->context, 'attribute' )->find( 'white', [], 'product', 'color' );
		$item = $this->object->uses( ['text'] )->get( $item->getId() );

		$this->assertInstanceOf( \Aimeos\MShop\Attribute\Item\Iface::class, $item );
		$this->assertEquals( 1, count( $item->getRefItems( 'text' ) ) );
	}



	public function testHas()
	{
		$this->assertEquals( 3, count( $this->object->has( 'price', 'default' )->search() ) );
	}


	public function testParse()
	{
		$cond = ['&&' => [['>' => ['attribute.status' => 0]], ['==' => ['attribute.type' => 'color']]]];
		$this->assertEquals( 5, count( $this->object->parse( $cond )->search() ) );
	}


	public function testProperty()
	{
		$this->assertEquals( 1, count( $this->object->property( 'size', '1024' )->search() ) );
	}


	public function testSearch()
	{
		$total = 0;
		$items = $this->object->uses( ['text'] )->sort( 'attribute.code' )->search( $total );

		$this->assertGreaterThanOrEqual( 26, count( $items ) );
		$this->assertGreaterThanOrEqual( 26, $total );
		$this->assertEquals( 1, count( $items->last()->getRefItems( 'text' ) ) );
	}


	public function testSlice()
	{
		$this->assertEquals( 2, count( $this->object->slice( 0, 2 )->search() ) );
	}


	public function testSort()
	{
		$this->assertGreaterThanOrEqual( 26, count( $this->object->sort()->search() ) );
	}


	public function testSortGeneric()
	{
		$this->assertGreaterThanOrEqual( 26, count( $this->object->sort( 'attribute.status' )->search() ) );
	}


	public function testSortMultiple()
	{
		$this->assertGreaterThanOrEqual( 26, count( $this->object->sort( 'attribute.status,-attribute.code' )->search() ) );
	}


	public function testSortPosition()
	{
		$result = $this->object->sort( 'position' )->search();
		$this->assertEquals( 'white', $result->first()->getCode() );
	}


	public function testSortCodeDesc()
	{
		$result = $this->object->sort( '-position' )->search();
		$this->assertStringStartsWith( 'white', $result->last()->getCode() );
	}


	public function testType()
	{
		$this->assertEquals( 6, count( $this->object->type( 'size' )->search() ) );
	}


	public function testUses()
	{
		$this->assertSame( $this->object, $this->object->uses( ['text'] ) );
	}
}
