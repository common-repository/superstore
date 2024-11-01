<?php

namespace Binarithm\Superstore;

/**
 * Superstore commission class
 */
class Commission {

	/**
	 * Store's order id
	 *
	 * @var int
	 */
	public $order_id = 0;

	/**
	 * Store's Order quantity
	 *
	 * @var int
	 */
	public $order_quantity = 0;

	/**
	 * Superstore commission constructor
	 */
	public function __construct() {
		add_filter( 'woocommerce_order_item_get_formatted_meta_data', array( $this, 'hide_superstore_meta' ) );
		add_action( 'woocommerce_order_status_changed', array( $this, 'update_gateway_fee' ), 100 );
		add_action( 'woocommerce_thankyou_ppec_paypal', array( $this, 'update_gateway_fee' ) );
	}

	/**
	 * Hide superstore order meta data
	 *
	 * @param  array $formatted_meta Formatted meta.
	 * @return array
	 */
	public function hide_superstore_meta( $formatted_meta ) {
		$need_to_hide = array( '_superstore_commission_rate', '_superstore_commission_type' );
		$hidden       = array();

		foreach ( $formatted_meta as $key => $meta ) {
			// phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
			if ( ! in_array( $meta->key, $need_to_hide ) ) {
				array_push( $hidden, $meta );
			}
		}

		return $hidden;
	}

	/**
	 * Update gateway fee
	 *
	 * @param int $order_id Order ID.
	 * @return void
	 */
	public function update_gateway_fee( $order_id ) {
		global $wpdb;
		$order          = wc_get_order( $order_id );
		$processing_fee = $this->get_processing_fee( $order );

		if ( ! $processing_fee ) {
			return;
		}

		foreach ( $this->get_orders_to_process( $order ) as $tmp_order ) {
			$gateway_fee_added = $tmp_order->get_meta( 'superstore_gateway_fee' );
			$seller_earning    = $this->get_earning_by_order( $tmp_order->get_id() );

			if ( is_null( $seller_earning ) || $gateway_fee_added ) {
				continue;
			}

			$gateway_fee     = ( $processing_fee / $order->get_total() ) * $tmp_order->get_total();
			$gateway_fee     = apply_filters( 'superstore_get_processing_gateway_fee', $gateway_fee, $tmp_order, $order );
			$net_amount      = $seller_earning - $gateway_fee;
			$net_amount      = apply_filters( 'superstore_order_gateway_fee_seller_net_earning', $net_amount, $seller_earning, $gateway_fee, $tmp_order, $order );
			$net_amount      = (float) $net_amount;
			$statement_query = superstore()->payment->get_payment_statements(
				array(
					'txn_id' => $tmp_order->get_id(),
					'type'   => 'superstore_orders',
				)
			);

			foreach ( $statement_query as $statement_obj ) {
				$statement_obj->set_debit( $net_amount );
				$statement_obj->save();
			}

			$tmp_order->update_meta_data( 'superstore_gateway_fee', $gateway_fee );
			// phpcs:ignore WordPress.WP.I18n.MissingTranslatorsComment
			$tmp_order->add_order_note( sprintf( __( 'Payment gateway processing fee %s', 'superstore' ), round( $gateway_fee, 2 ) ) );
			$tmp_order->save_meta_data();
		}
	}

	/**
	 * Get processing fee
	 *
	 * @param \WC_Order $order Order.
	 * @return float
	 */
	public function get_processing_fee( $order ) {
		$processing_fee = 0;
		$payment_mthod  = $order->get_payment_method();

		if ( 'paypal' === $payment_mthod ) {
			$processing_fee = $order->get_meta( 'PayPal Transaction Fee' );
		}

		if ( 'ppec_paypal' === $payment_mthod && defined( 'PPEC_FEE_META_NAME_NEW' ) ) {
			$processing_fee = $order->get_meta( PPEC_FEE_META_NAME_NEW );
		}

		return apply_filters( 'superstore_get_processing_fee', $processing_fee, $order );
	}

