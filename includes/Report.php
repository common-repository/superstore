<?php

namespace Binarithm\Superstore;

/**
 * Superstore report controller class
 */
class Report {

	/**
	 * Get admin dashboard overview
	 *
	 * @return array
	 */
	public function get_admin_overview() {
		$sellers                 = superstore()->seller->get_sellers();
		$args                    = array(
			'ss_meta_query' => array(
				'enabled' => 'no',
			),
		);
		$not_enabled_seller_args = array(
			// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			'meta_query' => array(
				array(
					'key'     => 'superstore_enabled',
					'value'   => 'no',
					'compare' => 'LIKE',
				),
			),
		);
		$not_enabled_sellers     = superstore()->seller->get_total( $not_enabled_seller_args );
		$total_sellers           = superstore()->seller->get_total();

		$current_month_signups_arg = array(
			'ss_meta_query' => array(
				'enabled' => 'no',
			),
		);

		$current_month_signups_arg = array(
			'date_query' => array(
				array(
					'after'     => '1 month ago',
					'inclusive' => true,
				),
			),
		);
		$current_month_signups     = superstore()->seller->get_total( $current_month_signups_arg );

		// Product, order and ratings.
		// phpcs:ignore Generic.Formatting.MultipleStatementAlignment.IncorrectWarning, WordPress.DateTime.CurrentTimeTimestamp.Requested, WordPress.DateTime.RestrictedFunctions.date_date
		$start_date                 = date( 'Y-m-d', strtotime( date( 'Ym', current_time( 'timestamp' ) ) . '01' ) );
		// phpcs:ignore WordPress.DateTime.CurrentTimeTimestamp.Requested, WordPress.DateTime.RestrictedFunctions.date_date
		$end_date                   = date( 'Y-m-d', current_time( 'timestamp' ) );
		$this_month_arg             = array(
			'date_created' => $start_date . '...' . $end_date,
		);
		$pending_product_review_arg = array(
			'status' => 'pending',
		);

		$current_month_sales          = 0;
		$commission_earned            = 0;
		$storage_occupied             = 0;
		$product_views                = 0;
		$all_ratings                  = array();
		$total_pending_products       = 0;
		$current_month_total_products = 0;
		foreach ( $sellers as $seller ) {
			$current_month_orders = superstore()->order->get_seller_orders( $seller->get_id(), $this_month_arg );
			foreach ( $current_month_orders as $order ) {
				$current_month_sales += $order->get_total();
			}

			if ( ! empty( superstore()->product->get_seller_products( $seller->get_id() ) ) ) {
				$current_month_total_products = count( superstore()->product->get_seller_products( $seller->get_id(), $this_month_arg ) );
				$total_pending_products       = count( superstore()->product->get_seller_products( $seller->get_id(), $pending_product_review_arg ) );
			}

			if ( ! is_wp_error( superstore_get_earnings_by_seller( $seller->get_id() ) ) ) {
				$commission_earned += (float) superstore_get_earnings_by_seller( $seller->get_id(), 'admin' );
			}

			$storage_occupied += (float) $this->get_seller_media_overview( $seller->get_id() )['storage_occupied'];

			$product_views += (int) $this->get_seller_products_overview( $seller->get_id() )['views'];

			$all_ratings[] = (float) $this->get_seller_products_overview( $seller->get_id() )['avarage_ratings'];
		}

		$avarage_ratings = array();
		foreach ( $all_ratings as $rating ) {
			if ( empty( $rating ) ) {
				continue;
			}

			$avarage_ratings[] = (float) $rating;
		}
		$avarage_ratings2 = 0;
		if ( ! empty( $avarage_ratings ) ) {
			$avarage_ratings2 = number_format( array_sum( $avarage_ratings ) / count( $avarage_ratings ), 2 );
		}

		// Payment.
		$total_payment_pending = superstore()->payment->get_total_payments( array( 'status' => 'pending' ) );
		$approved_payments     = superstore()->payment->get_payments( array( 'status' => 'approved' ) );
		$total_paid            = 0;
		foreach ( $approved_payments as $payment ) {
			$total_paid += (float) $payment->get_amount();
		}

