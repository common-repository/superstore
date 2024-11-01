<?php

namespace Binarithm\Superstore\Localize\AdminDashboard;

/**
 * Superstore admin dashboard localize settings class
 */
class Settings {

	/**
	 * Class constructor
	 */
	public function __construct() {
		add_filter( 'superstore_admin_localize_data', array( $this, 'add_data' ) );
	}

	/**
	 * Add data
	 *
	 * @param array $data Data.
	 * @return array
	 */
	public function add_data( $data ) {
		$data['settings'] = array(
			'tab' => array(
				'active' => '/settings',
				'tabs'   => $this->tabs(),
				'body'   => $this->tabs_body(),
			),
		);

		return apply_filters( 'superstore_admin_dashboard_localize_settings_data', $data );
	}

	/**
	 * Add tabs
	 *
	 * @return array
	 */
	public function tabs() {
		$tabs = array(
			'general'    => array(
				'title' => __( 'General', 'superstore' ),
				'route' => '/settings',
			),
			'seller'     => array(
				'title' => __( 'Seller', 'superstore' ),
				'route' => '/settings/seller',
			),
			'payment'    => array(
				'title' => __( 'Payment', 'superstore' ),
				'route' => '/settings/payment',
			),
			'appearance' => array(
				'title' => __( 'Appearance', 'superstore' ),
				'route' => '/settings/appearance',
			),
		);

		return apply_filters( 'superstore_admin_dashboard_localize_settings_tabs', $tabs );
	}

	/**
	 * Tabs body data
	 *
	 * @return array
	 */
	public function tabs_body() {
		$data = array(
			'general'    => array(
				'form' => array(
					'fields'          => superstore_get_form_field_values_from_sections( $this->get_general_settings_form_sections(), 'superstore_general' ),
					'sections'        => $this->get_general_settings_form_sections(),
					'submitEvent'     => 'settings/edit',
					'submitExtraData' => 'superstore_general',
				),
			),
			'seller'     => array(
				'form' => array(
					'fields'          => superstore_get_form_field_values_from_sections( $this->get_seller_settings_form_sections(), 'superstore_seller' ),
					'sections'        => $this->get_seller_settings_form_sections(),
					'submitEvent'     => 'settings/edit',
					'submitExtraData' => 'superstore_seller',
				),
			),
			'payment'    => array(
				'form' => array(
					'fields'          => superstore_get_form_field_values_from_sections( $this->get_payment_settings_form_sections(), 'superstore_payment' ),
					'sections'        => $this->get_payment_settings_form_sections(),
					'submitEvent'     => 'settings/edit',
					'submitExtraData' => 'superstore_payment',
				),
			),
			'appearance' => array(
				'form' => array(
					'fields'          => superstore_get_form_field_values_from_sections( $this->get_appearance_settings_form_sections(), 'superstore_appearance' ),
					'sections'        => $this->get_appearance_settings_form_sections(),
					'submitEvent'     => 'settings/edit',
					'submitExtraData' => 'superstore_appearance',
				),
			),
		);

		return apply_filters( 'superstore_admin_dashboard_localize_settings_tabs_body', $data );
	}

