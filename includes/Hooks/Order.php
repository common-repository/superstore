<?php

namespace Binarithm\Superstore\Hooks;

use Exception;

/**
 * Superstore order hooks controller
 */
class Order {

	/**
	 * Superstore order hooks controller constructor
	 */
	public function __construct() {
		if ( is_admin() ) {
			add_action( 'woocommerce_process_shop_order_meta', array( $this, 'maybe_create_sub_order' ) );
			add_action( 'manage_shop_order_posts_custom_column', array( $this, 'add_order_columns' ), 11 );
			add_filter( 'woocommerce_reports_get_order_report_query', array( $this, 'remove_parent_reports' ) );
			add_filter( 'manage_edit-shop_order_columns', array( $this, 'edit_order_columns' ), 11 );
			add_action( 'admin_footer-edit.php', array( $this, 'sub_order_row_scripts' ) );
			add_action( 'wp_trash_post', array( $this, 'trash_order' ) );
			add_action( 'untrash_post', array( $this, 'untrash_order' ) );
			add_action( 'delete_post', array( $this, 'delete_order' ) );
			add_action( 'restrict_manage_posts', array( $this, 'add_toggle_sub_orders_button' ) );
			add_filter( 'post_class', array( $this, 'sub_order_row_classes' ), 10, 2 );
		}

		add_action( 'woocommerce_order_status_changed', array( $this, 'on_order_status_change' ), 10, 4 );
		add_action( 'woocommerce_order_status_changed', array( $this, 'on_sub_order_status_change' ), 99, 3 );
		add_action( 'woocommerce_checkout_update_order_meta', array( $this, 'maybe_create_sub_order' ), 20 );
		add_filter( 'woocommerce_coupon_is_valid', array( $this, 'update_seller_coupon' ), 10, 2 );
		add_action( 'woocommerce_reduce_order_stock', array( $this, 'restore_stock' ) );
		add_action( 'woocommerce_reduce_order_stock', array( $this, 'update_order_notes_for_suborder' ), 99 );
		add_action( 'wc-admin_import_orders', array( $this, 'delete_child_order' ) );
		add_filter( 'woocommerce_analytics_orders_select_query', array( $this, 'unset_child_order' ) );

		add_filter( 'manage_edit-shop_order_columns', array( $this, 'remove_action_column' ), 15 );
		add_filter( 'woocommerce_admin_order_preview_actions', array( $this, 'remove_action_button' ), 15 );
		add_filter( 'woocommerce_rest_prepare_shop_order_object', array( $this, 'add_seller_info' ) );
		add_action( 'woocommerce_order_status_changed', array( $this, 'delete_cache_data' ), 10, 4 );
		add_action( 'woocommerce_order_status_pending_to_on-hold', array( $this, 'stop_sending_multiple_emails' ) );
		add_action( 'woocommerce_order_status_on-hold_to_processing', array( $this, 'stop_sending_multiple_emails' ) );
		add_action( 'woocommerce_order_status_pending_to_processing', array( $this, 'stop_sending_multiple_emails' ) );
		add_action( 'woocommerce_order_status_completed', array( $this, 'stop_sending_multiple_emails' ) );

		add_filter( 'woocommerce_my_account_my_orders_query', array( $this, 'do_not_show_sub_orders' ) );
		add_action( 'woocommerce_order_item_meta_start', array( $this, 'add_seller_name' ), 10, 2 );
		add_action( 'woocommerce_order_details_after_order_table', array( $this, 'show_suborders' ) );
	}

	/**
	 * Create sub orders if order contains multiple sellers.
	 *
	 * @param int $order_id Order ID.
	 */
	public function maybe_create_sub_order( $order_id ) {
		superstore()->order->maybe_create_sub_order( $order_id );
	}

