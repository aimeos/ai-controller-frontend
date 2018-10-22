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
		$filter = $this->object->createFilter();
		$list = $this->object->aggregate( $filter, 'index.attribute.id' );

		$this->assertGreaterThan( 0, count( $list ) );
	}


	public function testCreateFilter()
	{
		$filter = $this->object->createFilter();

		$this->assertInstanceOf( '\\Aimeos\\MW\\Criteria\\Iface', $filter );
		$this->assertEquals( [], $filter->getSortations() );
		$this->assertEquals( 0, $filter->getSliceStart() );
		$this->assertEquals( 100, $filter->getSliceSize() );
	}


	public function testCreateFilterIgnoreDates()
	{
		$this->context->getConfig()->set( 'controller/frontend/product/ignore-dates', true );

		$filter = $this->object->createFilter();

		$this->assertInstanceOf( '\\Aimeos\\MW\\Criteria\\Iface', $filter );
	}


	public function testAddFilterAttribute()
	{
		$filter = $this->object->createFilter();
		$filter = $this->object->addFilterAttribute( $filter, array( 1, 2 ), [], [] );

		$list = $filter->getConditions()->getExpressions();

		if( !isset( $list[0] ) || !( $list[0] instanceof \Aimeos\MW\Criteria\Expression\Compare\Iface ) ) {
			throw new \RuntimeException( 'Wrong expression' );
		}

		$this->assertEquals( 'index.attribute:all([1,2])', $list[0]->getName() );
	}


	public function testAddFilterAttributeOptions()
	{
		$filter = $this->object->createFilter();
		$filter = $this->object->addFilterAttribute( $filter, [], array( 1 ), [] );

		$list = $filter->getConditions()->getExpressions();

		if( !isset( $list[0] ) || !( $list[0] instanceof \Aimeos\MW\Criteria\Expression\Compare\Iface ) ) {
			throw new \RuntimeException( 'Wrong expression' );
		}

		$this->assertEquals( 'index.attribute.id', $list[0]->getName() );
		$this->assertEquals( [1], $list[0]->getValue() );
	}


	public function testAddFilterAttributeOne()
	{
		$filter = $this->object->createFilter();
		$filter = $this->object->addFilterAttribute( $filter, [], [], array( 'test' => array( 2 ) ) );

		$list = $filter->getConditions()->getExpressions();

		if( !isset( $list[0] ) || !( $list[0] instanceof \Aimeos\MW\Criteria\Expression\Compare\Iface ) ) {
			throw new \RuntimeException( 'Wrong expression' );
		}

		$this->assertEquals( 'index.attribute.id', $list[0]->getName() );
		$this->assertEquals( [2], $list[0]->getValue() );
	}


	public function testAddFilterCategory()
	{
		$context = \TestHelperFrontend::getContext();
		$manager = \Aimeos\MShop\Factory::createManager( $context, 'catalog' );

		$catId = $manager->findItem( 'root' )->getId();
		$level = \Aimeos\MW\Tree\Manager\Base::LEVEL_LIST;

		$filter = $this->object->createFilter();
		$filter = $this->object->addFilterCategory( $filter, $catId, $level );

		$list = $filter->getConditions()->getExpressions();

		if( !isset( $list[0] ) || !( $list[0] instanceof \Aimeos\MW\Criteria\Expression\Compare\Iface ) ) {
			throw new \RuntimeException( 'Wrong expression' );
		}

		$this->assertEquals( 'index.catalog.id', $list[0]->getName() );
		$this->assertEquals( 3, count( $list[0]->getValue() ) );
		$this->assertEquals( [], $filter->getSortations() );
	}


	public function testAddFilterSupplier()
	{
		$filter = $this->object->createFilter();
		$filter = $this->object->addFilterSupplier( $filter, [1, 2] );

		$list = $filter->getConditions()->getExpressions();

		if( !isset( $list[0] ) || !( $list[0] instanceof \Aimeos\MW\Criteria\Expression\Compare\Iface ) ) {
			throw new \RuntimeException( 'Wrong expression' );
		}

		$this->assertEquals( 'index.supplier.id', $list[0]->getName() );
		$this->assertEquals( 2, count( $list[0]->getValue() ) );
	}


	public function testAddFilterText()
	{
		$filter = $this->object->createFilter();
		$filter = $this->object->addFilterText( $filter, 'Espresso' );

		$this->assertInstanceOf( '\\Aimeos\\MW\\Criteria\\Iface', $filter );

		$list = $filter->getConditions()->getExpressions();


		if( !isset( $list[0] ) || !( $list[0] instanceof \Aimeos\MW\Criteria\Expression\Compare\Iface ) ) {
			throw new \RuntimeException( 'Wrong expression' );
		}
		$this->assertEquals( 'index.text:relevance("default","de","Espresso")', $list[0]->getName() );
		$this->assertEquals( 0, $list[0]->getValue() );

		$this->assertEquals( [], $filter->getSortations() );
		$this->assertEquals( 0, $filter->getSliceStart() );
		$this->assertEquals( 100, $filter->getSliceSize() );
	}


	public function testCreateFilterSortRelevanceCategory()
	{
		$level = \Aimeos\MW\Tree\Manager\Base::LEVEL_ONE;

		$filter = $this->object->createFilter();
		$filter = $this->object->addFilterCategory( $filter, 0, $level, 'relevance', '-', 'test' );

		$this->assertInstanceOf( '\\Aimeos\\MW\\Criteria\\Iface', $filter );

		$sort = $filter->getSortations();
		if( ( $item = reset( $sort ) ) === false ) {
			throw new \RuntimeException( 'Sortation not set' );
		}

		$this->assertEquals( 'sort:index.catalog:position("test",["0"],0,100)', $item->getName() );
		$this->assertEquals( '-', $item->getOperator() );
	}


	public function testCreateFilterSortRelevanceText()
	{
		$filter = $this->object->createFilter( 'relevance', '-', 1, 2, 'test' );
		$filter = $this->object->addFilterText( $filter, 'Espresso' );

		$this->assertInstanceOf( '\\Aimeos\\MW\\Criteria\\Iface', $filter );
		$this->assertEquals( [], $filter->getSortations() );
		$this->assertEquals( 1, $filter->getSliceStart() );
		$this->assertEquals( 2, $filter->getSliceSize() );
	}


	public function testCreateFilterSortCode()
	{
		$filter = $this->object->createFilter( 'code' );

		$this->assertInstanceOf( '\\Aimeos\\MW\\Criteria\\Iface', $filter );

		$sort = $filter->getSortations();
		if( ( $item = reset( $sort ) ) === false ) {
			throw new \RuntimeException( 'Sortation not set' );
		}

		$this->assertEquals( 'product.code', $item->getName() );
	}


	public function testCreateFilterSortCtime()
	{
		$filter = $this->object->createFilter( 'ctime' );

		$this->assertInstanceOf( '\\Aimeos\\MW\\Criteria\\Iface', $filter );

		$sort = $filter->getSortations();
		if( ( $item = reset( $sort ) ) === false ) {
			throw new \RuntimeException( 'Sortation not set' );
		}

		$this->assertEquals( 'product.ctime', $item->getName() );
	}


	public function testCreateFilterSortName()
	{
		$filter = $this->object->createFilter( 'name' );

		$this->assertInstanceOf( '\\Aimeos\\MW\\Criteria\\Iface', $filter );

		$sort = $filter->getSortations();
		if( ( $item = reset( $sort ) ) === false ) {
			throw new \RuntimeException( 'Sortation not set' );
		}

		$this->assertEquals( 'sort:index.text:name("de")', $item->getName() );
	}


	public function testCreateFilterSortPrice()
	{
		$filter = $this->object->createFilter( 'price' );

		$this->assertInstanceOf( '\\Aimeos\\MW\\Criteria\\Iface', $filter );

		$sort = $filter->getSortations();
		if( ( $item = reset( $sort ) ) === false ) {
			throw new \RuntimeException( 'Sortation not set' );
		}

		$this->assertStringStartsWith( 'sort:index.price:value("default","EUR","default")', $item->getName() );
	}


	public function testCreateFilterSortInvalid()
	{
		$filter = $this->object->createFilter( '', 'failure' );

		$this->assertInstanceOf( '\\Aimeos\\MW\\Criteria\\Iface', $filter );
		$this->assertEquals( [], $filter->getSortations() );
	}


	public function testGetItem()
	{
		$context = \TestHelperFrontend::getContext();
		$id = \Aimeos\MShop\Factory::createManager( $context, 'product' )->findItem( 'CNC' )->getId();

		$result = $this->object->getItem( $id );

		$this->assertInstanceOf( '\Aimeos\MShop\Product\Item\Iface', $result );
		$this->assertGreaterThan( 0, $result->getPropertyItems() );
		$this->assertGreaterThan( 0, $result->getRefItems( 'attribute' ) );
		$this->assertGreaterThan( 0, $result->getRefItems( 'media' ) );
		$this->assertGreaterThan( 0, $result->getRefItems( 'price' ) );
		$this->assertGreaterThan( 0, $result->getRefItems( 'product' ) );
		$this->assertGreaterThan( 0, $result->getRefItems( 'text' ) );
	}


	public function testGetItems()
	{
		$context = \TestHelperFrontend::getContext();
		$context->getConfig()->set( 'controller/frontend/product/ignore-dates', true );

		$manager = \Aimeos\MShop\Factory::createManager( $context, 'product' );

		$search = $manager->createSearch();
		$search->setConditions( $search->compare( '==', 'product.code', array( 'CNC', 'CNE' ) ) );

		$ids = [];
		foreach( $manager->searchItems( $search ) as $productItem ) {
			$ids[] = $productItem->getId();
		}


		$result = $this->object->getItems( $ids );

		$this->assertEquals( 2, count( $result ) );

		foreach( $result as $productItem ) {
			$this->assertInstanceOf( '\Aimeos\MShop\Product\Item\Iface', $productItem );
		}
	}


	public function testSearchItemsCategory()
	{
		$catalogManager = \Aimeos\MShop\Catalog\Manager\Factory::createManager( \TestHelperFrontend::getContext() );
		$search = $catalogManager->createSearch();

		$search->setConditions( $search->compare( '==', 'catalog.code', 'new' ) );
		$search->setSlice( 0, 1 );
		$items = $catalogManager->searchItems( $search );

		if( ( $item = reset( $items ) ) === false ) {
			throw new \RuntimeException( 'Product item not found' );
		}

		$filter = $this->object->createFilter( 'position', '+', 1, 1 );
		$filter = $this->object->addFilterCategory( $filter, $item->getId() );

		$total = 0;
		$results = $this->object->searchItems( $filter, [], $total );

		$this->assertEquals( 3, $total );
		$this->assertEquals( 1, count( $results ) );
	}


	public function testSearchItemsText()
	{
		$filter = $this->object->createFilter( 'relevance', '+', 0, 1, 'unittype13' );
		$filter = $this->object->addFilterText( $filter, 'Expresso', 'relevance', '+', 'unittype13' );

		$total = 0;
		$results = $this->object->searchItems( $filter, [], $total );

		$this->assertEquals( 2, $total );
		$this->assertEquals( 1, count( $results ) );
	}
}
