<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2017-2018
 */


namespace Aimeos\Controller\Frontend\Product\Decorator;


class BaseTest extends \PHPUnit\Framework\TestCase
{
	private $context;
	private $object;
	private $stub;


	protected function setUp()
	{
		$this->context = \TestHelperFrontend::getContext();

		$this->stub = $this->getMockBuilder( \Aimeos\Controller\Frontend\Product\Standard::class )
			->disableOriginalConstructor()
			->getMock();

		$this->object = $this->getMockBuilder( \Aimeos\Controller\Frontend\Product\Decorator\Base::class )
			->setConstructorArgs( [$this->stub, $this->context] )
			->getMockForAbstractClass();
	}


	protected function tearDown()
	{
		unset( $this->context, $this->object, $this->stub );
	}


	public function testConstructException()
	{
		$stub = $this->getMockBuilder( \Aimeos\Controller\Frontend\Iface::class )->getMock();

		$this->setExpectedException( \Aimeos\MW\Common\Exception::class );

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
			->will( $this->returnValue( [] ) );

		$this->assertEquals( [], $this->object->aggregate( 'test' ) );
	}


	public function testAllOf()
	{
		$expected = \Aimeos\Controller\Frontend\Product\Iface::class;

		$this->stub->expects( $this->once() )->method( 'allOf' )
			->will( $this->returnValue( $this->stub ) );

		$this->assertInstanceOf( $expected, $this->object->allOf( [1, 2] ) );
	}


	public function testCategory()
	{
		$expected = \Aimeos\Controller\Frontend\Product\Iface::class;

		$this->stub->expects( $this->once() )->method( 'category' )
			->will( $this->returnValue( $this->stub ) );

		$this->assertInstanceOf( $expected, $this->object->category( 1, 'default', \Aimeos\MW\Tree\Manager\Base::LEVEL_TREE ) );
	}


	public function testCompare()
	{
		$expected = \Aimeos\Controller\Frontend\Product\Iface::class;

		$this->stub->expects( $this->once() )->method( 'compare' )
			->will( $this->returnValue( $this->stub ) );

		$this->assertInstanceOf( $expected, $this->object->compare( '==', 'product.code', 'test' ) );
	}


	public function testFind()
	{
		$item = \Aimeos\MShop::create( $this->context, 'product' )->createItem();
		$expected = \Aimeos\MShop\Product\Item\Iface::class;

		$this->stub->expects( $this->once() )->method( 'find' )
			->will( $this->returnValue( $item ) );

		$this->assertInstanceOf( $expected, $this->object->find( 'test', ['text'] ) );
	}


	public function testGet()
	{
		$item = \Aimeos\MShop::create( $this->context, 'product' )->createItem();
		$expected = \Aimeos\MShop\Product\Item\Iface::class;

		$this->stub->expects( $this->once() )->method( 'get' )
			->will( $this->returnValue( $item ) );

		$this->assertInstanceOf( $expected, $this->object->get( 1, ['text'] ) );
	}


	public function testHas()
	{
		$expected = \Aimeos\Controller\Frontend\Product\Iface::class;

		$this->stub->expects( $this->once() )->method( 'has' )
			->will( $this->returnValue( $this->stub ) );

		$this->assertInstanceOf( $expected, $this->object->has( 'attribute', 'default', -1 ) );
	}


	public function testOneOf()
	{
		$expected = \Aimeos\Controller\Frontend\Product\Iface::class;

		$this->stub->expects( $this->once() )->method( 'oneOf' )
			->will( $this->returnValue( $this->stub ) );

		$this->assertInstanceOf( $expected, $this->object->oneOf( [1, 2] ) );
	}


	public function testParse()
	{
		$expected = \Aimeos\Controller\Frontend\Product\Iface::class;

		$this->stub->expects( $this->once() )->method( 'parse' )
			->will( $this->returnValue( $this->stub ) );

		$this->assertInstanceOf( $expected, $this->object->parse( [] ) );
	}


	public function testProduct()
	{
		$expected = \Aimeos\Controller\Frontend\Product\Iface::class;

		$this->stub->expects( $this->once() )->method( 'product' )
			->will( $this->returnValue( $this->stub ) );

		$this->assertInstanceOf( $expected, $this->object->product( [1, 3] ) );
	}


	public function testProperty()
	{
		$expected = \Aimeos\Controller\Frontend\Product\Iface::class;

		$this->stub->expects( $this->once() )->method( 'property' )
			->will( $this->returnValue( $this->stub ) );

		$this->assertInstanceOf( $expected, $this->object->property( 'test', 'value' ) );
	}


	public function testSearch()
	{
		$item = \Aimeos\MShop::create( $this->context, 'product' )->createItem();
		$expected = \Aimeos\MShop\Product\Item\Iface::class;
		$total = 0;

		$this->stub->expects( $this->once() )->method( 'search' )
			->will( $this->returnValue( [$item] ) );

		$this->assertEquals( [$item], $this->object->search( ['text'], $total ) );
	}


	public function testSlice()
	{
		$expected = \Aimeos\Controller\Frontend\Product\Iface::class;

		$this->stub->expects( $this->once() )->method( 'slice' )
			->will( $this->returnValue( $this->stub ) );

		$this->assertInstanceOf( $expected, $this->object->slice( 0, 100 ) );
	}


	public function testSort()
	{
		$expected = \Aimeos\Controller\Frontend\Product\Iface::class;

		$this->stub->expects( $this->once() )->method( 'sort' )
			->will( $this->returnValue( $this->stub ) );

		$this->assertInstanceOf( $expected, $this->object->sort( 'code' ) );
	}


	public function testSupplier()
	{
		$expected = \Aimeos\Controller\Frontend\Product\Iface::class;

		$this->stub->expects( $this->once() )->method( 'supplier' )
			->will( $this->returnValue( $this->stub ) );

		$this->assertInstanceOf( $expected, $this->object->supplier( [1], 'default' ) );
	}


	public function testText()
	{
		$expected = \Aimeos\Controller\Frontend\Product\Iface::class;

		$this->stub->expects( $this->once() )->method( 'text' )
			->will( $this->returnValue( $this->stub ) );

		$this->assertInstanceOf( $expected, $this->object->text( 'test' ) );
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
