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


	public function testStore()
	{
		$orderItem = \Aimeos\MShop\Factory::createManager( $this->context, 'order' )->createItem();
		$basket = \Aimeos\MShop\Factory::createManager( $this->context, 'order/base' )->createItem();

		$this->stub->expects( $this->once() )->method( 'store' )
			->will( $this->returnValue( $orderItem ) );

		$this->assertInstanceOf( '\Aimeos\MShop\Order\Item\Iface', $this->object->store( $basket ) );
	}


	public function testBlock()
	{
		$orderItem = \Aimeos\MShop\Factory::createManager( $this->context, 'order' )->createItem();

		$this->stub->expects( $this->once() )->method( 'block' );

		$this->object->block( $orderItem );
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


	public function testGetController()
	{
		$result = $this->access( 'getController' )->invokeArgs( $this->object, [] );

		$this->assertSame( $this->stub, $result );
	}


	public function testGetContext()
	{
		$result = $this->access( 'getContext' )->invokeArgs( $this->object, [] );

		$this->assertInstanceOf( '\Aimeos\MShop\Context\Item\Iface', $result );
	}


	protected function access( $name )
	{
		$class = new \ReflectionClass( '\Aimeos\Controller\Frontend\Order\Decorator\Base' );
		$method = $class->getMethod( $name );
		$method->setAccessible( true );

		return $method;
	}
}
