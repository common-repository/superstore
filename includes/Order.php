<?php

namespace Binarithm\Superstore;

use WP_Error;
use WC_Order;
use WC_Order_Item_Product;
use WC_Order_Item_Tax;
use WC_Order_Item_Shipping;
use WC_Coupon;
use WC_Order_Item_Coupon;
use Automattic\WooCommerce\Admin\Overrides\OrderRefund;

/**
 * Superstore order controller class
 */
class Order {

	/**
	 * Read a seller orders
	 *
	 * @param array $seller_id Seller ID.
	 * @param array $args Filter orders.
	 * @return array
	 */
	public function get_seller_orders( $seller_id, $args = array() ) {
		$seller_orders = array();

		if ( ! $seller_id ) {
			return $seller_orders;
		} else {
			$seller_id = absint( $seller_id );
		}

		if ( ! superstore_is_user_seller( $seller_id ) ) {
			return $seller_orders;
		}

		$defaults = array(
			'limit'   => -1,
			'order'   => 'ASC',
			'orderby' => 'date',
		);

		$args = wp_parse_args( $args, $defaults );

		$orders = wc_get_orders( $args );
		foreach ( $orders as $order ) {
			if ( ! $order instanceof OrderRefund ) {
				if ( $order->get_meta( 'superstore_has_sub_order' ) && ! $order->get_parent_id() ) {
					continue;
				}

				$items = $order->get_items( 'line_item' );

				if ( ! $items ) {
					return $seller_orders;
				}

				$product_id      = current( $items )->get_product_id();
				$order_seller_id = get_post_field( 'post_author', $product_id );
				$order_seller_id = $order_seller_id ? absint( $order_seller_id ) : 0;

				if ( $seller_id !== $order_seller_id ) {
					continue;
				}

				if ( $order ) {
					$seller_orders[] = $order;
				} else {
					return $seller_orders;
				}
			}
		}

		return $seller_orders;
	}

	/**
	 * If an order contains multiple sellers then split and show
	 * only own order product to each seller dashboard.
	 *
	 * @param int $parent_order_id Order ID.
	 */
	public function maybe_create_sub_order( $parent_order_id ) {
		$parent_order = wc_get_order( $parent_order_id );

		superstore_log( sprintf( 'New Order #%d created. Create sub order.', $parent_order_id ) );

		if ( true === $parent_order->get_meta( 'superstore_has_sub_order' ) ) {
			$args = array(
				'post_parent' => $parent_order_id,
				'post_type'   => 'shop_order',
				'numberposts' => -1,
				'post_status' => 'any',
			);

			$child_orders = get_children( $args );

			foreach ( $child_orders as $child ) {
				wp_delete_post( $child->ID, true );
			}
		}

		$sellers = superstore_get_sellers_by_order( $parent_order_id );

		// Return if do not have multiple sellers.
		if ( 1 === count( $sellers ) ) {
			superstore_log( 'Do not contains multiple sellers, skipping sub orders.' );

			$temp      = array_keys( $sellers );
			$seller_id = reset( $temp );

			do_action( 'superstore_create_parent_order', $parent_order, $seller_id );

			$parent_order->update_meta_data( '_superstore_seller_id', $seller_id );
			$parent_order->save();

			/*********** Create payment statement */
			$seller_id        = superstore_get_seller_by_order( $parent_order_id )->get_id();
			$order_total      = $parent_order->get_total();
			$order_status     = superstore_get_prop( $parent_order, 'status' );
			$admin_commission = superstore()->commission->get_earning_by_order( $parent_order, 'admin' );
			$net_amount       = $order_total - $admin_commission;
			$net_amount       = apply_filters( 'superstore_order_net_amount', $net_amount, $parent_order );

			// Make sure order status contains "wc-" prefix.
			if ( stripos( $order_status, 'wc-' ) === false ) {
				$order_status = 'wc-' . $order_status;
			}

			// Return if already created or woocommerce order status is changing.
			$statement_query = superstore()->payment->get_payment_statements( array( 'txn_id' => (int) $parent_order_id ) );
			if ( ! empty( $statement_query ) ) {
				return;
			}

			$statement_obj = superstore()->payment->crud_payment_statement();
			$statement_obj->set_user_id( $seller_id );
			$statement_obj->set_txn_id( $parent_order_id );
			$statement_obj->set_debit( $net_amount );
			$statement_obj->set_credit( 0 );
			$statement_obj->set_type( 'superstore_orders' );
			$statement_obj->set_status( $order_status );
			$statement_obj->save();
			/*********** Create payment statement */

			return;
		}

		$parent_order->update_meta_data( 'superstore_has_sub_order', true );
		$parent_order->save();

		superstore_log( sprintf( 'Contains multiple sellers, creating sub orders.', count( $sellers ) ) );

		foreach ( $sellers as $seller_id => $seller_products ) {
			$this->create_sub_order( $parent_order, $seller_id, $seller_products );
		}

		superstore_log( sprintf( 'Sub order creation process completed for #%d.', $parent_order_id ) );
	}

