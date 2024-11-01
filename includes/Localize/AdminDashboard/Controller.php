<?php

namespace Binarithm\Superstore\Localize\AdminDashboard;

use Binarithm\Superstore\Traits\Container;

/**
 * Superstore admin dashboard localize data controller class
 */
class Controller {

	use Container;

	/**
	 * Class constructor
	 */
	public function __construct() {
		add_filter( 'superstore_admin_localize_global_data', array( $this, 'add_global_data' ) );

		$this->container['setup_wizard'] = new SetupWizard();
		$this->container['home']         = new Home();
		$this->container['seller']       = new Seller();
		$this->container['payment']      = new Payment();
		$this->container['settings']     = new Settings();
		$this->container['get_pro']      = new GetPro();
	}

	/**
	 * Global data for admin dashboard.
	 *
	 * @param Aarray $data Data.
	 * @return array
	 */
	public function add_global_data( $data ) {
		// phpcs:ignore WordPress.WP.I18n.MissingTranslatorsComment
		$footer_rate_text = sprintf( __( 'Please rate<span class="px-1">Superstore</span><a href="%1$s" target="_blank" rel="noopener noreferrer"> ★★★★★ </a> on <a href="%1$s" class="font-weight-bold px-1">WordPress.org</a>', 'superstore' ), esc_url( 'https://wordpress.org/support/plugin/superstore/reviews/?filter=5#new-post' ) );

		$data['menus']                   = array(
			'sidebar'          => $this->get_sidebar_menus(),
			'navbar'           => $this->get_navbar_menus(),
			'quick'            => $this->get_quick_menus(),
			'quick_link_title' => __( 'See Quick Links', 'superstore' ),
			'footer_rate_text' => $footer_rate_text,
			'footer'           => $this->get_footer_menus(),
		);
		$data['table']                   = $this->get_table_data();
		$data['notify']                  = $this->get_notification_data();
		$data['dashboard_primary_color'] = superstore_get_option( 'admin_dashboard_primary_color', 'superstore_appearance', '#6f6af8' );
		$data['google_map_api_key']      = superstore_get_option( 'google_map_api_key', 'superstore_general', '' );
		$data['overlay_loading_text']    = __( 'Please wait...', 'superstore' );
		$data['images']                  = array(
			'default_banner'       => SUPERSTORE_ASSETS_DIR . '/images/default-store-banner.png',
			'superstore_logo'      => SUPERSTORE_ASSETS_DIR . '/images/superstore-logo-black.png',
			'superstore_logo_dark' => SUPERSTORE_ASSETS_DIR . '/images/superstore-logo-white.png',
		);
		$data['no_name']                 = __( '(no name)', 'superstore' );
		$data['form']                    = array(
			'submit_button_title'     => __( 'Submit', 'superstore' ),
			'submit_button_hint'      => __( 'To enable submit button fill all input fields that are valid and required', 'superstore' ),
			'valid_required_text'     => __( 'Required', 'superstore' ),
			'valid_email_text'        => __( 'Email is not valid', 'superstore' ),
			'text_editor_placeholder' => __( 'Type here...', 'superstore' ),
		);
		$data['media_uploader']          = array(
			'upload_text' => __( 'Upload', 'superstore' ),
			'edit_text'   => __( 'Change', 'superstore' ),
			'delete_text' => __( 'Remove', 'superstore' ),
		);

		return apply_filters( 'superstore_admin_dashboard_localize_global_data', $data );
	}

	/**
	 * Secondary sidebar menus inside admin dashboard.
	 *
	 * @return array
	 */
	public function get_sidebar_menus() {
		global $submenu;

		$menus = array_key_exists( 'superstore', $submenu ) ? $submenu['superstore'] : false;
		if ( ! $menus ) {
			return;
		}
		$data = array();

		foreach ( $menus as $key => $menu ) {
			$data[ $key ]['title']      = $menus[ $key ][0];
			$data[ $key ]['capability'] = $menus[ $key ][1];
			$data[ $key ]['to_admin']   = $menus[ $key ][2];
			$data[ $key ]['icon']       = $menus[ $key ][3];
			$data[ $key ]['to']         = $menus[ $key ][4];

			if ( '/superstore-pro' === $menus[ $key ][4] ) {
				$data[ $key ]['title'] = __( 'Get PRO', 'superstore' );
			}
		}

		return apply_filters( 'superstore_admin_dashboard_localize_sidebar_menus', $data );
	}

