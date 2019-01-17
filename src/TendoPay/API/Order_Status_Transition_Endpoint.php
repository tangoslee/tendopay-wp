<?php
/**
 * Created by PhpStorm.
 * User: robert
 * Date: 04.08.2018
 * Time: 06:40
 */

namespace TendoPay\API;


use GuzzleHttp\Exception\BadResponseException;
use InvalidArgumentException;
use Monolog\Logger;
use TendoPay\Constants;
use TendoPay\Exceptions\TendoPay_Integration_Exception;
use TendoPay\Gateway;
use TendoPay\Logger_Factory;

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * This class is responsible for communication with the Order Status Transition Endpoint of TendoPay API.
 *
 * @package TendoPay\API
 */
class Order_Status_Transition_Endpoint {
	/** @var Logger $logger */
	private $logger;

	/**
	 * Order_Status_Transition_Endpoint constructor.
	 */
	public function __construct() {
		$this->logger = Logger_Factory::create_logger("status transition");
	}

	/**
	 * Updates the payment status info.
	 *
	 * @param \WC_Order $order the order for which the payment is being updated
	 * @param array $data data that came with the redirection from TP site.
	 *
	 * @throws TendoPay_Integration_Exception when the response code from order status transition endpoint is not equal
	 *         to `200`
	 * @throws \GuzzleHttp\Exception\GuzzleException when there was a problem in communication with the API (originally
	 *         thrown by guzzle http client)
	 */
	public function notify( \WC_Order $order, array $data, array $update_data ) {
		ksort( $data );

		$gateway_options = get_option( "woocommerce_" . Gateway::GATEWAY_ID . "_settings" );

		$disposition                  = $data[ Constants::DISPOSITION_PARAM ];
		$tendo_pay_transaction_number = $data[ Constants::TRANSACTION_NO_PARAM ];
		$verification_token           = $data[ Constants::VERIFICATION_TOKEN_PARAM ];
		$tendo_pay_user_id            = $data[ Constants::USER_ID_PARAM ];

		$data = [
			Constants::DISPOSITION_PARAM        => $disposition,
			Constants::ORDER_ID_PARAM           => (string) $order->get_id(),
			Constants::ORDER_KEY_PARAM          => $order->get_order_key(),
			Constants::VENDOR_ID_PARAM          => (string) $gateway_options[ Gateway::OPTION_TENDOPAY_VENDOR_ID ],
			Constants::TRANSACTION_NO_PARAM     => (string) $tendo_pay_transaction_number,
			Constants::USER_ID_PARAM            => $tendo_pay_user_id,
			Constants::VERIFICATION_TOKEN_PARAM => $verification_token,
			Constants::ORDER_UPDATE_PARAM       => json_encode( $update_data )
		];
		$data = apply_filters( 'tendopay_order_status_transition_data', $data );

		$endpoint_caller = new Endpoint_Caller();

		try {
			$response = $endpoint_caller->do_call( Constants::get_order_status_transition_endpoint_uri(), $data );
		} catch ( BadResponseException $exception ) {
			$this->logger->error( $exception->getResponse()->getBody() );
			$this->logger->error( $exception->getTraceAsString() );
			throw new TendoPay_Integration_Exception(
				__( "Received error from TendoPay API while trying to notify about status transition",
					"tendopay", $exception ) );
		}

		$response = apply_filters( 'tendopay_order_status_transition_endpoint_response', $response );

		if ( $response->get_code() !== 200 ) {
			throw new TendoPay_Integration_Exception(
				sprintf( __( "Received error: [%s] while trying to notify about status transition of the transaction",
					'tendopay' ), $response->get_code() ) );
		}
	}
}
