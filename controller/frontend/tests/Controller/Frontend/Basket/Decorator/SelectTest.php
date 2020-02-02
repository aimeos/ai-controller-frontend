<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2017-2020
 */


namespace Aimeos\Controller\Frontend\Basket\Decorator;


class SelectTest extends \PHPUnit\Framework\TestCase
{
	private $object;
	private $context;
	private $testItem;


	protected function setUp() : void
	{
		$this->context = \TestHelperFrontend::getContext();

		$manager = \Aimeos\MShop::create( $this->context, 'product' );
		$this->testItem = $manager->findItem( 'U:TESTP', ['attribute', 'media', 'price', 'product', 'text'] );

		$object = new \Aimeos\Controller\Frontend\Basket\Standard( $this->context );
		$this->object = new \Aimeos\Controller\Frontend\Basket\Decorator\Select( $object, $this->context );
	}


	protected function tearDown() : void
	{
		$this->object->clear();
		$this->context->getSession()->set( 'aimeos', [] );

		unset( $this->object, $this->testItem, $this->context );
	}


	public function testAddDeleteProduct()
	{
		$basket = $this->object->get();

		$this->assertSame( $this->object, $this->object->addProduct( $this->testItem, 2 ) );
		$this->assertEquals( 1, count( $basket->getProducts() ) );
		$this->assertEquals( 2, $basket->getProduct( 0 )->getQuantity() );
		$this->assertEquals( 'U:TESTPSUB01', $basket->getProduct( 0 )->getProductCode() );
	}


	public function testAddProductNoSelection()
	{
		$manager = \Aimeos\MShop::create( $this->context, 'product' );
		$item = $manager->findItem( 'CNC', ['attribute', 'media', 'price', 'product', 'text'] );

		$this->assertSame( $this->object, $this->object->addProduct( $item ) );
		$this->assertEquals( 1, count( $this->object->get()->getProducts() ) );
		$this->assertEquals( 'CNC', $this->object->get()->getProduct( 0 )->getProductCode() );
		$this->assertEquals( 0, count( $this->object->get()->getProduct( 0 )->getProducts() ) );
	}


	public function testAddProductVariant()
	{
		$manager = \Aimeos\MShop::create( \TestHelperFrontend::getContext(), 'attribute' );

		$search = $manager->createSearch();
		$search->setConditions( $search->compare( '==', 'attribute.code', array( 'xs', 'white' ) ) );

		$attrIds = $manager->searchItems( $search )->keys();

		if( $attrIds->isEmpty() ) {
			throw new \RuntimeException( 'Attributes not found' );
		}


		$manager = \Aimeos\MShop::create( $this->context, 'product' );
		$item = $manager->findItem( 'CNC', ['attribute', 'media', 'price', 'product', 'text'] );

		$result = $this->object->addProduct( $item, 1, $attrIds->toArray(), [], [], 'default', 'unitsupplier' );

		$this->assertSame( $this->object, $result );
		$this->assertEquals( 1, count( $this->object->get()->getProducts() ) );
		$this->assertEquals( 'CNC', $this->object->get()->getProduct( 0 )->getProductCode() );
		$this->assertEquals( 'default', $this->object->get()->getProduct( 0 )->getStockType() );
		$this->assertEquals( 'unitsupplier', $this->object->get()->getProduct( 0 )->getSupplierCode() );
	}


	public function testAddProductVariantIncomplete()
	{
		$id = \Aimeos\MShop::create( $this->context, 'attribute' )->findItem( '30', [], 'product', 'length' )->getId();

		$manager = \Aimeos\MShop::create( $this->context, 'product' );
		$item = $manager->findItem( 'U:TEST', ['attribute', 'media', 'price', 'product', 'text'] );

		$this->assertSame( $this->object, $this->object->addProduct( $item, 1, [$id] ) );
		$this->assertEquals( 1, count( $this->object->get()->getProducts() ) );
		$this->assertEquals( 'U:TESTSUB02', $this->object->get()->getProduct( 0 )->getProductCode() );
		$this->assertEquals( 2, count( $this->object->get()->getProduct( 0 )->getAttributeItems() ) );
	}


	public function testAddProductVariantNonUnique()
	{
		$id = \Aimeos\MShop::create( $this->context, 'attribute' )->findItem( '30', [], 'product', 'width' )->getId();

		$manager = \Aimeos\MShop::create( $this->context, 'product' );
		$item = $manager->findItem( 'U:TEST', ['attribute', 'media', 'price', 'product', 'text'] );

		$this->expectException( '\\Aimeos\\Controller\\Frontend\\Basket\\Exception' );
		$this->object->addProduct( $item, 1, [$id] );
	}


	public function testAddProductVariantNotRequired()
	{
		$this->context->getConfig()->set( 'controller/frontend/basket/require-variant', false );
		$id = \Aimeos\MShop::create( $this->context, 'attribute' )->findItem( 'xs', [], 'product', 'size' )->getId();

		$this->object->addProduct( $this->testItem, 1, [$id] );

		$this->assertEquals( 1, count( $this->object->get()->getProducts() ) );
		$this->assertEquals( 'U:TESTP', $this->object->get()->getProduct( 0 )->getProductCode() );
	}


	public function testAddProductEmptySelectionException()
	{
		$manager = \Aimeos\MShop::create( $this->context, 'product' );
		$item = $manager->findItem( 'U:noSel', ['attribute', 'media', 'price', 'product', 'text'] );

		$this->expectException( '\\Aimeos\\Controller\\Frontend\\Basket\\Exception' );
		$this->object->addProduct( $item );
	}


	public function testAddProductSelectionWithPricelessItem()
	{
		$this->assertSame( $this->object, $this->object->addProduct( $this->testItem ) );
		$this->assertEquals( 'U:TESTPSUB01', $this->object->get()->getProduct( 0 )->getProductCode() );
	}


	public function testAddProductConfigAttribute()
	{
		$id = \Aimeos\MShop::create( $this->context, 'attribute' )->findItem( 'xs', [], 'product', 'size' )->getId();

		$result = $this->object->addProduct( $this->testItem, 1, [], [$id => 1] );
		$basket = $this->object->get();

		$this->assertSame( $this->object, $result );
		$this->assertEquals( 1, count( $basket->getProducts() ) );
		$this->assertEquals( 'U:TESTPSUB01', $basket->getProduct( 0 )->getProductCode() );
		$this->assertEquals( 'xs', $basket->getProduct( 0 )->getAttribute( 'size', 'config' ) );
	}


	public function testAddProductHiddenAttribute()
	{
		$result = $this->object->addProduct( $this->testItem );

		$basket = $this->object->get();
		$this->assertEquals( 1, count( $basket->getProducts() ) );

		$product = $basket->getProduct( 0 );
		$this->assertEquals( 'U:TESTPSUB01', $product->getProductCode() );

		$attributes = $product->getAttributeItems();
		$this->assertEquals( 1, count( $attributes ) );

		$this->assertSame( $this->object, $result );
		$this->assertEquals( 'hidden', $attributes->first()->getType() );
		$this->assertEquals( '29', $product->getAttribute( 'width', 'hidden' ) );
	}
}
