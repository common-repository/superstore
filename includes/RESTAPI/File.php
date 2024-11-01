<?php

namespace Binarithm\Superstore\RESTAPI;

use WP_Error;
use WP_REST_Server;
use Binarithm\Superstore\Abstracts\AbstractRestPostsController;

/**
 * Media REST API Controller
 */
class File extends AbstractRestPostsController {

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
	protected $base = 'media';

	/**
	 * Post type
	 *
	 * @var string
	 */
	protected $post_type = 'attachment';

	/**
	 * Post status
	 *
	 * @var array
	 */
	protected $post_status = array(
		'publish',
		'pending',
		'draft',
		'auto-draft',
		'future',
		'private',
		'inherit',
		'trash',
	);

	/**
	 * Register all product routes
	 *
	 * @return void
	 */
	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'/' . $this->base,
			array(
				'args' => array(
					'id' => array(
						'description' => __( 'Unique identifier for the object.', 'superstore' ),
						'type'        => 'integer',
					),
				),
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_items' ),
					'args'                => $this->get_collection_params(),
					'permission_callback' => array( $this, 'check_permission' ),
				),
			)
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->base . '/(?P<id>[\d]+)/',
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
					'permission_callback' => array( $this, 'check_permission' ),
				),
				array(
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'update_item' ),
					'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE ),
					'permission_callback' => array( $this, 'check_permission' ),
				),
				array(
					'methods'             => WP_REST_Server::DELETABLE,
					'callback'            => array( $this, 'delete_item' ),
					'permission_callback' => array( $this, 'check_permission' ),
					'args'                => array(
						'force' => array(
							'type'        => 'boolean',
							'default'     => false,
							'description' => __( 'Whether to bypass trash and force deletion.', 'superstore' ),
						),
					),
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
	 * Get object
	 *
	 * @param string|int $id File id.
	 * @return object
	 */
	public function get_object( $id ) {
		return get_post( $id );
	}



	/**
	 * Get file data.
	 *
	 * @param obj    $file file instance.
	 * @param string $request Request context.
	 * @return array
	 */
	protected function prepare_data_for_response( $file, $request ) {
		$meta   = wp_get_attachment_metadata( $file->ID );
		$seller = superstore()->seller->crud_seller( (int) $file->ID );
		$data   = array(
			'id'                => $file->ID,
			'title'             => $file->post_title,
			'width'             => $meta['width'],
			'height'            => $meta['height'],
			'size'              => size_format( (int) $meta['filesize'] ),
			'src'               => $file->guid,
			'type'              => $file->post_mime_type,
			'post_author'       => $file->ID,
			'date_created'      => wc_rest_prepare_date_response( $file->post_date, false ),
			'date_created_gmt'  => wc_rest_prepare_date_response( $file->post_date_gmt ),
			'date_modified'     => wc_rest_prepare_date_response( $file->post_modified, false ),
			'date_modified_gmt' => wc_rest_prepare_date_response( $file->post_modified_gmt ),
			'seller'            => array(
				'id'                  => $seller->get_id(),
				'name'                => $seller->get_store_name(),
				'url'                 => $seller->get_store_url(),
				'profile_picture_src' => $seller->get_profile_picture_src(),
				'address'             => $seller->get_address(),
			),
		);

		$response = rest_ensure_response( $data );
		$response->add_links( $this->prepare_links( $file, $request ) );
		return apply_filters( "superstore_rest_prepare_{$this->post_type}_object", $response, $file, $request );
	}

	/**
	 * Validation before get files
	 *
	 * @param WP_REST_Request $request Request.
	 * @return bool|WP_Error
	 */
	public function validation_before_get_item( $request ) {
		$store_id = get_current_user_id();

		if ( empty( $store_id ) ) {
			return new WP_Error( 'superstore_no_order_seller_found', __( 'No seller found', 'superstore' ), array( 'status' => 407 ) );
		}

		$object = $this->get_object( (int) $request['id'] );

		if ( ! $object ) {
			return new WP_Error( "superstore_rest_{$this->post_type}_invalid_id", __( 'Invalid ID.', 'superstore' ), array( 'status' => 407 ) );
		}

		$file_author = (int) $object->post_author;

		if ( $store_id !== $file_author ) {
			return new WP_Error( "superstore_rest_{$this->post_type}_invalid_id", __( 'This is not your file', 'superstore' ), array( 'status' => 407 ) );
		}

		return true;
	}

	/**
	 * Validation before create product
	 *
	 * @param WP_REST_Request $request Request.
	 * @return bool|WP_Error
	 */
	public function validation_before_create_item( $request ) {
		$store_id = get_current_user_id();

		if ( empty( $store_id ) ) {
			return new WP_Error( 'superstore_no_product_seller_found', __( 'No seller found', 'superstore' ), array( 'status' => 407 ) );
		}

		$author_is_seller = user_can( $store_id, 'manage_superstore' );

		if ( ! $author_is_seller ) {
			return new WP_Error( 'superstore_user_is_not_seller', __( 'This user is not a seller', 'superstore' ) );
		}

		if ( ! empty( $request['id'] ) ) {
			/* translators: %s: product */
			return new WP_Error( "woocommerce_rest_{$this->post_type}_exists", sprintf( __( 'Cannot create existing %s.', 'superstore' ), 'product' ), array( 'status' => 407 ) );
		}

		$enabled                          = superstore()->seller->crud_seller( $store_id )->get_enabled();
		$disabled_ac_add_media_permission = superstore_get_option( 'disabled_seller_can', 'superstore_seller', array( 'add_media' => 'no' ) );

		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			if ( 'yes' !== $enabled ) {
				if ( 'yes' !== $disabled_ac_add_media_permission['add_media'] ) {
					return new WP_Error( 'superstore_rest_media_seller_not_enabled', __( 'Account is not enabled.', 'superstore' ), array( 'status' => 407 ) );
				}
			}
		}

		// Add storage limit validation from superstore option.

		return true;
	}

	/**
	 * Validation before update product
	 *
	 * @param WP_REST_Request $request Request.
	 * @return bool|WP_Error
	 */
	public function validation_before_update_item( $request ) {
		$store_id = get_current_user_id();

		if ( empty( $store_id ) ) {
			return new WP_Error( 'superstore_no_media_seller_found', __( 'No seller found', 'superstore' ), array( 'status' => 407 ) );
		}

		$object = $this->get_object( (int) $request['id'] );

		if ( ! $object ) {
			return new WP_Error( "superstore_rest_{$this->post_type}_invalid_id", __( 'Invalid ID.', 'superstore' ), array( 'status' => 407 ) );
		}

		$file_author = (int) get_post_field( 'post_author', $object->ID );

		if ( $store_id !== $file_author ) {
			return new WP_Error( "superstore_rest_{$this->post_type}_invalid_id", __( 'This is not your file', 'superstore' ), array( 'status' => 407 ) );
		}

		return true;
	}

	/**
	 * Validation before delete item
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_Error|Boolean
	 */
	public function validation_before_delete_item( $request ) {
		$store_id = get_current_user_id();
		$object   = $this->get_object( (int) $request['id'] );
		$result   = false;

		if ( ! $object ) {
			return new WP_Error( "superstore_rest_{$this->post_type}_invalid_id", __( 'Invalid ID.', 'superstore' ), array( 'status' => 405 ) );
		}

		$enabled                       = get_user_meta( $store_id, 'superstore_enabled', true ) ? get_user_meta( $store_id, 'superstore_enabled', true ) : 'no';
		$disabled_ac_delete_permission = superstore_get_option( 'disabled_seller_can', 'superstore_seller', array( 'withdraw_payment' => 'no' ) );

		if ( ! current_user_can( 'manage_woocommerce' ) && 'yes' !== $enabled ) {
			if ( 'yes' !== $disabled_ac_delete_permission['delete_media'] ) {
				return new WP_Error( "superstore_rest_{$this->post_type}_seller_not_enabled", __( 'Account is not enabled.', 'superstore' ) );
			}
		}

		$file_author = (int) get_post_field( 'post_author', $object->ID );

		if ( (int) $store_id !== $file_author ) {
			return new WP_Error( "superstore_rest_{$this->post_type}_invalid_id", __( 'This is not your file', 'superstore' ), array( 'status' => 405 ) );
		}

		return true;
	}

	/**
	 * Delete an item
	 *
	 * @param WP_REST_Request $request Request.
	 * @return void|object|array|string
	 */
	public function delete_item( $request ) {
		$validate = $this->validation_before_delete_item( $request );

		if ( is_wp_error( $validate ) ) {
			return $validate;
		}

		$result = wp_delete_attachment( (int) $request['id'], true );

		if ( ! $result ) {
			// phpcs:ignore WordPress.WP.I18n.MissingTranslatorsComment
			return new WP_Error( 'superstore_rest_cannot_delete', sprintf( __( 'The %s cannot be deleted.', 'superstore' ), $this->post_type ), array( 'status' => 500 ) );
		}

		$data = rest_ensure_response( $result );

		do_action( "superstore_rest_delete_{$this->post_type}_object", $request );

		return $data;
	}

	/**
	 * Prepare links for the request.
	 *
	 * @param WC_Data         $object  Object data.
	 * @param WP_REST_Request $request Request object.
	 * @return array.
	 */
	protected function prepare_links( $object, $request ) {
		$links = array(
			'self'       => array(
				'href' => rest_url( sprintf( '/%s/%s/%d', $this->namespace, $this->base, $object->ID ) ),
			),
			'collection' => array(
				'href' => rest_url( sprintf( '/%s/%s', $this->namespace, $this->base ) ),
			),
		);

		if ( $object->parent_id ) {
			$links['up'] = array(
				'href' => rest_url( sprintf( '/%s/products/%d', $this->namespace, $object->parent_id ) ),
			);
		}

		return $links;
	}

	/**
	 * Prepare objects query
	 *
	 * @param WP_REST_Request $request Request.
	 * @return array
	 */
	protected function prepare_objects_query( $request ) {
		$args = parent::prepare_objects_query( $request );

		// phpcs:ignore WordPress.WP.PostsPerPage.posts_per_page_posts_per_page
		$args['posts_per_page'] = isset( $request['limit'] ) ? $request['limit'] : -1;

		return $args;
	}

	/**
	 * Format item's collection for response
	 *
	 * @param  object $response Response.
	 * @param  object $request Request.
	 * @param  int    $total_items Total items.
	 *
	 * @return object
	 */
	public function format_collection_response( $response, $request, $total_items ) {
		parent::format_collection_response( $response, $request, $total_items );

		$all = count( superstore()->media->get_seller_files( get_current_user_id() ) );

		$response->header( 'superstore-all', $all );

		return $response;
	}
}

