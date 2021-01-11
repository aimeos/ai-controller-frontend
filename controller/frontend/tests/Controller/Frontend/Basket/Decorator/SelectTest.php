<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2017-2021
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
		$this->testItem = $manager->find( 'U:TESTP', ['attribute', 'media', 'price', 'product', 'text'] );

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
		$item = $manager->find( 'CNC', ['attribute', 'media', 'price', 'product', 'text'] );

		$this->assertSame( $this->object, $this->object->addProduct( $item ) );
		$this->assertEquals( 1, count( $this->object->get()->getProducts() ) );
		$this->assertEquals( 'CNC', $this->object->get()->getProduct( 0 )->getProductCode() );
		$this->assertEquals( 0, count( $this->object->get()->getProduct( 0 )->getProducts() ) );
	}


	public function testAddProductVariant()
	{
		$manager = \Aimeos\MShop::create( \TestHelperFrontend::getContext(), 'attribute' );

		$search = $manager->filter();
		$search->setConditions( $search->compare( '==', 'attribute.code', array( 'xs', 'white' ) ) );

		$attrIds = $manager->search( $search )->keys();

		if( $attrIds->isEmpty() ) {
			throw new \RuntimeException( 'Attributes not found' );
		}


		$manager = \Aimeos\MShop::create( $this->context, 'product' );
		$item = $manager->find( 'CNC', ['attribute', 'media', 'price', 'product', 'text'] );
		$supId = \Aimeos\MShop::create( $this->context, 'supplier' )->find( 'unitSupplier001' )->getId();

		$result = $this->object->addProduct( $item, 1, $attrIds->toArray(), [], [], 'default', $supId );

		$this->assertSame( $this->object, $result );
		$this->assertEquals( 1, count( $this->object->get()->getProducts() ) );
		$this->assertEquals( 'CNC', $this->object->get()->getProduct( 0 )->getProductCode() );
		$this->assertEquals( 'default', $this->object->get()->getProduct( 0 )->getStockType() );
		$this->assertEquals( $supId, $this->object->get()->getProduct( 0 )->getSupplierId() );
	}


	public function testAddProductVariantIncomplete()
	{
		$id = \Aimeos\MShop::create( $this->context, 'attribute' )->find( '30', [], 'product', 'length' )->getId();

		$manager = \Aimeos\MShop::create( $this->context, 'product' );
		$item = $manager->find( 'U:TEST', ['attribute', 'media', 'price', 'product', 'text'] );

		$this->assertSame( $this->object, $this->object->addProduct( $item, 1, [$id] ) );
		$this->assertEquals( 1, count( $this->object->get()->getProducts() ) );
		$this->assertEquals( 'U:TESTSUB02', $this->object->get()->getProduct( 0 )->getProductCode() );
		$this->assertEquals( 2, count( $this->object->get()->getProduct( 0 )->getAttributeItems() ) );
	}


	public function testAddProductVariantNonUnique()
	{
		$id = \Aimeos\MShop::create( $this->context, 'attribute' )->find( '30', [], 'product', 'width' )->getId();

		$manager = \Aimeos\MShop::create( $this->context, 'product' );
		$item = $manager->find( 'U:TEST', ['attribute', 'media', 'price', 'product', 'text'] );

		$this->expectException( '\\Aimeos\\Controller\\Frontend\\Basket\\Exception' );
		$this->object->addProduct( $item, 1, [$id] );
	}


	public function testAddProductVariantNotRequired()
	{
		$this->context->getConfig()->set( 'controller/frontend/basket/require-variant', false );
		$id = \Aimeos\MShop::create( $this->context, 'attribute' )->find( 'xs', [], 'product', 'size' )->getId();

		$this->object->addProduct( $this->testItem, 1, [$id] );

		$this->assertEquals( 1, count( $this->object->get()->getProducts() ) );
		$this->assertEquals( 'U:TESTP', $this->object->get()->getProduct( 0 )->getProductCode() );
	}


	public function testAddProductEmptySelectionException()
	{
		$manager = \Aimeos\MShop::create( $this->context, 'product' );
		$item = $manager->find( 'U:noSel', ['attribute', 'media', 'price', 'product', 'text'] );

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
		$id = \Aimeos\MShop::create( $this->context, 'attribute' )->find( 'xs', [], 'product', 'size' )->getId();

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


	public function testUpdateProduct()
	{
		$manager = \Aimeos\MShop::create( $this->context, 'product' );
		$item = $manager->find( 'CNC', ['attribute', 'media', 'price', 'product', 'text'] );
		$this->object->addProduct( $item );

		$this->assertSame( $this->object, $this->object->updateProduct( 0, 2 ) );
		$this->assertEquals( 1, count( $this->object->get()->getProducts() ) );
		$this->assertEquals( 2, $this->object->get()->getProduct( 0 )->getQuantity() );
		$this->assertEquals( 'CNC', $this->object->get()->getProduct( 0 )->getProductCode() );
		$this->assertEquals( '600.00', $this->object->get()->getProduct( 0 )->getPrice()->getValue() );
	}


	public function testUpdateProductSelect()
	{
		$this->object->addProduct( $this->testItem, 1 );

		$this->assertSame( $this->object, $this->object->updateProduct( 0, 2 ) );
		$this->assertEquals( 1, count( $this->object->get()->getProducts() ) );
		$this->assertEquals( 2, $this->object->get()->getProduct( 0 )->getQuantity() );
		$this->assertEquals( 'U:TESTPSUB01', $this->object->get()->getProduct( 0 )->getProductCode() );
		$this->assertEquals( '18.00', $this->object->get()->getProduct( 0 )->getPrice()->getValue() );
	}
}
