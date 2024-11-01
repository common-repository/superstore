<?php

namespace Binarithm\Superstore\Hooks;

/**
 * Superstore product hooks
 */
class Product {

	/**
	 * Superstore admin woocommerce contructor
	 */
	public function __construct() {
		if ( is_admin() ) {
			add_action( 'add_meta_boxes', array( $this, 'add_seller_meta_box' ) );
			add_action( 'woocommerce_process_product_meta', array( $this, 'change_product_author' ), 12, 2 );
			add_action( 'manage_product_posts_custom_column', array( $this, 'add_superstore_author_field_data' ), 99, 2 );
			add_action( 'woocommerce_product_quick_edit_end', array( $this, 'add_superstore_author_field' ) );
			add_action( 'woocommerce_product_bulk_edit_end', array( $this, 'add_superstore_author_field' ) );
			add_action( 'woocommerce_product_quick_edit_save', array( $this, 'save_quick_edit_data' ), 10, 1 );
			add_action( 'woocommerce_product_bulk_edit_save', array( $this, 'save_quick_edit_data' ), 10, 1 );
			add_action( 'pending_to_publish', array( $this, 'notify_seller_on_product_published' ) );
			add_filter( 'manage_edit-product_columns', array( $this, 'add_product_author_columns' ) );
		}

		add_filter( 'woocommerce_product_tabs', array( $this, 'add_seller_info_tab' ) );
		add_filter( 'woocommerce_get_item_data', array( $this, 'add_product_seller_info' ), 10, 2 );
		add_filter( 'woocommerce_register_post_type_product', array( $this, 'add_edit_product_capability' ) );
		add_action( 'woocommerce_product_duplicate', array( $this, 'keep_old_seller' ), 35, 2 );
	}

	/**
	 * Remove default author metabox and add superstore sellers as author
	 */
	public function add_seller_meta_box() {
		remove_meta_box( 'authordiv', 'product', 'core' );
		add_meta_box( 'sellerdiv', __( 'Seller', 'superstore' ), array( self::class, 'box_content' ), 'product', 'normal', 'core' );
	}

	/**
	 * Display list of sellers.
	 *
	 * @param object $post Post.
	 */
	public static function box_content( $post ) {
		global $user_ID;

		$admin_user = get_user_by( 'id', $user_ID );
		$selected   = empty( $post->ID ) ? $user_ID : $post->post_author;
		$sellers    = superstore()->seller->get_sellers();
		?>
		<label class="screen-reader-text" for="superstore_product_author_override"><?php esc_html_e( 'Seller', 'superstore' ); ?></label>
		<select name="superstore_product_author_override" id="superstore_product_author_override" class="">
			<?php if ( empty( $sellers ) ) : ?>
				<option value="<?php echo esc_attr( $admin_user->ID ); ?>"><?php echo esc_html( $admin_user->display_name ); ?></option>
			<?php else : ?>
				<option value="<?php echo esc_attr( $user_ID ); ?>" <?php selected( $selected, $user_ID ); ?>><?php echo esc_html( $admin_user->display_name ); ?></option>
				<?php foreach ( $sellers as $key => $seller ) : ?>
					<option value="<?php echo esc_attr( $seller->get_id() ); ?>" <?php selected( $selected, $seller->get_id() ); ?>><?php echo ! empty( $seller->get_store_name() ) ? esc_html( $seller->get_store_name() ) : esc_html( $seller->get_user_login() ); ?></option>
				<?php endforeach ?>
			<?php endif ?>
		</select>
		<?php
	}

	/**
	 * Add value for quick edit data
	 *
	 * @param array   $column Column.
	 * @param integer $post_id POST ID.
	 */
	public function add_superstore_author_field_data( $column, $post_id ) {
		switch ( $column ) {
			case 'name':
				?>
				<div class="hidden superstore_seller_id_inline" id="superstore_seller_id_inline_<?php echo esc_attr( $post_id ); ?>">
					<div id="superstore_seller_id"><?php echo esc_html( get_post_field( 'post_author', $post_id ) ); ?></div>
				</div>
				<?php
				break;
			default:
				break;
		}
	}

