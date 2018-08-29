<?php
/**
 * Created by PhpStorm.
 * User: robert
 * Date: 02.01.18
 * Time: 06:10
 */

namespace TendoPay;

use TendoPay\API\Authorization_Endpoint;
use TendoPay\API\Description_Endpoint;
use TendoPay\API\Hash_Calculator;
use TendoPay\Exceptions\TendoPay_Integration_Exception;
use \WC_Payment_Gateway;
use \WC_Order;

/**
 * This class implements the woocommerce gateway mechanism.
 *
 * @package TendoPay
 */
class Gateway extends WC_Payment_Gateway {
	const TENDOPAY_PAYMENT_INITIATED_KEY = '_tendopay_payment_initiated';

	const OPTION_METHOD_TITLE = 'method_title';
	const OPTION_ENABLED = 'enabled';
	const OPTION_METHOD_DESC = 'method_description';
	const OPTION_TENDOPAY_SANDBOX_ENABLED = 'tendo_sandbox_enabled';
	const OPTION_TENDOPAY_VENDOR_ID = 'tendo_pay_merchant_id';
	const OPTION_TENDOPAY_SECRET = 'tendo_secret';
	const OPTION_TENDOPAY_CLIENT_ID = 'tendo_client_id';
	const OPTION_TENDOPAY_CLIENT_SECRET = 'tendo_client_secret';

	/**
	 * Unique ID of the gateway.
	 */
	const GATEWAY_ID = 'tendopay';

