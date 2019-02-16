<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2017-2018
 */


namespace Aimeos\Controller\Frontend\Attribute\Decorator;


class BaseTest extends \PHPUnit\Framework\TestCase
{
	private $context;
	private $object;
	private $stub;


	protected function setUp()
	{
		$this->context = \TestHelperFrontend::getContext();

		$this->stub = $this->getMockBuilder( \Aimeos\Controller\Frontend\Attribute\Standard::class )
			->disableOriginalConstructor()
			->getMock();

		$this->object = $this->getMockBuilder( \Aimeos\Controller\Frontend\Attribute\Decorator\Base::class )
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

		$this->getMockBuilder( \Aimeos\Controller\Frontend\Attribute\Decorator\Base::class )
			->setConstructorArgs( [$stub, $this->context] )
			->getMockForAbstractClass();
	}


	public function testCall()
	{
		$stub = $this->getMockBuilder( \Aimeos\Controller\Frontend\Attribute\Standard::class )
			->disableOriginalConstructor()
			->setMethods( ['invalid'] )
			->getMock();

		$object = $this->getMockBuilder( \Aimeos\Controller\Frontend\Attribute\Decorator\Base::class )
			->setConstructorArgs( [$stub, $this->context] )
			->getMockForAbstractClass();

		$stub->expects( $this->once() )->method( 'invalid' )->will( $this->returnValue( true ) );

		$this->assertTrue( $object->invalid() );
	}


	public function testAttribute()
	{
		$expected = \Aimeos\Controller\Frontend\Attribute\Iface::class;

		$this->stub->expects( $this->once() )->method( 'attribute' )
			->will( $this->returnValue( $this->stub ) );

		$this->assertInstanceOf( $expected, $this->object->attribute( [1, 3] ) );
	}


	public function testDomain()
	{
		$expected = \Aimeos\Controller\Frontend\Attribute\Iface::class;

		$this->stub->expects( $this->once() )->method( 'domain' )
			->will( $this->returnValue( $this->stub ) );

		$this->assertInstanceOf( $expected, $this->object->domain( 'catalog' ) );
	}


	public function testCompare()
	{
		$expected = \Aimeos\Controller\Frontend\Attribute\Iface::class;

		$this->stub->expects( $this->once() )->method( 'compare' )
			->will( $this->returnValue( $this->stub ) );

		$this->assertInstanceOf( $expected, $this->object->compare( '==', 'attribute.code', 'test' ) );
	}


	public function testFind()
	{
		$item = \Aimeos\MShop::create( $this->context, 'attribute' )->createItem();
		$expected = \Aimeos\MShop\Attribute\Item\Iface::class;

		$this->stub->expects( $this->once() )->method( 'find' )
			->will( $this->returnValue( $item ) );

		$this->assertInstanceOf( $expected, $this->object->find( 'test', ['text'], 'color' ) );
	}


	public function testGet()
	{
		$item = \Aimeos\MShop::create( $this->context, 'attribute' )->createItem();
		$expected = \Aimeos\MShop\Attribute\Item\Iface::class;

		$this->stub->expects( $this->once() )->method( 'get' )
			->will( $this->returnValue( $item ) );

		$this->assertInstanceOf( $expected, $this->object->get( 1, ['text'] ) );
	}


	public function testHas()
	{
		$expected = \Aimeos\Controller\Frontend\Attribute\Iface::class;

		$this->stub->expects( $this->once() )->method( 'has' )
			->will( $this->returnValue( $this->stub ) );

		$this->assertInstanceOf( $expected, $this->object->has( 'price', 'default', -1 ) );
	}


	public function testParse()
	{
		$expected = \Aimeos\Controller\Frontend\Attribute\Iface::class;

		$this->stub->expects( $this->once() )->method( 'parse' )
			->will( $this->returnValue( $this->stub ) );

		$this->assertInstanceOf( $expected, $this->object->parse( [] ) );
	}


	public function testProperty()
	{
		$expected = \Aimeos\Controller\Frontend\Attribute\Iface::class;

		$this->stub->expects( $this->once() )->method( 'property' )
			->will( $this->returnValue( $this->stub ) );

		$this->assertInstanceOf( $expected, $this->object->property( 'test', 'value' ) );
	}


	public function testSearch()
	{
		$item = \Aimeos\MShop::create( $this->context, 'attribute' )->createItem();
		$expected = \Aimeos\MShop\Attribute\Item\Iface::class;
		$total = 0;

		$this->stub->expects( $this->once() )->method( 'search' )
			->will( $this->returnValue( [$item] ) );

		$this->assertEquals( [$item], $this->object->search( ['text'], $total ) );
	}


	public function testSlice()
	{
		$expected = \Aimeos\Controller\Frontend\Attribute\Iface::class;

		$this->stub->expects( $this->once() )->method( 'slice' )
			->will( $this->returnValue( $this->stub ) );

		$this->assertInstanceOf( $expected, $this->object->slice( 0, 100 ) );
	}


	public function testSort()
	{
		$expected = \Aimeos\Controller\Frontend\Attribute\Iface::class;

		$this->stub->expects( $this->once() )->method( 'sort' )
			->will( $this->returnValue( $this->stub ) );

		$this->assertInstanceOf( $expected, $this->object->sort( 'position' ) );
	}


	public function testGetController()
	{
		$result = $this->access( 'getController' )->invokeArgs( $this->object, [] );

		$this->assertSame( $this->stub, $result );
	}


	protected function access( $name )
	{
		$class = new \ReflectionClass( \Aimeos\Controller\Frontend\Attribute\Decorator\Base::class );
		$method = $class->getMethod( $name );
		$method->setAccessible( true );

		return $method;
	}
}