	/**
	 * General settings form sections
	 *
	 * @return array
	 */
	public function get_general_settings_form_sections() {
		$sections = array(
			array(
				'title'  => __( 'Account', 'superstore' ),
				'fields' => array(
					'enable_registration'               => array(
						'name'    => 'enable_registration',
						'title'   => __( 'Enable registration', 'superstore' ),
						'type'    => 'checkbox',
						'default' => 'yes',
					),
					'registration_generate_username'    => array(
						'name'    => 'registration_generate_username',
						'title'   => __( 'Generate username', 'superstore' ),
						'type'    => 'checkbox',
						'default' => 'yes',
					),
					'registration_generate_password'    => array(
						'name'    => 'registration_generate_password',
						'title'   => __( 'Generate password', 'superstore' ),
						'type'    => 'checkbox',
						'default' => 'no',
					),
					'new_seller_auto_enable'            => array(
						'name'    => 'new_seller_auto_enable',
						'title'   => __( 'Auto enable newly registered seller', 'superstore' ),
						'hint'    => __( 'Newly registered seller will be enabled automatically. If auto enable is disabled, you can enable/disable by each seller manually', 'superstore' ),
						'type'    => 'checkbox',
						'default' => 'no',
					),
					'new_seller_auto_requires_product_publishing_review' => array(
						'name'    => 'new_seller_auto_requires_product_publishing_review',
						'hint'    => 'Newly added product will be published directly(Without admin review). If by default new sellers auto require admin review is disabled, you can enable/disable by each seller manually',
						'title'   => __( 'Auto required product publishing review.', 'superstore' ),
						'type'    => 'checkbox',
						'default' => 'yes',
					),
					'seller_register_url_text'          => array(
						'name'    => 'seller_register_url_text',
						'title'   => __( 'Register url text', 'superstore' ),
						'type'    => 'text',
						'default' => __( 'Become a seller', 'superstore' ),
					),
					'hide_seller_register_url_from_wc_register_form' => array(
						'name'    => 'hide_seller_register_url_from_wc_register_form',
						'title'   => __( 'Hide seller register url from woocommerce register/my-account form', 'superstore' ),
						'type'    => 'checkbox',
						'default' => 'no',
					),
					'set_logged_in_after_registration'  => array(
						'name'    => 'set_logged_in_after_registration',
						'title'   => __( 'Keep logged in after registration', 'superstore' ),
						'hint'    => __( 'Seller does not requires login again after registration', 'superstore' ),
						'type'    => 'checkbox',
						'default' => 'yes',
					),
					'show_goto_seller_dashboard_button_on_wc_my_account' => array(
						'name'    => 'show_goto_seller_dashboard_button_on_wc_my_account',
						'title'   => __( 'Show goto seller dashboard button on woocommerce my account', 'superstore' ),
						'type'    => 'checkbox',
						'default' => 'yes',
					),
					'goto_seller_dashboard_button_text' => array(
						'name'    => 'goto_seller_dashboard_button_text',
						'title'   => __( 'Woocommerce goto seller dashboard button text', 'superstore' ),
						'type'    => 'text',
						'default' => 'Go to seller dashboard',
					),
				),
			),
			array(
				'title'  => __( 'Commission', 'superstore' ),
				'fields' => $this->get_admin_commission_fields(),
			),
			array(
				'title'  => __( 'Others', 'superstore' ),
				'fields' => array(
					'shipping_fee_recipient'       => array(
						'name'    => 'shipping_fee_recipient',
						'title'   => __( 'Shipping fee recipient', 'superstore' ),
						'type'    => 'radio',
						'default' => 'seller',
						'groups'  => array(
							array(
								'label' => __( 'Seller', 'superstore' ),
								'value' => 'seller',
							),
							array(
								'label' => __( 'Admin', 'superstore' ),
								'value' => 'admin',
							),
						),
					),
					'tax_fee_recipient'            => array(
						'name'    => 'tax_fee_recipient',
						'title'   => __( 'Tax fee recipient', 'superstore' ),
						'type'    => 'radio',
						'default' => 'seller',
						'groups'  => array(
							array(
								'label' => __( 'Seller', 'superstore' ),
								'value' => 'seller',
							),
							array(
								'label' => __( 'Admin', 'superstore' ),
								'value' => 'admin',
							),
						),
					),
					'show_suborders_on_customer_order_details' => array(
						'name'    => 'show_suborders_on_customer_order_details',
						'title'   => __( 'Show suborders on customer order details', 'superstore' ),
						'type'    => 'checkbox',
						'default' => 'yes',
					),
					'admin_dashboard_navbar_menus' => array(
						'name'       => 'admin_dashboard_navbar_menus',
						'title'      => __( 'Admin dashboard navbar menus', 'superstore' ),
						'type'       => 'multiple',
						'value_type' => 'object',
						'items'      => array(
							'home'           => array(
								'child_name' => 'home',
								'label'      => __( 'Home', 'superstore' ),
								'type'       => 'checkbox',
								'default'    => 'yes',
							),
							'sellers'        => array(
								'child_name' => 'sellers',
								'label'      => __( 'Sellers', 'superstore' ),
								'type'       => 'checkbox',
								'default'    => 'yes',
							),
							'add_new_seller' => array(
								'child_name' => 'add_new_seller',
								'label'      => __( 'Add new seller', 'superstore' ),
								'type'       => 'checkbox',
								'default'    => 'yes',
							),
							'payments'       => array(
								'child_name' => 'payments',
								'label'      => __( 'Payments', 'superstore' ),
								'type'       => 'checkbox',
								'default'    => 'yes',
							),
							'settings'       => array(
								'child_name' => 'settings',
								'label'      => __( 'Settings', 'superstore' ),
								'type'       => 'checkbox',
								'default'    => 'yes',
							),
						),
					),
					'show_account_disabled_alert_in_seller_dashboard' => array(
						'name'    => 'show_account_disabled_alert_in_seller_dashboard',
						'title'   => __( 'Show account disabled alert in seller dashboard', 'superstore' ),
						'type'    => 'checkbox',
						'default' => 'yes',
					),
					'google_map_api_key'           => array(
						'name'  => 'google_map_api_key',
						'title' => __( 'Google map API key', 'superstore' ),
						'hint'  => __( 'To get API key visit https://developers.google.com/maps', 'superstore' ),
						'type'  => 'text',
					),
				),
			),
		);

		return apply_filters( 'superstore_admin_dashboard_localize_general_settings_sections', $sections );
	}

