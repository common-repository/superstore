<?php

namespace Binarithm\Superstore\Localize\SellerDashboard;

/**
 * Superstore seller dashboard localize home class
 */
class Home {

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
		$data['home'] = array(
			'overview'        => array(
				'title' => __( 'Your store at a glance', 'superstore' ),
			),
			'top_10_products' => array(
				'title'        => __( 'Top 10 products by items sold', 'superstore' ),
				'table_header' => array(
					'product'    => __( 'Product', 'superstore' ),
					'items_sold' => __( 'Items sold', 'superstore' ),
					'ratings'    => __( 'Avarage ratings', 'superstore' ),
				),
			),
		);

		return apply_filters( 'superstore_seller_dashboard_localize_home_data', $data );
	}
}
