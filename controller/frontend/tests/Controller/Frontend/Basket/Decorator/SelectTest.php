<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2017
 */


namespace Aimeos\Controller\Frontend\Basket\Decorator;


class SelectTest extends \PHPUnit_Framework_TestCase
{
	private $object;
	private $context;
	private $testItem;


	protected function setUp()
	{
		$this->context = \TestHelperFrontend::getContext();
		$this->testItem = \Aimeos\MShop\Factory::createManager( $this->context, 'product' )->findItem( 'U:TESTP' );

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
		$this->object->addProduct( $this->testItem->getId(), 2 );

		$this->assertEquals( 1, count( $basket->getProducts() ) );
		$this->assertEquals( 2, $basket->getProduct( 0 )->getQuantity() );
		$this->assertEquals( 'U:TESTPSUB01', $basket->getProduct( 0 )->getProductCode() );
	}


	public function testAddProductNoSelection()
	{
		$item = \Aimeos\MShop\Factory::createManager( $this->context, 'product' )->findItem( 'CNC' );

		$this->object->addProduct( $item->getId(), 1 );

		$this->assertEquals( 1, count( $this->object->get()->getProducts() ) );
		$this->assertEquals( 'CNC', $this->object->get()->getProduct( 0 )->getProductCode() );
		$this->assertEquals( 0, count( $this->object->get()->getProduct( 0 )->getProducts() ) );
	}


	public function testAddProductVariant()
	{
		$attributeManager = \Aimeos\MShop\Attribute\Manager\Factory::createManager( \TestHelperFrontend::getContext() );

		$search = $attributeManager->createSearch();
		$search->setConditions( $search->compare( '==', 'attribute.code', array( 'xs', 'white' ) ) );

		$attributes = $attributeManager->searchItems( $search );

		if( count( $attributes ) === 0 ) {
			throw new \RuntimeException( 'Attributes not found' );
		}


		$item = \Aimeos\MShop\Factory::createManager( $this->context, 'product' )->findItem( 'CNC' );

		$this->object->addProduct( $item->getId(), 1, [], array_keys( $attributes ), [], [], [], 'default' );

		$this->assertEquals( 1, count( $this->object->get()->getProducts() ) );
		$this->assertEquals( 'CNC', $this->object->get()->getProduct( 0 )->getProductCode() );
	}


	public function testAddProductVariantIncomplete()
	{
		$attributeManager = \Aimeos\MShop\Factory::createManager( $this->context, 'attribute' );

		$search = $attributeManager->createSearch();
		$expr = array(
			$search->compare( '==', 'attribute.domain', 'product' ),
			$search->compare( '==', 'attribute.code', '30' ),
			$search->compare( '==', 'attribute.type.code', 'length' ),
		);
		$search->setConditions( $search->combine( '&&', $expr ) );

		$attributes = $attributeManager->searchItems( $search );

		if( count( $attributes ) === 0 ) {
			throw new \RuntimeException( 'Attributes not found' );
		}


		$item = \Aimeos\MShop\Factory::createManager( $this->context, 'product' )->findItem( 'U:TEST' );

		$this->object->addProduct( $item->getId(), 1, [], array_keys( $attributes ) );

		$this->assertEquals( 1, count( $this->object->get()->getProducts() ) );
		$this->assertEquals( 'U:TESTSUB02', $this->object->get()->getProduct( 0 )->getProductCode() );
		$this->assertEquals( 2, count( $this->object->get()->getProduct( 0 )->getAttributes() ) );
	}


	public function testAddProductVariantNonUnique()
	{
		$attributeManager = \Aimeos\MShop\Factory::createManager( $this->context, 'attribute' );

		$search = $attributeManager->createSearch();
		$expr = array(
			$search->compare( '==', 'attribute.domain', 'product' ),
			$search->compare( '==', 'attribute.code', '30' ),
			$search->compare( '==', 'attribute.type.code', 'width' ),
		);
		$search->setConditions( $search->combine( '&&', $expr ) );

		$attributes = $attributeManager->searchItems( $search );

		if( count( $attributes ) === 0 ) {
			throw new \RuntimeException( 'Attributes not found' );
		}


		$item = \Aimeos\MShop\Factory::createManager( $this->context, 'product' )->findItem( 'U:TEST' );

		$this->setExpectedException( '\\Aimeos\\Controller\\Frontend\\Basket\\Exception' );
		$this->object->addProduct( $item->getId(), 1, [], array_keys( $attributes ) );
	}


