<?php

namespace Binarithm\Superstore\Hooks;

/**
 * Superstore woocommerce general hooks
 */
class WCGeneral {

	/**
	 * Superstore woocommerce general hooks constructor
	 */
	public function __construct() {
		add_action( 'woocommerce_register_form', array( $this, 'add_superstore_seller_register_url' ) );
		add_filter( 'woocommerce_login_redirect', array( $this, 'redirect_to_seller_dashboard' ), 1, 2 );
		add_filter( 'woocommerce_email_headers', array( $this, 'add_seller_email' ), 10, 3 );
		add_filter( 'woocommerce_dashboard_status_widget_sales_query', array( $this, 'add_superstore_data' ) );
		add_filter( 'woocommerce_email_recipient_cancelled_order', array( $this, 'notify_seller' ), 10, 2 );
		add_action( 'phpmailer_init', array( $this, 'exclude_child_order_email' ) );
		add_action( 'woocommerce_before_single_product', array( $this, 'update_product_views' ) );
		add_action( 'woocommerce_account_dashboard', array( $this, 'add_goto_button' ) );
		add_filter( 'woocommerce_product_data_store_cpt_get_products_query', array( $this, 'add_search_by_name_query' ), 10, 2 );
	}

	/**
	 * Search woocommerce products by name
	 *
	 * @param array $query Query.
	 * @param array $query_vars Query vars.
	 * @return array
	 */
	public function add_search_by_name_query( $query, $query_vars ) {
		if ( isset( $query_vars['like_name'] ) && ! empty( $query_vars['like_name'] ) ) {
			$query['s'] = esc_attr( $query_vars['like_name'] );
		}

		return $query;
	}

	/**
	 * Add go to seller dashboard button on woocommerce my acount page
	 */
	public function add_goto_button() {

		if ( ! superstore_is_user_seller( get_current_user_id() ) ) {
			return;
		}

		$show = superstore_get_option( 'show_goto_seller_dashboard_button_on_wc_my_account', 'superstore_general', 'yes' );
		if ( 'yes' !== $show ) {
			return;
		}

		$url      = superstore_get_page_permalink( 'seller_account' );
		$btn_text = superstore_get_option( 'goto_seller_dashboard_button_text', 'superstore_general', 'Go to seller dashboard' );

		printf(
			'<p><a href="%s" class="superstore-button" >%s</a></p>',
			esc_url( $url ),
			// phpcs:ignore WordPress.WP.I18n.MissingTranslatorsComment, WordPress.WP.I18n.NoEmptyStrings
			sprintf( esc_html__( '%s', 'superstore' ), esc_html( $btn_text ) )
		);

		?>
			<style>
				.superstore-button {
					color: white;
					text-decoration: none !important;
					background-color: #6f6af8;
					border-radius: 3px;
					margin: 5px 0;
					padding: 5px 8px;
				}
			</style>

		<?php
	}

	/**
	 * Update woocommerce product views.
	 */
	public function update_product_views() {
		if ( is_singular() ) {
			$key     = 'product_views';
			$post_id = get_the_ID();
			$count   = (int) get_post_meta( $post_id, $key, true );
			$count++;
			update_post_meta( $post_id, $key, $count );
		}
	}

	/**
	 * Go to superstore seller account
	 */
	public function add_superstore_seller_register_url() {

		if ( 'yes' !== superstore_get_option( 'enable_registration', 'superstore_general', 'yes' ) ) {
			return;
		}

		$hide_url_on_wc_myaccount = superstore_get_option( 'hide_seller_register_url_from_wc_register_form', 'superstore_general', 'no' );
		if ( 'yes' === $hide_url_on_wc_myaccount ) {
			return;
		}

		$url_text                = superstore_get_option( 'seller_register_url_text', 'superstore_general', 'Become a seller' );
		$url_style               = superstore_get_option( 'seller_register_url_style', 'superstore_appearance', 'button' ); // button or text.
		$button_background_color = superstore_get_option( 'seller_register_button_background_color', 'superstore_appearance', '#6f6af8' ); // Any css color.
		$button_text_color       = superstore_get_option( 'seller_register_button_text_color', 'superstore_appearance', 'white' ); // Any css color.

		$url = superstore_get_page_permalink( 'seller_account' );
		if ( 'text' === $url_style ) {
			printf(
				'<p><a href="%s"><strong>%s</strong></a></p>',
				esc_url( $url ),
				// phpcs:ignore WordPress.WP.I18n.MissingTranslatorsComment, WordPress.WP.I18n.NoEmptyStrings
				sprintf( esc_html__( '%s', 'superstore' ), esc_html( $url_text ) )
			);
		} elseif ( 'button' === $url_style ) {
			printf(
				'<p><a href="%s" class="superstore-button" ><strong>%s</strong></a></p>',
				esc_url( $url ),
				// phpcs:ignore WordPress.WP.I18n.MissingTranslatorsComment, WordPress.WP.I18n.NoEmptyStrings
				sprintf( esc_html__( '%s', 'superstore' ), esc_html( $url_text ) )
			);
		}

		?>
			<style>
				.superstore-button {
					color: <?php echo esc_html( $button_text_color ); ?>;
					text-decoration: none !important;
					background-color: <?php echo esc_html( $button_background_color ); ?>;
					border-radius: 3px;
					margin: 5px 0;
					padding: 5px 8px;
				}
			</style>

		<?php
	}

