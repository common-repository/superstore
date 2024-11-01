<?php

namespace Binarithm\Superstore\Localize\SellerDashboard;

use Binarithm\Superstore\Traits\Container;

/**
 * Superstore seller dashboard localize data conroller class
 */
class Controller {

	use Container;

	/**
	 * Class constructor
	 */
	public function __construct() {
		add_filter( 'superstore_frontend_localize_global_data', array( $this, 'add_global_data' ) );

		$this->container['setup_wizard'] = new SetupWizard();
		$this->container['home']         = new Home();
		$this->container['media']        = new Media();
		$this->container['product']      = new Product();
		$this->container['order']        = new Order();
		$this->container['payment']      = new Payment();
		$this->container['settings']     = new Settings();
	}

	/**
	 * Global data for seller dashboard.
	 *
	 * @param Aarray $data Data.
	 * @return array
	 */
	public function add_global_data( $data ) {
		$data['menus']                   = array(
			'sidebar'        => $this->get_sidebar_menus(),
			'navbar'         => $this->get_navbar_menus(),
			'navbar_account' => $this->get_navbar_account_menus(),
		);
		$data['table']                   = $this->get_table_data();
		$data['notify']                  = $this->get_notification_data();
		$data['dashboard_primary_color'] = superstore_get_option( 'seller_dashboard_primary_color', 'superstore_appearance', '#6f6af8' );
		$data['google_map_api_key']      = superstore_get_option( 'google_map_api_key', 'superstore_general', '' );
		$data['overlay_loading_text']    = __( 'Please wait...', 'superstore' );
		$data['images']                  = array(
			'wc_product_image_placeholder' => '',
			'default_banner'               => SUPERSTORE_ASSETS_DIR . '/images/default-store-banner.png',
		);
		$data['form']                    = array(
			'submit_button_title'     => __( 'Submit', 'superstore' ),
			'submit_button_hint'      => __( 'To enable submit button fill all input fields that are valid and required', 'superstore' ),
			'valid_required_text'     => __( 'Required', 'superstore' ),
			'valid_email_text'        => __( 'Email is not valid', 'superstore' ),
			'text_editor_placeholder' => __( 'Type here...', 'superstore' ),
		);
		$data['logout_text']             = __( 'Log out', 'superstore' );
		$data['copy_text']               = __( 'Copy URL to clipboard', 'superstore' );
		$data['media_uploader']          = array(
			'add_new_media_uploaded_files_text' => __( 'Uploaded files', 'superstore' ),
			'add_new_media_choose_files_text'   => __( 'Click to choose files', 'superstore' ),
			'upload_text'                       => __( 'Upload', 'superstore' ),
			'edit_text'                         => __( 'Change', 'superstore' ),
			'delete_text'                       => __( 'Remove', 'superstore' ),
			'drop_text'                         => __( 'Drop files or click the box to upload', 'superstore' ),
		);

		return apply_filters( 'superstore_seller_dashboard_localize_global_data', $data );
	}

	/**
	 * Seller dashboard sidebar menus.
	 *
	 * @return array
	 */
	public function get_sidebar_menus() {

		$menus = array(
			array(
				'title' => __( 'Dashboard', 'superstore' ),
				'to'    => '/',
				'icon'  => 'mdi-view-dashboard',
			),
			array(
				'title' => __( 'Media', 'superstore' ),
				'to'    => '/media',
				'icon'  => 'mdi-folder-multiple-image',
			),
			array(
				'title' => __( 'Product', 'superstore' ),
				'to'    => '/product',
				'icon'  => 'mdi-basket',
			),
			array(
				'title' => __( 'Order', 'superstore' ),
				'to'    => '/order',
				'icon'  => 'mdi-basket-plus',
			),
			array(
				'title' => __( 'Payment', 'superstore' ),
				'to'    => '/payment',
				'icon'  => 'mdi-credit-card',
			),
			array(
				'title' => __( 'Settings', 'superstore' ),
				'to'    => '/settings',
				'icon'  => 'mdi-cog',
			),
		);

		return apply_filters( 'superstore_seller_dashboard_localize_sidebar_menus', $menus );
	}

