<?php

namespace Binarithm\Superstore\Seller;

use Binarithm\Superstore\DataStore\Controller as DataStore;

/**
 * Superstore seller controller class
 */
class Controller {

	/**
	 * Create, read, update, or delete (crud) a single seller
	 *
	 * @param bool|object $id Seller id.
	 * @return object
	 */
	public function crud_seller( $id = 0 ) {
		return new Seller( $id );
	}

	/**
	 * Read multiple sellers
	 *
	 * @param array $args Filter sellers.
	 * @return array
	 */
	public function get_sellers( $args = array() ) {
		$args    = apply_filters( 'superstore_seller_query_args', $args );
		$results = DataStore::load( 'seller' )->query( $args );
		return apply_filters( 'superstore_sellers', $results, $args );
	}

	/**
	 * Get total sellers count.
	 *
	 * @param array $args Args.
	 * @return int
	 */
	public function get_total( $args = array() ) {
		$defaults = array( 'number' => -1 );
		$args     = wp_parse_args( $args, $defaults );
		$count    = count( $this->get_sellers( $args ) );
		return $count;
	}
}
