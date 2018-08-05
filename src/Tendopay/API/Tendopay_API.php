<?php
/**
 * Created by PhpStorm.
 * User: robert
 * Date: 05.08.2018
 * Time: 05:59
 */

namespace Tendopay\API;


use Tendopay\Gateway;

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

class Tendopay_API {
	const HASH_ALGORITHM = 'sha256';

	const REDIRECT_URL = 'https://tendopay.example.com/make/payment';
	const VIEW_URL_PATTERN = 'https://tendopay.example.com/view/transaction/%s';
	const VERIFICATION_ENDPOINT_URL = 'https://tendopay.com/payment/verification';
	const AUTHORIZATION_ENDPOINT_URL = 'https://tendopay.com/payment/authorise';
	const DESCRIPTION_ENDPOINT_URL = 'https://tendopay.com/payment/description';

	const SANDBOX_REDIRECT_URL = 'https://sandbox.tendopay.example.com/make/payment';
	const SANDBOX_VIEW_URL_PATTERN = 'https://sandbox.tendopay.example.com/view/transaction/%s';
	const SANDBOX_VERIFICATION_ENDPOINT_URL = 'https://sandbox.tendopay.com/payment/verification';
	const SANDBOX_AUTHORIZATION_ENDPOINT_URL = 'https://sandbox.tendopay.com/payment/authorise';
	const SANDBOX_DESCRIPTION_ENDPOINT_URL = 'https://sandbox.tendopay.com/payment/description';

	public static function get_hash_algorithm() {
		return self::HASH_ALGORITHM;
	}

	public static function get_redirect_url() {
		return self::is_sandbox_enabled() ? self::SANDBOX_REDIRECT_URL : self::REDIRECT_URL;
	}

	public static function get_view_url_pattern() {
		return self::is_sandbox_enabled() ? self::SANDBOX_VIEW_URL_PATTERN : self::VIEW_URL_PATTERN;
	}

	public static function get_verification_endpoint_url() {
		return self::is_sandbox_enabled() ? self::SANDBOX_VERIFICATION_ENDPOINT_URL : self::VERIFICATION_ENDPOINT_URL;
	}

	public static function get_authorization_endpoint_url() {
		return self::is_sandbox_enabled() ? self::SANDBOX_AUTHORIZATION_ENDPOINT_URL : self::AUTHORIZATION_ENDPOINT_URL;
	}

	public static function get_description_endpoint_url() {
		return self::is_sandbox_enabled() ? self::SANDBOX_DESCRIPTION_ENDPOINT_URL : self::DESCRIPTION_ENDPOINT_URL;
	}

	private static function is_sandbox_enabled() {
		$gateway_options = get_option( "woocommerce_" . Gateway::GATEWAY_ID . "_settings" );

		return $gateway_options['tendo_sandbox_enabled'];
	}
}