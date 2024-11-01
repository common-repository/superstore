<?php

namespace Binarithm\Superstore\DataStore;

use Binarithm\Superstore\Exceptions\SuperstoreException;
use DateTime;
use WP_Error;
use Exception;

/**
 * Superstore payment data store
 */
class Payment {

	/**
	 * Data validation for creating new item.
	 *
	 * @param obj $obj Object.
	 * @return bool|WP_Error
	 */
	public function validate_before_create_item( $obj ) {

		if ( ! $obj->get_user_id() ) {
			return new WP_Error( 'superstore_withdraw_user_id_required', __( 'No user found', 'superstore' ) );
		}

		$enabled                        = get_user_meta( $obj->get_user_id(), 'superstore_enabled', true ) ? get_user_meta( $obj->get_user_id(), 'superstore_enabled', true ) : 'no';
		$disabled_ac_payment_permission = superstore_get_option( 'disabled_seller_can', 'superstore_seller', array( 'withdraw_payment' => 'no' ) );

		if ( ! current_user_can( 'manage_woocommerce' ) && 'yes' !== $enabled ) {
			if ( 'yes' !== $disabled_ac_payment_permission['withdraw_payment'] ) {
				return new WP_Error( 'superstore_withdraw_seller_not_enabled', __( 'Account is not enabled.', 'superstore' ) );
			}
		}

		$threshold = 0;
		if ( ! empty( get_user_meta( $obj->get_user_id(), 'superstore_withdraw_threshold_day', true ) ) ) {
			$threshold = (int) get_user_meta( $obj->get_user_id(), 'superstore_withdraw_threshold_day', true );
		} else {
			$threshold = (int) superstore_get_option( 'withdraw_threshold_day', 'superstore_payment', 0 );
		}
		$last_request = superstore()->payment->get_payments(
			array(
				'user_id'  => $obj->get_user_id(),
				'per_page' => 1,
			)
		);
		$last_date    = new DateTime( $last_request[0]->get_date_created() );
		$now          = new DateTime();
		if ( ! empty( $last_request ) && ! empty( $threshold ) && $last_date->diff( $now )->days <= $threshold ) {
			// phpcs:ignore WordPress.WP.I18n.MissingTranslatorsComment
			return new WP_Error( 'superstore_withdraw_threshold_day', sprintf( __( 'You have to wait %s days for next request', 'superstore' ), $threshold ) );
		}

		if ( empty( $obj->get_amount() ) ) {
			return new WP_Error( 'superstore_withdraw_amount_required', __( 'Amount is empty', 'superstore' ) );
		}

		if ( empty( $obj->get_method() ) ) {
			return new WP_Error( 'superstore_withdraw_method_required', __( 'Method is required', 'superstore' ) );
		}

		if ( empty( $obj->get_status() ) ) {
			return new WP_Error( 'superstore_withdraw_status_required', __( 'Status is required', 'superstore' ) );
		}

		$minimum_amount      = superstore_get_option( 'minimum_withdraw_amount', 'superstore_payment' );
		$maximum_amount      = superstore_get_option( 'maximum_withdraw_amount', 'superstore_payment' );
		$balance             = superstore()->payment->get_total_balance( array( 'user_id' => $obj->get_user_id() ) );
		$has_pending_request = superstore()->payment->get_payments(
			array(
				'user_id' => $obj->get_user_id(),
				'status'  => 'pending',
			)
		);

		if ( ! current_user_can( 'superstore_manage_payment' ) ) {
			return new WP_Error( 'superstore_withdraw_no_permission', __( 'User has no permission.', 'superstore' ) );
		}

		if ( ! $obj->get_id() ) {
			if ( $has_pending_request ) {
				return new WP_Error( 'superstore_withdraw_has_pending_request', __( 'A request already sent.', 'superstore' ) );
			}
		}

		if ( $obj->get_amount() > $balance ) {
			return new WP_Error( 'superstore_withdraw_not_enough_balance', __( 'Don\'t have enough balance.', 'superstore' ) );
		}

		if ( $minimum_amount && $obj->get_amount() < $minimum_amount ) {
			$frmt_price = wc_format_decimal( $minimum_amount, 2 );
			// phpcs:ignore WordPress.WP.I18n.MissingTranslatorsComment
			return new WP_Error( 'superstore_withdraw_minimum_amount', sprintf( __( 'Minimum withdraw amount is %s.', 'superstore' ), $frmt_price ) );
		}

		if ( $maximum_amount && $obj->get_amount() > $maximum_amount ) {
			$frmt_price = wc_format_decimal( $maximum_amount, 2 );
			// phpcs:ignore WordPress.WP.I18n.MissingTranslatorsComment
			return new WP_Error( 'superstore_withdraw_maximum_amount', sprintf( __( 'Maximum withdraw amount is %s.', 'superstore' ), $frmt_price ) );
		}

		if ( ! in_array( $obj->get_method(), superstore_get_seller_active_payment_methods( $obj->get_user_id() ), true ) ) {
			return new WP_Error( 'superstore_withdraw_method_not_active', __( 'Don\'t found any active payment methods', 'superstore' ) );
		}

		$allowed_methods = superstore_get_option(
			'allowed_payment_methods',
			'superstore_payment',
			array(
				'paypal' => 'yes',
				'skrill' => 'yes',
				'bank'   => 'yes',
			)
		);
		if ( 'yes' !== $allowed_methods[ $obj->get_method() ] ) {
			return new WP_Error( 'superstore_withdraw_method_not_available', __( 'This method is not currently available', 'superstore' ) );
		}

		if ( ! empty( $obj->get_type() ) ) {
			// phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
			if ( ! in_array( $obj->get_type(), array( 'request', 'schedule', 'instant' ) ) ) {
				return new WP_Error( 'superstore_withdraw_no_valid_status', __( 'Status must be request, schedule or instant.', 'superstore' ) );
			}
		}

		// phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
		if ( ! in_array( $obj->get_status(), array( 'pending', 'approved', 'cancelled' ) ) ) {
			return new WP_Error( 'superstore_withdraw_no_valid_status', __( 'Status must be pending, approved or cancel.', 'superstore' ) );
		}

		if ( ! current_user_can( 'manage_woocommerce' ) && 'approved' === $obj->get_status() ) {
			return new WP_Error( 'superstore_withdraw_no_permission_to_approve', __( 'User has no permission to send approved status request.', 'superstore' ) );
		}

		return true;
	}

