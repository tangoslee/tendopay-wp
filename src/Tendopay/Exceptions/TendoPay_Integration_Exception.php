<?php
/**
 * Created by PhpStorm.
 * User: robert
 * Date: 04.08.2018
 * Time: 13:25
 */

namespace TendoPay\Exceptions;


if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Class TendoPay_Integration_Exception
 * @package TendoPay\Exceptions
 */
class TendoPay_Integration_Exception extends \Exception {
	/**
	 * TendoPay_Integration_Exception constructor.
	 */
	public function __construct( $message, \Throwable $previous = null ) {
		parent::__construct( $message, 0, $previous );
	}
}
