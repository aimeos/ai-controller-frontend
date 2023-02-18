<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2017-2023
 */

namespace Aimeos\Controller\Frontend\Basket;


class BaseTest extends \PHPUnit\Framework\TestCase
{
	private $context;


	protected function setUp() : void
	{
		\Aimeos\MShop::cache( true );
		$this->context = \TestHelper::context();
	}


	protected function tearDown() : void
	{
		\Aimeos\MShop::cache( false );
		$this->context->session()->set( 'aimeos', [] );

		unset( $this->context );
	}


	public function testCheckLocale()
	{
		$object = $this->getMockBuilder( \Aimeos\Controller\Frontend\Basket\Standard::class )
			->setConstructorArgs( [$this->context] )
			->onlyMethods( [] )
			->getMock();

		$this->context->session()->set( 'aimeos/basket/locale', 'unittest|en|EUR' );
		$this->access( 'checkLocale' )->invokeArgs( $object, [$this->context->locale(), 'unittest'] );

		$this->assertEquals( 'unittest|de|EUR', $this->context->session()->get( 'aimeos/basket/locale' ) );
	}


	public function testCopyAddresses()
	{
		$manager = \Aimeos\MShop::create( \TestHelper::context(), 'order' );
		$ordBaseItem = $manager->create();

		$address = $this->getAddress( 'Example company' );
		$ordBaseItem->addAddress( $address, \Aimeos\MShop\Order\Item\Address\Base::TYPE_PAYMENT );


		$object = $this->getMockBuilder( \Aimeos\Controller\Frontend\Basket\Standard::class )
			->setConstructorArgs( [$this->context] )
			->onlyMethods( [] )
			->getMock();

		$result = $this->access( 'copyAddresses' )->invokeArgs( $object, [$ordBaseItem, ['test'], 'unittest|en|EUR'] );


		$this->assertEquals( ['test'], $result );
		$this->assertEquals( 1, count( $object->get()->getAddresses() ) );

		$addr = $object->get()->getAddress( \Aimeos\MShop\Order\Item\Address\Base::TYPE_PAYMENT, 0 );
		$this->assertInstanceOf( \Aimeos\MShop\Order\Item\Address\Iface::class, $addr );

		$object->clear();
	}


	public function testCopyAddressesException()
	{
		$manager = \Aimeos\MShop::create( \TestHelper::context(), 'order' );
		$ordBaseItem = $manager->create();

		$address = $this->getAddress( 'Example company' );
		$ordBaseItem->addAddress( $address, \Aimeos\MShop\Order\Item\Address\Base::TYPE_PAYMENT );


		$object = $this->getMockBuilder( \Aimeos\Controller\Frontend\Basket\Standard::class )
			->setConstructorArgs( [$this->context] )
			->onlyMethods( ['get'] )
			->getMock();

		$object->expects( $this->once() )->method( 'get' )->will( $this->throwException( new \Exception() ) );

		$result = $this->access( 'copyAddresses' )->invokeArgs( $object, [$ordBaseItem, [], 'unittest|en|EUR'] );

		$this->assertEquals( 1, count( $result ) );
		$this->assertArrayHasKey( 'address', $result );
	}


	public function testCopyCoupon()
	{
		$manager = \Aimeos\MShop::create( \TestHelper::context(), 'order' );
		$ordBaseItem = $manager->create();

		$product = \Aimeos\MShop::create( $this->context, 'product' )->find( 'CNC', ['price'] );
		$ordProdManager = \Aimeos\MShop::create( $this->context, 'order/product' );
		$ordProdItem = $ordProdManager->create()->copyFrom( $product )->setStockType( 'default' );

		$ordProdItem->setPrice( $product->getRefItems( 'price' )->first() );

		$ordBaseItem->addProduct( $ordProdItem );
		$ordBaseItem->addCoupon( 'OPQR', [] );


		$object = $this->getMockBuilder( \Aimeos\Controller\Frontend\Basket\Standard::class )
			->setConstructorArgs( [$this->context] )
			->onlyMethods( [] )
			->getMock();

		$object->addProduct( $product );

		$result = $this->access( 'copyCoupons' )->invokeArgs( $object, [$ordBaseItem, ['test'], 'unittest|en|EUR'] );


		$this->assertEquals( ['test'], $result );

		$object->clear();
	}


