<?php

namespace Binarithm\Superstore\Localize;

/**
 * Superstore frontend stores localize data conroller class
 */
class Stores {

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
		$data['stores'] = array(
			'loading'        => __( 'Loading stores...', 'superstore' ),
			'featured'       => __( 'Featured', 'superstore' ),
			'total'          => superstore()->seller->get_total(),
			'total_showing'  => __( 'Total stores showing', 'superstore' ),
			'visit'          => __( 'Visit store', 'superstore' ),
			'search_by_text' => __( 'Search by store name...', 'superstore' ),
		);
		$data['store']  = array(
			'items'               => array(
				array(
					'title' => 'Best selling Products',
					'key'   => 'best-selling',
				),
				array(
					'title' => 'Latest Products',
					'key'   => 'latest',
				),
				array(
					'title' => 'Fetured Products',
					'key'   => 'featured',
				),
				array(
					'title' => 'Top rated Products',
					'key'   => 'top-rated',
				),
			),
			'featured'            => __( 'Featured', 'superstore' ),
			'valid_required_text' => __( 'Required', 'superstore' ),
			'valid_email_text'    => __( 'Email is not valid', 'superstore' ),
			'send_msg'            => __( 'Send', 'superstore' ),
			'day'                 => array(
				'sun' => __( 'Sunday', 'superstore' ),
				'mon' => __( 'Monday', 'superstore' ),
				'tue' => __( 'Tuesday', 'superstore' ),
				'wed' => __( 'Wednesday', 'superstore' ),
				'thu' => __( 'Thursday', 'superstore' ),
				'fri' => __( 'Friday', 'superstore' ),
				'sat' => __( 'Saturday', 'superstore' ),
			),
			'open_schedule'       => __( 'Store open close schedule', 'superstore' ),
			'loading'             => __( 'Store is loading...', 'superstore' ),
			'not_found'           => __( 'Store does not found', 'superstore' ),
			'contact'             => __( 'Send Message', 'superstore' ),
			'contact_name'        => __( 'Your name', 'superstore' ),
			'contact_email'       => __( 'Your email', 'superstore' ),
			'contact_message'     => __( 'Message', 'superstore' ),
			'opening_hours'       => __( 'Opening hours', 'superstore' ),
			'contact_details'     => __( 'Contact details', 'superstore' ),
			'about'               => __( 'About store', 'superstore' ),
			'sale'                => __( 'Sale!', 'superstore' ),
		);

		return apply_filters( 'superstore_stores_list_localize_data', $data );
	}
}
