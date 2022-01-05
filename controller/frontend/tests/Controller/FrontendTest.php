<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2015-2022
 */


namespace Aimeos\Controller;


class FrontendTest extends \PHPUnit\Framework\TestCase
{
	public function testCreateController()
	{
		$controller = \Aimeos\Controller\Frontend::create( \TestHelperFrontend::context(), 'basket' );
		$this->assertInstanceOf( '\\Aimeos\\Controller\\Frontend\\Iface', $controller );
	}


	public function testCreateControllerEmpty()
	{
		$this->expectException( '\\Aimeos\\Controller\\Frontend\\Exception' );
		\Aimeos\Controller\Frontend::create( \TestHelperFrontend::context(), '' );
	}


	public function testCreateControllerInvalidName()
	{
		$this->expectException( '\\Aimeos\\Controller\\Frontend\\Exception' );
		\Aimeos\Controller\Frontend::create( \TestHelperFrontend::context(), '%^' );
	}


	public function testCreateControllerNotExisting()
	{
		$this->expectException( '\\Aimeos\\Controller\\Frontend\\Exception' );
		\Aimeos\Controller\Frontend::create( \TestHelperFrontend::context(), 'notexist' );
	}


	public function testCreateSubControllerNotExisting()
	{
		$this->expectException( '\\Aimeos\\Controller\\Frontend\\Exception' );
		\Aimeos\Controller\Frontend::create( \TestHelperFrontend::context(), 'basket/notexist' );
	}


	public function testCache()
	{
		$context = \TestHelperFrontend::context();
		\Aimeos\Controller\Frontend::cache( true );

		$controller1 = \Aimeos\Controller\Frontend::create( $context, 'basket' );
		$controller2 = \Aimeos\Controller\Frontend::create( $context, 'basket' );

		\Aimeos\Controller\Frontend::cache( false );
		$this->assertNotSame( $controller1, $controller2 );
	}
}
