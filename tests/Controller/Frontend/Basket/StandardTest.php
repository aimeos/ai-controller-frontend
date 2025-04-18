<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Metaways Infosystems GmbH, 2012
 * @copyright Aimeos (aimeos.org), 2015-2025
 */

namespace Aimeos\Controller\Frontend\Basket;


class StandardTest extends \PHPUnit\Framework\TestCase
{
	private $object;
	private $context;
	private $testItem;


	protected function setUp() : void
	{
		\Aimeos\MShop::cache( true );

		$this->context = \TestHelper::context();

		$manager = \Aimeos\MShop::create( $this->context, 'product' );
		$this->testItem = $manager->find( 'U:TESTP', ['attribute', 'media', 'price', 'product', 'text'] );

		$this->object = new \Aimeos\Controller\Frontend\Basket\Standard( $this->context );
	}


	protected function tearDown() : void
	{
		\Aimeos\MShop::cache( false );

		$this->object->clear();
		$this->context->session()->set( 'aimeos', [] );

		unset( $this->context, $this->object );
	}


	public function testAdd()
	{
		$result = $this->object->add( ['order.comment' => 'test'] );

		$this->assertInstanceOf( \Aimeos\Controller\Frontend\Basket\Iface::class, $result );
		$this->assertEquals( 'test', $this->object->get()->getComment() );
	}


	public function testClear()
	{
		$this->object->addProduct( $this->testItem );

		$this->assertInstanceOf( \Aimeos\Controller\Frontend\Basket\Iface::class, $this->object->clear() );
		$this->assertEquals( 0, count( $this->object->get()->getProducts() ) );
	}


	public function testGet()
	{
		$this->assertInstanceOf( \Aimeos\MShop\Order\Item\Iface::class, $this->object->get() );
	}


	public function testSave()
	{
		$stub = $this->getMockBuilder( \Aimeos\MShop\Order\Manager\Standard::class )
			->setConstructorArgs( [$this->context] )
			->onlyMethods( ['setSession', 'type'] )
			->getMock();

		\Aimeos\MShop::inject( \Aimeos\MShop\Order\Manager\Standard::class, $stub );

		$stub->expects( $this->exactly( 2 ) )->method( 'setSession' );
		$stub->method( 'type' )->willReturn( ['order'] );

		$object = new \Aimeos\Controller\Frontend\Basket\Standard( $this->context );
		$object->addProduct( $this->testItem );

		$this->assertInstanceOf( \Aimeos\Controller\Frontend\Basket\Iface::class, $object->save() );
	}


	public function testSetType()
	{
		$this->assertInstanceOf( \Aimeos\Controller\Frontend\Basket\Iface::class, $this->object->setType( 'test' ) );
	}


	public function testLoad()
	{
		$stub = $this->getMockBuilder( \Aimeos\MShop\Order\Manager\Standard::class )
			->setConstructorArgs( [$this->context] )
			->onlyMethods( ['get'] )
			->getMock();

		\Aimeos\MShop::inject( \Aimeos\MShop\Order\Manager\Standard::class, $stub );

		$stub->expects( $this->once() )->method( 'get' )
			->willReturn( $stub->create() );

		$object = new \Aimeos\Controller\Frontend\Basket\Standard( $this->context );

		$this->assertInstanceOf( \Aimeos\MShop\Order\Item\Iface::class, $object->load( -1 ) );
	}


	public function testStore()
	{
		$stub = $this->getMockBuilder( \Aimeos\MShop\Order\Manager\Standard::class )
			->setConstructorArgs( [$this->context] )
			->onlyMethods( ['save', 'type'] )
			->getMock();

		\Aimeos\MShop::inject( \Aimeos\MShop\Order\Manager\Standard::class, $stub );

		$priceManager = \Aimeos\MShop::create( $this->context, 'price' );

		$basket = $this->getMockBuilder( \Aimeos\MShop\Order\Item\Standard::class )
			->setConstructorArgs( ['order.', ['.price' => $priceManager->create(), '.locale' => $this->context->locale()]] )
			->onlyMethods( ['check'] )
			->getMock();

		$object = $this->getMockBuilder( \Aimeos\Controller\Frontend\Basket\Standard::class )
			->setConstructorArgs( [$this->context] )
			->onlyMethods( ['get'] )
			->getMock();

		$basket->expects( $this->once() )->method( 'check' )->willReturn( $basket );
		$object->expects( $this->once() )->method( 'get' )->willReturn( $basket );
		$stub->expects( $this->once() )->method( 'save' )->willReturn( $basket );
		$stub->method( 'type' )->willReturn( ['order'] );

		$this->assertInstanceOf( \Aimeos\MShop\Order\Item\Iface::class, $object->store() );
	}


