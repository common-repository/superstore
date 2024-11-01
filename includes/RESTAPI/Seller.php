<?php

namespace Binarithm\Superstore\RESTAPI;

use WP_Error;
use WP_Query;
use WP_REST_Controller;
use WP_REST_Server;
use Binarithm\Superstore\Exceptions\SuperstoreException;

/**
 * Seller REST API Controller
 */
class Seller extends WP_REST_Controller {

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
	protected $base = 'sellers';

	/**
	 * Register all seller routes
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
					'permission_callback' => '__return_true',
				),
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'create_item' ),
					'args'                => $this->get_collection_params(),
					'permission_callback' => '__return_true',
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
					'permission_callback' => '__return_true',
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
		register_rest_route(
			$this->namespace,
			'/' . $this->base . '/(?P<slug>[-\w]+)',
			array(
				'args' => array(
					'slug' => array(
						'description' => __( 'Unique identifier for the object.', 'superstore' ),
						'type'        => 'string',
					),
				),
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_item_by_slug' ),
					'permission_callback' => '__return_true',
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
			return (int) get_current_user_id() === (int) $request->get_param( 'id' );
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
	public function get_items( $request ) {
		try {
			$filters = array();
			if ( isset( $request['filters'] ) && ! empty( $request['filters'] ) ) {
				$filters = $request['filters'];
			}

			$meta_args = array();

			if ( ! empty( $filters ) && is_array( $filters ) ) {
				foreach ( $filters as $key => $value ) {
					if ( 'current_month' === $key ) {
						continue;
					}

					if ( 'not_enabled' === $key ) {
						$meta_args[] = array(
							'key'     => 'superstore_enabled',
							'value'   => 'no',
							'compare' => 'LIKE',
						);
					} else {
						$meta_args[] = array(
							'key'     => "superstore_$key",
							'value'   => $value,
							'compare' => 'LIKE',
						);
					}
				}
			}

			$args = array(
				'number'     => isset( $request['limit'] ) ? (int) $request['limit'] : 10,
				'paged'      => isset( $request['page'] ) ? (int) $request['page'] : 1,
				'date_query' => array(
					array(
						'after'     => ! empty( $filters['current_month'] ) ? $filters['current_month'] : null,
						'inclusive' => true,
					),
				),
				// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
				'meta_query' => array( $meta_args ),
			);

			if ( is_email( $request['search'] ) ) {
				$args['search'] = $request['search'];
			} else {
				$args['meta_query'][] = array(
					'key'     => 'superstore_store_name',
					'value'   => $request['search'],
					'compare' => 'LIKE',
				);
			}

			$query = superstore()->seller->get_sellers( $args );
			$data  = array();

			foreach ( $query as $obj ) {
				$prepared_data = $this->prepare_item_for_response( $obj, $request );
				$data[]        = $this->prepare_response_for_collection( $prepared_data );
			}

			$response = rest_ensure_response( $data );
			$count    = superstore_count_sellers();

			$response->header( 'superstore-all', $count['all'] );
			$response->header( 'superstore-enabled', $count['enabled'] );
			$response->header( 'superstore-not_enabled', $count['not_enabled'] );
			$response->header( 'superstore-featured', $count['featured'] );
			$response->header( 'superstore-current_month', $count['current_month'] );

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
			$id = $request['id'] ? $request['id'] : get_current_user_id();

			$obj = superstore()->seller->crud_seller( $id );

			$prepared_data = $this->prepare_item_for_response( $obj, $request );
			$data          = $this->prepare_response_for_collection( $prepared_data );

			return $data;
		} catch ( SuperstoreException $e ) {
			return new WP_Error( $e->getCode(), $e->getMessage() );
		}
	}

	/**
	 * Create item
	 *
	 * @param mixed $request Request.
	 * @return array
	 * @throws SuperstoreException On error.
	 */
	public function create_item( $request ) {
		try {
			if ( ! empty( $request['id'] ) ) {
				throw new SuperstoreException( 'superstore_rest_registration_error_can_not_set_id', __( 'Cannot create existing resource.', 'superstore' ), 402 );
			}

			$obj = superstore()->seller->crud_seller();

			foreach ( $obj->get_data() as $key => $value ) {
				if ( is_array( $value ) ) {
					foreach ( $value as $key2 => $value2 ) {
						$callable = 'set_' . $key . '_' . $key2;
						if ( is_callable( array( $obj, $callable ) ) ) {
							$request_key = $key . '_' . $key2;
							if ( ! empty( $request[ $request_key ] ) ) {
								$obj->{$callable}( $request[ $request_key ] );
							}
						}
					}
				} else {
					if ( is_callable( array( $obj, "set_$key" ) ) ) {
						if ( ! empty( $request[ $key ] ) ) {
							$obj->{"set_$key"}( $request[ $key ] );

							if ( $request['banner_id'] && is_array( $request['banner_id'] ) ) {
								$obj->set_banner_id( $request['banner_id']['id'] );
							}

							if ( $request['profile_picture_id'] && is_array( $request['profile_picture_id'] ) ) {
								$obj->set_profile_picture_id( $request['profile_picture_id']['id'] );
							}
						}
					}
				}
			}

			if ( isset( $request['password'] ) ) {
				$obj->set_password( $request['password'] );
			}

			$id = $obj->save();

			if ( ! is_user_logged_in() && 'yes' === superstore_get_option( 'set_logged_in_after_registration', 'superstore_general', 'yes' ) ) {
				wp_set_current_user( $id );
				wp_set_auth_cookie( $id, true );
			}

			$prepared_data = $this->prepare_item_for_response( $obj, $request );
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
			$id = ! empty( $request['id'] ) ? (int) $request['id'] : 0;

			$obj = superstore()->seller->crud_seller( $id );

			foreach ( $obj->get_data() as $key => $value ) {
				if ( is_array( $value ) ) {
					foreach ( $value as $key2 => $value2 ) {
						$callable = 'set_' . $key . '_' . $key2;
						if ( is_callable( array( $obj, $callable ) ) ) {
							$request_key = $key . '_' . $key2;
							if ( isset( $request[ $request_key ] ) ) {
								$obj->{$callable}( $request[ $request_key ] );
							}
						}
					}
				} else {
					if ( is_callable( array( $obj, "set_$key" ) ) ) {
						if ( isset( $request[ $key ] ) ) {
							$obj->{"set_$key"}( $request[ $key ] );
						}

						if ( $request['banner_id'] && is_array( $request['banner_id'] ) ) {
							$obj->set_banner_id( $request['banner_id']['id'] );
						}

						if ( $request['profile_picture_id'] && is_array( $request['profile_picture_id'] ) ) {
							$obj->set_profile_picture_id( $request['profile_picture_id']['id'] );
						}
					}
				}
			}

			if ( isset( $request['password'] ) ) {
				if ( current_user_can( 'manage_woocommerce' ) ) {
					$obj->set_password( $request['password'] );
				} else {
					if ( 'yes' === superstore_get_option( 'seller_can_edit_password', 'superstore_seller', 'yes' ) ) {
						$obj->set_password( $request['password'] );
					}
				}
			}

			$obj->save();

			$prepared_data = $this->prepare_item_for_response( $obj, $request );
			$data          = $this->prepare_response_for_collection( $prepared_data );

			return $data;
		} catch ( SuperstoreException $e ) {
			return new WP_Error( $e->getCode(), $e->getMessage() );
		}
	}

