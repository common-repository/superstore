<?php

namespace Binarithm\Superstore\Shortcode;

use Binarithm\Superstore\Abstracts\AbstractShortcode;

/**
 * Superstore stores list shortcode class
 */
class Stores extends AbstractShortcode {

	/**
	 * Superstore stores list
	 *
	 * @var string
	 */
	protected $shortcode = 'superstore-stores';

	/**
	 * Seller stores list template files and properties
	 *
	 * @param array $atts Shortcode atts.
	 */
	public function properties( $atts ) {
		if ( ! function_exists( 'WC' ) ) {
			// phpcs:ignore WordPress.WP.I18n.MissingTranslatorsComment
			return sprintf( __( 'WooCommerce is missing. Install <a href="%s"><strong>WooCommerce</strong></a> first', 'superstore' ), 'http://wordpress.org/plugins/woocommerce/' );
		}

		return '<div id="superstore-stores"></div>';
	}
}
