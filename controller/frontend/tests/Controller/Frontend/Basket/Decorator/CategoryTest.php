<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2017-2020
 */


namespace Aimeos\Controller\Frontend\Basket\Decorator;


class CategoryTest extends \PHPUnit\Framework\TestCase
{
	private $object;
	private $context;


	protected function setUp() : void
	{
		$this->context = \TestHelperFrontend::getContext();
		$object = new \Aimeos\Controller\Frontend\Basket\Standard( $this->context );
		$this->object = new \Aimeos\Controller\Frontend\Basket\Decorator\Category( $object, $this->context );
	}


	protected function tearDown() : void
	{
		$this->object->clear();
		$this->context->getSession()->set( 'aimeos', [] );

		unset( $this->object );
	}


	public function testAddProductWithCategory()
	{
		$manager = \Aimeos\MShop::create( $this->context, 'product' );
		$item = $manager->findItem( 'CNE', ['price'] );

		$this->assertSame( $this->object, $this->object->addProduct( $item ) );
	}


	public function testAddProductNoCategory()
	{
		$manager = \Aimeos\MShop::create( $this->context, 'product' );
		$item = $manager->findItem( 'ABCD' );

		$this->expectException( \Aimeos\Controller\Frontend\Basket\Exception::class );
		$this->object->addProduct( $item );
	}
}