	/**
	 * Get all the orders to be processed
	 *
	 * @param \WC_Order $order Order.
	 * @return array
	 */
	public function get_orders_to_process( $order ) {
		$has_suborder = $order->get_meta( 'superstore_has_sub_order' );
		$all_orders   = array();

		if ( $has_suborder ) {
			$sub_order_ids = get_children(
				array(
					'post_parent' => $order->get_id(),
					'post_type'   => 'shop_order',
					'fields'      => 'ids',
				)
			);

			foreach ( $sub_order_ids as $sub_order_id ) {
				$sub_order    = wc_get_order( $sub_order_id );
				$all_orders[] = $sub_order;
			}
		} else {
			$all_orders[] = $order;
		}

		return $all_orders;
	}

	/**
	 * Set order id
	 *
	 * @param  int $id Order ID.
	 */
	public function set_order_id( $id ) {
		$this->order_id = $id;
	}

	/**
	 * Get order id
	 *
	 * @return int
	 */
	public function get_order_id() {
		return $this->order_id;
	}

	/**
	 * Set order quantity
	 *
	 * @param  int $number Quantity.
	 */
	public function set_order_quantity( $number ) {
		$this->order_quantity = $number;
	}

	/**
	 * Get order quantity
	 *
	 * @return int
	 */
	public function get_order_quantity() {
		return $this->order_quantity;
	}

	/**
	 * Get admin commission by order
	 *
	 * @param  int|WC_Order $order Order id or object.
	 * @param  string       $context Seller or admin.
	 * @return float|null on failure
	 */
	public function get_earning_by_order( $order, $context = 'seller' ) {
		if ( ! $order instanceof \WC_Order ) {
			$order = wc_get_order( $order );
		}

		if ( ! $order ) {
			return new \WP_Error( __( 'Order not found', 'superstore' ), 407 );
		}

		if ( $order->get_meta( 'superstore_has_sub_order' ) ) {
			return;
		}

		// For checking commission has been saved previously or not.
		$this->set_order_id( $order->get_id() );

		$earning = 0;

		foreach ( $order->get_items() as $item_id => $item ) {
			if ( ! $item->get_product() ) {
				continue;
			}

			// Set line item quantity to use in `Binarithm\Superstore\Commission::prepare_for_calculation()`.
			$this->set_order_quantity( $item->get_quantity() );

			$product_id = $item->get_product()->get_id();
			$refund     = $order->get_total_refunded_for_item( $item_id );

			if ( $refund ) {
				$earning += $this->get_earning_by_product( $product_id, $context, $item->get_total() - $refund );
			} else {
				$earning += $this->get_earning_by_product( $product_id, $context, $item->get_total() );
			}
		}

		if ( superstore_get_option( 'shipping_fee_recipient', 'superstore_general', 'seller' ) === $context ) {
			$earning += $order->get_total_shipping() - $order->get_total_shipping_refunded();
		}

		if ( superstore_get_option( 'tax_fee_recipient', 'superstore_general', 'seller' ) === $context ) {
			$earning += $order->get_total_tax() - $order->get_total_tax_refunded();
		}

		return apply_filters( 'superstore_get_earning_by_order', $earning, $order, $context );
	}

	/**
	 * Get earning by product
	 *
	 * @param  int|WC_Product $product Product.
	 * @param  string         $context Seller or admin.
	 * @param  float          $price Price.
	 * @return float
	 */
	public function get_earning_by_product( $product, $context = 'seller', $price = null ) {
		if ( ! $product instanceof \WC_Product ) {
			$product = wc_get_product( $product );
		}

		if ( ! $product ) {
			return new \WP_Error( __( 'Product not found', 'superstore' ), 407 );
		}

		$product_price = is_null( $price ) ? (float) $product->get_price() : (float) $price;
		$seller_id     = get_post_field( 'post_author', $product->get_id() );

		$earning = $this->get_commission( $product->get_id(), $product_price, $seller_id );
		$earning = 'admin' === $context ? $product_price - $earning : $earning;

		return apply_filters( 'superstore_get_earning_by_product', $earning, $product, $context );
	}

