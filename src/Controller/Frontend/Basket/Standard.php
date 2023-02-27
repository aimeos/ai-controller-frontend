<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Metaways Infosystems GmbH, 2012
 * @copyright Aimeos (aimeos.org), 2015-2023
 * @package Controller
 * @subpackage Frontend
 */


namespace Aimeos\Controller\Frontend\Basket;


/**
 * Default implementation of the basket frontend controller.
 *
 * @package Controller
 * @subpackage Frontend
 */
class Standard
	extends Base
	implements Iface, \Aimeos\Controller\Frontend\Common\Iface
{
	/** controller/frontend/basket/name
	 * Class name of the used basket frontend controller implementation
	 *
	 * Each default frontend controller can be replace by an alternative imlementation.
	 * To use this implementation, you have to set the last part of the class
	 * name as configuration value so the controller factory knows which class it
	 * has to instantiate.
	 *
	 * For example, if the name of the default class is
	 *
	 *  \Aimeos\Controller\Frontend\Basket\Standard
	 *
	 * and you want to replace it with your own version named
	 *
	 *  \Aimeos\Controller\Frontend\Basket\Mybasket
	 *
	 * then you have to set the this configuration option:
	 *
	 *  controller/jobs/frontend/basket/name = Mybasket
	 *
	 * The value is the last part of your own class name and it's case sensitive,
	 * so take care that the configuration value is exactly named like the last
	 * part of the class name.
	 *
	 * The allowed characters of the class name are A-Z, a-z and 0-9. No other
	 * characters are possible! You should always start the last part of the class
	 * name with an upper case character and continue only with lower case characters
	 * or numbers. Avoid chamel case names like "MyBasket"!
	 *
	 * @param string Last part of the class name
	 * @since 2014.03
	 * @category Developer
	 */

	/** controller/frontend/basket/decorators/excludes
	 * Excludes decorators added by the "common" option from the basket frontend controllers
	 *
	 * Decorators extend the functionality of a class by adding new aspects
	 * (e.g. log what is currently done), executing the methods of the underlying
	 * class only in certain conditions (e.g. only for logged in users) or
	 * modify what is returned to the caller.
	 *
	 * This option allows you to remove a decorator added via
	 * "controller/frontend/common/decorators/default" before they are wrapped
	 * around the frontend controller.
	 *
	 *  controller/frontend/basket/decorators/excludes = array( 'decorator1' )
	 *
	 * This would remove the decorator named "decorator1" from the list of
	 * common decorators ("\Aimeos\Controller\Frontend\Common\Decorator\*") added via
	 * "controller/frontend/common/decorators/default" for the basket frontend controller.
	 *
	 * @param array List of decorator names
	 * @since 2014.03
	 * @category Developer
	 * @see controller/frontend/common/decorators/default
	 * @see controller/frontend/basket/decorators/global
	 * @see controller/frontend/basket/decorators/local
	 */

	/** controller/frontend/basket/decorators/global
	 * Adds a list of globally available decorators only to the basket frontend controllers
	 *
	 * Decorators extend the functionality of a class by adding new aspects
	 * (e.g. log what is currently done), executing the methods of the underlying
	 * class only in certain conditions (e.g. only for logged in users) or
	 * modify what is returned to the caller.
	 *
	 * This option allows you to wrap global decorators
	 * ("\Aimeos\Controller\Frontend\Common\Decorator\*") around the frontend controller.
	 *
	 *  controller/frontend/basket/decorators/global = array( 'decorator1' )
	 *
	 * This would add the decorator named "decorator1" defined by
	 * "\Aimeos\Controller\Frontend\Common\Decorator\Decorator1" only to the frontend controller.
	 *
	 * @param array List of decorator names
	 * @since 2014.03
	 * @category Developer
	 * @see controller/frontend/common/decorators/default
	 * @see controller/frontend/basket/decorators/excludes
	 * @see controller/frontend/basket/decorators/local
	 */

	/** controller/frontend/basket/decorators/local
	 * Adds a list of local decorators only to the basket frontend controllers
	 *
	 * Decorators extend the functionality of a class by adding new aspects
	 * (e.g. log what is currently done), executing the methods of the underlying
	 * class only in certain conditions (e.g. only for logged in users) or
	 * modify what is returned to the caller.
	 *
	 * This option allows you to wrap local decorators
	 * ("\Aimeos\Controller\Frontend\Basket\Decorator\*") around the frontend controller.
	 *
	 *  controller/frontend/basket/decorators/local = array( 'decorator2' )
	 *
	 * This would add the decorator named "decorator2" defined by
	 * "\Aimeos\Controller\Frontend\Basket\Decorator\Decorator2" only to the frontend
	 * controller.
	 *
	 * @param array List of decorator names
	 * @since 2014.03
	 * @category Developer
	 * @see controller/frontend/common/decorators/default
	 * @see controller/frontend/basket/decorators/excludes
	 * @see controller/frontend/basket/decorators/global
	 */

	private \Aimeos\MShop\Common\Manager\Iface $manager;
	private string $type = 'default';
	private array $baskets = [];


	/**
	 * Initializes the frontend controller.
	 *
	 * @param \Aimeos\MShop\ContextIface $context Object storing the required instances for manaing databases
	 *  connections, logger, session, etc.
	 */
	public function __construct( \Aimeos\MShop\ContextIface $context )
	{
		parent::__construct( $context );

		$this->manager = \Aimeos\MShop::create( $context, 'order' );
	}


	/**
	 * Adds values like comments to the basket
	 *
	 * @param array $values Order values like comment
	 * @return \Aimeos\Controller\Frontend\Basket\Iface Basket frontend object for fluent interface
	 */
	public function add( array $values ) : Iface
	{
		$this->baskets[$this->type] = $this->get()->fromArray( $values );
		return $this;
	}


	/**
	 * Empties the basket and removing all products, addresses, services, etc.
	 *
	 * @return \Aimeos\Controller\Frontend\Basket\Iface Basket frontend object for fluent interface
	 */
	public function clear() : Iface
	{
		$this->baskets[$this->type] = $this->manager->create();
		$this->manager->setSession( $this->baskets[$this->type], $this->type );

		return $this;
	}


	/**
	 * Returns the basket object.
	 *
	 * @return \Aimeos\MShop\Order\Item\Iface Basket holding products, addresses and delivery/payment options
	 */
	public function get() : \Aimeos\MShop\Order\Item\Iface
	{
		if( !isset( $this->baskets[$this->type] ) )
		{
			$this->baskets[$this->type] = $this->manager->getSession( $this->type );
			$this->checkLocale( $this->baskets[$this->type]->locale(), $this->type );
			$this->baskets[$this->type]->setCustomerId( (string) $this->context()->user() );
		}

		return $this->baskets[$this->type];
	}


	/**
	 * Explicitely persists the basket content
	 *
	 * @return \Aimeos\Controller\Frontend\Basket\Iface Basket frontend object for fluent interface
	 */
	public function save() : Iface
	{
		if( isset( $this->baskets[$this->type] ) && $this->baskets[$this->type]->isModified() ) {
			$this->manager->setSession( $this->baskets[$this->type], $this->type );
		}

		return $this;
	}


	/**
	 * Sets the new basket type
	 *
	 * @param string $type Basket type
	 * @return \Aimeos\Controller\Frontend\Basket\Iface Basket frontend object for fluent interface
	 */
	public function setType( string $type ) : Iface
	{
		$this->type = $type;
		return $this;
	}


	/**
	 * Creates a new order object from the current basket
	 *
	 * @return \Aimeos\MShop\Order\Item\Iface Order object including products, addresses and services
	 */
	public function store() : \Aimeos\MShop\Order\Item\Iface
	{
		$total = 0;
		$context = $this->context();
		$config = $context->config();

		/** controller/frontend/basket/limit-count
		 * Maximum number of orders within the time frame
		 *
		 * Creating new orders is limited to avoid abuse and mitigate denial of
		 * service attacks. The number of orders created within the time frame
		 * configured by "controller/frontend/basket/limit-seconds" are counted
		 * before a new order of the same user (either logged in or identified
		 * by the IP address) is created. If the number of orders is higher than
		 * the configured value, an error message will be shown to the user
		 * instead of creating a new order.
		 *
		 * @param integer Number of orders allowed within the time frame
		 * @since 2017.05
		 * @category Developer
		 * @see controller/frontend/basket/limit-seconds
		 */
		$count = $config->get( 'controller/frontend/basket/limit-count', 5 );

		/** controller/frontend/basket/limit-seconds
		 * Order limitation time frame in seconds
		 *
		 * Creating new orders is limited to avoid abuse and mitigate denial of
		 * service attacks. Within the configured time frame, only a limited
		 * number of orders can be created. All orders of the current user
		 * (either logged in or identified by the IP address) within the last X
		 * seconds are counted. If the total value is higher then the number
		 * configured in "controller/frontend/basket/limit-count", an error
		 * message will be shown to the user instead of creating a new order.
		 *
		 * @param integer Number of seconds to check orders within
		 * @since 2017.05
		 * @category Developer
		 * @see controller/frontend/basket/limit-count
		 */
		$seconds = $config->get( 'controller/frontend/basket/limit-seconds', 900 );

		$search = $this->manager->filter()->slice( 0, 0 );
		$expr = [
			$search->compare( '==', 'order.editor', $context->editor() ),
			$search->compare( '>=', 'order.ctime', date( 'Y-m-d H:i:s', time() - $seconds ) ),
		];
		$search->setConditions( $search->and( $expr ) );

		$this->manager->search( $search, [], $total );

		if( $total >= $count )
		{
			$msg = $context->translate( 'controller/frontend', 'Temporary order limit reached' );
			throw new \Aimeos\Controller\Frontend\Basket\Exception( $msg );
		}


		$basket = $this->get()->setCustomerId( (string) $context->user() )->finish()->check();

		$this->manager->begin();
		$this->manager->save( $basket );
		$this->manager->commit();

		$this->save(); // for reusing unpaid orders, might have side effects (!)
		$this->createSubscriptions( $basket );

		return $basket;
	}


	/**
	 * Returns the order object for the given ID
	 *
	 * @param string $id Unique ID of the order object
	 * @param array $ref References items that should be fetched too
	 * @param bool $default True to add default criteria (user logged in), false if not
	 * @return \Aimeos\MShop\Order\Item\Iface Order object including the given parts
	 */
	public function load( string $id, array $ref = ['order/address', 'order/coupon', 'order/product', 'order/service'],
		bool $default = true ) : \Aimeos\MShop\Order\Item\Iface
	{
		return $this->manager->get( $id, $ref, $default );
	}


	/**
	 * Adds a product to the basket of the customer stored in the session
	 *
	 * @param \Aimeos\MShop\Product\Item\Iface $product Product to add including texts, media, prices, attributes, etc.
	 * @param float $quantity Amount of products that should by added
	 * @param array $variant List of variant-building attribute IDs that identify an article in a selection product
	 * @param array $config List of configurable attribute IDs the customer has chosen from
	 * @param array $custom Associative list of attribute IDs as keys and arbitrary values that will be added to the ordered product
	 * @param string $stocktype Unique code of the stock type to deliver the products from
	 * @param string|null $siteId Unique ID of the site the product should be bought from or NULL for site the product is from
	 * @return \Aimeos\Controller\Frontend\Basket\Iface Basket frontend object for fluent interface
	 * @throws \Aimeos\Controller\Frontend\Basket\Exception If the product isn't available
	 */
	public function addProduct( \Aimeos\MShop\Product\Item\Iface $product, float $quantity = 1,
		array $variant = [], array $config = [], array $custom = [], string $stocktype = 'default', string $siteId = null ) : Iface
	{
		$quantity = $this->call( 'checkQuantity', $product, $quantity );
		$this->call( 'checkAttributes', [$product], 'custom', array_keys( $custom ) );
		$this->call( 'checkAttributes', [$product], 'config', array_keys( $config ) );

		$prices = $product->getRefItems( 'price', 'default', 'default' );
		$hidden = $product->getRefItems( 'attribute', null, 'hidden' );

		$custAttr = $this->call( 'getOrderProductAttributes', 'custom', array_keys( $custom ), $custom );
		$confAttr = $this->call( 'getOrderProductAttributes', 'config', array_keys( $config ), [], $config );
		$hideAttr = $this->call( 'getOrderProductAttributes', 'hidden', $hidden->keys()->toArray() );

		$orderBaseProductItem = \Aimeos\MShop::create( $this->context(), 'order/product' )
			->create()
			->copyFrom( $product )
			->setQuantity( $quantity )
			->setStockType( $stocktype )
			->setSiteId( $this->call( 'getSiteId', $product, $siteId ) )
			->setAttributeItems( array_merge( $custAttr, $confAttr, $hideAttr ) );

		$orderBaseProductItem->setPrice( $this->call( 'calcPrice', $orderBaseProductItem, $prices, $quantity ) );

		$this->baskets[$this->type] = $this->get()->addProduct( $orderBaseProductItem );
		return $this->save();
	}


	/**
	 * Deletes a product item from the basket.
	 *
	 * @param int $position Position number (key) of the order product item
	 * @return \Aimeos\Controller\Frontend\Basket\Iface Basket frontend object for fluent interface
	 */
	public function deleteProduct( int $position ) : Iface
	{
		$product = $this->get()->getProduct( $position );

		if( $product->getFlags() === \Aimeos\MShop\Order\Item\Product\Base::FLAG_IMMUTABLE )
		{
			$msg = $this->context()->translate( 'controller/frontend', 'Basket item at position "%1$d" cannot be deleted manually' );
			throw new \Aimeos\Controller\Frontend\Basket\Exception( sprintf( $msg, $position ) );
		}

		$this->baskets[$this->type] = $this->get()->deleteProduct( $position );
		return $this->save();
	}


	/**
	 * Edits the quantity of a product item in the basket.
	 *
	 * @param int $position Position number (key) of the order product item
	 * @param float $quantity New quantiy of the product item
	 * @return \Aimeos\Controller\Frontend\Basket\Iface Basket frontend object for fluent interface
	 */
	public function updateProduct( int $position, float $quantity ) : Iface
	{
		$context = $this->context();
		$orderProduct = $this->get()->getProduct( $position );

		if( $orderProduct->getFlags() & \Aimeos\MShop\Order\Item\Product\Base::FLAG_IMMUTABLE )
		{
			$msg = $context->translate( 'controller/frontend', 'Basket item at position "%1$d" cannot be changed' );
			throw new \Aimeos\Controller\Frontend\Basket\Exception( sprintf( $msg, $position ) );
		}

		$manager = \Aimeos\MShop::create( $context, 'product' );
		$product = $manager->get( $orderProduct->getProductId(), ['attribute', 'catalog', 'price', 'text'], true );
		$product = \Aimeos\MShop::create( $context, 'rule' )->apply( $product, 'catalog' );

		$quantity = $this->call( 'checkQuantity', $product, $quantity );
		$price = $this->call( 'calcPrice', $orderProduct, $product->getRefItems( 'price', 'default', 'default' ), $quantity );
		$orderProduct = $orderProduct->setQuantity( $quantity )->setPrice( $price );

		$this->baskets[$this->type] = $this->get()->addProduct( $orderProduct, $position );
		return $this->save();
	}


	/**
	 * Adds the given coupon code and updates the basket.
	 *
	 * @param string $code Coupon code entered by the user
	 * @return \Aimeos\Controller\Frontend\Basket\Iface Basket frontend object for fluent interface
	 * @throws \Aimeos\Controller\Frontend\Basket\Exception if the coupon code is invalid or not allowed
	 */
	public function addCoupon( string $code ) : Iface
	{
		$context = $this->context();

		/** controller/frontend/basket/coupon/allowed
		 * Number of coupon codes a customer is allowed to enter
		 *
		 * This configuration option enables shop owners to limit the number of coupon
		 * codes that can be added by a customer to his current basket. By default, only
		 * one coupon code is allowed per order.
		 *
		 * Coupon codes are valid until a payed order is placed by the customer. The
		 * "count" of the codes is decreased afterwards. If codes are not personalized
		 * the codes can be reused in the next order until their "count" reaches zero.
		 *
		 * @param integer Positive number of coupon codes including zero
		 * @since 2017.08
		 * @category User
		 * @category Developer
		 */
		$allowed = $context->config()->get( 'controller/frontend/basket/coupon/allowed', 1 );

		if( $allowed <= count( $this->get()->getCoupons() ) )
		{
			$msg = $context->translate( 'controller/frontend', 'Number of coupon codes exceeds the limit' );
			throw new \Aimeos\Controller\Frontend\Basket\Exception( $msg );
		}

		$this->baskets[$this->type] = $this->get()->addCoupon( $code );
		return $this->save();
	}


	/**
	 * Removes the given coupon code and its effects from the basket.
	 *
	 * @param string $code Coupon code entered by the user
	 * @return \Aimeos\Controller\Frontend\Basket\Iface Basket frontend object for fluent interface
	 * @throws \Aimeos\Controller\Frontend\Basket\Exception if the coupon code is invalid
	 */
	public function deleteCoupon( string $code ) : Iface
	{
		$this->baskets[$this->type] = $this->get()->deleteCoupon( $code );
		return $this->save();
	}


	/**
	 * Adds an address of the customer to the basket
	 *
	 * @param string $type Address type code like 'payment' or 'delivery'
	 * @param array $values Associative list of key/value pairs with address details
	 * @param int|null $position Position number (key) of the order address item
	 * @return \Aimeos\Controller\Frontend\Basket\Iface Basket frontend object for fluent interface
	 */
	public function addAddress( string $type, array $values = [], int $position = null ) : Iface
	{
		foreach( $values as $key => $value )
		{
			if( is_scalar( $value ) ) {
				$values[$key] = strip_tags( (string) $value ); // prevent XSS
			}
		}

		$context = $this->context();
		$address = \Aimeos\MShop::create( $context, 'order/address' )->create()->fromArray( $values );
		$address->set( 'nostore', ( $values['nostore'] ?? false ) ? true : false );

		$this->baskets[$this->type] = $this->get()->addAddress( $address, $type, $position );
		return $this->save();
	}


	/**
	 * Removes the address of the given type and position if available
	 *
	 * @param string $type Address type code like 'payment' or 'delivery'
	 * @param int|null $position Position of the address in the list to overwrite
	 * @return \Aimeos\Controller\Frontend\Basket\Iface Basket frontend object for fluent interface
	 */
	public function deleteAddress( string $type, int $position = null ) : Iface
	{
		$this->baskets[$this->type] = $this->get()->deleteAddress( $type, $position );
		return $this->save();
	}


	/**
	 * Adds the delivery/payment service including the given configuration
	 *
	 * @param \Aimeos\MShop\Service\Item\Iface $service Service item selected by the customer
	 * @param array $config Associative list of key/value pairs with the options selected by the customer
	 * @param int|null $position Position of the address in the list to overwrite
	 * @return \Aimeos\Controller\Frontend\Basket\Iface Basket frontend object for fluent interface
	 * @throws \Aimeos\Controller\Frontend\Basket\Exception If given service attributes are invalid
	 */
	public function addService( \Aimeos\MShop\Service\Item\Iface $service, array $config = [], int $position = null ) : Iface
	{
		$basket = $this->get();
		$context = $this->context();

		$type = $service->getType();
		$code = $service->getCode();

		foreach( $basket->getService( $type ) as $pos => $ordService )
		{
			if( $ordService->getCode() === $code ) {
				$position = $pos;
			}
		}

		$manager = \Aimeos\MShop::create( $context, 'service' );
		$provider = $manager->getProvider( $service, $type );

		$errors = $provider->checkConfigFE( $config );
		$unknown = array_diff_key( $config, $errors );

		if( count( $unknown ) > 0 )
		{
			$msg = $context->translate( 'controller/frontend', 'Unknown service attributes' );
			throw new \Aimeos\Controller\Frontend\Basket\Exception( $msg, -1, null, $unknown );
		}

		if( count( array_filter( $errors ) ) > 0 )
		{
			$msg = $context->translate( 'controller/frontend', 'Invalid service attributes' );
			throw new \Aimeos\Controller\Frontend\Basket\Exception( $msg, -1, null, array_filter( $errors ) );
		}

		// remove service rebate of original price
		$price = $provider->calcPrice( $this->get(), $config )->setRebate( '0.00' );

		$orderBaseServiceManager = \Aimeos\MShop::create( $context, 'order/service' );

		$orderServiceItem = $orderBaseServiceManager->create()->copyFrom( $service )->setPrice( $price );
		$orderServiceItem = $provider->setConfigFE( $orderServiceItem, $config );

		$this->baskets[$this->type] = $basket->addService( $orderServiceItem, $type, $position );
		return $this->save();
	}


	/**
	 * Removes the delivery or payment service items from the basket
	 *
	 * @param string $type Service type code like 'payment' or 'delivery'
	 * @param int|null $position Position of the address in the list to overwrite
	 * @return \Aimeos\Controller\Frontend\Basket\Iface Basket frontend object for fluent interface
	 */
	public function deleteService( string $type, int $position = null ) : Iface
	{
		$this->baskets[$this->type] = $this->get()->deleteService( $type, $position );
		return $this->save();
	}


	/**
	 * Returns the manager used by the controller
	 *
	 * @return \Aimeos\MShop\Common\Manager\Iface Manager object
	 */
	protected function getManager() : \Aimeos\MShop\Common\Manager\Iface
	{
		return $this->manager;
	}
}
