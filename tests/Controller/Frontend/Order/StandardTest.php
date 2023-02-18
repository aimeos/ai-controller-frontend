<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Metaways Infosystems GmbH, 2014
 * @copyright Aimeos (aimeos.org), 2015-2023
 */


namespace Aimeos\Controller\Frontend\Order;


class StandardTest extends \PHPUnit\Framework\TestCase
{
	private $object;
	private $context;


	protected function setUp() : void
	{
		\Aimeos\MShop::cache( true );

		$this->context = \TestHelper::context();
		$this->object = new \Aimeos\Controller\Frontend\Order\Standard( $this->context );
	}


	protected function tearDown() : void
	{
		\Aimeos\MShop::cache( false );
		unset( $this->object, $this->context );
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
			->onlyMethods( ['save'] )
			->getMock();

		\Aimeos\MShop::inject( \Aimeos\MShop\Order\Manager\Standard::class, $manager );

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
		$items = $object->uses( ['order'] )->search( $total );

		$this->assertGreaterThanOrEqual( 4, $items->count() );
		$this->assertGreaterThanOrEqual( 4, $total );
	}


	public function testSlice()
	{
		$this->assertSame( $this->object, $this->object->slice( 0, 100 ) );
	}


	public function testSort()
	{
		$this->assertSame( $this->object, $this->object->sort( '-order.type,order.id' ) );
	}


	public function testUses()
	{
		$this->assertSame( $this->object, $this->object->uses( ['order'] ) );
	}
}
