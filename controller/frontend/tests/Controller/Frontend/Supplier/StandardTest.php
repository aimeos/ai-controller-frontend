<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2018
 */


namespace Aimeos\Controller\Frontend\Supplier;


class StandardTest extends \PHPUnit\Framework\TestCase
{
	private $object;
	private $context;


	protected function setUp()
	{
		$this->context = \TestHelperFrontend::getContext();
		$this->object = new \Aimeos\Controller\Frontend\Supplier\Standard( $this->context );
	}


	protected function tearDown()
	{
		unset( $this->object, $this->context );
	}


	public function testCompare()
	{
		$this->assertEquals( 2, count( $this->object->compare( '=~', 'supplier.label', 'unit' )->search() ) );
	}


	public function testFind()
	{
		$iface = \Aimeos\MShop\Supplier\Item\Iface::class;
		$this->assertInstanceOf( $iface, $this->object->find( 'unitCode001' ) );
	}


	public function testGet()
	{
		$iface = \Aimeos\MShop\Supplier\Item\Iface::class;
		$item = \Aimeos\MShop::create( $this->context, 'supplier' )->findItem( 'unitCode001' );

		$this->assertInstanceOf( $iface, $this->object->get( $item->getId() ) );
	}


	public function testParse()
	{
		$cond = ['&&' => [['==' => ['supplier.status' => 1]], ['=~' => ['supplier.label' => 'unit']]]];
		$this->assertEquals( 2, count( $this->object->parse( $cond )->search() ) );
	}


	public function testSearch()
	{
		$total = 0;
		$this->assertGreaterThanOrEqual( 2, count( $this->object->search( [], $total ) ) );
		$this->assertGreaterThanOrEqual( 2, $total );
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
}
