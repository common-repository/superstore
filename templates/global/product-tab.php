<?php
/**
 * Superstore seller info in single product tab template
 *
 * @package superstore
 */

?>

<h2><?php esc_html_e( 'Seller Info', 'superstore' ); ?></h2>

<ul>
	<?php do_action( 'superstore_single_product_seller_tab_start', $author, $seller ); ?>

	<li>
		<a href="<?php echo esc_url( $seller->get_store_url() ); ?>"><?php echo esc_html( $seller->get_store_name() ); ?></a>
	</li>

	<?php do_action( 'superstore_single_product_seller_tab_middle', $author, $seller ); ?>
</ul>

<div>
	<?php
	/**
	 * More products from this seller
	 *
	 * @param array $seller_id Seller ID.
	 * @param array $posts_per_page Per page.
	 */
	function superstore_seller_more_products( $seller_id = 0, $posts_per_page = 6 ) {
		global $product, $post;

		if ( 0 === $seller_id || 'more_seller_product' === $seller_id ) {
			$seller_id = $post->post_author;
		}

		if ( ! is_int( $posts_per_page ) ) {
			$posts_per_page = apply_filters( 'superstore_get_more_products_per_page', 6 );
		}

		$args = array(
			'post_type'      => 'product',
			'posts_per_page' => $posts_per_page,
			'orderby'        => 'rand',
			'post__not_in'   => array( $post->ID ),
			'author'         => $seller_id,
		);

		$products = new WP_Query( $args );

		if ( $products->have_posts() ) {
			woocommerce_product_loop_start();

			while ( $products->have_posts() ) {
				$products->the_post();
				wc_get_template_part( 'content', 'product' );
			}

			woocommerce_product_loop_end();
		} else {
			esc_html_e( 'No products found!', 'superstore' );
		}

		wp_reset_postdata();
	}

	superstore_seller_more_products( $seller->get_id() );
	?>

	<?php do_action( 'superstore_single_product_seller_tab_end', $author, $seller ); ?>
</div>
