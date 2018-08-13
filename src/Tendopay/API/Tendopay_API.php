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
	const REDIRECT_URL_PATTERN = '^tendopay-result/?';

	const HASH_ALGORITHM = 'sha256';
	const BASE_API_URL = 'http://alpha.tendopay.ph';

	const SANDBOX_BASE_API_URL = 'http://alpha.tendopay.ph';

	const REDIRECT_URI = 'http://alpha.tendopay.ph/payments/authorise';
	const VIEW_URI_PATTERN = 'http://alpha.tendopay.ph/view/transaction/%s';
	const VERIFICATION_ENDPOINT_URI = 'payments/api/v1/verification';
	const AUTHORIZATION_ENDPOINT_URI = 'payments/api/v1/authTokenRequest';
	const DESCRIPTION_ENDPOINT_URI = 'payments/api/v1/paymentDescription';
	const BEARER_TOKEN_ENDPOINT_URI = 'oauth/token';

	const SANDBOX_REDIRECT_URI = 'http://alpha.tendopay.ph/payments/authorise';
	const SANDBOX_VIEW_URI_PATTERN = 'http://alpha.tendopay.ph/view/transaction/%s';
	const SANDBOX_VERIFICATION_ENDPOINT_URI = 'payments/api/v1/verification';
	const SANDBOX_AUTHORIZATION_ENDPOINT_URI = 'payments/api/v1/authTokenRequest';
	const SANDBOX_DESCRIPTION_ENDPOINT_URI = 'payments/api/v1/paymentDescription';
	const SANDBOX_BEARER_TOKEN_ENDPOINT_URI = 'oauth/token';

	public static function get_hash_algorithm() {
		return self::HASH_ALGORITHM;
	}

	public static function get_base_api_url() {
		return self::is_sandbox_enabled() ? self::SANDBOX_BASE_API_URL : self::BASE_API_URL;
	}

	public static function get_redirect_uri() {
		return self::is_sandbox_enabled() ? self::SANDBOX_REDIRECT_URI : self::REDIRECT_URI;
	}

	public static function get_view_uri_pattern() {
		return self::is_sandbox_enabled() ? self::SANDBOX_VIEW_URI_PATTERN : self::VIEW_URI_PATTERN;
	}

	public static function get_verification_endpoint_uri() {
		return self::is_sandbox_enabled() ? self::SANDBOX_VERIFICATION_ENDPOINT_URI : self::VERIFICATION_ENDPOINT_URI;
	}

	public static function get_authorization_endpoint_uri() {
		return self::is_sandbox_enabled() ? self::SANDBOX_AUTHORIZATION_ENDPOINT_URI : self::AUTHORIZATION_ENDPOINT_URI;
	}

	public static function get_description_endpoint_uri() {
		return self::is_sandbox_enabled() ? self::SANDBOX_DESCRIPTION_ENDPOINT_URI : self::DESCRIPTION_ENDPOINT_URI;
	}

	public static function get_bearer_token_endpoint_uri() {
		return self::is_sandbox_enabled() ? self::SANDBOX_BEARER_TOKEN_ENDPOINT_URI : self::BEARER_TOKEN_ENDPOINT_URI;
	}

	private static function is_sandbox_enabled() {
		$gateway_options = get_option( "woocommerce_" . Gateway::GATEWAY_ID . "_settings" );

		return $gateway_options['tendo_sandbox_enabled'] === 'yes';
	}
}