	/**
	 * Get admin commission settings fields
	 *
	 * @return array
	 */
	public function get_admin_commission_fields() {
		$fields = array(
			'commission_by_priority'  => array(
				'name'          => 'commission_by_priority',
				'title'         => __( 'Commission by priority', 'superstore' ),
				'hint'          => __( 'You can set commissions in multiple places(per seller, global etc), but only the first priority will be applied if value is not null. To change priority click uncheck and then check items in ascending order. The left colored box number indicates the priority. To set per seller commission go to seller profile.', 'superstore)' ),
				'type'          => 'autocomplete',
				'multiple'      => 'yes',
				'show_priority' => 'yes',
				'items'         => apply_filters(
					'superstore_admin_dashboard_commission_priorities',
					array(
						array(
							'label' => __( 'Per Seller', 'superstore' ),
							'value' => 'seller',
						),
						array(
							'label' => __( 'Global', 'superstore' ),
							'value' => 'global',
						),
					),
				),
				'default'       => array( 'global', 'seller' ),
			),
			'admin_commission_global' => array(
				'name'       => 'admin_commission_global',
				'title'      => __( 'Global admin commission', 'superstore' ),
				'type'       => 'multiple',
				'value_type' => 'object',
				'items'      => array(
					'rate' => array(
						'child_name' => 'rate',
						'label'      => __( 'Rate', 'superstore' ),
						'hint'       => __( 'If value is totally empty, the next priority will be applied automatically for commission. 0 will count as an empty value.', 'superstore' ),
						'type'       => 'number',
					),
					'type' => array(
						'child_name' => 'type',
						'label'      => __( 'Type', 'superstore' ),
						'type'       => 'select',
						'items'      => array( 'percentage', 'flat' ),
						'default'    => 'percentage',
					),
				),
			),
		);

		return apply_filters( 'superstore_admin_dashboard_localize_commission_settings_fields', $fields );
	}

