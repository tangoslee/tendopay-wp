<?php
/**
 * Created by PhpStorm.
 * User: robert
 * Date: 04.08.2018
 * Time: 13:27
 */

namespace Tendopay\API;


if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Class Response
 * @package Tendopay\API
 */
class Response {
	/**
	 * @var
	 */
	private $body;
	/**
	 * @var
	 */
	private $code;

	/**
	 * Response constructor.
	 *
	 * @param $body
	 * @param $code
	 */
	public function __construct( $code, $body ) {
		$this->body = $body;
		$this->code = $code;
	}

	/**
	 * Returns body of the response from TendoPay API
	 *
	 * @return mixed
	 */
	public function get_body() {
		return $this->body;
	}

	/**
	 * Returns response code from TendoPay API
	 *
	 * @return mixed
	 */
	public function get_code() {
		return $this->code;
	}
}
