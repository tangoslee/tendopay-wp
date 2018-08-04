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

class Authorization_Endpoint {
	const ENDPOINT_URL = 'https://tendopay.com/payment/authorise';

	/** @var int order_id */
	private $order_id;
	private $order_key;
	private $amount;

	public function __construct( \WC_Order $order ) {
		$this->order_id  = $order->get_id();
		$this->order_key = $order->get_order_key();
		$this->amount    = $order->get_total();

		error_log( "init authorization endpoint: [ {$this->order_id}, {$this->order_key}, {$this->amount} ]" );
	}

	/**
	 * @throws TendoPay_Integration_Exception if response code from authorization endpoint is not 200 or empty body
	 */
	public function request_token() {
		$caller   = new Endpoint_Caller();
		$response = $caller->do_call( self::ENDPOINT_URL, [
			'amount'               => $this->amount,
			'customer_reference_1' => $this->order_id,
			'customer_reference_2' => $this->order_key
		] );

		if ( $response->get_code() !== 200 || empty( $response->get_body() ) ) {
			error_log( 'Code is not 200 or body is empty' );
			throw new TendoPay_Integration_Exception( __( 'Could not communicate with TendoPay', 'tendopay' ) );
		}

		error_log( 'returning response' );

		return $response->get_body();
	}
}