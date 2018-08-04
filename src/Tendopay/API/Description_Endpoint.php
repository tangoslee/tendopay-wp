<?php
/**
 * Created by PhpStorm.
 * User: robert
 * Date: 04.08.2018
 * Time: 06:28
 */

namespace Tendopay\API;


use Tendopay\Exceptions\TendoPay_Integration_Exception;

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

class Description_Endpoint {
	const ENDPOINT_URL = 'https://tendopay.com/payment/description';

	private $authorization_token;
	private $order_id;
	private $order_key;

	public function __construct( $auth_token, \WC_Order $order ) {
		$this->authorization_token = $auth_token;
		$this->order_id            = $order->get_id();
		$this->order_key           = $order->get_order_key();
	}

	/**
	 * @param $order_details
	 *
	 * @throws TendoPay_Integration_Exception
	 */
	public function set_description( $order_details ) {
		if ( ! is_array( $order_details ) && ! is_object( $order_details ) ) {
			throw new \InvalidArgumentException( "Order details parameter must be either ARRAY or OBJECT" );
		}

		if ( empty( $order_details ) ) {
			// nothing to send to TP, exiting
			return;
		}

		$caller   = new Endpoint_Caller();
		$response = $caller->do_call( self::ENDPOINT_URL, [
			'authorisation_token'  => $this->authorization_token,
			'customer_reference_1' => $this->order_id,
			'customer_reference_2' => $this->order_key,
			'description'          => json_encode( $order_details ),
		] );

		// todo remove below line while integrating with actuall API - just for local tests
		$response = new Response( 204, $response->get_body() );

		if ( $response->get_code() !== 204 ) {
			error_log( "didn't get 204 after sending description" );
			throw new TendoPay_Integration_Exception( "Could not communicate with TendoPay" );
		}

		error_log( "got 204 after sending description" );
	}
}
