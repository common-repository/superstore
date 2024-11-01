<?php

namespace Binarithm\Superstore;

/**
 * Superstore admin menus manager
 */
class Menus {

	/**
	 * Superstore admin menus constructor
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_menus' ) );
		add_action( 'wp_before_admin_bar_render', array( $this, 'add_admin_toolbar_menus' ) );
		add_action( 'admin_bar_menu', array( $this, 'visit_seller_dashboard_menu' ), 35 );
	}

	/**
	 * Add Superstore menus
	 */
	public function add_menus() {
		global $submenu;

		// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
		$icon          = 'data:image/svg+xml;base64,' . base64_encode(
			'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24.58 26.63"><defs><style>.cls-1{fill:#333;}</style></defs><g id="Layer_2" data-name="Layer 2"><g id="Layer_1-2" data-name="Layer 1"><path class="cls-1" d="M1.46,8.19v3a2.68,2.68,0,0,0,1.17.27A2.64,2.64,0,0,0,5.27,8.78V8.19Zm5,0v.59a2.64,2.64,0,0,0,5.27,0V8.19Zm6.44,0v.59a2.63,2.63,0,0,0,5.26,0V8.19Zm6.43,0v.59A2.64,2.64,0,0,0,22,11.41a2.68,2.68,0,0,0,1.17-.27v-3ZM22,12.58a3.8,3.8,0,0,1-3.22-1.78,3.8,3.8,0,0,1-6.44,0,3.8,3.8,0,0,1-6.44,0,3.82,3.82,0,0,1-4.39,1.6v13a1.22,1.22,0,0,0,1.22,1.22H21.9a1.22,1.22,0,0,0,1.22-1.22v-13A3.77,3.77,0,0,1,22,12.58Zm-7,11.12H10.24v-.58h4.68Zm0-2.34H10.24v-.58h4.68Zm0-2.34H10.24v-.58h4.68Z"/><path class="cls-1" d="M6.19,0,5.34,7H.16L1.65.94A1.24,1.24,0,0,1,2.84,0Z"/><polygon class="cls-1" points="11.71 0 11.71 7.02 6.51 7.02 7.38 0 11.71 0"/><polygon class="cls-1" points="18.07 7.02 12.88 7.02 12.88 0 17.21 0 18.07 7.02"/><path class="cls-1" d="M24.42,7H19.25l-.86-7h3.35a1.23,1.23,0,0,1,1.19.94Z"/><path class="cls-1" d="M5.27,8.19v.59a2.64,2.64,0,0,1-2.64,2.63A2.62,2.62,0,0,1,0,8.78V8.19Z"/><path class="cls-1" d="M11.71,8.19v.59a2.64,2.64,0,0,1-5.27,0V8.19Z"/><path class="cls-1" d="M12.88,8.19h5.26v.59a2.63,2.63,0,0,1-5.26,0Z"/><path class="cls-1" d="M24.58,8.19v.59a2.64,2.64,0,0,1-5.27,0V8.19Z"/></g></g></svg>'
		);
		$capability    = apply_filters( 'superstore_admin_menu_capability', 'manage_woocommerce' );
		$slug          = 'superstore';
		$menu_position = apply_filters( 'superstore_admin_menu_position', 55 );
		$dashboard     = add_menu_page( __( 'Superstore', 'superstore' ), __( 'Superstore', 'superstore' ), $capability, $slug, array( $this, 'superstore_admin_dashboard' ), $icon, $menu_position );

		if ( current_user_can( $capability ) ) {
			// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
			$submenu[ $slug ][] = array( __( 'Dashboard', 'superstore' ), $capability, 'admin.php?page=' . $slug . '#/', 'mdi-view-dashboard', '/' );

			// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
			$submenu[ $slug ][] = array( __( 'Seller', 'superstore' ), $capability, 'admin.php?page=' . $slug . '#/seller', 'mdi-account-multiple-outline', '/seller' );

			// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
			$submenu[ $slug ][] = array( __( 'Payment', 'superstore' ), $capability, 'admin.php?page=' . $slug . '#/payment', 'mdi-credit-card-outline', '/payment' );
		}

		do_action( 'superstore_admin_menu', $capability, $menu_position, $slug );

		if ( current_user_can( $capability ) ) {
			// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
			$submenu[ $slug ][] = array( __( 'Settings', 'superstore' ), $capability, 'admin.php?page=' . $slug . '#/settings', 'mdi-cog', '/settings' );

			if ( ! superstore()->superstore_pro_exists() ) {
				// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
				$submenu[ $slug ][] = array( __( '<strong style="color:#ff388b"><span class="dashicons dashicons-star-filled" style="font-size:13px;line-height:1.5;"></span> Get PRO</strong>', 'superstore' ), $capability, 'admin.php?page=' . $slug . '#/superstore-pro', 'mdi-crown', '/superstore-pro' );
			}
		}
	}

	/**
	 * Superstore admin dashboard
	 *
	 * @since 1.0.0
	 */
	public function superstore_admin_dashboard() {
		echo '<div class="wrap"><div id="superstore-admin-dashboard"></div></div>';
	}

	/**
	 * Add superstore pro admin toolbar menus
	 */
	public function add_admin_toolbar_menus() {
		global $wp_admin_bar;

		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}

		$args = array(
			'id'    => 'superstore',
			'title' => __( 'Superstore', 'superstore' ),
			'href'  => admin_url( 'admin.php?page=superstore' ),
		);

		$wp_admin_bar->add_menu( $args );

		$wp_admin_bar->add_menu(
			array(
				'id'     => 'superstore-dashboard',
				'parent' => 'superstore',
				'title'  => __( 'Dashboard', 'superstore' ),
				'href'   => admin_url( 'admin.php?page=superstore' ),
			)
		);

		$wp_admin_bar->add_menu(
			array(
				'id'     => 'superstore-seller',
				'parent' => 'superstore',
				'title'  => __( 'Seller', 'superstore' ),
				'href'   => admin_url( 'admin.php?page=superstore#/seller' ),
			)
		);

		$wp_admin_bar->add_menu(
			array(
				'id'     => 'superstore-payment',
				'parent' => 'superstore',
				'title'  => __( 'Payment', 'superstore' ),
				'href'   => admin_url( 'admin.php?page=superstore#/payment' ),
			)
		);

		do_action( 'superstore_admin_toolbar_menu' );

		$wp_admin_bar->add_menu(
			array(
				'id'     => 'superstore-setting',
				'parent' => 'superstore',
				'title'  => __( 'Settings', 'superstore' ),
				'href'   => admin_url( 'admin.php?page=superstore#/settings' ),
			)
		);
	}

	/**
	 * Add visit seller dashboard menu in admin bar
	 *
	 * @param WP_Admin_Bar $wp_admin_bar Admin bar.
	 * @return void
	 */
	public function visit_seller_dashboard_menu( $wp_admin_bar ) {
		if ( ! is_admin() || ! is_admin_bar_showing() ) {
			return;
		}

		// Show only if member or super admin.
		if ( ! is_user_member_of_blog() && ! is_super_admin() ) {
			return;
		}

		$wp_admin_bar->add_node(
			array(
				'parent' => 'site-name',
				'id'     => 'view-seller-dashboard',
				'title'  => __( 'Visit Seller Dashboard', 'superstore' ),
				'href'   => home_url( 'seller-account' ),
			)
		);
	}
}
