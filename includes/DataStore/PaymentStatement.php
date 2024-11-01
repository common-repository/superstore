<?php

namespace Binarithm\Superstore\DataStore;

use Binarithm\Superstore\Exceptions\SuperstoreException;
use WP_Error;
use Exception;

/**
 * Superstore payment statement data store
 */
class PaymentStatement {

	/**
	 * Method to create a new object in the database.
	 *
	 * @param obj $obj Object.
	 * @throws SuperstoreException If unable to create new request.
	 */
	public function create( &$obj ) {
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		$created = $wpdb->insert(
			$wpdb->superstore_payment_statements,
			array(
				'user_id'      => (int) $obj->get_user_id(),
				'txn_id'       => (int) $obj->get_txn_id(),
				'debit'        => (float) $obj->get_debit(),
				'credit'       => (float) $obj->get_credit(),
				'type'         => $obj->get_type(),
				'status'       => $obj->get_status(),
				'txn_date'     => $obj->get_txn_date() ? $obj->get_txn_date() : current_time( 'mysql' ),
				'date_created' => $obj->get_date_created() ? $obj->get_date_created() : current_time( 'mysql' ),
			),
			array(
				'%d',
				'%d',
				'%f',
				'%f',
				'%s',
				'%s',
				'%s',
				'%s',
			)
		);

		if ( is_wp_error( $created ) ) {
			throw new SuperstoreException( 'superstore_payment_statement_creation_error', __( 'Error making payment statement', 'superstore' ), 406 );
		}

		$id = (int) $wpdb->insert_id;
		$obj->set_id( $id );
		do_action( 'superstore_new_payment_statement', $obj );
	}

	/**
	 * Method to read a object.
	 *
	 * @param obj $obj Object.
	 * @throws SuperstoreException If object not found.
	 */
	public function read( &$obj ) {
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$result = $wpdb->get_row(
			$wpdb->prepare(
				"select * from {$wpdb->superstore_payment_statements} where id = %d",
				$obj->get_id()
			),
			ARRAY_A
		);

		if ( ! $result ) {
			throw new SuperstoreException( 'superstore_statement_id_not_exists', __( 'ID does not exist', 'superstore' ), 406 );
		}

		$props = array();
		$props = array(
			'user_id'      => $result['user_id'],
			'txn_id'       => $result['txn_id'],
			'debit'        => $result['debit'],
			'credit'       => $result['credit'],
			'type'         => $result['type'],
			'status'       => $result['status'],
			'txn_date'     => $result['txn_date'],
			'date_created' => $result['date_created'],
		);

		$obj->set_props( $props );

		$obj->set_object_read( true );
		do_action( 'superstore_payment_statement_loaded', $obj );
	}

	/**
	 * Updates a in the database.
	 *
	 * @param obj $obj Object.
	 * @throws SuperstoreException On error.
	 */
	public function update( &$obj ) {
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$result = $wpdb->get_row(
			$wpdb->prepare(
				"select * from {$wpdb->superstore_payment_statements} where id = %d",
				$obj->get_id()
			),
			ARRAY_A
		);

		if ( ! $result ) {
			throw new SuperstoreException( 'superstore_statement_id_not_exists', __( 'ID does not exist', 'superstore' ), 406 );
		}

		$args                 = array();
		$args['debit']        = $obj->get_debit();
		$args['credit']       = $obj->get_credit();
		$args['type']         = $obj->get_type();
		$args['status']       = $obj->get_status();
		$args['txn_date']     = $obj->get_txn_date();
		$args['date_created'] = $obj->get_date_created();

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->update(
			$wpdb->superstore_payment_statements,
			$args,
			array(
				'id' => $obj->get_id(),
			),
			array( '%f', '%f', '%s', '%s', '%s', '%s' ),
			array( '%d' )
		);

		$obj->apply_changes();
		do_action( 'superstore_update_payment_statement', $obj );
	}

	/**
	 * Deletes a from the database.
	 *
	 * @param string|int $obj Object.
	 * @param array      $args Array of args to pass to the delete method.
	 * @throws SuperstoreException On error.
	 */
	public function delete( &$obj, $args = array() ) {
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$result = $wpdb->get_row(
			$wpdb->prepare(
				"select * from {$wpdb->superstore_payment_statements} where id = %d",
				$obj->get_id()
			),
			ARRAY_A
		);

		if ( ! $result ) {
			throw new SuperstoreException( 'superstore_statement_id_not_exists', __( 'ID does not exist', 'superstore' ), 406 );
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$deleted = $wpdb->delete(
			$wpdb->superstore_payment_statements,
			array( 'id' => $obj->get_id() ),
			array( '%d' )
		);

		if ( ! $deleted ) {
			return new WP_Error( 'superstore_payment_statement_delete_error', __( 'Error deleting payment statement', 'superstore' ) );
		}

		do_action( 'superstore_delete_payment_statement', $obj );
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

		if ( isset( $args['txn_id'] ) ) {
			$where       .= ' AND txn_id = %d';
			$query_args[] = $args['txn_id'];
		}

		if ( isset( $args['type'] ) ) {
			$where       .= ' AND type = %s';
			$query_args[] = $args['type'];
		}

		if ( isset( $args['status'] ) ) {
			if ( is_array( $args['status'] ) ) {
				$status_placeholder = substr( str_repeat( ',%s', count( $args['status'] ) ), 1 );

				foreach ( $args['status'] as $status ) {
					$query_args[] = $status;
				}
			} else {
				$status_placeholder = '%s';
				$query_args[]       = $args['status'];
			}
			$where .= ' AND status in ( ' . $status_placeholder . ' )';
		}

		if ( ! empty( $args['limit'] ) ) {
			$limit  = absint( $args['limit'] );
			$page   = absint( $args['page'] );
			$page   = $page ? $page : 1;
			$offset = ( $page - 1 ) * $limit;

			$limits       = 'LIMIT %d, %d';
			$query_args[] = $offset;
			$query_args[] = $limit;
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$statements = $wpdb->get_results(
			// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				"SELECT $fields FROM {$wpdb->superstore_payment_statements} $join WHERE %d=%d $where $groupby $orderby $limits",
				...$query_args
			),
			ARRAY_A
		);

		$filtered_statements = array();

		if ( ! empty( $statements ) ) {
			foreach ( $statements as $statement ) {
				$filtered_statements[] = superstore()->payment->crud_payment_statement( $statement['id'] );
			}
		}

		return $filtered_statements;
	}
}