	public function testStoreLimit()
	{
		$this->context->setEditor( 'core' );
		$config = $this->context->config();
		$config->set( 'controller/frontend/basket/limit-count', 0 );
		$config->set( 'controller/frontend/basket/limit-seconds', 86400 * 365 );

		$object = new \Aimeos\Controller\Frontend\Basket\Standard( $this->context );

		$this->expectException( \Aimeos\Controller\Frontend\Basket\Exception::class );
		$object->store();
	}


	public function testAddDeleteProduct()
	{
		$basket = $this->object->get();
		$manager = \Aimeos\MShop::create( $this->context, 'product' );
		$item = $manager->find( 'CNC', ['attribute', 'media', 'price', 'product', 'text', 'locale/site'] );

		$result1 = $this->object->addProduct( $item, 2 );
		$item2 = $this->object->get()->getProduct( 0 );
		$result2 = $this->object->deleteProduct( 0 );

		$this->assertEquals( 0, count( $basket->getProducts() ) );
		$this->assertEquals( 'CNC', $item2->getProductCode() );
		$this->assertEquals( 'default', $item2->getStockType() );
		$this->assertEquals( 'Unit test site', $item2->getVendor() );
		$this->assertInstanceOf( \Aimeos\Controller\Frontend\Basket\Iface::class, $result1 );
		$this->assertInstanceOf( \Aimeos\Controller\Frontend\Basket\Iface::class, $result2 );
	}


	public function testAddProductFractionalQuantity()
	{
		$manager = \Aimeos\MShop::create( $this->context, 'product' );
		$item = $manager->find( 'CNC', ['attribute', 'media', 'price', 'product', 'text'] );
		$item->setConfig( ['quantity-step' => '0.1'] );

		$this->object->addProduct( $item, 2.31 );
		$this->assertEquals( 2.4, $this->object->get()->getProduct( 0 )->getQuantity() );
	}


	public function testAddProductCustomAttribute()
	{
		$attrItem = \Aimeos\MShop::create( $this->context, 'attribute' )->find( 'custom', [], 'product', 'date' );
		$attrValues = [$attrItem->getId() => '2000-01-01'];

		$result = $this->object->addProduct( $this->testItem, 1, [], [], $attrValues );
		$basket = $this->object->get();

		$this->assertEquals( 1, count( $basket->getProducts() ) );
		$this->assertEquals( '2000-01-01', $basket->getProduct( 0 )->getAttribute( 'date', 'custom' ) );
		$this->assertInstanceOf( \Aimeos\Controller\Frontend\Basket\Iface::class, $result );
	}


	public function testAddProductCustomPrice()
	{
		$attrItem = \Aimeos\MShop::create( $this->context, 'attribute' )->find( 'custom', [], 'product', 'price' );
		$attrValues = [$attrItem->getId() => '0.01'];

		$result = $this->object->addProduct( $this->testItem, 1, [], [], $attrValues );
		$basket = $this->object->get();

		$this->assertEquals( 1, count( $basket->getProducts() ) );
		$this->assertEquals( '0.01', $basket->getProduct( 0 )->getPrice()->getValue() );
		$this->assertInstanceOf( \Aimeos\Controller\Frontend\Basket\Iface::class, $result );
	}


	public function testAddProductCustomPriceException()
	{
		$attrItem = \Aimeos\MShop::create( $this->context, 'attribute' )->find( 'custom', [], 'product', 'price' );
		$attrValues = [$attrItem->getId() => ','];

		$this->expectException( \Aimeos\Controller\Frontend\Basket\Exception::class );
		$this->object->addProduct( $this->testItem, 1, [], [], $attrValues );
	}


