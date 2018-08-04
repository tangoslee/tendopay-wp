<?php
/**
 * Created by PhpStorm.
 * User: robert
 * Date: 04.08.2018
 * Time: 15:16
 */

namespace Tendopay\API;


if ( ! defined( 'ABSPATH' ) ) {
	die();
}

class Hash_Calculator {
	const HASH_ALGO = 'sha256';

	private $hash_keys_exclusion_list = [ 'hash' ];
	private $secret;

	/**
	 * Hash_Calculator constructor.
	 *
	 * @param $secret
	 */
	public function __construct( $secret ) {
		$this->secret = $secret;
	}

	public function calculate( array $data ) {
		$data = array_map( function ( $value ) {
			return trim( $value );
		}, $data );

		$data = array_filter( $data, function ( $value, $key ) {
			return ! in_array( $key, $this->hash_keys_exclusion_list ) && ! empty( $value );
		}, ARRAY_FILTER_USE_BOTH );

		ksort( $data );

		$message = join( "", $data );

		return hash_hmac( self::HASH_ALGO, $message, $this->secret, false );
	}
}
