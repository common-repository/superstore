<?php

namespace Binarithm\Superstore\Payment;

use Binarithm\Superstore\DataStore\Controller as DataStore;

/**
 * Superstore payment controller class
 */
class Controller {

	/**
	 * Read, create, update, or delete (crud) a single payment
	 *
	 * @param bool|object $id Payment id.
	 * @return object
	 */
	public function crud_payment( $id = 0 ) {
		return new Payment( $id );
	}

	/**
	 * Read, create, update, or delete (crud) a single payment statement
	 *
	 * @param bool|object $id Payment statement id.
	 * @return object
	 */
	public function crud_payment_statement( $id = 0 ) {
		return new PaymentStatement( $id );
	}

	/**
	 * Read multiple payments
	 *
	 * @param array $args Filter payments.
	 * @return array
	 */
	public function get_payments( $args = array() ) {
		$args    = apply_filters( 'superstore_payment_query_args', $args );
		$results = DataStore::load( 'payment' )->query( $args );
		return apply_filters( 'superstore_payments', $results, $args );
	}

	/**
	 * Read multiple payment statements
	 *
	 * @param array $args Filter payment statements.
	 * @return array
	 */
	public function get_payment_statements( $args = array() ) {
		$args    = apply_filters( 'superstore_payment_statement_query_args', $args );
		$results = DataStore::load( 'payment_statement' )->query( $args );
		return apply_filters( 'superstore_payment_statements', $results, $args );
	}

	/**
	 * Get total payment requests count.
	 *
	 * @param array $args Args.
	 * @return int
	 */
	public function get_total_payments( $args = array() ) {
		$defaults = array( 'per_page' => -1 );
		$args     = wp_parse_args( $args, $defaults );
		$count    = count( $this->get_payments( $args ) );
		return $count;
	}

	/**
	 * Get total payment statements count.
	 *
	 * @param array $args Args.
	 * @return int
	 */
	public function get_total_payment_statements( $args = array() ) {
		$defaults = array( 'per_page' => -1 );
		$args     = wp_parse_args( $args, $defaults );
		$count    = count( $this->get_payment_statements( $args ) );
		return $count;
	}

	/**
	 * Get seller balance. If no seller get all sellers total balance.
	 *
	 * @param int  $args Filter balance.
	 * @param bool $formatted Add dollar sign and the number of decimals after
	 * the decimal point or not.
	 * @return float
	 */
	public function get_total_balance( $args = array(), $formatted = false ) {
		$exclude_statuses = superstore_get_option(
			'exclude_order_status_from_balance',
			'superstore_payment',
			array(
				'wc-completed'  => 'no',
				'wc-processing' => 'yes',
				'wc-pending'    => 'yes',
				'wc-on-hold'    => 'yes',
				'wc-cancelled'  => 'yes',
				'wc-failed'     => 'yes',
			)
		);
		$statuses         = array();
		foreach ( $exclude_statuses as $key => $exclude ) {
			if ( 'yes' === $exclude ) {
				$exclude_statuses[] = $key;
			}
		}
		$query       = $this->get_payment_statements( $args );
		$all_debits  = array();
		$all_credits = array();

		foreach ( $query as $obj ) {
			if ( in_array( $obj->get_status(), $exclude_statuses ) ) {
				continue;
			}
			$all_debits[]  = (float) $obj->get_debit();
			$all_credits[] = (float) $obj->get_credit();
		}

		$balance = array_sum( $all_debits ) - array_sum( $all_credits );

		if ( $formatted ) {
			$decimal = ( 0 === wc_get_price_decimals() ) ? 2 : wc_get_price_decimals();
			$balance = wc_price(
				$balance,
				array( 'decimals' => $decimal )
			);
		}

		return $balance;
	}

	/**
	 * Get total paid to seller. If no seller get all time payment amount that was sent to sellers.
	 *
	 * @param array $args Filter paid.
	 * @return float
	 */
	public function get_total_paid( $args = array() ) {
		$defaults = array( 'status' => 'approved' );
		$args     = wp_parse_args( $args, $defaults );
		$payments = superstore()->payment->get_payments( $args );
		$paid     = 0;

		foreach ( $payments as $payment ) {
			$paid += $payment->get_amount();
		}

		return (float) $paid;
	}
}