	/**
	 * Update seller coupon.
	 * Only if products fron the seller exists in the cart restrict coupons.
	 *
	 * @param boolean   $valid Valid.
	 * @param WC_Coupon $coupon Coupon.
	 * @return boolean|Execption
	 * @throws \Exception Exception.
	 */
	public function update_seller_coupon( $valid, $coupon ) {
		$coupon_id         = $coupon->get_id();
		$seller_id         = get_post_field( 'post_author', $coupon_id );
		$available_vendors = array();

		if ( count( $coupon->get_product_ids() ) === 0 ) {
			throw new Exception( __( 'Coupon must be restricted with a seller product.', 'superstore' ) );
		}

		foreach ( WC()->cart->get_cart() as $item ) {
			$product_id = $item['data']->get_id();

			$available_vendors[] = get_post_field( 'post_author', $product_id );
		}

		if ( ! in_array( $seller_id, $available_vendors, true ) ) {
			return false;
		}

		return $valid;
	}

	/**
	 * Restore order stock if reduced by twice
	 *
	 * @param object $order Order.
	 * @return void
	 */
	public function restore_stock( $order ) {
		// If rest request return (No such issue in rest).
		if ( defined( 'REST_REQUEST' ) ) {
			return;
		}

		$has_sub_order = wp_get_post_parent_id( $order->get_id() );

		// Check parent order or no.
		if ( ! $has_sub_order ) {
			return;
		}

		foreach ( $order->get_items() as $item ) {
			if ( ! $item->is_type( 'line_item' ) ) {
				continue;
			}

			// Only reduce stock once for each item.
			$product            = $item->get_product();
			$item_stock_reduced = $item->get_meta( '_reduced_stock', true );

			if ( ! $item_stock_reduced || ! $product || ! $product->managing_stock() ) {
				continue;
			}

			$item_name = $product->get_formatted_name();
			$new_stock = wc_update_product_stock( $product, $item_stock_reduced, 'increase' );

			if ( is_wp_error( $new_stock ) ) {
				/* translators: %s item name. */
				$order->add_order_note( sprintf( __( 'Unable to restore stock for item %s.', 'superstore' ), $item_name ) );
				continue;
			}

			$item->delete_meta_data( '_reduced_stock' );
			$item->save();
		}
	}

	/**
	 * Manage suborder notes for wrong stock level calculation
	 *
	 * @param obj $order Order.
	 * @return void
	 */
	public function update_order_notes_for_suborder( $order ) {
		$has_sub_order = wp_get_post_parent_id( $order->get_id() );

		if ( ! $has_sub_order ) {
			return;
		}

		$order = wc_get_order( $order->get_id() );

		$notes = wc_get_order_notes( array( 'order_id' => $order->get_id() ) );

		// Instead of deleting change stock level note status.
		foreach ( $notes as $note ) {
			if ( false !== strpos( $note->content, __( 'Stock level reduced:', 'woocommerce' ) ) ) {
				wp_set_comment_status( $note->id, 'hold' );
			}
		}

		// Adding stock level notes in order.
		foreach ( $order->get_items( 'line_item' ) as $key => $line_item ) {
			$item_id = $line_item->get_variation_id() ? $line_item->get_variation_id() : $line_item->get_product_id();

			$product = wc_get_product( $item_id );

			if ( $product->get_manage_stock() ) {
				$stock_quantity    = $product->get_stock_quantity();
				$previous_quantity = (int) $stock_quantity + $line_item->get_quantity();

				$notes_content = $product->get_formatted_name() . ' ' . $previous_quantity . '&rarr;' . $stock_quantity;

				$order->add_order_note( __( 'Stock level reduced:', 'woocommerce' ) . ' ' . $notes_content );
			}
		}
	}

	/**
	 * Delete child order from woocommerce order product
	 *
	 * @param ActionScheduler_Action $args Args.
	 */
	public function delete_child_order( $args ) {
		$order = get_post( $args );

		if ( $order->post_parent ) {
			global $wpdb;
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->delete( $wpdb->prefix . 'wc_order_product_lookup', array( 'order_id' => $order->ID ) );
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->delete( $wpdb->prefix . 'wc_order_stats', array( 'order_id' => $order->ID ) );
		}
	}

