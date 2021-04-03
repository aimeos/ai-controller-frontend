<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2021
 */


namespace Aimeos\Controller\Frontend\Site;


class StandardTest extends \PHPUnit\Framework\TestCase
{
	private $context;
	private $object;


	protected function setUp() : void
	{
		$this->context = \TestHelperFrontend::getContext();
		$this->object = new \Aimeos\Controller\Frontend\Site\Standard( $this->context );
	}


	protected function tearDown() : void
	{
		unset( $this->object, $this->context );
	}


	public function testCompare()
	{
		$list = $this->object->compare( '==', 'locale.site.status', 1 )->search();
		$this->assertGreaterThanOrEqual( 1, count( $list ) );
	}


	public function testFind()
	{
		$item = $this->object->find( 'unittest' );

		$this->assertInstanceOf( \Aimeos\MShop\Locale\Item\Site\Iface::class, $item );
	}


	public function testGet()
	{
		$item = \Aimeos\MShop::create( $this->context, 'locale/site' )->find( 'unittest' );
		$item = $this->object->get( $item->getId() );

		$this->assertInstanceOf( \Aimeos\MShop\Locale\Item\Site\Iface::class, $item );
	}


	public function testGetPath()
	{
		$item = \Aimeos\MShop::create( $this->context, 'locale/site' )->find( 'unittest', [] );
		$items = $this->object->getPath( $item->getId() );

		$this->assertGreaterThanOrEqual( 1, count( $items ) );

		foreach( $items as $item ) {
			$this->assertInstanceOf( \Aimeos\MShop\Locale\Item\Site\Iface::class, $item );
		}
	}


	public function testGetTree()
	{
		$item = \Aimeos\MShop::create( $this->context, 'locale/site' )->find( 'unittest' );
		$tree = $this->object->root( $item->getId() )->getTree();

		foreach( $tree->toList() as $item ) {
			$this->assertInstanceOf( \Aimeos\MShop\Locale\Item\Site\Iface::class, $item );
		}

		$this->assertGreaterThanOrEqual( 1, count( $tree->toList() ) );
	}


	public function testParse()
	{
		$cond = ['>' => ['locale.site.status' => 0]];
		$this->assertGreaterThanOrEqual( 1, count( $this->object->parse( $cond )->search() ) );
	}


	public function testRoot()
	{
		$item = \Aimeos\MShop::create( $this->context, 'locale/site' )->find( 'unittest' );

		$this->assertGreaterThanOrEqual( 1, count( $this->object->root( $item->getId() )->getPath( $item->getId() ) ) );
	}


	public function testSearch()
	{
		$total = 0;
		$items = $this->object->compare( '==', 'locale.site.code', 'unittest' )->search( $total );

		$this->assertCount( 1, $items );
		$this->assertEquals( 1, $total );
	}


	public function testSlice()
	{
		$this->assertEquals( 1, count( $this->object->slice( 0, 1 )->search() ) );
	}


	public function testSort()
	{
		$this->assertGreaterThanOrEqual( 1, count( $this->object->sort( 'locale.site.label' )->search() ) );
	}
}
