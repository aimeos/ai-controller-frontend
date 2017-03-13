<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2017
 */


namespace Aimeos\Controller\Frontend\Basket\Decorator;


class BaseTest extends \PHPUnit_Framework_TestCase
{
	private $context;
	private $object;
	private $stub;


	protected function setUp()
	{
		$this->context = \TestHelperFrontend::getContext();

		$this->stub = $this->getMockBuilder( '\Aimeos\Controller\Frontend\Basket\Standard' )
			->disableOriginalConstructor()
			->getMock();

		$this->object = $this->getMockBuilder( '\Aimeos\Controller\Frontend\Basket\Decorator\Base' )
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

		$this->getMockBuilder( '\Aimeos\Controller\Frontend\Basket\Decorator\Base' )
			->setConstructorArgs( [$stub, $this->context] )
			->getMockForAbstractClass();
	}


	public function testCall()
	{
		$stub = $this->getMockBuilder( '\Aimeos\Controller\Frontend\Basket\Standard' )
			->disableOriginalConstructor()
			->setMethods( ['invalid'] )
			->getMock();

		$object = $this->getMockBuilder( '\Aimeos\Controller\Frontend\Basket\Decorator\Base' )
			->setConstructorArgs( [$stub, $this->context] )
			->getMockForAbstractClass();

		$stub->expects( $this->once() )->method( 'invalid' )->will( $this->returnValue( true ) );

		$this->assertTrue( $object->invalid() );
	}


	public function testClear()
	{
		$this->stub->expects( $this->once() )->method( 'clear' );

		$this->object->clear();
	}


	public function testGet()
	{
		$context = \TestHelperFrontend::getContext();
		$order = \Aimeos\MShop\Factory::createManager( $context, 'order/base' )->createItem();

		$this->stub->expects( $this->once() )->method( 'get' )
			->will( $this->returnValue( $order ) );

		$this->assertInstanceOf( '\Aimeos\MShop\Order\Item\Base\Iface', $this->object->get() );
	}


	public function testSave()
	{
		$this->stub->expects( $this->once() )->method( 'save' );

		$this->object->save();
	}


	public function testSetType()
	{
		$this->stub->expects( $this->once() )->method( 'setType' );

		$this->object->setType( 'test' );
	}


	public function testStore()
	{
		$this->stub->expects( $this->once() )->method( 'store' );

		$this->object->store();
	}


	public function testLoad()
	{
		$this->stub->expects( $this->once() )->method( 'load' );

		$this->object->load( -1 );
	}


	public function testAddProduct()
	{
		$this->stub->expects( $this->once() )->method( 'addProduct' );

		$this->object->addProduct( -1 );
	}


	public function testDeleteProduct()
	{
		$this->stub->expects( $this->once() )->method( 'deleteProduct' );

		$this->object->deleteProduct( 0 );
	}


	public function testEditProduct()
	{
		$this->stub->expects( $this->once() )->method( 'editProduct' );

		$this->object->editProduct( 0, 1 );
	}


	public function testAddCoupon()
	{
		$this->stub->expects( $this->once() )->method( 'addCoupon' );

		$this->object->addCoupon( 'test' );
	}


	public function testDeleteCoupon()
	{
		$this->stub->expects( $this->once() )->method( 'deleteCoupon' );

		$this->object->deleteCoupon( 'test' );
	}


	public function testSetAddress()
	{
		$this->stub->expects( $this->once() )->method( 'setAddress' );

		$this->object->setAddress( \Aimeos\MShop\Order\Item\Base\Address\Base::TYPE_PAYMENT, null );
	}


	public function testSetService()
	{
		$this->stub->expects( $this->once() )->method( 'setService' );

		$this->object->setService( \Aimeos\MShop\Order\Item\Base\Service\Base::TYPE_PAYMENT, -1 );
	}


	public function testGetController()
	{
		$result = $this->access( 'getController' )->invokeArgs( $this->object, [] );

		$this->assertSame( $this->stub, $result );
	}


	protected function access( $name )
	{
		$class = new \ReflectionClass( '\Aimeos\Controller\Frontend\Basket\Decorator\Base' );
		$method = $class->getMethod( $name );
		$method->setAccessible( true );

		return $method;
	}
}
