<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2017
 */


namespace Aimeos\Controller\Frontend\Catalog\Decorator;


class BaseTest extends \PHPUnit_Framework_TestCase
{
	private $context;
	private $object;
	private $stub;


	protected function setUp()
	{
		$this->context = \TestHelperFrontend::getContext();

		$this->stub = $this->getMockBuilder( '\Aimeos\Controller\Frontend\Catalog\Standard' )
			->disableOriginalConstructor()
			->getMock();

		$this->object = $this->getMockBuilder( '\Aimeos\Controller\Frontend\Catalog\Decorator\Base' )
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

		$this->getMockBuilder( '\Aimeos\Controller\Frontend\Catalog\Decorator\Base' )
			->setConstructorArgs( [$stub, $this->context] )
			->getMockForAbstractClass();
	}


	public function testCall()
	{
		$stub = $this->getMockBuilder( '\Aimeos\Controller\Frontend\Catalog\Standard' )
			->disableOriginalConstructor()
			->setMethods( ['invalid'] )
			->getMock();

		$object = $this->getMockBuilder( '\Aimeos\Controller\Frontend\Catalog\Decorator\Base' )
			->setConstructorArgs( [$stub, $this->context] )
			->getMockForAbstractClass();

		$stub->expects( $this->once() )->method( 'invalid' )->will( $this->returnValue( true ) );

		$this->assertTrue( $object->invalid() );
	}


	public function testCreateManager()
	{
		$catalogManager = \Aimeos\MShop\Factory::createManager( $this->context, 'catalog' );

		$this->stub->expects( $this->once() )->method( 'createManager' )
			->will( $this->returnValue( $catalogManager ) );

		$this->assertInstanceOf( '\Aimeos\MShop\Common\Manager\Iface', $this->object->createManager( 'catalog' ) );
	}


	public function testCreateFilter()
	{
		$search = \Aimeos\MShop\Factory::createManager( $this->context, 'catalog' )->createSearch();

		$this->stub->expects( $this->once() )->method( 'createFilter' )
			->will( $this->returnValue( $search ) );

		$this->assertInstanceOf( '\Aimeos\MW\Criteria\Iface', $this->object->createFilter() );
	}


	public function testGetPath()
	{
		$this->stub->expects( $this->once() )->method( 'getPath' )
			->will( $this->returnValue( [] ) );

		$this->assertEquals( [], $this->object->getPath( -1 ) );
	}


	public function testGetTree()
	{
		$catItem = \Aimeos\MShop\Factory::createManager( $this->context, 'catalog' )->createItem();

		$this->stub->expects( $this->once() )->method( 'getTree' )
			->will( $this->returnValue( $catItem ) );

		$this->assertInstanceOf( '\Aimeos\MShop\Catalog\Item\Iface', $this->object->getTree() );
	}


	public function testCreateCatalogFilter()
	{
		$search = \Aimeos\MShop\Factory::createManager( $this->context, 'catalog' )->createSearch();

		$this->stub->expects( $this->once() )->method( 'createCatalogFilter' )
			->will( $this->returnValue( $search ) );

		$this->assertInstanceOf( '\Aimeos\MW\Criteria\Iface', $this->object->createCatalogFilter() );
	}


	public function testGetCatalogPath()
	{
		$this->stub->expects( $this->once() )->method( 'getCatalogPath' )
			->will( $this->returnValue( [] ) );

		$this->assertEquals( [], $this->object->getCatalogPath( -1 ) );
	}


	public function testGetCatalogTree()
	{
		$catItem = \Aimeos\MShop\Factory::createManager( $this->context, 'catalog' )->createItem();

		$this->stub->expects( $this->once() )->method( 'getCatalogTree' )
			->will( $this->returnValue( $catItem ) );

		$this->assertInstanceOf( '\Aimeos\MShop\Catalog\Item\Iface', $this->object->getCatalogTree() );
	}


