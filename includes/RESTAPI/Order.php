<?php

namespace Binarithm\Superstore\RESTAPI;

use WP_Error;
use WP_REST_Server;
use Binarithm\Superstore\Abstracts\AbstractRestPostsController;
use WC_Customer_Download;
use WC_Data_Store;

/**
 * Order REST API Controller
 */
class Order extends AbstractRestPostsController {
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
	protected $base = 'orders';

	/**
	 * Post type
	 *
	 * @var string
	 */
	protected $post_type = 'shop_order';

	/**
	 * Post status
	 *
	 * @var array
	 */
	protected $post_status = array();

	/**
	 * Order rest constructor
	 */
	public function __construct() {
		$this->post_status = array_keys( wc_get_order_statuses() );
		add_filter( 'woocommerce_new_order_data', array( $this, 'add_seller_id' ) );
		add_filter( 'woocommerce_rest_pre_insert_shop_order_object', array( $this, 'make_order_parent' ), 10, 3 );
		add_action( 'woocommerce_rest_insert_shop_order_object', array( $this, 'create_sub_order' ), 10, 2 );
	}

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
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_items' ),
					'permission_callback' => array( $this, 'check_permission' ),
					'args'                => $this->get_collection_params(),
				),
				'schema' => array( $this, 'get_public_item_schema' ),
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
					'permission_callback' => array( $this, 'check_update_permission' ),
				),
			)
		);
		register_rest_route(
			$this->namespace,
			'/' . $this->base . '/revoke-access',
			array(
				array(
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'revoke_access_to_download' ),
					'args'                => $this->get_collection_params(),
					'permission_callback' => array( $this, 'check_permission' ),
				),
			)
		);
		register_rest_route(
			$this->namespace,
			'/' . $this->base . '/grant-access',
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'grant_new_access_to_download' ),
					'args'                => $this->get_collection_params(),
					'permission_callback' => array( $this, 'check_permission' ),
				),
			)
		);
		register_rest_route(
			$this->namespace,
			'/' . $this->base . '/notes',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_notes' ),
					'args'                => $this->get_collection_params(),
					'permission_callback' => array( $this, 'check_permission' ),
				),
			)
		);
		register_rest_route(
			$this->namespace,
			'/' . $this->base . '/notes',
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'create_notes' ),
					'args'                => $this->get_collection_params(),
					'permission_callback' => array( $this, 'check_permission' ),
				),
			)
		);
		register_rest_route(
			$this->namespace,
			'/' . $this->base . '/notes',
			array(
				array(
					'methods'             => WP_REST_Server::DELETABLE,
					'callback'            => array( $this, 'delete_notes' ),
					'args'                => $this->get_collection_params(),
					'permission_callback' => array( $this, 'check_permission' ),
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
	 * Check user is seller and updating only own data if has admin permission
	 *
	 * @param obj|array $request Request.
	 * @return bool
	 */
	public function check_update_permission( $request ) {
		if ( current_user_can( 'manage_woocommerce' ) ) {
			return true;
		}

		if ( superstore_is_user_seller( get_current_user_id() ) ) {
			$has_permission = superstore_get_option( 'seller_can_change_order_status', 'superstore_seller', 'yes' );

			if ( 'yes' === $has_permission ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Add seller ID to order object if request is from api.
	 *
	 * @param array $args Args.
	 * @return array
	 */
	public function add_seller_id( $args ) {
		if ( defined( 'REST_REQUEST' ) ) {
			$args['post_author'] = get_current_user_id();
		}

		return $args;
	}

	/**
	 * If order have multiple sellers mark order as parent
	 *
	 * @param WC_Order        $order Order.
	 * @param WP_REST_Request $request Request.
	 * @param bool            $creating Creating.
	 * @return WC_Order
	 */
	public function make_order_parent( $order, $request, $creating ) {
		if ( $creating ) {
			$vendors = superstore_get_sellers_by_order( $order );

			if ( count( $vendors ) > 1 ) {
				$order->update_meta_data( 'superstore_has_sub_order', true );
			}
		}

		return $order;
	}

	/**
	 * Creater sub orders if order created via api
	 *
	 * @param  WC_Order        $object Object.
	 * @param  WP_REST_Request $request Request.
	 */
	public function create_sub_order( $object, $request ) {
		superstore()->order->maybe_create_sub_order( $object->get_id() );
	}

	/**
	 * Get object
	 *
	 * @param string|int $id Order id.
	 * @return object
	 */
	public function get_object( $id ) {
		return wc_get_order( $id );
	}

	/**
	 * Revoke file download access permission for customer
	 *
	 * @param WP_REST_Request $request Request data.
	 * @throws SuperstoreException On error.
	 */
	public function revoke_access_to_download( $request ) {
		try {
			if ( ! isset( $request['permission_id'] ) ) {
				return new WP_Error( 'superstore_rest_revoke_no_permission_id', __( 'No permission ID found', 'superstore' ), array( 'status' => 406 ) );
			}

			$error = $this->validation_before_update_item( array( 'id' => $request['order_id'] ) );
			if ( is_wp_error( $error ) ) {
				return new WP_Error( $error->get_error_code(), $error->get_error_message(), $error->get_error_data() );
			}

			$data_store = WC_Data_Store::load( 'customer-download' );
			$data_store->delete_by_id( $request['permission_id'] );

			$revoked_msg = __( 'Access revoked', 'superstore' );

			return rest_ensure_response( $revoked_msg );
		} catch ( SuperstoreException $e ) {
			return new WP_Error( $e->getCode(), $e->getMessage() );
		}
	}

	/**
	 * Grant new file download access permission for customer
	 *
	 * @param WP_REST_Request $request Request data.
	 * @throws SuperstoreException On error.
	 */
	public function grant_new_access_to_download( $request ) {
		try {
			if ( ! isset( $request['product_ids'] ) ) {
				return new WP_Error( 'superstore_rest_grant_no_product_id', __( 'No product ID found', 'superstore' ), array( 'status' => 406 ) );
			}

			if ( ! isset( $request['order_id'] ) ) {
				return new WP_Error( 'superstore_rest_grant_no_order_id', __( 'No order ID found', 'superstore' ), array( 'status' => 406 ) );
			}

			$error = $this->validation_before_update_item( array( 'id' => $request['order_id'] ) );
			if ( is_wp_error( $error ) ) {
				return new WP_Error( $error->get_error_code(), $error->get_error_message(), $error->get_error_data() );
			}

			$order_id     = (int) $request['order_id'];
			$product_ids  = $request['product_ids'];
			$grant_msg    = '';
			$file_counter = 0;
			$order        = wc_get_order( $order_id );

			$data  = array();
			$items = $order->get_items();

			// Check against order items first.
			foreach ( $items as $item ) {
				$product = $item->get_product();

				if ( $product && $product->exists() && in_array( $product->get_id(), $product_ids, true ) && $product->is_downloadable() ) {
					$data[ $product->get_id() ] = array(
						'files'      => $product->get_downloads(),
						'quantity'   => $item->get_quantity(),
						'order_item' => $item,
					);
				}
			}

			foreach ( $product_ids as $product_id ) {
				$product = wc_get_product( $product_id );

				if ( isset( $data[ $product->get_id() ] ) ) {
					$download_data = $data[ $product->get_id() ];
				} else {
					$download_data = array(
						'files'      => $product->get_downloads(),
						'quantity'   => 1,
						'order_item' => null,
					);
				}

				if ( ! empty( $download_data['files'] ) ) {
					foreach ( $download_data['files'] as $download_id => $file ) {
						$inserted_id = wc_downloadable_file_permission( $download_id, $product->get_id(), $order, $download_data['quantity'], $download_data['order_item'] );
						if ( $inserted_id ) {
							$grant_msg = __( 'New access granted', 'superstore' );
						}
					}
				} else {
					$grant_msg = __( 'Products contain with no files are skipped', 'superstore' );
				}
			}

			return rest_ensure_response( $grant_msg );
		} catch ( SuperstoreException $e ) {
			return new WP_Error( $e->getCode(), $e->getMessage() );
		}
	}

	/**
	 * Get modified posts.
	 *
	 * @param WP_REST_Request $request Request data.
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_items( $request ) {
		$filters = array();
		if ( isset( $request['filters'] ) && ! empty( $request['filters'] ) ) {
			$filters = $request['filters'];
		}

		$statuses = array();

		if ( ! empty( $filters ) && is_array( $filters ) ) {
			foreach ( $filters as $key => $value ) {
				if ( 'current_month' === $key ) {
					continue;
				}
				if ( 'yes' === $value ) {
					$statuses[] = $key;
				}
			}
		}

		// Filter by post statuses.
		$args['post_status']  = ! empty( $statuses ) ? $statuses : $this->post_status;
		$args['order_by']     = 'date';
		$args['order']        = 'DESC';
		$args['limit']        = isset( $request['limit'] ) ? (int) $request['limit'] : 10;
		$args['paged']        = isset( $request['page'] ) ? (int) $request['page'] : 1;
		$args['date_created'] = ! empty( $filters['current_month'] ) ? $filters['current_month'] : null;
		$args['p']            = ! empty( $request['search'] ) ? (int) $request['search'] : null;

		$orders = superstore()->order->get_seller_orders( get_current_user_id(), $args );

		$data_objects = array();
		$total_orders = 0;

		if ( ! empty( $orders ) ) {
			foreach ( $orders as $order ) {
				$wc_order = $this->get_object( $order->get_id() );
				if ( ! empty( $wc_order ) ) {
					$data           = $this->prepare_data_for_response( $wc_order, $request );
					$data_objects[] = $this->prepare_response_for_collection( $data );
				}
			}
		}

		$total_orders = count( $orders );

		$response = rest_ensure_response( $data_objects );
		$response = $this->format_collection_response( $response, $request, $total_orders );

		return $response;
	}

	/**
	 * Get order notes.
	 *
	 * @param WP_REST_Request $request Request data.
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_notes( $request ) {
		$post_id        = isset( $request['post_id'] ) ? (int) $request['post_id'] : 0;
		$store_id       = get_current_user_id();
		$product_author = superstore_get_seller_by_order( $this->get_object( $post_id ) )->get_id();

		if ( (int) $store_id !== (int) $product_author ) {
			return new WP_Error( "superstore_rest_{$this->post_type}_invalid_id", __( 'This is not your order', 'superstore' ), array( 'status' => 406 ) );
		}

		$args = array(
			'post_id' => $post_id,
			'approve' => 'approve',
			'type'    => 'order_note',
			'status'  => 1,
		);
		remove_filter( 'comments_clauses', array( 'WC_Comments', 'exclude_order_comments' ), 10, 1 );
		$comments = get_comments( $args );
		$notes    = array();
		foreach ( $comments as $key => $comment ) {
			$notes[ $key ] = array(
				'note_id'       => $comment->comment_ID,
				'post_id'       => $comment->comment_post_ID,
				'author'        => $comment->comment_author,
				'content'       => $comment->comment_content,
				'customer_note' => get_comment_meta( $comment->comment_ID, 'is_customer_note', true ) ? 'yes' : 'no',
				'time'          => human_time_diff( strtotime( $comment->comment_date_gmt ), time() ),
			);
		}

		add_filter( 'comments_clauses', array( 'WC_Comments', 'exclude_order_comments' ), 10, 1 );

		return rest_ensure_response( $notes );
	}

	/**
	 * Create order notes.
	 *
	 * @param WP_REST_Request $request Request data.
	 * @return WP_Error|WP_REST_Response
	 */
	public function create_notes( $request ) {
		$post_id   = isset( $request['post_id'] ) ? absint( $request['post_id'] ) : 0;
		$note      = isset( $request['note'] ) ? sanitize_textarea_field( wp_unslash( $request['note'] ) ) : '';
		$note_type = isset( $request['note_type'] ) ? sanitize_text_field( wp_unslash( $request['note_type'] ) ) : '';

		$is_customer_note = ( 'customer' === $note_type ) ? 1 : 0;

		if ( $post_id > 0 ) {
			$order          = $this->get_object( $post_id );
			$store_id       = get_current_user_id();
			$product_author = superstore_get_seller_by_order( $this->get_object( $post_id ) )->get_id();

			if ( (int) $store_id !== (int) $product_author ) {
				return new WP_Error( "superstore_rest_{$this->post_type}_invalid_id", __( 'This is not your order', 'superstore' ), array( 'status' => 406 ) );
			}

			$comment_id = $order->add_order_note( $note, $is_customer_note, true );

			return rest_ensure_response( $comment_id );
		}
	}

	/**
	 * Delete order notes.
	 *
	 * @param WP_REST_Request $request Request data.
	 * @return WP_Error|WP_REST_Response
	 */
	public function delete_notes( $request ) {
		$post_id        = isset( $request['post_id'] ) ? absint( $request['post_id'] ) : 0;
		$store_id       = get_current_user_id();
		$product_author = superstore_get_seller_by_order( $this->get_object( $post_id ) )->get_id();

		if ( (int) $store_id !== (int) $product_author ) {
			return new WP_Error( "superstore_rest_{$this->post_type}_invalid_id", __( 'This is not your order', 'superstore' ), array( 'status' => 406 ) );
		}

		$note_id = isset( $request['note_id'] ) ? (int) $request['note_id'] : 0;

		if ( $note_id > 0 ) {
			wp_delete_comment( $note_id );
		}
	}

	/**
	 * Validation before get order
	 *
	 * @param WP_REST_Request $request Request.
	 * @return bool|WP_Error
	 */
	public function validation_before_get_item( $request ) {
		$store_id = get_current_user_id();

		if ( empty( $store_id ) ) {
			return new WP_Error( 'superstore_no_order_seller_found', __( 'No seller found', 'superstore' ), array( 'status' => 406 ) );
		}

		$object = $this->get_object( (int) $request['id'] );

		if ( ! $object || 0 === $object->get_id() ) {
			return new WP_Error( "superstore_rest_{$this->post_type}_invalid_id", __( 'Invalid ID.', 'superstore' ), array( 'status' => 406 ) );
		}

		$has_suborder = $object->get_meta( 'superstore_has_sub_order' );
		if ( $has_suborder ) {
			return new WP_Error( "superstore_rest_{$this->post_type}_parent", __( 'This is a parent order', 'superstore' ), array( 'status' => 406 ) );
		}

		$product_author = (int) superstore_get_seller_by_order( $object )->get_id();

		if ( $store_id !== $product_author ) {
			return new WP_Error( "superstore_rest_{$this->post_type}_invalid_id", __( 'This is not your order', 'superstore' ), array( 'status' => 406 ) );
		}

		return true;
	}

	/**
	 * Validation before update order
	 *
	 * @param WP_REST_Request $request Request.
	 * @return bool|WP_Error
	 */
	public function validation_before_update_item( $request ) {
		$store_id = get_current_user_id();

		if ( empty( $store_id ) ) {
			return new WP_Error( 'superstore_no_order_seller_found', __( 'No seller found', 'superstore' ), array( 'status' => 406 ) );
		}

		$object = $this->get_object( (int) $request['id'] );

		if ( ! $object || 0 === $object->get_id() ) {
			return new WP_Error( "superstore_rest_{$this->post_type}_invalid_id", __( 'Invalid ID.', 'superstore' ), array( 'status' => 406 ) );
		}

		$has_suborder = $object->get_meta( 'superstore_has_sub_order' );
		if ( $has_suborder ) {
			return new WP_Error( "superstore_rest_{$this->post_type}_parent", __( 'This is a parent order', 'superstore' ), array( 'status' => 406 ) );
		}

		$product_author = (int) superstore_get_seller_by_order( $object )->get_id();

		if ( $store_id !== $product_author ) {
			return new WP_Error( "superstore_rest_{$this->post_type}_invalid_id", __( 'This is not your order', 'superstore' ), array( 'status' => 406 ) );
		}

		$enabled                             = superstore()->seller->crud_seller( $store_id )->get_enabled();
		$disabled_ac_manage_order_permission = superstore_get_option( 'disabled_seller_can', 'superstore_seller', array( 'manage_order' => 'no' ) );

		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			if ( 'yes' !== $enabled ) {
				if ( 'yes' !== $disabled_ac_manage_order_permission['manage_order'] ) {
					return new WP_Error( 'superstore_rest_order_seller_not_enabled', __( 'Account is not enabled.', 'superstore' ), array( 'status' => 406 ) );
				}
			}
		}

		return true;
	}

	/**
	 * Prepare single order data for response.
	 *
	 * @param  WC_Data         $object  Object data.
	 * @param  WP_REST_Request $request Request object.
	 * @return WP_REST_Response
	 */
	public function prepare_data_for_response( $object, $request ) {
		$this->request = $request;
		$data          = $this->get_formatted_item_data( $object );
		$response      = rest_ensure_response( $data );
		$response->add_links( $this->prepare_links( $object, $request ) );
		return apply_filters( "superstore_rest_prepare_{$this->post_type}_object", $response, $object, $request );
	}

	/**
	 * Prepare object for database mapping
	 *
	 * @param array $request Request.
	 * @return obj|error
	 */
	public function prepare_object_for_database( $request ) {
		$id             = isset( $request['id'] ) ? absint( $request['id'] ) : 0;
		$status         = isset( $request['status'] ) ? sanitize_text_field( $request['status'] ) : '';
		$order_statuses = wc_get_order_statuses();

		foreach ( $order_statuses as $key => $value ) {
			$short_key = substr( $key, 3 );
			if ( $short_key === $status ) {
				$status = 'wc-' . $status;
			}
		}

		if ( empty( $id ) ) {
			return new WP_Error( "superstore_rest_invalid_{$this->post_type}_id", __( 'Invalid order ID', 'superstore' ), array( 'status' => 406 ) );
		}

		if ( empty( $status ) ) {
			return new WP_Error( "superstore_rest_empty_{$this->post_type}_status", __( 'Order status is required', 'superstore' ), array( 'status' => 406 ) );
		}

		if ( ! in_array( $status, array_keys( $order_statuses ), true ) ) {
			return new WP_Error( "superstore_rest_invalid_{$this->post_type}_status", __( 'Order status is not valid', 'superstore' ), array( 'status' => 406 ) );
		}

		$order = $this->get_object( $id );

		if ( ! $order ) {
			return new WP_Error( "superstore_rest_invalid_{$this->post_type}", __( 'Invalid order', 'superstore' ), array( 'status' => 406 ) );
		}

		$order->set_status( $status );

		return apply_filters( "superstore_rest_pre_insert_{$this->post_type}_object", $order, $request );
	}

	/**
	 * Get formatted data.
	 *
	 * @param  WC_Data $object WC_Data instance.
	 * @return array
	 */
	protected function get_formatted_item_data( $object ) {
		$data              = $object->get_data();
		$format_decimal    = array( 'discount_total', 'discount_tax', 'shipping_total', 'shipping_tax', 'shipping_total', 'shipping_tax', 'cart_tax', 'total', 'total_tax' );
		$format_date       = array( 'date_created', 'date_modified', 'date_completed', 'date_paid' );
		$format_line_items = array( 'line_items', 'tax_lines', 'shipping_lines', 'fee_lines', 'coupon_lines' );

		// Format decimal values.
		foreach ( $format_decimal as $key ) {
			$data[ $key ] = wc_format_decimal( $data[ $key ], '' );
		}

		// Format date values.
		foreach ( $format_date as $key ) {
			$datetime              = $data[ $key ];
			$data[ $key ]          = wc_rest_prepare_date_response( $datetime, false );
			$data[ $key . '_gmt' ] = wc_rest_prepare_date_response( $datetime );
		}

		// Format the order status.
		$data['status'] = 'wc-' === substr( $data['status'], 0, 3 ) ? substr( $data['status'], 3 ) : $data['status'];

		// Format line items.
		foreach ( $format_line_items as $key ) {
			$data[ $key ] = array_values( array_map( array( $this, 'get_order_item_data' ), $data[ $key ] ) );
		}

		// Refunds.
		$data['refunds'] = array();
		foreach ( $object->get_refunds() as $refund ) {
			$data['refunds'][] = array(
				'id'     => $refund->get_id(),
				'refund' => $refund->get_reason() ? $refund->get_reason() : '',
				'total'  => '-' . wc_format_decimal( $refund->get_amount(), '' ),
			);
		}

		$customer_downloads = array();
		$data_store         = WC_Data_Store::load( 'customer-download' );
		$downloads          = $data_store->get_downloads(
			array(
				'order_id' => $object->get_id(),
				'limit'    => -1,
			)
		);

		foreach ( $downloads as $download ) {
			$product = wc_get_product( $download->get_product_id() );
			if ( ! $product || ! $product->exists() || ! $product->has_file( $download->get_download_id() ) ) {
				continue;
			}

			$customer_download                 = $download->get_data();
			$customer_download['product_name'] = $product->get_formatted_name();
			$customer_downloads[]              = $customer_download;
		}

		$earnings = superstore_get_earnings_by_order( $object->get_id() );

		return array(
			'id'                   => $object->get_id(),
			'parent_id'            => $data['parent_id'],
			'number'               => $data['number'],
			'order_key'            => $data['order_key'],
			'created_via'          => $data['created_via'],
			'version'              => $data['version'],
			'status'               => $data['status'],
			'currency'             => $data['currency'],
			'date_created'         => $data['date_created'],
			'date_created_gmt'     => $data['date_created_gmt'],
			'date_modified'        => $data['date_modified'],
			'date_modified_gmt'    => $data['date_modified_gmt'],
			'discount_total'       => $data['discount_total'],
			'discount_tax'         => $data['discount_tax'],
			'shipping_total'       => $data['shipping_total'],
			'shipping_tax'         => $data['shipping_tax'],
			'cart_tax'             => $data['cart_tax'],
			'total'                => wc_price( $data['total'] ),
			'total_tax'            => $data['total_tax'],
			'prices_include_tax'   => $data['prices_include_tax'],
			'customer_id'          => $data['customer_id'],
			'customer_ip_address'  => $data['customer_ip_address'],
			'customer_user_agent'  => $data['customer_user_agent'],
			'customer_note'        => $data['customer_note'],
			'billing'              => $data['billing'],
			'shipping'             => $data['shipping'],
			'payment_method'       => $data['payment_method'],
			'payment_method_title' => $data['payment_method_title'],
			'transaction_id'       => $data['transaction_id'],
			'date_paid'            => $data['date_paid'],
			'date_paid_gmt'        => $data['date_paid_gmt'],
			'date_completed'       => $data['date_completed'],
			'date_completed_gmt'   => $data['date_completed_gmt'],
			'cart_hash'            => $data['cart_hash'],
			'meta_data'            => $data['meta_data'],
			'line_items'           => $data['line_items'],
			'tax_lines'            => $data['tax_lines'],
			'shipping_lines'       => $data['shipping_lines'],
			'fee_lines'            => $data['fee_lines'],
			'coupon_lines'         => $data['coupon_lines'],
			'refunds'              => $data['refunds'],
			'downloadable_items'   => $customer_downloads,
			'earnings'             => wc_price( $earnings ),
		);
	}

	/**
	 * Expands an order item to get its data.
	 *
	 * @param WC_Order_item $item Item.
	 * @return array
	 */
	protected function get_order_item_data( $item ) {
		$data           = $item->get_data();
		$format_decimal = array( 'subtotal', 'subtotal_tax', 'total', 'total_tax', 'tax_total', 'shipping_tax_total' );

		// Format decimal values.
		foreach ( $format_decimal as $key ) {
			if ( isset( $data[ $key ] ) ) {
				$data[ $key ] = wc_format_decimal( $data[ $key ], '' );
			}
		}

		// Add SKU and PRICE to products.
		if ( is_callable( array( $item, 'get_product' ) ) ) {
			$data['sku']   = $item->get_product() ? $item->get_product()->get_sku() : null;
			$data['price'] = (float) ( $item->get_total() / max( 1, $item->get_quantity() ) );
		}

		// Format taxes.
		if ( ! empty( $data['taxes']['total'] ) ) {
			$taxes = array();

			foreach ( $data['taxes']['total'] as $tax_rate_id => $tax ) {
				$taxes[] = array(
					'id'       => $tax_rate_id,
					'total'    => $tax,
					'subtotal' => isset( $data['taxes']['subtotal'][ $tax_rate_id ] ) ? $data['taxes']['subtotal'][ $tax_rate_id ] : '',
				);
			}
			$data['taxes'] = $taxes;
		} elseif ( isset( $data['taxes'] ) ) {
			$data['taxes'] = array();
		}

		// Remove names for coupons, taxes and shipping.
		if ( isset( $data['code'] ) || isset( $data['rate_code'] ) || isset( $data['method_title'] ) ) {
			unset( $data['name'] );
		}

		// Remove props we don't want to expose.
		unset( $data['order_id'] );
		unset( $data['type'] );

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
				'href' => rest_url( sprintf( '/%s/%s/%d', $this->namespace, $this->rest_base, $object->get_id() ) ),
			),
			'collection' => array(
				'href' => rest_url( sprintf( '/%s/%s', $this->namespace, $this->rest_base ) ),
			),
		);

		if ( 0 !== (int) $object->get_customer_id() ) {
			$links['customer'] = array(
				'href' => rest_url( sprintf( '/%s/customers/%d', $this->namespace, $object->get_customer_id() ) ),
			);
		}

		if ( 0 !== (int) $object->get_parent_id() ) {
			$links['up'] = array(
				'href' => rest_url( sprintf( '/%s/orders/%d', $this->namespace, $object->get_parent_id() ) ),
			);
		}

		return $links;
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

		// phpcs:ignore WordPress.DateTime.CurrentTimeTimestamp.Requested, WordPress.DateTime.RestrictedFunctions.date_date
		$start_date = date( 'Y-m-d', strtotime( date( 'Ym', current_time( 'timestamp' ) ) . '01' ) );
		// phpcs:ignore WordPress.DateTime.CurrentTimeTimestamp.Requested, WordPress.DateTime.RestrictedFunctions.date_date
		$end_date          = date( 'Y-m-d', current_time( 'timestamp' ) );
		$current_month_arg = array(
			'date_created' => $start_date . '...' . $end_date,
		);
		$all               = count( superstore()->order->get_seller_orders( get_current_user_id() ) );
		$processing        = count( superstore()->order->get_seller_orders( get_current_user_id(), array( 'status' => 'wc-processing' ) ) );
		$on_hold           = count( superstore()->order->get_seller_orders( get_current_user_id(), array( 'status' => 'wc-on-hold' ) ) );
		$refunded          = count( superstore()->order->get_seller_orders( get_current_user_id(), array( 'status' => 'wc-refunded' ) ) );
		$pending           = count( superstore()->order->get_seller_orders( get_current_user_id(), array( 'status' => 'wc-pending' ) ) );
		$completed         = count( superstore()->order->get_seller_orders( get_current_user_id(), array( 'status' => 'wc-completed' ) ) );
		$cancelled         = count( superstore()->order->get_seller_orders( get_current_user_id(), array( 'status' => 'wc-cancelled' ) ) );
		$current_month     = count( superstore()->order->get_seller_orders( get_current_user_id(), $current_month_arg ) );

		$response->header( 'superstore-all', $all );
		$response->header( 'superstore-wc-processing', $processing );
		$response->header( 'superstore-wc-on-hold', $on_hold );
		$response->header( 'superstore-wc-refunded', $refunded );
		$response->header( 'superstore-wc-pending', $pending );
		$response->header( 'superstore-wc-completed', $completed );
		$response->header( 'superstore-wc-cancelled', $cancelled );
		$response->header( 'superstore-current_month', $current_month );

		return $response;
	}

	/**
	 * Prepare objects query
	 *
	 * @param WP_REST_Request $request Request.
	 * @return array
	 */
	protected function prepare_objects_query( $request ) {
		$args = parent::prepare_objects_query( $request );

		$filters = array();
		if ( isset( $request['filters'] ) && ! empty( $request['filters'] ) ) {
			$filters = $request['filters'];
		}

		$statuses = array();

		if ( ! empty( $filters ) && is_array( $filters ) ) {
			foreach ( $filters as $key => $value ) {
				if ( 'yes' === $value ) {
					$statuses[] = $key;
				}
			}
		}

		// Filter by post statuses.
		$args['post_status'] = ! empty( $statuses ) ? $statuses : $this->post_status;
		$args['order_by']    = 'date';
		$args['order']       = 'ASC';

		return $args;
	}
}
