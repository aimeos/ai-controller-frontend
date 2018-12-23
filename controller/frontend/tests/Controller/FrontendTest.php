<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2015-2018
 */


namespace Aimeos\Controller;


class FrontendTest extends \PHPUnit\Framework\TestCase
{
	public function testCreateController()
	{
		$controller = \Aimeos\Controller\Frontend::create( \TestHelperFrontend::getContext(), 'basket' );
		$this->assertInstanceOf( '\\Aimeos\\Controller\\Frontend\\Iface', $controller );
	}


	public function testCreateControllerEmpty()
	{
		$this->setExpectedException( '\\Aimeos\\Controller\\Frontend\\Exception' );
		\Aimeos\Controller\Frontend::create( \TestHelperFrontend::getContext(), '' );
	}


	public function testCreateControllerInvalidName()
	{
		$this->setExpectedException( '\\Aimeos\\Controller\\Frontend\\Exception' );
		\Aimeos\Controller\Frontend::create( \TestHelperFrontend::getContext(), '%^' );
	}


	public function testCreateControllerNotExisting()
	{
		$this->setExpectedException( '\\Aimeos\\Controller\\Frontend\\Exception' );
		\Aimeos\Controller\Frontend::create( \TestHelperFrontend::getContext(), 'notexist' );
	}


	public function testCreateSubControllerNotExisting()
	{
		$this->setExpectedException( '\\Aimeos\\Controller\\Frontend\\Exception' );
		\Aimeos\Controller\Frontend::create( \TestHelperFrontend::getContext(), 'basket/notexist' );
	}


	public function testClear()
	{
		$cache = \Aimeos\Controller\Frontend::cache( true );

		$context = \TestHelperFrontend::getContext();

		$controller1 = \Aimeos\Controller\Frontend::create( $context, 'basket' );
		\Aimeos\Controller\Frontend::clear();
		$controller2 = \Aimeos\Controller\Frontend::create( $context, 'basket' );

		\Aimeos\Controller\Frontend::cache( $cache );

		$this->assertNotSame( $controller1, $controller2 );
	}


	public function testClearSite()
	{
		$cache = \Aimeos\Controller\Frontend::cache( true );

		$context = \TestHelperFrontend::getContext();

		$basket1 = \Aimeos\Controller\Frontend::create( $context, 'basket' );
		$catalog1 = \Aimeos\Controller\Frontend::create( $context, 'catalog' );

		\Aimeos\Controller\Frontend::clear( (string) $context );

		$basket2 = \Aimeos\Controller\Frontend::create( $context, 'basket' );
		$catalog2 = \Aimeos\Controller\Frontend::create( $context, 'catalog' );

		\Aimeos\Controller\Frontend::cache( $cache );

		$this->assertNotSame( $basket1, $basket2 );
		$this->assertNotSame( $catalog1, $catalog2 );
	}


	public function testClearSpecific()
	{
		$cache = \Aimeos\Controller\Frontend::cache( true );

		$context = \TestHelperFrontend::getContext();

		$basket1 = \Aimeos\Controller\Frontend::create( $context, 'basket' );
		$catalog1 = \Aimeos\Controller\Frontend::create( $context, 'catalog' );

		\Aimeos\Controller\Frontend::clear( (string) $context, 'basket' );

		$basket2 = \Aimeos\Controller\Frontend::create( $context, 'basket' );
		$catalog2 = \Aimeos\Controller\Frontend::create( $context, 'catalog' );

		\Aimeos\Controller\Frontend::cache( $cache );

		$this->assertNotSame( $basket1, $basket2 );
		$this->assertSame( $catalog1, $catalog2 );
	}

}