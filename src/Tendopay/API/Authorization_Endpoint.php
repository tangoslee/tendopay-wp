<?php
/**
 * Created by PhpStorm.
 * User: robert
 * Date: 04.08.2018
 * Time: 06:21
 */

namespace Tendopay\API;

use Tendopay\Exceptions\TendoPay_Integration_Exception;

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Class Authorization_Endpoint
 * @package Tendopay\API
 */
class Authorization_Endpoint {
	/** @var int order_id */
	private $order_id;
	/**
	 * @var string
	 */
	private $order_key;
	/**
	 * @var float
	 */
	private $amount;

	/**
	 * Authorization_Endpoint constructor.
	 *
	 * @param \WC_Order $order
	 */
	public function __construct( \WC_Order $order ) {
		$this->order_id  = $order->get_id();
		$this->order_key = $order->get_order_key();
		$this->amount    = $order->get_total();
	}

	/**
	 * @throws TendoPay_Integration_Exception if response code from authorization endpoint is not 200 or empty body
	 * @throws \GuzzleHttp\Exception\GuzzleException
	 */
	public function request_token() {
		$caller   = new Endpoint_Caller();
		$response = $caller->do_call( Tendopay_API::get_authorization_endpoint_uri(), [
			'amount'               => (int) $this->amount,
			'customer_reference_1' => (string) $this->order_id,
			'customer_reference_2' => (string) $this->order_key
		] );

		if ( $response->get_code() !== 200 || empty( $response->get_body() ) ) {
			throw new TendoPay_Integration_Exception( __( 'Could not communicate with TendoPay', 'tendopay' ) );
		}

		return trim( (string) $response->get_body(), "\"" );
	}
}
