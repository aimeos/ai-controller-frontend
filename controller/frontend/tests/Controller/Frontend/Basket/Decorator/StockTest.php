<?php

namespace Aimeos\Controller\Frontend\Basket\Decorator;


class StockTest extends \PHPUnit_Framework_TestCase
{
	private $object;
	private $context;


	protected function setUp()
	{
		$this->context = \TestHelperFrontend::getContext();
		$object = new \Aimeos\Controller\Frontend\Basket\Standard( $this->context );
		$this->object = new \Aimeos\Controller\Frontend\Basket\Decorator\Stock( $object, $this->context );
	}


	protected function tearDown()
	{
		$this->object->clear();
		$this->context->getSession()->set( 'aimeos', array() );

		unset( $this->object );
	}


	public function testAddProductNotEnoughStockException()
	{
		$productManager = \Aimeos\MShop\Product\Manager\Factory::createManager( \TestHelperFrontend::getContext() );

		$search = $productManager->createSearch();
		$search->setConditions( $search->compare( '==', 'product.code', 'IJKL' ) );

		$items = $productManager->searchItems( $search );

		if( ( $item = reset( $items ) ) === false ) {
			throw new \Exception( 'Product not found' );
		}

		try
		{
			$this->object->addProduct( $item->getId(), 5, array(), array(), array(), array(), array(), 'unit_warehouse3' );
			throw new \Exception( 'Expected exception not thrown' );
		}
		catch( \Aimeos\Controller\Frontend\Basket\Exception $e )
		{
			$item = $this->object->get()->getProduct( 0 );
			$this->assertEquals( 3, $item->getQuantity() );
		}
	}


	public function testAddProductNoStockException()
	{
		$productManager = \Aimeos\MShop\Product\Manager\Factory::createManager( \TestHelperFrontend::getContext() );

		$search = $productManager->createSearch();
		$search->setConditions( $search->compare( '==', 'product.code', 'EFGH' ) );

		$items = $productManager->searchItems( $search );

		if( ( $item = reset( $items ) ) === false ) {
			throw new \Exception( 'Product not found' );
		}

		try
		{
			$this->object->addProduct( $item->getId(), 5, array(), array(), array(), array(), array(), 'unit_warehouse2' );
			throw new \Exception( 'Expected exception not thrown' );
		}
		catch( \Aimeos\Controller\Frontend\Basket\Exception $e )
		{
			$this->assertEquals( array(), $this->object->get()->getProducts() );
		}
	}


	public function testAddProductNoStockRequired()
	{
		$productManager = \Aimeos\MShop\Product\Manager\Factory::createManager( \TestHelperFrontend::getContext() );

		$search = $productManager->createSearch();
		$search->setConditions( $search->compare( '==', 'product.code', 'IJKL' ) );

		$items = $productManager->searchItems( $search );

		if( ( $item = reset( $items ) ) === false ) {
			throw new \Exception( 'Product not found' );
		}

		$this->object->addProduct( $item->getId(), 5, array( 'stock' => false ) );
	}


	public function testAddProductNoStockItem()
	{
		$productManager = \Aimeos\MShop\Product\Manager\Factory::createManager( \TestHelperFrontend::getContext() );

		$search = $productManager->createSearch();
		$search->setConditions( $search->compare( '==', 'product.code', 'QRST' ) );

		$items = $productManager->searchItems( $search );

		if( ( $item = reset( $items ) ) === false ) {
			throw new \Exception( 'Product not found' );
		}

		$this->setExpectedException( '\\Aimeos\\Controller\\Frontend\\Basket\\Exception' );
		$this->object->addProduct( $item->getId(), 1 );
	}


	public function testEditProductNotEnoughStock()
	{
		$productManager = \Aimeos\MShop\Product\Manager\Factory::createManager( \TestHelperFrontend::getContext() );
		$search = $productManager->createSearch();
		$search->setConditions( $search->compare( '==', 'product.code', 'IJKL' ) );
		$items = $productManager->searchItems( $search );

		if( ( $item = reset( $items ) ) === false ) {
			throw new \Exception( 'Product not found' );
		}

		$this->object->addProduct( $item->getId(), 2, array(), array(), array(), array(), array(), 'unit_warehouse3' );

		$item = $this->object->get()->getProduct( 0 );
		$this->assertEquals( 2, $item->getQuantity() );

		try
		{
			$this->object->editProduct( 0, 5 );
			throw new \Exception( 'Expected exception not thrown' );
		}
		catch( \Aimeos\Controller\Frontend\Basket\Exception $e )
		{
			$item = $this->object->get()->getProduct( 0 );
			$this->assertEquals( 3, $item->getQuantity() );
			$this->assertEquals( 'IJKL', $item->getProductCode() );
		}
	}


	public function testEditProductNoStock()
	{
		$context = \TestHelperFrontend::getContext();

		$productManager = \Aimeos\MShop\Factory::createManager( $context, 'product' );
		$search = $productManager->createSearch();
		$search->setConditions( $search->compare( '==', 'product.code', 'IJKL' ) );
		$items = $productManager->searchItems( $search );

		if( ( $item = reset( $items ) ) === false ) {
			throw new \Exception( 'Product not found' );
		}

		$orderProductManager = \Aimeos\MShop\Factory::createManager( $context, 'order/base/product' );
		$orderProductItem = $orderProductManager->createItem();
		$orderProductItem->copyFrom( $item );
		$orderProductItem->setQuantity( 2 );
		$orderProductItem->setWarehouseCode( 'unit_warehouse3' );

		$pos = $this->object->get()->addProduct( $orderProductItem, 1 );

		$item = $this->object->get()->getProduct( $pos );
		$this->assertEquals( 2, $item->getQuantity() );

		try
		{
			$this->object->editProduct( $pos, 5 );
			throw new \Exception( 'Expected exception not thrown' );
		}
		catch( \Aimeos\Controller\Frontend\Basket\Exception $e )
		{
			$this->assertEquals( 3, $this->object->get()->getProduct( $pos )->getQuantity() );
		}
	}


	public function testEditProductStockNotChecked()
	{
		$productManager = \Aimeos\MShop\Product\Manager\Factory::createManager( \TestHelperFrontend::getContext() );
		$search = $productManager->createSearch();
		$search->setConditions( $search->compare( '==', 'product.code', 'IJKL' ) );
		$items = $productManager->searchItems( $search );

		if( ( $item = reset( $items ) ) === false ) {
			throw new \Exception( 'Product not found' );
		}

		$this->object->addProduct( $item->getId(), 2, array(), array(), array(), array(), array(), 'unit_warehouse3' );

		$item = $this->object->get()->getProduct( 0 );
		$this->assertEquals( 2, $item->getQuantity() );

		$this->object->editProduct( 0, 5, array( 'stock' => false ) );

		$item = $this->object->get()->getProduct( 0 );
		$this->assertEquals( 5, $item->getQuantity() );
		$this->assertEquals( 'IJKL', $item->getProductCode() );
	}
}
