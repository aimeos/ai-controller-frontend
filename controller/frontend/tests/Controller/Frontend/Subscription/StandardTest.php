<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2018-2021
 */


namespace Aimeos\Controller\Frontend\Subscription;


class StandardTest extends \PHPUnit\Framework\TestCase
{
	private $context;
	private $manager;
	private $object;


	protected function setUp() : void
	{
		$this->context = \TestHelperFrontend::getContext();
		$this->context->setUserId( $this->getCustomerId() );

		$this->manager = $this->getMockBuilder( '\\Aimeos\\MShop\\Subscription\\Manager\\Standard' )
			->setConstructorArgs( [$this->context] )
			->setMethods( ['save'] )
			->getMock();

		\Aimeos\MShop::cache( true );
		\Aimeos\MShop::inject( 'subscription', $this->manager );

		$this->object = new \Aimeos\Controller\Frontend\Subscription\Standard( $this->context );
	}


	protected function tearDown() : void
	{
		\Aimeos\MShop::cache( false );
		unset( $this->object, $this->manager, $this->context );
	}


	public function testCancel()
	{
		$expected = \Aimeos\MShop\Subscription\Item\Iface::class;
		$item = \Aimeos\MShop::create( $this->context, 'subscription' )->create();

		$this->manager->expects( $this->once() )->method( 'save' )
			->will( $this->returnValue( $item ) );

		$this->assertInstanceOf( $expected, $this->object->cancel( $this->getSubscriptionId() ) );
	}


	public function testCompare()
	{
		$this->assertEquals( 2, count( $this->object->compare( '>=', 'subscription.datenext', '2000-01-01' )->search() ) );
	}


	public function testGet()
	{
		$expected = \Aimeos\MShop\Subscription\Item\Iface::class;
		$this->assertInstanceOf( $expected, $this->object->get( $this->getSubscriptionId() ) );
	}


	public function testGetException()
	{
		$this->expectException( \Aimeos\Controller\Frontend\Subscription\Exception::class );
		$this->object->get( -1 );
	}


	public function testGetIntervals()
	{
		$this->assertGreaterThan( 0, count( $this->object->getIntervals() ) );
	}


	public function testParse()
	{
		$cond = ['&&' => [['==' => ['subscription.datenext' => '2000-01-01']], ['==' => ['subscription.status' => 1]]]];
		$this->assertEquals( 1, count( $this->object->parse( $cond )->search() ) );
	}


	public function testSave()
	{
		$item = $this->manager->create();
		$expected = \Aimeos\MShop\Subscription\Item\Iface::class;

		$this->manager->expects( $this->once() )->method( 'save' )
			->will( $this->returnValue( $item ) );

		$this->assertInstanceOf( $expected, $this->object->save( $item ) );
	}


	public function testSearch()
	{
		$total = 0;
		$this->assertGreaterThanOrEqual( 2, count( $this->object->search( $total ) ) );
		$this->assertGreaterThanOrEqual( 2, $total );
	}


	public function testSlice()
	{
		$this->assertEquals( 1, count( $this->object->slice( 0, 1 )->search() ) );
	}


	public function testSort()
	{
		$this->assertEquals( 2, count( $this->object->sort()->search() ) );
	}


	public function testSortInterval()
	{
		$this->assertEquals( 2, count( $this->object->sort( 'interval' )->search() ) );
	}


	public function testSortGeneric()
	{
		$this->assertEquals( 2, count( $this->object->sort( 'subscription.dateend' )->search() ) );
	}


	public function testSortMultiple()
	{
		$this->assertEquals( 2, count( $this->object->sort( 'subscription.dateend,-subscription.id' )->search() ) );
	}


	protected function getCustomerId()
	{
		return \Aimeos\MShop::create( $this->context, 'customer' )->find( 'test@example.com' )->getId();
	}


	protected function getSubscriptionId()
	{
		$manager = \Aimeos\MShop::create( $this->context, 'subscription' );

		$search = $manager->filter()->slice( 0, 1 );
		$search->setConditions( $search->compare( '==', 'order.base.customerid', $this->context->getUserId() ) );

		if( ( $item = $manager->search( $search )->first() ) === null ) {
			throw new \RuntimeException( 'No subscription item found' );
		}

		return $item->getId();
	}
}