	/**
	 * For analytics order trim child order if parent exist from wc_order_product_lookup
	 *
	 * @param WC_Order $orders Orders.
	 * @return WC_Order
	 */
	public function unset_child_order( $orders ) {
		foreach ( $orders->data as $key => $order ) {
			if ( $order['parent_id'] ) {
				unset( $orders->data[ $key ] );
			}
		}

		return $orders;
	}

	/**
	 * Seller can not change order status if permission is not given
	 *
	 * @param array $columns Columns.
	 * @return array
	 */
	public function remove_action_column( $columns ) {
		if ( current_user_can( 'manage_woocommerce' ) ) {
			return $columns;
		}

		if ( superstore_get_option( 'seller_can_change_order_status', 'superstore_seller', 'yes' ) !== 'yes' ) {
			unset( $columns['wc_actions'] );
		}

		return $columns;
	}

	/**
	 * Seller can not view change order status button if permission is not given
	 *
	 * @param array $actions Actions.
	 * @return array
	 */
	public function remove_action_button( $actions ) {
		if ( current_user_can( 'manage_woocommerce' ) ) {
			return $actions;
		}

		if ( superstore_get_option( 'seller_can_change_order_status', 'superstore_seller', 'yes' ) !== 'yes' ) {
			unset( $actions['status'] );
		}

		return $actions;
	}

	/**
	 * Add superstore custom orders columns on woocommerce orders table
	 *
	 * @global type $post
	 * @global type $woocommerce
	 * @global \WC_Order $the_order
	 * @param type $col Column.
	 * @return mixed
	 */
	public function add_order_columns( $col ) {
		global $post, $the_order;

		if ( empty( $the_order ) || (int) $the_order->get_id() !== (int) $post->ID ) {
			$the_order = new \WC_Order( $post->ID );
		}

		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return $col;
		}

