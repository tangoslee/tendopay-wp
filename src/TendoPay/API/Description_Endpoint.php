<?php
/**
 * Created by PhpStorm.
 * User: robert
 * Date: 04.08.2018
 * Time: 06:28
 */

namespace TendoPay\API;


use TendoPay\Constants;
use TendoPay\Exceptions\TendoPay_Integration_Exception;
use TendoPay\Woocommerce_Order_Description_Retriever;

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Handles API calls to the Description Endpoint. It is used to send descriptions of items being paid by TendoPay.
 *
 * @package TendoPay\API
 */
class Description_Endpoint {
	/**
	 * Sends the line items details to the TendoPay. It is used on TP site to properly display checkout items and their
	 * prices.
	 *
	 * @param string $authorization_token authorization token obtained from {@link Authorization_Endpoint}
	 * @param \WC_Order $order order that is going paid.
	 *
	 * @throws \InvalidArgumentException if the order details taken from the order are not returned in form of array
	 *         or object. It should be very rare case as it shouldn't be possible to place order without items.
	 * @throws TendoPay_Integration_Exception if response code from the request from Description Endpoint is different
	 *         than 204
	 * @throws \GuzzleHttp\Exception\GuzzleException when there was a problem in communication with the API (originally
	 *         thrown by guzzle http client)
	 */
	public static function set_description( $authorization_token, \WC_Order $order ) {
		$order_retriever = new Woocommerce_Order_Description_Retriever( $order );
		$order_details   = $order_retriever->get_order_details();

		if ( ! is_array( $order_details ) && ! is_object( $order_details ) ) {
			throw new \InvalidArgumentException(
				__( 'Order details parameter must be either ARRAY or OBJECT', 'tendopay' ) );
		}

		if ( empty( $order_details ) ) {
			// nothing to send to TP, exiting
			return;
		}

		$caller   = new Endpoint_Caller();
		$response = $caller->do_call( Constants::get_description_endpoint_uri(), [
			Constants::AUTH_TOKEN_PARAM => $authorization_token,
			Constants::ORDER_ID_PARAM   => (string) $order->get_id(),
			Constants::ORDER_KEY_PARAM  => $order->get_order_key(),
			Constants::DESC_PARAM       => json_encode( $order_details ),
		] );
		$response = apply_filters( 'tendopay_description_response', $response );

		if ( $response->get_code() !== 204 ) {
			throw new TendoPay_Integration_Exception(
				__( 'Got response code != 204 while sending products description', 'tendopay' ) );
		}
	}
}
