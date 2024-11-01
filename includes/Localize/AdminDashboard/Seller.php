<?php

namespace Binarithm\Superstore\Localize\AdminDashboard;

/**
 * Superstore admin dashboard localize seller class
 */
class Seller {

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
		$data['seller'] = array(
			'tab' => array(
				'active' => '/seller',
				'tabs'   => $this->tabs(),
				'body'   => $this->tabs_body(),
			),
		);

		return apply_filters( 'superstore_admin_dashboard_localize_seller_tab', $data );
	}

	/**
	 * Add tabs
	 *
	 * @return array
	 */
	public function tabs() {
		$tabs = array(
			'sellers'        => array(
				'title' => __( 'Sellers', 'superstore' ),
				'route' => '/seller',
			),
			'add_new_seller' => array(
				'title' => __( 'Add new seller', 'superstore' ),
				'route' => '/seller/add-new-seller',
			),
		);

		return apply_filters( 'superstore_admin_dashboard_localize_seller_tabs', $tabs );
	}

	/**
	 * Tabs body data
	 *
	 * @return array
	 */
	public function tabs_body() {
		$data = array(
			'sellers'        => array(
				'table'  => array(
					'filterItems'  => $this->table_filters(),
					'links'        => $this->table_links(),
					'actions'      => $this->table_actions(),
					'headers'      => $this->table_headers(),
					'restEndpoint' => '/sellers/',
				),
				'notify' => $this->get_sellers_notifications(),
				'single' => $this->get_single_seller_data(),
			),
			'add_new_seller' => array(
				'form'   => array(
					'fields'      => superstore_get_form_field_values_from_sections( $this->get_add_new_seller_form_sections() ),
					'sections'    => $this->get_add_new_seller_form_sections(),
					'submitEvent' => 'seller/addNewSeller',
				),
				'notify' => apply_filters(
					'superstore_admin_dashboard_localize_add_new_seller_notifications',
					array(
						'account_created' => __( 'A seller account has been created successfully', 'superstore' ),
					)
				),
			),
		);

		return apply_filters( 'superstore_admin_dashboard_localize_seller_tabs_body', $data );
	}

	/**
	 * Table filters
	 *
	 * @return array
	 */
	public function table_filters() {
		$data = array(
			array(
				'title' => __( 'All', 'superstore' ),
				'name'  => 'all',
				'value' => null,
			),
			array(
				'title' => __( 'Enabled', 'superstore' ),
				'name'  => 'enabled',
				'value' => 'yes',
			),
			array(
				'title' => __( 'Not enabled', 'superstore' ),
				'name'  => 'not_enabled',
				'value' => 'yes',
			),
			array(
				'title' => __( 'Featured', 'superstore' ),
				'name'  => 'featured',
				'value' => 'yes',
			),
			array(
				'title' => __( 'Registered current month', 'superstore' ),
				'name'  => 'current_month',
				'value' => '1 month ago',
			),
		);

		return apply_filters( 'superstore_admin_dashboard_localize_seller_table_filters', $data );
	}

	/**
	 * Table top right links
	 *
	 * @return array
	 */
	public function table_links() {
		$data = array(
			array(
				'title' => __( 'Seller settings', 'superstore' ),
				'to'    => '/settings/seller',
			),
		);

		return apply_filters( 'superstore_admin_dashboard_localize_seller_table_links', $data );
	}

	/**
	 * Table actions with js namespace methods
	 *
	 * @return array
	 */
	public function table_actions() {
		$data = array(
			array(
				'title'  => __( 'Activate', 'superstore' ),
				'name'   => 'enabled',
				'value'  => 'yes',
				'method' => 'seller/edit',
			),
			array(
				'title'  => __( 'Deactivate', 'superstore' ),
				'name'   => 'enabled',
				'value'  => 'no',
				'method' => 'seller/edit',
			),
			array(
				'title'       => __( 'Enable product review', 'superstore' ),
				'name'        => 'requires_product_review',
				'value'       => 'yes',
				'method'      => 'seller/edit',
				'skip_in_row' => 'yes',
			),
			array(
				'title'       => __( 'Disable product review', 'superstore' ),
				'name'        => 'requires_product_review',
				'value'       => 'no',
				'method'      => 'seller/edit',
				'skip_in_row' => 'yes',
			),
			array(
				'title'       => __( 'Make featured', 'superstore' ),
				'name'        => 'featured',
				'value'       => 'yes',
				'method'      => 'seller/edit',
				'skip_in_row' => 'yes',
			),
			array(
				'title'       => __( 'Make unfeatured', 'superstore' ),
				'name'        => 'featured',
				'value'       => 'no',
				'method'      => 'seller/edit',
				'skip_in_row' => 'yes',
			),
			array(
				'title'  => __( 'Delete', 'superstore' ),
				'name'   => 'delete',
				'method' => 'seller/delete',
			),
		);

		return apply_filters( 'superstore_admin_dashboard_localize_seller_table_actions', $data );
	}

	/**
	 * Table headers
	 *
	 * @return array
	 */
	public function table_headers() {
		$headers = array(
			array(
				'text'     => __( 'Seller', 'superstore' ),
				'sortable' => true,
				'value'    => 'seller',
				'class'    => 'font-weight-bold',
			),
			array(
				'text'     => 'Earnings',
				'sortable' => true,
				'value'    => 'earnings',
				'class'    => 'font-weight-bold',
				'sortable' => true,
			),
			array(
				'text'     => __( 'Contact', 'superstore' ),
				'sortable' => false,
				'value'    => 'contact',
				'class'    => 'font-weight-bold',
			),
			array(
				'text'     => __( 'Registered', 'superstore' ),
				'sortable' => true,
				'value'    => 'date_created',
				'class'    => 'font-weight-bold',
			),
			array(
				'text'     => __( 'Enabled', 'superstore' ),
				'sortable' => true,
				'value'    => 'enabled',
				'class'    => 'font-weight-bold',
			),
			array(
				'text'     => __( 'Action', 'superstore' ),
				'sortable' => false,
				'value'    => 'action',
				'class'    => 'font-weight-bold',
			),
		);

		return apply_filters( 'superstore_admin_dashboard_localize_seller_table_headers', $headers );
	}

	/**
	 * Add sellers notifications
	 *
	 * @return array
	 */
	public function get_sellers_notifications() {
		$notifications = array(
			'activate'                => __( 'Successfully activated', 'superstore' ),
			'deactivate'              => __( 'Successfully deactivated', 'superstore' ),
			'enabled_product_review'  => __( 'Successfully enabled product review', 'superstore' ),
			'disabled_product_review' => __( 'Successfully disabled product review', 'superstore' ),
			'featured'                => __( 'Successfully featured', 'superstore' ),
			'unfeatured'              => __( 'Successfully unfeatured', 'superstore' ),
		);

		return apply_filters( 'superstore_admin_dashboard_localize_sellers_notifications', $notifications );
	}

	/**
	 * Single seller data
	 *
	 * @return array
	 */
	public function get_single_seller_data() {
		$data = array(
			'form'                  => array(
				'fields'      => superstore_get_form_field_values_from_sections( $this->get_single_seller_form_sections() ),
				'sections'    => $this->get_single_seller_form_sections(),
				'submitEvent' => 'seller/addNewSeller',
			),
			'short_details_text'    => __( 'Short details', 'superstore' ),
			'link_btn_action_title' => __( 'Actions', 'superstore' ),
			'link_btns'             => $this->get_sinlge_seller_link_btns(),
			'about_title'           => __( 'About', 'superstore' ),
			'overviews'             => $this->get_single_seller_overviews_data(),
			'seller_not_found'      => __( 'Seller not found', 'superstore' ),
		);

		return apply_filters( 'superstore_admin_dashboard_localize_single_seller_data', $data );
	}

	/**
	 * Single seller link buttons
	 *
	 * @return array
	 */
	public function get_sinlge_seller_link_btns() {
		$data = array(
			array(
				'title' => __( 'Products', 'superstore' ),
				'name'  => 'products',
			),
			array(
				'title' => __( 'Orders', 'superstore' ),
				'name'  => 'orders',
			),
			array(
				'title' => __( 'Files', 'superstore' ),
				'name'  => 'files',
			),
			array(
				'title' => __( 'Visit store', 'superstore' ),
				'name'  => 'visit_store',
			),
			array(
				'title' => __( 'Edit more', 'superstore' ),
				'name'  => 'edit_more',
			),
		);

		return apply_filters( 'superstore_admin_dashboard_localize_single_seller_link_buttons', $data );
	}

	/**
	 * Single seller edit form sections
	 *
	 * @return array
	 */
	public function get_single_seller_form_sections() {
		$countries = array();
		foreach ( WC()->countries->get_countries() as $code => $name ) {
			$countries[] = array(
				'title' => $name,
				'value' => $code,
			);
		}
		$sections = array(
			array(
				'title'  => __( 'General', 'superstore' ),
				'fields' => array(
					'first_name'              => array(
						'name'  => 'first_name',
						'title' => __( 'First name', 'superstore' ),
						'type'  => 'text',
					),
					'last_name'               => array(
						'name'  => 'last_name',
						'title' => __( 'Last name', 'superstore' ),
						'type'  => 'text',
					),
					'store_name'              => array(
						'name'  => 'store_name',
						'title' => __( 'Store name', 'superstore' ),
						'type'  => 'text',
					),
					'store_url_nicename'      => array(
						'name'  => 'store_url_nicename',
						'title' => __( 'Store url prefix', 'superstore' ),
						'type'  => 'text',
					),
					'email'                   => array(
						'name'  => 'email',
						'title' => __( 'Email', 'superstore' ),
						'type'  => 'email',
					),
					'date_created'            => array(
						'name'     => 'date_created',
						'title'    => __( 'Registered', 'superstore' ),
						'type'     => 'text',
						'disabled' => 'yes',
					),
					'phone'                   => array(
						'name'  => 'phone',
						'title' => __( 'Phone', 'superstore' ),
						'type'  => 'text',
					),
					'address'                 => array(
						'name'      => 'address',
						'title'     => __( 'Address', 'superstore' ),
						'slot_name' => 'address',
						'type'      => 'slot',
						'items'     => array(
							'address_country'  => array(
								'name'  => 'address_country',
								'label' => __( 'Country', 'superstore' ),
								'items' => $countries,
							),
							'address_state'    => array(
								'name'  => 'address_state',
								'label' => __( 'State', 'superstore' ),
							),
							'address_postcode' => array(
								'name'  => 'address_postcode',
								'label' => __( 'Postcode', 'superstore' ),
							),
							'address_city'     => array(
								'name'  => 'address_city',
								'label' => __( 'City', 'superstore' ),
							),
							'address_street_1' => array(
								'name'  => 'address_street_1',
								'label' => __( 'Street 1', 'superstore' ),
							),
							'address_street_2' => array(
								'name'  => 'address_street_2',
								'label' => __( 'Street 2', 'superstore' ),
							),
						),
					),
					'geolocation'             => array(
						'name'  => 'geolocation',
						'title' => __( 'Geolocation', 'superstore' ),
						'type'  => 'multiple',
						'items' => array(
							'geolocation_latitude'  => array(
								'name'       => 'geolocation_latitude',
								'child_name' => 'latitude',
								'label'      => __( 'Latitude', 'superstore' ),
								'type'       => 'number',
								'default'    => 0,
							),
							'geolocation_longitude' => array(
								'name'       => 'geolocation_longitude',
								'child_name' => 'longitude',
								'label'      => __( 'Longitude', 'superstore' ),
								'type'       => 'number',
								'default'    => 0,
							),
						),
					),
					'about'                   => array(
						'name'  => 'about',
						'title' => __( 'About', 'superstore' ),
						'type'  => 'textarea',
					),
					'admin_commission'        => array(
						'name'  => 'admin_commission',
						'title' => __( 'Admin commission', 'superstore' ),
						'type'  => 'multiple',
						'items' => array(
							'admin_commission_type' => array(
								'name'       => 'admin_commission_type',
								'child_name' => 'type',
								'label'      => __( 'Type', 'superstore' ),
								'type'       => 'select',
								'items'      => array(
									array(
										'title' => __( 'Percentage', 'superstore' ),
										'value' => 'percentage',
									),
									array(
										'title' => __( 'Flat', 'superstore' ),
										'value' => 'flat',
									),
								),
							),
							'admin_commission_rate' => array(
								'name'       => 'admin_commission_rate',
								'child_name' => 'rate',
								'label'      => __( 'Rate', 'superstore' ),
								'type'       => 'number',
							),
						),
					),
					'enabled'                 => array(
						'name'    => 'enabled',
						'title'   => __( 'Enabled', 'superstore' ),
						'hint'    => __( 'Seller account status', 'superstore' ),
						'type'    => 'switch',
						'default' => 'no',
					),
					'featured'                => array(
						'name'    => 'featured',
						'title'   => __( 'Featured', 'superstore' ),
						'type'    => 'checkbox',
						'default' => 'no',
					),
					'requires_product_review' => array(
						'name'    => 'requires_product_review',
						'title'   => __( 'Requires product review', 'superstore' ),
						'hint'    => __( 'Seller requires review before product is live', 'superstore' ),
						'type'    => 'checkbox',
						'default' => 'no',
					),
					'storage_limit'           => array(
						'name'  => 'storage_limit',
						'title' => __( 'Storage limit', 'superstore' ),
						'hint'  => __( 'Number will be calculated in MB. For unlimited storage enter -1', 'superstore' ),
						'type'  => 'number',
					),
					'tnc'                     => array(
						'name'  => 'tnc',
						'title' => __( 'Terms and conditions', 'superstore' ),
						'type'  => 'multiple',
						'items' => array(
							'tnc_enabled' => array(
								'name'       => 'tnc_enabled',
								'child_name' => 'enabled',
								'label'      => __( 'Enabled', 'superstore' ),
								'type'       => 'checkbox',
							),
							'tnc_text'    => array(
								'name'       => 'tnc_text',
								'child_name' => 'text',
								'label'      => __( 'text', 'superstore' ),
								'type'       => 'textarea',
							),
						),
					),
					'store_products_per_page' => array(
						'name'  => 'store_products_per_page',
						'title' => __( 'Store products per page', 'superstore' ),
						'type'  => 'number',
					),
					'withdraw_threshold_day'  => array(
						'name'  => 'withdraw_threshold_day',
						'title' => __( 'Withdraw threshold day', 'superstore' ),
						'type'  => 'number',
					),
				),
			),
			array(
				'title'  => __( 'Payment methods', 'superstore' ),
				'fields' => $this->get_single_seller_payment_method_fields(),
			),
			array(
				'title'  => __( 'Show on store', 'superstore' ),
				'fields' => array(
					'show_on_store' => array(
						'name'  => 'show_on_store',
						'title' => __( 'Show on store', 'superstore' ),
						'type'  => 'multiple',
						'items' => array(
							'show_on_store_email'   => array(
								'name'       => 'show_on_store_email',
								'child_name' => 'email',
								'label'      => __( 'Email', 'superstore' ),
								'type'       => 'checkbox',
							),
							'show_on_store_phone'   => array(
								'name'       => 'show_on_store_phone',
								'child_name' => 'phone',
								'label'      => __( 'Phone', 'superstore' ),
								'type'       => 'checkbox',
							),
							'show_on_store_address' => array(
								'name'       => 'show_on_store_address',
								'child_name' => 'address',
								'label'      => __( 'Address', 'superstore' ),
								'type'       => 'checkbox',
							),
							'show_on_store_map'     => array(
								'name'       => 'show_on_store_map',
								'child_name' => 'map',
								'label'      => __( 'Map', 'superstore' ),
								'type'       => 'checkbox',
							),
							'show_on_store_about'   => array(
								'name'       => 'show_on_store_about',
								'child_name' => 'about',
								'label'      => __( 'About', 'superstore' ),
								'type'       => 'checkbox',
							),
							'show_on_store_contact' => array(
								'name'       => 'show_on_store_contact',
								'child_name' => 'contact',
								'label'      => __( 'Contact form', 'superstore' ),
								'type'       => 'checkbox',
							),
							'show_on_store_best_selling_products' => array(
								'name'       => 'show_on_store_best_selling_products',
								'child_name' => 'best_selling_products',
								'label'      => __( 'Best selling products', 'superstore' ),
								'type'       => 'checkbox',
							),
							'show_on_store_latest_products' => array(
								'name'       => 'show_on_store_latest_products',
								'child_name' => 'latest_products',
								'label'      => __( 'Latest products', 'superstore' ),
								'type'       => 'checkbox',
							),
							'show_on_store_top_rated_products' => array(
								'name'       => 'show_on_store_top_rated_products',
								'child_name' => 'top_rated_products',
								'label'      => __( 'Top rated products', 'superstore' ),
								'type'       => 'checkbox',
							),
							'show_on_store_featured_products' => array(
								'name'       => 'show_on_store_featured_products',
								'child_name' => 'featured_products',
								'label'      => __( 'Featured products', 'superstore' ),
								'type'       => 'checkbox',
							),
						),
					),
				),
			),
			array(
				'title'  => __( 'Store open close schedule', 'superstore' ),
				'fields' => array(
					'store_time' => array(
						'name'            => 'store_time',
						'title'           => __( 'Store time', 'superstore' ),
						'open_hour_text'  => __( 'Open time', 'superstore' ),
						'close_hour_text' => __( 'Close time', 'superstore' ),
						'close_menu_text' => __( 'OK', 'superstore' ),
						'slot_name'       => 'store_time',
						'type'            => 'slot',
						'items'           => array(
							'store_time_enabled'        => array(
								'name'  => 'store_time_enabled',
								'label' => __( 'Enabled', 'superstore' ),
							),
							'store_time_open_notice'    => array(
								'name'  => 'store_time_open_notice',
								'label' => __( 'Open notice', 'superstore' ),
							),
							'store_time_close_notice'   => array(
								'name'  => 'store_time_close_notice',
								'label' => __( 'Close notice', 'superstore' ),
							),
							'store_time_off_day_notice' => array(
								'name'  => 'store_time_off_day_notice',
								'label' => __( 'Off day notice', 'superstore' ),
							),
							'store_time_open_24_hours_notice' => array(
								'name'  => 'store_time_open_24_hours_notice',
								'label' => __( '24 hours open notice', 'superstore' ),
							),
						),
						'items2'          => array(
							'store_time_open_sunday'    => array(
								'name'          => 'store_time_open_sunday',
								'child_name'    => 'open_sunday',
								'title'         => __( 'Sunday', 'superstore' ),
								'groups'        => array(
									array(
										'label' => __( 'Open', 'superstore' ),
										'value' => 'yes',
									),
									array(
										'label' => __( 'Close', 'superstore' ),
										'value' => 'no',
									),
								),
								'opening_hours' => array(
									'name'       => 'store_time_sunday_opening_hours',
									'child_name' => 'sunday_opening_hours',
									'label'      => __( 'Opening hours', 'superstore' ),
								),
							),
							'store_time_open_monday'    => array(
								'name'          => 'store_time_open_monday',
								'child_name'    => 'open_monday',
								'title'         => __( 'Monday', 'superstore' ),
								'groups'        => array(
									array(
										'label' => __( 'Open', 'superstore' ),
										'value' => 'yes',
									),
									array(
										'label' => __( 'Close', 'superstore' ),
										'value' => 'no',
									),
								),
								'opening_hours' => array(
									'name'       => 'store_time_monday_opening_hours',
									'child_name' => 'monday_opening_hours',
									'label'      => __( 'Opening hours', 'superstore' ),
								),
							),
							'store_time_open_tuesday'   => array(
								'name'          => 'store_time_open_tuesday',
								'child_name'    => 'open_tuesday',
								'title'         => __( 'Tuesday', 'superstore' ),
								'groups'        => array(
									array(
										'label' => __( 'Open', 'superstore' ),
										'value' => 'yes',
									),
									array(
										'label' => __( 'Close', 'superstore' ),
										'value' => 'no',
									),
								),
								'opening_hours' => array(
									'name'       => 'store_time_tuesday_opening_hours',
									'child_name' => 'tuesday_opening_hours',
									'label'      => __( 'Opening hours', 'superstore' ),
								),
							),
							'store_time_open_wednesday' => array(
								'name'          => 'store_time_open_wednesday',
								'child_name'    => 'open_wednesday',
								'title'         => __( 'Wednesday', 'superstore' ),
								'groups'        => array(
									array(
										'label' => __( 'Open', 'superstore' ),
										'value' => 'yes',
									),
									array(
										'label' => __( 'Close', 'superstore' ),
										'value' => 'no',
									),
								),
								'opening_hours' => array(
									'name'       => 'store_time_wednesday_opening_hours',
									'child_name' => 'wednesday_opening_hours',
									'label'      => __( 'Opening hours', 'superstore' ),
								),
							),
							'store_time_open_thursday'  => array(
								'name'          => 'store_time_open_thursday',
								'child_name'    => 'open_thursday',
								'title'         => __( 'Thursday', 'superstore' ),
								'groups'        => array(
									array(
										'label' => __( 'Open', 'superstore' ),
										'value' => 'yes',
									),
									array(
										'label' => __( 'Close', 'superstore' ),
										'value' => 'no',
									),
								),
								'opening_hours' => array(
									'name'       => 'store_time_thursday_opening_hours',
									'child_name' => 'thursday_opening_hours',
									'label'      => __( 'Opening hours', 'superstore' ),
								),
							),
							'store_time_open_friday'    => array(
								'name'          => 'store_time_open_friday',
								'child_name'    => 'open_friday',
								'title'         => __( 'Friday', 'superstore' ),
								'groups'        => array(
									array(
										'label' => __( 'Open', 'superstore' ),
										'value' => 'yes',
									),
									array(
										'label' => __( 'Close', 'superstore' ),
										'value' => 'no',
									),
								),
								'opening_hours' => array(
									'name'       => 'store_time_friday_opening_hours',
									'child_name' => 'friday_opening_hours',
									'label'      => __( 'Opening hours', 'superstore' ),
								),
							),
							'store_time_open_saturday'  => array(
								'name'          => 'store_time_open_saturday',
								'child_name'    => 'open_saturday',
								'title'         => __( 'Saturday', 'superstore' ),
								'groups'        => array(
									array(
										'label' => __( 'Open', 'superstore' ),
										'value' => 'yes',
									),
									array(
										'label' => __( 'Close', 'superstore' ),
										'value' => 'no',
									),
								),
								'opening_hours' => array(
									'name'       => 'store_time_saturday_opening_hours',
									'child_name' => 'saturday_opening_hours',
									'label'      => __( 'Opening hours', 'superstore' ),
								),
							),
						),
					),
				),
			),
		);
		return apply_filters( 'superstore_admin_dashboard_localize_single_seller_form_sections', $sections );
	}

	/**
	 * Add new seller form sections
	 *
	 * @return array
	 */
	public function get_add_new_seller_form_sections() {
		$sections = array(
			array(
				'title'  => __( 'Account info', 'superstore' ),
				'fields' => array(
					'first_name'              => array(
						'name'  => 'first_name',
						'title' => __( 'First name', 'superstore' ),
						'type'  => 'text',
					),
					'last_name'               => array(
						'name'  => 'last_name',
						'title' => __( 'Last name', 'superstore' ),
						'type'  => 'text',
					),
					'store_name'              => array(
						'name'  => 'store_name',
						'title' => __( 'Store name', 'superstore' ),
						'type'  => 'text',
					),
					'store_url_nicename'      => array(
						'name'  => 'store_url_nicename',
						'title' => __( 'Store url prefix', 'superstore' ),
						'type'  => 'text',
					),
					'email'                   => array(
						'name'     => 'email',
						'title'    => __( 'Email', 'superstore' ),
						'type'     => 'email',
						'required' => 'yes',
					),
					'user_login'              => array(
						'name'  => 'user_login',
						'title' => __( 'Username', 'superstore' ),
						'type'  => 'text',
					),
					'password'                => array(
						'name'  => 'password',
						'title' => __( 'Password', 'superstore' ),
						'type'  => 'password',
					),
					'enabled'                 => array(
						'name'    => 'enabled',
						'title'   => __( 'Enabled', 'superstore' ),
						'type'    => 'checkbox',
						'default' => 'yes',
					),
					'requires_product_review' => array(
						'name'    => 'requires_product_review',
						'title'   => __( 'Requires review before product publishing', 'superstore' ),
						'type'    => 'checkbox',
						'default' => 'yes',
					),
				),
			),
			array(
				'title'  => __( 'Media', 'superstore' ),
				'fields' => array(
					'banner_id'          => array(
						'name'            => 'banner_id',
						'title'           => __( 'Banner', 'superstore' ),
						'cropping_width'  => superstore_get_option( 'store_banner_width', 'superstore_appearance', 1920 ),
						'cropping_height' => superstore_get_option( 'store_banner_height', 'superstore_appearance', 300 ),
						'type'            => 'file',
					),
					'profile_picture_id' => array(
						'name'            => 'profile_picture_id',
						'title'           => __( 'Profile picture', 'superstore' ),
						'cropping_width'  => 100,
						'cropping_height' => 100,
						'type'            => 'file',
					),
				),
			),
		);
		return apply_filters( 'superstore_admin_dashboard_localize_add_new_seller_form_sections', $sections );
	}

	/**
	 * Get seller overview data
	 *
	 * @return array
	 */
	public function get_single_seller_overviews_data() {
		$data = array(
			'products_overview' => array(
				'title' => 'Products overview',
				'items' => array(
					'total'           => array(
						'title' => 'Total',
						'value' => null,
					),
					'published'       => array(
						'title' => 'Published',
						'value' => null,
					),
					'draft'           => array(
						'title' => 'Draft',
						'value' => null,
					),
					'pending'         => array(
						'title' => 'Pending',
						'value' => null,
					),
					'views'           => array(
						'title' => 'Views',
						'value' => null,
					),
					'avarage_ratings' => array(
						'title' => 'Avarage ratings',
						'value' => null,
					),
					'total_reviews'   => array(
						'title' => 'Total reviews',
						'value' => null,
					),
				),
			),
			'orders_overview'   => array(
				'title' => 'Orders overview',
				'items' => array(
					'total'      => array(
						'title' => 'Total',
						'value' => null,
					),
					'pending'    => array(
						'title' => 'Pending',
						'value' => null,
					),
					'completed'  => array(
						'title' => 'Completed',
						'value' => null,
					),
					'on-hold'    => array(
						'title' => 'On hold',
						'value' => null,
					),
					'processing' => array(
						'title' => 'Processing',
						'value' => null,
					),
					'refunded'   => array(
						'title' => 'Refunded',
						'value' => null,
					),
					'cancelled'  => array(
						'title' => 'Cancelled',
						'value' => null,
					),
					'failed'     => array(
						'title' => 'Failed',
						'value' => null,
					),
					'sales'      => array(
						'title' => 'Sales',
						'value' => null,
					),
					'earnings'   => array(
						'title' => 'Earnings',
						'value' => null,
					),
				),
			),
			'payments_overview' => array(
				'title' => 'Payments overview',
				'items' => array(
					'total'           => array(
						'title' => 'Total',
						'value' => null,
					),
					'paid'            => array(
						'title' => 'Paid',
						'value' => null,
					),
					'approved'        => array(
						'title' => 'Approved',
						'value' => null,
					),
					'pending'         => array(
						'title' => 'Pending',
						'value' => null,
					),
					'cancelled'       => array(
						'title' => 'Cancelled',
						'value' => null,
					),
					'current_balance' => array(
						'title' => 'Current balance',
						'value' => null,
					),
				),
			),
			'media_overview'    => array(
				'title' => 'Media overview',
				'items' => array(
					'total_files'       => array(
						'title' => 'Total files',
						'value' => null,
					),
					'storage_occupied'  => array(
						'title' => 'Storage occupied',
						'value' => null,
					),
					'storage_available' => array(
						'title' => 'Storage available',
						'value' => null,
					),
				),
			),
		);
		return apply_filters( 'superstore_admin_dashboard_localize_single_seller_overviews_data', $data );
	}

	/**
	 * Get seller overview data
	 *
	 * @return array
	 */
	public function get_single_seller_payment_method_fields() {
		$data = array(
			'payment_method_paypal_email' => array(
				'name'      => 'payment_method_paypal_email',
				'title'     => __( 'Paypal email', 'superstore' ),
				'slot_name' => 'payment_method_paypal_email',
				'type'      => 'slot',
			),
			'payment_method_skrill_email' => array(
				'name'      => 'payment_method_skrill_email',
				'title'     => __( 'Skrill email', 'superstore' ),
				'slot_name' => 'payment_method_skrill_email',
				'type'      => 'slot',
			),
			'payment_method'              => array(
				'name'  => 'payment_method',
				'title' => __( 'Bank details', 'superstore' ),
				'type'  => 'multiple',
				'items' => array(
					'payment_method_bank_ac_name'        => array(
						'name'       => 'payment_method_bank_ac_name',
						'child_name' => 'bank_ac_name',
						'label'      => __( 'Account name', 'superstore' ),
						'type'       => 'text',
					),
					'payment_method_bank_ac_number'      => array(
						'name'       => 'payment_method_bank_ac_number',
						'child_name' => 'bank_ac_number',
						'label'      => __( 'Account number', 'superstore' ),
						'type'       => 'text',
					),
					'payment_method_bank_name'           => array(
						'name'       => 'payment_method_bank_name',
						'child_name' => 'bank_name',
						'label'      => __( 'Bank name', 'superstore' ),
						'type'       => 'text',
					),
					'payment_method_bank_address'        => array(
						'name'       => 'payment_method_bank_address',
						'child_name' => 'bank_address',
						'label'      => __( 'Bank address', 'superstore' ),
						'type'       => 'text',
					),
					'payment_method_bank_routing_number' => array(
						'name'       => 'payment_method_bank_routing_number',
						'child_name' => 'routing_number',
						'label'      => __( 'Routing number', 'superstore' ),
						'type'       => 'text',
					),
					'payment_method_bank_iban'           => array(
						'name'       => 'payment_method_bank_iban',
						'child_name' => 'bank_iban',
						'label'      => __( 'IBAN', 'superstore' ),
						'type'       => 'text',
					),
					'payment_method_bank_swift'          => array(
						'name'       => 'payment_method_bank_swift',
						'child_name' => 'bank_swift',
						'label'      => __( 'Swift', 'superstore' ),
						'type'       => 'text',
					),
				),
			),
		);
		return apply_filters( 'superstore_admin_dashboard_localize_single_seller_payment_method_fields', $data );
	}
}
