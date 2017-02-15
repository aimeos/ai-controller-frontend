<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Metaways Infosystems GmbH, 2012
 * @copyright Aimeos (aimeos.org), 2015-2017
 */


namespace Aimeos\Controller\Frontend\Service\Decorator;


class ExampleTest extends \PHPUnit_Framework_TestCase
{
	private $object;


	protected function setUp()
	{
		$context = \TestHelperFrontend::getContext();
		$controller = \Aimeos\Controller\Frontend\Service\Factory::createController( $context, 'Standard' );
		$this->object = new \Aimeos\Controller\Frontend\Service\Decorator\Example( $controller, $context );
	}


	protected function tearDown()
	{
		$this->object = null;
	}


	public function testCall()
	{
		$this->setExpectedException( '\\Aimeos\\Controller\\Frontend\\Service\\Exception' );
		$this->object->checkServiceAttributes( 'delivery', -1, array() );
	}

}
