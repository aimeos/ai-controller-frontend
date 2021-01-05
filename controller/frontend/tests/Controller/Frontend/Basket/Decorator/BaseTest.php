<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2017-2021
 */


namespace Aimeos\Controller\Frontend\Basket\Decorator;


class BaseTest extends \PHPUnit\Framework\TestCase
{
	private $context;
	private $object;
	private $stub;


	protected function setUp() : void
	{
		$this->context = \TestHelperFrontend::getContext();

		$this->stub = $this->getMockBuilder( \Aimeos\Controller\Frontend\Basket\Standard::class )
			->disableOriginalConstructor()
			->getMock();

		$this->object = $this->getMockBuilder( \Aimeos\Controller\Frontend\Basket\Decorator\Base::class )
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

		$this->getMockBuilder( \Aimeos\Controller\Frontend\Basket\Decorator\Base::class )
			->setConstructorArgs( [$stub, $this->context] )
			->getMockForAbstractClass();
	}


	public function testCall()
	{
		$stub = $this->getMockBuilder( \Aimeos\Controller\Frontend\Basket\Standard::class )
			->disableOriginalConstructor()
			->setMethods( ['invalid'] )
			->getMock();

		$object = $this->getMockBuilder( \Aimeos\Controller\Frontend\Basket\Decorator\Base::class )
			->setConstructorArgs( [$stub, $this->context] )
			->getMockForAbstractClass();

		$stub->expects( $this->once() )->method( 'invalid' )->will( $this->returnValue( true ) );

		$this->assertTrue( $object->invalid() );
	}


	public function testAdd()
	{
		$this->stub->expects( $this->once() )->method( 'add' );
		$this->assertSame( $this->object, $this->object->add( [] ) );
	}


	public function testClear()
	{
		$this->stub->expects( $this->once() )->method( 'clear' );
		$this->assertSame( $this->object, $this->object->clear() );
	}


	public function testGet()
	{
		$context = \TestHelperFrontend::getContext();
		$order = \Aimeos\MShop::create( $context, 'order/base' )->create();

		$this->stub->expects( $this->once() )->method( 'get' )->will( $this->returnValue( $order ) );

		$this->assertInstanceOf( \Aimeos\MShop\Order\Item\Base\Iface::class, $this->object->get() );
	}


	public function testSave()
	{
		$this->stub->expects( $this->once() )->method( 'save' );
		$this->assertSame( $this->object, $this->object->save() );
	}


	public function testSetType()
	{
		$this->stub->expects( $this->once() )->method( 'setType' );
		$this->assertSame( $this->object, $this->object->setType( 'test' ) );
	}


	public function testStore()
	{
		$basket = \Aimeos\MShop::create( $this->context, 'order/base' )->create();

		$this->stub->expects( $this->once() )->method( 'store' )->will( $this->returnValue( $basket ) );

		$this->assertInstanceOf( \Aimeos\MShop\Order\Item\Base\Iface::class, $this->object->store() );
	}


	public function testLoad()
	{
		$basket = \Aimeos\MShop::create( $this->context, 'order/base' )->create();

		$this->stub->expects( $this->once() )->method( 'load' )->will( $this->returnValue( $basket ) );

		$this->assertInstanceOf( \Aimeos\MShop\Order\Item\Base\Iface::class, $this->object->load( -1 ) );
	}


	public function testAddProduct()
	{
		$product = \Aimeos\MShop::create( $this->context, 'product' )->create();

		$this->stub->expects( $this->once() )->method( 'addProduct' );

		$this->assertSame( $this->object, $this->object->addProduct( $product ) );
	}


	public function testDeleteProduct()
	{
		$this->stub->expects( $this->once() )->method( 'deleteProduct' );

		$this->assertSame( $this->object, $this->object->deleteProduct( 0 ) );
	}


	public function testUpdateProduct()
	{
		$this->stub->expects( $this->once() )->method( 'updateProduct' );

		$this->assertSame( $this->object, $this->object->updateProduct( 0, 1 ) );
	}


	public function testAddCoupon()
	{
		$this->stub->expects( $this->once() )->method( 'addCoupon' );

		$this->assertSame( $this->object, $this->object->addCoupon( 'test' ) );
	}


	public function testDeleteCoupon()
	{
		$this->stub->expects( $this->once() )->method( 'deleteCoupon' );

		$this->assertSame( $this->object, $this->object->deleteCoupon( 'test' ) );
	}


	public function testAddAddress()
	{
		$this->stub->expects( $this->once() )->method( 'addAddress' );

		$this->assertSame( $this->object, $this->object->addAddress( 'payment', [] ) );
	}


	public function testDeleteAddress()
	{
		$this->stub->expects( $this->once() )->method( 'deleteAddress' );

		$this->assertSame( $this->object, $this->object->deleteAddress( 'payment' ) );
	}


	public function testAddService()
	{
		$item = \Aimeos\MShop::create( $this->context, 'service' )->create()->setType( 'payment' );

		$this->stub->expects( $this->once() )->method( 'addService' );

		$this->assertSame( $this->object, $this->object->addService( $item ) );
	}


	public function testDeleteService()
	{
		$this->stub->expects( $this->once() )->method( 'deleteService' );

		$this->assertSame( $this->object, $this->object->deleteService( 'payment' ) );
	}


	public function testGetController()
	{
		$result = $this->access( 'getController' )->invokeArgs( $this->object, [] );

		$this->assertSame( $this->stub, $result );
	}


	protected function access( $name )
	{
		$class = new \ReflectionClass( \Aimeos\Controller\Frontend\Basket\Decorator\Base::class );
		$method = $class->getMethod( $name );
		$method->setAccessible( true );

		return $method;
	}
}
