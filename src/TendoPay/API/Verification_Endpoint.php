<?php
/**
 * Created by PhpStorm.
 * User: robert
 * Date: 04.08.2018
 * Time: 06:40
 */

namespace TendoPay\API;


use InvalidArgumentException;
use TendoPay\Constants;
use TendoPay\Exceptions\TendoPay_Integration_Exception;
use TendoPay\Gateway;

if ( ! defined( 'ABSPATH' ) ) {
    die();
}

/**
 * This class is responsible for communication with the Verification Endpoint of TendoPay API.
 *
 * @package TendoPay\API
 */
class Verification_Endpoint {
    /**
     * Verifies the payment.
     *
     * @param \WC_Order $order the order for which the payment is being verified
     * @param array $data data that came with the redirection from TP site.
     *
     * @return bool if the payment has been properly verified AND its status is `success`
     *
     * @throws TendoPay_Integration_Exception when the response code from verification endpoint is not equal to `200`
     * @throws \GuzzleHttp\Exception\GuzzleException when there was a problem in communication with the API (originally
     *         thrown by guzzle http client)
     * @throws InvalidArgumentException if the hash from redirection doesn't match the calculated hash
     */
    public function verify_payment( \WC_Order $order, array $data ) {
        ksort( $data );

        $gateway_options = get_option( "woocommerce_" . Gateway::GATEWAY_ID . "_settings" );
        $hash_calculator = new Hash_Calculator( $gateway_options[ Gateway::OPTION_TENDOPAY_SECRET ] );

        $hash = $data[ Constants::HASH_PARAM ];

        if ( $hash !== $hash_calculator->calculate( $data ) ) {
            throw new InvalidArgumentException( __( "Hash doesn't match", "tendopay" ) );
        }

        $disposition                  = $data[ Constants::DISPOSITION_PARAM ];
        $tendo_pay_transaction_number = $data[ Constants::TRANSACTION_NO_PARAM ];
        $verification_token           = $data[ Constants::VERIFICATION_TOKEN_PARAM ];
        $tendo_pay_user_id            = $data[ Constants::USER_ID_PARAM ];

        $verification_data = [
            Constants::ORDER_ID_PARAM           => (string) $order->get_id(),
            Constants::ORDER_KEY_PARAM          => $order->get_order_key(),
            Constants::DISPOSITION_PARAM        => $disposition,
            Constants::VENDOR_ID_PARAM          => (string) $gateway_options[ Gateway::OPTION_TENDOPAY_VENDOR_ID ],
            Constants::TRANSACTION_NO_PARAM     => (string) $tendo_pay_transaction_number,
            Constants::VERIFICATION_TOKEN_PARAM => $verification_token,
            Constants::USER_ID_PARAM 		    => $tendo_pay_user_id,
        ];

        $verification_data = apply_filters( 'tendopay_verification_data', $verification_data );

        $endpoint_caller = new Endpoint_Caller();
        $response        = $endpoint_caller->do_call( Constants::get_verification_endpoint_uri(),
            $verification_data );

        $response = apply_filters( 'tendopay_verification_endpoint_response', $response );

        if ( $response->get_code() !== 200 ) {
            throw new TendoPay_Integration_Exception(
                sprintf( __( "Received error: [%s] while trying to verify the transaction", 'tendopay' ),
                    $response->get_code() ) );
        }

        $json = json_decode( $response->get_body() );

        return $json->{Constants::STATUS_PARAM} === 'success';
    }
}
