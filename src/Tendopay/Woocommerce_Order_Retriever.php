<?php
/**
 * Created by PhpStorm.
 * User: robert
 * Date: 04.08.2018
 * Time: 06:35
 */

namespace Tendopay;

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

use WC_Order;
use WC_Order_Item;

/**
 * This class helps converting woocommerce order into an array, that will is later converted into JSON used for
 * description endpoint.
 *
 * @package Tendopay
 */
class Woocommerce_Order_Retriever {
	/** @var WC_Order $order */
	private $order;


	/**
	 * Initializes class' fields.
	 *
	 * @param WC_Order $order Order from which to retrieve data
	 */
	public function __construct( WC_Order $order ) {
		$this->order = $order;
	}

	/**
	 * Transforms order details into an array ready to be encoded as JSON to be used for description endpoint.
	 *
	 * @return array order details in form of an array
	 */
	public function get_order_details() {
		$order_details = [
			'items' => []
		];

		$line_items = $this->order->get_items();
		foreach ( $line_items as $item ) {
			$order_details['items'][] = $this->create_line_item( $item );
		}

		return $order_details;
	}

	/**
	 * Creates single line item in the description array.
	 *
	 * @param WC_Order_Item $line_item line item from the order
	 *
	 * @return array converted line item for the description doc
	 */
	private function create_line_item( WC_Order_Item $line_item ) {
		return [ 'title' => $line_item->get_name() ];
	}
}