	/**
	 * Creates a sub order
	 *
	 * @param integer $parent_order Parent Order.
	 * @param integer $seller_id Seller ID.
	 * @param array   $seller_products Seller products.
	 * @return WP_Error on error
	 */
	public function create_sub_order( $parent_order, $seller_id, $seller_products ) {
		superstore_log( 'Creating sub order for seller: #' . $seller_id );

		$bill_ship = array(
			'billing_country',
			'billing_first_name',
			'billing_last_name',
			'billing_company',
			'billing_address_1',
			'billing_address_2',
			'billing_city',
			'billing_state',
			'billing_postcode',
			'billing_email',
			'billing_phone',
			'shipping_country',
			'shipping_first_name',
			'shipping_last_name',
			'shipping_company',
			'shipping_address_1',
			'shipping_address_2',
			'shipping_city',
			'shipping_state',
			'shipping_postcode',
		);

		try {
			$order = new WC_Order();

			// save billing and shipping address.
			foreach ( $bill_ship as $key ) {
				if ( is_callable( array( $order, "set_{$key}" ) ) ) {
					$order->{"set_{$key}"}( $parent_order->{"get_{$key}"}() );
				}
			}

			$this->create_line_items( $order, $seller_products );
			$this->create_taxes( $order, $parent_order, $seller_products );
			$this->create_shipping( $order, $parent_order );
			$this->create_coupons( $order, $parent_order, $seller_products );

			$order->set_created_via( 'superstore' );
			$order->set_cart_hash( $parent_order->get_cart_hash() );
			$order->set_customer_id( $parent_order->get_customer_id() );
			$order->set_currency( $parent_order->get_currency() );
			$order->set_prices_include_tax( $parent_order->get_prices_include_tax() );
			$order->set_customer_ip_address( $parent_order->get_customer_ip_address() );
			$order->set_customer_user_agent( $parent_order->get_customer_user_agent() );
			$order->set_customer_note( $parent_order->get_customer_note() );
			$order->set_payment_method( $parent_order->get_payment_method() );
			$order->set_payment_method_title( $parent_order->get_payment_method_title() );
			$order->update_meta_data( '_superstore_seller_id', $seller_id );

			do_action( 'superstore_create_sub_order_before_calculate_totals', $order, $parent_order, $seller_products );
			$order->calculate_totals();

			$order->set_status( $parent_order->get_status() );
			$order->set_parent_id( $parent_order->get_id() );

			$order_id = $order->save();

			wc_update_total_sales_counts( $order_id );

			/*********** Create payment statement */
			$order            = wc_get_order( $order_id );
			$order_total      = $order->get_total();
			$order_status     = superstore_get_prop( $order, 'status' );
			$admin_commission = superstore()->commission->get_earning_by_order( $order, 'admin' );
			$net_amount       = $order_total - $admin_commission;
			$net_amount       = apply_filters( 'superstore_order_net_amount', $net_amount, $order );

			// Make sure order status contains "wc-" prefix.
			if ( stripos( $order_status, 'wc-' ) === false ) {
				$order_status = 'wc-' . $order_status;
			}

			$statement_obj = superstore()->payment->crud_payment_statement();
			$statement_obj->set_user_id( $seller_id );
			$statement_obj->set_txn_id( $order_id );
			$statement_obj->set_debit( $net_amount );
			$statement_obj->set_credit( 0 );
			$statement_obj->set_type( 'superstore_orders' );
			$statement_obj->set_status( $order_status );
			$statement_obj->save();
			/*********** Create payment statement */

			superstore_log( 'Sub order created for #' . $order_id );

			do_action( 'superstore_checkout_update_order_meta', $order_id, $seller_id );
		} catch ( Exception $e ) {
			return new WP_Error( 'superstore-suborder-error', $e->getMessage() );
		}
	}

	/**
	 * Create line items
	 *
	 * @param object $order    Order.
	 * @param array  $products Products.
	 */
	public function create_line_items( $order, $products ) {
		foreach ( $products as $item ) {
			$product_item = new WC_Order_Item_Product();

			$product_item->set_name( $item->get_name() );
			$product_item->set_product_id( $item->get_product_id() );
			$product_item->set_variation_id( $item->get_variation_id() );
			$product_item->set_quantity( $item->get_quantity() );
			$product_item->set_tax_class( $item->get_tax_class() );
			$product_item->set_subtotal( $item->get_subtotal() );
			$product_item->set_subtotal_tax( $item->get_subtotal_tax() );
			$product_item->set_total_tax( $item->get_total_tax() );
			$product_item->set_total( $item->get_total() );
			$product_item->set_taxes( $item->get_taxes() );

			$metadata = $item->get_meta_data();
			if ( $metadata ) {
				foreach ( $metadata as $meta ) {
					$product_item->add_meta_data( $meta->key, $meta->value );
				}
			}

			$order->add_item( $product_item );
		}

		$order->save();

		do_action( 'superstore_after_create_line_items', $order );
	}

