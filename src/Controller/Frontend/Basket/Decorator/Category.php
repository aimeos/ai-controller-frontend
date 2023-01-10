<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2016-2023
 * @package Controller
 * @subpackage Frontend
 */


namespace Aimeos\Controller\Frontend\Basket\Decorator;


/**
 * Category check for basket controller
 *
 * @package Controller
 * @subpackage Frontend
 */
class Category
	extends Base
	implements \Aimeos\Controller\Frontend\Basket\Iface, \Aimeos\Controller\Frontend\Common\Decorator\Iface
{
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
		array $variant = [], array $config = [], array $custom = [], string $stocktype = 'default', string $siteId = null
	) : \Aimeos\Controller\Frontend\Basket\Iface
	{
		if( $product->getListItems( 'catalog' )->isEmpty() )
		{
			$context = $this->context();
			$manager = \Aimeos\MShop::create( $context, 'product' );

			$filter = $manager->filter( true );
			$func = $filter->make( 'product:has', ['product', 'default', $product->getId()] );
			$filter->add( $filter->is( $func, '!=', null ) );

			$prodIds = $manager->search( $filter )->keys()->all();

			if( empty( $prodIds ) || !$this->checkCategory( $prodIds ) )
			{
				$msg = $context->translate( 'controller/frontend', 'Adding product with ID "%1$s" is not allowed' );
				throw new \Aimeos\Controller\Frontend\Basket\Exception( sprintf( $msg, $product->getId() ) );
			}
		}

		$this->getController()->addProduct( $product, $quantity, $variant, $config, $custom, $stocktype, $siteId );

		return $this;
	}


	/**
	 * Checks if the given product IDs are assigned to a category
	 *
	 * @param iterable $prodIds Unique product IDs to check for
	 * @return bool True if at least one product ID is assigned to a category, false if not
	 */
	protected function checkCategory( iterable $prodIds ) : bool
	{
		$manager = \Aimeos\MShop::create( $this->context(), 'product' );

		$filter = $manager->filter( true )->slice( 0, 1 );
		$func = $filter->make( 'product:has', ['catalog'] );
		$filter->add( $func, '!=', null )->add( 'product.id', '==', $prodIds );

		return !$manager->search( $filter )->isEmpty();
	}
}
