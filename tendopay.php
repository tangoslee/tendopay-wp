<?php
/*
Plugin Name: TendoPay
Description: TendoPay is a 'Buy now. Pay later' financing platform for online shopping. This plugin allows your ecommerce site to use TendoPay as a payment method.
Version:     0.1
Author:      TendoPay
Author URI:  http://tendopay.ph/
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

use TendoPay\TendoPay;
use TendoPay\Utils;
use TendoPay\Exceptions\TendoPay_Integration_Exception;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

if ( ! defined( 'TENDOPAY' ) ) {
	define( 'TENDOPAY', true );

	require_once "vendor/autoload.php";
	require_once "src/TendoPay/Utils.php";
	require_once "src/TendoPay/TendoPay.php";
	require_once "src/TendoPay/Redirect_Url_Rewriter.php";
	require_once "src/TendoPay/Woocommerce_Order_Description_Retriever.php";
	require_once "src/TendoPay/Exceptions/TendoPay_Integration_Exception.php";
	require_once "src/TendoPay/Constants.php";
	require_once "src/TendoPay/API/Hash_Calculator.php";
	require_once "src/TendoPay/API/Description_Endpoint.php";
	require_once "src/TendoPay/API/Authorization_Endpoint.php";
	require_once "src/TendoPay/API/Endpoint_Caller.php";
	require_once "src/TendoPay/API/Response.php";
	require_once "src/TendoPay/API/Verification_Endpoint.php";

	function tendopay_fatal_error() {
		$error     = error_get_last();
		$trace     = isset( $error['message'] ) ? $error['message'] : '';x
		$backtrace = array(
			'message' => 'Fatal Error',
			'trace'   => explode( "\n", $trace ),
		);
		TendoPay_Integration_Exception::sendReport( $backtrace );
	}

	register_shutdown_function( 'tendopay_fatal_error' );


	/**
	 * The main function responsible for plugin's initialization.
	 * You can access the plugin simply by using <?php $tendopay = tendopay(); ?>
	 */
	function tendopay() {
		return TendoPay::get_instance();
	}

	if ( Utils::is_woocommerce_active() ) {
		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ),
			[ TendoPay::class, 'add_settings_link' ] );
		tendopay();
	} else {
		add_action( 'admin_notices', [ TendoPay::class, 'no_woocommerce_admin_notice' ] );
	}

	register_activation_hook( __FILE__, [ TendoPay::class, 'install' ] );
	register_deactivation_hook( __FILE__, [ TendoPay::class, 'uninstall' ] );
}
