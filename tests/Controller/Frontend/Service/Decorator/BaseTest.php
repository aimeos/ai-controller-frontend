<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2017-2024
 */


namespace Aimeos\Controller\Frontend\Service\Decorator;


class Example extends Base
{
}


class BaseTest extends \PHPUnit\Framework\TestCase
{
	private $context;
	private $object;
	private $stub;


	protected function setUp() : void
	{
		$this->context = \TestHelper::context();

		$this->stub = $this->getMockBuilder( \Aimeos\Controller\Frontend\Service\Standard::class )
			->disableOriginalConstructor()
			->getMock();

		$this->object = new \Aimeos\Controller\Frontend\Service\Decorator\Example( $this->stub, $this->context );
	}


	protected function tearDown() : void
	{
		unset( $this->context, $this->object, $this->stub );
	}


	public function testCall()
	{
		$stub = $this->getMockBuilder( \Aimeos\Controller\Frontend\Service\Standard::class )
			->disableOriginalConstructor()
			->onlyMethods( ['__call'] )
			->getMock();

		$object = new \Aimeos\Controller\Frontend\Service\Decorator\Example( $stub, $this->context );

		$stub->expects( $this->once() )->method( '__call' )->willReturn( true );

		$this->assertTrue( $object->invalid() );
	}


	public function testCompare()
	{
		$this->assertSame( $this->object, $this->object->compare( '==', 'service.type', 'delivery' ) );
	}


	public function testFind()
	{
		$item = \Aimeos\MShop::create( $this->context, 'service' )->create();

		$this->stub->expects( $this->once() )->method( 'find' )
			->willReturn( $item );

		$this->assertSame( $item, $this->object->find( 'test' ) );
	}


	public function testFunction()
	{
		$this->stub->expects( $this->once() )->method( 'function' )
			->willReturn( 'service:has("domain","type","refid")' );

		$str = $this->object->function( 'service:has', ['domain', 'type', 'refid'] );
		$this->assertEquals( 'service:has("domain","type","refid")', $str );
	}


	public function testGet()
	{
		$item = \Aimeos\MShop::create( $this->context, 'service' )->create();

		$this->stub->expects( $this->once() )->method( 'get' )
			->willReturn( $item );

		$this->assertSame( $item, $this->object->get( -1 ) );
	}


	public function testGetProvider()
	{
		$manager = \Aimeos\MShop::create( $this->context, 'service' );
		$provider = $manager->getProvider( $manager->find( 'unitdeliverycode', [], 'service', 'delivery' ), 'delivery' );

		$this->stub->expects( $this->once() )->method( 'getProvider' )
			->willReturn( $provider );

		$this->assertSame( $provider, $this->object->getProvider( -1 ) );
	}


	public function testGetProviders()
	{
		$this->stub->expects( $this->once() )->method( 'getProviders' )
			->willReturn( map() );

		$this->assertEquals( [], $this->object->getProviders( 'payment' )->toArray() );
	}


	public function testParse()
	{
		$this->assertSame( $this->object, $this->object->parse( [] ) );
	}


	public function testProcess()
	{
		$item = \Aimeos\MShop::create( $this->context, 'order' )->create();

		$this->stub->expects( $this->once() )->method( 'process' )
			->willReturn( new \Aimeos\MShop\Common\Helper\Form\Standard() );

		$this->assertInstanceOf( 'Aimeos\MShop\Common\Helper\Form\Iface', $this->object->process( $item, -1, [], [] ) );
	}


	public function testSearch()
	{
		$total = 0;
		$item = \Aimeos\MShop::create( $this->context, 'service' )->create();

		$this->stub->expects( $this->once() )->method( 'search' )
			->willReturn( map( [$item] ) );

		$this->assertEquals( [$item], $this->object->search( $total )->toArray() );
	}


	public function testSlice()
	{
		$this->assertSame( $this->object, $this->object->slice( 0, 100 ) );
	}


	public function testSort()
	{
		$this->assertSame( $this->object, $this->object->sort( 'type' ) );
	}


	public function testUpdatePush()
	{
		$response = $this->getMockBuilder( \Psr\Http\Message\ResponseInterface::class )->getMock();
		$request = $this->getMockBuilder( \Psr\Http\Message\ServerRequestInterface::class )->getMock();

		$this->stub->expects( $this->once() )->method( 'updatePush' )
			->willReturn( $response );

		$this->assertInstanceOf( \Psr\Http\Message\ResponseInterface::class, $this->object->updatePush( $request, $response, 'test' ) );
	}


	public function testUpdateSync()
	{
		$request = $this->getMockBuilder( \Psr\Http\Message\ServerRequestInterface::class )->getMock();
		$item = \Aimeos\MShop::create( $this->context, 'order' )->create();

		$this->stub->expects( $this->once() )->method( 'updateSync' )
			->willReturn( $item );

		$this->assertInstanceOf( 'Aimeos\MShop\Order\Item\Iface', $this->object->updateSync( $request, 'test', -1 ) );
	}


	public function testUses()
	{
		$this->assertSame( $this->object, $this->object->uses( ['text'] ) );
	}


	public function testGetController()
	{
		$this->assertSame( $this->stub, $this->access( 'getController' )->invokeArgs( $this->object, [] ) );
	}


	protected function access( $name )
	{
		$class = new \ReflectionClass( \Aimeos\Controller\Frontend\Service\Decorator\Base::class );
		$method = $class->getMethod( $name );
		$method->setAccessible( true );

		return $method;
	}
}
