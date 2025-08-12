<?php

/**
 * Class for handling all WooCommerce activities within eForm
 *
 * A basic method to actually add the product to the cart should be
 * called by the data class
 *
 * It needs to be instantiated by the loader for all filters and hooks
 *
 * This is a singleton class
 *
 * @package eForm - WordPress Form Builder
 * @subpackage Integration\WooCommerce
 */
class IPT_eForm_WooCommerce {
	static $instant = null;

	/**
	 * Get the instantance of the singleton class
	 *
	 * @return     IPT_eForm_WooCommerce  The only instance
	 */
	public static function instant() {
		if ( null === self::$instant ) {
			self::$instant = new IPT_eForm_WooCommerce();
		}

		return self::$instant;
	}

	/**
	 * Constructor
	 *
	 * We made it private because this is a singleton class
	 *
	 * Hooks and filters necessary WooCommerce stuff
	 */
	private function __construct() {
		// Add hooks and filters
		// Filters for cart actions
		add_filter( 'woocommerce_add_cart_item', array( $this, 'add_cart_item' ), 10, 1 );
		add_filter( 'woocommerce_get_cart_item_from_session', array( $this, 'get_cart_item_from_session' ), 10, 2 );
		add_filter( 'woocommerce_get_item_data', array( $this, 'get_item_data' ), 10, 2 );

		// actions for cart items
		// @deprecated 4.7.0
		// add_action( 'woocommerce_add_order_item_meta', array( $this, 'add_order_item_meta' ), 10, 3 );
		// New hook per WC 3
		add_action( 'woocommerce_checkout_create_order_line_item', array( $this, 'checkout_create_order_line_item' ), 10, 4 );

		// actions for order item
		add_action( 'woocommerce_order_status_changed', array( $this, 'after_order_changed' ), 10, 3 );

		// Admin side filters
		add_filter( 'woocommerce_order_item_display_meta_key', [ $this, 'format_order_item_display_meta_key' ], 10, 3 );
		add_filter( 'woocommerce_order_item_display_meta_value', [ $this, 'format_order_item_display_meta_value' ], 10, 3 );
	}

	/**
	 * Conditionally format the WooCommerce Order Metadata
	 * key for eForm Products.
	 *
	 * It checks if the key is something, that eForm provides, if so then
	 * changes the formatting.
	 *
	 * @param string $display_key Display Key.
	 * @param stdClass $meta Metadata from db.
	 * @param WC_Order_Item_Product $item Order Item.
	 * @return string formatted display key if needed.
	 */
	public function format_order_item_display_meta_key( $display_key, $meta, $item ) {
		if ( '_eform_data_id' === $meta->key ) {
			return __( 'eForm Submission Id', 'ipt_fsqm' );
		}
		return $display_key;
	}

	/**
	 * Conditionally format the WooCommerce Order Metadata
	 * value for eForm Products.
	 *
	 * It checks if the key is something, that eForm provides, if so then
	 * changes the formatting.
	 *
	 * @param string $display_key Display Key.
	 * @param stdClass $meta Metadata from db.
	 * @param WC_Order_Item_Product $item Order Item.
	 * @return string formatted display key if needed.
	 */
	public function format_order_item_display_meta_value( $display_value, $meta, $item ) {
		// http://localhost:3000/wp-admin/admin.php?page=ipt_fsqm_view_submission&id=396
		if ( '_eform_data_id' === $meta->key ) {
			return sprintf( '<a href="%1$s">#%2$s</a>', admin_url( 'admin.php?page=ipt_fsqm_view_submission&id=' . $meta->value ), $meta->value );
		}
		return $display_value;
	}

	/**
	 * Filters WooCommerce Cart item add action to modify price for eForm
	 * products
	 *
	 * @param      array  $cart_item  Associative array representing the added
	 *                                item
	 *
	 * @return     array  Returns the cart item
	 */
	public function add_cart_item( $cart_item ) {
		// Adjust the value if this item was added
		if ( isset( $cart_item['eform_cart'] ) && true == $cart_item['eform_cart'] ) {
			// Adjust pricing
			if ( isset( $cart_item['eform_price'] ) && ! empty( $cart_item['eform_price'] ) ) {
				$cost = $cart_item['eform_price'];
				if ( '' != $cost && 0 != $cost ) {
					$cart_item['data']->set_price( $cost );
				}
			}
		}
		return $cart_item;
	}

	/**
	 * Filters the cart item from session variable to restore eForm data and
	 * pricing
	 *
	 * @param      array  $cart_item  Associative array representing the cart
	 *                                item
	 * @param      array  $values     Associative array representing the session
	 *                                stored cart item data
	 *
	 * @return     array  The cart item
	 */
	public function get_cart_item_from_session( $cart_item, $values ) {
		// Check if the product is added from eForm
		if ( isset( $values['eform_cart'] ) && true == $values['eform_cart'] ) {
			// Copy over the eForm attributes
			$cart_item['eform_cart'] = true;
			if ( isset( $values['eform_price'] ) ) {
				$cart_item['eform_price'] = $values['eform_price'];
			}
			$cart_item['eform_attr'] = isset( $values['eform_attr'] ) ? $values['eform_attr'] : array();
			$cart_item['eform_data_id'] = $values['eform_data_id'];

			// Adjust pricing
			if ( isset( $cart_item['eform_price'] ) && ! empty( $cart_item['eform_price'] ) ) {
				$cost = $cart_item['eform_price'];
				if ( '' != $cost && 0 != $cost ) {
					$cart_item['data']->set_price( $cost );
				}
			}
		}

		return $cart_item;
	}