	public function testAggregateIndex()
	{
		$search = \Aimeos\MShop\Factory::createManager( $this->context, 'index' )->createSearch();

		$this->stub->expects( $this->once() )->method( 'aggregateIndex' )
			->will( $this->returnValue( [] ) );

		$this->assertEquals( [], $this->object->aggregateIndex( $search, 'test' ) );
	}


	public function testAddIndexFilterCategory()
	{
		$search = \Aimeos\MShop\Factory::createManager( $this->context, 'index' )->createSearch();

		$this->stub->expects( $this->once() )->method( 'addIndexFilterCategory' )
			->will( $this->returnArgument( 0 ) );

		$this->assertInstanceOf( '\Aimeos\MW\Criteria\Iface', $this->object->addIndexFilterCategory( $search, -1 ) );
	}


	public function testAddIndexFilterText()
	{
		$search = \Aimeos\MShop\Factory::createManager( $this->context, 'index' )->createSearch();

		$this->stub->expects( $this->once() )->method( 'addIndexFilterText' )
			->will( $this->returnArgument( 0 ) );

		$this->assertInstanceOf( '\Aimeos\MW\Criteria\Iface', $this->object->addIndexFilterText( $search, 'test' ) );
	}


	public function testCreateIndexFilter()
	{
		$search = \Aimeos\MShop\Factory::createManager( $this->context, 'index' )->createSearch();

		$this->stub->expects( $this->once() )->method( 'createIndexFilter' )
			->will( $this->returnValue( $search ) );

		$this->assertInstanceOf( '\Aimeos\MW\Criteria\Iface', $this->object->createIndexFilter() );
	}


	public function testCreateIndexFilterCategory()
	{
		$search = \Aimeos\MShop\Factory::createManager( $this->context, 'index' )->createSearch();

		$this->stub->expects( $this->once() )->method( 'createIndexFilterCategory' )
			->will( $this->returnValue( $search ) );

		$this->assertInstanceOf( '\Aimeos\MW\Criteria\Iface', $this->object->createIndexFilterCategory( -1 ) );
	}


	public function testCreateIndexFilterText()
	{
		$search = \Aimeos\MShop\Factory::createManager( $this->context, 'index' )->createSearch();

		$this->stub->expects( $this->once() )->method( 'createIndexFilterText' )
			->will( $this->returnValue( $search ) );

		$this->assertInstanceOf( '\Aimeos\MW\Criteria\Iface', $this->object->createIndexFilterText( 'test' ) );
	}


	public function testGetIndexItems()
	{
		$search = \Aimeos\MShop\Factory::createManager( $this->context, 'index' )->createSearch();

		$this->stub->expects( $this->once() )->method( 'getIndexItems' )
			->will( $this->returnValue( [] ) );

		$this->assertEquals( [], $this->object->getIndexItems( $search ) );
	}


	public function testGetProductItems()
	{
		$this->stub->expects( $this->once() )->method( 'getProductItems' )
			->will( $this->returnValue( [] ) );

		$this->assertEquals( [], $this->object->getProductItems( [-1] ) );
	}


	public function testCreateTextFilter()
	{
		$search = \Aimeos\MShop\Factory::createManager( $this->context, 'index' )->createSearch();

		$this->stub->expects( $this->once() )->method( 'createTextFilter' )
			->will( $this->returnValue( $search ) );

		$this->assertInstanceOf( '\Aimeos\MW\Criteria\Iface', $this->object->createTextFilter( 'test' ) );
	}


	public function testGetTextList()
	{
		$search = \Aimeos\MShop\Factory::createManager( $this->context, 'index' )->createSearch();

		$this->stub->expects( $this->once() )->method( 'getTextList' )
			->will( $this->returnValue( [] ) );

		$this->assertEquals( [], $this->object->getTextList( $search ) );
	}


	public function testGetController()
	{
		$result = $this->access( 'getController' )->invokeArgs( $this->object, [] );

		$this->assertSame( $this->stub, $result );
	}


	protected function access( $name )
	{
		$class = new \ReflectionClass( '\Aimeos\Controller\Frontend\Catalog\Decorator\Base' );
		$method = $class->getMethod( $name );
		$method->setAccessible( true );

		return $method;
	}
}
