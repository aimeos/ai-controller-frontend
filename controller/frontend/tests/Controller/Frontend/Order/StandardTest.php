<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Metaways Infosystems GmbH, 2014
 * @copyright Aimeos (aimeos.org), 2015-2018
 */


namespace Aimeos\Controller\Frontend\Order;


class StandardTest extends \PHPUnit\Framework\TestCase
{
	private $object;
	private $context;


	protected function setUp()
	{
		\Aimeos\MShop\Factory::setCache( true );

		$this->context = \TestHelperFrontend::getContext();
		$this->object = new \Aimeos\Controller\Frontend\Order\Standard( $this->context );
	}


	protected function tearDown()
	{
		unset( $this->object, $this->context );

		\Aimeos\MShop\Factory::setCache( false );
		\Aimeos\MShop\Factory::clear();
	}


	public function testAddItem()
	{
		$manager = $this->getMockBuilder( '\\Aimeos\\MShop\\Order\\Manager\\Standard' )
			->setConstructorArgs( [$this->context] )
			->setMethods( ['saveItem'] )
			->getMock();

		\Aimeos\MShop\Factory::injectManager( $this->context, 'order', $manager );

		$manager->expects( $this->once() )->method( 'saveItem' )
			->will( $this->returnValue( $manager->createItem() ) );

		$this->assertInstanceOf( '\Aimeos\MShop\Order\Item\Iface', $this->object->addItem( -1, 'test' ) );
	}


	public function testAddItemLimit()
	{
		$this->context->getConfig()->set( 'controller/frontend/order/limit-seconds', 86400 * 365 );

		$manager = \Aimeos\MShop\Factory::createManager( $this->context, 'order/base' );
		$result = $manager->searchItems( $manager->createSearch()->setSlice( 0, 1 ) );

		if( ( $item = reset( $result ) ) === false ) {
			throw new \RuntimeException( 'No order item found' );
		}

		$this->setExpectedException( '\Aimeos\Controller\Frontend\Order\Exception' );
		$this->object->addItem( $item->getId(), 'test' );
	}


	public function testCreateFilter()
	{
		$this->assertInstanceOf( '\Aimeos\MW\Criteria\Iface', $this->object->createFilter() );
	}


	public function testGetItem()
	{
		$manager = \Aimeos\MShop\Factory::createManager( $this->context, 'customer' );
		$customerItem = $manager->findItem( 'UTC001' );

		$this->context->setEditor( 'core:unittest' );

		$manager = \Aimeos\MShop\Factory::createManager( $this->context, 'order' );
		$search = $manager->createSearch()->setSlice( 0, 1 );
		$search->setConditions( $search->compare( '==', 'order.base.customerid', $customerItem->getId() ) );
		$result = $manager->searchItems( $search );

		if( ( $item = reset( $result ) ) === false ) {
			throw new \RuntimeException( 'No order item found' );
		}

		$this->assertInstanceOf( '\Aimeos\MShop\Order\Item\Iface', $this->object->getItem( $item->getId() ) );
	}


	public function testGetItemException()
	{
		$this->setExpectedException( '\Aimeos\Controller\Frontend\Order\Exception' );
		$this->object->getItem( -1 );
	}


	public function testSaveItem()
	{
		$manager = $this->getMockBuilder( '\\Aimeos\\MShop\\Order\\Manager\\Standard' )
			->setConstructorArgs( [$this->context] )
			->setMethods( ['saveItem'] )
			->getMock();

		\Aimeos\MShop\Factory::injectManager( $this->context, 'order', $manager );

		$manager->expects( $this->once() )->method( 'saveItem' )
			->will( $this->returnValue( $manager->createItem() ) );

		$this->assertInstanceOf( '\Aimeos\MShop\Order\Item\Iface', $this->object->saveItem( $manager->createItem() ) );
	}


	public function testSearchItems()
	{
		$this->assertGreaterThan( 1, $this->object->searchItems( $this->object->createFilter() ) );
	}


	public function testBlock()
	{
		$name = 'ControllerFrontendOrderBlock';
		$this->context->getConfig()->set( 'controller/common/order/name', $name );


		$orderCntlStub = $this->getMockBuilder( '\\Aimeos\\Controller\\Common\\Order\\Standard' )
			->setMethods( array( 'block' ) )
			->setConstructorArgs( array( $this->context ) )
			->getMock();

		\Aimeos\Controller\Common\Order\Factory::injectController( '\\Aimeos\\Controller\\Common\\Order\\' . $name, $orderCntlStub );

		$orderCntlStub->expects( $this->once() )->method( 'block' );


		$this->object->block( \Aimeos\MShop\Factory::createManager( $this->context, 'order' )->createItem() );
	}


	public function testUnblock()
	{
		$name = 'ControllerFrontendOrderUnblock';
		$this->context->getConfig()->set( 'controller/common/order/name', $name );


		$orderCntlStub = $this->getMockBuilder( '\\Aimeos\\Controller\\Common\\Order\\Standard' )
			->setMethods( array( 'unblock' ) )
			->setConstructorArgs( array( $this->context ) )
			->getMock();

		\Aimeos\Controller\Common\Order\Factory::injectController( '\\Aimeos\\Controller\\Common\\Order\\' . $name, $orderCntlStub );

		$orderCntlStub->expects( $this->once() )->method( 'unblock' );


		$this->object->unblock( \Aimeos\MShop\Factory::createManager( $this->context, 'order' )->createItem() );
	}


	public function testUpdate()
	{
		$name = 'ControllerFrontendOrderUpdate';
		$this->context->getConfig()->set( 'controller/common/order/name', $name );


		$orderCntlStub = $this->getMockBuilder( '\\Aimeos\\Controller\\Common\\Order\\Standard' )
			->setMethods( array( 'update' ) )
			->setConstructorArgs( array( $this->context ) )
			->getMock();

		\Aimeos\Controller\Common\Order\Factory::injectController( '\\Aimeos\\Controller\\Common\\Order\\' . $name, $orderCntlStub );

		$orderCntlStub->expects( $this->once() )->method( 'update' );


		$this->object->update( \Aimeos\MShop\Factory::createManager( $this->context, 'order' )->createItem() );
	}
}