	/**
	 * Adds a product safely to the cart
	 *
	 * Checks if WooCommerce is actually there or not
	 *
	 * @param      int    $data_id   The data_id of eForm submission
	 * @param      int    $prod_id   The product ID for WooCommerce
	 * @param      array  $data_arr  Associative array for the cart item
	 */
	public function add_to_cart( $data_id, $prod_id, $data_arr, $quantity = 1 ) {
		// Check for the WooCommerce Presence
		global $woocommerce;
		if ( ! $woocommerce ) {
			return;
		}

		// Now add to the cart
		// Also add the item data
		$woocommerce->cart->add_to_cart( $prod_id, $quantity, 0, array(), $data_arr );
	}

	/**
	 * Modify the WooCommerce item data to include eForm attributes Checks for
	 * the presense of eForm cart, if there then adds eForm attributes to the
	 * item data
	 *
	 * @param      array  $item_data  The item data
	 * @param      array  $cart_item  The cart item
	 *
	 * @return     array  The item data.
	 */
	public function get_item_data( $item_data, $cart_item ) {
		if ( isset( $cart_item['eform_cart'] ) && true == $cart_item['eform_cart'] ) {
			if ( ! empty( $cart_item['eform_attr'] ) ) {
				$item_data = array_merge( $item_data, $cart_item['eform_attr'] );
			}
		}

		return $item_data;
	}

	/**
	 * Adds order item meta when the order is placed This adds meta data like
	 * product attribute ( from eForm math variables ) and eform data id to the
	 * order.
	 *
	 * @deprecated 4.7.0 Now we support only WC3+
	 *
	 * @param      int    $item_id    The item identifier
	 * @param      array  $cart_item  The cart item
	 */
	public function add_order_item_meta( $item_id, $cart_item ) {
		if ( isset( $cart_item['eform_cart'] ) && true == $cart_item['eform_cart'] ) {
			// loop and show the attributes
			if ( ! empty( $cart_item['eform_attr'] ) ) {
				foreach ( (array) $cart_item['eform_attr'] as $attr ) {
					wc_add_order_item_meta( $item_id, $attr['name'], $attr['display'] );
				}
			}
			// Add the data_id in hidden attribute
			wc_add_order_item_meta( $item_id, '_eform_data_id', $cart_item['eform_data_id'] );
		}
	}

	/**
	 * Adds order item meta when the order is placed. This adds metadata like
	 * product attribute and eform data Id to the order.
	 *
	 * This uses the new `woocommerce_checkout_create_order_line_item` hook
	 * for new WooCommerce system.
	 *
	 * @param WC_Order_Item_Product $item The WooCommerce order item.
	 * @param string|int $item_id Cart item unique hash key.
	 * @param array $cart_item The cart item (associative array as we added it).
	 * @param WP_Order $order The WooCommerce order object.
	 * @return void
	 */
	public function checkout_create_order_line_item( $item, $item_id, $cart_item, $order ) {
		// Check if this is an eForm thing
		if ( isset( $cart_item['eform_cart'] ) && true == $cart_item['eform_cart'] ) {
			// loop and show the attributes
			if ( ! empty( $cart_item['eform_attr'] ) ) {
				foreach ( (array) $cart_item['eform_attr'] as $attr ) {
					$item->update_meta_data( $attr['name'], $attr['display'] );
				}
			}
			// Add the data_id in hidden attribute
			$item->update_meta_data( '_eform_data_id', $cart_item['eform_data_id'] );
		}
	}

	/**
	 * Hooks into WooCommerce order change event to check if order has any eForm
	 * item and if so modifies the eForm data submission paid flag accordingly.
	 * This essentially locks or unlocks the eForm submission status
	 *
	 * @param      int     $order_id    The order identifier
	 * @param      string  $old_status  The old status
	 * @param      string  $new_status  The new status
	 */
	public function after_order_changed( $order_id, $old_status, $new_status ) {
		$order = wc_get_order( $order_id );
		// Get items
		$items = $order->get_items();
		if ( empty( $items ) ) {
			// No items, so do not do anything
			return;
		}

		// Loop through all items and see if eform is there
		foreach ( $items as $item ) {
			// If not eForm submission, then do not do anything
			$data_id = null;
			if ( isset( $item['item_meta']['_eform_data_id'] ) ) {
				$data_id = $item['item_meta']['_eform_data_id'];
			} else {
				// WooCommerce 3.0 Compatibility
				if ( is_a( $item, 'WC_Order_Item_Product' ) ) {
					$data_id = $item->get_meta( '_eform_data_id', true );
				}
			}

			if ( empty( $data_id ) ) {
				continue;
			}

			// So it is eForm order
			$data = new IPT_FSQM_Form_Elements_Data( $data_id );
			if ( is_null( $data->data_id ) ) {
				// No actual data
				continue;
			}

			// Now check with the settings
			$paid_flat_state = $data->settings['payment']['woocommerce']['paid_flag_state'];
			// backward compat
			if ( ! is_array( $paid_flat_state ) ) {
				$paid_flat_state = [ $paid_flat_state ];
			}
			if ( in_array( $new_status, $paid_flat_state ) ) {
				// Set to paid
				$data->set_paid_status( 1 );
				// Send user email
				if ( $data->settings['payment']['woocommerce']['send_email_after_checkout'] ) {
					$data->send_user_notification_email();
				}
			} else {
				// Set to unpaid
				$data->set_paid_status( 0 );
			}
		}
	}
}
