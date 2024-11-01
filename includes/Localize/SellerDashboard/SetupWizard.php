<?php

namespace Binarithm\Superstore\Localize\SellerDashboard;

/**
 * Superstore seller dashboard setup wizard localize data class
 */
class SetupWizard {

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
		$value                = get_user_meta( get_current_user_id(), 'superstore_seller_setup_wizard_run', true ) ? get_user_meta( get_current_user_id(), 'superstore_seller_setup_wizard_run', true ) : 'yes';
		$data['setup_wizard'] = array(
			'run'               => $value,
			'entry'             => array(
				'wish'      => __( 'Wishing a quick start?', 'superstore' ),
				'go'        => __( 'Let\'s go', 'superstore' ),
				'dashboard' => __( 'No, Back to superstore dashboard', 'superstore' ),
				'thanks'    => __( 'Thank you for joining us.', 'superstore' ),
			),
			'continue'          => __( 'Continue', 'superstore' ),
			'skip'              => __( 'Skip step', 'superstore' ),
			'headers_name'      => array(
				'account' => __( 'Account', 'superstore' ),
				'payment' => __( 'Payment', 'superstore' ),
				'finish'  => __( 'Finish', 'superstore' ),
			),
			'finish'            => array(
				'excellent'     => __( 'Excellent!!', 'superstore' ),
				'ready'         => __( 'Your store is ready to launch.', 'superstore' ),
				'goto'          => __( 'Go to', 'superstore' ),
				'ssdb'          => __( 'Dashboard', 'superstore' ),
				'more_settings' => __( 'More settings', 'superstore' ),
			),
			'form_step_account' => array(
				'banner_id'          => array(
					'name'  => 'banner_id',
					'title' => __( 'Store banner', 'superstore' ),
					'type'  => 'file',
					'value' => false,
				),
				'profile_picture_id' => array(
					'name'  => 'profile_picture_id',
					'title' => __( 'Profile picture', 'superstore' ),
					'type'  => 'file',
					'value' => false,
				),
				'store_name'         => array(
					'name'  => 'store_name',
					'title' => __( 'Store name', 'superstore' ),
					'type'  => 'text',
					'value' => get_user_meta( get_current_user_id(), 'superstore_store_name', true ) ? get_user_meta( get_current_user_id(), 'superstore_store_name', true ) : '',
				),
				'address'            => array(
					'name'  => 'address',
					'title' => __( 'Address', 'superstore' ),
					'type'  => 'multiple',
					'items' => array(
						'address_country'  => array(
							'name'  => 'address_country',
							'label' => __( 'Select country', 'superstore' ),
							'type'  => 'select',
							'items' => array(),
						),
						'address_state'    => array(
							'name'  => 'address_state',
							'label' => __( 'Enter state', 'superstore' ),
							'type'  => 'text',
						),
						'address_city'     => array(
							'name'  => 'address_city',
							'label' => __( 'Enter town or city', 'superstore' ),
							'type'  => 'text',
						),
						'address_street_1' => array(
							'name'  => 'address_street_1',
							'label' => __( 'Enter street 1', 'superstore' ),
							'type'  => 'text',
						),
						'address_street_2' => array(
							'name'  => 'address_street_2',
							'label' => __( 'Enter street 2', 'superstore' ),
							'type'  => 'text',
						),
						'address_postcode' => array(
							'name'  => 'address_postcode',
							'label' => __( 'Enter post code', 'superstore' ),
							'type'  => 'text',
						),
					),
				),
			),
			'form_step_payment' => array(
				'payment_method_paypal_email' => array(
					'name'  => 'payment_method_paypal_email',
					'title' => __( 'Paypal email', 'superstore' ),
					'hint'  => __( 'Paypal account open with' ),
					'type'  => 'email',
					'value' => null,
				),
				'payment_method_skrill_email' => array(
					'name'  => 'payment_method_skrill_email',
					'title' => __( 'Skrill email', 'superstore' ),
					'hint'  => __( 'Skrill account open with' ),
					'type'  => 'email',
					'value' => null,
				),
				'bank_details'                => array(
					'name'  => 'bank_details',
					'title' => __( 'Bank details', 'superstore' ),
					'type'  => 'multiple',
					'items' => array(
						'payment_method_bank_ac_name'   => array(
							'name'  => 'payment_method_bank_ac_name',
							'label' => __( 'Account name', 'superstore' ),
							'type'  => 'text',
						),
						'payment_method_bank_ac_number' => array(
							'name'  => 'payment_method_bank_ac_number',
							'label' => __( 'Account number', 'superstore' ),
							'type'  => 'text',
						),
						'payment_method_bank_name'      => array(
							'name'  => 'payment_method_bank_name',
							'label' => __( 'Bank name', 'superstore' ),
							'type'  => 'text',
						),
						'payment_method_bank_address'   => array(
							'name'  => 'payment_method_bank_address',
							'label' => __( 'Bank address', 'superstore' ),
							'type'  => 'text',
						),
						'payment_method_bank_routing_number' => array(
							'name'  => 'payment_method_bank_routing_number',
							'label' => __( 'Routing number', 'superstore' ),
							'type'  => 'text',
						),
						'payment_method_bank_iban'      => array(
							'name'  => 'payment_method_bank_iban',
							'label' => __( 'IBAN', 'superstore' ),
							'type'  => 'text',
						),
						'payment_method_bank_swift'     => array(
							'name'  => 'payment_method_bank_swift',
							'label' => __( 'Swift', 'superstore' ),
							'type'  => 'text',
						),
					),
				),
			),
		);

		return apply_filters( 'superstore_seller_dashboard_setup_wizard_localize_data', $data );
	}
}
