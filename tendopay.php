<?php
/*
Plugin Name: Tendopay
Description: This plugin provides integration with Tendopay
Version:     0.1
Author:      TreeVert Kłodziński Robert
Author URI:  http://www.treevert.com/
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

use Tendopay\Tendopay;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

if ( ! defined( 'TENDOPAY' ) ) {
	define( 'TENDOPAY', true );

	require_once "src/Tendopay/Tendopay.php";
	require_once "src/Tendopay/Url_Rewriter.php";
	require_once "src/Tendopay/Woocommerce_Order_Retriever.php";
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

	/**
	 * Initialize.
	 */

	if ( in_array( 'woocommerce/woocommerce.php',
		apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
		tendopay();
	} else {
		add_action( 'admin_notices', [ Tendopay::class, 'no_woocommerce_admin_notice' ] );
	}
}
