<?php

/**
 * Created by PhpStorm.
 * User: robert
 * Date: 16.01.18
 * Time: 22:03
 */

namespace TendoPay;

use TendoPay\API\Verification_Endpoint;
use \WC_Order_Factory;

/**
 * Class TendoPay
 * @package TendoPay
 */
class TendoPay {
	/**
	 * @var TendoPay $instance the only instance of this class
	 */
	private static $instance;

	/**
	 * Private constructor required for singleton implementation. Registers hooks.
	 */
	private function __construct() {
		$this->register_hooks();
	}

	/**
	 * Returns the only instance of this class. If instance wasn't created yet - it creates the instance before returning.
	 *
	 * @return TendoPay the only instance of this class
	 */
	public static function get_instance() {
		if ( self::$instance === null ) {
			self::$instance = new TendoPay();
		}

		return self::$instance;
	}

	/**
	 * @hook activate_tendopay/tendopay.php
	 *
	 * Installs rewrite rules required by the plugin. This method is executed every time the plugin is activated.
	 */
	public static function install() {
		Redirect_Url_Rewriter::get_instance()->add_rules();
		flush_rewrite_rules();
	}

	/**
	 * @hook deactivate_tendopay/tendopay.php
	 *
	 * Removes rewirte rules required by this plugin.  This method is executed every time the plugin is deactivated.
	 */
	public static function uninstall() {
		/** @var \WP_Rewrite $wp_rewrite */
		global $wp_rewrite;
		unset( $wp_rewrite->non_wp_rules[ Constants::REDIRECT_URL_PATTERN ] );
		flush_rewrite_rules();
	}

	/**
	 * Registers hooks required by the plugin:
	 * - payment gateway initialization and registration in the woocommerce
	 * - Setting up rewirte rules
	 * - handing redirect with disposition from TendoPay after the transaction is completed
	 */
	public function register_hooks() {
		add_action( 'plugins_loaded', array( $this, 'init_gateway' ) );
		add_filter( 'woocommerce_payment_gateways', array( $this, 'register_gateway' ) );
		add_action( 'plugins_loaded', array( Redirect_Url_Rewriter::class, 'get_instance' ) );
		add_action( 'admin_post_tendopay-result', array( $this, 'handle_redirect_from_tendopay' ) );
		add_action( 'admin_post_nopriv_tendopay-result', array( $this, 'handle_redirect_from_tendopay' ) );
	}

	/**
	 * @hook admin_post_tendopay-result 10
	 * @hook admin_post_nopriv_tendopay-result 10
	 *
	 * Handles redirect with disposition from TendoPay after the transaction is completed.
	 *
	 * When the redirect comes in this function verifies the outcome of transaction. It does that first by checking if
	 * the hash from incoming parameters is calculated properly. Only if it is valid it will call TendoPay API
	 * Verification Endpoint with verification token to get the confirmed status of this transaction.
	 *
	 * Please note you should not trust only the parameters that comes with the redirect.
	 *
	 * @throws \GuzzleHttp\Exception\GuzzleException
	 */
	function handle_redirect_from_tendopay() {
		$posted_data = apply_filters( 'tendopay_posted_data', $_REQUEST );

		if ( isset( $posted_data['action'] ) ) {
			unset( $posted_data['action'] );
		}

		$order     = WC_Order_Factory::get_order( (int) $posted_data['customer_reference_1'] );
		$order_key = $posted_data['customer_reference_2'];

		if ( $order->get_order_key() !== $order_key ) {
			wp_die( new \WP_Error( 'wrong-order-key', __( 'Wrong order key provided', 'tendopay' ) ),
				__( 'Wrong order key provided', 'tendopay' ), 403 );
		}

		$gateway_options             = get_option( "woocommerce_" . Gateway::GATEWAY_ID . "_settings" );
		$tendo_pay_merchant_id       = $posted_data['tendo_pay_merchant_id'];
		$local_tendo_pay_merchant_id = $gateway_options['tendo_pay_merchant_id'];

		if ( $tendo_pay_merchant_id !== $local_tendo_pay_merchant_id ) {
			wp_die( new \WP_Error( 'wrong-merchant-id', 'Malformed payload' ),
				__( 'Malformed payload', 'tendopay' ), 403 );
		}

		try {
			$verification      = new Verification_Endpoint();
			$payment_completed = $verification->verify_payment( $order, $posted_data );
		} catch ( \Exception $exception ) {
			error_log( $exception->getMessage() );
			error_log( $exception->getTraceAsString() );
			wp_die( new \WP_Error( 'tendopay-integration-error',
				__( 'Could not communicate with TendoPay properly', 'tendopay' ) ),
				__( 'Could not communicate with TendoPay properly', 'tendopay' ), 403 );
		}

		if ( $payment_completed ) {
			$order->payment_complete();
			wp_redirect( $order->get_checkout_order_received_url() );
		} else {
			wp_redirect( $order->get_checkout_payment_url() );
		}

		exit;
	}


	/**
	 * @hook woocommerce_payment_gateways 10
	 *
	 * Registers TendoPay gateway in the system.
	 *
	 * @param array $methods Other methods registered in the system
	 *
	 * @return array modified list of gateways (including tendopay)
	 */
	public function register_gateway( $methods ) {
		$methods[] = Gateway::class;

		return $methods;
	}

	/**
	 * @hook plugins_loaded 10
	 *
	 * Initializes gateway
	 */
	public function init_gateway() {
		include_once dirname( __FILE__ ) . "/Gateway.php";
	}

	/**
	 * @hook admin_notices 10
	 *
	 * Shows notice that Woocommerce plugin must be enabled.
	 */
	public static function no_woocommerce_admin_notice() {
		?>
        <div class="notice notice-warning">
            <p><?php
				_e( '<strong>TendoPay</strong> requires <strong>WooCommerce</strong> plugin enabled.',
					'tendopay' );
				?></p>
        </div>
		<?php
	}

	/**
	 * @hook plugin_action_links_tendopay/tendopay.php 10
	 *
	 * @param array $links List of other links
	 *
	 * @return array list of plugin action links with added link to plugin settings
	 */
	public static function add_settings_link( $links ) {
		$settings_link = [
			'<a href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout&section=tendopay' ) . '">'
			. __( 'Settings', 'tendopay' ) . '</a>'
		];

		return array_merge( $settings_link, $links );
	}

	private function __wakeup() {
	}

	private function __clone() {
	}
}
