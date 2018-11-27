<?php
/**
 * Created by PhpStorm.
 * User: robert
 * Date: 11.01.18
 * Time: 20:26
 */

namespace TendoPay;

/**
 * This class registers new custom (nice) link used for handling redirection from TendoPay.
 *
 * @package TendoPay
 */
class Redirect_Url_Rewriter {
	/**
	 * @var Redirect_Url_Rewriter $instance the only instance of this class (since it's singleton)
	 */
	private static $instance;

	/**
	 * Making not possible to call the constructor outside of this class.
	 */
	private function __constructor() {
	}

	/**
	 * Returns the only instance of this class. If it doesn't exists, it creates it (once).
	 *
	 * @return Redirect_Url_Rewriter the only one instance of this class
	 */
	public static function get_instance() {
		if ( null == self::$instance ) {
			self::$instance = new self();
			add_action( 'init', array( self::$instance, "add_rules" ) );
		}

		return self::$instance;
	}

	/**
	 * Returns the url used for redirection from TendoPay after the payment process is finished (either successfully or
	 * failed).
	 *
	 * Depends on whether or not the .htaccess file is writable, this function will return nice URL or just dirty URL if
	 * we can't write rewrite rules to .htaccess file.
	 *
	 * @return string url for redirection from TendoPay
	 */
	public function get_redirect_url() {
		return admin_url( 'admin-ajax.php?action=tendopay-result' );
	}

	/**
	 * @hook init 10
	 *
	 * Adds rewrite rules to handle custom link.
	 */
	public function add_rules() {
		// removed due to weird problems
		// todo possibly remove the whole class
	}
}
