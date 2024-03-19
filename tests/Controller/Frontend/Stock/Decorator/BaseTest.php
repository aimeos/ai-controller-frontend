<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2017-2024
 */


namespace Aimeos\Controller\Frontend\Stock\Decorator;


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

		$this->stub = $this->getMockBuilder( \Aimeos\Controller\Frontend\Stock\Standard::class )
			->disableOriginalConstructor()
			->getMock();

		$this->object = new \Aimeos\Controller\Frontend\Stock\Decorator\Example( $this->stub, $this->context );
	}


	protected function tearDown() : void
	{
		unset( $this->context, $this->object, $this->stub );
	}


	public function testCall()
	{
		$stub = $this->getMockBuilder( \Aimeos\Controller\Frontend\Stock\Standard::class )
			->disableOriginalConstructor()
			->onlyMethods( ['__call'] )
			->getMock();

		$object = new \Aimeos\Controller\Frontend\Stock\Decorator\Example( $stub, $this->context );

		$stub->expects( $this->once() )->method( '__call' )->willReturn( true );

		$this->assertTrue( $object->invalid() );
	}


	public function testProduct()
	{
		$this->assertSame( $this->object, $this->object->product( ['123', '456'] ) );
	}


	public function testCompare()
	{
		$this->assertSame( $this->object, $this->object->compare( '==', 'stock.dateback', '2000-01-01 00:00:00' ) );
	}


	public function testGet()
	{
		$item = \Aimeos\MShop::create( $this->context, 'stock' )->create();
		$expected = \Aimeos\MShop\Stock\Item\Iface::class;

		$this->stub->expects( $this->once() )->method( 'get' )
			->willReturn( $item );

		$this->assertInstanceOf( $expected, $this->object->get( 1 ) );
	}


	public function testParse()
	{
		$this->assertSame( $this->object, $this->object->parse( [] ) );
	}


	public function testSearch()
	{
		$item = \Aimeos\MShop::create( $this->context, 'stock' )->create();

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
		$this->assertSame( $this->object, $this->object->sort( 'stock' ) );
	}


	public function testGetController()
	{
		$this->assertSame( $this->stub, $this->access( 'getController' )->invokeArgs( $this->object, [] ) );
	}


	protected function access( $name )
	{
		$class = new \ReflectionClass( \Aimeos\Controller\Frontend\Stock\Decorator\Base::class );
		$method = $class->getMethod( $name );
		$method->setAccessible( true );

		return $method;
	}
}
