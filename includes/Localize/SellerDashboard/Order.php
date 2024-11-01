<?php

namespace Binarithm\Superstore\Localize\SellerDashboard;

/**
 * Superstore seller dashboard localize order class
 */
class Order {

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
		$data['order'] = array(
			'tab' => array(
				'active_tab' => '/order',
				'tabs'       => $this->tabs(),
				'body'       => $this->tabs_body(),
			),
		);

		return apply_filters( 'superstore_seller_dashboard_localize_order_data', $data );
	}

	/**
	 * Add tabs
	 *
	 * @return array
	 */
	public function tabs() {
		$tabs = array(
			'orders' => array(
				'title' => __( 'Orders', 'superstore' ),
				'route' => '/order',
			),
		);

		return apply_filters( 'superstore_seller_dashboard_localize_order_tabs', $tabs );
	}

	/**
	 * Tabs body data
	 *
	 * @return array
	 */
	public function tabs_body() {
		$data = array(
			'orders' => array(
				'table'  => array(
					'filterItems'  => $this->table_filters(),
					'links'        => $this->table_links(),
					'actions'      => $this->table_actions(),
					'headers'      => $this->table_headers(),
					'restEndpoint' => '/orders/',
				),
				'notify' => $this->get_order_notifications(),
				'single' => $this->get_single_order_data(),
			),
		);

		return apply_filters( 'superstore_seller_dashboard_localize_order_tabs_body', $data );
	}

	/**
	 * Add orders notifications
	 *
	 * @return array
	 */
	public function get_order_notifications() {
		$notifications = array(
			'csv_exported' => __( 'Successfully CSV exported', 'superstore' ),
		);
		return apply_filters( 'superstore_seller_dashboard_localize_order_notifications', $notifications );
	}

	/**
	 * Table top left links (Generally used to filter and count the table rows)
	 *
	 * @return array
	 */
	public function table_filters() {
		// phpcs:ignore WordPress.DateTime.CurrentTimeTimestamp.Requested, WordPress.DateTime.RestrictedFunctions.date_date
		$start_date = date( 'Y-m-d', strtotime( date( 'Ym', current_time( 'timestamp' ) ) . '01' ) );
		// phpcs:ignore WordPress.DateTime.CurrentTimeTimestamp.Requested, WordPress.DateTime.RestrictedFunctions.date_date
		$end_date          = date( 'Y-m-d', current_time( 'timestamp' ) );
		$current_month_arg = $start_date . '...' . $end_date;
		$filters           = array(
			array(
				'title' => __( 'All', 'superstore' ),
				'name'  => 'all',
				'value' => null,
			),
			array(
				'title' => __( 'Processing', 'superstore' ),
				'name'  => 'wc-processing',
				'value' => 'yes',
			),
			array(
				'title' => __( 'On hold', 'superstore' ),
				'name'  => 'wc-on-hold',
				'value' => 'yes',
			),
			array(
				'title' => __( 'Refunded', 'superstore' ),
				'name'  => 'wc-refunded',
				'value' => 'yes',
			),
			array(
				'title' => __( 'Pending', 'superstore' ),
				'name'  => 'wc-pending',
				'value' => 'yes',
			),
			array(
				'title' => __( 'Completed', 'superstore' ),
				'name'  => 'wc-completed',
				'value' => 'yes',
			),
			array(
				'title' => __( 'Cancelled', 'superstore' ),
				'name'  => 'wc-cancelled',
				'value' => 'yes',
			),
			array(
				'title' => __( 'Current month orders', 'superstore' ),
				'name'  => 'current_month',
				'value' => $current_month_arg,
			),
		);

		return apply_filters( 'superstore_seller_dashboard_localize_order_table_filters', $filters );
	}

	/**
	 * Table top right links
	 *
	 * @return array
	 */
	public function table_links() {
		$links = array(
			array(
				'title' => __( 'Settings', 'superstore' ),
				'to'    => '/settings',
			),
		);

		return apply_filters( 'superstore_seller_dashboard_localize_order_table_links', $links );
	}

	/**
	 * Table actions with js namespace methods
	 *
	 * @return array
	 */
	public function table_actions() {
		$statuses = wc_get_order_statuses();
		$items    = array();
		if ( 'yes' === superstore_get_option( 'seller_can_change_order_status', 'superstore_seller', 'yes' ) ) {
			foreach ( $statuses as $status => $title ) {
				$items[] = array(
					/* translators: %s: Status title */
					'title'       => sprintf( __( 'Change status to %s', 'superstore' ), $title ),
					'name'        => 'status',
					'value'       => $status,
					'method'      => 'order/edit',
					'skip_in_row' => 'wc-completed' === $status ? 'no' : 'yes',
				);
			}
		}

		$items[] = array(
			'title'  => __( 'Export CSV', 'superstore' ),
			'name'   => 'expostcsv',
			'method' => 'order/exportCSV',
		);

		return apply_filters( 'superstore_seller_dashboard_localize_order_table_actions', $items );
	}

	/**
	 * Table headers
	 *
	 * @return array
	 */
	public function table_headers() {
		$headers = array(
			array(
				'text'     => 'Order',
				'sortable' => false,
				'value'    => 'order',
				'class'    => 'font-weight-bold',
			),
			array(
				'text'  => 'Total',
				'value' => 'total',
				'class' => 'font-weight-bold',
			),
			array(
				'text'  => 'My earnings',
				'value' => 'earnings',
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

		return apply_filters( 'superstore_seller_dashboard_localize_order_table_headers', $headers );
	}

	/**
	 * Single order data
	 *
	 * @return array
	 */
	public function get_single_order_data() {
		$data = array(
			'order_not_found' => __( 'Order not found' ),
			'form'            => array(
				'fields'      => superstore_get_form_field_values_from_sections( $this->get_single_order_form_sections() ),
				'sections'    => $this->get_single_order_form_sections(),
				'submitEvent' => 'order/singleEdit',
			),
		);

		return apply_filters( 'superstore_seller_dashboard_localize_single_order_data', $data );
	}

	/**
	 * Single order form sections
	 *
	 * @return array
	 */
	public function get_single_order_form_sections() {
		$order_statuses = wc_get_order_statuses();
		$status_options = array();

		foreach ( $order_statuses as $value => $name ) {
			$status_options[] = array(
				'name'  => $name,
				'value' => substr( $value, 3 ),
			);
		}

		$can_change_status = superstore_get_option( 'seller_can_change_order_status', 'superstore_seller', 'yes' );

		$sections = array(
			array(
				'title'  => __( 'General', 'superstore' ),
				'fields' => array(
					'status'               => array(
						'name'     => 'status',
						'title'    => __( 'Status', 'superstore' ),
						'type'     => 'select',
						'disabled' => 'yes' === $can_change_status ? 'no' : 'yes',
						'items'    => $status_options,
					),
					'products'             => array(
						'name'      => 'products',
						'title'     => __( 'Products', 'superstore' ),
						'type'      => 'slot',
						'slot_name' => 'products',
						'items'     => array(
							'line_items' => array(
								'name'             => 'line_items',
								'type'             => 'read_only',
								'table_name_text'  => __( 'Name', 'superstore' ),
								'table_qty_text'   => __( 'Qty', 'superstore' ),
								'table_total_text' => __( 'Total', 'superstore' ),
							),
						),
					),
					'total'                => array(
						'name'  => 'total',
						'title' => __( 'total', 'superstore' ),
						'type'  => 'read_only',
					),
					'earnings'             => array(
						'name'  => 'earnings',
						'title' => __( 'My earnings', 'superstore' ),
						'type'  => 'read_only',
					),
					'payment_method_title' => array(
						'name'  => 'payment_method_title',
						'title' => __( 'Payment method', 'superstore' ),
						'type'  => 'read_only',
					),
					'date_created'         => array(
						'name'      => 'date_created',
						'title'     => __( 'Order date', 'superstore' ),
						'type'      => 'slot',
						'slot_name' => 'date_created',
					),
					'customer_note'        => array(
						'name'  => 'customer_note',
						'title' => __( 'Customer provided note', 'superstore' ),
						'type'  => 'read_only',
					),
				),
			),
			array(
				'title'  => __( 'Downloadable product permission', 'superstore' ),
				'fields' => array(
					'downloads' => array(
						'name'      => 'downloads',
						'title'     => __( 'Downloadable items', 'superstore' ),
						'type'      => 'slot',
						'slot_name' => 'downloads',
						'items'     => array(
							'downloadable_items' => array(
								'name'                 => 'downloadable_items',
								'revoke_text'          => __( 'Revoke access', 'superstore' ),
								'choose_text'          => __( 'Choose downloadable products', 'superstore' ),
								'grant_text'           => __( 'Grant new access', 'superstore' ),
								'table_name_text'      => __( 'Product name', 'superstore' ),
								'table_download_text'  => __( 'Total downloaded', 'superstore' ),
								'table_remaining_text' => __( 'Downloads remaining', 'superstore' ),
								'table_expires_text'   => __( 'Access expires', 'superstore' ),
								'table_action_text'    => __( 'Action', 'superstore' ),
							),
						),
					),
				),
			),
			array(
				'title'  => __( 'Customer details', 'superstore' ),
				'fields' => array(
					'customer_details' => array(
						'name'      => 'customer_details',
						'type'      => 'slot_only',
						'slot_name' => 'customer_details',
						'items'     => array(
							'name'  => array(
								'name'  => 'name',
								'title' => __( 'Name', 'superstore' ),
							),
							'email' => array(
								'name'  => 'email',
								'title' => __( 'Email', 'superstore' ),
							),
							'phone' => array(
								'name'  => 'customer_phone',
								'title' => __( 'Phone', 'superstore' ),
							),
							'ip'    => array(
								'name'  => 'ip',
								'title' => __( 'IP address', 'superstore' ),
							),
						),
					),
				),
			),
			array(
				'title'  => __( 'Order notes', 'superstore' ),
				'fields' => array(
					'notes' => array(
						'name'          => 'notes',
						'type'          => 'slot_only',
						'slot_name'     => 'notes',
						'delete_text'   => __( 'Delete note', 'superstore' ),
						'add_text'      => __( 'Add note', 'superstore' ),
						'by_text'       => __( 'by', 'superstore' ),
						'ago_text'      => __( 'ago', 'superstore' ),
						'placeholder'   => __( 'Type here...', 'superstore' ),
						'customer_note' => array(
							array(
								'name'  => __( 'Private note', 'superstore' ),
								'value' => 'private',
							),
							array(
								'name'  => __( 'Note to customer', 'superstore' ),
								'value' => 'customer',
							),
						),
					),
				),
			),
			array(
				'title'  => __( 'Billing & shipping details', 'superstore' ),
				'fields' => array(
					'billing_shipping' => array(
						'name'      => 'billing_shipping',
						'type'      => 'slot_only',
						'slot_name' => 'billing_shipping',
						'billing'   => array(
							'name'  => 'billing',
							'title' => __( 'Billing address', 'superstore' ),
							'items' => array(
								'country' => array(
									'title' => __( 'Country', 'superstore' ),
								),
							),
						),
						'shipping'  => array(
							'name'  => 'shipping',
							'title' => __( 'Shipping address', 'superstore' ),
							'items' => array(
								'country' => array(
									'title' => __( 'Country', 'superstore' ),
								),
							),
						),
					),
				),
			),
		);
		return apply_filters( 'superstore_seller_dashboard_localize_single_order_form_sections', $sections );
	}
}