	public function testAddProductAttributePrice()
	{
		$attrItem = \Aimeos\MShop::create( $this->context, 'attribute' )->find( 'xs', [], 'product', 'size' );

		$result = $this->object->addProduct( $this->testItem, 1, [], [$attrItem->getId() => 2] );

		$this->assertEquals( '43.90', $this->object->get()->getPrice()->getValue() );
		$this->assertEquals( '25.90', $this->object->get()->getProducts()->first()->getAttributeItems()->getPrice()->first() );
		$this->assertInstanceOf( \Aimeos\Controller\Frontend\Basket\Iface::class, $result );
	}


	public function testAddProductAttributeNotAssigned()
	{
		$attrItem = \Aimeos\MShop::create( $this->context, 'attribute' )->find( '30', [], 'product', 'width' );
		$ids = [$attrItem->getId()];

		$this->expectException( '\\Aimeos\\Controller\\Frontend\\Basket\\Exception' );
		$this->object->addProduct( $this->testItem, 1, [], $ids, $ids );
	}


	public function testAddProductNegativeQuantityException()
	{
		$this->expectException( '\\Aimeos\\MShop\\Order\\Exception' );
		$this->object->addProduct( $this->testItem, -1 );
	}


	public function testAddProductNoPriceException()
	{
		$item = \Aimeos\MShop::create( $this->context, 'product' )->find( 'MNOP' );

		$this->expectException( '\\Aimeos\\MShop\\Price\\Exception' );
		$this->object->addProduct( $item );
	}


	public function testAddProductConfigAttributeException()
	{
		$this->expectException( '\\Aimeos\\Controller\\Frontend\\Basket\\Exception' );
		$this->object->addProduct( $this->testItem, 1, [], [-1] );
	}


	public function testAddProductLowQuantityPriceException()
	{
		$item = \Aimeos\MShop::create( $this->context, 'product' )->find( 'IJKL', ['attribute', 'price'] );

		$this->expectException( '\\Aimeos\\MShop\\Price\\Exception' );
		$this->object->addProduct( $item );
	}


	public function testAddProductHigherQuantities()
	{
		$item = \Aimeos\MShop::create( $this->context, 'product' )->find( 'IJKL', ['price'] );

		$result = $this->object->addProduct( $item, 2, [], [], [], 'unitstock' );

		$this->assertEquals( 2, $this->object->get()->getProduct( 0 )->getQuantity() );
		$this->assertEquals( 'IJKL', $this->object->get()->getProduct( 0 )->getProductCode() );
		$this->assertInstanceOf( \Aimeos\Controller\Frontend\Basket\Iface::class, $result );
	}


	public function testDeleteProductFlagError()
	{
		$this->object->addProduct( $this->testItem, 2 );

		$item = $this->object->get()->getProduct( 0 );
		$item->setFlags( \Aimeos\MShop\Order\Item\Product\Base::FLAG_IMMUTABLE );

		$this->expectException( '\\Aimeos\\Controller\\Frontend\\Basket\\Exception' );
		$this->object->deleteProduct( 0 );
	}


	public function testUpdateProduct()
	{
		$this->object->addProduct( $this->testItem );

		$item = $this->object->get()->getProduct( 0 );
		$this->assertEquals( 1, $item->getQuantity() );

		$result = $this->object->updateProduct( 0, 4 );
		$item = $this->object->get()->getProduct( 0 );

		$this->assertEquals( 4, $item->getQuantity() );
		$this->assertEquals( 'U:TESTP', $item->getProductCode() );
		$this->assertInstanceOf( \Aimeos\Controller\Frontend\Basket\Iface::class, $result );
	}


	public function testUpdateProductFlagError()
	{
		$this->object->addProduct( $this->testItem, 2 );

		$item = $this->object->get()->getProduct( 0 );
		$item->setFlags( \Aimeos\MShop\Order\Item\Product\Base::FLAG_IMMUTABLE );

		$this->expectException( '\\Aimeos\\Controller\\Frontend\\Basket\\Exception' );
		$this->object->updateProduct( 0, 4 );
	}


