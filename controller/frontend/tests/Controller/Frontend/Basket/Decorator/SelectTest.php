<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2017-2018
 */


namespace Aimeos\Controller\Frontend\Basket\Decorator;


class SelectTest extends \PHPUnit\Framework\TestCase
{
	private $object;
	private $context;
	private $testItem;


	protected function setUp()
	{
		$this->context = \TestHelperFrontend::getContext();
		$this->testItem = \Aimeos\MShop::create( $this->context, 'product' )->findItem( 'U:TESTP' );

		$object = new \Aimeos\Controller\Frontend\Basket\Standard( $this->context );
		$this->object = new \Aimeos\Controller\Frontend\Basket\Decorator\Select( $object, $this->context );
	}


	protected function tearDown()
	{
		$this->object->clear();
		$this->context->getSession()->set( 'aimeos', [] );

		unset( $this->object );
	}


	public function testAddDeleteProduct()
	{
		$basket = $this->object->get();

		$this->assertSame( $this->object, $this->object->addProduct( $this->testItem->getId(), 2 ) );
		$this->assertEquals( 1, count( $basket->getProducts() ) );
		$this->assertEquals( 2, $basket->getProduct( 0 )->getQuantity() );
		$this->assertEquals( 'U:TESTPSUB01', $basket->getProduct( 0 )->getProductCode() );
	}


	public function testAddProductNoSelection()
	{
		$item = \Aimeos\MShop::create( $this->context, 'product' )->findItem( 'CNC' );

		$this->assertSame( $this->object, $this->object->addProduct( $item->getId(), 1 ) );
		$this->assertEquals( 1, count( $this->object->get()->getProducts() ) );
		$this->assertEquals( 'CNC', $this->object->get()->getProduct( 0 )->getProductCode() );
		$this->assertEquals( 0, count( $this->object->get()->getProduct( 0 )->getProducts() ) );
	}


	public function testAddProductVariant()
	{
		$manager = \Aimeos\MShop::create( \TestHelperFrontend::getContext(), 'attribute' );

		$search = $manager->createSearch();
		$search->setConditions( $search->compare( '==', 'attribute.code', array( 'xs', 'white' ) ) );

		$attrs = $manager->searchItems( $search );

		if( count( $attrs ) === 0 ) {
			throw new \RuntimeException( 'Attributes not found' );
		}


		$item = \Aimeos\MShop::create( $this->context, 'product' )->findItem( 'CNC' );
		$result = $this->object->addProduct( $item->getId(), 1, 'default', array_keys( $attrs ), [], [], [] );

		$this->assertSame( $this->object, $result );
		$this->assertEquals( 1, count( $this->object->get()->getProducts() ) );
		$this->assertEquals( 'CNC', $this->object->get()->getProduct( 0 )->getProductCode() );
	}


	public function testAddProductVariantIncomplete()
	{
		$id = \Aimeos\MShop::create( $this->context, 'attribute' )->findItem( '30', [], 'product', 'length' )->getId();
		$item = \Aimeos\MShop::create( $this->context, 'product' )->findItem( 'U:TEST' );

		$this->assertSame( $this->object, $this->object->addProduct( $item->getId(), 1, 'default', [$id] ) );
		$this->assertEquals( 1, count( $this->object->get()->getProducts() ) );
		$this->assertEquals( 'U:TESTSUB02', $this->object->get()->getProduct( 0 )->getProductCode() );
		$this->assertEquals( 2, count( $this->object->get()->getProduct( 0 )->getAttributeItems() ) );
	}


	public function testAddProductVariantNonUnique()
	{
		$id = \Aimeos\MShop::create( $this->context, 'attribute' )->findItem( '30', [], 'product', 'width' )->getId();
		$item = \Aimeos\MShop::create( $this->context, 'product' )->findItem( 'U:TEST' );

		$this->setExpectedException( '\\Aimeos\\Controller\\Frontend\\Basket\\Exception' );
		$this->object->addProduct( $item->getId(), 1, 'default', [$id] );
	}


	public function testAddProductVariantNotRequired()
	{
		$this->context->getConfig()->set( 'controller/frontend/basket/require-variant', false );
		$id = \Aimeos\MShop::create( $this->context, 'attribute' )->findItem( 'xs', [], 'product', 'size' )->getId();

		$this->object->addProduct( $this->testItem->getId(), 1, 'default', [$id] );

		$this->assertEquals( 1, count( $this->object->get()->getProducts() ) );
		$this->assertEquals( 'U:TESTP', $this->object->get()->getProduct( 0 )->getProductCode() );
	}


	public function testAddProductEmptySelectionException()
	{
		$item = \Aimeos\MShop::create( $this->context, 'product' )->findItem( 'U:noSel' );

		$this->setExpectedException( '\\Aimeos\\Controller\\Frontend\\Basket\\Exception' );
		$this->object->addProduct( $item->getId(), 1 );
	}


	public function testAddProductSelectionWithPricelessItem()
	{
		$this->assertSame( $this->object, $this->object->addProduct( $this->testItem->getId(), 1 ) );
		$this->assertEquals( 'U:TESTPSUB01', $this->object->get()->getProduct( 0 )->getProductCode() );
	}


	public function testAddProductConfigAttribute()
	{
		$id = \Aimeos\MShop::create( $this->context, 'attribute' )->findItem( 'xs', [], 'product', 'size' )->getId();

		$result = $this->object->addProduct( $this->testItem->getId(), 1, 'default', [], [$id => 1] );
		$basket = $this->object->get();

		$this->assertSame( $this->object, $result );
		$this->assertEquals( 1, count( $basket->getProducts() ) );
		$this->assertEquals( 'U:TESTPSUB01', $basket->getProduct( 0 )->getProductCode() );
		$this->assertEquals( 'xs', $basket->getProduct( 0 )->getAttribute( 'size', 'config' ) );
	}


	public function testAddProductHiddenAttribute()
	{
		$id = \Aimeos\MShop::create( $this->context, 'attribute' )->findItem( '29', [], 'product', 'width' )->getId();

		$result = $this->object->addProduct( $this->testItem->getId(), 1, 'default', [], [], [$id] );

		$basket = $this->object->get();
		$this->assertEquals( 1, count( $basket->getProducts() ) );

		$product = $basket->getProduct( 0 );
		$this->assertEquals( 'U:TESTPSUB01', $product->getProductCode() );

		$attributes = $product->getAttributeItems();
		$this->assertEquals( 1, count( $attributes ) );

		$this->assertSame( $this->object, $result );
		$this->assertEquals( 'hidden', current( $attributes )->getType() );
		$this->assertEquals( '29', $product->getAttribute( 'width', 'hidden' ) );
	}
}
