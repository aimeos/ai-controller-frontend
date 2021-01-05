<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2018-2021
 */


namespace Aimeos\Controller\Frontend\Review\Decorator;


class BaseTest extends \PHPUnit\Framework\TestCase
{
	private $context;
	private $object;
	private $stub;


	protected function setUp() : void
	{
		$this->context = \TestHelperFrontend::getContext();

		$this->stub = $this->getMockBuilder( \Aimeos\Controller\Frontend\Review\Standard::class )
			->disableOriginalConstructor()
			->getMock();

		$this->object = $this->getMockBuilder( \Aimeos\Controller\Frontend\Review\Decorator\Base::class )
			->setConstructorArgs( [$this->stub, $this->context] )
			->getMockForAbstractClass();
	}


	protected function tearDown() : void
	{
		unset( $this->context, $this->object, $this->stub );
	}


	public function testConstructException()
	{
		$stub = $this->getMockBuilder( \Aimeos\Controller\Frontend\Iface::class )->getMock();

		$this->expectException( \Aimeos\MW\Common\Exception::class );

		$this->getMockBuilder( \Aimeos\Controller\Frontend\Review\Decorator\Base::class )
			->setConstructorArgs( [$stub, $this->context] )
			->getMockForAbstractClass();
	}


	public function testCall()
	{
		$stub = $this->getMockBuilder( \Aimeos\Controller\Frontend\Review\Standard::class )
			->disableOriginalConstructor()
			->setMethods( ['invalid'] )
			->getMock();

		$object = $this->getMockBuilder( \Aimeos\Controller\Frontend\Review\Decorator\Base::class )
			->setConstructorArgs( [$stub, $this->context] )
			->getMockForAbstractClass();

		$stub->expects( $this->once() )->method( 'invalid' )->will( $this->returnValue( true ) );

		$this->assertTrue( $object->invalid() );
	}


	public function testAggregate()
	{
		$this->stub->expects( $this->once() )->method( 'aggregate' )
			->will( $this->returnValue( map() ) );

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
			->will( $this->returnValue( $item ) );

		$this->assertInstanceOf( \Aimeos\MShop\Review\Item\Iface::class, $this->object->get( -1 ) );
	}


	public function testList()
	{
		$item = \Aimeos\MShop::create( $this->context, 'review' )->create();

		$this->stub->expects( $this->once() )->method( 'list' )
			->will( $this->returnValue( map( [$item] ) ) );

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
			->will( $this->returnValue( $item ) );

		$this->assertInstanceOf( \Aimeos\MShop\Review\Item\Iface::class, $this->object->save( $item ) );
	}


	public function testSearch()
	{
		$item = \Aimeos\MShop::create( $this->context, 'review' )->create();

		$this->stub->expects( $this->once() )->method( 'search' )
			->will( $this->returnValue( map( [$item] ) ) );

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
