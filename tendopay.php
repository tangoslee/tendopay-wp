<?php
/*
Plugin Name: TendoPay
Description: TendoPay is a ‘Buy now. Pay later’ financing platform for online shopping. This plugin allows your ecommerce site to use TendoPay as a payment method.
Version:     0.1
Author:      TendoPay
Author URI:  http://tendopay.ph/
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

use Tendopay\Tendopay;
use Tendopay\Utils;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

if ( ! defined( 'TENDOPAY' ) ) {
	define( 'TENDOPAY', true );

	require_once "vendor/autoload.php";
	require_once "src/Tendopay/Utils.php";
	require_once "src/Tendopay/Tendopay.php";
	require_once "src/Tendopay/Redirect_Url_Rewriter.php";
	require_once "src/Tendopay/Woocommerce_Order_Description_Retriever.php";
	require_once "src/Tendopay/Exceptions/TendoPay_Integration_Exception.php";
	require_once "src/Tendopay/API/Tendopay_API.php";
	require_once "src/Tendopay/API/Hash_Calculator.php";
	require_once "src/Tendopay/API/Description_Endpoint.php";
	require_once "src/Tendopay/API/Authorization_Endpoint.php";
	require_once "src/Tendopay/API/Endpoint_Caller.php";
	require_once "src/Tendopay/API/Response.php";
	require_once "src/Tendopay/API/Verification_Endpoint.php";

	/**
	 * The main function responsible for plugin's initialization.
	 * You can access the plugin simply by using <?php $tendopay = tendopay(); ?>
	 */
	function tendopay() {
		return Tendopay::get_instance();
	}

	if ( Utils::is_woocommerce_active() ) {
		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ),
			[ Tendopay::class, 'add_settings_link' ] );
		tendopay();
	} else {
		add_action( 'admin_notices', [ Tendopay::class, 'no_woocommerce_admin_notice' ] );
	}

	register_activation_hook( __FILE__, [ Tendopay::class, 'install' ] );
	register_deactivation_hook( __FILE__, [ Tendopay::class, 'uninstall' ] );
}
