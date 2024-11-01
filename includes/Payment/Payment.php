<?php

namespace Binarithm\Superstore\Payment;

use Exception;
use Binarithm\Superstore\Abstracts\AbstractData;
use Binarithm\Superstore\DataStore\Controller as DataStore;

/**
 * Superstore payment class
 */
class Payment extends AbstractData {

	/**
	 * Stores payment data.
	 *
	 * @var array
	 */
	protected $data = array(
		'user_id'      => 0,
		'store_name'   => '',
		'amount'       => 0,
		'type'         => '',
		'date_created' => '',
		'status'       => '',
		'method'       => '',
		'note'         => '',
		'ip'           => '',
	);

	/**
	 * Payment constructor.
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

		$this->data_store = DataStore::load( 'payment' );

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
	public function set_amount( $value ) {
		$this->set_prop( 'amount', $value );
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
	public function set_method( $value ) {
		$this->set_prop( 'method', $value );
	}

	/**
	 * Set value.
	 *
	 * @param string $value Value.
	 */
	public function set_note( $value ) {
		$this->set_prop( 'note', $value );
	}

	/**
	 * Set value.
	 *
	 * @param string $value Value.
	 */
	public function set_ip( $value ) {
		$this->set_prop( 'ip', $value );
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
	 * Set value.
	 *
	 * @param string $value Value.
	 */
	public function set_store_name( $value ) {
		$this->set_prop( 'store_name', $value );
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
	public function get_amount( $context = 'view' ) {
		return $this->get_prop( 'amount', $context );
	}

	/**
	 * Get value.
	 *
	 * @param  string $context What the value is for. Valid values are 'view' and 'edit'.
	 * @return string
	 */
	public function get_store_name( $context = 'view' ) {
		return $this->get_prop( 'store_name', $context );
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
	public function get_method( $context = 'view' ) {
		return $this->get_prop( 'method', $context );
	}

	/**
	 * Get value.
	 *
	 * @param  string $context What the value is for. Valid values are 'view' and 'edit'.
	 * @return string
	 */
	public function get_note( $context = 'view' ) {
		return $this->get_prop( 'note', $context );
	}

	/**
	 * Get value.
	 *
	 * @param  string $context What the value is for. Valid values are 'view' and 'edit'.
	 * @return string
	 */
	public function get_ip( $context = 'view' ) {
		return $this->get_prop( 'ip', $context );
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
