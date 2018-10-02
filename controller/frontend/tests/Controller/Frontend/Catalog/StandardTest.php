<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Metaways Infosystems GmbH, 2012
 * @copyright Aimeos (aimeos.org), 2015-2018
 */


namespace Aimeos\Controller\Frontend\Catalog;


class StandardTest extends \PHPUnit\Framework\TestCase
{
	private $context;
	private $object;


	protected function setUp()
	{
		$this->context = \TestHelperFrontend::getContext();
		$this->object = new \Aimeos\Controller\Frontend\Catalog\Standard( $this->context );
	}


	protected function tearDown()
	{
		unset( $this->object, $this->context );
	}


	public function testCreateFilter()
	{
		$filter = $this->object->createFilter();

		$this->assertInstanceOf( '\\Aimeos\\MW\\Criteria\\Iface', $filter );
	}


	public function testGetPath()
	{
		$manager = \Aimeos\MShop\Factory::createManager( $this->context, 'catalog' );
		$items = $this->object->getPath( $manager->findItem( 'cafe' )->getId() );

		$this->assertEquals( 3, count( $items ) );

		foreach( $items as $item ) {
			$this->assertInstanceOf( '\\Aimeos\\MShop\\Catalog\\Item\\Iface', $item );
		}
	}


	public function testGetTree()
	{
		$tree = $this->object->getTree();

		$this->assertEquals( 2, count( $tree->getChildren() ) );

		foreach( $tree->getChildren() as $item ) {
			$this->assertInstanceOf( '\\Aimeos\\MShop\\Catalog\\Item\\Iface', $item );
		}
	}
}
