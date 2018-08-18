<?php
/**
 * Created by PhpStorm.
 * User: robert
 * Date: 04.08.2018
 * Time: 15:16
 */

namespace TendoPay\API;


use TendoPay\Constants;

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Provides logic for hash calculation based on the input data. Uses sha256.
 *
 * @package TendoPay\API
 */
class Hash_Calculator {
	/**
	 * @var array $hash_keys_exclusion_list it provides list of array keys that will be excluded from hashing.
	 */
	private $hash_keys_exclusion_list = [ Constants::HASH_PARAM ];
	/**
	 * @var string $secret secret used to calculate the hash
	 */
	private $secret;

	/**
	 * Configures the hash calculator.
	 *
	 * @param string $secret secret used to calculate the hash
	 */
	public function __construct( $secret ) {
		$this->secret = $secret;
	}

	/**
	 * Calculates hash based on the `$data` and {@link Hash_Calculator::$secret}.
	 *
	 * @param array $data input data based on which the hash will be calculated
	 *
	 * @return false|string The hash in string. False if hash algorithm defined in
	 *         {@link Constants::get_hash_algorithm()} is unknown or invalid.
	 */
	public function calculate( array $data ) {
		$data = array_map( function ( $value ) {
			return trim( $value );
		}, $data );
		$data = apply_filters( 'tendopay_hash_calculator_data', $data );

		$exclusion_list = apply_filters( 'tendopay_hash_calculator_exclusion_list', $this->hash_keys_exclusion_list );

		$data = array_filter( $data, function ( $value, $key ) use ( $exclusion_list ) {
			return ! in_array( $key, $exclusion_list ) && ! empty( $value );
		}, ARRAY_FILTER_USE_BOTH );
		$data = apply_filters( 'tendopay_hash_calculator_filter_data', $data );

		ksort( $data );

		$message = join( "", $data );

		return hash_hmac( Constants::get_hash_algorithm(), $message, $this->secret, false );
	}
}