	/**
	 * Seller settings form sections
	 *
	 * @return array
	 */
	public function get_seller_settings_form_sections() {
		$sections = array(
			array(
				'title'  => __( 'Capabilities', 'superstore' ),
				'fields' => array(
					'disabled_seller_can'                 => array(
						'name'       => 'disabled_seller_can',
						'title'      => __( 'Disabled seller can', 'superstore' ),
						'type'       => 'multiple',
						'value_type' => 'object',
						'items'      => array(
							'add_product'      => array(
								'child_name' => 'add_product',
								'label'      => __( 'Add product', 'superstore' ),
								'type'       => 'checkbox',
								'default'    => 'no',
							),
							'delete_product'   => array(
								'child_name' => 'delete_product',
								'label'      => __( 'Delete product', 'superstore' ),
								'type'       => 'checkbox',
								'default'    => 'no',
							),
							'withdraw_payment' => array(
								'child_name' => 'withdraw_payment',
								'label'      => __( 'Withdraw payment', 'superstore' ),
								'type'       => 'checkbox',
								'default'    => 'no',
							),
							'manage_order'     => array(
								'child_name' => 'manage_order',
								'label'      => __( 'Manage order', 'superstore' ),
								'type'       => 'checkbox',
								'default'    => 'no',
							),
							'add_media'        => array(
								'child_name' => 'add_media',
								'label'      => __( 'Add media', 'superstore' ),
								'type'       => 'checkbox',
								'default'    => 'no',
							),
							'delete_media'     => array(
								'child_name' => 'delete_media',
								'label'      => __( 'Delete media', 'superstore' ),
								'type'       => 'checkbox',
								'default'    => 'no',
							),
						),
					),
					'seller_new_product_status'           => array(
						'name'    => 'seller_new_product_status',
						'title'   => __( 'New product status', 'superstore' ),
						'hint'    => __( 'Woocommerce product status for seller new product adding request', 'superstore' ),
						'type'    => 'select',
						'default' => 'pending',
						'items'   => array( 'pending', 'publish' ),
					),
					'seller_can_edit_password'            => array(
						'name'    => 'seller_can_edit_password',
						'title'   => __( 'Seller can change password', 'superstore' ),
						'type'    => 'checkbox',
						'default' => 'yes',
					),
					'require_login_after_change_password' => array(
						'name'    => 'require_login_after_change_password',
						'title'   => __( 'Seller requires login after change password', 'superstore' ),
						'type'    => 'checkbox',
						'default' => 'yes',
					),
					'seller_can_access_admin_area'        => array(
						'name'    => 'seller_can_access_admin_area',
						'title'   => __( 'Seller can access admin area', 'superstore' ),
						'type'    => 'checkbox',
						'default' => 'no',
					),
					'show_seller_info_in_single_product_page_tab' => array(
						'name'    => 'show_seller_info_in_single_product_page_tab',
						'title'   => __( 'Show seller info in single product page tab', 'superstore' ),
						'type'    => 'checkbox',
						'default' => 'yes',
					),
					'show_seller_name_in_cart'            => array(
						'name'    => 'show_seller_name_in_cart',
						'title'   => __( 'Show seller name in cart', 'superstore' ),
						'type'    => 'checkbox',
						'default' => 'yes',
					),
					'seller_can_change_order_status'      => array(
						'name'    => 'seller_can_change_order_status',
						'title'   => __( 'Seller can change order status', 'superstore' ),
						'type'    => 'checkbox',
						'default' => 'yes',
					),
					'seller_can_add_multiple_categories'  => array(
						'name'    => 'seller_can_add_multiple_categories',
						'title'   => __( 'Seller can add multiple product categories', 'superstore' ),
						'type'    => 'checkbox',
						'default' => 'no',
					),
					'unlimited_storage_for_all_sellers'   => array(
						'name'    => 'unlimited_storage_for_all_sellers',
						'title'   => __( 'Unlimited storage for all sellers', 'superstore' ),
						'hint'    => __( 'If storage is not unlimited you can set storage limit by each seller', 'superstore' ),
						'type'    => 'checkbox',
						'default' => 'yes',
					),
					'seller_can_hide_on_store'            => array(
						'name'       => 'seller_can_hide_on_store',
						'title'      => __( 'Seller can hide on their store', 'superstore' ),
						'type'       => 'multiple',
						'value_type' => 'object',
						'items'      => $this->get_show_on_store_option_capabilities(),
					),
				),
			),
		);

		return apply_filters( 'superstore_admin_dashboard_localize_seller_settings_sections', $sections );
	}

