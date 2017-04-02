<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2017
 */


namespace Aimeos\Controller\Frontend\Order\Decorator;


class BaseTest extends \PHPUnit_Framework_TestCase
{
	private $context;
	private $object;
	private $stub;


	protected function setUp()
	{
		$this->context = \TestHelperFrontend::getContext();

		$this->stub = $this->getMockBuilder( '\Aimeos\Controller\Frontend\Order\Standard' )
			->disableOriginalConstructor()
			->getMock();

		$this->object = $this->getMockBuilder( '\Aimeos\Controller\Frontend\Order\Decorator\Base' )
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

		$this->getMockBuilder( '\Aimeos\Controller\Frontend\Order\Decorator\Base' )
			->setConstructorArgs( [$stub, $this->context] )
			->getMockForAbstractClass();
	}


	public function testCall()
	{
		$stub = $this->getMockBuilder( '\Aimeos\Controller\Frontend\Order\Standard' )
			->disableOriginalConstructor()
			->setMethods( ['invalid'] )
			->getMock();

		$object = $this->getMockBuilder( '\Aimeos\Controller\Frontend\Order\Decorator\Base' )
			->setConstructorArgs( [$stub, $this->context] )
			->getMockForAbstractClass();

		$stub->expects( $this->once() )->method( 'invalid' )->will( $this->returnValue( true ) );

		$this->assertTrue( $object->invalid() );
	}


	public function testAddItem()
	{
		$item = \Aimeos\MShop\Factory::createManager( $this->context, 'order' )->createItem();

		$this->stub->expects( $this->once() )->method( 'addItem' )->will( $this->returnValue( $item ) );

		$this->assertInstanceOf( '\Aimeos\MShop\Order\Item\Iface', $this->object->addItem( -1, '' ) );
	}


	public function testCreateFilter()
	{
		$search = \Aimeos\MShop\Factory::createManager( $this->context, 'order' )->createSearch();

		$this->stub->expects( $this->once() )->method( 'createFilter' )->will( $this->returnValue( $search ) );

		$this->assertInstanceOf( '\Aimeos\MW\Criteria\Iface', $this->object->createFilter() );
	}


	public function testGetItem()
	{
		$item = \Aimeos\MShop\Factory::createManager( $this->context, 'order' )->createItem();

		$this->stub->expects( $this->once() )->method( 'getItem' )->will( $this->returnValue( $item ) );

		$this->assertInstanceOf( '\Aimeos\MShop\Order\Item\Iface', $this->object->getItem( -1 ) );
	}


	public function testSearchItems()
	{
		$search = $this->getMockBuilder( '\Aimeos\MW\Criteria\Iface' )->getMock();

		$this->stub->expects( $this->once() )->method( 'searchItems' )->will( $this->returnValue( [] ) );

		$this->assertEquals( [], $this->object->searchItems( $search ) );
	}


	public function testUnblock()
	{
		$orderItem = \Aimeos\MShop\Factory::createManager( $this->context, 'order' )->createItem();

		$this->stub->expects( $this->once() )->method( 'unblock' );

		$this->object->unblock( $orderItem );
	}


	public function testUpdate()
	{
		$orderItem = \Aimeos\MShop\Factory::createManager( $this->context, 'order' )->createItem();

		$this->stub->expects( $this->once() )->method( 'update' );

		$this->object->update( $orderItem );
	}


	public function testStore()
	{
		$orderItem = \Aimeos\MShop\Factory::createManager( $this->context, 'order' )->createItem();
		$basket = \Aimeos\MShop\Factory::createManager( $this->context, 'order/base' )->createItem();

		$this->stub->expects( $this->once() )->method( 'store' )
			->will( $this->returnValue( $orderItem ) );

		$this->assertInstanceOf( '\Aimeos\MShop\Order\Item\Iface', $this->object->store( $basket ) );
	}


	public function testGetController()
	{
		$result = $this->access( 'getController' )->invokeArgs( $this->object, [] );

		$this->assertSame( $this->stub, $result );
	}


	protected function access( $name )
	{
		$class = new \ReflectionClass( '\Aimeos\Controller\Frontend\Order\Decorator\Base' );
		$method = $class->getMethod( $name );
		$method->setAccessible( true );

		return $method;
	}
}