		// Overview.
		$overview = array(
			array(
				'title'         => wc_price( $current_month_sales ),
				'description'   => __( 'Current month sales from marketplace', 'superstore' ),
				'icon'          => 'mdi-cart-percent',
				'icon_color'    => 'rgba(0, 207, 190, 1)',
				'icon_bg_color' => 'rgba(0, 207, 190, .1)',
			),
			array(
				'title'         => $total_payment_pending,
				'description'   => __( 'Pending withdraw rerquests', 'superstore' ),
				'icon'          => 'mdi-cash-clock',
				'icon_color'    => 'rgba(0, 4, 255, .5)',
				'icon_bg_color' => 'rgba(0, 4, 255, .1)',
			),
			array(
				'title'         => $total_pending_products,
				'description'   => __( 'Pending product reviews', 'superstore' ),
				'icon'          => 'mdi-package-variant',
				'icon_color'    => 'rgba(199, 0, 179, .5)',
				'icon_bg_color' => 'rgba(199, 0, 179, .1)',
			),
			array(
				'title'         => $current_month_total_products,
				'description'   => __( 'Products created this month', 'superstore' ),
				'icon'          => 'mdi-package-variant-plus',
				'icon_color'    => 'rgba(73, 209, 0, .9)',
				'icon_bg_color' => 'rgba(73, 209, 0, .1)',
			),
			array(
				'title'         => $total_sellers,
				'description'   => __( 'Total sellers', 'superstore' ),
				'icon'          => 'mdi-account',
				'icon_color'    => 'rgba(255, 0, 102, .5)',
				'icon_bg_color' => 'rgba(255, 0, 102, .1)',
			),
			array(
				'title'         => $current_month_signups,
				'description'   => __( 'Sellers signup this month', 'superstore' ),
				'icon'          => 'mdi-account-plus',
				'icon_color'    => 'rgba(153, 209, 0, 1)',
				'icon_bg_color' => 'rgba(153, 209, 0, .1)',
			),
			array(
				'icon'          => 'mdi-account-clock',
				'icon_color'    => 'rgba(111, 0, 255, .5)',
				'icon_bg_color' => 'rgba(111, 0, 255, .1)',
				'title'         => $not_enabled_sellers,
				'description'   => __( 'Sellers are not enabled', 'superstore' ),
			),
			array(
				'title'         => size_format( $storage_occupied ) ? size_format( $storage_occupied ) : 0,
				'description'   => __( 'Storage occupied by sellers', 'superstore' ),
				'icon'          => 'mdi-database',
				'icon_color'    => 'rgba(250, 96, 7, .5)',
				'icon_bg_color' => 'rgba(250, 96, 7, .1)',
			),
			array(
				'title'         => wc_price( $commission_earned ),
				'description'   => __( 'Commission earned', 'superstore' ),
				'icon'          => 'mdi-percent-circle',
				'icon_color'    => 'rgba(255, 98, 0, .5)',
				'icon_bg_color' => 'rgba(255, 98, 0, .1)',
			),
			array(
				'title'         => wc_price( $total_paid ),
				'description'   => __( 'Total paid to sellers', 'superstore' ),
				'icon'          => 'mdi-cash-check',
				'icon_color'    => 'rgba(169, 0, 247, .5)',
				'icon_bg_color' => 'rgba(169, 0, 247, .1)',
			),
			array(
				'title'         => $product_views,
				'description'   => __( 'Total marketplace product views', 'superstore' ),
				'icon'          => 'mdi-eye',
				'icon_color'    => 'rgba(227, 0, 155, .5)',
				'icon_bg_color' => 'rgba(227, 0, 155, .1)',
			),
			array(
				'title'         => number_format( $avarage_ratings2, 2 ),
				'description'   => __( 'Avarage marketplace ratings', 'superstore' ),
				'icon'          => 'mdi-star',
				'icon_color'    => 'rgba(255, 230, 0, 1)',
				'icon_bg_color' => 'rgba(255, 230, 0, .1)',
			),
		);

