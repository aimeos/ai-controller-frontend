<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2017
 */


namespace Aimeos\Controller\Frontend\Service\Decorator;


class BaseTest extends \PHPUnit_Framework_TestCase
{
	private $context;
	private $object;
	private $stub;


	protected function setUp()
	{
		$this->context = \TestHelperFrontend::getContext();

		$this->stub = $this->getMockBuilder( '\Aimeos\Controller\Frontend\Service\Standard' )
			->disableOriginalConstructor()
			->getMock();

		$this->object = $this->getMockBuilder( '\Aimeos\Controller\Frontend\Service\Decorator\Base' )
			->setConstructorArgs( [$this->stub, $this->context] )
			->getMockForAbstractClass();
	}


	protected function tearDown()
	{
		unset( $this->context, $this->object, $this->stub );
	}


	public function testGetServices()
	{
		$basket = \Aimeos\MShop\Factory::createManager( $this->context, 'order/base' )->createItem();

		$this->stub->expects( $this->once() )->method( 'getServices' )
			->will( $this->returnValue( [] ) );

		$this->assertEquals( [], $this->object->getServices( 'payment', $basket ) );
	}


	public function testGetServiceAttributes()
	{
		$basket = \Aimeos\MShop\Factory::createManager( $this->context, 'order/base' )->createItem();

		$this->stub->expects( $this->once() )->method( 'getServiceAttributes' )
			->will( $this->returnValue( [] ) );

		$this->assertEquals( [], $this->object->getServiceAttributes( 'payment', -1, $basket ) );
	}


	public function testGetServicePrice()
	{
		$priceItem = \Aimeos\MShop\Factory::createManager( $this->context, 'price' )->createItem();
		$basket = \Aimeos\MShop\Factory::createManager( $this->context, 'order/base' )->createItem();

		$this->stub->expects( $this->once() )->method( 'getServicePrice' )
			->will( $this->returnValue( $priceItem ) );

		$this->assertInstanceOf( '\Aimeos\MShop\Price\Item\Iface', $this->object->getServicePrice( 'payment', -1, $basket ) );
	}


	public function testCheckServiceAttributes()
	{
		$this->stub->expects( $this->once() )->method( 'checkServiceAttributes' )
			->will( $this->returnValue( [] ) );

		$this->assertEquals( [], $this->object->checkServiceAttributes( 'payment', -1, [] ) );
	}


	public function testGetContext()
	{
		$result = $this->access( 'getContext' )->invokeArgs( $this->object, [] );

		$this->assertInstanceOf( '\Aimeos\MShop\Context\Item\Iface', $result );
	}


	protected function access( $name )
	{
		$class = new \ReflectionClass( '\Aimeos\Controller\Frontend\Service\Decorator\Base' );
		$method = $class->getMethod( $name );
		$method->setAccessible( true );

		return $method;
	}
}
