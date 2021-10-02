<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2016-2021
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
	 * @param string|null $supplier Unique supplier ID the product is from
	 * @param string|null $siteid Unique site ID the product is from or null for siteid of the product item
	 * @return \Aimeos\Controller\Frontend\Basket\Iface Basket frontend object for fluent interface
	 * @throws \Aimeos\Controller\Frontend\Basket\Exception If the product isn't available
	 */
	public function addProduct( \Aimeos\MShop\Product\Item\Iface $product,
		float $quantity = 1, array $variant = [], array $config = [], array $custom = [],
		string $stocktype = 'default', string $supplierid = null, string $siteid = null ) : \Aimeos\Controller\Frontend\Basket\Iface
	{
		if( !$this->checkCategory( $product->getId() ) )
		{
			$context = $this->getContext();
			$manager = \Aimeos\MShop::create( $context, 'product' );

			$filter = $manager->filter( true );
			$func = $filter->make( 'product:has', ['product', 'default', $product->getId()] );
			$filter->add( $filter->is( $func, '!=', null ) );

			$prodIds = [$product->getId()];
			foreach( $manager->search( $filter ) as $item ) {
				$prodIds[] = $item->getId();
			}

			if( !$this->checkCategory( $prodIds ) )
			{
				$msg = $context->translate( 'controller/frontend', 'Adding product with ID "%1$s" is not allowed' );
				throw new \Aimeos\Controller\Frontend\Basket\Exception( sprintf( $msg, print_r( $prodIds, true ) ) );
			}
		}

		$this->getController()->addProduct( $product, $quantity, $variant, $config, $custom, $stocktype, $supplierid, $siteid );

		return $this;
	}


	/**
	 * Checks if the given product IDs are assigned to a category
	 *
	 * @param array|string $prodIds Unique product IDs to check for
	 * @return bool True if at least one product ID is assigned to a category, false if not
	 */
	protected function checkCategory( $prodIds ) : bool
	{
		$context = $this->getContext();
		$manager = \Aimeos\MShop::create( $context, 'catalog' );

		$filter = $manager->filter( true )->slice( 0, 1 );
		$func = $filter->make( 'catalog:has', ['product', ['default', 'promotion'], $prodIds] );
		$filter->add( $filter->is( $func, '!=', null ) );

		return !$manager->search( $filter )->isEmpty();
	}
}
