<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2017-2018
 */


namespace Aimeos\Controller\Frontend\Attribute;


class StandardTest extends \PHPUnit\Framework\TestCase
{
	private $context;
	private $object;


	protected function setUp()
	{
		$this->context = \TestHelperFrontend::getContext();
		$this->object = new \Aimeos\Controller\Frontend\Attribute\Standard( $this->context );
	}


	protected function tearDown()
	{
		unset( $this->object, $this->context );
	}


	public function testAttribute()
	{
		$manager = \Aimeos\MShop::create( $this->context, 'attribute' );

		$blueId = $manager->findItem( 'blue', [], 'product', 'color' )->getId();
		$whiteId = $manager->findItem( 'white', [], 'product', 'color' )->getId();

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


	public function testGet()
	{
		$iface = \Aimeos\MShop\Attribute\Item\Iface::class;
		$item = \Aimeos\MShop::create( $this->context, 'attribute' )->findItem( 'white', [], 'product', 'color' );

		$this->assertInstanceOf( $iface, $this->object->get( $item->getId() ) );
	}


	public function testFind()
	{
		$iface = \Aimeos\MShop\Attribute\Item\Iface::class;
		$this->assertInstanceOf( $iface, $this->object->find( 'white', [], 'color' ) );
	}


	public function testParse()
	{
		$cond = ['&&' => [['>' => ['attribute.status' => 0]], ['==' => ['attribute.type' => 'color']]]];
		$this->assertEquals( 5, count( $this->object->parse( $cond )->search() ) );
	}


	public function testSearch()
	{
		$total = 0;
		$this->assertGreaterThanOrEqual( 26, count( $this->object->search( [], $total ) ) );
		$this->assertGreaterThanOrEqual( 26, $total );
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


	public function testSortPosition()
	{
		$result = $this->object->sort( 'position' )->search( [] );
		$this->assertEquals( 'white', reset( $result )->getCode() );
	}


	public function testSortCodeDesc()
	{
		$result = $this->object->sort( '-position' )->search( [] );
		$this->assertStringStartsWith( 'white', end( $result )->getCode() );
	}


	public function testType()
	{
		$this->assertEquals( 6, count( $this->object->type( 'size' )->search() ) );
	}
}
