<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Metaways Infosystems GmbH, 2014
 * @copyright Aimeos (aimeos.org), 2015-2021
 */


namespace Aimeos\Controller\Frontend\Order;


class StandardTest extends \PHPUnit\Framework\TestCase
{
	private $object;
	private $context;


	protected function setUp() : void
	{
		\Aimeos\MShop::cache( true );

		$this->context = \TestHelperFrontend::getContext();
		$this->object = new \Aimeos\Controller\Frontend\Order\Standard( $this->context );
	}


	protected function tearDown() : void
	{
		\Aimeos\MShop::cache( false );
		unset( $this->object, $this->context );
	}


	public function testAdd()
	{
		$this->assertSame( $this->object, $this->object->add( -1, [] ) );
	}


	public function testCompare()
	{
		$this->assertSame( $this->object, $this->object->compare( '==', 'order.type', 'test' ) );
	}


	public function testGet()
	{
		$manager = \Aimeos\MShop::create( $this->context, 'order' );
		$items = $manager->search( $manager->filter()->slice( 0, 1 ) );

		if( ( $item = $items->first() ) === null ) {
			throw new \RuntimeException( 'No order item found' );
		}

		$this->assertEquals( $item, $this->object->get( $item->getId(), false ) );
	}


	public function testParse()
	{
		$this->assertSame( $this->object, $this->object->parse( [] ) );
	}


	public function testSave()
	{
		$manager = $this->getMockBuilder( \Aimeos\MShop\Order\Manager\Standard::class )
			->setConstructorArgs( [$this->context] )
			->setMethods( ['save'] )
			->getMock();

		\Aimeos\MShop::inject( 'order', $manager );

		$item = $manager->create();
		$object = new \Aimeos\Controller\Frontend\Order\Standard( $this->context );

		$manager->expects( $this->once() )->method( 'save' )->will( $this->returnArgument( 0 ) );

		$this->assertInstanceOf( \Aimeos\MShop\Order\Item\Iface::class, $object->save( $item ) );
	}


	public function testSearch()
	{
		$userId = \Aimeos\MShop::create( $this->context, 'customer' )->find( 'test@example.com' )->getId();
		$this->context->setUserId( $userId );

		$total = 0;
		$object = new \Aimeos\Controller\Frontend\Order\Standard( $this->context );
		$items = $object->uses( ['order/base'] )->search( $total );

		$this->assertGreaterThanOrEqual( 4, $items->count() );
		$this->assertGreaterThanOrEqual( 4, $total );

		foreach( $items as $item ) {
			$this->assertInstanceOf( \Aimeos\MShop\Order\Item\Base\Iface::class, $item->getBaseItem() );
		}
	}


	public function testSlice()
	{
		$this->assertSame( $this->object, $this->object->slice( 0, 100 ) );
	}


	public function testSort()
	{
		$this->assertSame( $this->object, $this->object->sort( '-order.type,order.id' ) );
	}


	public function testStore()
	{
		$class = \Aimeos\Controller\Common\Order\Standard::class;

		$manager = $this->getMockBuilder( \Aimeos\MShop\Order\Manager\Standard::class )
			->setConstructorArgs( [$this->context] )
			->setMethods( ['save'] )
			->getMock();

		\Aimeos\MShop::inject( 'order', $manager );

		$stub = $this->getMockBuilder( $class )
			->setConstructorArgs( [$this->context] )
			->setMethods( ['block'] )
			->getMock();

		$object = new \Aimeos\Controller\Frontend\Order\Standard( $this->context );
		$object->add( -1 );

		$item = $manager->create()->setBaseId( -1 );

		$manager->expects( $this->once() )->method( 'save' )->will( $this->returnValue( $item ) );
		$stub->expects( $this->once() )->method( 'block' )->will( $this->returnValue( $item ) );

		\Aimeos\Controller\Common\Order\Factory::inject( $class, $stub );
		$this->assertEquals( $item, $object->store() );
		\Aimeos\Controller\Common\Order\Factory::inject( $class, null );
	}


	public function testStoreLimit()
	{
		$this->context->getConfig()->set( 'controller/frontend/order/limit-count', 1 );
		$this->context->getConfig()->set( 'controller/frontend/order/limit-seconds', 86400 * 365 );

		$manager = \Aimeos\MShop::create( $this->context, 'order/base' );
		$filter = $manager->filter()->add( 'order.base.price', '==', '53.50' );
		$items = $manager->search( $filter->slice( 0, 1 ) );

		$item = $items->first( new \RuntimeException( 'No order base item found' ) );

		$this->expectException( \Aimeos\Controller\Frontend\Order\Exception::class );
		$this->object->add( $item->getId() )->store();
	}


	public function testUses()
	{
		$this->assertSame( $this->object, $this->object->uses( ['order/base'] ) );
	}
}
