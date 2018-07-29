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
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

if ( ! class_exists( 'Tendopay' ) ) {

	include_once "src/class-tendopay-rewriter.php";
	include_once "src/class-tendopay.php";

	/**
	 * The main function responsible for plugin's initialization.
	 * You can access the plugin simply by using <?php $tendopay = tendopay(); ?>
	 */
	function tendopay() {
		static $tendopay;

		if ( ! isset( $tendopay ) ) {
			$tendopay = new Tendopay();
		}

		return $tendopay;
	}

	/**
	 * Initialize.
	 */

	tendopay();

}
