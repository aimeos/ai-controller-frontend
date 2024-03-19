<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2018-2024
 */


namespace Aimeos\Controller\Frontend\Review\Decorator;


class Example extends Base
{
}


class BaseTest extends \PHPUnit\Framework\TestCase
{
	private $context;
	private $object;
	private $stub;


	protected function setUp() : void
	{
		$this->context = \TestHelper::context();

		$this->stub = $this->getMockBuilder( \Aimeos\Controller\Frontend\Review\Standard::class )
			->disableOriginalConstructor()
			->getMock();

		$this->object = new \Aimeos\Controller\Frontend\Review\Decorator\Example( $this->stub, $this->context );
	}


	protected function tearDown() : void
	{
		unset( $this->context, $this->object, $this->stub );
	}


	public function testCall()
	{
		$stub = $this->getMockBuilder( \Aimeos\Controller\Frontend\Review\Standard::class )
			->disableOriginalConstructor()
			->onlyMethods( ['__call'] )
			->getMock();

		$object = new \Aimeos\Controller\Frontend\Review\Decorator\Example( $stub, $this->context );

		$stub->expects( $this->once() )->method( '__call' )->willReturn( true );

		$this->assertTrue( $object->invalid() );
	}


	public function testAggregate()
	{
		$this->stub->expects( $this->once() )->method( 'aggregate' )
			->willReturn( map() );

		$this->assertEquals( [], $this->object->aggregate( 'test' )->toArray() );
	}


	public function testCompare()
	{
		$this->assertSame( $this->object, $this->object->compare( '==', 'supplier.status', 1 ) );
	}


	public function testCreate()
	{
		$result = $this->object->create( ['review.rating' => 5] );
		$this->assertInstanceOf( \Aimeos\MShop\Review\Item\Iface::class, $result );
	}


	public function testDelete()
	{
		$this->stub->expects( $this->once() )->method( 'delete' );

		$this->assertSame( $this->object, $this->object->delete( '-1' ) );
	}


	public function testDomain()
	{
		$this->assertSame( $this->object, $this->object->domain( 'product' ) );
	}


	public function testFor()
	{
		$this->assertSame( $this->object, $this->object->for( 'product', '-1' ) );
	}


	public function testGet()
	{
		$item = \Aimeos\MShop::create( $this->context, 'review' )->create();

		$this->stub->expects( $this->once() )->method( 'get' )
			->willReturn( $item );

		$this->assertInstanceOf( \Aimeos\MShop\Review\Item\Iface::class, $this->object->get( -1 ) );
	}


	public function testList()
	{
		$item = \Aimeos\MShop::create( $this->context, 'review' )->create();

		$this->stub->expects( $this->once() )->method( 'list' )
			->willReturn( map( [$item] ) );

		$this->assertEquals( [$item], $this->object->list()->toArray() );
	}


	public function testParse()
	{
		$this->assertSame( $this->object, $this->object->parse( [] ) );
	}


	public function testSave()
	{
		$item = \Aimeos\MShop::create( $this->context, 'review' )->create();

		$this->stub->expects( $this->once() )->method( 'save' )
			->willReturn( $item );

		$this->assertInstanceOf( \Aimeos\MShop\Review\Item\Iface::class, $this->object->save( $item ) );
	}


	public function testSearch()
	{
		$item = \Aimeos\MShop::create( $this->context, 'review' )->create();

		$this->stub->expects( $this->once() )->method( 'search' )
			->willReturn( map( [$item] ) );

		$this->assertEquals( [$item], $this->object->search()->toArray() );
	}


	public function testSlice()
	{
		$this->assertSame( $this->object, $this->object->slice( 0, 100 ) );
	}


	public function testSort()
	{
		$this->assertSame( $this->object, $this->object->sort( 'interval' ) );
	}


	public function testGetController()
	{
		$this->assertSame( $this->stub, $this->access( 'getController' )->invokeArgs( $this->object, [] ) );
	}


	protected function access( $name )
	{
		$class = new \ReflectionClass( \Aimeos\Controller\Frontend\Review\Decorator\Base::class );
		$method = $class->getMethod( $name );
		$method->setAccessible( true );

		return $method;
	}
}