	public function testCopyCouponException()
	{
		$manager = \Aimeos\MShop::create( \TestHelper::context(), 'order' );
		$ordBaseItem = $manager->create();

		$ordBaseItem->addCoupon( '90AB', [] );


		$object = $this->getMockBuilder( \Aimeos\Controller\Frontend\Basket\Standard::class )
			->setConstructorArgs( [$this->context] )
			->onlyMethods( ['addCoupon'] )
			->getMock();

		$object->expects( $this->once() )->method( 'addCoupon' )->will( $this->throwException( new \Exception() ) );

		$result = $this->access( 'copyCoupons' )->invokeArgs( $object, [$ordBaseItem, [], 'unittest|en|EUR'] );

		$this->assertEquals( 1, count( $result ) );
		$this->assertArrayHasKey( 'coupon', $result );
	}


	public function testCopyProduct()
	{
		$manager = \Aimeos\MShop::create( \TestHelper::context(), 'order' );
		$ordBaseItem = $manager->create();

		$product = \Aimeos\MShop::create( $this->context, 'product' )->find( 'CNC' );
		$ordProdManager = \Aimeos\MShop::create( $this->context, 'order/product' );
		$ordProdItem = $ordProdManager->create()->copyFrom( $product )->setStockType( 'default' );
		$ordBaseItem->addProduct( $ordProdItem );


		$object = $this->getMockBuilder( \Aimeos\Controller\Frontend\Basket\Standard::class )
			->setConstructorArgs( [$this->context] )
			->onlyMethods( [] )
			->getMock();

		$result = $this->access( 'copyProducts' )->invokeArgs( $object, [$ordBaseItem, ['test'], 'unittest|en|EUR'] );


		$this->assertEquals( ['test'], $result );
		$this->assertEquals( 1, count( $object->get()->getProducts() ) );
		$this->assertInstanceOf( \Aimeos\MShop\Order\Item\Product\Iface::class, $object->get()->getProduct( 0 ) );

		$object->clear();
	}


	public function testCopyProductException()
	{
		$manager = \Aimeos\MShop::create( \TestHelper::context(), 'order' );
		$ordBaseItem = $manager->create();

		$product = \Aimeos\MShop::create( $this->context, 'product' )->find( 'CNC' );
		$ordProdManager = \Aimeos\MShop::create( $this->context, 'order/product' );
		$ordProdItem = $ordProdManager->create()->copyFrom( $product )->setStockType( 'default' );
		$ordBaseItem->addProduct( $ordProdItem );


		$object = $this->getMockBuilder( \Aimeos\Controller\Frontend\Basket\Standard::class )
			->setConstructorArgs( [$this->context] )
			->onlyMethods( ['addProduct'] )
			->getMock();

		$object->expects( $this->once() )->method( 'addProduct' )->will( $this->throwException( new \Exception() ) );

		$result = $this->access( 'copyProducts' )->invokeArgs( $object, [$ordBaseItem, [], 'unittest|en|EUR'] );

		$this->assertEquals( 1, count( $result ) );
		$this->assertArrayHasKey( 'product', $result );
	}


	public function testCopyServices()
	{
		$manager = \Aimeos\MShop::create( \TestHelper::context(), 'order' );
		$ordBaseItem = $manager->create();

		$serviceManager = \Aimeos\MShop::create( $this->context, 'service' );
		$ordServManager = \Aimeos\MShop::create( $this->context, 'order/service' );

		$serviceItem = $serviceManager->find( 'unitdeliverycode', [], 'service', 'delivery' );
		$ordServItem = $ordServManager->create()->copyFrom( $serviceItem );

		$ordBaseItem->addService( $ordServItem, \Aimeos\MShop\Order\Item\Service\Base::TYPE_DELIVERY );


		$object = $this->getMockBuilder( \Aimeos\Controller\Frontend\Basket\Standard::class )
			->setConstructorArgs( [$this->context] )
			->onlyMethods( [] )
			->getMock();

		$result = $this->access( 'copyServices' )->invokeArgs( $object, [$ordBaseItem, ['test'], 'unittest|en|EUR'] );


		$this->assertEquals( ['test'], $result );
		$this->assertEquals( 1, count( $object->get()->getServices() ) );

		$services = $object->get()->getService( \Aimeos\MShop\Order\Item\Service\Base::TYPE_DELIVERY );

		foreach( $services as $service ) {
			$this->assertInstanceOf( \Aimeos\MShop\Order\Item\Service\Iface::class, $service );
		}

		$object->clear();
	}


