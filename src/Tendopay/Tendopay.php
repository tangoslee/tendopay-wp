<?php

/**
 * Created by PhpStorm.
 * User: robert
 * Date: 16.01.18
 * Time: 22:03
 */

namespace Tendopay;

use Tendopay\API\Tendopay_API;
use Tendopay\API\Verification_Endpoint;
use \WC_Order_Factory;

class Tendopay {
	private static $instance;

	/**
	 * Tendopay constructor.
	 */
	private function __construct() {
		$this->register_hooks();
	}

	public static function get_instance() {
		if ( self::$instance === null ) {
			self::$instance = new Tendopay();
		}

		return self::$instance;
	}

	public static function install() {
		Redirect_Url_Rewriter::get_instance()->add_rules();
		flush_rewrite_rules();
	}

	public static function uninstall() {
		/** @var \WP_Rewrite $wp_rewrite */
		global $wp_rewrite;
		unset( $wp_rewrite->non_wp_rules[ Tendopay_API::REDIRECT_URL_PATTERN ] );
		flush_rewrite_rules();
	}

	/**
	 * Registers required hooks.
	 */
	public function register_hooks() {
		add_action( 'plugins_loaded', array( $this, 'init_gateway' ) );
		add_filter( 'woocommerce_payment_gateways', array( $this, 'register_gateway' ) );
		add_action( 'plugins_loaded', array( Redirect_Url_Rewriter::class, 'get_instance' ) );
		add_action( 'admin_post_tendopay-result', array( $this, 'handle_redirect_from_tendopay' ) );
		add_action( 'admin_post_nopriv_tendopay-result', array( $this, 'handle_redirect_from_tendopay' ) );
	}

	function handle_redirect_from_tendopay() {
		$order     = WC_Order_Factory::get_order( (int) $_REQUEST['customer_reference_1'] );
		$order_key = $_REQUEST['customer_reference_2'];

		if ( $order->get_order_key() !== $order_key ) {
			wp_die( new \WP_Error( 'wrong-order-key', 'Wrong order key provided' ),
				__( 'Wrong order key provided', 'tendopay' ), 403 );
		}

		$gateway_options = get_option( "woocommerce_" . Gateway::GATEWAY_ID . "_settings" );

		$tendo_pay_merchant_id       = $_REQUEST['tendo_pay_merchant_id'];
		$local_tendo_pay_merchant_id = $gateway_options['tendo_pay_merchant_id'];
		if ( $tendo_pay_merchant_id !== $local_tendo_pay_merchant_id ) {
			wp_die( new \WP_Error( 'wrong-merchant-id', 'Malformed payload' ),
				__( 'Malformed payload', 'tendopay' ), 403 );
		}

		try {
			$posted_data = $_REQUEST;
			if ( isset( $posted_data['action'] ) ) {
				unset( $posted_data['action'] );
			}
			$verification      = new Verification_Endpoint();
			$payment_completed = $verification->verify_payment( $order, $posted_data );
		} catch ( \Exception $exception ) {
			error_log( $exception->getMessage() );
			error_log( $exception->getTraceAsString() );
			wp_die( new \WP_Error( 'tendopay-integration-error', 'Could not communicate with Tendopay properly' ),
				__( 'Could not communicate with Tendopay properly', 'tendopay' ), 403 );
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
	 * Registers Tendopay gateway in the system.
	 *
	 * @param $methods
	 *
	 * @return array
	 */
	public function register_gateway( $methods ) {
		$methods[] = Gateway::class;

		return $methods;
	}

	/**
	 * Initializes gateway
	 */
	public function init_gateway() {
		include_once dirname( __FILE__ ) . "/Gateway.php";
	}

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
