<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Metaways Infosystems GmbH, 2012
 * @copyright Aimeos (aimeos.org), 2015-2021
 * @package Controller
 * @subpackage Frontend
 */


namespace Aimeos\Controller\Frontend\Basket;


/**
 * \Exception for basket frontend controller classes.
 *
 * @package Controller
 * @subpackage Frontend
 */
class Exception extends \Aimeos\Controller\Frontend\Exception
{
	private $errors;


	/**
	 * Initializes the instance of the exception
	 *
	 * @param string $message Custom error message to describe the error
	 * @param int $code Custom error code to identify or classify the error
	 * @param \Exception|null $previous Previously thrown exception
	 * @param array $errors List of error codes for error handling
	 */
	public function __construct( string $message = '', int $code = 0, \Exception $previous = null, array $errors = [] )
	{
		parent::__construct( $message, $code, $previous );

		$this->errors = $errors;
	}


	/**
	 * Gets the error codes of the exception
	 *
	 * @return array list of error codes
	 */
	public function getErrors() : array
	{
		return $this->errors;
	}
}
