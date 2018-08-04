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

class Woocommerce_Order_Retriever {
	/** @var WC_Order $order */
	private $order;

	public function __construct( WC_Order $order ) {
		$this->order = $order;
	}

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

	private function create_line_item( WC_Order_Item $line_item ) {
		return [ 'title' => $line_item->get_name() ];
	}
}
