<?php

namespace Binarithm\Superstore\Localize\SellerDashboard;

/**
 * Superstore seller dashboard localize payment class
 */
class Payment {

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
		$data['payment'] = array(
			'tab' => array(
				'active_tab' => '/payment',
				'tabs'       => $this->tabs(),
				'body'       => $this->tabs_body(),
			),
		);

		return apply_filters( 'superstore_seller_dashboard_localize_payment_data', $data );
	}

	/**
	 * Add tabs
	 *
	 * @return array
	 */
	public function tabs() {
		$tabs = array(
			'payments'         => array(
				'title' => __( 'Payments', 'superstore' ),
				'route' => '/payment',
			),
			'send_new_request' => array(
				'title' => __( 'Send new request', 'superstore' ),
				'route' => '/payment/send-new-request',
			),
		);

		return apply_filters( 'superstore_seller_dashboard_localize_payment_tabs', $tabs );
	}

	/**
	 * Tabs body data
	 *
	 * @return array
	 */
	public function tabs_body() {
		$active_methods = superstore_get_seller_active_payment_methods( get_current_user_id() );
		$data           = array(
			'payments'         => array(
				'table' => array(
					'filterItems'  => $this->table_filters(),
					'links'        => $this->table_links(),
					'actions'      => $this->table_actions(),
					'headers'      => $this->table_headers(),
					'restEndpoint' => '/payments/',
				),
			),
			'send_new_request' => array(
				'form'             => array(
					'fields'      => superstore_get_form_field_values_from_sections( $this->get_send_new_request_form_sections() ),
					'sections'    => $this->get_send_new_request_form_sections(),
					'submitEvent' => 'payment/sendNewRequest',
				),
				'no_active_method' => array(
					'found'      => ! empty( $active_methods ) ? true : false,
					'alert_text' => __( 'You do not have any active payment methods. To send request, setup methods in payment settings. Reload page if not disappearing alert or showing method options after settings.', 'superstore' ),
					'btn_text'   => __( 'Payment settings', 'superstore' ),
				),
				'notify'           => array(
					'request_sent' => __( 'Request has been sent successfully', 'superstore' ),
				),
			),
		);

		return apply_filters( 'superstore_seller_dashboard_localize_payment_tabs_body', $data );
	}

	/**
	 * Table top left links (Generally used to filter and count the table rows)
	 *
	 * @return array
	 */
	public function table_filters() {
		$filters = array(
			array(
				'title' => __( 'All', 'superstore' ),
				'name'  => 'all',
				'value' => null,
			),
			array(
				'title' => __( 'Pending', 'superstore' ),
				'name'  => 'pending',
				'value' => 'yes',
			),
			array(
				'title' => __( 'Approved', 'superstore' ),
				'name'  => 'approved',
				'value' => 'yes',
			),
			array(
				'title' => __( 'Cancelled', 'superstore' ),
				'name'  => 'cancelled',
				'value' => 'yes',
			),
		);

		return apply_filters( 'superstore_seller_dashboard_localize_payment_table_filters', $filters );
	}

	/**
	 * Table top right links
	 *
	 * @return array
	 */
	public function table_links() {
		$current_balance = superstore()->payment->get_total_balance( array( 'user_id' => get_current_user_id() ) );
		$minimum         = superstore_get_option( 'minimum_withdraw_amount', 'superstore_payment' );
		$maximum         = superstore_get_option( 'maximum_withdraw_amount', 'superstore_payment' );
		$links           = array(
			array(
				// translators: %s: Balance.
				'title' => sprintf( esc_html__( 'Balance: %s', 'superstore' ), wc_price( $current_balance ) ),
			),
			array(
				// translators: %s: Minimum limit.
				'title' => sprintf( esc_html__( 'Per withdraw minimum: %s', 'superstore' ), wc_price( $minimum ) ),
			),
			array(
				// translators: %s: Maximum limit.
				'title' => sprintf( esc_html__( 'Per withdraw maximum: %s', 'superstore' ), wc_price( $maximum ) ),
			),
			array(
				'title' => __( 'Payment settings', 'superstore' ),
				'to'    => '/settings/payment',
			),
		);

		return apply_filters( 'superstore_seller_dashboard_localize_payment_table_links', $links );
	}

	/**
	 * Table actions with js namespace methods
	 *
	 * @return array
	 */
	public function table_actions() {
		$data = array(
			array(
				'title'  => __( 'Cancel', 'superstore' ),
				'name'   => 'status',
				'value'  => 'cancelled',
				'method' => 'payment/edit',
			),
		);

		return apply_filters( 'superstore_seller_dashboard_localize_payment_table_actions', $data );
	}

	/**
	 * Table headers
	 *
	 * @return array
	 */
	public function table_headers() {
		$headers = array(
			array(
				'text'     => 'Amount',
				'sortable' => false,
				'value'    => 'amount',
				'class'    => 'font-weight-bold',
			),
			array(
				'text'  => 'Type',
				'value' => 'type',
				'class' => 'font-weight-bold',
			),
			array(
				'text'  => 'Method',
				'value' => 'method',
				'class' => 'font-weight-bold',
			),
			array(
				'text'  => 'Note',
				'value' => 'note',
				'class' => 'font-weight-bold',
			),
			array(
				'text'  => 'Date Created',
				'value' => 'date_created',
				'class' => 'font-weight-bold',
			),
			array(
				'text'  => 'Status',
				'value' => 'status',
				'class' => 'font-weight-bold',
			),
			array(
				'text'  => 'Actions',
				'value' => 'action',
				'class' => 'font-weight-bold',
			),
		);

		return apply_filters( 'superstore_seller_dashboard_localize_payment_table_headers', $headers );
	}

	/**
	 * Send new payment request form sections
	 *
	 * @return array
	 */
	public function get_send_new_request_form_sections() {

		$active_methods = superstore_get_seller_active_payment_methods( get_current_user_id() );

		$sections = array(
			array(
				'title'  => __( 'Request info', 'superstore' ),
				'fields' => array(
					'amount' => array(
						'name'     => 'amount',
						'title'    => __( 'Amount', 'superstore' ),
						'required' => 'yes',
						'type'     => 'number',
					),
					'method' => array(
						'name'     => 'method',
						'title'    => __( 'Method', 'superstore' ),
						'required' => 'yes',
						'type'     => 'select',
						'items'    => $active_methods,
					),
				),
			),
		);
		return apply_filters( 'superstore_seller_dashboard_localize_send_new_payment_request_sections', $sections );
	}
}