	/**
	 * Data validation for updating item.
	 *
	 * @param obj $obj Object.
	 * @return bool|WP_Error
	 * @throws SuperstoreException On error.
	 */
	public function validate_before_update_item( $obj ) {
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$result = $wpdb->get_row(
			$wpdb->prepare(
				"select * from {$wpdb->superstore_payments} where id = %d",
				$obj->get_id()
			),
			ARRAY_A
		);

		if ( ! $result ) {
			throw new SuperstoreException( 'superstore_withdraw_id_not_exists', __( 'ID does not exist', 'superstore' ), 405 );
		}

		$payment = superstore()->payment->crud_payment( $obj->get_id() );

		if ( ! $obj->get_user_id() ) {
			return new WP_Error( 'superstore_withdraw_user_id_required', __( 'No user found', 'superstore' ) );
		}

		if ( empty( $obj->get_amount() ) ) {
			return new WP_Error( 'superstore_withdraw_amount_required', __( 'Amount is empty', 'superstore' ) );
		}

		if ( empty( $obj->get_method() ) ) {
			return new WP_Error( 'superstore_withdraw_method_required', __( 'Method is required', 'superstore' ) );
		}

		if ( empty( $obj->get_status() ) ) {
			return new WP_Error( 'superstore_withdraw_status_required', __( 'Status is required', 'superstore' ) );
		}

		if ( ! current_user_can( 'superstore_manage_payment' ) ) {
			return new WP_Error( 'superstore_withdraw_no_permission', __( 'User has no permission.', 'superstore' ) );
		}

		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			if ( (int) $payment->get_user_id() !== (int) $obj->get_user_id() ) {
				return new WP_Error( 'superstore_withdraw_update_only_own_request', __( 'You have no permission to edit other request.', 'superstore' ) );
			}
		}