	/**
	 * Create taxes
	 *
	 * @param  WC_Order $order Order.
	 * @param  WC_Order $parent_order Parent order.
	 * @param  array    $products Products.
	 */
	public function create_taxes( $order, $parent_order, $products ) {
		$shipping  = $order->get_items( 'shipping' );
		$tax_total = 0;

		foreach ( $products as $item ) {
			$tax_total += $item->get_total_tax();
		}

		foreach ( $parent_order->get_taxes() as $tax ) {
			$seller_shipping = reset( $shipping );

			$item = new WC_Order_Item_Tax();
			$item->set_props(
				array(
					'rate_id'            => $tax->get_rate_id(),
					'label'              => $tax->get_label(),
					'compound'           => $tax->get_compound(),
					'rate_code'          => \WC_Tax::get_rate_code( $tax->get_rate_id() ),
					'tax_total'          => $tax_total,
					'shipping_tax_total' => is_bool( $seller_shipping ) ? '' : $seller_shipping->get_total_tax(),
				)
			);

			$order->add_item( $item );
		}

		$order->save();
	}

	/**
	 * Create shipping for a sub-order if neccessary
	 *
	 * @param WC_Order $order Order.
	 * @param WC_Order $parent_order Parent order.
	 * @return void
	 */
	public function create_shipping( $order, $parent_order ) {
		superstore_log( sprintf( '#%d - Creating Shipping.', $order->get_id() ) );

		$shipping_methods = $parent_order->get_shipping_methods();
		$order_seller_id  = superstore_get_seller_by_order( $order->get_id() )->get_id();

		$applied_shipping_method = '';

		if ( $shipping_methods ) {
			foreach ( $shipping_methods as $method_item_id => $shipping_object ) {
				$shipping_seller_id = wc_get_order_item_meta( $method_item_id, 'seller_id', true );

				if ( (int) $order_seller_id === (int) $shipping_seller_id ) {
					$applied_shipping_method = $shipping_object;
					break;
				}
			}
		}

		$shipping_method = apply_filters( 'superstore_shipping_method', $applied_shipping_method, $order->get_id(), $parent_order );

		// Return if no shipping methods found.
		if ( ! $shipping_method ) {
			superstore_log( sprintf( '#%d - No shipping method found. Aborting.', $order->get_id() ) );
			return;
		}

		if ( is_a( $shipping_method, 'WC_Order_Item_Shipping' ) ) {
			$item = new WC_Order_Item_Shipping();

			superstore_log( sprintf( '#%d - Adding shipping item.', $order->get_id() ) );

			$item->set_props(
				array(
					'method_title' => $shipping_method->get_name(),
					'method_id'    => $shipping_method->get_method_id(),
					'total'        => $shipping_method->get_total(),
					'taxes'        => $shipping_method->get_taxes(),
				)
			);

			$metadata = $shipping_method->get_meta_data();

			if ( $metadata ) {
				foreach ( $metadata as $meta ) {
					$item->add_meta_data( $meta->key, $meta->value );
				}
			}

			$order->add_item( $item );
			$order->set_shipping_total( $shipping_method->get_total() );
			$order->save();
		}
	}

	/**
	 * Create coupons if required
	 *
	 * @param WC_Order $order Order.
	 * @param WC_Order $parent_order Parent order.
	 * @param array    $products Products.
	 * @return void
	 */
	public function create_coupons( $order, $parent_order, $products ) {
		$used_coupons = $parent_order->get_items( 'coupon' );
		$product_ids  = array_map(
			function( $item ) {
				return $item->get_product_id();
			},
			$products
		);

		if ( ! $used_coupons ) {
			return;
		}

		foreach ( $used_coupons as $item ) {
			$coupon = new WC_Coupon( $item->get_code() );

			if ( $coupon && ! is_wp_error( $coupon ) && array_intersect( $product_ids, $coupon->get_product_ids() ) ) {
				$new_item = new WC_Order_Item_Coupon();
				$new_item->set_props(
					array(
						'code'         => $item->get_code(),
						'discount'     => $item->get_discount(),
						'discount_tax' => $item->get_discount_tax(),
					)
				);

				$new_item->add_meta_data( 'coupon_data', $coupon->get_data() );

				$order->add_item( $new_item );
			}
		}

		$order->save();
	}
}