	/**
	 * Caculate commissions from priority settings.
	 *
	 * @param  int   $product_id Product ID.
	 * @param  float $product_price Product Price.
	 * @param  int   $seller_id Seller ID.
	 * @return float
	 */
	public function get_commission( $product_id, $product_price, $seller_id = 0 ) {

		$priorities = superstore_get_option(
			'commission_by_priority',
			'superstore_general',
			array(
				'product',
				'category',
				'seller_level',
				'seller',
				'global',
			),
		);

		$seller_id  = ! $seller_id ? get_post_field( 'post_author', $product_id ) : $seller_id;
		$commission = $this->calculate_commission( $product_id, $priorities[0], $seller_id );

		if ( is_null( $commission ) ) {
			if ( array_key_exists( 1, $priorities ) ) {
				$commission = $this->calculate_commission( $product_id, $priorities[1], $seller_id );
				if ( is_null( $commission ) ) {
					if ( array_key_exists( 2, $priorities ) ) {
						$commission = $this->calculate_commission( $product_id, $priorities[2], $seller_id );
						if ( is_null( $commission ) ) {
							if ( array_key_exists( 3, $priorities ) ) {
								$commission = $this->calculate_commission( $product_id, $priorities[3], $seller_id );
								if ( is_null( $commission ) ) {
									if ( array_key_exists( 4, $priorities ) ) {
										$commission = $this->calculate_commission( $product_id, $priorities[4], $seller_id );
									}
								}
							}
						}
					}
				}
			}
		}

		$commission_rate = null;
		$commission_type = null;

		$commission_rate = isset( $commission['rate'] ) ? $commission['rate'] : null;
		$commission_type = isset( $commission['type'] ) ? $commission['type'] : null;

		// Calculate commission based on previously purchased order.
		if ( $this->get_order_id() ) {
			$order      = wc_get_order( $this->get_order_id() );
			$line_items = $order->get_items();

			static $i = 0;
			foreach ( $line_items as $item ) {
				$items = array_keys( $line_items );

				if ( ! isset( $items[ $i ] ) ) {
					continue;
				}

				$saved_commission_rate = wc_get_order_item_meta( $items[ $i ], '_superstore_commission_rate', true );
				$saved_commission_type = wc_get_order_item_meta( $items[ $i ], '_superstore_commission_type', true );

				if ( $saved_commission_rate ) {
					$commission_rate = $saved_commission_rate;
				} else {
					wc_add_order_item_meta( $items[ $i ], '_superstore_commission_rate', $commission_rate );
				}

				if ( $saved_commission_type ) {
					$commission_type = $saved_commission_type;
				} else {
					wc_add_order_item_meta( $items[ $i ], '_superstore_commission_type', $commission_type );
				}

				$i++;
				break;
			}

			// Reset php background process.
			$i = count( $line_items ) === $i ? 0 : $i;
		}

		$earning = 0;

		if ( 'flat' === $commission_type ) {
			if ( $this->get_order_quantity() ) {
				$commission_rate *= $this->get_order_quantity();
			}

			// Monitor refund orders.
			$item_total = get_post_meta( $this->get_order_id(), '_superstore_item_total', true );
			if ( $item_total ) {
				$commission_rate = ( $commission_rate / $item_total ) * $product_price;
			}

			$earning = $product_price - $commission_rate;
		}

		if ( 'percentage' === $commission_type ) {
			$earning = ( $product_price * $commission_rate ) / 100;
			$earning = $product_price - $earning;

			// Seller will receive 100%.
			if ( $commission_rate > 100 ) {
				$earning = $product_price;
			}
		}

		$earnings = $earning ? $earning : $product_price;

		update_post_meta( $this->get_order_id(), '_superstore_order_earnings', $earnings );

		return $earnings;
	}

