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
use WC_Data_Exception;
use \WC_Payment_Gateway;
use \WC_Order;

class Gateway extends WC_Payment_Gateway {
	const TENDOPAY_URL = 'https://tendopay.example.co/make/payment';
	const TENDOPAY_VIEW_URL_PATTERN = 'https://tendopay.example.co/view/transaction/%s';
	const GATEWAY_ID = 'tendopay';

	function __construct() {
		$this->id         = self::GATEWAY_ID;
		$this->has_fields = false;

		$this->init_form_fields();
		$this->init_settings();

		$this->title        = $this->get_option( 'method_title' );
		$this->method_title = $this->get_option( 'method_title' );
		$this->description  = $this->get_option( 'method_description' );

		$this->view_transaction_url = self::TENDOPAY_VIEW_URL_PATTERN;

		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array(
			$this,
			'process_admin_options'
		) );
	}

	public function init_form_fields() {
		$this->form_fields = array(
			'enabled'            => array(
				'title'   => __( 'Enable/Disable', 'tendopay' ),
				'type'    => 'checkbox',
				'label'   => __( 'Enable Tendopay Integration', 'tendopay' ),
				'default' => 'yes'
			),
			'method_title'       => array(
				'title'       => __( 'Tendopay ID', 'tendopay' ),
				'type'        => 'text',
				'description' => __( 'This controls the title which the user sees during checkout.', 'tendopay' ),
				'default'     => __( 'Pay with Tendopay', 'tendopay' ),
				'desc_tip'    => true,
			),
			'method_description' => array(
				'title'       => __( 'Payment method description', 'tendopay' ),
				'description' => __( 'Additional information displayed to the customer after selecting Tendopay method', 'tendopay' ),
				'type'        => 'textarea',
				'default'     => ''
			),
			'tendo_pay_id'       => array(
				'title'   => __( 'Tendo Pay ID', 'tendopay' ),
				'type'    => 'text',
				'default' => ''
			),
			'tendo_secret'       => array(
				'title'   => __( 'Secret', 'tendopay' ),
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
	 * @throws WC_Data_Exception
	 */
	public function process_payment( $order_id ) {
		$order = new WC_Order( (int) $order_id );

		$authTp     = new Authorization_Endpoint( $order );
		$auth_token = $authTp->request_token();

		$descTp          = new Description_Endpoint( $auth_token, $order );
		$order_retriever = new Woocommerce_Order_Retriever( $order );
		$descTp->set_description( $order_retriever->get_order_details() );

		$redirect_args = [
			'amount'                => $order->get_total(),
			'authorisation_token'   => $auth_token,
			'customer_reference_1'  => $order->get_id(),
			'customer_reference_2'  => $order->get_order_key(),
			'redirect_url'          => add_query_arg( [ 'key' => 'val' ], get_site_url( get_current_blog_id(), 'tendopay-result' ) ),
			'tendo_pay_merchant_id' => '', // todo bind tendo pay merchant id
			'vendor'                => get_bloginfo( 'blogname' )
		];

		$hash_calc             = new Hash_Calculator( '' );
		$redirect_args_hash    = $hash_calc->calculate( $redirect_args );
		$redirect_args['hash'] = $redirect_args_hash;

		wc_reduce_stock_levels( $order->get_id() );

		global $woocommerce;
		$woocommerce->cart->empty_cart();

		$redirect_args = urlencode_deep( $redirect_args );

		$redirect_url = add_query_arg( $redirect_args, self::TENDOPAY_URL );

		return array(
			'result'   => 'success',
			'redirect' => $redirect_url
		);
	}
}
