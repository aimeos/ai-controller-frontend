<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2018
 */


namespace Aimeos\Controller\Frontend\Supplier;


class FactoryTest extends \PHPUnit\Framework\TestCase
{
	public function testCreateController()
	{
		$target = '\\Aimeos\\Controller\\Frontend\\Supplier\\Iface';

		$controller = \Aimeos\Controller\Frontend\Supplier\Factory::createController( \TestHelperFrontend::getContext() );
		$this->assertInstanceOf( $target, $controller );

		$controller = \Aimeos\Controller\Frontend\Supplier\Factory::createController( \TestHelperFrontend::getContext(), 'Standard' );
		$this->assertInstanceOf( $target, $controller );
	}


	public function testCreateControllerInvalidImplementation()
	{
		$this->setExpectedException( '\\Aimeos\\MW\\Common\\Exception' );
		\Aimeos\Controller\Frontend\Supplier\Factory::createController( \TestHelperFrontend::getContext(), 'Invalid' );
	}


	public function testCreateControllerInvalidName()
	{
		$this->setExpectedException( '\\Aimeos\\Controller\\Frontend\\Exception' );
		\Aimeos\Controller\Frontend\Supplier\Factory::createController( \TestHelperFrontend::getContext(), '%^' );
	}


	public function testCreateControllerNotExisting()
	{
		$this->setExpectedException( '\\Aimeos\\Controller\\Frontend\\Exception' );
		\Aimeos\Controller\Frontend\Supplier\Factory::createController( \TestHelperFrontend::getContext(), 'notexist' );
	}
}
