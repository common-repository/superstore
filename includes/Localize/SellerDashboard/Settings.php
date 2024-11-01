<?php

namespace Binarithm\Superstore\Localize\SellerDashboard;

/**
 * Superstore seller dashboard localize settings class
 */
class Settings {

	/**
	 * Class constructor
	 */
	public function __construct() {
		add_filter( 'superstore_frontend_localize_data', array( $this, 'add_data' ) );
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

		return apply_filters( 'superstore_seller_dashboard_localize_settings_data', $data );
	}

	/**
	 * Add tabs
	 *
	 * @return array
	 */
	public function tabs() {
		$tabs = array(
			'account' => array(
				'title' => __( 'Account', 'superstore' ),
				'route' => '/settings',
			),
			'payment' => array(
				'title' => __( 'Payment', 'superstore' ),
				'route' => '/settings/payment',
			),
		);

		return apply_filters( 'superstore_seller_dashboard_localize_settings_tabs', $tabs );
	}

	/**
	 * Tabs body data
	 *
	 * @return array
	 */
	public function tabs_body() {
		$data = array(
			'account' => array(
				'form'               => array(
					'fields'          => superstore_get_form_field_values_from_sections( $this->get_account_settings_form_sections() ),
					'sections'        => $this->get_account_settings_form_sections(),
					'submitEvent'     => 'settings/edit',
					'submitExtraData' => 'account',
				),
				'settings_not_found' => __( 'Can not get settings', 'superstore' ),
			),
			'payment' => array(
				'form'               => array(
					'fields'          => superstore_get_form_field_values_from_sections( $this->get_payment_settings_form_sections() ),
					'sections'        => $this->get_payment_settings_form_sections(),
					'submitEvent'     => 'settings/edit',
					'submitExtraData' => 'payment',
				),
				'settings_not_found' => __( 'Can not get settings', 'superstore' ),
			),
		);

		return apply_filters( 'superstore_seller_dashboard_localize_settings_tabs_body', $data );
	}

	/**
	 * Account settings edit form sections
	 *
	 * @return array
	 */
	public function get_account_settings_form_sections() {
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

		if ( 'yes' === superstore_get_option( 'seller_can_edit_password', 'superstore_seller', 'yes' ) ) {
			$sections[0]['fields']['change_password'] = array(
				'name'               => 'change_password',
				'title'              => __( 'Change password', 'superstore' ),
				'slot_name'          => 'change_password',
				'type'               => 'slot',
				'old_label'          => __( 'Old password', 'superstore' ),
				'old_valid_text'     => __( 'Old password is required', 'superstore' ),
				'new_label'          => __( 'New password', 'superstore' ),
				'new_valid_text'     => __( 'Minimum 6 characters', 'superstore' ),
				'confirm_label'      => __( 'Confirm new password', 'superstore' ),
				'confirm_valid_text' => __( 'Password must match', 'superstore' ),
				'cancel_text'        => __( 'Cancel', 'superstore' ),
				'change_text'        => __( 'Change', 'superstore' ),
			);
		}

		if ( ! empty( $this->get_show_on_store_account_settings_section() ) ) {
			$sections[] = $this->get_show_on_store_account_settings_section();
		}

		return apply_filters( 'superstore_seller_dashboard_localize_account_settings_form_sections', $sections );
	}

