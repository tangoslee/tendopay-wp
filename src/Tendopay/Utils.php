<?php
/**
 * Created by PhpStorm.
 * User: robert
 * Date: 14.08.2018
 * Time: 07:26
 */

namespace Tendopay;


if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Class Utils
 * @package Tendopay
 */
class Utils {
	/**
	 * @return bool
	 */
	public static function is_woocommerce_active() {
		return in_array( 'woocommerce/woocommerce.php',
			apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) );
	}
}