	/**
	 * Calculate commission by deafult priority 1.product, 2.category, 3.seller_level, 4.seller 5.global
	 *
	 * @param  int    $product_id Product ID.
	 * @param  string $source Valid sources are product, category, seller_level, seller, global.
	 * @param  int    $seller_id Seller ID.
	 * @return array|null on failure
	 */
	public function calculate_commission( $product_id, $source, $seller_id = 0 ) {
		$commission       = null;
		$valid_product_id = $this->validate_product_id( $product_id );
		$sources          = superstore_get_option(
			'commission_by_priority',
			'superstore_general',
			array( 'product', 'category', 'seller_level', 'seller', 'global' )
		);
		if ( ! in_array( $source, $sources, true ) ) {
			return null;
		}
		$get_commission = array();

		if ( 'product' === $source ) {
			if ( ! superstore()->superstore_pro_exists() ) {
				return null;
			}

			if ( get_post_meta( $valid_product_id, 'admin_commission_rate', true ) ) {
				$get_commission['rate'] = get_post_meta( $valid_product_id, 'admin_commission_rate', true );
			} else {
				return null;
			}

			$get_commission['type'] = get_post_meta( $valid_product_id, 'admin_commission_type', true ) ? get_post_meta( $valid_product_id, 'admin_commission_type', true ) : 'percentage';
		}

		if ( 'category' === $source ) {
			if ( ! superstore()->superstore_pro_exists() ) {
				return null;
			}

			$terms = get_the_terms( $valid_product_id, 'product_cat' );

			if ( empty( $terms ) || count( $terms ) > 1 || ! $terms ) {
				return null;
			}

			$term_id                = $terms[0]->term_id;
			$get_commission['rate'] = get_term_meta( $term_id, 'admin_commission_rate', true );
			$get_commission['type'] = get_term_meta( $term_id, 'admin_commission_type', true ) ? get_term_meta( $term_id, 'admin_commission_type', true ) : 'percentage';
		}

		if ( 'seller' === $source ) {
			if ( ! $seller_id ) {
				return null;
			}
			if ( get_user_meta( $seller_id, 'superstore_admin_commission_rate', true ) ) {
				$get_commission['type'] = get_user_meta( $seller_id, 'superstore_admin_commission_type', true );
				$get_commission['rate'] = get_user_meta( $seller_id, 'superstore_admin_commission_rate', true );
			} else {
				return null;
			}
		}

		if ( 'seller_level' === $source ) {
			if ( ! superstore()->superstore_pro_exists() ) {
				return null;
			}
			if ( ! $seller_id ) {
				return null;
			}

			if ( get_user_meta( $seller_id, 'superstore_seller_level', true ) ) {
				$seller_level  = get_user_meta( $seller_id, 'superstore_seller_level', true );
				$sl_commission = superstore_get_option(
					"admin_commission_for_{$seller_level}_seller",
					'superstore_general',
					array(
						'type' => 'percentage',
						'rate' => null,
					)
				);
				if ( empty( $sl_commission['rate'] ) ) {
					return null;
				}
				$get_commission['type'] = $sl_commission['type'];
				$get_commission['rate'] = $sl_commission['rate'];
			} else {
				return null;
			}
		}

		if ( 'global' === $source ) {
			$get_commission = superstore_get_option(
				'admin_commission_global',
				'superstore_general',
				array(
					'rate' => null,
					'type' => 'percentage',
				)
			);

			if ( empty( $get_commission['rate'] ) ) {
				return null;
			}
		}

		if ( ! empty( $get_commission['rate'] ) ) {
			$commission['rate'] = $this->validate_rate( $get_commission['rate'] );
			if ( empty( $commission['rate'] ) ) {
				return null;
			}
			$commission['type']   = $get_commission['type'];
			$commission['source'] = $source;
		}

		return apply_filters( 'superstore_commission_calculator', $commission, $product_id, $this->order_id );
	}

	/**
	 * Return parent id if variable product.
	 *
	 * @param  int $product_id Product ID.
	 * @return int
	 */
	public function validate_product_id( $product_id ) {
		$product   = wc_get_product( $product_id );
		$parent_id = $product->get_parent_id();

		return $parent_id ? $parent_id : $product_id;
	}

	/**
	 * Validate commission rate
	 *
	 * @param  float $rate Rate.
	 * @return float
	 */
	public function validate_rate( $rate ) {
		if ( '' === $rate || ! is_numeric( $rate ) || $rate < 0 || null === $rate || false === $rate ) {
			return null;
		}

		return (float) $rate;
	}
}
