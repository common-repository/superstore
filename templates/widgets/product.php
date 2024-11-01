<?php
/**
 * Superstore Widget Content Product Template
 *
 * @package superstore
 */

?>

<?php
if ( count( $r ) < 1 ) {
	?>
	<p><?php esc_html_e( 'No products found', 'superstore' ); ?></p> 
	<?php
} else {
	?>
	<ul class="superstore-bestselling-product-widget product_list_widget">
	<?php
	foreach ( $r as $key => $value ) {
		?>
		<li>
			<a href="<?php echo esc_url( $value->get_permalink() ); ?>" title="<?php echo esc_attr( $value->get_name() ); ?>">
				<?php echo wp_kses_post( $value->get_image() ); ?>
				<span class="product-title"><?php echo esc_html( $value->get_name() ); ?></span>
			</a>

			<!-- For WC < 3.0.0  backward compatibility  -->
			<?php if ( version_compare( WC_VERSION, '2.7', '>' ) ) : ?>
				<?php
				if ( ! empty( $show_rating ) ) {
					echo wp_kses_post( wc_get_rating_html( $value->get_average_rating() ) );
				}
				?>
			<?php else : ?>
				<?php
				if ( ! empty( $show_rating ) ) {
					echo wp_kses_post( $value->get_rating_html() );
				}
				?>
			<?php endif ?>

			<?php echo wp_kses_post( $value->get_price_html() ); ?>
		</li>
		<?php
	}
	?>
	</ul>
	<?php
}