		return apply_filters( 'superstore_admin_overview', $overview );
	}



	/**
	 * Get products overview of a seller.
	 *
	 * @param  int $seller_id Seller id.
	 * @return array
	 */
	public function get_seller_products_overview( $seller_id ) {
		$total     = array(
			'limit'  => -1,
			'author' => $seller_id,
		);
		$published = array(
			'limit'  => -1,
			'status' => 'publish',
			'author' => $seller_id,
		);
		$draft     = array(
			'limit'  => -1,
			'status' => 'draft',
			'author' => $seller_id,
		);
		$pending   = array(
			'limit'  => -1,
			'status' => 'pending',
			'author' => $seller_id,
		);

		$data                    = array();
		$data['total']           = count( wc_get_products( $total ) );
		$data['published']       = count( wc_get_products( $published ) );
		$data['draft']           = count( wc_get_products( $draft ) );
		$data['pending']         = count( wc_get_products( $pending ) );
		$data['views']           = superstore()->product->get_product_views( $seller_id );
		$data['avarage_ratings'] = superstore()->product->get_avarage_product_ratings( $seller_id )['avarage_ratings'];
		$data['total_reviews']   = superstore()->product->get_avarage_product_ratings( $seller_id )['total_reviews'];

		return apply_filters( 'superstore_products_overview', $data );
	}

	/**
	 * Get orders overview of a seller.
	 *
	 * @param  int  $seller_id Seller id.
	 * @param  bool $formatted  Wether to get wc_price formatted price.
	 * @return array
	 */
	public function get_seller_orders_overview( $seller_id, $formatted = false ) {
		$status = array(
			'pending',
			'completed',
			'on-hold',
			'processing',
			'refunded',
			'cancelled',
			'failed',
		);

		$orders = superstore()->order->get_seller_orders( $seller_id );

		$data['total'] = count( $orders );

		$sales = array();
		foreach ( $orders as $order ) {
			$sales[] = (float) $order->get_total();
		}

		$data['sales'] = $formatted ? wc_price( array_sum( $sales ) ) : array_sum( $sales );

		$earnings = 0;
		foreach ( $orders as $order ) {
			$earnings += (float) superstore_get_earnings_by_order( $order->get_id() );
		}
		$data['earnings'] = $formatted ? wc_price( $earnings ) : $earnings;

		foreach ( $status as $value ) {
			$data[ $value ] = count(
				superstore()->order->get_seller_orders(
					$seller_id,
					array(
						'status' => 'wc-' . $value,
					)
				)
			);
		}

		return apply_filters( 'superstore_orders_overview', $data );
	}

	/**
	 * Get payments overview of a seller.
	 *
	 * @param  int  $seller_id Seller id.
	 * @param  bool $formatted Wether to get wc_price formatted total paid amount and balance.
	 * @return array
	 */
	public function get_seller_payments_overview( $seller_id, $formatted = false ) {
		$total           = superstore()->payment->get_total_payments( array( 'user_id' => $seller_id ) );
		$current_balance = superstore()->payment->get_total_balance( array( 'user_id' => $seller_id ) );
		$paid            = superstore()->payment->get_total_paid( array( 'user_id' => $seller_id ) );
		$approved        = superstore()->payment->get_total_payments(
			array(
				'user_id' => $seller_id,
				'status'  => 'approved',
			)
		);
		$pending         = superstore()->payment->get_total_payments(
			array(
				'user_id' => $seller_id,
				'status'  => 'pending',
			)
		);
		$cancelled       = superstore()->payment->get_total_payments(
			array(
				'user_id' => $seller_id,
				'status'  => 'cancelled',
			)
		);

		$data['total']           = $total;
		$data['approved']        = $approved;
		$data['pending']         = $pending;
		$data['cancelled']       = $cancelled;
		$data['paid']            = $formatted ? wc_price( $paid ) : $paid;
		$data['current_balance'] = $formatted ? wc_price( $current_balance ) : $current_balance;

		return apply_filters( 'superstore_payments_overview', $data );
	}

	/**
	 * Get media, files, storage overview of a seller.
	 *
	 * @param  int  $seller_id Seller id.
	 * @param  bool $formatted Wether to get MB, GB etc formatted storage.
	 * @return array
	 */
	public function get_seller_media_overview( $seller_id, $formatted = false ) {
		$total_files       = count( superstore()->media->get_seller_files( array( 'author' => $seller_id ) ) );
		$storage_occupied  = superstore()->media->get_storage_occupied( array( 'author' => $seller_id ) );
		$storage_available = superstore()->media->get_seller_storage_available( array( 'author' => $seller_id ) );
		$storage_available = -1 === $storage_available ? 'unlimited' : $storage_available;

		$data['total_files'] = $total_files;

		if ( $formatted ) {
			$data['storage_occupied']  = size_format( $storage_occupied );
			$data['storage_available'] = 'unlimited' === $storage_available ? 'unlimited' : size_format( $storage_available );
		} else {
			$data['storage_occupied']  = $storage_occupied;
			$data['storage_available'] = 'unlimited' === $storage_available ? 'unlimited' : (float) $storage_available;
		}

		return apply_filters( 'superstore_media_overview', $data );
	}

	/**
	 * Get top 10 sellers
	 *
	 * @return array
	 */
	public function get_top_10_sellers() {
		$sellers        = superstore()->seller->get_sellers();
		$top_10_sellers = array();

		foreach ( $sellers as $key => $seller ) {

			if ( empty( superstore_get_earnings_by_seller( $seller->get_id(), 'admin' ) ) ) {
				continue;
			}

			$orders = superstore()->order->get_seller_orders( $seller->get_id() );
			$sales  = 0;
			foreach ( $orders as $order ) {
				$sales += (float) $order->get_total();
			}

			if ( ! is_wp_error( superstore_get_earnings_by_seller( $seller->get_id(), 'admin' ) ) ) {
				$top_10_sellers[ $key ]['admin_earnings'] = superstore_get_earnings_by_seller( $seller->get_id(), 'admin' );
				$top_10_sellers[ $key ]['id']             = $seller->get_id();
				$top_10_sellers[ $key ]['sales']          = wc_price( $sales );
				$top_10_sellers[ $key ]['store_name']     = $seller->get_store_name();
				$top_10_sellers[ $key ]['ratings']        = $this->get_seller_products_overview( $seller->get_id() )['avarage_ratings'];
			}
		}

		rsort( $top_10_sellers );
		$top_10_sellers = array_slice( $top_10_sellers, 0, 10 );

		foreach ( $top_10_sellers as $key => $value ) {
			$top_10_sellers[ $key ]['admin_earnings'] = wc_price( $top_10_sellers[ $key ]['admin_earnings'] );
		}

		return apply_filters( 'superstore_top_10_sellers', $top_10_sellers );
	}

	/**
	 * Get overview data of a seller
	 *
	 * @param int $seller_id Seller id.
	 * @return array
	 */
	public function get_seller_overview( $seller_id ) {
		// phpcs:ignore Generic.Formatting.MultipleStatementAlignment.IncorrectWarning, WordPress.DateTime.CurrentTimeTimestamp.Requested, WordPress.DateTime.RestrictedFunctions.date_date
		$start_date           = date( 'Y-m-d', strtotime( date( 'Ym', current_time( 'timestamp' ) ) . '01' ) );
		// phpcs:ignore WordPress.DateTime.CurrentTimeTimestamp.Requested, WordPress.DateTime.RestrictedFunctions.date_date
		$end_date             = date( 'Y-m-d', current_time( 'timestamp' ) );
		$this_month_arg       = array(
			'date_created' => $start_date . '...' . $end_date,
		);
		$current_month_orders = superstore()->order->get_seller_orders( $seller_id, $this_month_arg );
		$this_month_sales     = 0;
		foreach ( $current_month_orders as $order ) {
			$this_month_sales += $order->get_total();
		}

		$current_month_sales = wc_price( $this_month_sales );
		$earnings            = $this->get_seller_orders_overview( $seller_id, true )['earnings'];
		$completed_orders    = $this->get_seller_orders_overview( $seller_id, true )['completed'];
		$pending_orders      = $this->get_seller_orders_overview( $seller_id, true )['pending'];
		$published_products  = $this->get_seller_products_overview( $seller_id, true )['published'];
		$pending_products    = $this->get_seller_products_overview( $seller_id, true )['pending'];
		$current_balance     = $this->get_seller_payments_overview( $seller_id, true )['current_balance'];
		$payment_received    = $this->get_seller_payments_overview( $seller_id, true )['paid'];
		$storage_occupied    = $this->get_seller_media_overview( $seller_id, true )['storage_occupied'];
		$storage_available   = $this->get_seller_media_overview( $seller_id, true )['storage_available'];
		$product_views       = $this->get_seller_products_overview( $seller_id, true )['views'];
		$avarage_ratings     = $this->get_seller_products_overview( $seller_id, true )['avarage_ratings'];

		$overview = array(
			array(
				'icon'          => 'mdi-cart-percent',
				'icon_color'    => 'rgba(0, 207, 190, 1)',
				'icon_bg_color' => 'rgba(0, 207, 190, .1)',
				'title'         => $current_month_sales,
				'description'   => 'Sales this month',
			),
			array(
				'icon'          => 'mdi-percent-circle',
				'icon_color'    => 'rgba(255, 98, 0, .5)',
				'icon_bg_color' => 'rgba(255, 98, 0, .1)',
				'title'         => $earnings,
				'description'   => 'Total earnings',
			),
			array(
				'icon'          => 'mdi-cart-check',
				'icon_color'    => 'rgba(0, 4, 255, .5)',
				'icon_bg_color' => 'rgba(0, 4, 255, .1)',
				'title'         => $completed_orders,
				'description'   => 'Total completed orders',
			),
			array(
				'icon'          => 'mdi-cart-outline',
				'icon_color'    => 'rgba(255, 0, 102, .5)',
				'icon_bg_color' => 'rgba(255, 0, 102, .1)',
				'title'         => $pending_orders,
				'description'   => 'Total pending orders',
			),
			array(
				'icon'          => 'mdi-package-variant-closed-check',
				'icon_color'    => 'rgba(73, 209, 0, .9)',
				'icon_bg_color' => 'rgba(73, 209, 0, .1)',
				'title'         => $published_products,
				'description'   => 'Total published products',
			),
			array(
				'icon'          => 'mdi-package-variant-closed',
				'icon_color'    => 'rgba(199, 0, 179, .5)',
				'icon_bg_color' => 'rgba(199, 0, 179, .1)',
				'title'         => $pending_products,
				'description'   => 'Pending product reviews',
			),
			array(
				'icon'          => 'mdi-cash',
				'icon_color'    => 'rgba(153, 209, 0, 1)',
				'icon_bg_color' => 'rgba(153, 209, 0, .1)',
				'title'         => $current_balance,
				'description'   => 'Current balance',
			),
			array(
				'icon'          => 'mdi-cash-plus',
				'icon_color'    => 'rgba(111, 0, 255, .5)',
				'icon_bg_color' => 'rgba(111, 0, 255, .1)',
				'title'         => $payment_received,
				'description'   => 'Total payment received',
			),
			array(
				'icon'          => 'mdi-database-minus',
				'icon_color'    => 'rgba(169, 0, 247, .5)',
				'icon_bg_color' => 'rgba(169, 0, 247, .1)',
				'title'         => $storage_occupied ? $storage_occupied : 0,
				'description'   => 'Total storage occupied',
			),
			array(
				'icon'          => 'mdi-database-plus',
				'icon_color'    => 'rgba(250, 96, 7, .5)',
				'icon_bg_color' => 'rgba(250, 96, 7, .1)',
				'title'         => $storage_available,
				'description'   => 'Total storage available',
			),
			array(
				'icon'          => 'mdi-eye',
				'icon_color'    => 'rgba(227, 0, 155, .5)',
				'icon_bg_color' => 'rgba(227, 0, 155, .1)',
				'title'         => $product_views,
				'description'   => 'Total product Views',
			),
			array(
				'icon'          => 'mdi-star',
				'icon_color'    => 'rgba(255, 230, 0, 1)',
				'icon_bg_color' => 'rgba(255, 230, 0, .1)',
				'title'         => $avarage_ratings,
				'description'   => 'Avarage ratings',
			),
		);

		return apply_filters( 'superstore_seller_overview', $overview );
	}

	/**
	 * Get top 10 products of a seller
	 *
	 * @param int $seller_id Seller id.
	 * @return array
	 */
	public function get_seller_top_10_products( $seller_id ) {
		$orders          = superstore()->order->get_seller_orders( $seller_id );
		$products        = array();
		$top_10_products = array();
		foreach ( $orders as $order ) {
			foreach ( $order->get_items() as $item ) {
				$products[] = $item->get_product();
			}
		}

		$formatted_products = array();

		foreach ( $products as $key => $product ) {
			$top_10_products[ $key ]['items_sold'] = $product->get_total_sales();
			$top_10_products[ $key ]['id']         = $product->get_id();
			$top_10_products[ $key ]['name']       = $product->get_name();
			$top_10_products[ $key ]['ratings']    = $product->get_average_rating();
		}

		$top_10_products = array_map(
			'unserialize',
			array_unique( array_map( 'serialize', $top_10_products ) )
		);

		rsort( $top_10_products );
		$top_10_products = array_slice( $top_10_products, 0, 10 );

		return apply_filters( 'superstore_seller_top_10_products', $top_10_products );
	}
}
