<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2017-2018
 */


namespace Aimeos\Controller\Frontend\Attribute;


class StandardTest extends \PHPUnit\Framework\TestCase
{
	private $object;


	protected function setUp()
	{
		$this->object = new \Aimeos\Controller\Frontend\Attribute\Standard( \TestHelperFrontend::getContext() );
	}


	protected function tearDown()
	{
		unset( $this->object );
	}


	public function testCreateFilter()
	{
		$filter = $this->object->createFilter();

		$this->assertInstanceOf( '\\Aimeos\\MW\\Criteria\\Iface', $filter );
		$this->assertEquals( 2, count( $filter->getSortations() ) );
		$this->assertEquals( 0, $filter->getSliceStart() );
		$this->assertEquals( 100, $filter->getSliceSize() );
	}


	public function testAddFilterTypes()
	{
		$filter = $this->object->createFilter();
		$filter = $this->object->addFilterTypes( $filter, ['size'] );

		$list = $filter->getConditions()->getExpressions();

		if( !isset( $list[0] ) || !( $list[0] instanceof \Aimeos\MW\Criteria\Expression\Compare\Iface ) ) {
			throw new \RuntimeException( 'Wrong expression' );
		}

		$this->assertEquals( 'attribute.type.code', $list[0]->getName() );
		$this->assertEquals( 1, count( $list[0]->getValue() ) );
	}


	public function testGetItem()
	{
		$context = \TestHelperFrontend::getContext();
		$manager = \Aimeos\MShop\Factory::createManager( $context, 'attribute' );
		$id = $manager->findItem( 'xs', [], 'product', 'size' )->getId();

		$result = $this->object->getItem( $id, ['text'] );

		$this->assertInstanceOf( '\Aimeos\MShop\Attribute\Item\Iface', $result );
		$this->assertEquals( 3, count( $result->getRefItems( 'text' ) ) );
	}


	public function testGetItems()
	{
		$context = \TestHelperFrontend::getContext();
		$manager = \Aimeos\MShop\Factory::createManager( $context, 'attribute' );
		$id = $manager->findItem( 'xs', [], 'product', 'size' )->getId();

		$result = $this->object->getItems( [$id], ['text'] );

		$this->assertInternalType( 'array', $result );
		$this->assertEquals( 1, count( $result ) );

		foreach( $result as $attrItem )
		{
			$this->assertInstanceOf( '\Aimeos\MShop\Attribute\Item\Iface', $attrItem );
			$this->assertEquals( 3, count( $attrItem->getRefItems( 'text' ) ) );
		}
	}


	public function testSearchItems()
	{
		$filter = $this->object->createFilter();
		$filter = $this->object->addFilterTypes( $filter, ['size'] );

		$total = 0;
		$results = $this->object->searchItems( $filter, ['text'], $total );

		$this->assertEquals( 6, $total );
		$this->assertEquals( 6, count( $results ) );
	}
}
