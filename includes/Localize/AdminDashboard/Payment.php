<?php

namespace Binarithm\Superstore\Localize\AdminDashboard;

/**
 * Superstore admin dashboard localize payment class
 */
class Payment {

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
		$data['payment'] = array(
			'tab' => array(
				'active' => '/payment',
				'tabs'   => $this->tabs(),
				'body'   => $this->tabs_body(),
			),
		);

		return apply_filters( 'superstore_admin_dashboard_localize_payment_tab', $data );
	}

	/**
	 * Add tabs
	 *
	 * @return array
	 */
	public function tabs() {
		$tabs = array(
			'requests' => array(
				'title' => __( 'Payments', 'superstore' ),
				'route' => '/payment',
			),
		);

		return apply_filters( 'superstore_admin_dashboard_localize_payment_tabs', $tabs );
	}

	/**
	 * Tabs body data
	 *
	 * @return array
	 */
	public function tabs_body() {
		$data = array(
			'requests' => array(
				'table' => array(
					'filterItems'  => $this->table_filters(),
					'links'        => $this->table_links(),
					'actions'      => $this->table_actions(),
					'headers'      => $this->table_headers(),
					'restEndpoint' => '/payments/admin/',
				),
			),
		);

		return apply_filters( 'superstore_admin_dashboard_localize_payment_tabs_body', $data );
	}

	/**
	 * Table filters
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

		return apply_filters( 'superstore_admin_dashboard_localize_payment_table_filters', $filters );
	}

	/**
	 * Table top right links
	 *
	 * @return array
	 */
	public function table_links() {
		$data = array(
			array(
				'title' => __( 'Payment settings', 'superstore' ),
				'to'    => '/settings/payment',
			),
		);

		return apply_filters( 'superstore_admin_dashboard_localize_payment_table_links', $data );
	}

	/**
	 * Table actions with js namespace methods
	 *
	 * @return array
	 */
	public function table_actions() {
		$data = array(
			array(
				'title'  => __( 'Approve', 'superstore' ),
				'name'   => 'status',
				'value'  => 'approved',
				'method' => 'payment/edit',
			),
			array(
				'title'       => __( 'Cancel', 'superstore' ),
				'name'        => 'status',
				'value'       => 'cancelled',
				'method'      => 'payment/edit',
				'skip_in_row' => 'yes',
			),
			array(
				'title'       => __( 'Delete', 'superstore' ),
				'name'        => 'delete',
				'method'      => 'payment/delete',
				'skip_in_row' => 'yes',
			),
			array(
				'title'  => __( 'Pending', 'superstore' ),
				'name'   => 'status',
				'value'  => 'pending',
				'method' => 'payment/edit',
			),
		);

		return apply_filters( 'superstore_admin_dashboard_localize_payment_table_actions', $data );
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
				'value'    => 'seller',
				'align'    => 'start',
				'sortable' => false,
				'class'    => 'font-weight-bold',
			),
			array(
				'text'     => __( 'Amount', 'superstore' ),
				'sortable' => false,
				'value'    => 'amount',
				'class'    => 'font-weight-bold',
			),
			array(
				'text'  => __( 'Method', 'superstore' ),
				'value' => 'method',
				'class' => 'font-weight-bold',
			),
			array(
				'text'  => __( 'Note', 'superstore' ),
				'value' => 'note',
				'class' => 'font-weight-bold',
			),
			array(
				'text'  => __( 'Requested Date', 'superstore' ),
				'value' => 'date_created',
				'class' => 'font-weight-bold',
			),
			array(
				'text'  => 'Type',
				'value' => 'type',
				'class' => 'font-weight-bold',
			),
			array(
				'text'  => __( 'Status', 'superstore' ),
				'value' => 'status',
				'class' => 'font-weight-bold',
			),
			array(
				'text'  => __( 'Action', 'superstore' ),
				'value' => 'action',
				'class' => 'font-weight-bold',
			),
		);

		return apply_filters( 'superstore_admin_dashboard_localize_payment_table_headers', $headers );
	}
}
