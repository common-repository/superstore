<?php
/**
 * Get superstore settings
 *
 * @param string $option Settings field name.
 * @param string $section Section name the field.
 * @param string $default Default value.
 * @return mixed
 */
function superstore_get_option( $option, $section, $default = '' ) {
	$options = get_option( $section );

	if ( isset( $options[ $option ] ) ) {
		return $options[ $option ];
	}

	return $default;
}

/**
 * Get superstore template path.
 *
 * @return string
 */
function superstore_get_template_path() {
	return apply_filters( 'superstore_template_path', 'superstore/' );
}

/**
 * Get superstore plugin path.
 *
 * @return string
 */
function superstore_get_plugin_path() {
	return untrailingslashit( plugin_dir_path( SUPERSTORE_PLUGIN_FILE ) );
}

/**
 * Get template part (First priority is theme directory - yourtheme/superstore/slug-name.php or yourtheme/superstore/slug.php)
 *
 * @param string $slug Slug.
 * @param string $name Name.
 * @param array  $args Args.
 */
function superstore_get_template_part( $slug, $name = '', $args = array() ) {
	$defaults = array(
		'pro' => false,
	);

	$args = wp_parse_args( $args, $defaults );

	if ( $args && is_array( $args ) ) {
        // phpcs:ignore WordPress.PHP.DontExtract.extract_extract
		extract( $args );
	}

	$template = '';

	$template = locate_template( array( superstore_get_template_path() . "{$slug}-{$name}.php", superstore_get_template_path() . "{$slug}.php" ) );

	$template_path = apply_filters( 'superstore_set_template_path', superstore_get_plugin_path() . '/templates', $template, $args );

	if ( ! $template && $name && file_exists( $template_path . "/{$slug}-{$name}.php" ) ) {
		$template = $template_path . "/{$slug}-{$name}.php";
	}

	if ( ! $template && ! $name && file_exists( $template_path . "/{$slug}.php" ) ) {
		$template = $template_path . "/{$slug}.php";
	}

	$template = apply_filters( 'superstore_get_template_part', $template, $slug, $name );

	if ( $template ) {
		include $template;
	}
}

/**
 * Superstore custom capabilities
 *
 * @return array
 */
function superstore_get_capabilities() {
	$capabilities = array();

	$capability_types = array( 'product', 'order', 'coupon', 'payment' );

	foreach ( $capability_types as $capability_type ) {

		$capabilities[ $capability_type ] = array(
			/* translators: 1: Capability type */
			"superstore_read_{$capability_type}"   => sprintf( __( 'Read  %s', 'superstore' ), $capability_type ),
			/* translators: 1: Capability type */
			"superstore_edit_{$capability_type}"   => sprintf( __( 'Edit  %s', 'superstore' ), $capability_type ),
			/* translators: 1: Capability type */
			"superstore_add_{$capability_type}"    => sprintf( __( 'Add  %s', 'superstore' ), $capability_type ),
			/* translators: 1: Capability type */
			"superstore_delete_{$capability_type}" => sprintf( __( 'Delete  %s', 'superstore' ), $capability_type ),
			/* translators: 1: Capability type */
			"superstore_manage_{$capability_type}" => sprintf( __( 'Manage  %s', 'superstore' ), $capability_type ),
		);
	}

	return apply_filters( 'superstore_capabilities', $capabilities );
}

/**
 * Get page permalink based on context
 *
 * @param string $page Page name.
 * @param string $context Context name.
 *
 * @return string url of the page
 */
function superstore_get_page_permalink( $page, $context = 'superstore' ) {
	if ( 'woocommerce' === $context ) {
		$page_id = wc_get_page_id( $page );
	} else {
		$page_id = superstore_get_option( $page, 'superstore_pages' );
	}

	return apply_filters( 'superstore_page_permalink', get_permalink( $page_id ), $page_id, $context );
}

/**
 * Retrive field value with index from sections
 *
 * @param array  $sections Form sections.
 * @param string $section_name Form sections name.
 * @return array
 */