	public function testAddCoupon()
	{
		$this->object->addProduct( $this->testItem, 2 );

		$result = $this->object->addCoupon( 'GHIJ' );
		$basket = $this->object->get();

		$this->assertEquals( 1, count( $basket->getCoupons() ) );
		$this->assertInstanceOf( \Aimeos\Controller\Frontend\Basket\Iface::class, $result );
	}


	public function testAddCouponExceedCount()
	{
		$this->object->addProduct( $this->testItem, 2 );
		$this->object->addCoupon( 'GHIJ' );

		$this->expectException( '\\Aimeos\\Controller\\Frontend\\Basket\\Exception' );
		$this->object->addCoupon( 'GHIJ' );
	}


	public function testAddCouponInvalidCode()
	{
		$this->expectException( \Aimeos\MShop\Plugin\Provider\Exception::class );
		$this->object->addCoupon( 'invalid' );
	}


	public function testDeleteCoupon()
	{
		$this->object->addProduct( $this->testItem, 2 );
		$this->object->addCoupon( '90AB' );

		$result = $this->object->deleteCoupon( '90AB' );
		$basket = $this->object->get();

		$this->assertEquals( 0, count( $basket->getCoupons() ) );
		$this->assertInstanceOf( \Aimeos\Controller\Frontend\Basket\Iface::class, $result );
	}


	public function testAddAddress()
	{
		$values = array(
			'order.address.company' => '<p onclick="javascript: alert(\'gotcha\');">Example company</p>',
			'order.address.vatid' => 'DE999999999',
			'order.address.title' => '<br/>Dr.',
			'order.address.salutation' => 'mr',
			'order.address.firstname' => 'firstunit',
			'order.address.lastname' => 'lastunit',
			'order.address.address1' => 'unit str.',
			'order.address.address2' => ' 166',
			'order.address.address3' => '4.OG',
			'order.address.postal' => '22769',
			'order.address.city' => 'Hamburg',
			'order.address.state' => 'Hamburg',
			'order.address.countryid' => 'de',
			'order.address.languageid' => 'de',
			'order.address.telephone' => '05554433221',
			'order.address.email' => 'test@example.com',
			'order.address.telefax' => '05554433222',
			'order.address.website' => 'www.example.com',
			'random key from a random manager' => [], // ups, not a string
		);

		$result = $this->object->addAddress( 'payment', $values );
		$address = $this->object->get()->getAddress( 'payment', 0 );

		$this->assertEquals( 'Example company', $address->getCompany() );
		$this->assertEquals( 'Dr.', $address->getTitle() );
		$this->assertEquals( 'firstunit', $address->getFirstname() );
		$this->assertInstanceOf( \Aimeos\Controller\Frontend\Basket\Iface::class, $result );
	}


	public function testDeleteAddress()
	{
		$this->object->addAddress( 'payment', [] );
		$this->assertEquals( 1, count( $this->object->get()->getAddress( 'payment' ) ) );

		$result = $this->object->deleteAddress( 'payment' );

		$this->assertEquals( 0, count( $this->object->get()->getAddress( 'payment' ) ) );
		$this->assertInstanceOf( \Aimeos\Controller\Frontend\Basket\Iface::class, $result );
	}



	public function testAddServicePayment()
	{
		$manager = \Aimeos\MShop::create( $this->context, 'service' );
		$service = $manager->find( 'unitpaymentcode', [], 'service', 'payment' );

		$this->object->addService( $service );
		$item = $this->object->get()->getService( 'payment', 0 )->getCode();
		$this->assertEquals( 'unitpaymentcode', $item );

		$this->expectException( '\\Aimeos\\Controller\\Frontend\\Basket\\Exception' );
		$this->object->addService( $service, ['prepay' => true] );
	}