	/**
	 * Prepares the gateway configuration.
	 */
	function __construct() {
		$this->id         = self::GATEWAY_ID;
		$this->has_fields = false;

		$this->init_form_fields();
		$this->init_settings();

		$this->title             = $this->get_option( self::OPTION_METHOD_TITLE );
		$this->method_title      = $this->get_option( self::OPTION_METHOD_TITLE );
		$this->description       = $this->get_option( self::OPTION_METHOD_DESC );
		$this->order_button_text = apply_filters( 'tendopay_order_button_text',
			__( 'Buy now, pay later with TendoPay', 'tendopay' ) );

		$this->maybe_add_payment_initiated_notice();
		add_action( 'before_woocommerce_pay', [ $this, 'maybe_add_payment_failed_notice' ] );

		$this->view_transaction_url = Constants::get_view_uri_pattern();

		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array(
			$this,
			'process_admin_options'
		) );
	}

	public function maybe_add_payment_failed_notice() {
		$payment_failed = $_GET[ Constants::PAYMANET_FAILED_QUERY_PARAM ];

		if ( $payment_failed ) {
			$payment_failed_notice =
				__( "The payment attempt with TendoPay has failed. Please try again or choose other payment method.",
					'tendopay' );
			wc_print_notice( $payment_failed_notice, 'error' );
		}
	}

	private function maybe_add_payment_initiated_notice() {
		$order_id          = absint( get_query_var( 'order-pay' ) );
		$payment_initiated = get_post_meta( $order_id, self::TENDOPAY_PAYMENT_INITIATED_KEY, true );

		if ( $payment_initiated ) {
			$payment_initiated_notice = __( "<strong>Warning!</strong><br><br>You've aready initiated payment attempt with TendoPay once. If you continue you may end up finalizing two separate payments for single order.<br><br>Are you sure you want to conitnue?", 'tendopay' );

			$notices = wc_get_notices();
			if ( isset( $notices['notice'] ) && ! empty( $notices['notice'] ) ) {
				$payment_initiated_notice .= "<br><br>";
			} else {
				$notices['notice'] = [];
			}

			array_unshift( $notices['notice'], $payment_initiated_notice );
			wc_set_notices( $notices );
		}
	}

	/**
	 * Prepares settings forms for plugin's settings page.
	 */
	public function init_form_fields() {
		$this->form_fields = array(
			self::OPTION_ENABLED                  => array(
				'title'   => __( 'Enable/Disable', 'tendopay' ),
				'type'    => 'checkbox',
				'label'   => __( 'Enable TendoPay Integration', 'tendopay' ),
				'default' => 'yes'
			),
			self::OPTION_METHOD_TITLE             => array(
				'title'       => __( 'Payment gateway title', 'tendopay' ),
				'type'        => 'text',
				'description' => __( 'This controls the title which the user sees during checkout.', 'tendopay' ),
				'default'     => __( 'Pay with TendoPay', 'tendopay' ),
				'desc_tip'    => true,
			),
			self::OPTION_METHOD_DESC              => array(
				'title'       => __( 'Payment method description', 'tendopay' ),
				'description' => __( 'Additional information displayed to the customer after selecting TendoPay method', 'tendopay' ),
				'type'        => 'textarea',
				'default'     => '',
				'desc_tip'    => true,
			),
			self::OPTION_TENDOPAY_SANDBOX_ENABLED => array(
				'title'       => __( 'Enable SANDBOX', 'tendopay' ),
				'description' => __( 'Enable SANDBOX if you want to test integration with TendoPay without real transactions.', 'tendopay' ),
				'type'        => 'checkbox',
				'default'     => 'no',
				'desc_tip'    => true,
			),
			self::OPTION_TENDOPAY_VENDOR_ID       => array(
				'title'   => __( 'Tendo Pay Merchant ID', 'tendopay' ),
				'type'    => 'text',
				'default' => ''
			),
			self::OPTION_TENDOPAY_SECRET          => array(
				'title'   => __( 'Secret', 'tendopay' ),
				'type'    => 'password',
				'default' => ''
			),
			self::OPTION_TENDOPAY_CLIENT_ID       => array(
				'title'   => __( 'API Client ID', 'tendopay' ),
				'type'    => 'text',
				'default' => ''
			),
			self::OPTION_TENDOPAY_CLIENT_SECRET   => array(
				'title'   => __( 'API Client Secret', 'tendopay' ),
				'type'    => 'password',
				'default' => ''
			),
		);
	}

	/**
	 * Processes the payment. This method is called right after customer clicks the `Place order` button.
	 *
	 * @param int $order_id ID of the order that customer wants to pay.
	 *
	 * @return array status of the payment and redirect url. The status is always `success` because if there was
	 *         any problem, this method would rethrow an exception.
	 *
	 * @throws TendoPay_Integration_Exception rethrown either from {@link Authorization_Endpoint}
	 *         or {@link Description_Endpoint}
	 * @throws \GuzzleHttp\Exception\GuzzleException  when there was a problem in communication with the API (originally
	 *         thrown by guzzle http client)
	 */
	public function process_payment( $order_id ) {
		$order = new WC_Order( (int) $order_id );

		$auth_token = null;

		try {
			$auth_token = Authorization_Endpoint::request_token( $order );
			Description_Endpoint::set_description( $auth_token, $order );
		} catch ( \Exception $exception ) {
			error_log( $exception );
			throw new TendoPay_Integration_Exception(
				__( 'Could not communicate with TendoPay', 'tendopay' ), $exception );
		}

		$redirect_args = [
			Constants::AMOUNT_PARAM       => (int) $order->get_total(),
			Constants::AUTH_TOKEN_PARAM   => $auth_token,
			Constants::ORDER_ID_PARAM     => (string) $order->get_id(),
			Constants::ORDER_KEY_PARAM    => (string) $order->get_order_key(),
			Constants::REDIRECT_URL_PARAM => Redirect_Url_Rewriter::get_instance()->get_redirect_url(),
			Constants::VENDOR_ID_PARAM    => (string) $this->get_option( self::OPTION_TENDOPAY_VENDOR_ID ),
			Constants::VENDOR_PARAM       => get_bloginfo( 'blogname' )
		];

		$redirect_args = apply_filters( 'tendopay_process_payment_redirect_args', $redirect_args, $order, $this,
			$auth_token );

		$hash_calc                              = new Hash_Calculator(
			$this->get_option( Gateway::OPTION_TENDOPAY_SECRET ) );
		$redirect_args_hash                     = $hash_calc->calculate( $redirect_args );
		$redirect_args[ Constants::HASH_PARAM ] = $redirect_args_hash;

		$redirect_args = apply_filters( 'tendopay_process_payment_redirect_args_after_hash', $redirect_args, $order,
			$this, $auth_token );

		wc_reduce_stock_levels( $order->get_id() );

		global $woocommerce;
		$woocommerce->cart->empty_cart();

		$redirect_args = urlencode_deep( $redirect_args );

		$redirect_url = add_query_arg( $redirect_args, Constants::get_redirect_uri() );
		$redirect_url = apply_filters( 'tendopay_process_payment_redirect_url', $redirect_url, $redirect_args,
			$order, $this, $auth_token );

		update_post_meta( $order_id, self::TENDOPAY_PAYMENT_INITIATED_KEY, true );
		wc_clear_notices();

		return array(
			'result'   => 'success',
			'redirect' => $redirect_url
		);
	}
}
