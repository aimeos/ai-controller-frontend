<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2017
 */

namespace Aimeos\Controller\Frontend\Basket;


class BaseTest extends \PHPUnit\Framework\TestCase
{
	private $context;


	protected function setUp()
	{
		$this->context = \TestHelperFrontend::getContext();
	}


	protected function tearDown()
	{
		$this->context->getSession()->set( 'aimeos', [] );

		unset( $this->context );
	}


	public function testCheckLocale()
	{
		$object = $this->getMockBuilder( '\Aimeos\Controller\Frontend\Basket\Standard' )
			->setConstructorArgs( [$this->context] )
			->setMethods( null )
			->getMock();

		$this->context->getSession()->set( 'aimeos/basket/locale', 'unittest|en|EUR' );
		$this->access( 'checkLocale' )->invokeArgs( $object, ['unittest'] );
	}


	public function testCopyAddresses()
	{
		$manager = \Aimeos\MShop\Factory::createManager( \TestHelperFrontend::getContext(), 'order/base' );
		$ordBaseItem = $manager->createItem();

		$address = $this->getAddress( 'Example company' );
		$ordBaseItem->setAddress( $address, \Aimeos\MShop\Order\Item\Base\Address\Base::TYPE_PAYMENT );


		$object = $this->getMockBuilder( '\Aimeos\Controller\Frontend\Basket\Standard' )
			->setConstructorArgs( [$this->context] )
			->setMethods( null )
			->getMock();

		$result = $this->access( 'copyAddresses' )->invokeArgs( $object, [$ordBaseItem, ['test'], 'unittest|en|EUR'] );


		$this->assertEquals( ['test'], $result );
		$this->assertEquals( 1, count( $object->get()->getAddresses() ) );

		$addr = $object->get()->getAddress( \Aimeos\MShop\Order\Item\Base\Address\Base::TYPE_PAYMENT );
		$this->assertInstanceOf( '\Aimeos\MShop\Order\Item\Base\Address\Iface', $addr );

		$object->clear();
	}


	public function testCopyAddressesException()
	{
		$manager = \Aimeos\MShop\Factory::createManager( \TestHelperFrontend::getContext(), 'order/base' );
		$ordBaseItem = $manager->createItem();

		$address = $this->getAddress( 'Example company' );
		$ordBaseItem->setAddress( $address, \Aimeos\MShop\Order\Item\Base\Address\Base::TYPE_PAYMENT );


		$object = $this->getMockBuilder( '\Aimeos\Controller\Frontend\Basket\Standard' )
			->setConstructorArgs( [$this->context] )
			->setMethods( ['setAddress'] )
			->getMock();

		$object->expects( $this->once() )->method( 'setAddress' )->will( $this->throwException( new \Exception() ) );

		$result = $this->access( 'copyAddresses' )->invokeArgs( $object, [$ordBaseItem, [], 'unittest|en|EUR'] );

		$this->assertEquals( 1, count( $result ) );
		$this->assertArrayHasKey( 'address', $result );
	}


	public function testCopyCoupon()
	{
		$manager = \Aimeos\MShop\Factory::createManager( \TestHelperFrontend::getContext(), 'order/base' );
		$ordBaseItem = $manager->createItem();

		$product = \Aimeos\MShop\Factory::createManager( $this->context, 'product' )->findItem( 'CNC', ['price'] );
		$ordProdManager = \Aimeos\MShop\Factory::createManager( $this->context, 'order/base/product' );
		$ordProdItem = $ordProdManager->createItem()->copyFrom( $product );

		$priceItems = $product->getRefItems( 'price' );
		$ordProdItem->setPrice( reset( $priceItems ) );

		$ordBaseItem->addProduct( $ordProdItem );
		$ordBaseItem->addCoupon( 'OPQR', [] );


		$object = $this->getMockBuilder( '\Aimeos\Controller\Frontend\Basket\Standard' )
			->setConstructorArgs( [$this->context] )
			->setMethods( null )
			->getMock();

		$object->addProduct( $product->getId() );

		$result = $this->access( 'copyCoupons' )->invokeArgs( $object, [$ordBaseItem, ['test'], 'unittest|en|EUR'] );


		$this->assertEquals( ['test'], $result );

		$object->clear();
	}


	public function testCopyCouponException()
	{
		$manager = \Aimeos\MShop\Factory::createManager( \TestHelperFrontend::getContext(), 'order/base' );
		$ordBaseItem = $manager->createItem();

		$ordBaseItem->addCoupon( '90AB', [] );


		$object = $this->getMockBuilder( '\Aimeos\Controller\Frontend\Basket\Standard' )
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
		$manager = \Aimeos\MShop\Factory::createManager( \TestHelperFrontend::getContext(), 'order/base' );
		$ordBaseItem = $manager->createItem();

		$product = \Aimeos\MShop\Factory::createManager( $this->context, 'product' )->findItem( 'CNC' );
		$ordProdManager = \Aimeos\MShop\Factory::createManager( $this->context, 'order/base/product' );
		$ordProdItem = $ordProdManager->createItem()->copyFrom( $product );
		$ordBaseItem->addProduct( $ordProdItem );


		$object = $this->getMockBuilder( '\Aimeos\Controller\Frontend\Basket\Standard' )
			->setConstructorArgs( [$this->context] )
			->setMethods( null )
			->getMock();

		$result = $this->access( 'copyProducts' )->invokeArgs( $object, [$ordBaseItem, ['test'], 'unittest|en|EUR'] );


		$this->assertEquals( ['test'], $result );
		$this->assertEquals( 1, count( $object->get()->getProducts() ) );
		$this->assertInstanceOf( '\Aimeos\MShop\Order\Item\Base\Product\Iface', $object->get()->getProduct( 0 ) );

		$object->clear();
	}