		switch ( $col ) {
			case 'order_number':
				if ( 0 !== $post->post_parent ) {
					echo '<strong>';
					echo esc_html__( '&nbsp;Sub Order of', 'superstore' );
					printf( ' <a href="%s">#%s</a>', esc_url( admin_url( 'post.php?action=edit&post=' . $post->post_parent ) ), esc_html( $post->post_parent ) );
					echo '</strong>';
				}
				break;

			case 'suborder':
				$has_sub = get_post_meta( $post->ID, 'superstore_has_sub_order', true );

				if ( $has_sub ) {
					printf( '<a href="#" class="show-sub-orders" data-class="parent-%1$d" data-show="%2$s" data-hide="%3$s">%2$s</a>', esc_attr( $post->ID ), esc_attr__( 'Show Sub Orders', 'superstore' ), esc_attr__( 'Hide Sub Orders', 'superstore' ) );
				}
				break;

			case 'seller':
				$has_sub = get_post_meta( $post->ID, 'superstore_has_sub_order', true );

				// phpcs:ignore Squiz.PHP.DisallowMultipleAssignments.FoundInControlStructure, WordPress.CodeAnalysis.AssignmentInCondition.Found
				if ( ! $has_sub && $seller = get_user_by( 'id', superstore_get_seller_by_order( $post->ID )->get_id() ) ) {
					printf( '<a href="%s">%s</a>', esc_url( admin_url( 'edit.php?post_type=shop_order&seller_id=' . $seller->ID ) ), esc_html( $seller->display_name ) );
				} else {
					esc_html_e( 'multiple', 'superstore' );
				}

				break;
		}
	}

	/**
	 * Remove child orders from WC reports
	 *
	 * @param array $query Query.
	 * @return array
	 */
	public function remove_parent_reports( $query ) {
		$query['where'] .= ' AND posts.post_parent = 0';

		return $query;
	}

	/**
	 * Edit superstore custom order columns.
	 *
	 * @param array $existing_columns Columns.
	 * @return array
	 */
	public function edit_order_columns( $existing_columns ) {
		if ( WC_VERSION > '3.0' ) {
			unset( $existing_columns['wc_actions'] );

			$columns = array_slice( $existing_columns, 0, count( $existing_columns ), true ) +
				array(
					'seller'     => __( 'Seller', 'superstore' ),
					'wc_actions' => __( 'Actions', 'superstore' ),
					'suborder'   => __( 'Sub Order', 'superstore' ),
				)
				+ array_slice( $existing_columns, count( $existing_columns ), count( $existing_columns ) - 1, true );
		} else {
			$existing_columns['seller']   = __( 'Seller', 'superstore' );
			$existing_columns['suborder'] = __( 'Sub Order', 'superstore' );
		}

		if ( WC_VERSION > '3.0' ) {
			// Remove seller, suborder column if seller is viewing his own product.
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			if ( ! current_user_can( 'manage_woocommerce' ) || ( isset( $_GET['author'] ) && ! empty( $_GET['author'] ) ) ) {
				unset( $columns['suborder'] );
				unset( $columns['seller'] );
			}

			return apply_filters( 'superstore_edit_wc_orders_columns', $columns );
		}

		// Remove seller, suborder column if seller is viewing his own product.
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( ! current_user_can( 'manage_woocommerce' ) || ( isset( $_GET['author'] ) && ! empty( $_GET['author'] ) ) ) {
			unset( $existing_columns['suborder'] );
			unset( $existing_columns['seller'] );
		}

		return apply_filters( 'superstore_edit_wc_orders_columns', $existing_columns );
	}

	/**
	 * Filter orders of current seller
	 *
	 * @param object $args Args.
	 * @param object $query Query.
	 * @return object $args
	 */
	public function filter_current_seller_orders( $args, $query ) {
		global $wpdb;

		if ( ! is_admin() || ! $query->is_main_query() ) {
			return $args;
		}

		if ( ! isset( $query->query_vars['post_type'] ) ) {
			return $args;
		}

		// phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
		if ( ! in_array( $query->query_vars['post_type'], array( 'shop_order', 'wc_booking' ) ) ) {
			return $args;
		}

		$seller_id = 0;

		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			$seller_id = get_current_user_id();
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		} elseif ( ! empty( $_GET['seller_id'] ) ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$seller_id = sanitize_text_field( wp_unslash( $_GET['seller_id'] ) );
			$seller_id = absint( $seller_id );
		}

		if ( ! $seller_id ) {
			return $args;
		}

		$args['join']  .= " LEFT JOIN {$wpdb->prefix}superstore_orders as do ON $wpdb->posts.ID=do.order_id";
		$args['where'] .= " AND do.seller_id=$seller_id";

		return $args;
	}

	/**
	 * Update superstore order status,child orders status, seller balance
	 * on parent order status change.
	 *
	 * @param integer $order_id Order id.
	 * @param string  $old_status Old status.
	 * @param string  $new_status New status.
	 * @param obj     $order Order.
	 * @return void
	 */
	public function on_order_status_change( $order_id, $old_status, $new_status, $order ) {
		// Split order if the order doesn't have parent and sub orders.
		if ( empty( $order->post_parent ) && empty( $order->get_meta( 'superstore_has_sub_order' ) ) && is_admin() ) {
			// Prevent recursive calls.
			remove_action( 'woocommerce_order_status_changed', array( $this, 'on_order_status_change' ), 10, 4 );

			superstore()->order->maybe_create_sub_order( $order_id );

			// Add the hook back.
			add_action( 'woocommerce_order_status_changed', array( $this, 'on_order_status_change' ), 10, 4 );
		}

		// make sure order status contains "wc-" prefix.
		if ( stripos( $new_status, 'wc-' ) === false ) {
			$new_status = 'wc-' . $new_status;
		}

		// if any child orders found, change the orders as well.
		$sub_orders = get_children(
			array(
				'post_parent' => $order_id,
				'post_type'   => 'shop_order',
			)
		);

		if ( $sub_orders ) {
			foreach ( $sub_orders as $order_post ) {
				$order = wc_get_order( $order_post->ID );
				$order->update_status( $new_status );
			}
		}

		// If exclude_cod_payment is enabled, don't include the fund in seller balance.
		$exclude_cod_payment = superstore_get_option( 'exclude_cod_payment', 'superstore_payment', 'no' );

		if ( 'yes' === $exclude_cod_payment && 'cod' === $order->get_payment_method() ) {
			return;
		}

		$statement_query = superstore()->payment->get_payment_statements( array( 'txn_id' => (int) $order_id ) );
		foreach ( $statement_query as $statement_obj ) {
			$statement_obj->set_status( $new_status );
			$statement_obj->save();
		}

		// If refunded order, remove the order amount from seller balance also.
		if ( 'wc-refunded' === $new_status ) {
			global $wpdb;

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$balance_data = $wpdb->get_row(
				$wpdb->prepare(
					"select * from $wpdb->superstore_payment_statements where txn_id = %d AND status = 'approved'",
					$order_id
				)
			);

			// If approved the withdraw request before refund, return.
			if ( $balance_data ) {
				return;
			}

			$seller_id  = superstore_get_seller_by_order( $order_id )->get_id();
			$net_amount = superstore_get_earnings_by_order( $order_id );

			$statement_object = superstore()->payment->crud_payment_statement();
			$statement_object->set_user_id( (int) $seller_id );
			$statement_object->set_txn_id( (int) $order_id );
			$statement_object->set_debit( (float) 0 );
			$statement_object->set_credit( (float) $net_amount );
			$statement_object->set_type( 'superstore_refund' );
			$statement_object->set_status( 'approved' );
			$statement_object->save();

			update_post_meta( $order_id, '_superstore_order_earnings', 0 );
		}
	}

	/**
	 * Complete parent order when child orders are completed
	 *
	 * @param integer $order_id Order ID.
	 * @param string  $old_status Old status.
	 * @param string  $new_status New status.
	 * @return void
	 */
	public function on_sub_order_status_change( $order_id, $old_status, $new_status ) {
		$order_post = get_post( $order_id );

		// Only monitor child orders.
		if ( 0 === $order_post->post_parent ) {
			return;
		}

		$parent_order_id = $order_post->post_parent;
		$sub_orders      = get_children(
			array(
				'post_parent' => $parent_order_id,
				'post_type'   => 'shop_order',
			)
		);

		// Return if any child order is not completed.
		$all_complete = true;

		if ( $sub_orders ) {
			foreach ( $sub_orders as $sub ) {
				$order = wc_get_order( $sub->ID );

				if ( $order->get_status() !== 'completed' ) {
					$all_complete = false;
				}
			}
		}

		// Complete parent order when all child orders are completed.
		if ( $all_complete ) {
			$parent_order = wc_get_order( $parent_order_id );
			$parent_order->update_status( 'wc-completed', __( 'Complete parent order when all child orders are completed.', 'superstore' ) );
		}
	}

	/**
	 * Add css/js on sub order show/hide toggling
	 */
	public function sub_order_row_scripts() {
		?>
		<script type="text/javascript">
		jQuery(function($) {
			$('tr.sub-order').hide();

			$('a.show-sub-orders').on('click', function(e) {
				e.preventDefault();

				var $self = $(this),
					el = $('tr.' + $self.data('class') );

				if ( el.is(':hidden') ) {
					el.show();
					$self.text( $self.data('hide') );
				} else {
					el.hide();
					$self.text( $self.data('show') );
				}
			});

			$('button.toggle-sub-orders').on('click', function(e) {
				e.preventDefault();

				$('tr.sub-order').toggle();
			});
		});
		</script>

		<style type="text/css">
			tr.sub-order {
				background-color: rgba(144, 0, 255, .1) !important; /* #cfd0ff */
			}

			th#order_number {
				width: 21ch;
			}

			th#order_date {
				width: 9ch;
			}

			th#order_status {
				width: 12ch;
			}

			th#shipping_address {
				width: 18ch;
			}

			th#wc_actions {
				width: 9ch;
			}

			th#seller {
				width: 6ch;
			}

			th#suborder {
				width: 9ch;
			}
		</style>
		<?php
	}

	/**
	 * Delete sub orders when parent order is trashed
	 *
	 * @param int $post_id Post ID.
	 */
	public function trash_order( $post_id ) {
		$post = get_post( $post_id );

		// phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
		if ( 'shop_order' === $post->post_type && 0 == $post->post_parent ) {
			$sub_orders = get_children(
				array(
					'post_parent' => $post_id,
					'post_type'   => 'shop_order',
				)
			);

			if ( $sub_orders ) {
				foreach ( $sub_orders as $order_post ) {
					wp_trash_post( $order_post->ID );
				}
			}
		}
	}

	/**
	 * Untrash sub orders when parent orders are untrashed
	 *
	 * @param int $post_id Post ID.
	 */
	public function untrash_order( $post_id ) {
		$post = get_post( $post_id );

		// phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
		if ( 'shop_order' == $post->post_type && 0 == $post->post_parent ) {
			global $wpdb;

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$suborder_ids = $wpdb->get_col(
				$wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE post_parent = %d AND post_type = 'shop_order'", $post_id )
			);

			if ( $suborder_ids ) {
				foreach ( $suborder_ids as $suborder_id ) {
					wp_untrash_post( $suborder_id );
				}
			}
		}
	}

	/**
	 * Delete sub orders and from superstore_orders table when admin deleted an order
	 *
	 * @param int $post_id Post ID.
	 */
	public function delete_order( $post_id ) {
		$post = get_post( $post_id );

		if ( 'shop_order' === $post->post_type ) {

			$sub_orders = get_children(
				array(
					'post_parent' => $post_id,
					'post_type'   => 'shop_order',
				)
			);

			if ( $sub_orders ) {
				foreach ( $sub_orders as $order_post ) {
					wp_delete_post( $order_post->ID );
				}
			}
		}
	}

	/**
	 * Add sub orders toggle button in woocommerce orders table
	 *
	 * @global WP_Query $wp_query
	 */
	public function add_toggle_sub_orders_button() {
		global $wp_query;

		if ( isset( $wp_query->query['post_type'] ) && 'shop_order' === $wp_query->query['post_type'] ) {
			echo '<button class="toggle-sub-orders button">' . esc_html__( 'Toggle Sub orders', 'superstore' ) . '</button>';
		}
	}

	/**
	 * Add html classes to woocommerce orders table sub orders row
	 *
	 * @global WP_Post $post
	 * @param array $classes Classes.
	 * @param int   $post_id Post ID.
	 * @return array
	 */
	public function sub_order_row_classes( $classes, $post_id ) {
		global $post;

		if ( is_search() || ! current_user_can( 'manage_woocommerce' ) ) {
			return $classes;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$seller_id = isset( $_GET['seller_id'] ) ? sanitize_text_field( wp_unslash( $_GET['seller_id'] ) ) : 0;

		if ( $seller_id ) {
			return $classes;
		}

		// phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
		if ( 'shop_order' === $post->post_type && 0 != $post->post_parent ) {
			$classes[] = 'sub-order parent-' . $post->post_parent;
		}

		return $classes;
	}

	/**
	 * Add seller info in restful wc_order
	 *
	 * @param object $response Response.
	 * @return WP_REST_Response
	 */
	public function add_seller_info( $response ) {
		$seller_ids = array();

		foreach ( $response as $data ) {
			if ( empty( $data['line_items'] ) ) {
				continue;
			}

			foreach ( $data['line_items'] as $item ) {
				$product_id = ! empty( $item['product_id'] ) ? $item['product_id'] : 0;
				$seller_id  = get_post_field( 'post_author', $product_id );

				// phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
				if ( $seller_id && ! in_array( $seller_id, $seller_ids ) ) {
					array_push( $seller_ids, $seller_id );
				}
			}
		}

		if ( ! $seller_ids ) {
			return $response;
		}

		$data = $response->get_data();

		foreach ( $seller_ids as $store_id ) {
			$store             = superstore()->seller->crud_seller( $store_id );
			$data['sellers'][] = array(
				'id'         => $store->get_id(),
				'store_name' => $store->get_store_name(),
				'store_url'  => $store->get_store_url(),
				'address'    => $store->get_address(),
			);
		}

		// If multiple sellers, pass empty array.
		if ( count( $seller_ids ) > 1 ) {
			$data['seller'] = array();
		} else {
			$store          = superstore()->seller->crud_seller( $seller_ids[0] );
			$data['seller'] = array(
				'id'         => $store->get_id(),
				'store_name' => $store->get_store_name(),
				'store_url'  => $store->get_store_url(),
				'address'    => $store->get_address(),
			);
		}

		$response->set_data( $data );

		return $response;
	}

	/**
	 * Delete cache data on order status changed
	 *
	 * @param int $order_id Order ID.
	 */
	public function delete_cache_data( $order_id ) {
		$seller_id = superstore_get_seller_by_order( $order_id )->get_id();
		superstore_cache_delete_group( 'superstore_seller_data_' . $seller_id );
	}

	/**
	 * Stop sending multiple emails on order status changed
	 */
	public function stop_sending_multiple_emails() {
		if ( did_action( 'woocommerce_order_status_pending_to_on-hold_notification' ) ) {
			superstore_unset_hook( 'woocommerce_order_status_pending_to_on-hold_notification', 'WC_Email_Customer_On_Hold_Order', 'trigger', 10 );
		}

		if ( did_action( 'woocommerce_order_status_on-hold_to_processing_notification' ) ) {
			superstore_unset_hook( 'woocommerce_order_status_on-hold_to_processing_notification', 'WC_Email_Customer_Processing_Order', 'trigger', 10 );
		}

		if ( did_action( 'woocommerce_order_status_pending_to_processing_notification' ) ) {
			superstore_unset_hook( 'woocommerce_order_status_pending_to_processing_notification', 'WC_Email_Customer_Processing_Order', 'trigger', 10 );
		}

		if ( did_action( 'woocommerce_order_status_completed_notification' ) ) {
			superstore_unset_hook( 'woocommerce_order_status_completed_notification', 'WC_Email_Customer_Completed_Order', 'trigger', 10 );
		}
	}

	/**
	 * Show only customer main orders.
	 *
	 * @param array $customer_orders Orders.
	 * @return array post_arg_query
	 */
	public function do_not_show_sub_orders( $customer_orders ) {
		$customer_orders['post_parent'] = 0;

		return $customer_orders;
	}

	/**
	 * Add seller name in order details
	 *
	 * @param  int    $item_id Item ID.
	 * @param  object $order Order.
	 * @return string
	 */
	public function add_seller_name( $item_id, $order ) {
		$product_id = $order->get_product_id();

		if ( ! $product_id ) {
			return;
		}

		$seller_id = get_post_field( 'post_author', $product_id );
		$seller    = superstore()->seller->crud_seller( $seller_id );

		if ( ! is_object( $seller ) ) {
			return;
		}

		printf( '<br>%s: <a href="%s">%s</a>', esc_html__( 'Seller', 'superstore' ), esc_url( $seller->get_store_url() ), esc_html( $seller->get_store_name() ) );
	}

	/**
	 * Show in customer order details if sub orders are available
	 *
	 * @param WC_Order $parent_order Parent order.
	 * @return void
	 */
	public function show_suborders( $parent_order ) {
		$sub_orders = get_children(
			array(
				'post_parent' => superstore_get_prop( $parent_order, 'id' ),
				'post_type'   => 'shop_order',
				'post_status' => array_keys( wc_get_order_statuses() ),
			)
		);

		if ( ! $sub_orders ) {
			return;
		}

		if ( 'yes' !== superstore_get_option( 'show_suborders_on_customer_order_details', 'superstore_general', 'yes' ) ) {
			return;
		}

		$statuses = wc_get_order_statuses();

		superstore_get_template_part(
			'global/sub-orders',
			'',
			array(
				'sub_orders' => $sub_orders,
				'statuses'   => $statuses,
			)
		);
	}
}