	/**
	 * Navbar menus in seller dashboard.
	 *
	 * @return array
	 */
	public function get_navbar_menus() {
		$menus = array(
			array(
				'title' => __( 'Home', 'superstore' ),
				'to'    => '/',
			),
			array(
				'title' => __( 'Orders', 'superstore' ),
				'to'    => '/order',
			),
			array(
				'title' => __( 'Add New Product', 'superstore' ),
				'to'    => '/product/add-new-product',
			),
			array(
				'title' => __( 'Settings', 'superstore' ),
				'to'    => '/settings',
			),
		);

		return apply_filters( 'superstore_seller_dashboard_localize_navbar_menus', $menus );
	}

	/**
	 * Navbar menus in seller dashboard.
	 *
	 * @return array
	 */
	public function get_navbar_account_menus() {
		$store_url = superstore_get_store_url( get_current_user_id() );
		$menus     = array(
			array(
				'title' => __( 'Edit account', 'superstore' ),
				'to'    => '/settings',
				'icon'  => 'mdi-account',
			),
			array(
				'title' => __( 'Visit store', 'superstore' ),
				'name'  => 'store_url',
				'to'    => $store_url,
				'icon'  => 'mdi-storefront',
			),
		);

		return apply_filters( 'superstore_seller_dashboard_localize_navbar_account_menus', $menus );
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

		return apply_filters( 'superstore_seller_dashboard_localize_table_global_data', $data );
	}

	/**
	 * Notifications data
	 *
	 * @return array
	 */
	public function get_notification_data() {
		$account_enabled     = superstore()->seller->crud_seller( get_current_user_id() )->get_enabled();
		$disabled_seller_can = superstore_get_option(
			'disabled_seller_can',
			'superstore_seller',
			array(
				'add_product'      => 'no',
				'delete_product'   => 'no',
				'withdraw_payment' => 'no',
				'manage_order'     => 'no',
				'add_media'        => 'no',
				'delete_media'     => 'no',
			)
		);

		$add_product      = 'no' === $disabled_seller_can['add_product'] ? __( '"Add product"', 'superstore' ) : null;
		$delete_product   = 'no' === $disabled_seller_can['delete_product'] ? __( '"Delete product"', 'superstore' ) : null;
		$withdraw_payment = 'no' === $disabled_seller_can['withdraw_payment'] ? __( '"Withdraw payment"', 'superstore' ) : null;
		$manage_order     = 'no' === $disabled_seller_can['manage_order'] ? __( '"Manage order"', 'superstore' ) : null;
		$add_media        = 'no' === $disabled_seller_can['add_media'] ? __( '"Add media"', 'superstore' ) : null;
		$delete_media     = 'no' === $disabled_seller_can['delete_media'] ? __( '"Delete media"', 'superstore' ) : null;

		/* translators: 1: Add product 2: Delete product 3: Withdraw payment 4: Manage order 5: Add media 6: Delete media */
		$message = 'no' === $account_enabled ? sprintf( __( 'Your account is not enabled. You can not %1$s %2$s %3$s %4$s %5$s %6$s.', 'superstore' ), $add_product, $delete_product, $withdraw_payment, $manage_order, $add_media, $delete_media ) : null;

		$data = array(
			'confirm_delete' => __( 'Deleting permanently. Are you sure?', 'superstore' ),
			'success_delete' => __( 'Successfully deleted', 'superstore' ),
			'success_saved'  => __( 'Successfully saved', 'superstore' ),
			'account'        => array(
				'enabled'                => ! current_user_can( 'manage_woocommerce' ) ? $account_enabled : '',
				'message'                => $message,
				'show_ac_disabled_alert' => superstore_get_option( 'show_account_disabled_alert_in_seller_dashboard', 'superstore_general', 'yes' ),
			),
			'text_copied'    => __( 'Copied', 'superstore' ),
		);

		return apply_filters( 'superstore_seller_dashboard_localize_notification_global_data', $data );
	}
}
