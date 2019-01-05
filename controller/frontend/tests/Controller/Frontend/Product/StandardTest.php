<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2017-2018
 */


namespace Aimeos\Controller\Frontend\Product;


class StandardTest extends \PHPUnit\Framework\TestCase
{
	private $object;


	protected function setUp()
	{
		$this->context = \TestHelperFrontend::getContext();
		$this->object = new \Aimeos\Controller\Frontend\Product\Standard( $this->context );
	}


	protected function tearDown()
	{
		unset( $this->object );
	}


	public function testAggregate()
	{
		$list = $this->object->aggregate( 'index.attribute.id' );

		$this->assertGreaterThan( 0, count( $list ) );
	}


	public function testAllOf()
	{
		$manager = \Aimeos\MShop::create( $this->context, 'attribute' );

		$length = $manager->findItem( '30', [], 'product', 'length' )->getId();
		$width = $manager->findItem( '29', [], 'product', 'width' )->getId();

		$this->assertEquals( 2, count( $this->object->allOf( [$length, $width] )->search() ) );
	}


	public function testCategory()
	{
		$manager = \Aimeos\MShop::create( $this->context, 'catalog' );
		$catId = $manager->findItem( 'cafe' )->getId();

		$this->assertEquals( 2, count( $this->object->category( $catId, 'promotion' )->search() ) );
	}


	public function testCategoryTree()
	{
		$manager = \Aimeos\MShop::create( $this->context, 'catalog' );

		$catId = $manager->findItem( 'categories' )->getId();
		$grpId = $manager->findItem( 'group' )->getId();

		$this->object->category( [$catId, $grpId], 'promotion', \Aimeos\MW\Tree\Manager\Base::LEVEL_TREE );
		$this->assertEquals( 3, count( $this->object->search() ) );
	}


	public function testCompare()
	{
		$this->assertEquals( 1, count( $this->object->compare( '==', 'product.type', 'bundle' )->search() ) );
	}


	public function testGet()
	{
		$item = \Aimeos\MShop::create( $this->context, 'product' )->findItem( 'CNC' );

		$this->assertInstanceOf( \Aimeos\MShop\Product\Item\Iface::class, $this->object->get( $item->getId() ) );
	}


	public function testFind()
	{
		$this->assertInstanceOf( \Aimeos\MShop\Product\Item\Iface::class, $this->object->find( 'CNC' ) );
	}


	public function testOneOf()
	{
		$manager = \Aimeos\MShop::create( $this->context, 'attribute' );

		$length = $manager->findItem( '30', [], 'product', 'length' )->getId();
		$width = $manager->findItem( '29', [], 'product', 'width' )->getId();

		$this->assertEquals( 4, count( $this->object->oneOf( [$length, $width] )->search() ) );
	}


	public function testParse()
	{
		$cond = ['&&' => [['>' => ['product.status' => 0]], ['==' => ['product.type' => 'default']]]];
		$this->assertEquals( 4, count( $this->object->parse( $cond )->search() ) );
	}


	public function testProduct()
	{
		$manager = \Aimeos\MShop::create( $this->context, 'product' );

		$cncId = $manager->findItem( 'CNC' )->getId();
		$cneId = $manager->findItem( 'CNE' )->getId();

		$this->assertEquals( 2, count( $this->object->product( [$cncId, $cneId] )->search() ) );
	}


	public function testSearch()
	{
		$this->assertEquals( 8, count( $this->object->search() ) );
	}


	public function testSlice()
	{
		$this->assertEquals( 2, count( $this->object->slice( 0, 2 )->search() ) );
	}


	public function testSort()
	{
		$this->assertEquals( 8, count( $this->object->sort( 'relevance' )->search() ) );
	}


	public function testSortCode()
	{
		$result = $this->object->sort( 'code' )->search( [] );
		$this->assertEquals( 'CNC', reset( $result )->getCode() );
	}


	public function testSortCodeDesc()
	{
		$result = $this->object->sort( '-code' )->search( [] );
		$this->assertStringStartsWith( 'U:', reset( $result )->getCode() );
	}


	public function testSortCtime()
	{
		$this->assertEquals( 8, count( $this->object->sort( 'ctime' )->search( [] ) ) );
	}


	public function testSortCtimeDesc()
	{
		$this->assertEquals( 8, count( $this->object->sort( '-ctime' )->search( [] ) ) );
	}


	public function testSortName()
	{
		$result = $this->object->sort( 'name' )->search( ['text'] );
		$this->assertEquals( 'Cafe Noire Cappuccino', reset( $result )->getName() );
	}


	public function testSortNameDesc()
	{
		$result = $this->object->sort( '-name' )->search( ['text'] );
		$this->assertEquals( 'Unterproduct 3', reset( $result )->getName() );
	}


	public function testSortPrice()
	{
		$result = $this->object->sort( 'price' )->search( [] );
		$this->assertEquals( 'IJKL', reset( $result )->getCode() );
	}


	public function testSortPriceDesc()
	{
		$result = $this->object->sort( '-price' )->search( [] );
		$this->assertTrue( in_array( reset( $result )->getCode(), ['CNC', 'U:BUNDLE'] ) );
	}


	public function testSortRelevanceCategory()
	{
		$manager = \Aimeos\MShop::create( $this->context, 'catalog' );
		$catId = $manager->findItem( 'new' )->getId();

		$result = $this->object->category( $catId )->sort( 'relevance' )->search( [] );

		$this->assertEquals( 3, count( $result ) );
		$this->assertEquals( 'CNE', reset( $result )->getCode() );
		$this->assertEquals( 'U:BUNDLE', end( $result )->getCode() );
	}


	public function testSortRelevanceSupplier()
	{
		$manager = \Aimeos\MShop::create( $this->context, 'supplier' );
		$supId = $manager->findItem( 'unitCode001' )->getId();

		$result = $this->object->supplier( $supId )->sort( 'relevance' )->search( [] );

		$this->assertEquals( 2, count( $result ) );
		$this->assertEquals( 'CNC', reset( $result )->getCode() );
		$this->assertEquals( 'CNE', end( $result )->getCode() );
	}


	public function testSupplier()
	{
		$manager = \Aimeos\MShop::create( $this->context, 'supplier' );
		$supId = $manager->findItem( 'unitCode001' )->getId();

		$this->assertEquals( 2, count( $this->object->supplier( $supId )->search( [] ) ) );
	}


	public function testText()
	{
		$this->assertEquals( 3, count( $this->object->text( 'Cafe' )->search( [] ) ) );
	}
}
