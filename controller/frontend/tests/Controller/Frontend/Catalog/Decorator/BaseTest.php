<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2017-2018
 */


namespace Aimeos\Controller\Frontend\Catalog\Decorator;


class BaseTest extends \PHPUnit\Framework\TestCase
{
	private $context;
	private $object;
	private $stub;


	protected function setUp()
	{
		$this->context = \TestHelperFrontend::getContext();

		$this->stub = $this->getMockBuilder( '\Aimeos\Controller\Frontend\Catalog\Standard' )
			->disableOriginalConstructor()
			->getMock();

		$this->object = $this->getMockBuilder( '\Aimeos\Controller\Frontend\Catalog\Decorator\Base' )
			->setConstructorArgs( [$this->stub, $this->context] )
			->getMockForAbstractClass();
	}


	protected function tearDown()
	{
		unset( $this->context, $this->object, $this->stub );
	}


	public function testConstructException()
	{
		$stub = $this->getMockBuilder( '\Aimeos\Controller\Frontend\Iface' )->getMock();

		$this->setExpectedException( '\Aimeos\MW\Common\Exception' );

		$this->getMockBuilder( '\Aimeos\Controller\Frontend\Catalog\Decorator\Base' )
			->setConstructorArgs( [$stub, $this->context] )
			->getMockForAbstractClass();
	}


	public function testCall()
	{
		$stub = $this->getMockBuilder( '\Aimeos\Controller\Frontend\Catalog\Standard' )
			->disableOriginalConstructor()
			->setMethods( ['invalid'] )
			->getMock();

		$object = $this->getMockBuilder( '\Aimeos\Controller\Frontend\Catalog\Decorator\Base' )
			->setConstructorArgs( [$stub, $this->context] )
			->getMockForAbstractClass();

		$stub->expects( $this->once() )->method( 'invalid' )->will( $this->returnValue( true ) );

		$this->assertTrue( $object->invalid() );
	}


	public function testCreateFilter()
	{
		$search = \Aimeos\MShop\Factory::createManager( $this->context, 'catalog' )->createSearch();

		$this->stub->expects( $this->once() )->method( 'createFilter' )
			->will( $this->returnValue( $search ) );

		$this->assertInstanceOf( '\Aimeos\MW\Criteria\Iface', $this->object->createFilter() );
	}


	public function testGetPath()
	{
		$this->stub->expects( $this->once() )->method( 'getPath' )
			->will( $this->returnValue( [] ) );

		$this->assertEquals( [], $this->object->getPath( -1 ) );
	}


	public function testGetTree()
	{
		$catItem = \Aimeos\MShop\Factory::createManager( $this->context, 'catalog' )->createItem();

		$this->stub->expects( $this->once() )->method( 'getTree' )
			->will( $this->returnValue( $catItem ) );

		$this->assertInstanceOf( '\Aimeos\MShop\Catalog\Item\Iface', $this->object->getTree() );
	}


	public function testGetController()
	{
		$result = $this->access( 'getController' )->invokeArgs( $this->object, [] );

		$this->assertSame( $this->stub, $result );
	}


	protected function access( $name )
	{
		$class = new \ReflectionClass( '\Aimeos\Controller\Frontend\Catalog\Decorator\Base' );
		$method = $class->getMethod( $name );
		$method->setAccessible( true );

		return $method;
	}
}
