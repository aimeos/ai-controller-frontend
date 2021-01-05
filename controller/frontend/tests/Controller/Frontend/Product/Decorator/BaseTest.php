<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2017-2021
 */


namespace Aimeos\Controller\Frontend\Product\Decorator;


class BaseTest extends \PHPUnit\Framework\TestCase
{
	private $context;
	private $object;
	private $stub;


	protected function setUp() : void
	{
		$this->context = \TestHelperFrontend::getContext();

		$this->stub = $this->getMockBuilder( \Aimeos\Controller\Frontend\Product\Standard::class )
			->disableOriginalConstructor()
			->getMock();

		$this->object = $this->getMockBuilder( \Aimeos\Controller\Frontend\Product\Decorator\Base::class )
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

		$this->getMockBuilder( \Aimeos\Controller\Frontend\Product\Decorator\Base::class )
			->setConstructorArgs( [$stub, $this->context] )
			->getMockForAbstractClass();
	}


	public function testCall()
	{
		$stub = $this->getMockBuilder( \Aimeos\Controller\Frontend\Product\Standard::class )
			->disableOriginalConstructor()
			->setMethods( ['invalid'] )
			->getMock();

		$object = $this->getMockBuilder( \Aimeos\Controller\Frontend\Product\Decorator\Base::class )
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


	public function testAllOf()
	{
		$this->assertSame( $this->object, $this->object->allOf( [1, 2] ) );
	}


	public function testCategory()
	{
		$level = \Aimeos\MW\Tree\Manager\Base::LEVEL_TREE;
		$this->assertSame( $this->object, $this->object->category( 1, 'default', $level ) );
	}


	public function testCompare()
	{
		$this->assertSame( $this->object, $this->object->compare( '==', 'product.code', 'test' ) );
	}


	public function testFind()
	{
		$item = \Aimeos\MShop::create( $this->context, 'product' )->create();
		$expected = \Aimeos\MShop\Product\Item\Iface::class;

		$this->stub->expects( $this->once() )->method( 'find' )
			->will( $this->returnValue( $item ) );

		$this->assertInstanceOf( $expected, $this->object->find( 'test', ['text'] ) );
	}


	public function testFunction()
	{
		$this->stub->expects( $this->once() )->method( 'function' )
			->will( $this->returnValue( 'product:has("domain","type","refid")' ) );

		$str = $this->object->function( 'product:has', ['domain', 'type', 'refid'] );
		$this->assertEquals( 'product:has("domain","type","refid")', $str );
	}


	public function testGet()
	{
		$item = \Aimeos\MShop::create( $this->context, 'product' )->create();
		$expected = \Aimeos\MShop\Product\Item\Iface::class;

		$this->stub->expects( $this->once() )->method( 'get' )
			->will( $this->returnValue( $item ) );

		$this->assertInstanceOf( $expected, $this->object->get( 1, ['text'] ) );
	}


	public function testHas()
	{
		$this->assertSame( $this->object, $this->object->has( 'attribute', 'default', -1 ) );
	}


	public function testOneOf()
	{
		$this->assertSame( $this->object, $this->object->oneOf( [1, 2] ) );
	}


	public function testParse()
	{
		$this->assertSame( $this->object, $this->object->parse( [] ) );
	}


	public function testPrice()
	{
		$this->assertSame( $this->object, $this->object->price( 0 ) );
	}


	public function testProduct()
	{
		$this->assertSame( $this->object, $this->object->product( [1, 3] ) );
	}


	public function testProperty()
	{
		$this->assertSame( $this->object, $this->object->property( 'test', 'value' ) );
	}


	public function testResolve()
	{
		$item = \Aimeos\MShop::create( $this->context, 'product' )->create();

		$this->stub->expects( $this->once() )->method( 'resolve' )
			->will( $this->returnValue( $item ) );

		$this->assertEquals( $item, $this->object->resolve( 'test' ) );
	}


	public function testSearch()
	{
		$total = 0;
		$item = \Aimeos\MShop::create( $this->context, 'product' )->create();

		$this->stub->expects( $this->once() )->method( 'search' )
			->will( $this->returnValue( map( [$item] ) ) );

		$this->assertEquals( [$item], $this->object->search( $total )->toArray() );
	}


	public function testSlice()
	{
		$this->assertSame( $this->object, $this->object->slice( 0, 100 ) );
	}


	public function testSort()
	{
		$this->assertSame( $this->object, $this->object->sort( 'code' ) );
	}


	public function testSupplier()
	{
		$this->assertSame( $this->object, $this->object->supplier( [1], 'default' ) );
	}


	public function testText()
	{
		$this->assertSame( $this->object, $this->object->text( 'test' ) );
	}


	public function testUses()
	{
		$this->assertSame( $this->object, $this->object->uses( ['text'] ) );
	}


	public function testGetController()
	{
		$result = $this->access( 'getController' )->invokeArgs( $this->object, [] );

		$this->assertSame( $this->stub, $result );
	}


	protected function access( $name )
	{
		$class = new \ReflectionClass( \Aimeos\Controller\Frontend\Product\Decorator\Base::class );
		$method = $class->getMethod( $name );
		$method->setAccessible( true );

		return $method;
	}
}