	/**
	 * Author field for product quick edit
	 *
	 * @return void
	 */
	public function add_superstore_author_field() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}

		$admin_user = get_user_by( 'id', get_current_user_id() );
		$sellers    = superstore()->seller->get_sellers();
		?>
		<div class="superstore-product-author-field inline-edit-group">
			<label class="alignleft">
				<span class="title">
					<?php esc_html_e( 'Seller', 'superstore' ); ?>
				</span>
				<span class="input-text-wrap">
					<select name="superstore_product_author_override" id="superstore_product_author_override">
						<?php if ( empty( $sellers ) ) : ?>
							<option value="<?php echo esc_attr( $admin_user->ID ); ?>"><?php echo esc_html( $admin_user->display_name ); ?></option>
						<?php else : ?>
							<option value=""><?php esc_html_e( '— No change —', 'superstore' ); ?></option>
							<option value="<?php echo esc_attr( $admin_user->ID ); ?>"><?php echo esc_html( $admin_user->display_name ); ?></option>
							<?php foreach ( $sellers as $key => $seller ) : ?>
								<option value="<?php echo esc_attr( $seller->get_id() ); ?>"><?php echo ! empty( $seller->get_store_name() ) ? esc_html( $seller->get_store_name() ) : esc_html( $seller->get_user_login() ); ?></option>
							<?php endforeach ?>
						<?php endif ?>
					</select>
				</span>
			</label>
		</div>

		<script>
			;(function($){
				$('#the-list').on('click', '.editinline', function(){
					var post_id = $(this).closest('tr').attr('id');

					post_id = post_id.replace("post-", "");

					var $seller_id_inline_data = $('#superstore_seller_id_inline_' + post_id).find('#superstore_seller_id').text(),
						$wc_inline_data = $('#woocommerce_inline_' + post_id );

					$( 'select[name="superstore_product_author_override"] option', '.inline-edit-row' ).attr( 'selected', false ).change();
					$( 'select[name="superstore_product_author_override"] option[value="' + $seller_id_inline_data + '"]' ).attr( 'selected', 'selected' ).change();
				});
			})(jQuery);
		</script>
		<?php
	}

	/**
	 * Save quick edit data
	 *
	 * @param object $product Product.
	 */
	public function save_quick_edit_data( $product ) {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}

		if ( ! isset( $_REQUEST['woocommerce_quick_edit_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_REQUEST['woocommerce_quick_edit_nonce'] ), 'woocommerce_quick_edit_nonce' ) ) {
			return;
		}

		$posted_seller_id = ! empty( $_REQUEST['superstore_product_author_override'] ) ? (int) sanitize_text_field( wp_unslash( $_REQUEST['superstore_product_author_override'] ) ) : 0;

		if ( ! $posted_seller_id ) {
			return;
		}

		$seller = superstore_get_seller_by_product( $product );

		if ( ! $seller ) {
			return;
		}

		if ( $posted_seller_id === $seller->get_id() ) {
			return;
		}

		superstore_override_product_author( $product, $posted_seller_id );
	}

	/**
	 * Change product author ID from admin
	 *
	 * @param int|string $product_id Product ID.
	 * @param object     $post Post.
	 * @return void
	 */
	public function change_product_author( $product_id, $post ) {
		// phpcs:ignore Generic.Formatting.MultipleStatementAlignment.IncorrectWarning
		$product          = wc_get_product( $product_id );
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$posted_seller_id = ! empty( $_POST['superstore_product_author_override'] ) ? intval( $_POST['superstore_product_author_override'] ) : 0;

		if ( ! $posted_seller_id ) {
			return;
		}

		$seller = superstore_get_seller_by_product( $product );

		if ( ! $seller ) {
			return;
		}

		if ( $posted_seller_id === $seller->get_id() ) {
			return;
		}

		superstore_override_product_author( $product, $posted_seller_id );
	}

	/**
	 * Notify or send emails the seller if product status is changed from pending to publish
	 *
	 * @param WP_Post $post Product.
	 * @return void
	 */
	public function notify_seller_on_product_published( $post ) {
		if ( 'product' !== $post->post_type ) {
			return;
		}

		// Only send emails to marketplace sellers.
		if ( ! superstore_is_user_seller( $post->post_author ) ) {
			return;
		}

		do_action( 'superstore_product_published', $post );
	}

	/**
	 * Add product author|seller column on woocommerce product table
	 *
	 * @param array $columns Column.
	 * @return array
	 */
	public function add_product_author_columns( $columns ) {
		$columns['author'] = __( 'Seller', 'superstore' );

		return $columns;
	}

	/**
	 * Add seller info tab in product single page
	 *
	 * @param array $tabs Tabs.
	 * @return array
	 */
	public function add_seller_info_tab( $tabs ) {
		$tabs['seller'] = array(
			'title'    => __( 'Seller Info', 'superstore' ),
			'priority' => 90,
			'callback' => array( $this, 'seller_info_tab_callback' ),
		);

		return $tabs;
	}

	/**
	 * Seller info tab data in product single page
	 *
	 * @global WC_Product $product
	 * @param type $val Val.
	 */
	public function seller_info_tab_callback( $val ) {
		global $product;

		$author_id = get_post_field( 'post_author', $product->get_id() );
		$author    = get_user_by( 'id', $author_id );
		$seller    = superstore()->seller->crud_seller( $author->ID );

		if ( 'yes' === superstore_get_option( 'show_seller_info_in_single_product_page_tab', 'superstore_seller', 'yes' ) ) {
			superstore_get_template_part(
				'global/product-tab',
				'',
				array(
					'author' => $author,
					'seller' => $seller,
				)
			);
		}
	}

	/**
	 * Add product seller name in cart
	 *
	 * @param array $item_data Item data.
	 * @param array $cart_item Cart item.
	 * @return array
	 */
	public function add_product_seller_info( $item_data, $cart_item ) {
		$seller = superstore_get_seller_by_product( $cart_item['product_id'] );

		if ( ! $seller ) {
			return $item_data;
		}

		if ( 'yes' !== superstore_get_option( 'show_seller_name_in_cart', 'superstore_seller', 'yes' ) ) {
			return $item_data;
		}

		$url   = $seller->get_store_url();
		$name  = $seller->get_store_name();
		$value = '<a href=' . "$url" . '>' . "$name" . '</a>';

		$item_data[] = array(
			'name'  => __( 'Seller', 'superstore' ),
			'value' => $value,
		);

		return $item_data;
	}

	/**
	 * Add edit post capability to woocommerce proudct post type
	 *
	 * @param array $capability Capability.
	 *
	 * @return capability
	 */
	public function add_edit_product_capability( $capability ) {
		$capability['capabilities'] = array(
			'edit_post' => 'edit_product',
		);

		return $capability;
	}

	/**
	 * Keep old seller after duplicate products
	 *
	 * @param object $duplicate Duplicate.
	 * @param object $product Product.
	 * @return void
	 */
	public function keep_old_seller( $duplicate, $product ) {
		$old_author = get_post_field( 'post_author', $product->get_id() );
		$new_author = get_post_field( 'post_author', $duplicate->get_id() );

		if ( absint( $old_author ) === absint( $new_author ) ) {
			return;
		}

		superstore_override_product_author( $duplicate, absint( $old_author ) );
	}
}