	/**
	 * Redirect a seller to seller dashboard if login with wc myaccount
	 *
	 * @param string $redirect_to Redirect url.
	 * @param object $user Seller.
	 * @return string  $redirect_to
	 */
	public function redirect_to_seller_dashboard( $redirect_to, $user ) {
		if ( user_can( $user, 'manage_superstore' ) ) {
			$dashboard_url = superstore_get_page_permalink( 'seller_account' );

			if ( -1 !== $dashboard_url ) {
				$redirect_to = $dashboard_url;
			}
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		if ( ! empty( $_POST['redirect_to'] ) ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
			$redirect_to = esc_url_raw( $_POST['redirect_to'] );
		}

		return $redirect_to;
	}

	/**
	 * Add seller email on customers note mail
	 *
	 * @param string $headers Headers.
	 * @param string $id ID.
	 * @param object $order Order.
	 * @return string $headers
	 */
	public function add_seller_email( $headers, $id, $order ) {
		if ( ! ( $order instanceof WC_Order ) ) {
			return $headers;
		}

		if ( 'customer_note' === $id ) {
			foreach ( $order->get_items( 'line_item' ) as $item ) {
				$product_id  = $item['product_id'];
				$author      = get_post_field( 'post_author', $product_id );
				$author_data = get_userdata( absint( $author ) );
				$user_email  = $author_data->user_email;

				$headers .= "Reply-to: <$user_email>\r\n";
			}
		}

		return $headers;
	}

	/**
	 * Add superstore dashboard repor data in  woocommerce admin dashboard sales Report
	 *
	 * @param array $query Query.
	 * @global WPDB $wpdb
	 * @return $query
	 */
	public function add_superstore_data( $query ) {
		global $wpdb;

		$query['where'] .= " AND posts.ID NOT IN ( SELECT post_parent FROM {$wpdb->posts} WHERE post_type IN ( '" . implode( "','", array_merge( wc_get_order_types( 'sales-reports' ), array( 'shop_order_refund' ) ) ) . "' ) )";

		return $query;
	}

	/**
	 * On order cancellation send email to seller
	 *
	 * @param string $recipient Recipient.
	 * @param object $order Order.
	 */
	public function notify_seller( $recipient, $order ) {
		if ( ! $order instanceof \WC_Order ) {
			return $recipient;
		}

		$seller_id = superstore_get_seller_by_order( $order->get_id() )->get_id();

		$seller_info  = get_userdata( $seller_id );
		$seller_email = $seller_info->user_email;

		// if admin email & seller email is same.
		if ( false === strpos( $recipient, $seller_email ) ) {
			$recipient .= ',' . $seller_email;
		}

		return $recipient;
	}

	/**
	 * Exclude customer email receipt for child order
	 *
	 * @param array $phpmailer Phpmailer.
	 * @return array
	 */
	public function exclude_child_order_email( &$phpmailer ) {
		// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
		$subject = $phpmailer->Subject;

		$sub_receipt  = __( 'Your {site_title} order receipt from {order_date}', 'superstore' );
		$sub_download = __( 'Your {site_title} order from {order_date} is complete', 'superstore' );

		$sub_receipt  = str_replace(
			array(
				'{site_title}',
				'{order_date}',
			),
			array( wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES ), '' ),
			$sub_receipt
		);
		$sub_download = str_replace(
			array(
				'{site_title}',
				'{order_date} is complete',
			),
			array( wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES ), '' ),
			$sub_download
		);

		// Not a customer receipt mail.
		if ( ( stripos( $subject, $sub_receipt ) === false ) && ( stripos( $subject, $sub_download ) === false ) ) {
			return;
		}

		// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
		$message = $phpmailer->Body;
		$pattern = '/Order: #(\d+)/';
		preg_match( $pattern, $message, $matches );

		if ( isset( $matches[1] ) ) {
			$order_id = $matches[1];
			$order    = get_post( $order_id );

			// Child order found.
			if ( ! is_wp_error( $order ) && 0 !== $order->post_parent ) {
				$phpmailer = null;
			}
		}
	}
}
