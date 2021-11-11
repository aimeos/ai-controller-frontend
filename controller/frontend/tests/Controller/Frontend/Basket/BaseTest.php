<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2017-2021
 */

namespace Aimeos\Controller\Frontend\Basket;


class BaseTest extends \PHPUnit\Framework\TestCase
{
	private $context;


	protected function setUp() : void
	{
		$this->context = \TestHelperFrontend::getContext();
		\Aimeos\MShop::cache( true );
	}


	protected function tearDown() : void
	{
		\Aimeos\MShop::cache( false );
		$this->context->getSession()->set( 'aimeos', [] );

		unset( $this->context );
	}


	public function testCheckLocale()
	{
		$object = $this->getMockBuilder( \Aimeos\Controller\Frontend\Basket\Standard::class )
			->setConstructorArgs( [$this->context] )
			->setMethods( null )
			->getMock();

		$this->context->getSession()->set( 'aimeos/basket/locale', 'unittest|en|EUR' );
		$this->access( 'checkLocale' )->invokeArgs( $object, [$this->context->getLocale(), 'unittest'] );

		$this->assertEquals( 'unittest|de|EUR', $this->context->getSession()->get( 'aimeos/basket/locale' ) );
	}


	public function testCopyAddresses()
	{
		$manager = \Aimeos\MShop::create( \TestHelperFrontend::getContext(), 'order/base' );
		$ordBaseItem = $manager->create();

		$address = $this->getAddress( 'Example company' );
		$ordBaseItem->addAddress( $address, \Aimeos\MShop\Order\Item\Base\Address\Base::TYPE_PAYMENT );


		$object = $this->getMockBuilder( \Aimeos\Controller\Frontend\Basket\Standard::class )
			->setConstructorArgs( [$this->context] )
			->setMethods( null )
			->getMock();

		$result = $this->access( 'copyAddresses' )->invokeArgs( $object, [$ordBaseItem, ['test'], 'unittest|en|EUR'] );


		$this->assertEquals( ['test'], $result );
		$this->assertEquals( 1, count( $object->get()->getAddresses() ) );

		$addr = $object->get()->getAddress( \Aimeos\MShop\Order\Item\Base\Address\Base::TYPE_PAYMENT, 0 );
		$this->assertInstanceOf( \Aimeos\MShop\Order\Item\Base\Address\Iface::class, $addr );

		$object->clear();
	}


	public function testCopyAddressesException()
	{
		$manager = \Aimeos\MShop::create( \TestHelperFrontend::getContext(), 'order/base' );
		$ordBaseItem = $manager->create();

		$address = $this->getAddress( 'Example company' );
		$ordBaseItem->addAddress( $address, \Aimeos\MShop\Order\Item\Base\Address\Base::TYPE_PAYMENT );


		$object = $this->getMockBuilder( \Aimeos\Controller\Frontend\Basket\Standard::class )
			->setConstructorArgs( [$this->context] )
			->setMethods( ['get'] )
			->getMock();

		$object->expects( $this->once() )->method( 'get' )->will( $this->throwException( new \Exception() ) );

		$result = $this->access( 'copyAddresses' )->invokeArgs( $object, [$ordBaseItem, [], 'unittest|en|EUR'] );

		$this->assertEquals( 1, count( $result ) );
		$this->assertArrayHasKey( 'address', $result );
	}


	public function testCopyCoupon()
	{
		$manager = \Aimeos\MShop::create( \TestHelperFrontend::getContext(), 'order/base' );
		$ordBaseItem = $manager->create();

		$product = \Aimeos\MShop::create( $this->context, 'product' )->find( 'CNC', ['price'] );
		$ordProdManager = \Aimeos\MShop::create( $this->context, 'order/base/product' );
		$ordProdItem = $ordProdManager->create()->copyFrom( $product )->setStockType( 'default' );

		$ordProdItem->setPrice( $product->getRefItems( 'price' )->first() );

		$ordBaseItem->addProduct( $ordProdItem );
		$ordBaseItem->addCoupon( 'OPQR', [] );


		$object = $this->getMockBuilder( \Aimeos\Controller\Frontend\Basket\Standard::class )
			->setConstructorArgs( [$this->context] )
			->setMethods( null )
			->getMock();

		$object->addProduct( $product );

		$result = $this->access( 'copyCoupons' )->invokeArgs( $object, [$ordBaseItem, ['test'], 'unittest|en|EUR'] );


		$this->assertEquals( ['test'], $result );

		$object->clear();
	}


