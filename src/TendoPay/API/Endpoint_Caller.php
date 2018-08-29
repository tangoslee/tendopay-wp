<?php
/**
 * Created by PhpStorm.
 * User: robert
 * Date: 04.08.2018
 * Time: 06:49
 */

namespace TendoPay\API;

use GuzzleHttp\Client;
use TendoPay\Constants;
use TendoPay\Gateway;

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Class Endpoint_Caller
 * @package TendoPay\API
 */
class Endpoint_Caller {
	/**
	 * @var string $bearer_token the bearer token requested in previous API calls. If it's null, it will be taken from
	 * wordpress options. If it was null or expired in the options, it will be then requested from the API.
	 */
	private static $bearer_token;

	/**
	 * @var string $tendopay_merchant_id the merchand ID taken from TendoPay
	 */
	private $tendopay_merchant_id;
	/**
	 * @var string $secret the `secret` string used for hash calculation.
	 */
	private $secret;
	/**
	 * @var string $api_client_id the client ID used for requesting the Bearer token
	 */
	private $api_client_id;
	/**
	 * @var string $api_client_secret the secret string used when requesting the bearer token
	 */
	private $api_client_secret;
	/**
	 * @var Hash_Calculator $hash_calculator calculates sha256 hash of input data using a secret key
	 */
	private $hash_calculator;
	/**
	 * @var \GuzzleHttp\Client $client a http client to make the API calls
	 */
	private $client;

	/**
	 * Prepares configuration required to make the TendoPay API calls.
	 */
	public function __construct() {
		$gateway_options = get_option( "woocommerce_" . Gateway::GATEWAY_ID . "_settings" );

		// initialize parameters, etc
		$this->tendopay_merchant_id = $gateway_options[ Gateway::OPTION_TENDOPAY_VENDOR_ID ];
		$this->secret               = $gateway_options[ Gateway::OPTION_TENDOPAY_SECRET ];
		$this->api_client_id        = $gateway_options[ Gateway::OPTION_TENDOPAY_CLIENT_ID ];
		$this->api_client_secret    = $gateway_options[ Gateway::OPTION_TENDOPAY_CLIENT_SECRET ];
		$this->hash_calculator      = new Hash_Calculator( $this->secret );

		$this->client = new Client( [
			'base_uri' => Constants::BASE_API_URL
		] );
	}

	/**
	 * Performs the actual API call.
	 *
	 * @param string $url url of the endpoint
	 * @param array $data data to be posted to the endpoint
	 *
	 * @return Response response from the API call
	 * @throws \GuzzleHttp\Exception\GuzzleException
	 */
	public function do_call( $url, array $data ) {
		$data = wp_parse_args( $data, [
			Constants::VENDOR_ID_PARAM => $this->tendopay_merchant_id,
		] );
		$data = apply_filters( 'tendopay_endpoint_call_data', $data, $this );

		$data[ Constants::HASH_PARAM ] = $this->hash_calculator->calculate( $data );
		$data                          = apply_filters( 'tendopay_endpoint_call_data_after_hash', $data, $this );

		$headers = [
			'headers' => [
				'Authorization' => 'Bearer ' . $this->get_bearer_token(),
				'Accept'        => 'application/json',
				'Content-Type'  => 'application/json',
				'X-Using'       => 'TendoPay Woocommerce Plugin',
			],
			'json'    => $data
		];
		$headers = apply_filters( 'tendopay_endpoint_call_headers', $headers, $data, $this );

		$response = $this->client->request( 'POST', $url, $headers );
		$response = apply_filters( 'tendopay_endpoint_call_response', $response );

		return new Response( $response->getStatusCode(), $response->getBody() );
	}


	/**
	 * Gets the bearer token in the following way:
	 * 1. Takes the bearer token from {@link Endpoint_Caller::$bearer_token}
	 * 2. If {@link Endpoint_Caller::$bearer_token} is null, takes it from the wordpress options
	 *    by option_key = `tendopay_bearer_token`
	 * 3. If the bearer token still is null, then it requests it from the API and updates both
	 *    {@link Endpoint_Caller::$bearer_token} and wordpress option.
	 *
	 * @return object returns an object containing the `token` and the `expiration_timestamp`
	 * @throws \GuzzleHttp\Exception\GuzzleException when there was a problem in communication with the API (originally
	 * thrown by guzzle http client)
	 */
	private function get_bearer_token() {
		self::$bearer_token = apply_filters( 'tendopay_bearer_token', self::$bearer_token );

		if ( self::$bearer_token === null ) {
			self::$bearer_token = get_option( 'tendopay_bearer_token' );
		}

		$bearer_expiration_timestamp = - 1;
		if ( self::$bearer_token !== null && property_exists( self::$bearer_token, 'expiration_timestamp' ) ) {
			$bearer_expiration_timestamp = self::$bearer_token->expiration_timestamp;
		}

		$current_timestamp = new \DateTime( 'now' );
		$current_timestamp = $current_timestamp->getTimestamp();

		if ( $bearer_expiration_timestamp <= $current_timestamp - 30 ) {
			$headers = [
				'headers' => [
					'Accept'       => 'application/json',
					'Content-Type' => 'application/json',
					'X-Using'      => 'TendoPay Woocommerce Plugin'
				],
				'json'    => [
					"grant_type"    => "client_credentials",
					"client_id"     => $this->api_client_id,
					"client_secret" => $this->api_client_secret
				]
			];
			$headers = apply_filters( 'tendopay_bearer_token_request_headers', $headers, $this );

			$response = $this->client->request( 'POST', Constants::get_bearer_token_endpoint_uri(), $headers );
			$response = apply_filters( 'tendopay_bearer_token_request_response', $response );

			$response_body = (string) $response->getBody();
			$response_body = json_decode( $response_body );

			self::$bearer_token                       = new \stdClass();
			self::$bearer_token->expiration_timestamp = $response_body->expires_in + $current_timestamp;
			self::$bearer_token->token                = $response_body->access_token;

			update_option( 'tendopay_bearer_token', self::$bearer_token, false );
		}

		return self::$bearer_token->token;
	}
}