		$enabled                        = get_user_meta( $obj->get_user_id(), 'superstore_enabled', true ) ? get_user_meta( $obj->get_user_id(), 'superstore_enabled', true ) : 'no';
		$disabled_ac_payment_permission = superstore_get_option( 'disabled_seller_can', 'superstore_seller', array( 'withdraw_payment' => 'no' ) );

		if ( ! current_user_can( 'manage_woocommerce' ) && 'yes' !== $enabled ) {
			if ( 'yes' !== $disabled_ac_payment_permission['withdraw_payment'] ) {
				return new WP_Error( 'superstore_withdraw_seller_not_enabled', __( 'Account is not enabled.', 'superstore' ) );
			}
		}

		if ( ! current_user_can( 'manage_woocommerce' ) && ! in_array( $obj->get_method(), superstore_get_seller_active_payment_methods( $obj->get_user_id() ), true ) ) {
			return new WP_Error( 'superstore_withdraw_method_not_active', __( 'Don\'t found any active payment methods.', 'superstore' ) );
		}

		if ( ! empty( $obj->get_type() ) ) {
			// phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
			if ( ! in_array( $obj->get_type(), array( 'request', 'schedule', 'instant' ) ) ) {
				return new WP_Error( 'superstore_withdraw_no_valid_status', __( 'Status must be request, schedule or instant.', 'superstore' ) );
			}
		}

		// phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
		if ( ! in_array( $obj->get_status(), array( 'pending', 'approved', 'cancelled' ) ) ) {
			return new WP_Error( 'superstore_withdraw_no_valid_status', __( 'Status must be pending, approved or cancel.', 'superstore' ) );
		}

		if ( ! current_user_can( 'manage_woocommerce' ) && 'approved' === $obj->get_status() ) {
			return new WP_Error( 'superstore_withdraw_no_permission_to_approve', __( 'User has no permission to send approved status request.', 'superstore' ) );
		}

		if ( ! current_user_can( 'manage_woocommerce' ) && 'pending' === $obj->get_status() ) {
			return new WP_Error( 'superstore_withdraw_no_permission_to_pending', __( 'User has no permission to change status pending.', 'superstore' ) );
		}

		if ( ! current_user_can( 'manage_woocommerce' ) && 'cancelled' === $obj->get_status() ) {
			if ( 'pending' !== $payment->get_status() ) {
				return new WP_Error( 'superstore_withdraw_cancel_request_non_pending', __( 'Can not cancel a non pending request', 'superstore' ) );
			}
		}

