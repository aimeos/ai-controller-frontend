<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2017-2024
 */


namespace Aimeos\Controller\Frontend\Order\Decorator;


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

		$this->stub = $this->getMockBuilder( \Aimeos\Controller\Frontend\Order\Standard::class )
			->disableOriginalConstructor()
			->getMock();

		$this->object = new \Aimeos\Controller\Frontend\Order\Decorator\Example( $this->stub, $this->context );
	}


	protected function tearDown() : void
	{
		unset( $this->context, $this->object, $this->stub );
	}


	public function testCall()
	{
		$stub = $this->getMockBuilder( \Aimeos\Controller\Frontend\Order\Standard::class )
			->disableOriginalConstructor()
			->onlyMethods( ['__call'] )
			->getMock();

		$object = new \Aimeos\Controller\Frontend\Order\Decorator\Example( $stub, $this->context );

		$stub->expects( $this->once() )->method( '__call' )->willReturn( true );

		$this->assertTrue( $object->invalid() );
	}


	public function testCompare()
	{
		$this->assertSame( $this->object, $this->object->compare( '==', 'order.type', 'test' ) );
	}


	public function testGet()
	{
		$item = \Aimeos\MShop::create( $this->context, 'order' )->create();
		$expected = \Aimeos\MShop\Order\Item\Iface::class;

		$this->stub->expects( $this->once() )->method( 'get' )->willReturn( $item );

		$this->assertInstanceOf( $expected, $this->object->get( -1, false ) );
	}


	public function testParse()
	{
		$this->assertSame( $this->object, $this->object->parse( [] ) );
	}


	public function testSave()
	{
		$item = \Aimeos\MShop::create( $this->context, 'order' )->create();
		$expected = \Aimeos\MShop\Order\Item\Iface::class;

		$this->stub->expects( $this->once() )->method( 'save' )->willReturnArgument( 0 );

		$this->assertInstanceOf( $expected, $this->object->save( $item ) );
	}


	public function testSearch()
	{
		$total = 0;
		$item = \Aimeos\MShop::create( $this->context, 'order' )->create();

		$this->stub->expects( $this->once() )->method( 'search' )
			->willReturn( map( [$item] ) );

		$this->assertEquals( [$item], $this->object->search( $total )->toArray() );
	}


	public function testSlice()
	{
		$this->assertSame( $this->object, $this->object->slice( 0, 100 ) );
	}


	public function testSort()
	{
		$this->assertSame( $this->object, $this->object->sort( 'order.id' ) );
	}


	public function testUpdate()
	{
		$item = \Aimeos\MShop::create( $this->context, 'order' )->create();
		$this->assertSame( $this->object, $this->object->update( $item ) );
	}


	public function testUses()
	{
		$this->assertSame( $this->object, $this->object->uses( ['order'] ) );
	}


	public function testGetController()
	{
		$result = $this->access( 'getController' )->invokeArgs( $this->object, [] );

		$this->assertSame( $this->stub, $result );
	}


	protected function access( $name )
	{
		$class = new \ReflectionClass( \Aimeos\Controller\Frontend\Order\Decorator\Base::class );
		$method = $class->getMethod( $name );
		$method->setAccessible( true );

		return $method;
	}
}
