<?php

namespace Binarithm\Superstore;

use WC_Product_Query;

/**
 * Superstore product class
 */
class Product {

	/**
	 * Read a seller products
	 *
	 * @param array $seller_id Seller ID.
	 * @param array $args Filter products.
	 * @return array
	 */
	public function get_seller_products( $seller_id, $args = array() ) {
		$seller_products = array();

		$seller_id = $seller_id ? $seller_id : get_current_user_id();

		if ( ! $seller_id ) {
			return $seller_products;
		} else {
			$seller_id = absint( $seller_id );
		}

		if ( ! superstore_is_user_seller( $seller_id ) ) {
			return $seller_products;
		}

		$defaults = array(
			'limit'  => -1,
			'author' => $seller_id,
		);

		$args = wp_parse_args( $args, $defaults );

		return wc_get_products( $args );
	}

	/**
	 * Get product views.
	 *
	 * @param array $seller_id Seller ID.
	 * @return array
	 */
	public function get_product_views( $seller_id = 0 ) {
		global $wpdb;

		$cache_key       = 'superstore-product-views-' . $seller_id;
		$product_views   = wp_cache_get( $cache_key, 'superstore_product_views' );
		$specific_seller = $seller_id ? 'AND p.post_author = ' . $seller_id : null;

		if ( false === $product_views ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
			$count = $wpdb->get_row(
				$wpdb->prepare(
					"SELECT SUM(meta_value) as product_views
	                FROM {$wpdb->postmeta} AS meta
	                LEFT JOIN {$wpdb->posts} AS p ON p.ID = meta.post_id
	                WHERE meta.meta_key = 'product_views' $specific_seller AND p.post_status IN ('publish', 'pending', 'draft')"
				)
			);

			$product_views = $count->product_views;

			wp_cache_set( $cache_key, $product_views, 'superstore_page_view', 3600 * 4 );
		}

		return (int) $product_views;
	}