		return true;
	}

	/**
	 * Method to create a new object in the database.
	 *
	 * @param obj $obj Object.
	 * @throws SuperstoreException If unable to create new request.
	 */
	public function create( &$obj ) {
		$user_id = get_current_user_id();
		$status  = 'pending';
		$type    = 'request';
		$note    = '';

		if ( current_user_can( 'manage_woocommerce' ) ) {
			$user_id = $obj->get_user_id() ? $obj->get_user_id() : get_current_user_id();
			$type    = $obj->get_type() ? $obj->get_type() : 'request';
			$status  = $obj->get_status() ? $obj->get_status() : 'pending';
			$note    = $obj->get_note() ? $obj->get_note() : '';
		}

		$obj->set_user_id( $user_id );
		$obj->set_status( $status );
		$obj->set_type( $type );
		$obj->set_note( $note );

		$validate = $this->validate_before_create_item( $obj );

		if ( is_wp_error( $validate ) ) {
			throw new SuperstoreException( $validate->get_error_code(), $validate->get_error_message(), 405 );
		}

		global $wpdb;

		$args = array(
			'user_id'      => (int) $obj->get_user_id(),
			'amount'       => (float) $obj->get_amount(),
			'method'       => $obj->get_method(),
			'type'         => $obj->get_type(),
			'note'         => $obj->get_note(),
			'ip'           => $obj->get_ip() ? $obj->get_ip() : superstore_get_client_ip(),
			'status'       => $obj->get_status(),
			'date_created' => $obj->get_date_created() ? $obj->get_date_created() : current_time( 'mysql' ),
		);

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		$created = $wpdb->insert(
			$wpdb->superstore_payments,
			$args,
			array( '%d', '%f', '%s', '%s', '%s', '%s', '%s', '%s' )
		);

		if ( is_wp_error( $created ) || ! $created ) {
			throw new SuperstoreException( 'superstore_payment_creation_error', __( 'Error making payment request', 'superstore' ), 405 );
		}

		$id = (int) $wpdb->insert_id;
		$obj->set_id( $id );
		do_action( 'superstore_new_payment', $obj );
	}

	/**
	 * Method to read a object object.
	 *
	 * @param obj $obj Object.
	 * @throws SuperstoreException If obj or object not found.
	 */
	public function read( &$obj ) {
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$result = $wpdb->get_row(
			$wpdb->prepare(
				"select * from {$wpdb->superstore_payments} where id = %d",
				$obj->get_id()
			),
			ARRAY_A
		);

		if ( ! $result ) {
			throw new SuperstoreException( 'superstore_withdraw_id_not_exists', __( 'ID does not exist', 'superstore' ), 405 );
		}

		$store_name = get_user_meta( $obj->get_user_id(), 'superstore_store_name', true ) ? get_user_meta( $obj->get_user_id(), 'superstore_store_name', true ) : '';

		$props = array();
		$props = array(
			'user_id'      => $result['user_id'],
			'store_name'   => $store_name,
			'amount'       => $result['amount'],
			'method'       => $result['method'],
			'type'         => $result['type'],
			'note'         => $result['note'],
			'status'       => $result['status'],
			'ip'           => $result['ip'],
			'date_created' => $result['date_created'],
		);

		$obj->set_props( $props );

		$obj->set_object_read( true );
		do_action( 'superstore_payment_loaded', $obj );
	}

	/**
	 * Updates a object in the database.
	 *
	 * @param obj $obj Object.
	 * @throws SuperstoreException On error.
	 */
	public function update( &$obj ) {
		$user_id = get_current_user_id();
		$status  = $obj->get_status();
		$type    = 'request';
		$note    = '';

		if ( current_user_can( 'manage_woocommerce' ) ) {
			$user_id = $obj->get_user_id() ? $obj->get_user_id() : get_current_user_id();
			$type    = $obj->get_type() ? $obj->get_type() : 'request';
			$status  = $obj->get_status() ? $obj->get_status() : 'pending';
			$note    = $obj->get_note() ? $obj->get_note() : '';
		}

		$obj->set_user_id( $user_id );
		$obj->set_status( $status );
		$obj->set_type( $type );
		$obj->set_note( $note );

		$validate = $this->validate_before_update_item( $obj );
		if ( is_wp_error( $validate ) ) {
			throw new SuperstoreException( $validate->get_error_code(), $validate->get_error_message(), 405 );
		}

		global $wpdb;

		$args                 = array();
		$args['user_id']      = (int) $obj->get_user_id();
		$args['amount']       = (float) $obj->get_amount();
		$args['method']       = $obj->get_method();
		$args['type']         = $obj->get_type();
		$args['note']         = $obj->get_note();
		$args['status']       = $obj->get_status();
		$args['ip']           = $obj->get_ip();
		$args['date_created'] = $obj->get_date_created();

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->update(
			$wpdb->superstore_payments,
			$args,
			array(
				'id' => $obj->get_id(),
			),
			array( '%d', '%f', '%s', '%s', '%s', '%s', '%s', '%s' ),
			array( '%d' )
		);

		$obj->apply_changes();
		if ( 'approved' === $obj->get_status() ) {
			$this->update_seller_balance( $obj );
		}
		do_action( 'superstore_update_payment', $obj );
	}

	/**
	 * Update seller balance after approved request.
	 *
	 * @param object $obj Object.
	 */
	public function update_seller_balance( $obj ) {
		$statements = superstore()->payment->get_payment_statements( array( 'txn_id' => (int) $obj->get_id() ) );

		if ( empty( $statements ) ) {
			$statement_object = superstore()->payment->crud_payment_statement();
			$statement_object->set_user_id( (int) $obj->get_user_id() );
			$statement_object->set_txn_id( (int) $obj->get_id() );
			$statement_object->set_debit( (float) 0 );
			$statement_object->set_credit( (float) $obj->get_amount() );
			$statement_object->set_type( 'superstore_withdraw' );
			$statement_object->set_status( 'approved' );
			$statement_object->set_txn_date( $obj->get_date_created() );
			$statement_object->save();
		}
	}

	/**
	 * Deletes a object from the database.
	 *
	 * @param object $obj Object.
	 * @param array  $args Array of args to pass to the delete method.
	 * @throws SuperstoreException On error.
	 */
	public function delete( &$obj, $args = array() ) {
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$result = $wpdb->get_row(
			$wpdb->prepare(
				"select * from {$wpdb->superstore_payments} where id = %d",
				$obj->get_id()
			),
			ARRAY_A
		);

		if ( ! $result ) {
			throw new SuperstoreException( 'superstore_withdraw_id_not_exists', __( 'ID does not exist', 'superstore' ), 405 );
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$deleted = $wpdb->delete(
			$wpdb->superstore_payments,
			array( 'id' => $obj->get_id() ),
			array( '%d' )
		);

		if ( ! $deleted ) {
			return new WP_Error( 'superstore_payment_delete_error', __( 'Error deleting payment', 'superstore' ) );
		}

		do_action( 'superstore_delete_payment', $obj );
	}

	/**
	 * Filtered data.
	 *
	 * @param  array $query_vars Array of query.
	 */
	public function query( $query_vars = array() ) {
		global $wpdb;

		$args = $query_vars;

		$fields     = '*';
		$join       = '';
		$where      = '';
		$groupby    = '';
		$orderby    = 'ORDER BY date_created DESC';
		$limits     = '';
		$query_args = array( 1, 1 );

		if ( isset( $args['ids'] ) && is_array( $args['ids'] ) ) {
			$ids = array_map( 'absint', $args['ids'] );
			$ids = array_filter( $ids );

			$placeholders = array();
			foreach ( $ids as $id ) {
				$placeholders[] = '%d';
				$query_args[]   = $id;
			}

			$where .= ' AND id in ( ' . implode( ',', $placeholders ) . ' )';
		}

		if ( isset( $args['user_id'] ) ) {
			$where       .= ' AND user_id = %d';
			$query_args[] = $args['user_id'];
		}

		if ( isset( $args['method'] ) ) {
			$where       .= ' AND method = %s';
			$query_args[] = $args['method'];
		}

		if ( isset( $args['type'] ) ) {
			$where       .= ' AND type = %s';
			$query_args[] = $args['type'];
		}

		if ( isset( $args['status'] ) ) {
			$where       .= ' AND status = %s';
			$query_args[] = $args['status'];
		}

		if ( ! empty( $args['per_page'] ) && (int) $args['per_page'] > 0 ) {
			$limit  = $args['per_page'];
			$page   = isset( $args['page'] ) ? absint( $args['page'] ) : 1;
			$offset = ( $page - 1 ) * $limit;

			$limits       = 'LIMIT %d, %d';
			$query_args[] = $offset;
			$query_args[] = $limit;
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$withdraws = $wpdb->get_results(
			// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				"SELECT $fields FROM {$wpdb->superstore_payments} $join WHERE %d=%d $where $groupby $orderby $limits",
				...$query_args
			),
			ARRAY_A
		);

		$payments = array();

		if ( ! empty( $withdraws ) ) {
			foreach ( $withdraws as $withdraw ) {
				$payments[] = superstore()->payment->crud_payment( $withdraw['id'] );
			}
		}

		return $payments;
	}
}
