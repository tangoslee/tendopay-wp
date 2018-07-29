<?php

/**
 * Created by PhpStorm.
 * User: robert
 * Date: 11.01.18
 * Time: 20:26
 */
if ( ! class_exists( 'Tendopay_Rewriter' ) ) {
	class Tendopay_Rewriter {
		private static $instance;
		private $patterns = array(
			'tendopay-result' => 'tendopay-result'
		);

		// disabled for singleton
		private function __constructor() {
		}

		public static function get_instance() {
			if ( null == Tendopay_Rewriter::$instance ) {
				Tendopay_Rewriter::$instance = new Tendopay_Rewriter();
				add_action( 'init', array( Tendopay_Rewriter::$instance, "add_rules" ) );
			}

			return Tendopay_Rewriter::$instance;
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
}