	public function testCopyServicesException()
	{
		$manager = \Aimeos\MShop::create( \TestHelper::context(), 'order' );
		$ordBaseItem = $manager->create();

		$serviceManager = \Aimeos\MShop::create( $this->context, 'service' );
		$ordServManager = \Aimeos\MShop::create( $this->context, 'order/service' );

		$serviceItem = $serviceManager->find( 'unitdeliverycode', [], 'service', 'delivery' );
		$ordServItem = $ordServManager->create()->copyFrom( $serviceItem );

		$ordBaseItem->addService( $ordServItem, \Aimeos\MShop\Order\Item\Service\Base::TYPE_DELIVERY );


		$object = $this->getMockBuilder( \Aimeos\Controller\Frontend\Basket\Standard::class )
			->setConstructorArgs( [$this->context] )
			->onlyMethods( ['addService'] )
			->getMock();

		$object->expects( $this->once() )->method( 'addService' )->will( $this->throwException( new \Exception() ) );

		$result = $this->access( 'copyServices' )->invokeArgs( $object, [$ordBaseItem, [], 'unittest|en|EUR'] );

		$this->assertEquals( 0, count( $result ) );
	}


	public function testCreateSubscriptions()
	{
		$manager = \Aimeos\MShop::create( $this->context, 'order' );

		$search = $manager->filter();
		$search->setConditions( $search->compare( '==', 'order.price', '53.50' ) );

		if( ( $basket = $manager->search( $search, ['order/product'] )->first() ) === null ) {
			throw new \Exception( sprintf( 'No order item found for price "%1$s"', '53,50' ) );
		}

		$object = $this->getMockBuilder( \Aimeos\Controller\Frontend\Basket\Standard::class )
			->setConstructorArgs( [$this->context] )
			->onlyMethods( ['getAttributes'] )
			->getMock();

		$stub = $this->getMockBuilder( \Aimeos\MShop\Subscription\Manager\Standard::class )
			->setConstructorArgs( [$this->context] )
			->onlyMethods( ['save'] )
			->getMock();

		\Aimeos\MShop::inject( \Aimeos\MShop\Subscription\Manager\Standard::class, $stub );

		$stub->expects( $this->exactly( 2 ) )->method( 'save' );

		$this->access( 'createSubscriptions' )->invokeArgs( $object, [$basket] );
	}


	public function testGetOrderProductAttributes()
	{
		$attrItem = \Aimeos\MShop::create( $this->context, 'attribute' )->create()
			->setCode( 'special_instructions' )->setCode( 'test' )->setType( 'test' )->setId( '-1' );

		$object = $this->getMockBuilder( \Aimeos\Controller\Frontend\Basket\Standard::class )
			->setConstructorArgs( [$this->context] )
			->onlyMethods( ['getAttributes'] )
			->getMock();

		$object->expects( $this->once() )->method( 'getAttributes' )
			->will( $this->returnValue( map( [1 => $attrItem] ) ) );

		$result = $this->access( 'getOrderProductAttributes' )->invokeArgs( $object, ['test', ['1'], ['1' => 'test']] );

		$this->assertEquals( 1, count( $result ) );
		$this->assertEquals( 'test', $result[0]->getValue() );
	}


	/**
	 * @param string $company
	 */
	protected function getAddress( $company )
	{
		$manager = \Aimeos\MShop::create( \TestHelper::context(), 'customer/address' );

		$search = $manager->filter();
		$search->setConditions( $search->compare( '==', 'customer.address.company', $company ) );

		if( ( $item = $manager->search( $search )->first() ) === null ) {
			throw new \RuntimeException( sprintf( 'No address item with company "%1$s" found', $company ) );
		}

		$ordAddrManager = \Aimeos\MShop::create( \TestHelper::context(), 'order/address' );
		$ordAddrItem = $ordAddrManager->create()->copyFrom( $item );

		return $ordAddrItem;
	}


	protected function access( $name )
	{
		$class = new \ReflectionClass( \Aimeos\Controller\Frontend\Basket\Base::class );
		$method = $class->getMethod( $name );
		$method->setAccessible( true );

		return $method;
	}
}