	/**
	 * Show on single store options
	 *
	 * @return array
	 */
	public function get_show_on_store_option_capabilities() {
		$options = array(
			'email'                 => array(
				'child_name' => 'email',
				'label'      => __( 'Email', 'superstore' ),
				'type'       => 'checkbox',
				'default'    => 'yes',
			),
			'phone'                 => array(
				'child_name' => 'phone',
				'label'      => __( 'Phone', 'superstore' ),
				'type'       => 'checkbox',
				'default'    => 'yes',
			),
			'address'               => array(
				'child_name' => 'address',
				'label'      => __( 'Address', 'superstore' ),
				'type'       => 'checkbox',
				'default'    => 'yes',
			),
			'map'                   => array(
				'child_name' => 'map',
				'label'      => __( 'Map', 'superstore' ),
				'type'       => 'checkbox',
				'default'    => 'yes',
			),
			'contact'               => array(
				'child_name' => 'contact',
				'label'      => __( 'Contact', 'superstore' ),
				'type'       => 'checkbox',
				'default'    => 'no',
			),
			'about'                 => array(
				'child_name' => 'about',
				'label'      => __( 'About', 'superstore' ),
				'type'       => 'checkbox',
				'default'    => 'no',
			),
			'best_selling_products' => array(
				'child_name' => 'best_selling_products',
				'label'      => __( 'Best selling products', 'superstore' ),
				'type'       => 'checkbox',
				'default'    => 'no',
			),
			'latest_products'       => array(
				'child_name' => 'latest_products',
				'label'      => __( 'Latest products', 'superstore' ),
				'type'       => 'checkbox',
				'default'    => 'no',
			),
			'top_rated_products'    => array(
				'child_name' => 'top_rated_products',
				'label'      => __( 'Top rated products', 'superstore' ),
				'type'       => 'checkbox',
				'default'    => 'no',
			),
			'featured_products'     => array(
				'child_name' => 'featured_products',
				'label'      => __( 'Featured products', 'superstore' ),
				'type'       => 'checkbox',
				'default'    => 'no',
			),
		);

		return apply_filters( 'superstore_admin_dashboard_localize_show_on_store_option_capabilities', $options );
	}

	/**
	 * Payment settings form sections
	 *
	 * @return array
	 */
	public function get_payment_settings_form_sections() {
		$sections = array(
			array(
				'title'  => __( 'General', 'superstore' ),
				'fields' => array(
					'exclude_order_status_from_balance' => array(
						'name'       => 'exclude_order_status_from_balance',
						'title'      => __( 'Exclude order status from balance', 'superstore' ),
						'hint'       => __( 'If any order status of a seller is anyone above these, the order total amount/earnings will be excluded from the seller balance', 'superstore' ),
						'type'       => 'multiple',
						'value_type' => 'object',
						'items'      => apply_filters(
							'superstore_admin_dashboard_exclude_order_status_in_balance',
							array(
								'wc-completed'  => array(
									'child_name' => 'wc-completed',
									'label'      => __( 'Completed', 'superstore' ),
									'type'       => 'checkbox',
									'default'    => 'no',
								),
								'wc-processing' => array(
									'child_name' => 'wc-processing',
									'label'      => __( 'Processing', 'superstore' ),
									'type'       => 'checkbox',
									'default'    => 'yes',
								),
								'wc-pending'    => array(
									'child_name' => 'wc-pending',
									'label'      => __( 'Pending', 'superstore' ),
									'type'       => 'checkbox',
									'default'    => 'yes',
								),
								'wc-on-hold'    => array(
									'child_name' => 'wc-on-hold',
									'label'      => __( 'On hold', 'superstore' ),
									'type'       => 'checkbox',
									'default'    => 'yes',
								),
								'wc-cancelled'  => array(
									'child_name' => 'wc-cancelled',
									'label'      => __( 'Cancelled', 'superstore' ),
									'type'       => 'checkbox',
									'default'    => 'yes',
								),
								'wc-failed'     => array(
									'child_name' => 'wc-failed',
									'label'      => __( 'Failed', 'superstore' ),
									'type'       => 'checkbox',
									'default'    => 'yes',
								),
							),
						),
					),
					'withdraw_threshold_day'            => array(
						'name'    => 'withdraw_threshold_day',
						'title'   => __( 'Withdraw threshold day', 'superstore' ),
						'hint'    => __( 'Minimum gap requires for next withdraw request.', 'superstore' ),
						'type'    => 'number',
						'default' => '0',
					),
					'exclude_cod_payment'               => array(
						'name'    => 'exclude_cod_payment',
						'title'   => __( 'Exclude cod payment', 'superstore' ),
						'hint'    => __( 'Exclude payment from seller balance if order payment is cash on delivery(COD)', 'superstore' ),
						'type'    => 'checkbox',
						'default' => 'no',
					),
					'minimum_withdraw_amount'           => array(
						'name'  => 'minimum_withdraw_amount',
						'title' => __( 'Minimum withdraw amount', 'superstore' ),
						'hint'  => __( 'Nothing/empty/null means unlimited/no limit', 'superstore' ),
						'type'  => 'number',
					),
					'maximum_withdraw_amount'           => array(
						'name'  => 'maximum_withdraw_amount',
						'title' => __( 'Maximum withdraw amount', 'superstore' ),
						'hint'  => __( 'Nothing/empty/null means unlimited/no limit', 'superstore' ),
						'type'  => 'number',
					),
					'allowed_payment_methods'           => array(
						'name'       => 'allowed_payment_methods',
						'title'      => __( 'Allowed Payment methods', 'superstore' ),
						'hint'       => __( 'Allowed payment methods for sellers', 'superstore' ),
						'type'       => 'multiple',
						'value_type' => 'object',
						'items'      => apply_filters(
							'superstore_admin_dashboard_allowed_payment_methods',
							array(
								'paypal' => array(
									'child_name' => 'paypal',
									'label'      => __( 'Paypal', 'superstore' ),
									'type'       => 'checkbox',
									'default'    => 'yes',
								),
								'skrill' => array(
									'child_name' => 'skrill',
									'label'      => __( 'Skrill', 'superstore' ),
									'type'       => 'checkbox',
									'default'    => 'yes',
								),
								'bank'   => array(
									'child_name' => 'bank',
									'label'      => __( 'Bank', 'superstore' ),
									'type'       => 'checkbox',
									'default'    => 'yes',
								),
							),
						),
					),
				),
			),
		);

		return apply_filters( 'superstore_admin_dashboard_localize_payment_settings_sections', $sections );
	}

