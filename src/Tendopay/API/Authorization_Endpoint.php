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
 * This class is responsible for communication with the Authorization Endpoint of TendoPay API.
 *
 * @package Tendopay\API
 */
class Authorization_Endpoint {
	/**
	 * Initiates single payment attempt by sending basic information about the order to the TendoPay and returns
	 * the authentication token.
	 *
	 * @param \WC_Order $order the order for which we want to initiate the payment
	 *
	 * @return string authentication token
	 *
	 * @throws TendoPay_Integration_Exception if response code from authorization endpoint is not 200 or has empty body
	 * @throws \GuzzleHttp\Exception\GuzzleException when there was a problem in communication with the API (originally
	 * thrown by guzzle http client)
	 */
	public static function request_token( \WC_Order $order ) {
		$caller   = new Endpoint_Caller();
		$response = $caller->do_call( Tendopay_API::get_authorization_endpoint_uri(), [
			'amount'               => (int) $order->get_total(),
			'customer_reference_1' => (string) $order->get_id(),
			'customer_reference_2' => $order->get_order_key()
		] );

		if ( $response->get_code() !== 200 || empty( $response->get_body() ) ) {
			throw new TendoPay_Integration_Exception( __( 'Could not communicate with TendoPay', 'tendopay' ) );
		}

		return trim( (string) $response->get_body(), "\"" );
	}
}
