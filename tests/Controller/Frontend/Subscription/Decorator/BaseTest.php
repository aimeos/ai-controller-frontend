<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2018-2024
 */


namespace Aimeos\Controller\Frontend\Subscription\Decorator;


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

		$this->stub = $this->getMockBuilder( \Aimeos\Controller\Frontend\Subscription\Standard::class )
			->disableOriginalConstructor()
			->getMock();

		$this->object = new \Aimeos\Controller\Frontend\Subscription\Decorator\Example( $this->stub, $this->context );
	}


	protected function tearDown() : void
	{
		unset( $this->context, $this->object, $this->stub );
	}


	public function testCall()
	{
		$stub = $this->getMockBuilder( \Aimeos\Controller\Frontend\Subscription\Standard::class )
			->disableOriginalConstructor()
			->onlyMethods( ['__call'] )
			->getMock();

		$object = new \Aimeos\Controller\Frontend\Subscription\Decorator\Example( $stub, $this->context );

		$stub->expects( $this->once() )->method( '__call' )->willReturn( true );

		$this->assertTrue( $object->invalid() );
	}


	public function testCancel()
	{
		$item = \Aimeos\MShop::create( $this->context, 'subscription' )->create();

		$this->stub->expects( $this->once() )->method( 'cancel' )
			->willReturn( $item );

		$this->assertInstanceOf( \Aimeos\MShop\Subscription\Item\Iface::class, $this->object->cancel( -1 ) );
	}


	public function testCompare()
	{
		$this->assertSame( $this->object, $this->object->compare( '==', 'supplier.status', 1 ) );
	}


	public function testGet()
	{
		$item = \Aimeos\MShop::create( $this->context, 'subscription' )->create();

		$this->stub->expects( $this->once() )->method( 'get' )
			->willReturn( $item );

		$this->assertInstanceOf( \Aimeos\MShop\Subscription\Item\Iface::class, $this->object->get( -1 ) );
	}


	public function testGetIntervals()
	{
		$this->stub->expects( $this->once() )->method( 'getIntervals' )
			->willReturn( map() );

		$this->assertInstanceOf( \Aimeos\Map::class, $this->object->getIntervals() );
	}


	public function testParse()
	{
		$this->assertSame( $this->object, $this->object->parse( [] ) );
	}


	public function testSave()
	{
		$item = \Aimeos\MShop::create( $this->context, 'subscription' )->create();

		$this->stub->expects( $this->once() )->method( 'save' )
			->willReturn( $item );

		$this->assertInstanceOf( \Aimeos\MShop\Subscription\Item\Iface::class, $this->object->save( $item ) );
	}


	public function testSearch()
	{
		$item = \Aimeos\MShop::create( $this->context, 'subscription' )->create();

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


	public function testUses()
	{
		$this->assertSame( $this->object, $this->object->uses( ['order'] ) );
	}


	public function testGetController()
	{
		$this->assertSame( $this->stub, $this->access( 'getController' )->invokeArgs( $this->object, [] ) );
	}


	protected function access( $name )
	{
		$class = new \ReflectionClass( \Aimeos\Controller\Frontend\Subscription\Decorator\Base::class );
		$method = $class->getMethod( $name );
		$method->setAccessible( true );

		return $method;
	}
}
