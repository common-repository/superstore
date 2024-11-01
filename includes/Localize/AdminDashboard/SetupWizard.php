<?php

namespace Binarithm\Superstore\Localize\AdminDashboard;

/**
 * Superstore admin dashboard setup wizard localize data class
 */
class SetupWizard {

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
		$data['setup_wizard'] = array(
			'run'               => get_option( 'superstore_admin_setup_wizard_run', 'yes' ),
			'entry'             => array(
				'wish'      => __( 'Wishing a quick start?', 'superstore' ),
				'go'        => __( 'Let\'s go', 'superstore' ),
				'dashboard' => __( 'No, Back to superstore dashboard', 'superstore' ),
				'thanks'    => __( 'Thank you for using superstore.', 'superstore' ),
			),
			'continue'          => __( 'Continue', 'superstore' ),
			'skip'              => __( 'Skip step', 'superstore' ),
			'headers_name'      => array(
				'general' => __( 'General', 'superstore' ),
				'seller'  => __( 'Account & Seller', 'superstore' ),
				'payment' => __( 'Payment', 'superstore' ),
				'finish'  => __( 'Finish', 'superstore' ),
			),
			'finish'            => array(
				'excellent'     => __( 'Excellent!!', 'superstore' ),
				'ready'         => __( 'Your marketplace is ready to launch.', 'superstore' ),
				'goto'          => __( 'Go to', 'superstore' ),
				'ssdb'          => __( 'Superstore dashboard', 'superstore' ),
				'more_settings' => __( 'More settings', 'superstore' ),
				'wpdb'          => __( 'Wordpress dashboard', 'superstore' ),
			),
			'form_step_general' => array(
				'admin_commission_global' => array(
					'name'       => 'admin_commission_global',
					'title'      => __( 'Admin commission', 'superstore' ),
					'type'       => 'multiple',
					'value_type' => 'object',
					'items'      => array(
						'rate' => array(
							'child_name' => 'rate',
							'label'      => __( 'Rate', 'superstore' ),
							'hint'       => __( 'Amount you want to profit/charge from each product of a seller', 'superstore' ),
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
					'value'      => array(
						'type' => 'percentage',
						'rate' => 0,
					),
				),
			),
			'form_step_seller'  => array(
				'new_seller_auto_enable'         => array(
					'name'  => 'new_seller_auto_enable',
					'title' => __( 'New seller enable by default', 'superstore' ),
					'hint'  => __( 'Newly registered sellers will be enabled by default', 'superstore' ),
					'type'  => 'checkbox ',
					'value' => 'no',
				),
				'seller_can_change_order_status' => array(
					'name'  => 'seller_can_change_order_status',
					'title' => __( 'Seller can change order status', 'superstore' ),
					'type'  => 'checkbox',
					'value' => 'yes',
				),
				'new_seller_auto_requires_product_publishing_review' => array(
					'name'  => 'new_seller_auto_requires_product_publishing_review',
					'title' => __( 'Seller new product requires admin review', 'superstore' ),
					'hint'  => __( 'Admin will review newly added products before live.', 'superstore' ),
					'type'  => 'checkbox',
					'value' => 'yes',
				),
			),
			'form_step_payment' => array(
				'allowed_payment_methods' => array(
					'name'  => 'allowed_payment_methods',
					'title' => __( 'Allowed payment methods', 'superstore' ),
					'type'  => 'multicheck',
					'value' => array(
						'paypal' => 'yes',
						'skrill' => 'yes',
						'bank'   => 'yes',
					),
					'items' => array(
						array(
							'label' => __( 'Paypal', 'superstore' ),
							'value' => 'paypal',
						),
						array(
							'label' => __( 'Skrill', 'superstore' ),
							'value' => 'skrill',
						),
						array(
							'label' => __( 'Bank', 'superstore' ),
							'value' => 'bank',
						),
					),
				),
				'minimum_withdraw_amount' => array(
					'name'  => 'minimum_withdraw_amount',
					'title' => __( 'Minimum withdraw amount', 'superstore' ),
					'hint'  => __( 'Minimum required amount for withdraw request . Keep blank for not limit . ', 'superstore' ),
					'type'  => 'number',
					'value' => null,
				),
				'maximum_withdraw_amount' => array(
					'name'  => 'maximum_withdraw_amount',
					'title' => __( 'Maximum withdraw amount', 'superstore' ),
					'hint'  => __( 'Maximum amount for withdraw request . Keep blank for not limit . ', 'superstore' ),
					'type'  => 'number',
					'value' => null,
				),
				'exclude_cod_payment'     => array(
					'name'  => 'exclude_cod_payment',
					'title' => __( 'Exclude COD payments', 'superstore' ),
					'hint'  => __( 'Exclude customer COD paid amount from seller balance . ', 'superstore' ),
					'type'  => 'checkbox',
					'value' => 'no',
				),
			),
		);

		return apply_filters( 'superstore_admin_dashboard_setup_wizard_localize_data', $data );
	}
}
