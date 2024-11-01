<?php

namespace Binarithm\Superstore\Hooks;

/**
 * Superstore core hooks
 */
class Core {

	/**
	 * Superstore core hooks contructor
	 */
	public function __construct() {
		add_action( 'admin_init', array( $this, 'admin_access_permission' ) );
		add_filter( 'show_admin_bar', array( $this, 'show_admin_bar' ) );
		add_filter( 'posts_where', array( $this, 'show_only_own_uploads' ) );
		add_filter( 'ajax_query_attachments_args', array( $this, 'show_only_self_uploads' ) );
	}

	/**
	 * Allow/disallow user to access admin area.
	 */
	public function admin_access_permission() {
		global $pagenow, $current_user;

		// bail out if we are from WP Cli.
		if ( defined( 'WP_CLI' ) ) {
			return;
		}

		$access      = superstore_get_option( 'seller_can_access_admin_area', 'superstore_seller', 'no' );
		$valid_pages = array( 'admin-ajax.php', 'admin-post.php', 'async-upload.php', 'media-upload.php' );
		$user_role   = reset( $current_user->roles );

		// phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
		if ( ( 'no' === $access ) && ( ! in_array( $pagenow, $valid_pages ) ) && in_array( $user_role, array( 'superstore_seller', 'customer', 'superstore_seller_staff' ) ) ) {
			wp_safe_redirect( home_url() );
			exit;
		}
	}

	/**
	 * Prevent sellers,staffs and customers from seeing the admin bar
	 *
	 * @param bool $show Show or hide admin bar.
	 * @return bool
	 */
	public function show_admin_bar( $show ) {
		global $current_user;
		$access = superstore_get_option( 'seller_can_access_admin_area', 'superstore_seller', 'no' );

		if ( 0 !== $current_user->ID ) {
			$role = reset( $current_user->roles );

			if ( 'no' === $access ) {
				// phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
				if ( in_array( $role, array( 'superstore_seller', 'customer', 'superstore_seller_staff' ) ) ) {
					return false;
				}
			}
		}

		return $show;
	}

	/**
	 * Show only own uploads. Hide media uploads in page "upload.php" and "media-upload.php" for
	 * sellers.
	 *
	 * FIXME: fix the upload counts
	 *
	 * @global string $pagenow
	 * @global object $wpdb
	 * @param string $where Where.
	 * @return string
	 */
	public function show_only_own_uploads( $where ) {
		global $pagenow, $wpdb;

		if ( current_user_can( 'manage_woocommerce' ) ) {
			return $where;
		}

		if ( ( 'upload.php' === $pagenow || 'media-upload.php' === $pagenow ) && current_user_can( 'manage_superstore' ) ) {
			$user_id = get_current_user_id();

			$where .= " AND $wpdb->posts.post_author = $user_id";
		}

		return $where;
	}

	/**
	 * Show only self uploaded files to seller
	 *
	 * @param array $args Args.
	 * @return array
	 */
	public function show_only_self_uploads( $args ) {
		if ( current_user_can( 'manage_woocommerce' ) ) {
			return $args;
		}

		if ( current_user_can( 'manage_superstore' ) ) {
			$args['author'] = get_current_user_id();

			return $args;
		}

		return $args;
	}

	/**
	 * Add superstore seller/user fields to admin user profile
	 *
	 * @param WP_User $user User.
	 * @return void|false
	 */
	public function add_profile_fields( $user ) {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}

		$seller = superstore()->seller->crud_seller( $user->ID );

		?>
		<table class="form-table">
			<h2><?php esc_html_e( 'Superstore options', 'superstore' ); ?></h2>
			<tbody>
				<tr>
					<th><?php esc_html_e( 'Store name', 'superstore' ); ?></th>
					<td>
						<input type="text" name="superstore_store_name" class="regular-text" value="<?php echo esc_attr( $seller->get_store_name() ); ?>">
					</td>
				</tr>
				<tr>
					<th><?php esc_html_e( 'Store url', 'superstore' ); ?></th>
					<td>
						<input type="text" name="superstore_store_url_nicename" class="regular-text" value="<?php echo esc_attr( $seller->get_store_url() ); ?>">
					</td>
				</tr>
				<tr>
					<th><?php esc_html_e( 'Phone', 'superstore' ); ?></th>
					<td>
						<input type="text" name="superstore_phone" class="regular-text" value="<?php echo esc_attr( $seller->get_phone() ); ?>">
					</td>
				</tr>
				<?php wp_nonce_field( 'superstore_update_user_profile', 'superstore_update_user_profile_nonce' ); ?>
			</tbody>
		</table>
		<?php
	}

	/**
	 * Save superstore seller/user fields to admin user profile
	 *
	 * @param int $user_id User ID.
	 * @return void
	 */
	public function save_profile_fields( $user_id ) {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}

		if ( ! isset( $_POST['superstore_update_user_profile_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['superstore_update_user_profile_nonce'] ), 'superstore_update_user_profile' ) ) {
			return;
		}

		$store_name   = isset( $_POST['superstore_store_name'] ) ? sanitize_text_field( wp_unslash( $_POST['superstore_store_name'] ) ) : '';
		$url_nicename = isset( $_POST['superstore_store_url_nicename'] ) ? sanitize_text_field( wp_unslash( $_POST['superstore_store_url_nicename'] ) ) : '';
		$phone        = isset( $_POST['superstore_phone'] ) ? sanitize_text_field( wp_unslash( $_POST['superstore_phone'] ) ) : '';

		$seller = superstore()->seller->crud_seller( $user_id );
		$seller->set_store_name( $store_name );
		$seller->set_store_url_nicename( $url_nicename );
		$seller->set_phone( $phone );
		$seller->save();
	}
}
