<?php
/**
 * Created by PhpStorm.
 * User: robert
 * Date: 02.01.18
 * Time: 06:10
 */

namespace Tendopay;

use \WC_Payment_Gateway;
use \WC_Order;

class Gateway extends WC_Payment_Gateway {
	const GATEWAY_ID = "tendopay";

	function __construct() {
		$this->id         = self::GATEWAY_ID;
		$this->has_fields = false;

		$this->init_form_fields();
		$this->init_settings();

		$this->title        = $this->get_option( 'method_title' );
		$this->method_title = $this->get_option( 'method_title' );
		$this->description  = $this->get_option( 'method_description' );

		$this->instructions = "Some instructions";

		$this->icon = "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACAAAAAgCAYAAABzenr0AAAEwklEQVRYR7XX6VMbZRwH8B"
		              . "/haDkSDjkDlJYBBJuE0iKd2tpWOYROnU7V6Wh1rG2x8XWHGf8GZxzH+kpr1dFpBVFbR8cWB4r1niKHKUcoWGoKxJBs"
		              . "Dp5tdnPtrvM8GyjNbliWkefNTvZ58ns+z/fZ3WwSIKZdty6+IwC0x55X+iwAgCDwmpSkxIkIL5xrqs68pPQd3J8QO6"
		              . "jX6kOGLTotL+CSa28cJ4BlxgUlRTmA7oeYIBs2txhzLipVkAImfGibPkMb4tQBeF6AqX9cUFmWB2FeAJoOMcGAMkIK"
		              . "GPeh4sJ0bSDCK+Ef6seAe3Nu2FNTCBTDEQRCIYYNhs1tqyQhAfSMedGWoox1A/YbCiHEAbijCJ8vxATDQXObMVd2Oy"
		              . "SAa6NeVLZewLwbGk1FZPUYgZOI8AJ4fSGGDQTNh3dIERLA1VseVKbXkgSunn971W04dKZjuR9vwey8G1p26IHjBSnC"
		              . "GyDbEYuQAL63uNHWYh0BBMJrvw4EXgCn0wttdXrA1+8DhAAUw0OEE8DjY5lAECdRsLwdEsB3I25UXioClBJYWv5Tr5"
		              . "0FQRDAFQXg8ysRwYgAbpYn20F5WIZlg+Yju0SEBPDtEIXKyzIJgA2pSEAQgHJ6obVOv1w0FkGxHHA8gMNJDx4y5T4u"
		              . "C/hmiEIVUcA1hWtgKYEDJ84CfnBRCx7FW1ejSYCEhGTmaH1+uizgyqALVWzNIgn4A5xiwZUDFlEQOI4H8SEqRI8PRm"
		              . "zelAiZacl4G+ij9Xk6WcDXA05UtS2bAHo+XP0uUKUDgNbXO2Bzkgam7nrp5xvy5QFf3XSiR8uztWyEB5pVl4ASSJua"
		              . "CKlJGrg946Vf2B0H0P3HAqqpyBEBzP8MSBMB1r899LE9BfIJdP3uQNsrHyEAxESUFqWqX5eWRADj0276xScK5QGf/+"
		              . "ZAhioRsOgXAX6PE+4O9IGh9biqCWMHZ6aLgLEpN318bxzApV/+RcbqXC0b5sHnj8Dkj5cBOe6RWkkpm8D07ClyxG2g"
		              . "811VoJb2DkhN1sDoJEW//GSRfAIXf7YjY00eAcxYrWD7sw+MKyZVNWPM4CycAAZYXfQr+/XygM9+sqPax0TAlGUE5i"
		              . "y/gunwSUiMrnplzcGuc6o8zdEELBMu+tUDcQCf3phHtdvzCYBy34epG5ch6F+E7NJKKDHtk4WsVZGtTSYJWMad9ImD"
		              . "xfIJfNI/j+qMIsBLh0lt6s4YeGenwe9dgKqDz0Fadj45P/SFugSaogmMjDrpk0/HAXx8fQ7tNBUQgAeJgKU22dtJki"
		              . "iorl/roh8al6MTExi+tUCfaiyRT+Cjvlm0q7ZQy4R5sPT3QEpGJpkQb8Pt3k7Qm/ZBbrmBFB7pVpdAY3sHpCVrYMji"
		              . "oE83lcoDLvTOovoowDZ9B2aH+snkuGGI3rh3XavHX8IJYMCgxUG3N8cBnO+1oYZaPUnAjUIQwivv6wLDEbPk5eGvL9"
		              . "9ThWk8LSYwYLHTZ5rL5BP44AcbaqgTAR4UUjWB0uAcXYoIGLHT5mfiAXpsqGGnCNiIRgDDdtrcGgfwfo8N7d5gwM1h"
		              . "O/1GPMBbnYMXdFl5xzZi9Us1kc/V/eZL9eQPsOSlFADwu1pB9LgRDj8ALOAf2XiAjZg0bs3/ALzRkT/CPcj3AAAAAE"
		              . "lFTkSuQmCC";

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
			'tendo_pay_id' => array(
				'title'       => __( 'Tendo Pay ID', 'tendopay' ),
				'type'        => 'text',
				'default'     => ''
			),
			'tendo_secret' => array(
				'title'       => __( 'Secret', 'tendopay' ),
				'type'        => 'password',
				'default'     => ''
			),
		);
	}

	public function process_payment( $order_id ) {
		global $woocommerce;

		$order_id = (int) $order_id;

		$order = new WC_Order( $order_id );

		$tendopay_url = '';

//		return array(
//			'result'   => 'success',
//			'redirect' => $tendopay_url
//		);

		return array(
			'result'   => 'success',
			'redirect' => $this->get_return_url( $order )
		);
	}
}
