<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Metaways Infosystems GmbH, 2012
 * @copyright Aimeos (aimeos.org), 2015-2016
 * @package Controller
 * @subpackage Frontend
 */


namespace Aimeos\Controller\Frontend\Service;


/**
 * Interface for service frontend controllers.
 *
 * @package Controller
 * @subpackage Frontend
 */
interface Iface
{
	/**
	 * Returns a list of attributes that are invalid.
	 *
	 * @param string $serviceId Identifier of the service option chosen by the customer
	 * @param array $attributes List of key/value pairs with name of the attribute from attribute definition object as
	 * 	key and the string entered by the customer as value
	 * @return array List of key/value pairs of attributes keys and an error message for values that are invalid or
	 * 	missing
	 */
	public function checkAttributes( $serviceId, array $attributes );

	/**
	 * Returns the service item for the given ID
	 *
	 * @param string $id Unique service ID
	 * @param array $ref List of domains for which the items referenced by the services should be fetched too
	 * @return \Aimeos\MShop\Service\Item\Iface Service item object
	 */
	public function getProvider( $serviceId, $ref = ['media', 'price', 'text'] );

	/**
	 * Returns the service providers for the given
	 *
	 * @param string $type Service type, e.g. "delivery" (shipping related) or "payment" (payment related)
	 * @param string $serviceId Identifier of one of the service option returned by getService()
	 * @param \Aimeos\MShop\Order\Item\Base\Iface $basket Basket object
	 * @return array List of attribute definitions implementing \Aimeos\MW\Criteria\Attribute\Iface
	 */
	public function getProviders( $type, $ref = ['media', 'price', 'text'] );
}