	/**
	 * Navbar menus in admin dashboard.
	 *
	 * @return array
	 */
	public function get_navbar_menus() {
		$enabled = superstore_get_option(
			'admin_dashboard_navbar_menus',
			'superstore_general',
			array(
				'home'           => 'yes',
				'sellers'        => 'yes',
				'add_new_seller' => 'yes',
				'payments'       => 'yes',
				'settings'       => 'yes',
			)
		);

		$menus = array(
			array(
				'title'   => __( 'Home', 'superstore' ),
				'to'      => '/',
				'enabled' => $enabled['home'],
			),
			array(
				'title'   => __( 'Sellers', 'superstore' ),
				'to'      => '/seller',
				'enabled' => $enabled['sellers'],
			),
			array(
				'title'   => __( 'Add new seller', 'superstore' ),
				'to'      => '/seller/add-new-seller',
				'enabled' => $enabled['add_new_seller'],
			),
			array(
				'title'   => __( 'Payments', 'superstore' ),
				'to'      => '/payment',
				'enabled' => $enabled['payments'],
			),
			array(
				'title'   => __( 'Settings', 'superstore' ),
				'to'      => '/settings',
				'enabled' => $enabled['settings'],
			),
		);

		$enabled_menus = array();

		foreach ( $menus as $menu ) {
			if ( 'yes' !== $menu['enabled'] ) {
				continue;
			}

			$enabled_menus[] = $menu;
		}

		return apply_filters( 'superstore_admin_dashboard_localize_navbar_menus', $enabled_menus );
	}

	/**
	 * Quick link menus in admin dashboard.
	 *
	 * @return array
	 */
	public function get_quick_menus() {
		$data = array(
			array(
				'title' => __( 'Upgrade to Superstore PRO', 'superstore' ),
				'to'    => 'https://binarithm.com/superstore-pricing',
				'icon'  => 'mdi-star',
			),
			array(
				'title' => __( 'Support & Docs', 'superstore' ),
				'to'    => 'https://binarithm.com/superstore-docs',
				'icon'  => 'mdi-help-circle',
			),
			array(
				'title' => __( 'Join Us on Facebook', 'superstore' ),
				'to'    => 'https://www.facebook.com/binarithmofficial',
				'icon'  => 'mdi-facebook',
			),
			array(
				'title' => __( 'Contact with Team', 'superstore' ),
				'to'    => 'https://binarithm.com/contact',
				'icon'  => 'mdi-face-agent',
			),
		);

		return apply_filters( 'superstore_admin_dashboard_localize_quick_menus', $data );
	}

	/**
	 * Footer menus in admin dashboard.
	 *
	 * @return array
	 */
	public function get_footer_menus() {

		$data = array(
			array(
				'title' => __( 'Superstore', 'superstore' ),
				'href'  => esc_url( 'https://www.binarithm.com/superstore' ),
			),
		);

		return apply_filters( 'superstore_admin_dashboard_localize_footer_menus', $data );
	}

	/**
	 * Table data
	 *
	 * @return array
	 */
	public function get_table_data() {
		$data = array(
			'no_item_selected_alert'    => __( 'No items are selected. Please select any item first.', 'superstore' ),
			'bulk_action_title'         => __( 'Bulk actions', 'superstore' ),
			'search_label'              => __( 'Type to search...', 'superstore' ),
			'loading_text'              => __( 'Please wait...', 'superstore' ),
			'no_item_found_text'        => __( 'No items found', 'superstore' ),
			'no_search_item_found_text' => __( 'No results found', 'superstore' ),
			'items_per_page_text'       => __( 'Rows per page', 'superstore' ),
			'items_per_page_all_text'   => __( 'All', 'superstore' ),
		);

		return apply_filters( 'superstore_admin_dashboard_localize_table_global_data', $data );
	}

	/**
	 * Notifications data
	 *
	 * @return array
	 */
	public function get_notification_data() {
		$data = array(
			'confirm_delete' => __( 'Deleting permanently. Are you sure?', 'superstore' ),
			'success_delete' => __( 'Successfully deleted', 'superstore' ),
			'success_saved'  => __( 'Successfully saved', 'superstore' ),
		);

		return apply_filters( 'superstore_admin_dashboard_localize_notification_global_data', $data );
	}
}
