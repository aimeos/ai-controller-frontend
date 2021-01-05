<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2017-2021
 */


namespace Aimeos\Controller\Frontend\Customer;


class StandardTest extends \PHPUnit\Framework\TestCase
{
	private $context;
	private $object;


	protected function setUp() : void
	{
		$this->context = \TestHelperFrontend::getContext();
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
		$this->assertSame( $this->object, $this->object->addListItem( 'customer', $listItem ) );
	}


	public function testAddListItemGroup()
	{
		$listItem = \Aimeos\MShop::create( $this->context, 'customer/lists' )->create();

		$this->expectException( \Aimeos\Controller\Frontend\Customer\Exception::class );
		$this->object->addListItem( 'customer/group', $listItem );
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
		$this->assertSame( $this->object, $this->object->deleteListItem( 'customer', $listItem ) );
	}


	public function testDeleteListItemGroup()
	{
		$listItem = \Aimeos\MShop::create( $this->context, 'customer/lists' )->create();

		$this->expectException( \Aimeos\Controller\Frontend\Customer\Exception::class );
		$this->object->deleteListItem( 'customer/group', $listItem );
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
		$this->context->setUserId( $this->object->find( 'test@example.com' )->getId() );
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
}
