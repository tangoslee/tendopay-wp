<?php

namespace Tendopay;

use Tendopay\API\Tendopay_API;

/**
 * Created by PhpStorm.
 * User: robert
 * Date: 11.01.18
 * Time: 20:26
 */
class Redirect_Url_Rewriter {
	/**
	 * @var
	 */
	private static $instance;

	/**
	 *
	 */
	private function __constructor() {
	}

	/**
	 * @return Redirect_Url_Rewriter
	 */
	public static function get_instance() {
		if ( null == self::$instance ) {
			self::$instance = new self();
			add_action( 'init', array( self::$instance, "add_rules" ) );
		}

		return self::$instance;
	}

	/**
	 *
	 */
	public function add_rules() {
		$url = substr( admin_url( 'admin-post.php?action=tendopay-result' ), strlen( site_url() ) + 1 );

		add_rewrite_rule( Tendopay_API::REDIRECT_URL_PATTERN, $url, 'top' );
	}
}
