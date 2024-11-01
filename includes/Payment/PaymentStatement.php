<?php

namespace Binarithm\Superstore\Payment;

use Exception;
use Binarithm\Superstore\Abstracts\AbstractData;
use Binarithm\Superstore\DataStore\Controller as DataStore;

/**
 * Superstore payment statement class
 */
class PaymentStatement extends AbstractData {

	/**
	 * Stores payment statements data.
	 *
	 * @var array
	 */
	protected $data = array(
		'user_id'      => 0,
		'txn_id'       => 0,
		'debit'        => 0,
		'credit'       => 0,
		'type'         => '',
		'status'       => '',
		'txn_date'     => '',
		'date_created' => '',
	);

	/**
	 * Payment statements constructor.
	 *
	 * @param int|obj $data Data.
	 */
	public function __construct( $data = 0 ) {
		parent::__construct( $data );

		if ( $data instanceof self ) {
			$this->set_id( absint( $data->ID ) );
		} elseif ( is_numeric( $data ) ) {
			$this->set_id( $data );
		}

		$this->data_store = DataStore::load( 'payment_statement' );

		// If we have an ID, load the request from the DB.
		if ( $this->get_id() ) {
			try {
				$this->data_store->read( $this );
			} catch ( Exception $e ) {
				$this->set_id( 0 );
				$this->set_object_read( true );
			}
		} else {
			$this->set_object_read( true );
		}

	}

	/**
	 * Set value.
	 *
	 * @param string $value Value.
	 */
	public function set_user_id( $value ) {
		$this->set_prop( 'user_id', $value );
	}

	/**
	 * Set value.
	 *
	 * @param string $value Value.
	 */
	public function set_debit( $value ) {
		$this->set_prop( 'debit', $value );
	}

	/**
	 * Set value.
	 *
	 * @param string $value Value.
	 */
	public function set_credit( $value ) {
		$this->set_prop( 'credit', $value );
	}

	/**
	 * Set value.
	 *
	 * @param string $value Value.
	 */
	public function set_type( $value ) {
		$this->set_prop( 'type', $value );
	}

	/**
	 * Set value.
	 *
	 * @param string $value Value.
	 */
	public function set_status( $value ) {
		$this->set_prop( 'status', $value );
	}

	/**
	 * Set value.
	 *
	 * @param string $value Value.
	 */
	public function set_txn_id( $value ) {
		$this->set_prop( 'txn_id', $value );
	}

	/**
	 * Set value.
	 *
	 * @param string $value Value.
	 */
	public function set_txn_date( $value ) {
		$this->set_prop( 'txn_date', $value );
	}

	/**
	 * Set value.
	 *
	 * @param string $value Value.
	 */
	public function set_date_created( $value ) {
		$this->set_prop( 'date_created', $value );
	}

	/**
	 * Get value.
	 *
	 * @param  string $context What the value is for. Valid values are 'view' and 'edit'.
	 * @return string
	 */
	public function get_user_id( $context = 'view' ) {
		return $this->get_prop( 'user_id', $context );
	}

	/**
	 * Get value.
	 *
	 * @param  string $context What the value is for. Valid values are 'view' and 'edit'.
	 * @return string
	 */
	public function get_debit( $context = 'view' ) {
		return $this->get_prop( 'debit', $context );
	}

	/**
	 * Get value.
	 *
	 * @param  string $context What the value is for. Valid values are 'view' and 'edit'.
	 * @return string
	 */
	public function get_credit( $context = 'view' ) {
		return $this->get_prop( 'credit', $context );
	}

	/**
	 * Get value.
	 *
	 * @param  string $context What the value is for. Valid values are 'view' and 'edit'.
	 * @return string
	 */
	public function get_type( $context = 'view' ) {
		return $this->get_prop( 'type', $context );
	}

	/**
	 * Get value.
	 *
	 * @param  string $context What the value is for. Valid values are 'view' and 'edit'.
	 * @return string
	 */
	public function get_status( $context = 'view' ) {
		return $this->get_prop( 'status', $context );
	}

	/**
	 * Get value.
	 *
	 * @param  string $context What the value is for. Valid values are 'view' and 'edit'.
	 * @return string
	 */
	public function get_txn_id( $context = 'view' ) {
		return $this->get_prop( 'txn_id', $context );
	}

	/**
	 * Get value.
	 *
	 * @param  string $context What the value is for. Valid values are 'view' and 'edit'.
	 * @return string
	 */
	public function get_txn_date( $context = 'view' ) {
		return $this->get_prop( 'txn_date', $context );
	}

	/**
	 * Get value.
	 *
	 * @param  string $context What the value is for. Valid values are 'view' and 'edit'.
	 * @return string
	 */
	public function get_date_created( $context = 'view' ) {
		return $this->get_prop( 'date_created', $context );
	}
}

