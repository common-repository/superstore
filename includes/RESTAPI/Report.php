<?php

namespace Binarithm\Superstore\RESTAPI;

use WP_Error;
use WP_Query;
use WP_REST_Controller;
use WP_REST_Server;
use Binarithm\Superstore\Exceptions\SuperstoreException;

/**
 * Report REST API class
 */
class Report extends WP_REST_Controller {

	/**
	 * Endpoint namespace
	 *
	 * @var string
	 */
	protected $namespace = 'superstore/v1';

	/**
	 * Route name
	 *
	 * @var string
	 */
	protected $base = 'reports';

	/**
	 * Register all routes
	 *
	 * @return void
	 */
	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'/' . $this->base . '/admin-overview',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_admin_overview' ),
					'permission_callback' => array( $this, 'check_admin_permission' ),
				),
			)
		);
		register_rest_route(
			$this->namespace,
			'/' . $this->base . '/top-10-sellers',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_top_10_sellers' ),
					'permission_callback' => array( $this, 'check_admin_permission' ),
				),
			)
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->base . '/seller-overview',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_seller_overview' ),
					'permission_callback' => array( $this, 'check_permission' ),
				),
			)
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->base . '/seller-top-10-products',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_seller_top_10_products' ),
					'permission_callback' => array( $this, 'check_permission' ),
				),
			)
		);
	}

	/**
	 * Check user is admin
	 *
	 * @return bool
	 */
	public function check_admin_permission() {
		return current_user_can( 'manage_woocommerce' );
	}

	/**
	 * Check user is seller and managing only own data
	 *
	 * @param obj|array $request Request.
	 * @return bool
	 */
	public function check_permission( $request ) {
		if ( current_user_can( 'manage_woocommerce' ) ) {
			return true;
		}

		if ( superstore_is_user_seller( get_current_user_id() ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Get items
	 *
	 * @param mixed $request Request.
	 * @return array
	 * @throws SuperstoreException On error.
	 */
	public function get_admin_overview( $request ) {
		try {
			return rest_ensure_response( superstore()->report->get_admin_overview() );
		} catch ( SuperstoreException $e ) {
			return new WP_Error( $e->getCode(), $e->getMessage() );
		}
	}

	/**
	 * Get items
	 *
	 * @param mixed $request Request.
	 * @return array
	 * @throws SuperstoreException On error.
	 */
	public function get_top_10_sellers( $request ) {
		try {
			return rest_ensure_response( superstore()->report->get_top_10_sellers() );
		} catch ( SuperstoreException $e ) {
			return new WP_Error( $e->getCode(), $e->getMessage() );
		}
	}

	/**
	 * Get items
	 *
	 * @param mixed $request Request.
	 * @return array
	 * @throws SuperstoreException On error.
	 */
	public function get_seller_overview( $request ) {
		try {
			return rest_ensure_response( superstore()->report->get_seller_overview( get_current_user_id() ) );
		} catch ( SuperstoreException $e ) {
			return new WP_Error( $e->getCode(), $e->getMessage() );
		}
	}

	/**
	 * Get items
	 *
	 * @param mixed $request Request.
	 * @return array
	 * @throws SuperstoreException On error.
	 */
	public function get_seller_top_10_products( $request ) {
		try {
			return rest_ensure_response( superstore()->report->get_seller_top_10_products( get_current_user_id() ) );
		} catch ( SuperstoreException $e ) {
			return new WP_Error( $e->getCode(), $e->getMessage() );
		}
	}
}
