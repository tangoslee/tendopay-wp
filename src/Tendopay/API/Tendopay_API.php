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

/**
 * Configuration class.
 *
 * @package Tendopay\API
 */
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

	/**
	 * Gets the hash algorithm.
	 *
	 * @return string hash algorithm
	 */
	public static function get_hash_algorithm() {
		return self::HASH_ALGORITHM;
	}

	/**
	 * Gets the base api URL. It checks whether to use SANDBOX URL or Production URL.
	 *
	 * @return string the base api url
	 */
	public static function get_base_api_url() {
		return self::is_sandbox_enabled() ? self::SANDBOX_BASE_API_URL : self::BASE_API_URL;
	}

	/**
	 * Gets the redirect uri. It checks whether to use SANDBOX URI or Production URI.
	 *
	 * @return string redirect uri
	 */
	public static function get_redirect_uri() {
		return self::is_sandbox_enabled() ? self::SANDBOX_REDIRECT_URI : self::REDIRECT_URI;
	}

	/**
	 * Gets the view uri pattern. It checks whether to use SANDBOX pattern or Production pattern.
	 *
	 * @return string view uri pattern
	 */
	public static function get_view_uri_pattern() {
		return self::is_sandbox_enabled() ? self::SANDBOX_VIEW_URI_PATTERN : self::VIEW_URI_PATTERN;
	}

	/**
	 * Gets the verification endpoint uri. It checks whether to use SANDBOX URI or Production URI.
	 *
	 * @return string verification endpoint uri
	 */
	public static function get_verification_endpoint_uri() {
		return self::is_sandbox_enabled() ? self::SANDBOX_VERIFICATION_ENDPOINT_URI : self::VERIFICATION_ENDPOINT_URI;
	}

	/**
	 * Gets the authorization endpoint uri. It checks whether to use SANDBOX URI or Production URI.
	 *
	 * @return string authorization endpoint uri
	 */
	public static function get_authorization_endpoint_uri() {
		return self::is_sandbox_enabled() ? self::SANDBOX_AUTHORIZATION_ENDPOINT_URI : self::AUTHORIZATION_ENDPOINT_URI;
	}

	/**
	 * Gets the description endpoint uri. It checks whether to use SANDBOX URI or Production URI.
	 *
	 * @return string description endpoint uri
	 */
	public static function get_description_endpoint_uri() {
		return self::is_sandbox_enabled() ? self::SANDBOX_DESCRIPTION_ENDPOINT_URI : self::DESCRIPTION_ENDPOINT_URI;
	}

	/**
	 * Gets the bearer token endpoint uri. It checks whether to use SANDBOX URI or Production URI.
	 *
	 * @return string bearer token endpoint uri
	 */
	public static function get_bearer_token_endpoint_uri() {
		return self::is_sandbox_enabled() ? self::SANDBOX_BEARER_TOKEN_ENDPOINT_URI : self::BEARER_TOKEN_ENDPOINT_URI;
	}

	/**
	 *
	 * @return bool true if sandbox is enabled
	 */
	private static function is_sandbox_enabled() {
		$gateway_options = get_option( "woocommerce_" . Gateway::GATEWAY_ID . "_settings" );

		return apply_filters( 'tendopay_sandbox_enabled', $gateway_options['tendo_sandbox_enabled'] === 'yes' );
	}
}
