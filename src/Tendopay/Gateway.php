<?php
/**
 * Created by PhpStorm.
 * User: robert
 * Date: 02.01.18
 * Time: 06:10
 */

namespace Tendopay;

use Tendopay\API\Authorization_Endpoint;
use Tendopay\API\Description_Endpoint;
use Tendopay\API\Hash_Calculator;
use Tendopay\API\Tendopay_API;
use WC_Data_Exception;
use \WC_Payment_Gateway;
use \WC_Order;

/**
 * Class Gateway
 * @package Tendopay
 */
class Gateway extends WC_Payment_Gateway {
	/**
	 *
	 */
	const GATEWAY_ID = 'tendopay';

	/**
	 * Gateway constructor.
	 */
	function __construct() {
		$this->id         = self::GATEWAY_ID;
		$this->has_fields = false;

		$this->init_form_fields();
		$this->init_settings();

		$this->title        = $this->get_option( 'method_title' );
		$this->method_title = $this->get_option( 'method_title' );
		$this->description  = $this->get_option( 'method_description' );

		$this->view_transaction_url = Tendopay_API::get_view_uri_pattern();

		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array(
			$this,
			'process_admin_options'
		) );
	}

	/**
	 *
	 */
	public function init_form_fields() {
		$this->form_fields = array(
			'enabled'               => array(
				'title'   => __( 'Enable/Disable', 'tendopay' ),
				'type'    => 'checkbox',
				'label'   => __( 'Enable Tendopay Integration', 'tendopay' ),
				'default' => 'yes'
			),
			'method_title'          => array(
				'title'       => __( 'Payment gateway title', 'tendopay' ),
				'type'        => 'text',
				'description' => __( 'This controls the title which the user sees during checkout.', 'tendopay' ),
				'default'     => __( 'Pay with Tendopay', 'tendopay' ),
				'desc_tip'    => true,
			),
			'method_description'    => array(
				'title'       => __( 'Payment method description', 'tendopay' ),
				'description' => __( 'Additional information displayed to the customer after selecting Tendopay method', 'tendopay' ),
				'type'        => 'textarea',
				'default'     => '',
				'desc_tip'    => true,
			),
			'tendo_sandbox_enabled' => array(
				'title'       => __( 'Enable SANDBOX', 'tendopay' ),
				'description' => __( 'Enable SANDBOX if you want to test integration with Tendopay without real transactions.' ),
				'type'        => 'checkbox',
				'default'     => 'no',
				'desc_tip'    => true,
			),
			'tendo_pay_merchant_id' => array(
				'title'   => __( 'Tendo Pay Merchant ID', 'tendopay' ),
				'type'    => 'text',
				'default' => ''
			),
			'tendo_secret'          => array(
				'title'   => __( 'Secret', 'tendopay' ),
				'type'    => 'password',
				'default' => ''
			),
			'tendo_client_id'       => array(
				'title'   => __( 'API Client ID', 'tendopay' ),
				'type'    => 'text',
				'default' => ''
			),
			'tendo_client_secret'   => array(
				'title'   => __( 'API Client Secret', 'tendopay' ),
				'type'    => 'password',
				'default' => ''
			),
		);
	}

	/**
	 * @param int $order_id
	 *
	 * @return array
	 * @throws Exceptions\TendoPay_Integration_Exception
	 * @throws \GuzzleHttp\Exception\GuzzleException
	 */
	public function process_payment( $order_id ) {
		$order = new WC_Order( (int) $order_id );

		$authTp     = new Authorization_Endpoint( $order );
		$auth_token = $authTp->request_token();

		$descTp          = new Description_Endpoint( $auth_token, $order );
		$order_retriever = new Woocommerce_Order_Retriever( $order );
		$descTp->set_description( $order_retriever->get_order_details() );

		$redirect_args = [
			'amount'                => (int) $order->get_total(),
			'authorisation_token'   => $auth_token,
			'customer_reference_1'  => (string) $order->get_id(),
			'customer_reference_2'  => (string) $order->get_order_key(),
			'redirect_url'          => get_site_url( get_current_blog_id(), 'tendopay-result' ),
			'tendo_pay_merchant_id' => (string) $this->get_option( 'tendo_pay_merchant_id' ),
			'vendor'                => get_bloginfo( 'blogname' )
		];

		$hash_calc             = new Hash_Calculator( $this->get_option( 'tendo_secret' ) );
		$redirect_args_hash    = $hash_calc->calculate( $redirect_args );
		$redirect_args['hash'] = $redirect_args_hash;

		wc_reduce_stock_levels( $order->get_id() );

		global $woocommerce;
		$woocommerce->cart->empty_cart();

		$redirect_args = urlencode_deep( $redirect_args );

		$redirect_url = add_query_arg( $redirect_args, Tendopay_API::get_redirect_uri() );

		return array(
			'result'   => 'success',
			'redirect' => $redirect_url
		);
	}
}
