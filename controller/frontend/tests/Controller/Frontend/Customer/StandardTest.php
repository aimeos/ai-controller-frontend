<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2017-2018
 */


namespace Aimeos\Controller\Frontend\Customer;


class StandardTest extends \PHPUnit\Framework\TestCase
{
	private $context;
	private $object;


	protected function setUp()
	{
		$this->context = \TestHelperFrontend::getContext();
		$this->object = new \Aimeos\Controller\Frontend\Customer\Standard( $this->context );
	}


	protected function tearDown()
	{
		unset( $this->context, $this->object );
	}


	public function testAddEditSaveDeleteItem()
	{
		$manager = \Aimeos\MShop\Factory::createManager( $this->context, 'customer' );
		$id = $manager->findItem( 'UTC001' )->getId();

		$this->context->setUserId( $id );
		$item = $this->object->addItem( ['customer.code' => 'unittest-ctnl', 'customer.status' => 1] );
		$this->assertInstanceOf( '\Aimeos\MShop\Customer\Item\Iface', $item );

		$this->context->setUserId( $item->getId() );
		$item = $this->object->editItem( $item->getId(), ['customer.code' => 'unittest-ctnl2'] );
		$this->assertInstanceOf( '\Aimeos\MShop\Customer\Item\Iface', $item );

		$item->setStatus( 0 );
		$item = $this->object->saveItem( $item );
		$this->assertEquals( 0, $item->getStatus() );

		$this->object->deleteItem( $item->getId() );

		$this->setExpectedException( '\Aimeos\MShop\Exception' );
		$manager->findItem( 'unittest-ctnl' );
	}


	public function testAddExistingItem()
	{
		$item = $this->object->addItem( ['customer.code' => 'UTC001'] );
		$this->assertInstanceOf( '\Aimeos\MShop\Customer\Item\Iface', $item );
	}


	public function testCreateItem()
	{
		$result = $this->object->createItem();
		$this->assertInstanceOf( '\Aimeos\MShop\Customer\Item\Iface', $result );
	}


	public function testGetItem()
	{
		$id = \Aimeos\MShop\Factory::createManager( $this->context, 'customer' )->findItem( 'UTC001' )->getId();
		$this->context->setUserId( $id );

		$result = $this->object->getItem( $id, ['customer/address', 'text'] );

		$this->assertInstanceOf( '\Aimeos\MShop\Customer\Item\Iface', $result );
		$this->assertEquals( 1, count( $result->getRefItems( 'text' ) ) );
		$this->assertEquals( 1, count( $result->getAddressItems() ) );
	}


	public function testFindItem()
	{
		$result = $this->object->findItem( 'UTC001' );
		$this->assertInstanceOf( '\Aimeos\MShop\Customer\Item\Iface', $result );
	}


	public function testAddEditSaveDeleteAddressItem()
	{
		$customer = \Aimeos\MShop\Factory::createManager( $this->context, 'customer' )->findItem( 'UTC001' );
		$this->context->setUserId( $customer->getId() );

		$item = $this->object->addAddressItem( ['customer.address.lastname' => 'unittest-ctnl'] );
		$this->assertInstanceOf( '\Aimeos\MShop\Customer\Item\Address\Iface', $item );

		$item = $this->object->editAddressItem( $item->getId(), ['customer.address.lastname' => 'unittest-ctnl2'] );
		$this->assertInstanceOf( '\Aimeos\MShop\Customer\Item\Address\Iface', $item );

		$item->setLastName( 'test' );
		$this->object->saveAddressItem( $item );
		$this->assertEquals( 'test', $item->getLastName() );

		$this->object->deleteAddressItem( $item->getId() );
	}


	public function testCreateAddressItem()
	{
		$result = $this->object->createAddressItem();
		$this->assertInstanceOf( '\Aimeos\MShop\Customer\Item\Address\Iface', $result );
	}


	public function testGetAddressItem()
	{
		$manager = \Aimeos\MShop\Factory::createManager( $this->context, 'customer/address' );
		$search = $manager->createSearch();
		$search->setSlice( 0, 1 );
		$result = $manager->searchItems( $search );

		if( ( $item = reset( $result ) ) === false ) {
			throw new \RuntimeException( 'No customer address available' );
		}

		$this->context->setUserId( $item->getParentId() );
		$result = $this->object->getAddressItem( $item->getId() );
		$this->assertInstanceOf( '\Aimeos\MShop\Customer\Item\Address\Iface', $result );
	}


	public function testAddEditDeleteListItem()
	{
		$customer = \Aimeos\MShop\Factory::createManager( $this->context, 'customer' )->findItem( 'UTC001' );
		$this->context->setUserId( $customer->getId() );

		$values = [
			'customer.lists.type' => 'favorite',
			'customer.lists.domain' => 'product',
			'customer.lists.refid' => '-1'
		];

		$item = $this->object->addListItem( $values );
		$this->assertInstanceOf( '\Aimeos\MShop\Common\Item\Lists\Iface', $item );

		$values = [
			'customer.lists.type' => 'favorite',
			'customer.lists.domain' => 'product',
			'customer.lists.refid' => '-2'
		];

		$item = $this->object->editListItem( $item->getId(), $values );
		$this->assertInstanceOf( '\Aimeos\MShop\Common\Item\Lists\Iface', $item );

		$this->object->deleteListItem( $item->getId() );

		$this->setExpectedException( '\Aimeos\MShop\Exception' );
		$this->object->getListItem( $item->getId() );
	}


	public function testGetListItem()
	{
		$manager = \Aimeos\MShop\Factory::createManager( $this->context, 'customer/lists' );
		$search = $manager->createSearch();
		$search->setSlice( 0, 1 );
		$result = $manager->searchItems( $search );

		if( ( $item = reset( $result ) ) === false ) {
			throw new \RuntimeException( 'No customer lists item available' );
		}

		$this->context->setUserId( $item->getParentId() );
		$result = $this->object->getListItem( $item->getId() );
		$this->assertInstanceOf( '\Aimeos\MShop\Common\Item\Lists\Iface', $result );
	}


	public function testSearchListItem()
	{
		$manager = \Aimeos\MShop\Factory::createManager( $this->context, 'customer' );
		$customer = $manager->findItem( 'UTC001' );
		$this->context->setUserId( $customer->getId() );

		$filter = $this->object->createListsFilter();
		$result = $this->object->searchListItems( $filter );

		foreach( $result as $item )
		{
			$this->assertEquals( $customer->getId(), $item->getParentId() );
			$this->assertInstanceOf( '\Aimeos\MShop\Common\Item\Lists\Iface', $item );
		}
	}
}
