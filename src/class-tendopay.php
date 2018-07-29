<?php

/**
 * Created by PhpStorm.
 * User: robert
 * Date: 16.01.18
 * Time: 22:03
 */

if ( ! class_exists( 'Tendopay' ) ) {
	class Tendopay {
		/**
		 * Tendopay constructor.
		 */
		public function __construct() {
			$this->register_hooks();
		}

		/**
		 * Registers required hooks.
		 */
		public function register_hooks() {
			add_action( 'plugins_loaded', array( $this, 'init_gateway' ) );
			add_filter( 'woocommerce_payment_gateways', array( $this, 'register_gateway' ) );

			// add endpoint for RedirectUrl
			add_action( 'plugins_loaded', array( 'Tendopay_Rewriter', 'get_instance' ) );
			add_action( 'admin_post_tendopay-result', array( $this, 'handle_redirect_from_tendopay' ) );
			add_action( 'admin_post_nopriv_tendopay-result', array( $this, 'handle_redirect_from_tendopay' ) );
		}

		function handle_redirect_from_tendopay() {
			$order     = WC_Order_Factory::get_order( (int) $_GET['order_id'] );
			$order_key = $_GET['key'];

			// todo implementation
		}


		/**
		 * Registers Tendopay gateway in the system.
		 *
		 * @param $methods
		 *
		 * @return array
		 */
		public function register_gateway( $methods ) {
            $methods[] = 'Tendopay_Gateway';

			return $methods;
		}

		/**
		 * Initializes gateway
		 */
		public function init_gateway() {
			include_once dirname(__FILE__) . "/class-tendopay-gateway.php";
		}
	}
}