	public function testCopyProductException()
	{
		$manager = \Aimeos\MShop\Factory::createManager( \TestHelperFrontend::getContext(), 'order/base' );
		$ordBaseItem = $manager->createItem();

		$product = \Aimeos\MShop\Factory::createManager( $this->context, 'product' )->findItem( 'CNC' );
		$ordProdManager = \Aimeos\MShop\Factory::createManager( $this->context, 'order/base/product' );
		$ordProdItem = $ordProdManager->createItem()->copyFrom( $product );
		$ordBaseItem->addProduct( $ordProdItem );


		$object = $this->getMockBuilder( '\Aimeos\Controller\Frontend\Basket\Standard' )
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
		$manager = \Aimeos\MShop\Factory::createManager( \TestHelperFrontend::getContext(), 'order/base' );
		$ordBaseItem = $manager->createItem();

		$serviceManager = \Aimeos\MShop\Factory::createManager( $this->context, 'service' );
		$ordServManager = \Aimeos\MShop\Factory::createManager( $this->context, 'order/base/service' );

		$serviceItem = $serviceManager->findItem( 'unitcode', [], 'service', 'delivery' );
		$ordServItem = $ordServManager->createItem()->copyFrom( $serviceItem );

		$ordBaseItem->setService( $ordServItem, \Aimeos\MShop\Order\Item\Base\Service\Base::TYPE_DELIVERY );


		$object = $this->getMockBuilder( '\Aimeos\Controller\Frontend\Basket\Standard' )
			->setConstructorArgs( [$this->context] )
			->setMethods( null )
			->getMock();

		$result = $this->access( 'copyServices' )->invokeArgs( $object, [$ordBaseItem, ['test'], 'unittest|en|EUR'] );


		$this->assertEquals( ['test'], $result );
		$this->assertEquals( 1, count( $object->get()->getServices() ) );

		$service = $object->get()->getService( \Aimeos\MShop\Order\Item\Base\Service\Base::TYPE_DELIVERY );
		$this->assertInstanceOf( '\Aimeos\MShop\Order\Item\Base\Service\Iface', $service );

		$object->clear();
	}


	public function testCopyServicesException()
	{
		$manager = \Aimeos\MShop\Factory::createManager( \TestHelperFrontend::getContext(), 'order/base' );
		$ordBaseItem = $manager->createItem();

		$serviceManager = \Aimeos\MShop\Factory::createManager( $this->context, 'service' );
		$ordServManager = \Aimeos\MShop\Factory::createManager( $this->context, 'order/base/service' );

		$serviceItem = $serviceManager->findItem( 'unitcode', [], 'service', 'delivery' );
		$ordServItem = $ordServManager->createItem()->copyFrom( $serviceItem );

		$ordBaseItem->setService( $ordServItem, \Aimeos\MShop\Order\Item\Base\Service\Base::TYPE_DELIVERY );


		$object = $this->getMockBuilder( '\Aimeos\Controller\Frontend\Basket\Standard' )
			->setConstructorArgs( [$this->context] )
			->setMethods( ['setService'] )
			->getMock();

		$object->expects( $this->once() )->method( 'setService' )->will( $this->throwException( new \Exception() ) );

		$result = $this->access( 'copyServices' )->invokeArgs( $object, [$ordBaseItem, [], 'unittest|en|EUR'] );

		$this->assertEquals( 0, count( $result ) );
	}


	public function testGetOrderProductAttributes()
	{
		$object = $this->getMockBuilder( '\Aimeos\Controller\Frontend\Basket\Standard' )
			->setConstructorArgs( [$this->context] )
			->setMethods( ['getAttributes'] )
			->getMock();

		$list = [1 => new \Aimeos\MShop\Attribute\Item\Standard( ['attribute.code' => 'special_instructions'] )];
		$object->expects( $this->once() )->method( 'getAttributes' )->will( $this->returnValue( $list ) );

		$values = new \stdClass(); // test for PHP bug
		$values->{'1'} = 'test';
		$result = $this->access( 'getOrderProductAttributes' )->invokeArgs( $object, ['test', ['1'], (array) $values] );

		$this->assertEquals( 1, count( $result ) );
		$this->assertEquals( 'test', $result[0]->getValue() );
	}


	/**
	 * @param string $company
	 */
	protected function getAddress( $company )
	{
		$manager = \Aimeos\MShop\Factory::createManager( \TestHelperFrontend::getContext(), 'customer/address' );

		$search = $manager->createSearch();
		$search->setConditions( $search->compare( '==', 'customer.address.company', $company ) );
		$items = $manager->searchItems( $search );

		if( ( $item = reset( $items ) ) === false ) {
			throw new \RuntimeException( sprintf( 'No address item with company "%1$s" found', $company ) );
		}

		$ordAddrManager = \Aimeos\MShop\Factory::createManager( \TestHelperFrontend::getContext(), 'order/base/address' );
		$ordAddrItem = $ordAddrManager->createItem()->copyFrom( $item );

		return $ordAddrItem;
	}


	protected function access( $name )
	{
		$class = new \ReflectionClass( '\Aimeos\Controller\Frontend\Basket\Base' );
		$method = $class->getMethod( $name );
		$method->setAccessible( true );

		return $method;
	}
}
