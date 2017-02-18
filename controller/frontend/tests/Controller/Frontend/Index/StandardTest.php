<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2017
 */


namespace Aimeos\Controller\Frontend\Index;


class StandardTest extends \PHPUnit_Framework_TestCase
{
	private $object;


	protected function setUp()
	{
		$this->object = new \Aimeos\Controller\Frontend\Index\Standard( \TestHelperFrontend::getContext() );
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
		$this->assertEquals( array(), $filter->getSortations() );
		$this->assertEquals( 0, $filter->getSliceStart() );
		$this->assertEquals( 100, $filter->getSliceSize() );
	}


	public function testAddFilterAttribute()
	{
		$filter = $this->object->createFilter();
		$filter = $this->object->addFilterAttribute( $filter, array( 0, 1 ), array(), array() );

		$list = $filter->getConditions()->getExpressions();

		if( !isset( $list[0] ) || !( $list[0] instanceof \Aimeos\MW\Criteria\Expression\Compare\Iface ) ) {
			throw new \RuntimeException( 'Wrong expression' );
		}

		$this->assertEquals( 'index.attributeaggregate([0,1])', $list[0]->getName() );
		$this->assertEquals( 2, $list[0]->getValue() );
	}


	public function testAddFilterAttributeOptions()
	{
		$filter = $this->object->createFilter();
		$filter = $this->object->addFilterAttribute( $filter, array(), array( 1 ), array() );

		$list = $filter->getConditions()->getExpressions();

		if( !isset( $list[0] ) || !( $list[0] instanceof \Aimeos\MW\Criteria\Expression\Compare\Iface ) ) {
			throw new \RuntimeException( 'Wrong expression' );
		}

		$this->assertEquals( 'index.attributeaggregate([1])', $list[0]->getName() );
		$this->assertEquals( 0, $list[0]->getValue() );
	}


	public function testAddFilterAttributeOne()
	{
		$filter = $this->object->createFilter();
		$filter = $this->object->addFilterAttribute( $filter, array(), array(), array( 'test' => array( 2 ) ) );

		$list = $filter->getConditions()->getExpressions();

		if( !isset( $list[0] ) || !( $list[0] instanceof \Aimeos\MW\Criteria\Expression\Compare\Iface ) ) {
			throw new \RuntimeException( 'Wrong expression' );
		}

		$this->assertEquals( 'index.attributeaggregate([2])', $list[0]->getName() );
		$this->assertEquals( 0, $list[0]->getValue() );
	}


	public function testAddFilterCategory()
	{
		$filter = $this->object->createFilter();
		$filter = $this->object->addFilterCategory( $filter, 0 );

		$list = $filter->getConditions()->getExpressions();

		if( !isset( $list[0] ) || !( $list[0] instanceof \Aimeos\MW\Criteria\Expression\Compare\Iface ) ) {
			throw new \RuntimeException( 'Wrong expression' );
		}

		$this->assertEquals( 'index.catalog.id', $list[0]->getName() );
		$this->assertEquals( array( 0 ), $list[0]->getValue() );
		$this->assertEquals( array(), $filter->getSortations() );
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
		$this->assertEquals( 'index.text.relevance("default","de","Espresso")', $list[0]->getName() );
		$this->assertEquals( 0, $list[0]->getValue() );

		$this->assertEquals( array(), $filter->getSortations() );
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

		$this->assertEquals( 'sort:index.catalog.position("test",["0"])', $item->getName() );
		$this->assertEquals( '-', $item->getOperator() );
	}


	public function testCreateFilterSortRelevanceText()
	{
		$filter = $this->object->createFilter( 'relevance', '-', 1, 2, 'test' );
		$filter = $this->object->addFilterText( $filter, 'Espresso' );

		$this->assertInstanceOf( '\\Aimeos\\MW\\Criteria\\Iface', $filter );
		$this->assertEquals( array(), $filter->getSortations() );
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


	public function testCreateFilterSortName()
	{
		$filter = $this->object->createFilter( 'name' );

		$this->assertInstanceOf( '\\Aimeos\\MW\\Criteria\\Iface', $filter );

		$sort = $filter->getSortations();
		if( ( $item = reset( $sort ) ) === false ) {
			throw new \RuntimeException( 'Sortation not set' );
		}

		$this->assertEquals( 'sort:index.text.value("default","de","name")', $item->getName() );
	}


	public function testCreateFilterSortPrice()
	{
		$filter = $this->object->createFilter( 'price' );

		$this->assertInstanceOf( '\\Aimeos\\MW\\Criteria\\Iface', $filter );

		$sort = $filter->getSortations();
		if( ( $item = reset( $sort ) ) === false ) {
			throw new \RuntimeException( 'Sortation not set' );
		}

		$this->assertStringStartsWith( 'sort:index.price.value("default","EUR","default")', $item->getName() );
	}


	public function testCreateFilterSortInvalid()
	{
		$filter = $this->object->createFilter( '', 'failure' );

		$this->assertInstanceOf( '\\Aimeos\\MW\\Criteria\\Iface', $filter );
		$this->assertEquals( array(), $filter->getSortations() );
	}


	public function testGetItemsCategory()
	{
		$catalogManager = \Aimeos\MShop\Catalog\Manager\Factory::createManager( \TestHelperFrontend::getContext() );
		$search = $catalogManager->createSearch();

		$search->setConditions( $search->compare( '==', 'catalog.code', 'new' ) );
		$search->setSlice( 0, 1 );
		$items = $catalogManager->searchItems( $search );

		if( ( $item = reset( $items ) ) === false ) {
			throw new \RuntimeException( 'Index item not found' );
		}

		$filter = $this->object->createFilter( 'position', '+', 1, 1 );
		$filter = $this->object->addFilterCategory( $filter, $item->getId() );

		$total = 0;
		$results = $this->object->getItems( $filter, array(), $total );

		$this->assertEquals( 3, $total );
		$this->assertEquals( 1, count( $results ) );
	}


	public function testGetItemsText()
	{
		$filter = $this->object->createFilter( 'relevance', '+', 0, 1, 'unittype13' );
		$filter = $this->object->addFilterText( $filter, 'Expresso', 'relevance', '+', 'unittype13' );

		$total = 0;
		$results = $this->object->getItems( $filter, array(), $total );

		$this->assertEquals( 2, $total );
		$this->assertEquals( 1, count( $results ) );
	}


	public function testCreateTextFilter()
	{
		$filter = $this->object->createTextFilter( 'Expresso', 'name', '+', 0, 1 );

		$this->assertInstanceOf( '\\Aimeos\\MW\\Criteria\\Iface', $filter );
		$this->assertInstanceOf( '\\Aimeos\\MW\\Criteria\\Expression\\Combine\\Iface', $filter->getConditions() );
		$this->assertEquals( 3, count( $filter->getConditions()->getExpressions() ) );
	}


	public function testGetTextListName()
	{
		$filter = $this->object->createTextFilter( 'Cafe Noire', 'relevance', '-', 0, 25, 'unittype19', 'name' );
		$results = $this->object->getTextList( $filter );

		$this->assertEquals( 1, count( $results ) );
		$this->assertContains( 'Cafe Noire Cappuccino', $results );
	}

}