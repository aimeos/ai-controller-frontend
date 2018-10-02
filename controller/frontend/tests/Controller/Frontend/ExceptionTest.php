<?php

namespace Aimeos\Controller\Frontend;


/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Metaways Infosystems GmbH, 2013
 * @copyright Aimeos (aimeos.org), 2015-2018
 */
class ExceptionTest extends \PHPUnit\Framework\TestCase
{
	private $object;


	protected function setUp()
	{
		$this->object = new \Aimeos\Controller\Frontend\Exception( 'msg', 1, null, array( 'key' => 'value' ) );
	}


	protected function tearDown()
	{
	}


	public function testGetMessage()
	{
		$this->assertEquals( 'msg', $this->object->getMessage() );
	}


	public function testGetCode()
	{
		$this->assertEquals( 1, $this->object->getCode() );
	}


	public function testGetErrorList()
	{
		$this->assertEquals( array( 'key' => 'value' ), $this->object->getErrorList() );
	}
}
