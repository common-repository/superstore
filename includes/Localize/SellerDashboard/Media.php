<?php

namespace Binarithm\Superstore\Localize\SellerDashboard;

/**
 * Superstore seller dashboard localize media class
 */
class Media {

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
		$data['media'] = array(
			'tab' => array(
				'active_tab' => '/media',
				'tabs'       => $this->tabs(),
				'body'       => $this->tabs_body(),
			),
		);

		return apply_filters( 'superstore_seller_dashboard_localize_media_data', $data );
	}

	/**
	 * Add tabs
	 *
	 * @return array
	 */
	public function tabs() {
		$tabs = array(
			'files'           => array(
				'title' => __( 'Files', 'superstore' ),
				'route' => '/media',
			),
			'upload_new_file' => array(
				'title' => __( 'Upload new file', 'superstore' ),
				'route' => '/media/upload-new-file',
			),
		);

		return apply_filters( 'superstore_seller_dashboard_media_localize_tabs', $tabs );
	}

	/**
	 * Tabs body data
	 *
	 * @return array
	 */
	public function tabs_body() {
		$data = array(
			'files' => array(
				'table' => array(
					'filterItems'  => $this->table_filters(),
					'links'        => $this->table_links(),
					'actions'      => $this->table_actions(),
					'headers'      => $this->table_headers(),
					'restEndpoint' => '/media/',
				),
			),
		);

		return apply_filters( 'superstore_seller_dashboard_localize_media_tabs_body', $data );
	}

	/**
	 * Table top left links (Generally used to filter and count the table rows)
	 *
	 * @return array
	 */
	public function table_filters() {
		$links = array(
			array(
				'title' => __( 'All', 'superstore' ),
				'name'  => 'all',
				'value' => null,
			),
		);

		return apply_filters( 'superstore_seller_dashboard_localize_media_table_filters', $links );
	}

	/**
	 * Table top right links
	 *
	 * @return array
	 */
	public function table_links() {
		$seller            = superstore()->seller->crud_seller( get_current_user_id() );
		$total             = (float) $seller->get_storage_limit() * 1000000;
		$storage_occupied  = superstore()->media->get_storage_occupied( array( 'author' => get_current_user_id() ) );
		$storage_available = superstore()->media->get_seller_storage_available( array( 'author' => get_current_user_id() ) );
		$storage_available = -1 === (int) $storage_available ? 'unlimited' : $storage_available;
		$link              = array();

		if ( 'unlimited' !== $storage_available ) {
			$link = array(
				// translators: %1$s: Available, %2$s: Total.
				'title' => sprintf( esc_html__( 'Storage available: %1$s of %2$s', 'superstore' ), size_format( $storage_available ), size_format( $total ) ),
			);
		}

		$links = array(
			$link,
			array(
				// translators: %s: Occupied.
				'title' => sprintf( esc_html__( 'Storage occupied: %s', 'superstore' ), size_format( $storage_occupied ) ),
			),
		);

		return apply_filters( 'superstore_seller_dashboard_localize_media_table_links', $links );
	}

	/**
	 * Table actions with js namespace methods
	 *
	 * @return array
	 */
	public function table_actions() {
		$items = array(
			array(
				'title'  => __( 'Delete', 'superstore' ),
				'name'   => 'delete',
				'method' => 'media/delete',
			),
		);

		return apply_filters( 'superstore_seller_dashboard_localize_media_table_actions', $items );
	}

	/**
	 * Table headers
	 *
	 * @return array
	 */
	public function table_headers() {
		$headers = array(
			array(
				'text'     => 'File',
				'sortable' => false,
				'value'    => 'file',
				'class'    => 'font-weight-bold',
			),
			array(
				'text'  => 'Size',
				'value' => 'size',
				'class' => 'font-weight-bold',
			),
			array(
				'text'       => 'Type',
				'value'      => 'type',
				'class'      => 'font-weight-bold',
				'filterable' => 'yes',
			),
			array(
				'text'        => 'Date Created',
				'value'       => 'date_created',
				'class'       => 'font-weight-bold',
				'filterable'  => 'yes',
				'filter_type' => 'date',
			),
			array(
				'text'  => 'Actions',
				'value' => 'action',
				'class' => 'font-weight-bold',
			),
		);

		return apply_filters( 'superstore_seller_dashboard_localize_media_table_headers', $headers );
	}
}
