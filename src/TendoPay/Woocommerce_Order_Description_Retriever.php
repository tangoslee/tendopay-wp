<?php
/**
 * Created by PhpStorm.
 * User: robert
 * Date: 04.08.2018
 * Time: 06:35
 */

namespace TendoPay;

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

use WC_Order;
use WC_Order_Item;
use WC_Product;

/**
 * This class helps converting woocommerce order into an array, that will is later converted into JSON used for
 * description endpoint.
 *
 * @package TendoPay
 */
class Woocommerce_Order_Description_Retriever {
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
	 * Transforms order details into an array ready to be encoded as JSON to be used for description endpoint in the
	 * following form:
	 *
	 * ```
	 * {
	 *   "items":
	 *     [{
	 *       "id": 123,
	 *       "title":"Beanie",
	 *       "description": "this is a Beanie",
	 *       "SKU": "1234", //optional
	 *       "price": "40.00",
	 *       "quantity": "2"
	 *     }]
	 * }
	 * ```
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

		return apply_filters( 'tendopay_order_details', $order_details, $this->order );
	}

	/**
	 * Creates single line item in the description array in the following form:
	 * ```
	 * {
	 *   "id": 123,
	 *   "title":"Beanie",
	 *   "description": "this is a Beanie",
	 *   "SKU": "1234", //optional
	 *   "price": "40.00",
	 *   "quantity": "2"
	 * }
	 * ```
	 *
	 * @param WC_Order_Item $line_item line item from the order
	 *
	 * @return array converted line item for the description doc
	 */
	private function create_line_item( WC_Order_Item $line_item ) {
		$line_item_data = $line_item->get_data();

		/** @var WC_Product $product */
		$product = wc_get_product( $line_item_data['product_id'] );

		$description_line_item = [
			'id'          => $line_item_data['id'],
			'title'       => $line_item_data['name'],
			'description' => $product->get_description(),
			'SKU'         => $product->get_sku(),
			'price'       => $line_item_data['total'] + $line_item_data['total_tax'],
			'quantity'    => $line_item_data['quantity'],
		];

		return apply_filters( 'tendopay_description_line_item', $description_line_item, $line_item );
	}
}
