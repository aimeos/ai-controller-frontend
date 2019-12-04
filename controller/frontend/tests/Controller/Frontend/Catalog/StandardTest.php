<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Metaways Infosystems GmbH, 2012
 * @copyright Aimeos (aimeos.org), 2015-2018
 */


namespace Aimeos\Controller\Frontend\Catalog;


class StandardTest extends \PHPUnit\Framework\TestCase
{
	private $context;
	private $object;


	protected function setUp()
	{
		$this->context = \TestHelperFrontend::getContext();
		$this->object = new \Aimeos\Controller\Frontend\Catalog\Standard( $this->context );
	}


	protected function tearDown()
	{
		unset( $this->object, $this->context );
	}


	public function testCompare()
	{
		$list = $this->object->compare( '==', 'catalog.code', 'categories' )->getTree()->toList();
		$this->assertEquals( 1, count( $list ) );
	}


	public function testFind()
	{
		$item = $this->object->uses( ['product'] )->find( 'cafe' );

		$this->assertInstanceOf( \Aimeos\MShop\Catalog\Item\Iface::class, $item );
		$this->assertEquals( 2, count( $item->getRefItems( 'product' ) ) );
	}


	public function testGet()
	{
		$item = \Aimeos\MShop::create( $this->context, 'catalog' )->findItem( 'cafe' );
		$item = $this->object->uses( ['product'] )->get( $item->getId() );

		$this->assertInstanceOf( \Aimeos\MShop\Catalog\Item\Iface::class, $item );
		$this->assertEquals( 2, count( $item->getRefItems( 'product' ) ) );
	}


	public function testGetPath()
	{
		$item = \Aimeos\MShop::create( $this->context, 'catalog' )->findItem( 'cafe', [] );
		$items = $this->object->uses( ['product'] )->getPath( $item->getId() );

		$this->assertEquals( 3, count( $items ) );
		$this->assertEquals( 2, count( current( array_reverse( $items, true ) )->getRefItems( 'product' ) ) );

		foreach( $items as $item ) {
			$this->assertInstanceOf( \Aimeos\MShop\Catalog\Item\Iface::class, $item );
		}
	}


	public function testGetTree()
	{
		$tree = $this->object->uses( ['product'] )->getTree();

		foreach( $tree->toList() as $item ) {
			$this->assertInstanceOf( \Aimeos\MShop\Catalog\Item\Iface::class, $item );
		}

		$this->assertEquals( 2, count( $tree->getChildren() ) );
		$this->assertEquals( 4, count( current( array_reverse( $tree->toList(), true ) )->getRefItems( 'product' ) ) );
	}


	public function testParse()
	{
		$cond = ['>' => ['catalog.status' => 0]];
		$this->assertEquals( 8, count( $this->object->parse( $cond )->getTree()->toList() ) );
	}


	public function testRoot()
	{
		$manager = \Aimeos\MShop::create( $this->context, 'catalog' );

		$root = $manager->findItem( 'categories' );
		$item = $manager->findItem( 'cafe' );

		$this->assertEquals( 2, count( $this->object->root( $root->getId() )->getPath( $item->getId() ) ) );
	}


	public function testSearch()
	{
		$total = 0;
		$items = $this->object->uses( ['product'] )->compare( '==', 'catalog.code', 'cafe' )->search( $total );

		$this->assertCount( 1, $items );
		$this->assertEquals( 2, count( current( $items )->getRefItems( 'product' ) ) );
	}


	public function testUses()
	{
		$this->assertSame( $this->object, $this->object->uses( ['text'] ) );
	}


	public function testVisible()
	{
		$manager = \Aimeos\MShop::create( $this->context, 'catalog' );

		$root = $manager->findItem( 'root' );
		$item = $manager->findItem( 'cafe' );
		$catIds = array_keys( $manager->getPath( $item->getId() ) );

		$result = $this->object->root( $root->getId() )->visible( $catIds )->getTree();

		$this->assertEquals( 6, count( $result->toList() ) );
	}
}