	/**
	 * Get avarage product ratings.
	 *
	 * @param  int $seller_id Seller ID.
	 * @return int
	 */
	public function get_avarage_product_ratings( $seller_id = 0 ) {
		global $wpdb;

		$specific_seller = $seller_id ? 'AND p.post_author = ' . $seller_id : null;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$result = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT AVG(cm.meta_value) as average, COUNT(wc.comment_ID) as count FROM $wpdb->posts p
		        INNER JOIN $wpdb->comments wc ON p.ID = wc.comment_post_ID
		        LEFT JOIN $wpdb->commentmeta cm ON cm.comment_id = wc.comment_ID
		        WHERE p.post_type = 'product' $specific_seller AND p.post_status = 'publish'
		        AND ( cm.meta_key = 'rating' OR cm.meta_key IS NULL) AND wc.comment_approved = 1
		        ORDER BY wc.comment_post_ID"
			)
		);

		$ratings = apply_filters(
			'superstore_avarage_product_ratings',
			array(
				'avarage_ratings' => number_format( $result->average, 2 ),
				'total_reviews'   => (int) $result->count,
			),
			$seller_id
		);

		return $ratings;
	}

	/**
	 * Get featured products
	 *
	 * @param array $args Filter products.
	 * @return array
	 */
	public function get_featured_products( $args = array() ) {
		$defaults['limit'] = -1;

		if ( version_compare( WC_VERSION, '2.7', '>' ) ) {
			$product_visibility_term_ids = wc_get_product_visibility_term_ids();

			$defaults['tax_query'][] = array(
				'taxonomy' => 'product_visibility',
				'field'    => 'term_taxonomy_id',
				'terms'    => is_search() ? $product_visibility_term_ids['exclude-from-search'] : $product_visibility_term_ids['exclude-from-catalog'],
				'operator' => 'NOT IN',
			);

			$defaults['tax_query'][] = array(
				'taxonomy' => 'product_visibility',
				'field'    => 'term_taxonomy_id',
				'terms'    => $product_visibility_term_ids['featured'],
			);
		} else {

			// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			$defaults['meta_query'] = array(
				array(
					'key'     => '_visibility',
					'value'   => array( 'catalog', 'visible' ),
					'compare' => 'IN',
				),
				array(
					'key'   => '_featured',
					'value' => 'yes',
				),
			);
		}

		$args = wp_parse_args( $args, $defaults );

		return wc_get_products( apply_filters( 'superstore_featured_products_query', $args ) );
	}

	/**
	 * Get latest products
	 *
	 * @param array $args Filter products.
	 * @return array
	 */
	public function get_latest_products( $args = array() ) {
		$defaults['limit'] = -1;

		if ( version_compare( WC_VERSION, '2.7', '>' ) ) {
			$product_visibility_term_ids = wc_get_product_visibility_term_ids();

			// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
			$defaults['tax_query'] = array(
				'taxonomy' => 'product_visibility',
				'field'    => 'term_taxonomy_id',
				'terms'    => is_search() ? $product_visibility_term_ids['exclude-from-search'] : $product_visibility_term_ids['exclude-from-catalog'],
				'operator' => 'NOT IN',
			);
		} else {
			// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			$defaults['meta_query'] = array(
				array(
					'key'     => '_visibility',
					'value'   => array( 'catalog', 'visible' ),
					'compare' => 'IN',
				),
			);
		}

		$args = wp_parse_args( $args, $defaults );

		return wc_get_products( apply_filters( 'superstore_latest_products_query', $args ) );
	}

	/**
	 * Get best Selling Products
	 *
	 * @param array $args Filter products.
	 * @return array
	 */
	public function get_best_selling_products( $args = array() ) {
		// phpcs:ignore Generic.Formatting.MultipleStatementAlignment.IncorrectWarning
		$defaults['limit']    = -1;
		// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
		$defaults['meta_key'] = 'total_sales';
		$defaults['orderby']  = 'meta_value_num';

		if ( version_compare( WC_VERSION, '2.7', '>' ) ) {
			$product_visibility_term_ids = wc_get_product_visibility_term_ids();
			// phpcs:ignore Generic.Formatting.MultipleStatementAlignment.IncorrectWarning, WordPress.DB.SlowDBQuery.slow_db_query_tax_query
			$defaults['tax_query']           = array(
				'taxonomy' => 'product_visibility',
				'field'    => 'term_taxonomy_id',
				'terms'    => is_search() ? $product_visibility_term_ids['exclude-from-search'] : $product_visibility_term_ids['exclude-from-catalog'],
				'operator' => 'NOT IN',
			);
		} else {
			// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			$defaults['meta_query'] = array(
				array(
					'key'     => '_visibility',
					'value'   => array( 'catalog', 'visible' ),
					'compare' => 'IN',
				),
			);
		}

		$args = wp_parse_args( $args, $defaults );

		return wc_get_products( apply_filters( 'superstore_best_selling_products_query', $args ) );
	}

	/**
	 * Get top rated product
	 *
	 * @param array $args Filter products.
	 * @return array
	 */
	public function get_top_rated_products( $args = array() ) {
		$defaults['limit'] = -1;
		if ( version_compare( WC_VERSION, '2.7', '>' ) ) {
			$product_visibility_term_ids = wc_get_product_visibility_term_ids();

			// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
			$defaults['tax_query'] = array(
				'taxonomy' => 'product_visibility',
				'field'    => 'term_taxonomy_id',
				'terms'    => is_search() ? $product_visibility_term_ids['exclude-from-search'] : $product_visibility_term_ids['exclude-from-catalog'],
				'operator' => 'NOT IN',
			);
		} else {
			// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			$defaults['meta_query'] = array(
				array(
					'key'     => '_visibility',
					'value'   => array( 'catalog', 'visible' ),
					'compare' => 'IN',
				),
			);
		}

		$args = wp_parse_args( $args, $defaults );

		add_filter( 'posts_clauses', array( 'WC_Shortcodes', 'order_by_rating_post_clauses' ) );
		$products = wc_get_products( apply_filters( 'superstore_top_rated_products_query', $args ) );
		remove_filter( 'posts_clauses', array( 'WC_Shortcodes', 'order_by_rating_post_clauses' ) );

		return $products;
	}
}
