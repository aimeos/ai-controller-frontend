<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2017-2024
 */


namespace Aimeos\Controller\Frontend\Catalog\Decorator;


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

		$this->stub = $this->getMockBuilder( \Aimeos\Controller\Frontend\Catalog\Standard::class )
			->disableOriginalConstructor()
			->getMock();

		$this->object = new \Aimeos\Controller\Frontend\Catalog\Decorator\Example( $this->stub, $this->context );
	}


	protected function tearDown() : void
	{
		unset( $this->context, $this->object, $this->stub );
	}


	public function testCall()
	{
		$stub = $this->getMockBuilder( \Aimeos\Controller\Frontend\Catalog\Standard::class )
			->disableOriginalConstructor()
			->onlyMethods( ['__call'] )
			->getMock();

		$object = new \Aimeos\Controller\Frontend\Catalog\Decorator\Example( $stub, $this->context );

		$stub->expects( $this->once() )->method( '__call' )->willReturn( true );

		$this->assertTrue( $object->invalid() );
	}


	public function testCompare()
	{
		$this->assertSame( $this->object, $this->object->compare( '==', 'catalog.code', 'test' ) );
	}


	public function testFind()
	{
		$item = \Aimeos\MShop::create( $this->context, 'catalog' )->create();
		$expected = \Aimeos\MShop\Catalog\Item\Iface::class;

		$this->stub->expects( $this->once() )->method( 'find' )
			->willReturn( $item );

		$this->assertInstanceOf( $expected, $this->object->find( 'test', ['text'] ) );
	}


	public function testFunction()
	{
		$this->stub->expects( $this->once() )->method( 'function' )
			->willReturn( 'catalog:has("domain","type","refid")' );

		$str = $this->object->function( 'catalog:has', ['domain', 'type', 'refid'] );
		$this->assertEquals( 'catalog:has("domain","type","refid")', $str );
	}


	public function testGet()
	{
		$item = \Aimeos\MShop::create( $this->context, 'catalog' )->create();
		$expected = \Aimeos\MShop\Catalog\Item\Iface::class;

		$this->stub->expects( $this->once() )->method( 'get' )
			->willReturn( $item );

		$this->assertInstanceOf( $expected, $this->object->get( 1, ['text'] ) );
	}


	public function testHas()
	{
		$this->assertSame( $this->object, $this->object->has( 'media', 'default', -1 ) );
	}


	public function testParse()
	{
		$this->assertSame( $this->object, $this->object->parse( [] ) );
	}


	public function testGetPath()
	{
		$this->stub->expects( $this->once() )->method( 'getPath' )
			->willReturn( [] );

		$this->assertEquals( [], $this->object->getPath( -1 ) );
	}


	public function testGetTree()
	{
		$catItem = \Aimeos\MShop::create( $this->context, 'catalog' )->create();

		$this->stub->expects( $this->once() )->method( 'getTree' )
			->willReturn( $catItem );

		$this->assertInstanceOf( \Aimeos\MShop\Catalog\Item\Iface::class, $this->object->getTree() );
	}


	public function testResolve()
	{
		$item = \Aimeos\MShop::create( $this->context, 'catalog' )->create();

		$this->stub->expects( $this->once() )->method( 'resolve' )
			->willReturn( $item );

		$this->assertEquals( $item, $this->object->resolve( 'test' ) );
	}


	public function testRoot()
	{
		$this->assertSame( $this->object, $this->object->root( -1 ) );
	}


	public function testSearch()
	{
		$total = 0;
		$item = \Aimeos\MShop::create( $this->context, 'catalog' )->create();

		$this->stub->expects( $this->once() )->method( 'search' )
			->willReturn( map( [$item] ) );

		$this->assertEquals( [$item], $this->object->search( $total )->toArray() );
	}


	public function testSlice()
	{
		$this->assertSame( $this->object, $this->object->slice( 0, 1 ) );
	}


	public function testSort()
	{
		$this->assertSame( $this->object, $this->object->sort( 'catalog.label' ) );
	}


	public function testVisible()
	{
		$this->assertSame( $this->object, $this->object->visible( [1, 2] ) );
	}


	public function testGetController()
	{
		$result = $this->access( 'getController' )->invokeArgs( $this->object, [] );

		$this->assertSame( $this->stub, $result );
	}


	protected function access( $name )
	{
		$class = new \ReflectionClass( \Aimeos\Controller\Frontend\Catalog\Decorator\Base::class );
		$method = $class->getMethod( $name );
		$method->setAccessible( true );

		return $method;
	}
}
