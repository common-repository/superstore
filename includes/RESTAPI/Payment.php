<?php

namespace Binarithm\Superstore\RESTAPI;

use WP_Error;
use WP_Query;
use WP_REST_Controller;
use WP_REST_Server;
use Binarithm\Superstore\Exceptions\SuperstoreException;

/**
 * Payment REST API class
 */
class Payment extends WP_REST_Controller {

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
	protected $base = 'payments';

	/**
	 * Register all routes
	 *
	 * @return void
	 */
	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'/' . $this->base,
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_items' ),
					'permission_callback' => array( $this, 'check_permission' ),
				),
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'create_item' ),
					'permission_callback' => array( $this, 'check_permission' ),
				),
			)
		);
		register_rest_route(
			$this->namespace,
			'/' . $this->base . '/admin',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_admin_items' ),
					'permission_callback' => array( $this, 'check_admin_permission' ),
				),
			)
		);
		register_rest_route(
			$this->namespace,
			'/' . $this->base . '/(?P<id>[\d]+)',
			array(
				'args' => array(
					'id' => array(
						'description' => __( 'Unique identifier for the object.', 'superstore' ),
						'type'        => 'integer',
					),
				),
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_item' ),
					'args'                => $this->get_collection_params(),
					'permission_callback' => array( $this, 'check_admin_permission' ),
				),
				array(
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'edit_item' ),
					'args'                => $this->get_collection_params(),
					'permission_callback' => array( $this, 'check_permission' ),
				),
				array(
					'methods'             => WP_REST_Server::DELETABLE,
					'callback'            => array( $this, 'delete_item' ),
					'args'                => $this->get_collection_params(),
					'permission_callback' => array( $this, 'check_admin_permission' ),
				),
			)
		);
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
	 * Check user is admin
	 *
	 * @param obj|array $request Request.
	 * @return bool
	 */
	public function check_admin_permission( $request ) {
		if ( current_user_can( 'manage_woocommerce' ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Get payments.
	 *
	 * @param WP_REST_Request $request Request data.
	 * @return WP_Error|WP_REST_Response
	 * @throws SuperstoreException On error.
	 */
	public function get_items( $request ) {
		try {
			$args = array(
				'per_page' => isset( $request['limit'] ) ? $request['limit'] : 10,
				'page'     => isset( $request['page'] ) ? $request['page'] : 1,
			);

			if ( isset( $request['filters'] ) && ! empty( $request['filters'] ) ) {
				$filters = $request['filters'];

				foreach ( $filters as $key => $value ) {
					if ( 'yes' === $value ) {
						$args['status'] = $key;
					}
				}
			}

			$user_id = get_current_user_id();

			$args['user_id'] = $user_id;

			$query = superstore()->payment->get_payments( $args );
			$data  = array();

			foreach ( $query as $obj ) {
				$prepared_data = $this->prepare_item_for_response( $obj, $request );
				$data[]        = $this->prepare_response_for_collection( $prepared_data );
			}

			$response = rest_ensure_response( $data );

			$all       = superstore()->payment->get_total_payments( array( 'user_id' => $user_id ) );
			$pending   = superstore()->payment->get_total_payments(
				array(
					'user_id' => $user_id,
					'status'  => 'pending',
				)
			);
			$approved  = superstore()->payment->get_total_payments(
				array(
					'user_id' => $user_id,
					'status'  => 'approved',
				)
			);
			$cancelled = superstore()->payment->get_total_payments(
				array(
					'user_id' => $user_id,
					'status'  => 'cancelled',
				)
			);

			$response->header( 'superstore-all', $all );
			$response->header( 'superstore-pending', $pending );
			$response->header( 'superstore-approved', $approved );
			$response->header( 'superstore-cancelled', $cancelled );

			return $response;
		} catch ( SuperstoreException $e ) {
			return new WP_Error( $e->getCode(), $e->getMessage() );
		}
	}

	/**
	 * Get admin payments.
	 *
	 * @param WP_REST_Request $request Request data.
	 * @return WP_Error|WP_REST_Response
	 * @throws SuperstoreException On error.
	 */
	public function get_admin_items( $request ) {
		try {
			$args = array(
				'per_page' => isset( $request['limit'] ) ? $request['limit'] : 10,
				'page'     => isset( $request['page'] ) ? $request['page'] : 1,
			);

			if ( isset( $request['filters'] ) && ! empty( $request['filters'] ) ) {
				$filters = $request['filters'];

				foreach ( $filters as $key => $value ) {
					if ( 'yes' === $value ) {
						$args['status'] = $key;
					}
				}
			}

			$query = superstore()->payment->get_payments( $args );
			$data  = array();

			foreach ( $query as $obj ) {
				$prepared_data = $this->prepare_item_for_response( $obj, $request );
				$data[]        = $this->prepare_response_for_collection( $prepared_data );
			}

			$response = rest_ensure_response( $data );

			$all       = superstore()->payment->get_total_payments();
			$pending   = superstore()->payment->get_total_payments(
				array(
					'status' => 'pending',
				)
			);
			$approved  = superstore()->payment->get_total_payments(
				array(
					'status' => 'approved',
				)
			);
			$cancelled = superstore()->payment->get_total_payments(
				array(
					'status' => 'cancelled',
				)
			);

			$response->header( 'superstore-all', $all );
			$response->header( 'superstore-pending', $pending );
			$response->header( 'superstore-approved', $approved );
			$response->header( 'superstore-cancelled', $cancelled );

			return $response;
		} catch ( SuperstoreException $e ) {
			return new WP_Error( $e->getCode(), $e->getMessage() );
		}
	}

	/**
	 * Get item
	 *
	 * @param mixed $request Request.
	 * @return array
	 * @throws SuperstoreException On error.
	 */
	public function get_item( $request ) {
		try {
			if ( ! isset( $request['id'] ) && empty( $request['id'] ) ) {
				throw new SuperstoreException( 'superstore_rest_update_error_no_payment_id', __( 'No payment ID found', 'superstore' ), 405 );
			}

			$item = superstore()->payment->crud_payment( (int) $request['id'] );

			$prepared_data = $this->prepare_item_for_response( $item, $request );
			$data          = $this->prepare_response_for_collection( $prepared_data );

			return $data;
		} catch ( SuperstoreException $e ) {
			return new WP_Error( $e->getCode(), $e->getMessage() );
		}
	}

	/**
	 * Edit item
	 *
	 * @param mixed $request Request.
	 * @return array
	 * @throws SuperstoreException On error.
	 */
	public function edit_item( $request ) {
		try {
			if ( ! isset( $request['id'] ) && empty( $request['id'] ) ) {
				throw new SuperstoreException( 'superstore_rest_update_error_no_payment', __( 'No payment ID found', 'superstore' ), 405 );
			}

			$obj = superstore()->payment->crud_payment( (int) $request['id'] );

			foreach ( $obj->get_data() as $key => $value ) {
				if ( is_callable( array( $obj, "set_$key" ) ) ) {
					if ( isset( $request['id'] ) && ! empty( $request[ $key ] ) ) {
						$obj->{"set_$key"}( $request[ $key ] );
					}
				}
			}

			$id = $obj->save();

			return rest_ensure_response( $id );
		} catch ( SuperstoreException $e ) {
			return new WP_Error( $e->getCode(), $e->getMessage() );
		}
	}

	/**
	 * Delete item
	 *
	 * @param mixed $request Request.
	 * @return array
	 * @throws SuperstoreException On error.
	 */
	public function delete_item( $request ) {
		try {
			$payment_id = isset( $request['id'] ) && ! empty( $request['id'] ) ? (int) $request['id'] : 0;

			if ( ! $payment_id ) {
				throw new SuperstoreException( 'superstore_rest_delete_no_payment_id_found', __( 'No payment ID found to delete', 'superstore' ), 405 );
			}

			$obj = superstore()->payment->crud_payment( $payment_id );
			$id  = $obj->delete();

			return rest_ensure_response( $id );
		} catch ( SuperstoreException $e ) {
			return new WP_Error( $e->getCode(), $e->getMessage() );
		}
	}

	/**
	 * Create payment|withdraw request
	 *
	 * @param array $request Request.
	 * @return WP_REST_Response
	 * @throws SuperstoreException On error.
	 */
	public function create_item( $request ) {
		try {
			if ( isset( $request['id'] ) || ! empty( $request['id'] ) ) {
				throw new SuperstoreException( 'superstore_rest_create_error_existing_payment_id_found', __( 'Cannot create existing resource.', 'superstore' ), 405 );
			}

			$obj = superstore()->payment->crud_payment();

			foreach ( $obj->get_data() as $key => $value ) {
				if ( is_callable( array( $obj, "set_$key" ) ) ) {
					if ( ! empty( $request[ $key ] ) ) {
						$obj->{"set_$key"}( $request[ $key ] );
					}
				}
			}

			$id = $obj->save();

			return rest_ensure_response( $id );
		} catch ( SuperstoreException $e ) {
			return new WP_Error( $e->getCode(), $e->getMessage() );
		}
	}

	/**
	 * Prepare a single payment output for response
	 *
	 * @param object          $payment Payment.
	 * @param WP_REST_Request $request Request object.
	 * @param array           $additional_fields Additional fields (optional).
	 *
	 * @return WP_REST_Response $response Response data.
	 */
	public function prepare_item_for_response( $payment, $request, $additional_fields = array() ) {

		$seller = superstore()->seller->crud_seller( $payment->get_user_id() );

		$data                              = array();
		$data                              = $payment->get_data();
		$data['amount']                    = wc_price( $payment->get_amount() );
		$data['store_name']                = $seller->get_store_name() ? $seller->get_store_name() : $seller->get_user_login();
		$data['store_profile_picture_src'] = $seller->get_profile_picture_src();
		$data                              = array_merge( $data, apply_filters( 'supertore_rest_payment_additional_fields', $additional_fields, $payment, $request ) );
		$response                          = rest_ensure_response( $data );
		$response->add_links( $this->prepare_links( $payment, $request ) );

		return apply_filters( 'supertore_rest_prepare_payment_item_for_response', $response );
	}

	/**
	 * Prepare links for the request.
	 *
	 * @param SuperstoreSeller $object  Object data.
	 * @param WP_REST_Request  $request Request object.
	 *
	 * @return array                   Links for the given post.
	 */
	protected function prepare_links( $object, $request ) {
		$links = array(
			'self'       => array(
				'href' => rest_url( sprintf( '/%s/%s/%d', $this->namespace, $this->base, $object->get_id() ) ),
			),
			'collection' => array(
				'href' => rest_url( sprintf( '/%s/%s', $this->namespace, $this->base ) ),
			),
		);

		return $links;
	}
}
