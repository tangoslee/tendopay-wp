<?php
/**
 * Created by PhpStorm.
 * User: robert
 * Date: 04.08.2018
 * Time: 06:28
 */

namespace Tendopay\API;


use Tendopay\Exceptions\TendoPay_Integration_Exception;
use Tendopay\Woocommerce_Order_Retriever;

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Handles API calls to the Description Endpoint. It is used to send descriptions of items being paid by TendoPay.
 *
 * @package Tendopay\API
 */
class Description_Endpoint {
	/**
	 * Sends the line items details to the TendoPay. It is used on TP site to properly display checkout items and their
	 * prices.
	 *
	 * @param string $authorization_token authorization token obtained from {@link Authorization_Endpoint}
	 * @param \WC_Order $order order that is going paid.
	 *
	 * @throws TendoPay_Integration_Exception if the order details taken from the order are not returned in form of array
	 *         or object. It should be very rare case as it shouldn't be possible to place order without items.
	 * @throws \GuzzleHttp\Exception\GuzzleException when there was a problem in communication with the API (originally
	 *         thrown by guzzle http client)
	 */
	public static function set_description( $authorization_token, \WC_Order $order ) {
		$order_retriever = new Woocommerce_Order_Retriever( $order );
		$order_details   = $order_retriever->get_order_details();

		if ( ! is_array( $order_details ) && ! is_object( $order_details ) ) {
			throw new \InvalidArgumentException( "Order details parameter must be either ARRAY or OBJECT" );
		}

		if ( empty( $order_details ) ) {
			// nothing to send to TP, exiting
			return;
		}

		$caller   = new Endpoint_Caller();
		$response = $caller->do_call( Tendopay_API::get_description_endpoint_uri(), [
			'authorisation_token'  => $authorization_token,
			'customer_reference_1' => (string) $order->get_id(),
			'customer_reference_2' => $order->get_order_key(),
			'description'          => json_encode( $order_details ),
		] );

		if ( $response->get_code() !== 204 ) {
			throw new TendoPay_Integration_Exception( "Could not communicate with TendoPay" );
		}
	}
}
