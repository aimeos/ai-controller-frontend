<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2017-2021
 */


namespace Aimeos\Controller\Frontend\Locale;


class StandardTest extends \PHPUnit\Framework\TestCase
{
	private $object;
	private $context;


	protected function setUp() : void
	{
		$this->context = \TestHelperFrontend::getContext();
		$this->object = new \Aimeos\Controller\Frontend\Locale\Standard( $this->context );
	}


	protected function tearDown() : void
	{
		unset( $this->object, $this->context );
	}


	public function testCompare()
	{
		$this->assertEquals( 1, count( $this->object->compare( '>', 'locale.status', 0 )->search() ) );
	}


	public function testGet()
	{
		$expected = \Aimeos\MShop\Locale\Item\Iface::class;

		$manager = \Aimeos\MShop::create( $this->context, 'locale' );
		$id = $manager->search( $manager->filter( true ) )->first()->getId();

		$this->assertInstanceOf( $expected, $this->object->get( $id ) );
	}


	public function testParse()
	{
		$cond = ['>' => ['locale.status' => 0]];
		$this->assertEquals( 1, count( $this->object->parse( $cond )->search() ) );
	}


	public function testSearch()
	{
		$total = 0;
		$this->assertGreaterThanOrEqual( 1, count( $this->object->search( $total ) ) );
		$this->assertGreaterThanOrEqual( 1, $total );
	}


	public function testSlice()
	{
		$this->assertEquals( 1, count( $this->object->slice( 0, 1 )->search() ) );
	}


	public function testSort()
	{
		$this->assertEquals( 1, count( $this->object->sort()->search() ) );
	}


	public function testSortPosition()
	{
		$this->assertEquals( 1, count( $this->object->sort( 'position' )->search() ) );
	}


	public function testSortGeneric()
	{
		$this->assertEquals( 1, count( $this->object->sort( 'locale.status' )->search() ) );
	}


	public function testSortMultiple()
	{
		$this->assertEquals( 1, count( $this->object->sort( 'locale.languageid,locale.status' )->search() ) );
	}
}
