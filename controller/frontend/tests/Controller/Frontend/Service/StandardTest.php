<?php

namespace Aimeos\Controller\Frontend\Service;


/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Metaways Infosystems GmbH, 2012
 * @copyright Aimeos (aimeos.org), 2015-2016
 */
class StandardTest extends \PHPUnit_Framework_TestCase
{
	private $object;
	private $context;
	private static $basket;


	protected function setUp()
	{
		\Aimeos\MShop\Factory::setCache( true );

		$this->context = \TestHelperFrontend::getContext();
		$this->object = new \Aimeos\Controller\Frontend\Service\Standard( $this->context );
	}


	public static function setUpBeforeClass()
	{
		$orderManager = \Aimeos\MShop\Order\Manager\Factory::createManager( \TestHelperFrontend::getContext() );
		$orderBaseMgr = $orderManager->getSubManager( 'base' );
		self::$basket = $orderBaseMgr->createItem();
	}


	protected function tearDown()
	{
		unset( $this->object, $this->context );

		\Aimeos\MShop\Factory::setCache( false );
		\Aimeos\MShop\Factory::clear();
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
		$form = new \Aimeos\MShop\Common\Item\Helper\Form\Standard();
		$item = \Aimeos\MShop\Factory::createManager( $this->context, 'order' )->createItem();
		$serviceId = \Aimeos\MShop\Factory::createManager( $this->context, 'service' )->findItem( 'unitcode' )->getId();

		$provider = $this->getMockBuilder( '\\Aimeos\\MShop\\Service\\Provider\\Delivery\\Standard' )
			->disableOriginalConstructor()
			->setMethods( ['process'] )
			->getMock();

		$manager = $this->getMockBuilder( '\\Aimeos\\MShop\\Service\\Manager\\Standard' )
			->setConstructorArgs( [$this->context] )
			->setMethods( ['getProvider'] )
			->getMock();

		\Aimeos\MShop\Factory::injectManager( $this->context, 'service', $manager );

		$provider->expects( $this->once() )->method( 'process' )->will( $this->returnValue( $form ) );
		$manager->expects( $this->once() )->method( 'getProvider' )->will( $this->returnValue( $provider ) );


		$result = $this->object->process( $item, $serviceId, [], [] );
		$this->assertInstanceOf( '\Aimeos\MShop\Common\Item\Helper\Form\Iface', $result );
	}


	public function testUpdateSync()
	{
		$item = \Aimeos\MShop\Factory::createManager( $this->context, 'order' )->createItem();

		$response = $this->getMockBuilder( '\Psr\Http\Message\ResponseInterface' )->getMock();
		$request = $this->getMockBuilder( '\Psr\Http\Message\ServerRequestInterface' )->getMock();

		$provider = $this->getMockBuilder( '\\Aimeos\\MShop\\Service\\Provider\\Delivery\\Standard' )
			->setMethods( ['updateSync', 'query', 'isImplemented'] )
			->disableOriginalConstructor()
			->getMock();

		$manager = $this->getMockBuilder( '\\Aimeos\\MShop\\Service\\Manager\\Standard' )
			->setConstructorArgs( array( $this->context ) )
			->setMethods( ['getProvider'] )
			->getMock();

		\Aimeos\MShop\Factory::injectManager( $this->context, 'service', $manager );


		$request->expects( $this->once() )->method( 'getQueryParams' )->will( $this->returnValue( ['code' => 'unitcode'] ) );
		$manager->expects( $this->once() )->method( 'getProvider' )->will( $this->returnValue( $provider ) );
		$provider->expects( $this->once() )->method( 'updateSync' )->will( $this->returnValue( $item ) );
		$provider->expects( $this->once() )->method( 'isImplemented' )->will( $this->returnValue( true ) );
		$provider->expects( $this->once() )->method( 'query' );

		$this->object->updateSync( $request, $response, [] );
	}


	public function testGetServices()
	{
		$orderManager = \Aimeos\MShop\Order\Manager\Factory::createManager( \TestHelperFrontend::getContext() );
		$basket = $orderManager->getSubManager( 'base' )->createItem();

		$services = $this->object->getServices( 'delivery', $basket );
		$this->assertGreaterThan( 0, count( $services ) );

		foreach( $services as $service ) {
			$this->assertInstanceOf( '\\Aimeos\\MShop\\Service\\Item\\Iface', $service );
		}
	}


	public function testGetServiceAttributes()
	{
		$service = $this->getServiceItem();
		$attributes = $this->object->getServiceAttributes( 'delivery', $service->getId(), self::$basket );

		$this->assertEquals( 0, count( $attributes ) );
	}


	public function testGetServiceAttributesCache()
	{
		$orderManager = \Aimeos\MShop\Order\Manager\Factory::createManager( \TestHelperFrontend::getContext() );
		$basket = $orderManager->getSubManager( 'base' )->createItem();

		$services = $this->object->getServices( 'delivery', $basket );

		if( ( $service = reset( $services ) ) === false ) {
			throw new \RuntimeException( 'No service item found' );
		}

		$attributes = $this->object->getServiceAttributes( 'delivery', $service->getId(), self::$basket );

		$this->assertEquals( 0, count( $attributes ) );
	}


	public function testGetServiceAttributesNoItems()
	{
		$this->setExpectedException( '\\Aimeos\\MShop\\Exception' );
		$this->object->getServiceAttributes( 'invalid', -1, self::$basket );
	}


	public function testGetServicePrice()
	{
		$orderManager = \Aimeos\MShop\Order\Manager\Factory::createManager( \TestHelperFrontend::getContext() );
		$basket = $orderManager->getSubManager( 'base' )->createItem();

		$service = $this->getServiceItem();
		$price = $this->object->getServicePrice( 'delivery', $service->getId(), $basket );

		$this->assertEquals( '12.95', $price->getValue() );
		$this->assertEquals( '1.99', $price->getCosts() );
	}


	public function testGetServicePriceCache()
	{
		$orderManager = \Aimeos\MShop\Order\Manager\Factory::createManager( \TestHelperFrontend::getContext() );
		$basket = $orderManager->getSubManager( 'base' )->createItem();

		$services = $this->object->getServices( 'delivery', $basket );

		if( ( $service = reset( $services ) ) === false ) {
			throw new \RuntimeException( 'No service item found' );
		}

		$price = $this->object->getServicePrice( 'delivery', $service->getId(), $basket );

		$this->assertEquals( '12.95', $price->getValue() );
		$this->assertEquals( '1.99', $price->getCosts() );
	}


	public function testGetServicePriceNoItems()
	{
		$orderManager = \Aimeos\MShop\Order\Manager\Factory::createManager( \TestHelperFrontend::getContext() );
		$basket = $orderManager->getSubManager( 'base' )->createItem();

		$this->setExpectedException( '\\Aimeos\\MShop\\Exception' );
		$this->object->getServicePrice( 'invalid', -1, $basket );
	}


	public function testCheckServiceAttributes()
	{
		$service = $this->getServiceItem();
		$attributes = $this->object->checkServiceAttributes( 'delivery', $service->getId(), [] );

		$this->assertEquals( [], $attributes );
	}


	/**
	 * @return \Aimeos\MShop\Service\Item\Iface
	 */
	protected function getServiceItem()
	{
		$manager = \Aimeos\MShop\Service\Manager\Factory::createManager( \TestHelperFrontend::getContext() );
		return $manager->findItem( 'unitcode' );
	}
}
