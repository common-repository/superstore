<?php
namespace Binarithm\Superstore\Widgets;

use WP_Widget;

/**
 * Superstore latest products widgets class
 */
class LatestProducts extends WP_Widget {

	/**
	 * Constructor
	 *
	 * @return void
	 */
	public function __construct() {
		$widget_ops = array(
			'classname'   => 'woocommerce widget_products superstore-latest-products',
			'description' => 'To display latest products of a store',
		);
		parent::__construct( 'superstore-latest-products', 'Superstore: Latest products', $widget_ops );
	}

	/**
	 * Outputs the HTML for this widget.
	 *
	 * @param array $args An array of standard parameters for widgets in this theme.
	 * @param array $instance An array of settings for this widget instance.
	 * @return void Echoes it's output
	 */
	public function widget( $args, $instance ) {
		$title           = isset( $instance['title'] ) ? apply_filters( 'widget_title', $instance['title'] ) : '';
		$no_of_product   = isset( $instance['no_of_product'] ) ? $instance['no_of_product'] : 8;
		$show_rating     = isset( $instance['show_rating'] ) ? $instance['show_rating'] : false;
		$hide_outofstock = isset( $instance['hide_outofstock'] ) ? $instance['hide_outofstock'] : false;

		$args = array(
			'post_type'           => 'product',
			'post_status'         => 'publish',
			'ignore_sticky_posts' => 1,
			'limit'               => $no_of_product,
		);

		if ( $hide_outofstock ) {
			// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			$args['meta_query'] = array(
				array(
					'key'     => '_stock_status',
					'value'   => 'outofstock',
					'compare' => '!=',
				),
			);
		}

		$r = superstore()->product->get_latest_products( $args );

		if ( array_key_exists( 'before_widget', $args ) ) {
			echo wp_kses_post( $args['before_widget'] );
		} else {
			do_action( 'before_widget' );
		}

		if ( ! empty( $title ) ) {
			if ( array_key_exists( 'before_title', $args ) && array_key_exists( 'after_title', $args ) ) {
				echo wp_kses_post( '<h2>' . $args['before_title'] . $title . $args['after_title'] . '</h2>' );
			} else {
				do_action( 'before_title' );
				echo wp_kses_post( '<h2>' . $title . '</h2>' );
				do_action( 'after_title' );
			}
		}

		superstore_get_template_part(
			'widgets/product',
			'',
			array(
				'r'           => $r,
				'show_rating' => $show_rating,
			)
		);

		if ( array_key_exists( 'after_widget', $args ) ) {
			echo wp_kses_post( $args['after_widget'] );
		} else {
			do_action( 'after_widget' );
		}

		wp_reset_postdata();
	}

	/**
	 * Deals with the settings when they are saved by the admin. Here is
	 * where any validation should be dealt with.
	 *
	 * @param array $new_instance An array of new settings as submitted by the admin.
	 * @param array $old_instance An array of the previous settings.
	 * @return array The validated and (if necessary) amended settings
	 */
	public function update( $new_instance, $old_instance ) {
		$instance                    = array();
		$instance['title']           = ( ! empty( $new_instance['title'] ) ) ? wp_strip_all_tags( $new_instance['title'] ) : '';
		$instance['no_of_product']   = ( ! empty( $new_instance['no_of_product'] ) && is_numeric( $new_instance['no_of_product'] ) && $new_instance['no_of_product'] > 0 ) ? wp_strip_all_tags( intval( $new_instance['no_of_product'] ) ) : '8';
		$instance['show_rating']     = ( ! empty( $new_instance['show_rating'] ) ) ? wp_strip_all_tags( $new_instance['show_rating'] ) : '';
		$instance['hide_outofstock'] = ( ! empty( $new_instance['hide_outofstock'] ) ) ? wp_strip_all_tags( $new_instance['hide_outofstock'] ) : '';

		return $instance;
	}

	/**
	 * Displays the form for this widget on the Widgets page of the WP Admin area.
	 *
	 * @param array $instance An array of the current settings for this widget.
	 * @return void Echoes it's output
	 */
	public function form( $instance ) {
		$title           = isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : __( 'Latest Products', 'superstore' );
		$no_of_product   = isset( $instance['no_of_product'] ) ? esc_attr( intval( $instance['no_of_product'] ) ) : 8;
		$no_of_product   = '-1' === $no_of_product ? '' : $no_of_product;
		$show_rating     = isset( $instance['show_rating'] ) ? esc_attr( $instance['show_rating'] ) : false;
		$hide_outofstock = isset( $instance['hide_outofstock'] ) ? esc_attr( $instance['hide_outofstock'] ) : false;
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title:', 'superstore' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'no_of_product' ) ); ?>"><?php esc_html_e( 'No of Product:', 'superstore' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'no_of_product' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'no_of_product' ) ); ?>" type="text" value="<?php echo esc_attr( $no_of_product ); ?>">
		</p>
		<p>
			<input id="<?php echo esc_attr( $this->get_field_id( 'show_rating' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'show_rating' ) ); ?>" type="checkbox" value="1" <?php checked( '1', $show_rating ); ?> />
			<label for="<?php echo esc_attr( $this->get_field_id( 'show_rating' ) ); ?>"><?php esc_html_e( 'Show Product Rating', 'superstore' ); ?></label>
		</p>
		<p>
			<input id="<?php echo esc_attr( $this->get_field_id( 'hide_outofstock' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'hide_outofstock' ) ); ?>" type="checkbox" value="1" <?php checked( '1', $hide_outofstock ); ?> />
			<label for="<?php echo esc_attr( $this->get_field_id( 'hide_outofstock' ) ); ?>"><?php esc_html_e( 'Hide Out of Stock', 'superstore' ); ?></label>
		</p>
		<?php
	}
}