	public function testCopyCouponException()
	{
		$manager = \Aimeos\MShop::create( \TestHelperFrontend::getContext(), 'order/base' );
		$ordBaseItem = $manager->create();

		$ordBaseItem->addCoupon( '90AB', [] );


		$object = $this->getMockBuilder( \Aimeos\Controller\Frontend\Basket\Standard::class )
			->setConstructorArgs( [$this->context] )
			->setMethods( ['addCoupon'] )
			->getMock();

		$object->expects( $this->once() )->method( 'addCoupon' )->will( $this->throwException( new \Exception() ) );

		$result = $this->access( 'copyCoupons' )->invokeArgs( $object, [$ordBaseItem, [], 'unittest|en|EUR'] );

		$this->assertEquals( 1, count( $result ) );
		$this->assertArrayHasKey( 'coupon', $result );
	}


	public function testCopyProduct()
	{
		$manager = \Aimeos\MShop::create( \TestHelperFrontend::getContext(), 'order/base' );
		$ordBaseItem = $manager->create();

		$product = \Aimeos\MShop::create( $this->context, 'product' )->find( 'CNC' );
		$ordProdManager = \Aimeos\MShop::create( $this->context, 'order/base/product' );
		$ordProdItem = $ordProdManager->create()->copyFrom( $product )->setStockType( 'default' );
		$ordBaseItem->addProduct( $ordProdItem );


		$object = $this->getMockBuilder( \Aimeos\Controller\Frontend\Basket\Standard::class )
			->setConstructorArgs( [$this->context] )
			->setMethods( null )
			->getMock();

		$result = $this->access( 'copyProducts' )->invokeArgs( $object, [$ordBaseItem, ['test'], 'unittest|en|EUR'] );


		$this->assertEquals( ['test'], $result );
		$this->assertEquals( 1, count( $object->get()->getProducts() ) );
		$this->assertInstanceOf( \Aimeos\MShop\Order\Item\Base\Product\Iface::class, $object->get()->getProduct( 0 ) );

		$object->clear();
	}


	public function testCopyProductException()
	{
		$manager = \Aimeos\MShop::create( \TestHelperFrontend::getContext(), 'order/base' );
		$ordBaseItem = $manager->create();

		$product = \Aimeos\MShop::create( $this->context, 'product' )->find( 'CNC' );
		$ordProdManager = \Aimeos\MShop::create( $this->context, 'order/base/product' );
		$ordProdItem = $ordProdManager->create()->copyFrom( $product )->setStockType( 'default' );
		$ordBaseItem->addProduct( $ordProdItem );


		$object = $this->getMockBuilder( \Aimeos\Controller\Frontend\Basket\Standard::class )
			->setConstructorArgs( [$this->context] )
			->setMethods( ['addProduct'] )
			->getMock();

		$object->expects( $this->once() )->method( 'addProduct' )->will( $this->throwException( new \Exception() ) );

		$result = $this->access( 'copyProducts' )->invokeArgs( $object, [$ordBaseItem, [], 'unittest|en|EUR'] );

		$this->assertEquals( 1, count( $result ) );
		$this->assertArrayHasKey( 'product', $result );
	}


