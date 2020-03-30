<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2017-2020
 */


namespace Aimeos\Controller\Frontend\Product;


class StandardTest extends \PHPUnit\Framework\TestCase
{
	private $object;


	protected function setUp() : void
	{
		$this->context = \TestHelperFrontend::getContext();
		$this->object = new \Aimeos\Controller\Frontend\Product\Standard( $this->context );
	}


	protected function tearDown() : void
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

		$this->assertEquals( 1, count( $this->object->allOf( [$length, $width] )->search() ) );
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


	public function testFind()
	{
		$item = $this->object->uses( ['product'] )->find( 'U:BUNDLE' );

		$this->assertInstanceOf( \Aimeos\MShop\Product\Item\Iface::class, $item );
		$this->assertEquals( 2, count( $item->getRefItems( 'product' ) ) );
	}


	public function testFunction()
	{
		$str = $this->object->function( 'product:has', ['domain', 'type', 'refid'] );
		$this->assertEquals( 'product:has("domain","type","refid")', $str );
	}


	public function testGet()
	{
		$item = \Aimeos\MShop::create( $this->context, 'product' )->findItem( 'U:BUNDLE' );
		$item = $this->object->uses( ['product'] )->get( $item->getId() );

		$this->assertInstanceOf( \Aimeos\MShop\Product\Item\Iface::class, $item );
		$this->assertEquals( 2, count( $item->getRefItems( 'product' ) ) );
	}


	public function testHas()
	{
		$manager = \Aimeos\MShop::create( $this->context, 'attribute' );
		$attrId = $manager->findItem( '30', [], 'product', 'length' )->getId();

		$this->assertEquals( 1, count( $this->object->has( 'attribute', 'variant', $attrId )->search() ) );
	}


	public function testOneOf()
	{
		$manager = \Aimeos\MShop::create( $this->context, 'attribute' );

		$length = $manager->findItem( '30', [], 'product', 'length' )->getId();
		$width = $manager->findItem( '30', [], 'product', 'width' )->getId();

		$this->assertEquals( 2, count( $this->object->oneOf( [$length, $width] )->search() ) );
	}


	public function testOneOfList()
	{
		$manager = \Aimeos\MShop::create( $this->context, 'attribute' );

		$length = $manager->findItem( '30', [], 'product', 'length' )->getId();
		$width = $manager->findItem( '30', [], 'product', 'width' )->getId();

		$this->assertEquals( 1, count( $this->object->oneOf( [[$length], [$width]] )->search() ) );
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


	public function testProperty()
	{
		$this->assertEquals( 1, count( $this->object->property( 'package-weight', '1.25' )->search() ) );
	}


	public function testResolve()
	{
		$item = $this->object->resolve( 'Cafe-Noire-Cappuccino' );

		$this->assertInstanceOf( \Aimeos\MShop\Product\Item\Iface::class, $item );
		$this->assertEquals( 'Cafe Noire Cappuccino', $item->getLabel() );
	}


	public function testSearch()
	{
		$total = 0;
		$items = $this->object->uses( ['price'] )->sort( 'code' )->search( $total );

		$this->assertEquals( 8, count( $items ) );
		$this->assertEquals( 8, $total );
		$this->assertEquals( 2, count( $items->first()->getRefItems( 'price' ) ) );
	}


	public function testSlice()
	{
		$this->assertEquals( 2, count( $this->object->slice( 0, 2 )->search() ) );
	}


	public function testSort()
	{
		$this->assertEquals( 8, count( $this->object->sort( '+relevance' )->search() ) );
	}


	public function testSortGeneric()
	{
		$this->assertEquals( 8, count( $this->object->sort( '-product.status' )->search() ) );
	}


	public function testSortMultiple()
	{
		$this->assertEquals( 8, count( $this->object->sort( '-product.status,product.id' )->search() ) );
	}


	public function testSortCode()
	{
		$result = $this->object->sort( 'code' )->search();
		$this->assertEquals( 'CNC', $result->first()->getCode() );
	}


	public function testSortCodeDesc()
	{
		$result = $this->object->sort( '-code' )->search();
		$this->assertStringStartsWith( 'U:', $result->first()->getCode() );
	}


	public function testSortCtime()
	{
		$this->assertEquals( 8, count( $this->object->sort( 'ctime' )->search() ) );
	}


	public function testSortCtimeDesc()
	{
		$this->assertEquals( 8, count( $this->object->sort( '-ctime' )->search() ) );
	}


	public function testSortName()
	{
		$result = $this->object->uses( ['text'] )->sort( 'name' )->search();
		$this->assertEquals( 'Cafe Noire Cappuccino', $result->first()->getName() );
	}


	public function testSortNameDesc()
	{
		$result = $this->object->uses( ['text'] )->sort( '-name' )->search();
		$this->assertEquals( 'Unterproduct 3', $result->first()->getName() );
	}


	public function testSortPrice()
	{
		$result = $this->object->uses( ['price'] )->sort( 'price' )->search();
		$prices = $result->first()->getRefItems( 'price', 'default', 'default' );

		$this->assertEquals( '12.00', $prices->first()->getValue() );
	}


	public function testSortPriceDesc()
	{
		$result = $this->object->uses( ['price'] )->sort( '-price' )->search();
		$prices = $result->first()->getRefItems( 'price', 'default', 'default' );

		$this->assertEquals( '600.00', $prices->first()->getValue() );
	}


	public function testSortRelevanceCategory()
	{
		$manager = \Aimeos\MShop::create( $this->context, 'catalog' );
		$catId = $manager->findItem( 'new' )->getId();

		$result = $this->object->category( $catId )->sort( 'relevance' )->search();

		$this->assertEquals( 3, count( $result ) );
		$this->assertEquals( 'CNE', $result->first()->getCode() );
		$this->assertEquals( 'U:BUNDLE', $result->last()->getCode() );
	}


	public function testSortRelevanceSupplier()
	{
		$manager = \Aimeos\MShop::create( $this->context, 'supplier' );
		$supId = $manager->findItem( 'unitCode001' )->getId();

		$result = $this->object->supplier( $supId )->sort( 'relevance' )->search();

		$this->assertEquals( 2, count( $result ) );
		$this->assertEquals( 'CNC', $result->first()->getCode() );
		$this->assertEquals( 'CNE', $result->last()->getCode() );
	}


	public function testSupplier()
	{
		$manager = \Aimeos\MShop::create( $this->context, 'supplier' );
		$supId = $manager->findItem( 'unitCode001' )->getId();

		$this->assertEquals( 2, count( $this->object->supplier( $supId )->search() ) );
	}


	public function testText()
	{
		$this->assertEquals( 2, count( $this->object->text( 'Cafe' )->search() ) );
	}


	public function testUses()
	{
		$this->assertSame( $this->object, $this->object->uses( ['text'] ) );
	}
}