function superstore_get_form_field_values_from_sections( $sections, $section_name = '' ) {
	$values = array();

	foreach ( $sections as $section ) {
		foreach ( $section['fields'] as $key => $field ) {
			if ( ! superstore_get_option( $key, $section_name ) ) {
				if ( 'multiple' === $field['type'] ) {
					$values[ $key ] = array();
					foreach ( $field['items'] as $field_key => $item ) {
						if ( ! array_key_exists( 'default', $item ) ) {
							$values[ $key ][ $field_key ] = '';
						} else {
							$values[ $key ][ $field_key ] = $item['default'];
						}
					}
				} else {
					if ( ! array_key_exists( 'default', $field ) ) {
						$values[ $key ] = '';
					} else {
						$values[ $key ] = $field['default'];
					}
				}
			} else {
				$values[ $key ] = superstore_get_option( $key, $section_name );
			}
		}
	}

	return $values;
}

/**
 * Superstore logging
 *
 * Valid levels can be found in `WC_Log_Levels` class
 *
 * Description of levels:
 *     'emergency': System is unusable.
 *     'alert': Action must be taken immediately.
 *     'critical': Critical conditions.
 *     'error': Error conditions.
 *     'warning': Warning conditions.
 *     'notice': Normal but significant condition.
 *     'info': Informational messages.
 *     'debug': Debug-level messages.
 *
 * @param string $message Message.
 * @param string $level Level.
 * @return mixed
 */
function superstore_log( $message, $level = 'debug' ) {
	$logger  = wc_get_logger();
	$context = array( 'source' => 'superstore' );

	return $logger->log( $level, $message, $context );
}

/**
 * Access dynamically
 *
 * @param Object $object Object.
 * @param String $prop Prop.
 * @param String $callback If object fetched by different callback.
 * @return $prop
 */
function superstore_get_prop( $object, $prop, $callback = false ) {
	if ( version_compare( WC_VERSION, '3.0', '>' ) ) {
		$fn_name = $callback ? $callback : 'get_' . $prop;
		return $object->$fn_name();
	}

	return $object->$prop;
}

/**
 * Delete bulk cache by group name
 *
 * @param string $group Group.
 */
function superstore_cache_delete_group( $group ) {
	$keys = get_option( $group, array() );

	if ( ! empty( $keys ) ) {
		foreach ( $keys as $key ) {
			wp_cache_delete( $key, $group );
			unset( $keys[ $key ] );
		}
	}

	update_option( $group, $keys );
}

/**
 * Unset hook for anonymous class
 *
 * @param string $hook_name Hook name.
 * @param string $class_name Class name.
 * @param string $method_name Method name.
 * @param int    $priority Priority.
 * @return bool
 */
function superstore_unset_hook( $hook_name = '', $class_name = '', $method_name = '', $priority = 0 ) {
	global $wp_filter;

	if ( ! isset( $wp_filter[ $hook_name ][ $priority ] ) || ! is_array( $wp_filter[ $hook_name ][ $priority ] ) ) {
		return false;
	}

	foreach ( (array) $wp_filter[ $hook_name ][ $priority ] as $unique_id => $filter_array ) {
		if ( isset( $filter_array['function'] ) && is_array( $filter_array['function'] ) ) {
			if ( is_object( $filter_array['function'][0] ) && get_class( $filter_array['function'][0] ) && get_class( $filter_array['function'][0] ) === $class_name && $filter_array['function'][1] === $method_name ) {
				if ( is_a( $wp_filter[ $hook_name ], 'WP_Hook' ) ) {
					unset( $wp_filter[ $hook_name ]->callbacks[ $priority ][ $unique_id ] );
				} else {
					unset( $wp_filter[ $hook_name ][ $priority ][ $unique_id ] );
				}
			}
		}
	}

	return false;
}

/**
 * Change product author
 *
 * @param  object  $product Product.
 * @param  integer $seller_id Seller ID.
 */
function superstore_override_product_author( $product, $seller_id ) {
	wp_update_post(
		array(
			'ID'          => $product->get_id(),
			'post_author' => $seller_id,
		)
	);

	do_action( 'superstore_after_override_product_author', $product, $seller_id );
}

/**
 * Get client ip address
 *
 * @return string
 */
