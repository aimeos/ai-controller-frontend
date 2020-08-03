<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2020
 */


namespace Aimeos\Controller\Frontend\Review;


class StandardTest extends \PHPUnit\Framework\TestCase
{
	private $context;
	private $manager;
	private $object;


	protected function setUp() : void
	{
		$this->context = \TestHelperFrontend::getContext();

		$this->manager = $this->getMockBuilder( '\\Aimeos\\MShop\\Review\\Manager\\Standard' )
			->setConstructorArgs( [$this->context] )
			->setMethods( ['delete', 'saveItem'] )
			->getMock();

		\Aimeos\MShop::cache( true );
		\Aimeos\MShop::inject( 'review', $this->manager );

		$this->object = new \Aimeos\Controller\Frontend\Review\Standard( $this->context );
	}


	protected function tearDown() : void
	{
		\Aimeos\MShop::cache( false );
		unset( $this->object, $this->manager, $this->context );
	}


	public function testCompare()
	{
		$this->assertCount( 1, $this->object->compare( '==', 'review.domain', 'customer' )->search() );
	}


	public function testDelete()
	{
		$this->context->setUserId( \Aimeos\MShop::create( $this->context, 'customer' )->find( 'test@example.com' )->getId() );

		$this->manager->expects( $this->once() )->method( 'delete' );

		$this->assertSame( $this->object, $this->object->delete( $this->getReviewItem()->getId() ) );
	}


	public function testDeleteException()
	{
		$this->expectException( \Aimeos\Controller\Frontend\Review\Exception::class );
		$this->object->delete( $this->getReviewItem()->getId() );
	}


	public function testDomain()
	{
		$this->assertCount( 1, $this->object->domain( 'customer' )->search() );
	}


	public function testFor()
	{
		$refId = \Aimeos\MShop::create( $this->context, 'product' )->find( 'CNE' )->getId();
		$result = $this->object->for( 'product', $refId );

		$this->assertInstanceOf( \Aimeos\Controller\Frontend\Review\Iface::class, $result );
		$this->assertCount( 1, $result->search() );
	}


	public function testGet()
	{
		$expected = \Aimeos\MShop\Review\Item\Iface::class;
		$this->assertInstanceOf( $expected, $this->object->get( $this->getReviewItem()->getId() ) );
	}


	public function testList()
	{
		$total = 0;
		$this->context->setUserId( \Aimeos\MShop::create( $this->context, 'customer' )->find( 'test@example.com' )->getId() );

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
		$item = $this->getReviewItem();
		$expected = \Aimeos\MShop\Review\Item\Iface::class;
		$this->context->setUserId( \Aimeos\MShop::create( $this->context, 'customer' )->find( 'test@example.com' )->getId() );

		$this->manager->expects( $this->once() )->method( 'saveItem' )
			->will( $this->returnValue( $item ) );

		$this->assertInstanceOf( $expected, $this->object->save( $item ) );
	}


	public function testSaveException()
	{
		$this->expectException( \Aimeos\Controller\Frontend\Review\Exception::class );
		$this->object->save( $this->manager->createItem() );
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


	public function testSortMtime()
	{
		$this->assertEquals( 2, count( $this->object->sort( 'mtime' )->search() ) );
	}


	public function testSortRating()
	{
		$this->assertEquals( 2, count( $this->object->sort( 'rating' )->search() ) );
	}


	public function testSortGeneric()
	{
		$this->assertEquals( 2, count( $this->object->sort( 'review.customerid' )->search() ) );
	}


	protected function getReviewItem()
	{
		$manager = \Aimeos\MShop::create( $this->context, 'review' );

		$search = $manager->createSearch()->setSlice( 0, 1 );
		$search->setConditions( $search->combine( '&&', [
			$search->compare( '==', 'review.domain', 'product' ),
			$search->compare( '>', 'review.status', 0 )
		] ) );

		return $manager->searchItems( $search )->first( new \RuntimeException( 'No review item found' ) );
	}
}
