<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2018
 */


namespace Aimeos\Controller\Frontend\Subscription;


class StandardTest extends \PHPUnit\Framework\TestCase
{
	private $object;
	private $context;


	protected function setUp()
	{
		\Aimeos\MShop\Factory::setCache( true );

		$this->context = \TestHelperFrontend::getContext();
		$this->object = new \Aimeos\Controller\Frontend\Subscription\Standard( $this->context );
	}


	protected function tearDown()
	{
		unset( $this->object, $this->context );

		\Aimeos\MShop\Factory::setCache( false );
		\Aimeos\MShop\Factory::clear();
	}


	public function testCancel()
	{
		$this->context->setUserId( $this->getCustomerId() );
		$item = \Aimeos\MShop\Factory::createManager( $this->context, 'subscription' )->createItem();

		$cntl = $this->getMockBuilder( '\Aimeos\Controller\Frontend\Subscription\Standard' )
			->setConstructorArgs( [$this->context] )
			->setMethods( ['saveItem'] )
			->getMock();

		$cntl->expects( $this->once() )->method( 'saveItem' )
			->will( $this->returnValue( $item ) );

		$this->assertInstanceOf( '\Aimeos\MShop\Subscription\Item\Iface', $cntl->cancel( $this->getSubscriptionId() ) );
	}


	public function testCreateFilter()
	{
		$this->assertInstanceOf( '\Aimeos\MW\Criteria\Iface', $this->object->createFilter() );
	}


	public function testGetIntervals()
	{
		$this->assertGreaterThan( 0, count( $this->object->getIntervals() ) );
	}


	public function testGetItem()
	{
		$id = $this->getSubscriptionId();
		$this->context->setUserId( $this->getCustomerId() );

		$this->assertInstanceOf( '\Aimeos\MShop\Subscription\Item\Iface', $this->object->getItem( $id ) );
	}


	public function testGetItemException()
	{
		$this->setExpectedException( '\Aimeos\Controller\Frontend\Subscription\Exception' );
		$this->object->getItem( -1 );
	}


	public function testSaveItem()
	{
		$manager = $this->getMockBuilder( '\\Aimeos\\MShop\\Subscription\\Manager\\Standard' )
			->setConstructorArgs( [$this->context] )
			->setMethods( ['saveItem'] )
			->getMock();

		\Aimeos\MShop\Factory::injectManager( $this->context, 'subscription', $manager );

		$manager->expects( $this->once() )->method( 'saveItem' )
			->will( $this->returnValue( $manager->createItem() ) );

		$this->assertInstanceOf( '\Aimeos\MShop\Subscription\Item\Iface', $this->object->saveItem( $manager->createItem() ) );
	}


	public function testSearchItems()
	{
		$this->assertGreaterThan( 1, $this->object->searchItems( $this->object->createFilter() ) );
	}

	protected function getCustomerId()
	{
		$manager = \Aimeos\MShop\Factory::createManager( $this->context, 'customer' );
		return $manager->findItem( 'UTC001' )->getId();
	}


	protected function getSubscriptionId()
	{
		$manager = \Aimeos\MShop\Factory::createManager( $this->context, 'subscription' );
		$search = $manager->createSearch()->setSlice( 0, 1 );
		$search->setConditions( $search->compare( '==', 'order.base.customerid', $this->getCustomerId() ) );
		$result = $manager->searchItems( $search );

		if( ( $item = reset( $result ) ) === false ) {
			throw new \RuntimeException( 'No subscription item found' );
		}

		return $item->getId();
	}
}
