<?php

namespace Binarithm\Superstore\Localize\AdminDashboard;

/**
 * Superstore admin dashboard localize home class
 */
class Home {

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
		$data['home'] = array(
			'overview'       => array(
				'title' => __( 'Your marketplace at a glance', 'superstore' ),
			),
			'top_10_sellers' => array(
				'title'        => __( 'Top 10 sellers by admin earnings', 'superstore' ),
				'table_header' => array(
					'seller'         => __( 'Seller', 'superstore' ),
					'admin_earnings' => __( 'Total admin earnings', 'superstore' ),
					'sales'          => __( 'Total sales', 'superstore' ),
					'ratings'        => __( 'Avarage ratings', 'superstore' ),
				),
			),
		);

		return apply_filters( 'superstore_admin_dashboard_localize_home_data', $data );
	}
}