	public function testCopyServices()
	{
		$manager = \Aimeos\MShop::create( \TestHelperFrontend::getContext(), 'order/base' );
		$ordBaseItem = $manager->create();

		$serviceManager = \Aimeos\MShop::create( $this->context, 'service' );
		$ordServManager = \Aimeos\MShop::create( $this->context, 'order/base/service' );

		$serviceItem = $serviceManager->find( 'unitdeliverycode', [], 'service', 'delivery' );
		$ordServItem = $ordServManager->create()->copyFrom( $serviceItem );

		$ordBaseItem->addService( $ordServItem, \Aimeos\MShop\Order\Item\Base\Service\Base::TYPE_DELIVERY );


		$object = $this->getMockBuilder( \Aimeos\Controller\Frontend\Basket\Standard::class )
			->setConstructorArgs( [$this->context] )
			->setMethods( null )
			->getMock();

		$result = $this->access( 'copyServices' )->invokeArgs( $object, [$ordBaseItem, ['test'], 'unittest|en|EUR'] );


		$this->assertEquals( ['test'], $result );
		$this->assertEquals( 1, count( $object->get()->getServices() ) );

		$services = $object->get()->getService( \Aimeos\MShop\Order\Item\Base\Service\Base::TYPE_DELIVERY );

		foreach( $services as $service ) {
			$this->assertInstanceOf( \Aimeos\MShop\Order\Item\Base\Service\Iface::class, $service );
		}

		$object->clear();
	}


	public function testCopyServicesException()
	{
		$manager = \Aimeos\MShop::create( \TestHelperFrontend::getContext(), 'order/base' );
		$ordBaseItem = $manager->create();

		$serviceManager = \Aimeos\MShop::create( $this->context, 'service' );
		$ordServManager = \Aimeos\MShop::create( $this->context, 'order/base/service' );

		$serviceItem = $serviceManager->find( 'unitdeliverycode', [], 'service', 'delivery' );
		$ordServItem = $ordServManager->create()->copyFrom( $serviceItem );

		$ordBaseItem->addService( $ordServItem, \Aimeos\MShop\Order\Item\Base\Service\Base::TYPE_DELIVERY );


		$object = $this->getMockBuilder( \Aimeos\Controller\Frontend\Basket\Standard::class )
			->setConstructorArgs( [$this->context] )
			->setMethods( ['addService'] )
			->getMock();

		$object->expects( $this->once() )->method( 'addService' )->will( $this->throwException( new \Exception() ) );

		$result = $this->access( 'copyServices' )->invokeArgs( $object, [$ordBaseItem, [], 'unittest|en|EUR'] );

		$this->assertEquals( 0, count( $result ) );
	}


	public function testCreateSubscriptions()
	{
		$baseManager = \Aimeos\MShop::create( $this->context, 'order/base' );

		$search = $baseManager->filter();
		$search->setConditions( $search->compare( '==', 'order.base.price', '53.50' ) );

		if( ( $basket = $baseManager->search( $search, ['order/base/product'] )->first() ) === null ) {
			throw new \Exception( sprintf( 'No order base item found for price "%1$s"', '53,50' ) );
		}

		$object = $this->getMockBuilder( \Aimeos\Controller\Frontend\Basket\Standard::class )
			->setConstructorArgs( [$this->context] )
			->setMethods( ['getAttributes'] )
			->getMock();

		$stub = $this->getMockBuilder( \Aimeos\MShop\Subscription\Manager\Standard::class )
			->setConstructorArgs( [$this->context] )
			->setMethods( ['save'] )
			->getMock();

		\Aimeos\MShop::inject( 'subscription', $stub );

		$stub->expects( $this->exactly( 2 ) )->method( 'save' );

		$this->access( 'createSubscriptions' )->invokeArgs( $object, [$basket] );
	}


	public function testGetOrderProductAttributes()
	{
		$attrItem = \Aimeos\MShop::create( $this->context, 'attribute' )->create()
			->setCode( 'special_instructions' )->setCode( 'test' )->setType( 'test' )->setId( '-1' );

		$object = $this->getMockBuilder( \Aimeos\Controller\Frontend\Basket\Standard::class )
			->setConstructorArgs( [$this->context] )
			->setMethods( ['getAttributes'] )
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
		$manager = \Aimeos\MShop::create( \TestHelperFrontend::getContext(), 'customer/address' );

		$search = $manager->filter();
		$search->setConditions( $search->compare( '==', 'customer.address.company', $company ) );

		if( ( $item = $manager->search( $search )->first() ) === null ) {
			throw new \RuntimeException( sprintf( 'No address item with company "%1$s" found', $company ) );
		}

		$ordAddrManager = \Aimeos\MShop::create( \TestHelperFrontend::getContext(), 'order/base/address' );
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
