<?php

namespace Binarithm\Superstore\Abstracts;

/**
 * Superstore shortcode helper
 */
abstract class AbstractShortcode {

	/**
	 * Shortcode name
	 *
	 * @var string
	 */
	protected $shortcode = '';

	/**
	 * Superstore shortcode constructor
	 */
	public function __construct() {
		if ( empty( $this->shortcode ) ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			_doing_it_wrong( static::class, __( 'Shortcode property can not be empty.', 'superstore' ), '1.0' );
		}

		add_shortcode( $this->shortcode, array( $this, 'properties' ) );
	}

	/**
	 * Get current shortcode
	 *
	 * @return string
	 */
	public function get_shortcode() {
		return $this->shortcode;
	}

	/**
	 * Shortcode data
	 *
	 * @param mix $atts Atts.
	 */
	abstract public function properties( $atts );
}