function superstore_get_client_ip() {
	$ipaddress = '';
	$_server   = $_SERVER;

	if ( isset( $_server['HTTP_CLIENT_IP'] ) ) {
		$ipaddress = $_server['HTTP_CLIENT_IP'];
	} elseif ( isset( $_server['HTTP_X_FORWARDED_FOR'] ) ) {
		$ipaddress = $_server['HTTP_X_FORWARDED_FOR'];
	} elseif ( isset( $_server['HTTP_X_FORWARDED'] ) ) {
		$ipaddress = $_server['HTTP_X_FORWARDED'];
	} elseif ( isset( $_server['HTTP_FORWARDED_FOR'] ) ) {
		$ipaddress = $_server['HTTP_FORWARDED_FOR'];
	} elseif ( isset( $_server['HTTP_FORWARDED'] ) ) {
		$ipaddress = $_server['HTTP_FORWARDED'];
	} elseif ( isset( $_server['REMOTE_ADDR'] ) ) {
		$ipaddress = $_server['REMOTE_ADDR'];
	} else {
		$ipaddress = 'UNKNOWN';
	}

	return $ipaddress;
}

/**
 * Count sellers
 *
 * @return array
 */
function superstore_count_sellers() {
	$data               = array();
	$enabled_args       = array(
		// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
		'meta_query' => array(
			array(
				'key'     => 'superstore_enabled',
				'value'   => 'yes',
				'compare' => 'LIKE',
			),
		),
	);
	$not_enabled_args   = array(
		// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
		'meta_query' => array(
			array(
				'key'     => 'superstore_enabled',
				'value'   => 'no',
				'compare' => 'LIKE',
			),
		),
	);
	$featured_args      = array(
		// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
		'meta_query' => array(
			array(
				'key'     => 'superstore_featured',
				'value'   => 'yes',
				'compare' => 'LIKE',
			),
		),
	);
	$current_month_args = array(
		'date_query' => array(
			array(
				'after'     => '1 month ago',
				'inclusive' => true,
			),
		),
	);

	$all           = superstore()->seller->get_total();
	$enabled       = superstore()->seller->get_total( $enabled_args );
	$not_enabled   = superstore()->seller->get_total( $not_enabled_args );
	$featured      = superstore()->seller->get_total( $featured_args );
	$current_month = superstore()->seller->get_total( $current_month_args );

	$data = array(
		'all'           => $all,
		'enabled'       => $enabled,
		'not_enabled'   => $not_enabled,
		'featured'      => $featured,
		'current_month' => $current_month,
	);

	return apply_filters( 'superstore_seller_count', $data );
}

/**
 * Get earnings by order of a seller or admin.
 *
 * @param int    $order_id Order ID.
 * @param string $context For seller or admin.
 * @return float
 */
function superstore_get_earnings_by_order( $order_id, $context = 'seller' ) {
	$earning = get_post_meta( $order_id, '_superstore_order_earnings', true ) ? get_post_meta( $order_id, '_superstore_order_earnings', true ) : 0;

	$order         = wc_get_order( $order_id );
	$product_price = 0;

	foreach ( $order->get_items() as $item_id => $item ) {
		if ( ! $item->get_product() ) {
			continue;
		}

		$product_price += (float) $item->get_total();
	}

	if ( 'refunded' === $order->get_status() ) {
		$product_price = 0;
	}

	$earning = (float) $earning;
	$earning = 'admin' === $context ? $product_price - $earning : $earning;
	$earning = (float) $earning;

	if ( superstore_get_option( 'shipping_fee_recipient', 'superstore_general', 'seller' ) === $context ) {
		$earning += (float) $order->get_total_shipping() - (float) $order->get_total_shipping_refunded();
	}

	if ( superstore_get_option( 'tax_fee_recipient', 'superstore_general', 'seller' ) === $context ) {
		$earning += (float) $order->get_total_tax() - (float) $order->get_total_tax_refunded();
	}

	return $earning;
}

/**
 * Get earnings by seller.
 *
 * @param int    $seller_id Seller ID.
 * @param string $context For seller or admin.
 * @return float
 */
function superstore_get_earnings_by_seller( $seller_id, $context = 'seller' ) {
	if ( ! superstore_is_user_seller( $seller_id ) ) {
		return new \WP_Error( __( 'This user is not a seller', 'superstore' ), 407 );
	}

	$seller_orders = superstore()->order->get_seller_orders( $seller_id );

	if ( ! $seller_orders ) {
		return new \WP_Error( __( 'No order found', 'superstore' ), 407 );
	}

	$earnings = 0;

	foreach ( $seller_orders as $order ) {
		$earnings += (float) superstore_get_earnings_by_order( $order->get_id(), $context );
	}

	return $earnings;
}

/**
 * Check if a user is seller
 *
 * @param int $user_id User ID.
 * @return bool
 */
