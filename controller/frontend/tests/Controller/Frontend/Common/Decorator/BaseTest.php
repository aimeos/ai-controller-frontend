<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2016
 */


namespace Aimeos\Controller\Frontend\Common\Decorator;


class BaseTest extends \PHPUnit_Framework_TestCase
{
	private $object;


	protected function setUp()
	{
		$context = \TestHelperFrontend::getContext();
		$cntl = new \Aimeos\Controller\Frontend\Catalog\Standard( $context );

		$this->object = new \Aimeos\Controller\Frontend\Common\Decorator\Example( $cntl, $context );
	}


	public function testCall()
	{
		$this->assertInstanceOf( '\Aimeos\MShop\Common\Manager\Iface', $this->object->createManager( 'product' ) );
	}


	public function testCallInvalid()
	{
		$this->setExpectedException( '\Aimeos\Controller\Frontend\Exception' );
		$this->object->invalidMethod();
	}


	public function testGetContext()
	{
		$this->assertInstanceOf( '\Aimeos\MShop\Context\Item\Iface', $this->object->getContext() );
	}


	public function testGetController()
	{
		$this->assertInstanceOf( '\Aimeos\Controller\Frontend\Iface', $this->object->getController() );
	}
}