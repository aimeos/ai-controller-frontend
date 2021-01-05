<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2017-2021
 */


namespace Aimeos\Controller\Frontend\Basket\Decorator;


class BundleTest extends \PHPUnit\Framework\TestCase
{
	private $object;
	private $context;


	protected function setUp() : void
	{
		$this->context = \TestHelperFrontend::getContext();
		$object = new \Aimeos\Controller\Frontend\Basket\Standard( $this->context );
		$this->object = new \Aimeos\Controller\Frontend\Basket\Decorator\Bundle( $object, $this->context );
	}


	protected function tearDown() : void
	{
		$this->object->clear();
		$this->context->getSession()->set( 'aimeos', [] );

		unset( $this->object );
	}


	public function testAddProductBundle()
	{
		$manager = \Aimeos\MShop::create( $this->context, 'product' );
		$item = $manager->find( 'U:BUNDLE', ['attribute', 'media', 'price', 'product', 'text'] );

		$this->assertSame( $this->object, $this->object->addProduct( $item, 1 ) );
		$this->assertEquals( 1, count( $this->object->get()->getProducts() ) );
		$this->assertEquals( 'U:BUNDLE', $this->object->get()->getProduct( 0 )->getProductCode() );
		$this->assertEquals( 2, count( $this->object->get()->getProduct( 0 )->getProducts() ) );
	}


	public function testAddProductNoBundle()
	{
		$manager = \Aimeos\MShop::create( $this->context, 'product' );
		$item = $manager->find( 'CNC', ['attribute', 'media', 'price', 'product', 'text'] );

		$this->assertSame( $this->object, $this->object->addProduct( $item, 1 ) );
		$this->assertEquals( 1, count( $this->object->get()->getProducts() ) );
		$this->assertEquals( 'CNC', $this->object->get()->getProduct( 0 )->getProductCode() );
		$this->assertEquals( 0, count( $this->object->get()->getProduct( 0 )->getProducts() ) );
	}
}