	/**
	 * Appearance settings form sections
	 *
	 * @return array
	 */
	public function get_appearance_settings_form_sections() {
		$sections = array(
			array(
				'title'  => __( 'General', 'superstore' ),
				'fields' => array(
					'tab_style_login_and_register_form' => array(
						'name'    => 'tab_style_login_and_register_form',
						'title'   => __( 'Tab style login and register form', 'superstore' ),
						'hint'    => __( 'Seller register and login form will display in tab style', 'superstore' ),
						'type'    => 'checkbox',
						'default' => 'yes',
					),
					'seller_register_url_style'         => array(
						'name'    => 'seller_register_url_style',
						'title'   => __( 'Seller register url style', 'superstore' ),
						'type'    => 'radio',
						'default' => 'button',
						'groups'  => array(
							array(
								'label' => __( 'Button', 'superstore' ),
								'value' => 'button',
							),
							array(
								'label' => __( 'Text', 'superstore' ),
								'value' => 'text',
							),
						),
					),
					'full_width_seller_dashboard'       => array(
						'name'    => 'full_width_seller_dashboard',
						'title'   => __( 'Full width seller dashboard', 'superstore' ),
						'type'    => 'checkbox',
						'default' => 'yes',
					),
					'store_banner_height'               => array(
						'name'    => 'store_banner_height',
						'title'   => __( 'Store banner height', 'superstore' ),
						'hint'    => __( 'In pixel', 'superstore' ),
						'type'    => 'text',
						'default' => '300',
					),
					'store_banner_width'                => array(
						'name'    => 'store_banner_width',
						'title'   => __( 'Store banner width', 'superstore' ),
						'hint'    => __( 'In pixel', 'superstore' ),
						'type'    => 'text',
						'default' => '2000',
					),
					'seller_dashboard_primary_color'    => array(
						'name'    => 'seller_dashboard_primary_color',
						'title'   => __( 'Seller dashboard primary color', 'superstore' ),
						'type'    => 'color_picker',
						'default' => '#6f6af8',
					),
					'admin_dashboard_primary_color'     => array(
						'name'    => 'admin_dashboard_primary_color',
						'title'   => __( 'Admin dashboard primary color', 'superstore' ),
						'type'    => 'color_picker',
						'default' => '#6f6af8',
					),
					'seller_register_button_background_color' => array(
						'name'    => 'seller_register_button_background_color',
						'title'   => __( 'Seller register button background color', 'superstore' ),
						'type'    => 'color_picker',
						'default' => '#b357ff',
					),
					'seller_register_button_text_color' => array(
						'name'    => 'seller_register_button_text_color',
						'title'   => __( 'Seller register button text color', 'superstore' ),
						'hint'    => __( 'Any css or vuetify color', 'superstore' ),
						'type'    => 'color_picker',
						'default' => '#ffffff',
					),
				),
			),
		);

		return apply_filters( 'superstore_admin_dashboard_localize_appearance_settings_sections', $sections );
	}
}
