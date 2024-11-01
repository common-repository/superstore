<?php

namespace Binarithm\Superstore\RESTAPI;

/**
 * Superstore rest api controller class
 */
class Controller {

	/**
	 * Load classes dynamically
	 *
	 * @var $class_map
	 */
	protected $class_map;

	/**
	 * Class contructor
	 *
	 * @return void
	 */
	public function __construct() {
		if ( ! class_exists( 'WP_REST_Server' ) ) {
			return;
		}

		$this->class_map = apply_filters(
			'superstore_rest_api_class_map',
			array(
				SUPERSTORE_INCLUDE_DIR . '/RESTAPI/Seller.php'  => '\Binarithm\Superstore\RESTAPI\Seller',
				SUPERSTORE_INCLUDE_DIR . '/RESTAPI/File.php'  => '\Binarithm\Superstore\RESTAPI\File',
				SUPERSTORE_INCLUDE_DIR . '/RESTAPI/Product.php'  => '\Binarithm\Superstore\RESTAPI\Product',
				SUPERSTORE_INCLUDE_DIR . '/RESTAPI/Order.php'  => '\Binarithm\Superstore\RESTAPI\Order',
				SUPERSTORE_INCLUDE_DIR . '/RESTAPI/Payment.php'  => '\Binarithm\Superstore\RESTAPI\Payment',
				SUPERSTORE_INCLUDE_DIR . '/RESTAPI/Report.php'  => '\Binarithm\Superstore\RESTAPI\Report',
			)
		);

		add_action( 'rest_api_init', array( $this, 'register_rest_routes' ), 10 );
	}

	/**
	 * Register Superstore REST API routes.
	 *
	 * @since 1.0.0
	 */
	public function register_rest_routes() {
		foreach ( $this->class_map as $file_name => $controller ) {
			require_once $file_name;
			$obj = new $controller();

			if ( method_exists( get_class( $obj ), 'register_routes' ) ) {
				$obj->register_routes();
			}
		}
	}
}
