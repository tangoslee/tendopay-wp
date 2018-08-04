<?php

namespace Tendopay;

/**
 * Created by PhpStorm.
 * User: robert
 * Date: 11.01.18
 * Time: 20:26
 */
class Url_Rewriter {
	private static $instance;
	private $patterns = array(
		'tendopay-result' => 'tendopay-result'
	);

	// disabled for singleton
	private function __constructor() {
	}

	public static function get_instance() {
		if ( null == self::$instance ) {
			self::$instance = new self();
			add_action( 'init', array( self::$instance, "add_rules" ) );
		}

		return self::$instance;
	}

	public function add_rules() {
		$url = substr( admin_url( 'admin-post.php?action=tendopay-result' ), strlen( site_url() ) + 1 );

		$pattern = '^' . $this->patterns['tendopay-result'] . '/?';
		add_rewrite_rule( $pattern, $url, 'top' );
	}

	public function get_pattern( $key ) {
		if ( ! array_key_exists( $key, $this->patterns ) ) {
			return false;
		}

		return $this->patterns[ $key ];
	}
}
