<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Metaways Infosystems GmbH, 2014
 * @copyright Aimeos (aimeos.org), 2015-2017
 */


namespace Aimeos\Controller\Frontend\Order;


class StandardTest extends \PHPUnit_Framework_TestCase
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

		$manager->expects( $this->once() )->method( 'saveItem' );

		$this->assertInstanceOf( '\Aimeos\MShop\Order\Item\Iface', $this->object->addItem( -1, 'test' ) );
	}


	public function testCreateFilter()
	{
		$this->assertInstanceOf( '\Aimeos\MW\Criteria\Iface', $this->object->createFilter() );
	}


	public function testGetItem()
	{
		$manager = \Aimeos\MShop\Factory::createManager( $this->context, 'customer' );
		$customerItem = $manager->findItem( 'UTC001' );

		$this->context->setUserId( $customerItem->getId() );

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
		$manager = \Aimeos\MShop\Factory::createManager( $this->context, 'order' );
		$search = $manager->createSearch()->setSlice( 0, 1 );
		$result = $manager->searchItems( $search );

		if( ( $item = reset( $result ) ) === false ) {
			throw new \RuntimeException( 'No order item found' );
		}

		$this->setExpectedException( '\Aimeos\Controller\Frontend\Order\Exception' );
		$this->object->getItem( $item->getId() );
	}


	public function testSearchItems()
	{
		$this->assertGreaterThan( 1, $this->object->searchItems( $this->object->createFilter() ) );
	}


	public function testStore()
	{
		$name = 'ControllerFrontendOrderStore';
		$this->context->getConfig()->set( 'mshop/order/manager/name', $name );


		$orderManagerStub = $this->getMockBuilder( '\\Aimeos\\MShop\\Order\\Manager\\Standard' )
			->setMethods( array( 'saveItem', 'getSubManager' ) )
			->setConstructorArgs( array( $this->context ) )
			->getMock();

		$orderBaseManagerStub = $this->getMockBuilder( '\\Aimeos\\MShop\\Order\\Manager\\Base\\Standard' )
			->setMethods( array( 'store' ) )
			->setConstructorArgs( array( $this->context ) )
			->getMock();

		\Aimeos\MShop\Order\Manager\Factory::injectManager( '\\Aimeos\\MShop\\Order\\Manager\\' . $name, $orderManagerStub );


		$orderBaseItem = $orderBaseManagerStub->createItem();
		$orderBaseItem->setId( 1 );


		$orderBaseManagerStub->expects( $this->once() )->method( 'store' );

		$orderManagerStub->expects( $this->once() )->method( 'getSubManager' )
			->will( $this->returnValue( $orderBaseManagerStub ) );

		$orderManagerStub->expects( $this->once() )->method( 'saveItem' );


		$this->object->store( $orderBaseItem );
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