	/**
	 * Show on store account settings edit form section
	 *
	 * @return array
	 */
	public function get_show_on_store_account_settings_section() {
		$defaults              = array(
			'email'                 => 'yes',
			'phone'                 => 'yes',
			'address'               => 'yes',
			'map'                   => 'yes',
			'contact'               => 'no',
			'at_a_glance'           => 'no',
			'about'                 => 'no',
			'best_selling_products' => 'no',
			'latest_products'       => 'no',
			'top_rated_products'    => 'no',
			'featured_products'     => 'no',
		);
		$settings_capabilities = superstore_get_option( 'seller_can_hide_on_store', 'superstore_seller', $defaults );

		$all_items = array(
			'show_on_store_email'                 => array(
				'name'       => 'show_on_store_email',
				'child_name' => 'email',
				'label'      => __( 'Email', 'superstore' ),
				'type'       => 'checkbox',
			),
			'show_on_store_phone'                 => array(
				'name'       => 'show_on_store_phone',
				'child_name' => 'phone',
				'label'      => __( 'Phone', 'superstore' ),
				'type'       => 'checkbox',
			),
			'show_on_store_address'               => array(
				'name'       => 'show_on_store_address',
				'child_name' => 'address',
				'label'      => __( 'Address', 'superstore' ),
				'type'       => 'checkbox',
			),
			'show_on_store_map'                   => array(
				'name'       => 'show_on_store_map',
				'child_name' => 'map',
				'label'      => __( 'Map', 'superstore' ),
				'type'       => 'checkbox',
			),
			'show_on_store_about'                 => array(
				'name'       => 'show_on_store_about',
				'child_name' => 'about',
				'label'      => __( 'About', 'superstore' ),
				'type'       => 'checkbox',
			),
			'show_on_store_contact'               => array(
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
			'show_on_store_latest_products'       => array(
				'name'       => 'show_on_store_latest_products',
				'child_name' => 'latest_products',
				'label'      => __( 'Latest products', 'superstore' ),
				'type'       => 'checkbox',
			),
			'show_on_store_top_rated_products'    => array(
				'name'       => 'show_on_store_top_rated_products',
				'child_name' => 'top_rated_products',
				'label'      => __( 'Top rated products', 'superstore' ),
				'type'       => 'checkbox',
			),
			'show_on_store_featured_products'     => array(
				'name'       => 'show_on_store_featured_products',
				'child_name' => 'featured_products',
				'label'      => __( 'Featured products', 'superstore' ),
				'type'       => 'checkbox',
			),
		);

		$items = array();

		foreach ( $all_items as $key => $item ) {
			$new_key = substr( $key, 14 );
			if ( 'no' === $settings_capabilities[ $new_key ] && ! current_user_can( 'manage_woocommerce' ) ) {
				continue;
			}
			$items[ $key ] = $item;
		}

		$data = array(
			'title'  => __( 'Show on store', 'superstore' ),
			'fields' => array(
				'show_on_store' => array(
					'name'  => 'show_on_store',
					'title' => __( 'Show on store', 'superstore' ),
					'type'  => 'multiple',
					'items' => $items,
				),
			),
		);

		if ( 0 !== count( $items ) ) {
			return apply_filters( 'superstore_seller_dashboard_localize_account_settings_show_on_store_section', $data );
		}

		return $items;
	}

	/**
	 * Payment settings form sections
	 *
	 * @return array
	 */
	public function get_payment_settings_form_sections() {
		$sections = array(
			array(
				'title'  => __( 'Methods', 'superstore' ),
				'fields' => array(),
			),
		);

		$allowed = superstore_get_option(
			'allowed_payment_methods',
			'superstore_payment',
			array(
				'paypal' => 'yes',
				'bank'   => 'yes',
				'skrill' => 'yes',
			)
		);

		if ( 'yes' === $allowed['paypal'] ) {
			$sections[0]['fields']['payment_method_paypal_email'] = array(
				'name'      => 'payment_method_paypal_email',
				'slot_name' => 'payment_method_paypal_email',
				'title'     => __( 'Paypal email', 'superstore' ),
				'type'      => 'slot',
			);
		}

		if ( 'yes' === $allowed['skrill'] ) {
			$sections[0]['fields']['payment_method_skrill_email'] = array(
				'name'      => 'payment_method_skrill_email',
				'slot_name' => 'payment_method_skrill_email',
				'title'     => __( 'Skrill email', 'superstore' ),
				'type'      => 'slot',
			);
		}

		if ( 'yes' === $allowed['bank'] ) {
			$sections[0]['fields']['payment_method'] = array(
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
						'child_name' => 'bank_routing_number',
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
			);
		}

		if ( 0 === count( $sections[0]['fields'] ) ) {
			$sections[0]['title'] = __( 'No payment methods are available', 'superstore' );
		}
		return apply_filters( 'superstore_seller_dashboard_localize_payment_settings_form_sections', $sections );
	}
}
