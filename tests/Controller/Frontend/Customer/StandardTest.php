<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2017-2026
 */


namespace Aimeos\Controller\Frontend\Customer;


class StandardTest extends \PHPUnit\Framework\TestCase
{
	private $context;
	private $object;


	protected function setUp() : void
	{
		$this->context = \TestHelper::context();
		$this->object = new \Aimeos\Controller\Frontend\Customer\Standard( $this->context );
	}


	protected function tearDown() : void
	{
		unset( $this->object, $this->context );
	}


	public function testAdd()
	{
		$this->assertSame( $this->object, $this->object->add( ['customer.code' => 'test'] ) );
		$this->assertEquals( 'test', $this->object->get()->getCode() );
	}


	public function testAddAddressItem()
	{
		$item = \Aimeos\MShop::create( $this->context, 'customer/address' )->create();
		$this->assertSame( $this->object, $this->object->addAddressItem( $item ) );
	}


	public function testAddListItem()
	{
		$listItem = \Aimeos\MShop::create( $this->context, 'customer/lists' )->create();
		$this->assertSame( $this->object, $this->object->addListItem( 'product', $listItem ) );
	}


	public function testAddListItemGroup()
	{
		$listItem = \Aimeos\MShop::create( $this->context, 'customer/lists' )->create();

		$this->expectException( \Aimeos\Controller\Frontend\Customer\Exception::class );
		$this->object->addListItem( 'group', $listItem );
	}


	public function testAddListItemNotAllowed()
	{
		$listItem = \Aimeos\MShop::create( $this->context, 'customer/lists' )->create();

		$this->expectException( \Aimeos\Controller\Frontend\Customer\Exception::class );
		$this->object->addListItem( 'order', $listItem );
	}


	public function testAddPropertyItem()
	{
		$item = \Aimeos\MShop::create( $this->context, 'customer/property' )->create();
		$this->assertSame( $this->object, $this->object->addPropertyItem( $item ) );
	}


	public function testCreateAddressItem()
	{
		$this->assertInstanceOf( \Aimeos\MShop\Common\Item\Address\Iface::class, $this->object->createAddressItem() );
	}


	public function testCreateListItem()
	{
		$this->assertInstanceOf( \Aimeos\MShop\Common\Item\Lists\Iface::class, $this->object->createListItem() );
	}


	public function testCreatePropertyItem()
	{
		$this->assertInstanceOf( \Aimeos\MShop\Common\Item\Property\Iface::class, $this->object->createPropertyItem() );
	}


	public function testDeleteAddressItem()
	{
		$item = \Aimeos\MShop::create( $this->context, 'customer/address' )->create();
		$this->assertSame( $this->object, $this->object->deleteAddressItem( $item ) );
	}


	public function testDeleteListItem()
	{
		$listItem = \Aimeos\MShop::create( $this->context, 'customer/lists' )->create();
		$this->assertSame( $this->object, $this->object->deleteListItem( 'product', $listItem ) );
	}


	public function testDeleteListItemGroup()
	{
		$listItem = \Aimeos\MShop::create( $this->context, 'customer/lists' )->create();

		$this->expectException( \Aimeos\Controller\Frontend\Customer\Exception::class );
		$this->object->deleteListItem( 'group', $listItem );
	}


	public function testDeleteListItemNotAllowed()
	{
		$listItem = \Aimeos\MShop::create( $this->context, 'customer/lists' )->create();

		$this->expectException( \Aimeos\Controller\Frontend\Customer\Exception::class );
		$this->object->deleteListItem( 'service', $listItem );
	}


	public function testDeletePropertyItem()
	{
		$item = \Aimeos\MShop::create( $this->context, 'customer/property' )->create();
		$this->assertSame( $this->object, $this->object->deletePropertyItem( $item ) );
	}


	public function testFind()
	{
		$item = $this->object->uses( ['product'] )->find( 'test@example.com' );

		$this->assertInstanceOf( \Aimeos\MShop\Customer\Item\Iface::class, $item );
		$this->assertEquals( 1, count( $item->getRefItems( 'product' ) ) );
	}


	public function testGet()
	{
		$this->context->setUser( $this->object->find( 'test@example.com' ) );
		$item = $this->object->uses( ['product'] )->get();

		$this->assertInstanceOf( \Aimeos\MShop\Customer\Item\Iface::class, $item );
		$this->assertEquals( 1, count( $item->getRefItems( 'product' ) ) );
	}


	public function testStoreDelete()
	{
		$this->object->add( ['customer.code' => 'cntl-test'] );

		$this->assertSame( $this->object, $this->object->store() );
		$this->assertEquals( 'cntl-test', $this->object->get()->getCode() );
		$this->assertSame( $this->object, $this->object->delete() );
	}


	public function testUses()
	{
		$this->assertSame( $this->object, $this->object->uses( ['product'] ) );
	}


	public function testUsesListDomains()
	{
		$this->context->config()->set( 'controller/frontend/domains', [] );
		$this->context->config()->set( 'controller/frontend/customer/listdomains', ['product', 'group'] );

		$domains = ['customer/address', 'customer/property', 'group', 'product' => ['favorite'], 'text'];
		$expected = ['customer/address', 'customer/property', 'group', 'product' => ['favorite']];
		$property = new \ReflectionProperty( $this->object, 'domains' );

		$this->assertEquals( $expected, $property->getValue( $this->object->uses( $domains ) ) );

		$listItem = \Aimeos\MShop::create( $this->context, 'customer/lists' )->create();

		$this->expectException( \Aimeos\Controller\Frontend\Customer\Exception::class );
		$this->object->addListItem( 'group', $listItem );
	}


	public function testUsesNotAllowed()
	{
		$this->context->setUser( $this->object->find( 'test@example.com' ) );

		$domains = ['customer', 'order' => ['download'], 'product' => ['favorite'], 'service', 'text'];
		$method = new \ReflectionMethod( $this->object, 'checkDomains' );

		$this->assertEquals( ['product' => ['favorite'], 2 => 'text'], $method->invoke( $this->object, $domains ) );
		$this->assertEquals( 1, count( $this->object->uses( $domains )->get()->getRefItems( 'product' ) ) );
	}


	public function testUsesNotAvailable()
	{
		$context = $this->context;
		$item = \Aimeos\MShop::create( $context, 'customer' )->create();
		$productManager = \Aimeos\MShop::create( $context, 'product' );
		$listManager = \Aimeos\MShop::create( $context, 'customer/lists' );

		$item->addListItem( 'product', $listManager->create()->setRefId( '1' ), $productManager->create()->setStatus( 1 ) );
		$item->addListItem( 'product', $listManager->create()->setRefId( '2' ), $productManager->create()->setStatus( 0 ) );
		$item->addListItem( 'product', $listManager->create()->setRefId( '3' ), $productManager->create()->setStatus( 1 )
			->setDateEnd( '2000-01-01 00:00:00' ) );

		$method = new \ReflectionMethod( $this->object, 'checkRefItems' );
		$result = $method->invoke( $this->object, $item );

		$this->assertEquals( 3, count( $result->getListItems( 'product', null, null, false ) ) );
		$this->assertEquals( 1, count( $result->getListItems( 'product', null, null, false )->getRefItem()->filter() ) );
	}
}
