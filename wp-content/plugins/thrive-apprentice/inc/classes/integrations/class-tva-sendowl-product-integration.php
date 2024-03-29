<?php
/**
 * Created by PhpStorm.
 * User: dan bilauca
 * Date: 15-Apr-19
 * Time: 03:37 PM
 */

/**
 * Class TVA_SendOwl_Product_Integration
 * - implements TVA_Integration methods
 */
class TVA_SendOwl_Product_Integration extends TVA_SendOwl_Integration {

	protected $_membership_key = 'sendowl';

	protected function init_items() {

		$items = array();

		$products = TVA_SendOwl::get_products();

		if ( is_array( $products ) && ! empty( $products ) ) {
			foreach ( $products as $product ) {
				try {
					$items[] = new TVA_Integration_Item( $product['id'], $product['name'] );
				} catch ( Exception $e ) {

				}
			}
		}

		$this->set_items( $items );
	}

	protected function _get_item_from_membership( $key, $value ) {

		$product = TVA_SendOwl::get_product_by_id( $value );

		return new TVA_Integration_Item( $product['id'], $product['name'] );
	}
}