	public function testAddProductVariantNotRequired()
	{
		$attributeManager = \Aimeos\MShop\Factory::createManager( $this->context, 'attribute' );

		$search = $attributeManager->createSearch();
		$search->setConditions( $search->compare( '==', 'attribute.code', 'xs' ) );

		$attributes = $attributeManager->searchItems( $search );

		if( count( $attributes ) === 0 ) {
			throw new \RuntimeException( 'Attribute not found' );
		}

		$options = array( 'variant' => false );

		$this->object->addProduct( $this->testItem->getId(), 1, $options, array_keys( $attributes ) );

		$this->assertEquals( 1, count( $this->object->get()->getProducts() ) );
		$this->assertEquals( 'U:TESTP', $this->object->get()->getProduct( 0 )->getProductCode() );
	}


	public function testAddProductEmptySelectionException()
	{
		$item = \Aimeos\MShop\Factory::createManager( $this->context, 'product' )->findItem( 'U:noSel' );

		$this->setExpectedException( '\\Aimeos\\Controller\\Frontend\\Basket\\Exception' );
		$this->object->addProduct( $item->getId(), 1 );
	}


	public function testAddProductSelectionWithPricelessItem()
	{
		$this->object->addProduct( $this->testItem->getId(), 1 );

		$this->assertEquals( 'U:TESTPSUB01', $this->object->get()->getProduct( 0 )->getProductCode() );
	}


	public function testAddProductConfigAttribute()
	{
		$attributeManager = \Aimeos\MShop\Factory::createManager( $this->context, 'attribute' );

		$search = $attributeManager->createSearch();
		$search->setConditions( $search->compare( '==', 'attribute.code', 'xs' ) );

		$attributes = $attributeManager->searchItems( $search );

		if( empty( $attributes ) ) {
			throw new \RuntimeException( 'Attribute not found' );
		}

		$this->object->addProduct( $this->testItem->getId(), 1, [], [], array_keys( $attributes ) );
		$basket = $this->object->get();

		$this->assertEquals( 1, count( $basket->getProducts() ) );
		$this->assertEquals( 'U:TESTPSUB01', $basket->getProduct( 0 )->getProductCode() );
		$this->assertEquals( 'xs', $basket->getProduct( 0 )->getAttribute( 'size', 'config' ) );
	}


	public function testAddProductHiddenAttribute()
	{
		$attributeManager = \Aimeos\MShop\Factory::createManager( $this->context, 'attribute' );

		$search = $attributeManager->createSearch();
		$expr = array(
			$search->compare( '==', 'attribute.code', '29' ),
			$search->compare( '==', 'attribute.type.code', 'width' ),
		);
		$search->setConditions( $search->combine( '&&', $expr ) );

		$attributes = $attributeManager->searchItems( $search );

		if( empty( $attributes ) ) {
			throw new \RuntimeException( 'Attribute not found' );
		}

		$this->object->addProduct( $this->testItem->getId(), 1, [], [], [], array_keys( $attributes ) );

		$basket = $this->object->get();
		$this->assertEquals( 1, count( $basket->getProducts() ) );

		$product = $basket->getProduct( 0 );
		$this->assertEquals( 'U:TESTPSUB01', $product->getProductCode() );

		$attributes = $product->getAttributes();
		$this->assertEquals( 1, count( $attributes ) );

		if( ( $attribute = reset( $attributes ) ) === false ) {
			throw new \RuntimeException( 'No attribute' );
		}

		$this->assertEquals( 'hidden', $attribute->getType() );
		$this->assertEquals( '29', $product->getAttribute( 'width', 'hidden' ) );
	}
}
