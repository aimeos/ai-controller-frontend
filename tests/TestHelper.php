<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2015-2023
 */


class TestHelper
{
	private static $aimeos;
	private static $context;


	public static function bootstrap()
	{
		self::getAimeos();
	}


	public static function context( $site = 'unittest' )
	{
		if( !isset( self::$context[$site] ) ) {
			self::$context[$site] = self::createContext( $site );
		}

		return clone self::$context[$site];
	}


	private static function getAimeos()
	{
		if( !isset( self::$aimeos ) )
		{
			require_once 'Bootstrap.php';
			spl_autoload_register( 'Aimeos\\Bootstrap::autoload' );

			self::$aimeos = new \Aimeos\Bootstrap();
		}

		return self::$aimeos;
	}


	/**
	 * @param string $site
	 */
	private static function createContext( $site )
	{
		$ctx = new \Aimeos\MShop\Context();
		$aimeos = self::getAimeos();


		$paths = $aimeos->getConfigPaths();
		$paths[] = __DIR__ . DIRECTORY_SEPARATOR . 'config';
		$file = __DIR__ . DIRECTORY_SEPARATOR . 'confdoc.ser';

		$conf = new \Aimeos\Base\Config\PHPArray( [], $paths );
		$conf = new \Aimeos\Base\Config\Decorator\Memory( $conf );
		$conf = new \Aimeos\Base\Config\Decorator\Documentor( $conf, $file );
		$ctx->setConfig( $conf );


		$dbm = new \Aimeos\Base\DB\Manager\Standard( $conf->get( 'resource', [] ), 'PDO' );
		$ctx->setDatabaseManager( $dbm );


		$mq = new \Aimeos\Base\MQueue\Manager\Standard( $conf->get( 'resource', [] ) );
		$ctx->setMessageQueueManager( $mq );


		$logger = new \Aimeos\Base\Logger\File( 'unittest.log', \Aimeos\Base\Logger\Iface::DEBUG );
		$ctx->setLogger( $logger );


		$i18n = new \Aimeos\Base\Translation\None( 'de' );
		$ctx->setI18n( array( 'de' => $i18n ) );


		$session = new \Aimeos\Base\Session\None();
		$ctx->setSession( $session );


		$localeManager = \Aimeos\MShop::create( $ctx, 'locale' );
		$locale = $localeManager->bootstrap( $site, '', '', false );
		$ctx->setLocale( $locale );


		return $ctx->setEditor( 'ai-controller-frontend' );
	}
}
