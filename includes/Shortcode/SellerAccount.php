<?php

namespace Binarithm\Superstore\Shortcode;

use Binarithm\Superstore\Abstracts\AbstractShortcode;

/**
 * Superstore seller account shortcode class
 */
class SellerAccount extends AbstractShortcode {

	/**
	 * Superstore seller account
	 *
	 * @var string
	 */
	protected $shortcode = 'superstore-seller-account';

	/**
	 * Seller dashboard template files and properties
	 *
	 * @param array $atts Shortcode atts.
	 */
	public function properties( $atts ) {
		if ( ! function_exists( 'WC' ) ) {
			// phpcs:ignore WordPress.WP.I18n.MissingTranslatorsComment
			return sprintf( __( 'WooCommerce is missing. Install <a href="%s"><strong>WooCommerce</strong></a> first', 'superstore' ), 'http://wordpress.org/plugins/woocommerce/' );
		}

		if ( ! is_user_logged_in() ) {
			return '<div id="superstore-seller-login-form"></div>';
		} else {
			if ( ! superstore_is_user_seller( get_current_user_id() ) ) {
				return __( 'You are not a seller.', 'superstore' );
			}

			return '<div id="superstore-seller-dashboard"></div>';
		}
	}
}
