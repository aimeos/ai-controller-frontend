<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2020-2024
 */


namespace Aimeos\Controller\Frontend\Review;


class StandardTest extends \PHPUnit\Framework\TestCase
{
	private $context;
	private $manager;
	private $object;


	protected function setUp() : void
	{
		\Aimeos\MShop::cache( true );

		$this->context = \TestHelper::context();

		$this->manager = $this->getMockBuilder( '\\Aimeos\\MShop\\Review\\Manager\\Standard' )
			->setConstructorArgs( [$this->context] )
			->onlyMethods( ['delete', 'save', 'type'] )
			->getMock();

		$this->manager->method( 'type' )->willReturn( ['review'] );

		\Aimeos\MShop::inject( '\\Aimeos\\MShop\\Review\\Manager\\Standard', $this->manager );

		$this->object = new \Aimeos\Controller\Frontend\Review\Standard( $this->context );
	}


	protected function tearDown() : void
	{
		\Aimeos\MShop::cache( false );
		unset( $this->object, $this->manager, $this->context );
	}


	public function testAggregate()
	{
		$list = $this->object->domain( 'product' )->aggregate( 'review.rating' );

		$this->assertEquals( 1, count( $list ) );
		$this->assertEquals( 1, $list[4] );
	}


	public function testCompare()
	{
		$this->assertCount( 1, $this->object->compare( '==', 'review.domain', 'customer' )->search() );
	}


	public function testCreate()
	{
		$item = $this->object->create( ['review.rating' => 5] );
		$this->assertEquals( 5, $item->getRating() );
	}


	public function testDelete()
	{
		$this->context->setUser( \Aimeos\MShop::create( $this->context, 'customer' )->find( 'test@example.com' ) );

		$this->manager->expects( $this->once() )->method( 'delete' );

		$this->assertSame( $this->object, $this->object->delete( $this->getReviewItem()->getId() ) );
	}


	public function testDomain()
	{
		$this->assertCount( 1, $this->object->domain( 'customer' )->search() );
	}


	public function testFor()
	{
		$refId = \Aimeos\MShop::create( $this->context, 'product' )->find( 'CNE' )->getId();
		$result = $this->object->for( 'product', [$refId] );

		$this->assertInstanceOf( \Aimeos\Controller\Frontend\Review\Iface::class, $result );
		$this->assertCount( 1, $result->search() );
	}


	public function testForDomain()
	{
		$result = $this->object->for( 'product', null );

		$this->assertInstanceOf( \Aimeos\Controller\Frontend\Review\Iface::class, $result );
		$this->assertCount( 1, $result->search() );
	}


	public function testGet()
	{
		$this->context->setUser( \Aimeos\MShop::create( $this->context, 'customer' )->find( 'test@example.com' ) );
		$result = $this->object->get( $this->getReviewItem() );
		$this->assertInstanceOf( \Aimeos\MShop\Review\Item\Iface::class, $result );
	}


	public function testList()
	{
		$total = 0;
		$this->context->setUser( \Aimeos\MShop::create( $this->context, 'customer' )->find( 'test@example.com' ) );

		$this->assertEquals( 3, count( $this->object->slice( 0, 3 )->list( $total ) ) );
		$this->assertGreaterThanOrEqual( 4, $total );

		$this->assertEquals( 1, count( $this->object->domain( 'product' )->slice( 0, 1 )->list( $total ) ) );
		$this->assertGreaterThanOrEqual( 2, $total );
	}


	public function testParse()
	{
		$cond = ['&&' => [['==' => ['review.domain' => 'customer']], ['==' => ['review.status' => 1]]]];
		$this->assertEquals( 1, count( $this->object->parse( $cond )->search() ) );
	}


	public function testSave()
	{
		$customer = \Aimeos\MShop::create( $this->context, 'customer' )->find( 'test@example.com' );
		$this->context->setUser( $customer );
		$item = $this->getReviewItem();

		$this->manager->expects( $this->once() )->method( 'save' )
			->willReturn( $item );

		$this->assertInstanceOf( \Aimeos\MShop\Review\Item\Iface::class, $this->object->save( $item ) );
	}


	public function testSaveCreate()
	{
		$customer = \Aimeos\MShop::create( $this->context, 'customer' )->find( 'test@example.com' );
		$this->context->setUser( $customer );

		$item = $this->object->create( $this->getReviewItem()->setId( null )->toArray( true ) );

		$this->manager->expects( $this->once() )->method( 'save' )
			->willReturn( ( clone $item )->setId( 123 ) );

		$this->assertInstanceOf( \Aimeos\MShop\Review\Item\Iface::class, $this->object->save( $item ) );
	}


	public function testSaveInvalidDomain()
	{
		$this->expectException( \Aimeos\Controller\Frontend\Review\Exception::class );
		$this->object->save( $this->getReviewItem()->setDomain( 'invalid' ) );
	}


	public function testSaveInvalidOrderProductId()
	{
		$this->expectException( \Aimeos\Controller\Frontend\Review\Exception::class );
		$this->object->save( $this->getReviewItem()->setOrderProductId( 0 ) );
	}


	public function testSaveException()
	{
		$this->expectException( \Aimeos\Controller\Frontend\Review\Exception::class );
		$this->object->save( $this->manager->create() );
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


	public function testSortCtime()
	{
		$this->assertEquals( 2, count( $this->object->sort( 'ctime' )->search() ) );
	}


	public function testSortRating()
	{
		$this->assertEquals( 2, count( $this->object->sort( 'rating' )->search() ) );
	}


	public function testSortGeneric()
	{
		$this->assertEquals( 2, count( $this->object->sort( 'review.mtime' )->search() ) );
	}


	protected function getReviewItem()
	{
		$manager = \Aimeos\MShop::create( $this->context, 'review' );

		$search = $manager->filter()->slice( 0, 1 );
		$search->setConditions( $search->and( [
			$search->compare( '==', 'review.domain', 'product' ),
			$search->compare( '>', 'review.status', 0 )
		] ) );

		return $manager->search( $search )->first( new \RuntimeException( 'No review item found' ) );
	}
}