	public function testAddServiceDelivery()
	{
		$manager = \Aimeos\MShop::create( $this->context, 'service' );
		$service = $manager->find( 'unitdeliverycode', [], 'service', 'delivery' );

		$this->object->addService( $service );
		$item = $this->object->get()->getService( 'delivery', 0 );
		$this->assertEquals( 'unitdeliverycode', $item->getCode() );

		$this->expectException( '\\Aimeos\\Controller\\Frontend\\Basket\\Exception' );
		$this->object->addService( $service, ['fast shipping' => true, 'air shipping' => false] );
	}


	public function testDeleteServices()
	{
		$manager = \Aimeos\MShop::create( $this->context, 'service' );
		$service = $manager->find( 'unitdeliverycode', [], 'service', 'delivery' );

		$this->assertSame( $this->object, $this->object->addService( $service ) );
		$this->assertEquals( 'unitdeliverycode', $this->object->get()->getService( 'delivery', 0 )->getCode() );

		$this->assertSame( $this->object, $this->object->deleteService( 'delivery' ) );
		$this->assertEquals( 0, count( $this->object->get()->getService( 'delivery' ) ) );
	}


	public function testDeleteServicePosition()
	{
		$manager = \Aimeos\MShop::create( $this->context, 'service' );
		$service = $manager->find( 'unitdeliverycode', [], 'service', 'delivery' );

		$this->assertSame( $this->object, $this->object->addService( $service ) );
		$this->assertEquals( 'unitdeliverycode', $this->object->get()->getService( 'delivery', 0 )->getCode() );

		$this->assertSame( $this->object, $this->object->deleteService( 'delivery', 0 ) );
		$this->assertEquals( 0, count( $this->object->get()->getService( 'delivery' ) ) );
	}


	public function testCheckLocale()
	{
		$manager = \Aimeos\MShop::create( $this->context, 'service' );
		$payment = $manager->find( 'unitpaymentcode', [], 'service', 'payment' );
		$delivery = $manager->find( 'unitdeliverycode', [], 'service', 'delivery' );

		$this->object->addProduct( $this->testItem, 2 );
		$this->object->addCoupon( 'OPQR' );

		$this->object->addService( $payment );
		$this->object->addService( $delivery );

		$basket = $this->object->get();
		$price = $basket->getPrice();

		foreach( $basket->getProducts() as $product )
		{
			$this->assertEquals( 2, $product->getQuantity() );
			$product->getPrice()->setCurrencyId( 'CHF' );
		}

		$basket->getService( 'delivery', 0 )->getPrice()->setCurrencyId( 'CHF' );
		$basket->getService( 'payment', 0 )->getPrice()->setCurrencyId( 'CHF' );
		$basket->locale()->setCurrencyId( 'CHF' );
		$price->setCurrencyId( 'CHF' );

		$this->context->locale()->setCurrencyId( 'CHF' );
		$this->object->addAddress( 'payment', $this->getAddress( 'Example company' )->toArray() );

		$this->context->session()->set( 'aimeos/basket/currency', 'CHF' );
		$this->context->locale()->setCurrencyId( 'EUR' );

		$this->context->session()->set( 'aimeos/basket/content-unittest-en-EUR-', null );

		$object = new \Aimeos\Controller\Frontend\Basket\Standard( $this->context );
		$basket = $object->get();

		foreach( $basket->getProducts() as $product )
		{
			$this->assertEquals( 'EUR', $product->getPrice()->getCurrencyId() );
			$this->assertEquals( 2, $product->getQuantity() );
		}

		$this->assertEquals( 'EUR', $basket->getService( 'payment', 0 )->getPrice()->getCurrencyId() );
		$this->assertEquals( 'EUR', $basket->getService( 'delivery', 0 )->getPrice()->getCurrencyId() );
		$this->assertEquals( 'EUR', $basket->locale()->getCurrencyId() );
		$this->assertEquals( 'EUR', $basket->getPrice()->getCurrencyId() );
	}


	/**
	 * @param string $company
	 */
	protected function getAddress( $company )
	{
		$addressManager = \Aimeos\MShop::create( \TestHelper::context(), 'customer/address' );
		$search = $addressManager->filter()->add( ['customer.address.company' => $company] );

		return $addressManager->search( $search )
			->first( new \RuntimeException( sprintf( 'No address item with company "%1$s" found', $company ) ) );
	}
}