	/**
	 * Delete item
	 *
	 * @param mixed $request Request.
	 * @return int
	 * @throws SuperstoreException On error.
	 */
	public function delete_item( $request ) {
		try {
			$id = ! empty( $request['id'] ) ? (int) $request['id'] : 0;

			$obj = superstore()->seller->crud_seller( $id );
			$id  = $obj->delete();

			return rest_ensure_response( $id );
		} catch ( SuperstoreException $e ) {
			return new WP_Error( $e->getCode(), $e->getMessage() );
		}
	}

	/**
	 * Get item by nicename/slug
	 *
	 * @param mixed $request Request.
	 * @return array
	 * @throws SuperstoreException On error.
	 */
	public function get_item_by_slug( $request ) {
		try {
			if ( ! $request['slug'] ) {
				throw new SuperstoreException( 'superstore_rest_user_slug_is_required', __( 'User slug is required', 'superstore' ), 402 );
			}

			$user = get_user_by( 'slug', $request['slug'] );

			if ( ! $user ) {
				throw new SuperstoreException( 'superstore_rest_user_slug_is_not_exists', __( 'User with this slug is not exists', 'superstore' ), 402 );
			}

			$obj = superstore()->seller->crud_seller( $user->ID );

			$prepared_data = $this->prepare_item_for_response( $obj, $request );
			$data          = $this->prepare_response_for_collection( $prepared_data );

			return $data;
		} catch ( SuperstoreException $e ) {
			return new WP_Error( $e->getCode(), $e->getMessage() );
		}
	}

	/**
	 * Prepare a single output for response
	 *
	 * @param object          $obj Object.
	 * @param WP_REST_Request $request Request object.
	 * @param array           $additional_fields Additional fields (optional).
	 *
	 * @return WP_REST_Response $response Response data.
	 */
	public function prepare_item_for_response( $obj, $request, $additional_fields = array() ) {

		$data                      = $obj->get_data();
		$data['products_overview'] = superstore()->report->get_seller_products_overview( $obj->get_id() );
		$data['orders_overview']   = superstore()->report->get_seller_orders_overview( $obj->get_id(), true );
		$data['payments_overview'] = superstore()->report->get_seller_payments_overview( $obj->get_id(), true );
		$data['media_overview']    = superstore()->report->get_seller_media_overview( $obj->get_id(), true );
		$data                      = array_merge( $data, apply_filters( 'supertore_rest_seller_additional_fields', $additional_fields, $obj, $request ) );
		$response                  = rest_ensure_response( $data );
		$response->add_links( $this->prepare_links( $obj, $request ) );

		return apply_filters( 'supertore_rest_prepare_seller_item_for_response', $response );
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
