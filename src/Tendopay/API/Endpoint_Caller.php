<?php
/**
 * Created by PhpStorm.
 * User: robert
 * Date: 04.08.2018
 * Time: 06:49
 */

namespace Tendopay\API;


use GuzzleHttp\Client;
use Tendopay\Gateway;

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

class Endpoint_Caller {
	private static $bearer_token;

	private $tendopay_merchant_id;
	private $secret;
	private $api_client_id;
	private $api_client_secret;
	private $hash_calculator;
	private $client;

	public function __construct() {
		$gateway_options = get_option( "woocommerce_" . Gateway::GATEWAY_ID . "_settings" );

		// initialize parameters, etc
		$this->tendopay_merchant_id = $gateway_options['tendo_pay_merchant_id'];
		$this->secret               = $gateway_options['tendo_secret'];
		$this->api_client_id        = $gateway_options['tendo_client_id'];
		$this->api_client_secret    = $gateway_options['tendo_client_secret'];
		$this->hash_calculator      = new Hash_Calculator( $this->secret );

		$this->client = new Client( [
			'base_uri' => Tendopay_API::BASE_API_URL
		] );
	}

	/**
	 * @param string $url url of the endpoint
	 * @param array $data data to be posted to the endpoint
	 *
	 * @return Response response from the API call
	 * @throws \GuzzleHttp\Exception\GuzzleException
	 */
	public function do_call( $url, array $data ) {
		$data = wp_parse_args( $data, [
			'tendo_pay_merchant_id' => $this->tendopay_merchant_id,
		] );

		$data['hash'] = $this->hash_calculator->calculate( $data );

		error_log( "do_call" );
		error_log( json_encode( $data ) );

		$response = $this->client->request( 'POST', $url, [
			'headers' => [
				'Authorization' => 'Bearer ' . $this->get_bearer_token(),
				'Accept'        => 'application/json',
				'Content-Type'  => 'application/json',
				'X-Using'       => 'Tendopay Woocommerce Plugin',
			],
			'json'    => $data
		] );

		return new Response( $response->getStatusCode(), $response->getBody() );
	}


	/**
	 * @return mixed
	 * @throws \GuzzleHttp\Exception\GuzzleException
	 */
	private function get_bearer_token() {
		if ( ! self::$bearer_token ) {
			self::$bearer_token = get_option( 'tendopay_bearer_token' );
		}

		$bearer_expiration_timestamp = self::$bearer_token->expiration_timestamp;
		$current_timestamp           = new \DateTime( 'now' );

		if ( $bearer_expiration_timestamp <= $current_timestamp->getTimestamp() - 30 ) {
			$response = $this->client->request( 'POST', Tendopay_API::get_bearer_token_endpoint_uri(), [
				'headers' => [
					'Accept'       => 'application/json',
					'Content-Type' => 'application/json',
					'X-Using'      => 'Tendopay Woocommerce Plugin'
				],
				'json'    => [
					"grant_type"    => "client_credentials",
					"client_id"     => $this->api_client_id,
					"client_secret" => $this->api_client_secret
				]
			] );

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
