<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Metaways Infosystems GmbH, 2012
 * @copyright Aimeos (aimeos.org), 2015-2018
 */


namespace Aimeos\Controller\Frontend\Service;


class StandardTest extends \PHPUnit\Framework\TestCase
{
	private $object;
	private $context;
	private static $basket;


	protected function setUp()
	{
		\Aimeos\MShop::cache( true );

		$this->context = \TestHelperFrontend::getContext();
		$this->object = new \Aimeos\Controller\Frontend\Service\Standard( $this->context );
	}


	public static function setUpBeforeClass()
	{
		$orderManager = \Aimeos\MShop\Order\Manager\Factory::create( \TestHelperFrontend::getContext() );
		$orderBaseMgr = $orderManager->getSubManager( 'base' );
		self::$basket = $orderBaseMgr->createItem();
	}


	protected function tearDown()
	{
		\Aimeos\MShop::cache( false );
		unset( $this->object, $this->context );
	}


	public function testCheckAttributes()
	{
		$attributes = $this->object->checkAttributes( $this->getServiceItem()->getId(), [] );
		$this->assertEquals( [], $attributes );
	}


	public function testGetProviders()
	{
		$providers = $this->object->getProviders( 'delivery' );
		$this->assertGreaterThan( 0, count( $providers ) );

		foreach( $providers as $provider ) {
			$this->assertInstanceOf( '\\Aimeos\\MShop\\Service\\Provider\\Iface', $provider );
		}
	}


	public function testGetProvider()
	{
		$provider = $this->object->getProvider( $this->getServiceItem()->getId() );
		$this->assertInstanceOf( '\\Aimeos\\MShop\\Service\\Provider\\Iface', $provider );
	}


	public function testProcess()
	{
		$form = new \Aimeos\MShop\Common\Helper\Form\Standard();
		$item = \Aimeos\MShop::create( $this->context, 'order' )->createItem();
		$serviceId = \Aimeos\MShop::create( $this->context, 'service' )->findItem( 'unitcode' )->getId();

		$provider = $this->getMockBuilder( '\\Aimeos\\MShop\\Service\\Provider\\Delivery\\Standard' )
			->disableOriginalConstructor()
			->setMethods( ['process'] )
			->getMock();

		$manager = $this->getMockBuilder( '\\Aimeos\\MShop\\Service\\Manager\\Standard' )
			->setConstructorArgs( [$this->context] )
			->setMethods( ['getProvider'] )
			->getMock();

		\Aimeos\MShop::inject( 'service', $manager );

		$provider->expects( $this->once() )->method( 'process' )->will( $this->returnValue( $form ) );
		$manager->expects( $this->once() )->method( 'getProvider' )->will( $this->returnValue( $provider ) );


		$result = $this->object->process( $item, $serviceId, [], [] );
		$this->assertInstanceOf( \Aimeos\MShop\Common\Helper\Form\Iface::class, $result );
	}


	public function testUpdatePush()
	{
		$request = $this->getMockBuilder( \Psr\Http\Message\ServerRequestInterface::class )->getMock();
		$response = $this->getMockBuilder( \Psr\Http\Message\ResponseInterface::class )->getMock();

		$response->expects( $this->once() )->method( 'withStatus' )->will( $this->returnValue( $response ) );

		$this->assertInstanceOf( \Psr\Http\Message\ResponseInterface::class, $this->object->updatePush( $request, $response, 'unitcode' ) );
	}


	public function testUpdateSync()
	{
		$item = \Aimeos\MShop::create( $this->context, 'order' )->createItem();
		$request = $this->getMockBuilder( \Psr\Http\Message\ServerRequestInterface::class )->getMock();

		$provider = $this->getMockBuilder( '\\Aimeos\\MShop\\Service\\Provider\\Delivery\\Standard' )
			->setMethods( ['updateSync', 'query', 'isImplemented'] )
			->disableOriginalConstructor()
			->getMock();

		$orderManager = $this->getMockBuilder( '\\Aimeos\\MShop\\Order\\Manager\\Standard' )
			->setConstructorArgs( array( $this->context ) )
			->setMethods( ['getItem'] )
			->getMock();

		$serviceManager = $this->getMockBuilder( '\\Aimeos\\MShop\\Service\\Manager\\Standard' )
			->setConstructorArgs( array( $this->context ) )
			->setMethods( ['getProvider'] )
			->getMock();

		\Aimeos\MShop::inject( 'order', $orderManager );
		\Aimeos\MShop::inject( 'service', $serviceManager );


		$orderManager->expects( $this->once() )->method( 'getItem' )->will( $this->returnValue( $item ) );
		$serviceManager->expects( $this->once() )->method( 'getProvider' )->will( $this->returnValue( $provider ) );
		$provider->expects( $this->once() )->method( 'updateSync' )->will( $this->returnValue( $item ) );
		$provider->expects( $this->once() )->method( 'isImplemented' )->will( $this->returnValue( true ) );
		$provider->expects( $this->once() )->method( 'query' );

		$this->object->updateSync( $request, 'unitcode', -1 );
	}


	/**
	 * @return \Aimeos\MShop\Service\Item\Iface
	 */
	protected function getServiceItem()
	{
		$manager = \Aimeos\MShop\Service\Manager\Factory::create( \TestHelperFrontend::getContext() );
		return $manager->findItem( 'unitcode' );
	}
}
