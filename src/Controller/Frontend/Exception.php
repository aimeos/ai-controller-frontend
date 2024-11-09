<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Metaways Infosystems GmbH, 2012
 * @copyright Aimeos (aimeos.org), 2015-2024
 * @package Controller
 * @subpackage Frontend
 */


namespace Aimeos\Controller\Frontend;


/**
 * Common exception for frontend controller classes.
 *
 * @package Controller
 * @subpackage Frontend
 */
class Exception
	extends \Exception
{
	private array $list;


	/**
	 * Initializes the exception.
	 *
	 * @param string $msg The exception message
	 * @param int $code The exception code
	 * @param \Exception $previous The previous exception used for the exception chaining.
	 * @param array $list The associative list of errors and their messages when several errors occured
	 */
	public function __construct( string $msg = '', int $code = 0, ?\Exception $previous = null, array $list = [] )
	{
		parent::__construct( $msg, $code, $previous );

		$this->list = $list;
	}


	/**
	 * Returns the list of error messages.
	 *
	 * @return array Associative list of keys and their error messages
	 */
	public function getErrorList() : array
	{
		return $this->list;
	}
}