function superstore_is_user_seller( $user_id ) {
	if ( ! user_can( $user_id, 'manage_superstore' ) ) {
		return false;
	}

	return true;
}

/**
 * Get sellers by woocommerce order
 *
 * @param WC_Order|int $order Order.
 * @return array $sellers
 */
function superstore_get_sellers_by_order( $order ) {
	if ( ! $order instanceof WC_Order ) {
		$order = wc_get_order( $order );
	}

	$order_items = $order->get_items();

	$sellers = array();

	foreach ( $order_items as $item ) {
		$seller_id = get_post_field( 'post_author', $item['product_id'] );

		// Hook to edit seller id at run time.
		$seller_id = apply_filters( 'superstore_get_sellers_by_order', $seller_id, $item );
		if ( ! empty( $seller_id ) ) {
			$sellers[ $seller_id ][] = $item;
		}
	}

	return $sellers;
}

/**
 * Get seller by woocommerce order
 *
 * Return false if multiple sellers|post_author found
 *
 * @param WC_Order|int $order Order ID.
 * @return obj|0 on failure
 */
function superstore_get_seller_by_order( $order ) {
	if ( ! $order instanceof WC_Order ) {
		$order = wc_get_order( $order );
		if ( ! $order ) {
			return false;
		}
	}

	$cache_key   = 'superstore_get_seller_' . $order->get_id();
	$cache_group = 'superstore_get_seller_by_order';
	$seller      = wp_cache_get( $cache_key, $cache_group );
	$items       = array();
	$seller_id   = 0;

	// if seller is not found, try to retrieve it via line items.
	if ( ! $seller ) {
		$items = $order->get_items( 'line_item' );

		if ( ! $items ) {
			return false;
		}

		$product_id = current( $items )->get_product_id();
		$seller_id  = get_post_field( 'post_author', $product_id );
		$seller_id  = $seller_id ? absint( $seller_id ) : 0;
		$seller     = superstore()->seller->crud_seller( $seller_id );

		wp_cache_set( $cache_key, $seller, $cache_group );

		return apply_filters( 'superstore_get_seller_by_order', superstore()->seller->crud_seller( $seller_id ), $items );
	} else {
		return apply_filters( 'superstore_get_seller_by_order', $seller, $items );
	}
}

/**
 * Superstore get seller by product
 *
 * @param int|object $product Product ID or Product Object.
 * @return SuperStore_Seller|false on faiure
 */
function superstore_get_seller_by_product( $product ) {
	if ( ! $product instanceof WC_Product ) {
		$product = wc_get_product( $product );
	}

	if ( ! $product ) {
		return false;
	}

	$seller_id = get_post_field( 'post_author', $product->get_id() );

	if ( ! $seller_id && 'variation' === $product->get_type() ) {
		$seller_id = get_post_field( 'post_author', $product->get_parent_id() );
	}

	$seller_id = apply_filters( 'superstore_get_seller_by_product', $seller_id, $product );

	return superstore()->seller->crud_seller( $seller_id );
}

/**
 * Get a store url
 *
 * @param int $seller_id Seller ID.
 * @return string
 */
function superstore_get_store_url( $seller_id ) {
	if ( ! $seller_id ) {
		return '';
	}

	$userdata      = get_userdata( $seller_id );
	$user_nicename = $userdata ? $userdata->user_nicename : '';
	$store_url     = superstore_get_page_permalink( 'stores' ) . '/#/' . $user_nicename . '/';

	return $store_url;
}

/**
 * Get active payments methods of a seller.
 *
 * @param int $seller_id Seller id.
 * @return array
 */
function superstore_get_seller_active_payment_methods( $seller_id ) {
	$methods        = superstore()->seller->crud_seller( $seller_id )->get_payment_method();
	$active_methods = array();

	if ( ! empty( $methods['paypal_email'] ) ) {
		$active_methods[] = 'paypal';
	}

	if ( ! empty( $methods['skrill_email'] ) ) {
		$active_methods[] = 'skrill';
	}

	if ( ! empty( $methods['bank_ac_number'] ) && ! empty( $methods['bank_ac_name'] ) && ! empty( $methods['bank_name'] ) ) {
		$active_methods[] = 'bank';
	}

	return apply_filters( 'superstore_get_seller_active_payment_methods', $active_methods, $seller_id );
}
