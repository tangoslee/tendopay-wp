<?php
/**
 * Created by PhpStorm.
 * User: robert
 * Date: 04.08.2018
 * Time: 06:49
 */

namespace Tendopay\API;


use Tendopay\Gateway;

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

class Endpoint_Caller {
	private $tendopay_merchant_id;
	private $hash_calculator;

	public function __construct() {
		$gateway_options = get_option( "woocommerce_" . Gateway::GATEWAY_ID . "_settings" );

		// initialize parameters, etc
		$this->tendopay_merchant_id = $gateway_options['tendo_pay_merchant_id'];
		$this->secret               = $gateway_options['tendo_secret'];
		$this->hash_calculator      = new Hash_Calculator( $this->secret );
	}

	/**
	 * @param string $url url of the endpoint
	 * @param array $data data to be posted to the endpoint
	 *
	 * @return Response response from the API call
	 */
	public function do_call( $url, array $data, $type = 'POST' ) {
		$data = wp_parse_args( $data, [
			'tendo_pay_merchant_id' => $this->tendopay_merchant_id,
		] );

		$data['hash'] = $this->hash_calculator->calculate( $data );

		// todo do the actuall call
		return new Response( 200, 'd5d11523ab317848ddcd943c2bb467635866dff8b504efe65cae42a21548ef62' );
	}
}
