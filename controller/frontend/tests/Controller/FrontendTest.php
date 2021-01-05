<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2015-2021
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
		$this->expectException( '\\Aimeos\\Controller\\Frontend\\Exception' );
		\Aimeos\Controller\Frontend::create( \TestHelperFrontend::getContext(), '' );
	}


	public function testCreateControllerInvalidName()
	{
		$this->expectException( '\\Aimeos\\Controller\\Frontend\\Exception' );
		\Aimeos\Controller\Frontend::create( \TestHelperFrontend::getContext(), '%^' );
	}


	public function testCreateControllerNotExisting()
	{
		$this->expectException( '\\Aimeos\\Controller\\Frontend\\Exception' );
		\Aimeos\Controller\Frontend::create( \TestHelperFrontend::getContext(), 'notexist' );
	}


	public function testCreateSubControllerNotExisting()
	{
		$this->expectException( '\\Aimeos\\Controller\\Frontend\\Exception' );
		\Aimeos\Controller\Frontend::create( \TestHelperFrontend::getContext(), 'basket/notexist' );
	}


	public function testCache()
	{
		$context = \TestHelperFrontend::getContext();
		\Aimeos\Controller\Frontend::cache( true );

		$controller1 = \Aimeos\Controller\Frontend::create( $context, 'basket' );
		$controller2 = \Aimeos\Controller\Frontend::create( $context, 'basket' );

		\Aimeos\Controller\Frontend::cache( false );
		$this->assertNotSame( $controller1, $controller2 );
	}
}
