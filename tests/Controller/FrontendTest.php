<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2015-2023
 */


namespace Aimeos\Controller;


class FrontendTest extends \PHPUnit\Framework\TestCase
{
	public function testCreateController()
	{
		$controller = \Aimeos\Controller\Frontend::create( \TestHelper::context(), 'basket' );
		$this->assertInstanceOf( '\\Aimeos\\Controller\\Frontend\\Iface', $controller );
	}


	public function testCreateControllerEmpty()
	{
		$this->expectException( '\\Aimeos\\Controller\\Frontend\\Exception' );
		\Aimeos\Controller\Frontend::create( \TestHelper::context(), '' );
	}


	public function testCreateControllerInvalidName()
	{
		$this->expectException( \LogicException::class );
		\Aimeos\Controller\Frontend::create( \TestHelper::context(), '%^unknown' );
	}


	public function testCreateControllerNotExisting()
	{
		$this->expectException( \LogicException::class );
		\Aimeos\Controller\Frontend::create( \TestHelper::context(), 'unknown' );
	}


	public function testCreateSubControllerNotExisting()
	{
		$this->expectException( \LogicException::class );
		\Aimeos\Controller\Frontend::create( \TestHelper::context(), 'basket/unknown' );
	}


	public function testCache()
	{
		$context = \TestHelper::context();
		\Aimeos\Controller\Frontend::cache( true );

		$controller1 = \Aimeos\Controller\Frontend::create( $context, 'basket' );
		$controller2 = \Aimeos\Controller\Frontend::create( $context, 'basket' );

		\Aimeos\Controller\Frontend::cache( false );
		$this->assertNotSame( $controller1, $controller2 );
	}
}
