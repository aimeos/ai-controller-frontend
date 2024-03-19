<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2017-2024
 */


namespace Aimeos\Controller\Frontend;


class Example extends Base
{
}


class BaseTest extends \PHPUnit\Framework\TestCase
{
	private $object;


	protected function setUp() : void
	{
		$context = \TestHelper::context();
		$this->object = new \Aimeos\Controller\Frontend\Example( $context );
	}


	protected function tearDown() : void
	{
		unset( $this->object );
	}


	public function testGetContext()
	{
		$result = $this->access( 'context' )->invokeArgs( $this->object, [] );

		$this->assertInstanceOf( \Aimeos\MShop\ContextIface::class, $result );
	}


	protected function access( $name )
	{
		$class = new \ReflectionClass( \Aimeos\Controller\Frontend\Base::class );
		$method = $class->getMethod( $name );
		$method->setAccessible( true );

		return $method;
	}
}
