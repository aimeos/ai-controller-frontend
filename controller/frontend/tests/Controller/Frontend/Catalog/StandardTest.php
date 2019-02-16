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
		$list = $this->object->compare( '==', 'catalog.code', 'categories' )->getTree( [] )->toList();
		$this->assertEquals( 1, count( $list ) );
	}


	public function testFind()
	{
		$iface = \Aimeos\MShop\Catalog\Item\Iface::class;
		$this->assertInstanceOf( $iface, $this->object->find( 'cafe', [] ) );
	}


	public function testGet()
	{
		$iface = \Aimeos\MShop\Catalog\Item\Iface::class;
		$item = \Aimeos\MShop::create( $this->context, 'catalog' )->findItem( 'cafe', [] );

		$this->assertInstanceOf( $iface, $this->object->get( $item->getId() ) );
	}


	public function testGetPath()
	{
		$manager = \Aimeos\MShop::create( $this->context, 'catalog' );
		$items = $this->object->getPath( $manager->findItem( 'cafe', [] )->getId() );

		$this->assertEquals( 3, count( $items ) );

		foreach( $items as $item ) {
			$this->assertInstanceOf( \Aimeos\MShop\Catalog\Item\Iface::class, $item );
		}
	}


	public function testGetTree()
	{
		$tree = $this->object->getTree();

		foreach( $tree->toList() as $item ) {
			$this->assertInstanceOf( \Aimeos\MShop\Catalog\Item\Iface::class, $item );
		}

		$this->assertEquals( 2, count( $tree->getChildren() ) );
	}


	public function testParse()
	{
		$cond = ['>' => ['catalog.status' => 0]];
		$this->assertEquals( 8, count( $this->object->parse( $cond )->getTree( [] )->toList() ) );
	}


	public function testRoot()
	{
		$manager = \Aimeos\MShop::create( $this->context, 'catalog' );

		$root = $manager->findItem( 'categories' );
		$item = $manager->findItem( 'cafe' );

		$this->assertEquals( 2, count( $this->object->root( $root->getId() )->getPath( $item->getId(), [] ) ) );
	}


	public function testVisible()
	{
		$manager = \Aimeos\MShop::create( $this->context, 'catalog' );

		$root = $manager->findItem( 'root' );
		$item = $manager->findItem( 'cafe' );
		$catIds = array_keys( $manager->getPath( $item->getId() ) );

		$result = $this->object->root( $root->getId() )->visible( $catIds )->getTree( [] );

		$this->assertEquals( 6, count( $result->toList() ) );
	}
}
