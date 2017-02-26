<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2017
 */


namespace Aimeos\Controller\Frontend\Product\Decorator;


class BaseTest extends \PHPUnit_Framework_TestCase
{
	private $context;
	private $object;
	private $stub;


	protected function setUp()
	{
		$this->context = \TestHelperFrontend::getContext();

		$this->stub = $this->getMockBuilder( '\Aimeos\Controller\Frontend\Product\Standard' )
			->disableOriginalConstructor()
			->getMock();

		$this->object = $this->getMockBuilder( '\Aimeos\Controller\Frontend\Product\Decorator\Base' )
			->setConstructorArgs( [$this->stub, $this->context] )
			->getMockForAbstractClass();
	}


	protected function tearDown()
	{
		unset( $this->context, $this->object, $this->stub );
	}


	public function testConstructException()
	{
		$stub = $this->getMockBuilder( '\Aimeos\Controller\Frontend\Iface' )->getMock();

		$this->setExpectedException( '\Aimeos\Controller\Frontend\Exception' );

		$this->getMockBuilder( '\Aimeos\Controller\Frontend\Product\Decorator\Base' )
			->setConstructorArgs( [$stub, $this->context] )
			->getMockForAbstractClass();
	}


	public function testCall()
	{
		$stub = $this->getMockBuilder( '\Aimeos\Controller\Frontend\Product\Standard' )
			->disableOriginalConstructor()
			->setMethods( ['invalid'] )
			->getMock();

		$object = $this->getMockBuilder( '\Aimeos\Controller\Frontend\Product\Decorator\Base' )
			->setConstructorArgs( [$stub, $this->context] )
			->getMockForAbstractClass();

		$stub->expects( $this->once() )->method( 'invalid' )->will( $this->returnValue( true ) );

		$this->assertTrue( $object->invalid() );
	}


	public function testAddFilterAttribute()
	{
		$search = \Aimeos\MShop\Factory::createManager( $this->context, 'index' )->createSearch();

		$this->stub->expects( $this->once() )->method( 'addFilterAttribute' )
			->will( $this->returnArgument( 0 ) );

		$this->assertInstanceOf( '\Aimeos\MW\Criteria\Iface', $this->object->addFilterAttribute( $search, [], [], [] ) );
	}


	public function testAddFilterCategory()
	{
		$search = \Aimeos\MShop\Factory::createManager( $this->context, 'index' )->createSearch();

		$this->stub->expects( $this->once() )->method( 'addFilterCategory' )
			->will( $this->returnArgument( 0 ) );

		$this->assertInstanceOf( '\Aimeos\MW\Criteria\Iface', $this->object->addFilterCategory( $search, -1 ) );
	}


	public function testAddFilterText()
	{
		$search = \Aimeos\MShop\Factory::createManager( $this->context, 'index' )->createSearch();

		$this->stub->expects( $this->once() )->method( 'addFilterText' )
			->will( $this->returnArgument( 0 ) );

		$this->assertInstanceOf( '\Aimeos\MW\Criteria\Iface', $this->object->addFilterText( $search, 'test' ) );
	}


	public function testAggregate()
	{
		$search = \Aimeos\MShop\Factory::createManager( $this->context, 'index' )->createSearch();

		$this->stub->expects( $this->once() )->method( 'aggregate' )
			->will( $this->returnValue( [] ) );

		$this->assertEquals( [], $this->object->aggregate( $search, 'test' ) );
	}


	public function testCreateFilter()
	{
		$search = \Aimeos\MShop\Factory::createManager( $this->context, 'index' )->createSearch();

		$this->stub->expects( $this->once() )->method( 'createFilter' )
			->will( $this->returnValue( $search ) );

		$this->assertInstanceOf( '\Aimeos\MW\Criteria\Iface', $this->object->createFilter() );
	}


	public function testGetItem()
	{
		$prodItem = \Aimeos\MShop\Factory::createManager( $this->context, 'index' )->createItem();

		$this->stub->expects( $this->once() )->method( 'getItem' )
			->will( $this->returnValue( $prodItem ) );

		$this->assertInstanceOf( '\Aimeos\MShop\Product\Item\Iface', $this->object->getItem( -1 ) );
	}


	public function testGetItems()
	{
		$this->stub->expects( $this->once() )->method( 'getItems' )
			->will( $this->returnValue( [] ) );

		$this->assertEquals( [], $this->object->getItems( [-1] ) );
	}


	public function testSearchItems()
	{
		$search = \Aimeos\MShop\Factory::createManager( $this->context, 'index' )->createSearch();

		$this->stub->expects( $this->once() )->method( 'searchItems' )
			->will( $this->returnValue( [] ) );

		$this->assertEquals( [], $this->object->searchItems( $search ) );
	}


	public function testGetController()
	{
		$result = $this->access( 'getController' )->invokeArgs( $this->object, [] );

		$this->assertSame( $this->stub, $result );
	}


	protected function access( $name )
	{
		$class = new \ReflectionClass( '\Aimeos\Controller\Frontend\Product\Decorator\Base' );
		$method = $class->getMethod( $name );
		$method->setAccessible( true );

		return $method;
	}
}
