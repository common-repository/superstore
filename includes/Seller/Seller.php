<?php

namespace Binarithm\Superstore\Seller;

use Exception;
use Binarithm\Superstore\Abstracts\AbstractData;
use Binarithm\Superstore\DataStore\Controller as DataStore;

/**
 * Superstore seller class
 */
class Seller extends AbstractData {

	/**
	 * Stores a password if this needs to be changed. Write-only and hidden from _data.
	 *
	 * @var string
	 */
	protected $password = '';

	/**
	 * Stores seller data.
	 *
	 * @var array
	 */
	protected $data = array(
		'first_name'              => '',
		'last_name'               => '',
		'store_name'              => '',
		'store_url_nicename'      => '',
		'email'                   => '',
		'phone'                   => '',
		'address'                 => array(
			'country'  => '',
			'state'    => '',
			'postcode' => '',
			'city'     => '',
			'street_1' => '',
			'street_2' => '',
		),
		'enabled'                 => 'no',
		'withdraw_threshold_day'  => 0,
		'payment_method'          => array(
			'paypal_email'        => '',
			'skrill_email'        => '',
			'bank_ac_name'        => '',
			'bank_ac_number'      => '',
			'bank_name'           => '',
			'bank_address'        => '',
			'bank_routing_number' => '',
			'bank_iban'           => '',
			'bank_swift'          => '',
		),
		'requires_product_review' => 'yes',
		'date_created'            => '',
		'date_modified'           => '',
		'banner_id'               => 0,
		'profile_picture_id'      => 0,
		'display_name'            => '',
		'role'                    => 'superstore_seller',
		'user_login'              => '',
		'geolocation'             => array(
			'latitude'  => 0,
			'longitude' => 0,
		),
		'featured'                => 'no',
		'admin_commission'        => array(
			'type' => 'percentage', // percentage or flat.
			'rate' => null,
		),
		'store_time'              => array(
			'enabled'                 => 'no',
			'open_notice'             => 'Now open',
			'close_notice'            => 'Now closed',
			'off_day_notice'          => 'Off day',
			'open_24_hours_notice'    => 'Open 24 hours',
			'open_sunday'             => 'no',
			'open_monday'             => 'yes',
			'open_tuesday'            => 'yes',
			'open_wednesday'          => 'yes',
			'open_thursday'           => 'yes',
			'open_friday'             => 'yes',
			'open_saturday'           => 'yes',
			'sunday_opening_hours'    => '10:00-20:00',
			'monday_opening_hours'    => '10:00-20:00',
			'tuesday_opening_hours'   => '10:00-20:00',
			'wednesday_opening_hours' => '10:00-20:00',
			'thursday_opening_hours'  => '10:00-20:00',
			'friday_opening_hours'    => '10:00-20:00',
			'saturday_opening_hours'  => '10:00-20:00',
		),
		'tnc'                     => array( // Terms & conditions.
			'enabled' => 'no',
			'text'    => '',
		),
		'about'                   => '',
		'storage_limit'           => 1000, // Sets number in mb. Negative number is for unlimited.
		'show_on_store'           => array(
			'email'                 => 'yes',
			'phone'                 => 'yes',
			'address'               => 'yes',
			'map'                   => 'yes',
			'contact'               => 'yes',
			'about'                 => 'yes',
			'best_selling_products' => 'yes',
			'latest_products'       => 'yes',
			'top_rated_products'    => 'yes',
			'featured_products'     => 'yes',
		),
		'store_products_per_page' => 6,

		// Only readable.
		'store_url'               => '',
		'banner_src'              => '',
		'profile_picture_src'     => '',
	);

	/**
	 * Superstore seller contructor
	 *
	 * @param int|obj $data Data.
	 */
	public function __construct( $data = 0 ) {
		parent::__construct( $data );

		if ( $data instanceof WP_User ) {
			$this->set_id( absint( $data->ID ) );
		} elseif ( is_numeric( $data ) ) {
			$this->set_id( $data );
		}

		$this->data_store = DataStore::load( 'seller' );

		// If we have an ID, load the seller from the DB.
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
	 * Sets a prop for a setter method.
	 *
	 * @param string $prop    Name of prop to set.
	 * @param string $primary_key    Primary key.
	 * @param mixed  $value   Value of the prop.
	 */
	protected function set_custom_prop( $prop, $primary_key, $value ) {
		$this->changes[ $primary_key ][ $prop ] = $value;
		if ( array_key_exists( $prop, $this->data[ $primary_key ] ) ) {
			if ( true === $this->object_read ) {
				if ( $value !== $this->data[ $primary_key ][ $prop ] || array_key_exists( $prop, $this->changes[ $primary_key ] ) ) {
					$this->changes[ $primary_key ][ $prop ] = $value;
				}
			} else {
				$this->data[ $primary_key ][ $prop ] = $value;
			}
		}
	}

	/**
	 * Gets a prop for a getter method.
	 *
	 * @param  string $prop Name of prop to get.
	 * @param string $primary_key    Primary key.
	 * @param  string $context What the value is for. Valid values are 'view' and 'edit'. What the value is for. Valid values are view and edit.
	 * @return mixed
	 */
	protected function get_custom_prop( $prop, $primary_key, $context = 'view' ) {
		$value = null;

		if ( array_key_exists( $prop, $this->data[ $primary_key ] ) ) {
			if ( array_key_exists( $primary_key, $this->changes ) ) {
				if ( array_key_exists( $prop, $this->changes[ $primary_key ] ) ) {
					$value = $this->changes[ $primary_key ][ $prop ];
				} else {
					$value = $this->data[ $primary_key ][ $prop ];
				}
			} else {
				$value = $this->data[ $primary_key ][ $prop ];
			}

			if ( 'view' === $context ) {
				$value = apply_filters( $this->get_hook_prefix() . $primary_key . '_' . $prop, $value, $this );
			}
		}
		return $value;
	}

	/**
	 * Set seller's first name.
	 *
	 * @param string $value First name.
	 */
	public function set_first_name( $value ) {
		$this->set_prop( 'first_name', $value );
	}

	/**
	 * Set seller's last name.
	 *
	 * @param string $value Last name.
	 */
	public function set_last_name( $value ) {
		$this->set_prop( 'last_name', $value );
	}

	/**
	 * Set seller's user name.
	 *
	 * @param string $value user name.
	 */
	public function set_user_login( $value ) {
		$this->set_prop( 'user_login', $value );
	}

	/**
	 * Set seller's password.
	 *
	 * @param string $value user name.
	 */
	public function set_password( $value ) {
		$this->password = $value;
		return $value;
	}

	/**
	 * Set seller's email.
	 *
	 * @param string $value Email.
	 */
	public function set_email( $value ) {
		$this->set_prop( 'email', $value );
	}

	/**
	 * Set date created.
	 *
	 * @param string $value date.
	 */
	public function set_date_created( $value ) {
		$this->set_prop( 'date_created', $value );
	}

	/**
	 * Set date modified.
	 *
	 * @param string $value date.
	 */
	public function set_date_modified( $value ) {
		$this->set_prop( 'date_modified', $value );
	}

	/**
	 * Set display name.
	 *
	 * @param string $value Value.
	 */
	public function set_display_name( $value ) {
		$this->set_prop( 'display_name', $value );
	}

	/**
	 * Set store name.
	 *
	 * @param string $value Value.
	 */
	public function set_store_name( $value ) {
		$this->set_prop( 'store_name', $value );
	}

	/**
	 * Set store url nicename.
	 *
	 * @param string $value Value.
	 */
	public function set_store_url_nicename( $value ) {
		$this->set_prop( 'store_url_nicename', $value );
	}

	/**
	 * Set enabled.
	 *
	 * @param string $value Value.
	 */
	public function set_enabled( $value ) {
		$this->set_prop( 'enabled', $value );
	}

	/**
	 * Set phone number.
	 *
	 * @param string $value Value.
	 */
	public function set_phone( $value ) {
		$this->set_prop( 'phone', $value );
	}

	/**
	 * Set value.
	 *
	 * @param string $value Value.
	 */
	public function set_address_country( $value ) {
		$this->set_custom_prop( 'country', 'address', $value );
	}

	/**
	 * Set value.
	 *
	 * @param string $value Value.
	 */
	public function set_address_state( $value ) {
		$this->set_custom_prop( 'state', 'address', $value );
	}

	/**
	 * Set value.
	 *
	 * @param string $value Value.
	 */
	public function set_address_postcode( $value ) {
		$this->set_custom_prop( 'postcode', 'address', $value );
	}

	/**
	 * Set value.
	 *
	 * @param string $value Value.
	 */
	public function set_address_city( $value ) {
		$this->set_custom_prop( 'city', 'address', $value );
	}

	/**
	 * Set value.
	 *
	 * @param string $value Value.
	 */
	public function set_address_street_1( $value ) {
		$this->set_custom_prop( 'street_1', 'address', $value );
	}

	/**
	 * Set value.
	 *
	 * @param string $value Value.
	 */
	public function set_address_street_2( $value ) {
		$this->set_custom_prop( 'street_2', 'address', $value );
	}

	/**
	 * Set value.
	 *
	 * @param string $value Value.
	 */
	public function set_banner_id( $value ) {
		$this->set_prop( 'banner_id', $value );
	}

	/**
	 * Set value.
	 *
	 * @param string $value Value.
	 */
	public function set_profile_picture_id( $value ) {
		$this->set_prop( 'profile_picture_id', $value );
	}

	/**
	 * Set value.
	 *
	 * @param string $value Value.
	 */
	public function set_payment_method_paypal_email( $value ) {
		$this->set_custom_prop( 'paypal_email', 'payment_method', $value );
	}

	/**
	 * Set value.
	 *
	 * @param string $value Value.
	 */
	public function set_payment_method_skrill_email( $value ) {
		$this->set_custom_prop( 'skrill_email', 'payment_method', $value );
	}

	/**
	 * Set value.
	 *
	 * @param string $value Value.
	 */
	public function set_payment_method_bank_ac_name( $value ) {
		$this->set_custom_prop( 'bank_ac_name', 'payment_method', $value );
	}

	/**
	 * Set value.
	 *
	 * @param string $value Value.
	 */
	public function set_payment_method_bank_ac_number( $value ) {
		$this->set_custom_prop( 'bank_ac_number', 'payment_method', $value );
	}

	/**
	 * Set value.
	 *
	 * @param string $value Value.
	 */
	public function set_payment_method_bank_name( $value ) {
		$this->set_custom_prop( 'bank_name', 'payment_method', $value );
	}

	/**
	 * Set value.
	 *
	 * @param string $value Value.
	 */
	public function set_payment_method_bank_address( $value ) {
		$this->set_custom_prop( 'bank_address', 'payment_method', $value );
	}

	/**
	 * Set value.
	 *
	 * @param string $value Value.
	 */
	public function set_payment_method_bank_iban( $value ) {
		$this->set_custom_prop( 'bank_iban', 'payment_method', $value );
	}

	/**
	 * Set value.
	 *
	 * @param string $value Value.
	 */
	public function set_payment_method_bank_swift( $value ) {
		$this->set_custom_prop( 'bank_swift', 'payment_method', $value );
	}

	/**
	 * Set value.
	 *
	 * @param string $value Value.
	 */
	public function set_payment_method_bank_routing_number( $value ) {
		$this->set_custom_prop( 'bank_routing_number', 'payment_method', $value );
	}

	/**
	 * Set social media addresses.
	 *
	 * @param string $value Value.
	 */
	public function set_social_media( $value ) {
		$this->set_prop( 'social_media', $value );
	}

	/**
	 * Set requires review before product publishing.
	 *
	 * @param string $value Value.
	 */
	public function set_requires_product_review( $value ) {
		$this->set_prop( 'requires_product_review', $value );
	}

	/**
	 * Set value.
	 *
	 * @param string $value Value.
	 */
	public function set_admin_commission_rate( $value ) {
		$this->set_custom_prop( 'rate', 'admin_commission', $value );
	}

	/**
	 * Set value.
	 *
	 * @param string $value Value.
	 */
	public function set_admin_commission_type( $value ) {
		$this->set_custom_prop( 'type', 'admin_commission', $value );
	}

	/**
	 * Set value.
	 *
	 * @param string $value Value.
	 */
	public function set_withdraw_threshold_day( $value ) {
		$this->set_prop( 'withdraw_threshold_day', $value );
	}

	/**
	 * Set value.
	 *
	 * @param string $value Value.
	 */
	public function set_geolocation_latitude( $value ) {
		$this->set_custom_prop( 'latitude', 'geolocation', $value );
	}

	/**
	 * Set value.
	 *
	 * @param string $value Value.
	 */
	public function set_geolocation_longitude( $value ) {
		$this->set_custom_prop( 'longitude', 'geolocation', $value );
	}

	/**
	 * Set value.
	 *
	 * @param string $value Value.
	 */
	public function set_featured( $value ) {
		$this->set_prop( 'featured', $value );
	}

	/**
	 * Set value.
	 *
	 * @param string $value Value.
	 */
	public function set_store_time_enabled( $value ) {
		$this->set_custom_prop( 'enabled', 'store_time', $value );
	}

	/**
	 * Set value.
	 *
	 * @param string $value Value.
	 */
	public function set_store_time_open_notice( $value ) {
		$this->set_custom_prop( 'open_notice', 'store_time', $value );
	}

	/**
	 * Set value.
	 *
	 * @param string $value Value.
	 */
	public function set_store_time_close_notice( $value ) {
		$this->set_custom_prop( 'close_notice', 'store_time', $value );
	}

	/**
	 * Set value.
	 *
	 * @param string $value Value.
	 */
	public function set_store_time_off_day_notice( $value ) {
		$this->set_custom_prop( 'off_day_notice', 'store_time', $value );
	}

	/**
	 * Set value.
	 *
	 * @param string $value Value.
	 */
	public function set_store_time_open_24_hours_notice( $value ) {
		$this->set_custom_prop( 'open_24_hours_notice', 'store_time', $value );
	}

	/**
	 * Set value.
	 *
	 * @param string $value Value.
	 */
	public function set_store_time_open_sunday( $value ) {
		$this->set_custom_prop( 'open_sunday', 'store_time', $value );
	}

	/**
	 * Set value.
	 *
	 * @param string $value Value.
	 */
	public function set_store_time_open_monday( $value ) {
		$this->set_custom_prop( 'open_monday', 'store_time', $value );
	}

	/**
	 * Set value.
	 *
	 * @param string $value Value.
	 */
	public function set_store_time_open_tuesday( $value ) {
		$this->set_custom_prop( 'open_tuesday', 'store_time', $value );
	}
	/**
	 * Set value.
	 *
	 * @param string $value Value.
	 */
	public function set_store_time_open_wednesday( $value ) {
		$this->set_custom_prop( 'open_wednesday', 'store_time', $value );
	}

	/**
	 * Set value.
	 *
	 * @param string $value Value.
	 */
	public function set_store_time_open_thursday( $value ) {
		$this->set_custom_prop( 'open_thursday', 'store_time', $value );
	}

	/**
	 * Set value.
	 *
	 * @param string $value Value.
	 */
	public function set_store_time_open_friday( $value ) {
		$this->set_custom_prop( 'open_friday', 'store_time', $value );
	}

	/**
	 * Set value.
	 *
	 * @param string $value Value.
	 */
	public function set_store_time_open_saturday( $value ) {
		$this->set_custom_prop( 'open_saturday', 'store_time', $value );
	}

	/**
	 * Set value.
	 *
	 * @param string $value Value.
	 */
	public function set_store_time_sunday_opening_hours( $value ) {
		$this->set_custom_prop( 'sunday_opening_hours', 'store_time', $value );
	}

	/**
	 * Set value.
	 *
	 * @param string $value Value.
	 */
	public function set_store_time_monday_opening_hours( $value ) {
		$this->set_custom_prop( 'monday_opening_hours', 'store_time', $value );
	}

	/**
	 * Set value.
	 *
	 * @param string $value Value.
	 */
	public function set_store_time_tuesday_opening_hours( $value ) {
		$this->set_custom_prop( 'tuesday_opening_hours', 'store_time', $value );
	}

	/**
	 * Set value.
	 *
	 * @param string $value Value.
	 */
	public function set_store_time_wednesday_opening_hours( $value ) {
		$this->set_custom_prop( 'wednesday_opening_hours', 'store_time', $value );
	}

	/**
	 * Set value.
	 *
	 * @param string $value Value.
	 */
	public function set_store_time_thursday_opening_hours( $value ) {
		$this->set_custom_prop( 'thursday_opening_hours', 'store_time', $value );
	}

	/**
	 * Set value.
	 *
	 * @param string $value Value.
	 */
	public function set_store_time_friday_opening_hours( $value ) {
		$this->set_custom_prop( 'friday_opening_hours', 'store_time', $value );
	}

	/**
	 * Set value.
	 *
	 * @param string $value Value.
	 */
	public function set_store_time_saturday_opening_hours( $value ) {
		$this->set_custom_prop( 'saturday_opening_hours', 'store_time', $value );
	}

	/**
	 * Set value.
	 *
	 * @param string $value Value.
	 */
	public function set_tnc_enabled( $value ) {
		$this->set_custom_prop( 'enabled', 'tnc', $value );
	}

	/**
	 * Set value.
	 *
	 * @param string $value Value.
	 */
	public function set_tnc_text( $value ) {
		$this->set_custom_prop( 'text', 'tnc', $value );
	}

	/**
	 * Set value.
	 *
	 * @param string $value Value.
	 */
	public function set_show_on_store_email( $value ) {
		$this->set_custom_prop( 'email', 'show_on_store', $value );
	}

	/**
	 * Set value.
	 *
	 * @param string $value Value.
	 */
	public function set_show_on_store_phone( $value ) {
		$this->set_custom_prop( 'phone', 'show_on_store', $value );
	}

	/**
	 * Set value.
	 *
	 * @param string $value Value.
	 */
	public function set_show_on_store_address( $value ) {
		$this->set_custom_prop( 'address', 'show_on_store', $value );
	}

	/**
	 * Set value.
	 *
	 * @param string $value Value.
	 */
	public function set_show_on_store_map( $value ) {
		$this->set_custom_prop( 'map', 'show_on_store', $value );
	}

	/**
	 * Set value.
	 *
	 * @param string $value Value.
	 */
	public function set_show_on_store_contact( $value ) {
		$this->set_custom_prop( 'contact', 'show_on_store', $value );
	}

	/**
	 * Set value.
	 *
	 * @param string $value Value.
	 */
	public function set_show_on_store_about( $value ) {
		$this->set_custom_prop( 'about', 'show_on_store', $value );
	}

	/**
	 * Set value.
	 *
	 * @param string $value Value.
	 */
	public function set_show_on_store_best_selling_products( $value ) {
		$this->set_custom_prop( 'best_selling_products', 'show_on_store', $value );
	}

	/**
	 * Set value.
	 *
	 * @param string $value Value.
	 */
	public function set_show_on_store_latest_products( $value ) {
		$this->set_custom_prop( 'latest_products', 'show_on_store', $value );
	}

	/**
	 * Set value.
	 *
	 * @param string $value Value.
	 */
	public function set_show_on_store_top_rated_products( $value ) {
		$this->set_custom_prop( 'top_rated_products', 'show_on_store', $value );
	}

	/**
	 * Set value.
	 *
	 * @param string $value Value.
	 */
	public function set_show_on_store_featured_products( $value ) {
		$this->set_custom_prop( 'featured_products', 'show_on_store', $value );
	}

	/**
	 * Set value.
	 *
	 * @param string $value Value.
	 */
	public function set_about( $value ) {
		$this->set_prop( 'about', $value );
	}

	/**
	 * Set value.
	 *
	 * @param string $value Value.
	 */
	public function set_storage_limit( $value ) {
		// Value will be given in MB.
		$this->set_prop( 'storage_limit', $value );
	}

	/**
	 * Set value.
	 *
	 * @param string $value Value.
	 */
	public function set_store_products_per_page( $value ) {
		$this->set_prop( 'store_products_per_page', $value );
	}


	/**
	 * Return the seller's email.
	 *
	 * @param  string $context What the value is for. Valid values are 'view' and 'edit'.
	 * @return string
	 */
	public function get_email( $context = 'view' ) {
		return $this->get_prop( 'email', $context );
	}

	/**
	 * Return the seller's first name.
	 *
	 * @param  string $context What the value is for. Valid values are 'view' and 'edit'.
	 * @return string
	 */
	public function get_first_name( $context = 'view' ) {
		return $this->get_prop( 'first_name', $context );
	}

	/**
	 * Return the seller's last name.
	 *
	 * @param  string $context What the value is for. Valid values are 'view' and 'edit'.
	 * @return string
	 */
	public function get_last_name( $context = 'view' ) {
		return $this->get_prop( 'last_name', $context );
	}

	/**
	 * Return the seller's user name.
	 *
	 * @param  string $context What the value is for. Valid values are 'view' and 'edit'.
	 * @return string
	 */
	public function get_user_login( $context = 'view' ) {
		return $this->get_prop( 'user_login', $context );
	}

	/**
	 * Return the seller's password.
	 *
	 * @param  string $context What the value is for. Valid values are 'view' and 'edit'.
	 * @return string
	 */
	public function get_password( $context = 'view' ) {
		return $this->password;
	}

	/**
	 * Return the seller's role.
	 *
	 * @param  string $context What the value is for. Valid values are 'view' and 'edit'.
	 * @return string
	 */
	public function get_role( $context = 'view' ) {
		return $this->get_prop( 'role', $context );
	}

	/**
	 * Return the seller's display name.
	 *
	 * @param  string $context What the value is for. Valid values are 'view' and 'edit'.
	 * @return string
	 */
	public function get_display_name( $context = 'view' ) {
		return $this->get_prop( 'display_name', $context );
	}

	/**
	 * Return the seller's store name.
	 *
	 * @param  string $context What the value is for. Valid values are 'view' and 'edit'.
	 * @return string
	 */
	public function get_store_name( $context = 'view' ) {
		return $this->get_prop( 'store_name', $context );
	}

	/**
	 * Return the seller's store url nicename.
	 *
	 * @param  string $context What the value is for. Valid values are 'view' and 'edit'.
	 * @return string
	 */
	public function get_store_url_nicename( $context = 'view' ) {
		return $this->get_prop( 'store_url_nicename', $context );
	}

	/**
	 * Return the seller is enabled or not.
	 *
	 * @param  string $context What the value is for. Valid values are 'view' and 'edit'.
	 * @return string
	 */
	public function get_enabled( $context = 'view' ) {
		return $this->get_prop( 'enabled', $context );
	}

	/**
	 * Return the seller's phone number.
	 *
	 * @param  string $context What the value is for. Valid values are 'view' and 'edit'.
	 * @return string
	 */
	public function get_phone( $context = 'view' ) {
		return $this->get_prop( 'phone', $context );
	}

	/**
	 * Get value.
	 *
	 * @param  string $context What the value is for. Valid values are 'view' and 'edit'.
	 * @return string
	 */
	public function get_address( $context = 'view' ) {
		return $this->data['address'];
	}

	/**
	 * Get value.
	 *
	 * @param  string $context What the value is for. Valid values are 'view' and 'edit'.
	 * @return string
	 */
	public function get_address_country( $context = 'view' ) {
		return $this->get_custom_prop( 'country', 'address', $context );
	}

	/**
	 * Get value.
	 *
	 * @param  string $context What the value is for. Valid values are 'view' and 'edit'.
	 * @return string
	 */
	public function get_address_state( $context = 'view' ) {
		return $this->get_custom_prop( 'state', 'address', $context );
	}

	/**
	 * Get value.
	 *
	 * @param  string $context What the value is for. Valid values are 'view' and 'edit'.
	 * @return string
	 */
	public function get_address_postcode( $context = 'view' ) {
		return $this->get_custom_prop( 'postcode', 'address', $context );
	}

	/**
	 * Get value.
	 *
	 * @param  string $context What the value is for. Valid values are 'view' and 'edit'.
	 * @return string
	 */
	public function get_address_city( $context = 'view' ) {
		return $this->get_custom_prop( 'city', 'address', $context );
	}

	/**
	 * Get value.
	 *
	 * @param  string $context What the value is for. Valid values are 'view' and 'edit'.
	 * @return string
	 */
	public function get_address_street_1( $context = 'view' ) {
		return $this->get_custom_prop( 'street_1', 'address', $context );
	}

	/**
	 * Get value.
	 *
	 * @param  string $context What the value is for. Valid values are 'view' and 'edit'.
	 * @return string
	 */
	public function get_address_street_2( $context = 'view' ) {
		return $this->get_custom_prop( 'street_2', 'address', $context );
	}

	/**
	 * Get value.
	 *
	 * @param  string $context What the value is for. Valid values are 'view' and 'edit'.
	 * @return string
	 */
	public function get_show_on_store( $context = 'view' ) {
		return $this->data['show_on_store'];
	}

	/**
	 * Get value.
	 *
	 * @param  string $context What the value is for. Valid values are 'view' and 'edit'.
	 * @return string
	 */
	public function get_show_on_store_email( $context = 'view' ) {
		return $this->get_custom_prop( 'email', 'show_on_store', $context );
	}

	/**
	 * Get value.
	 *
	 * @param  string $context What the value is for. Valid values are 'view' and 'edit'.
	 * @return string
	 */
	public function get_show_on_store_phone( $context = 'view' ) {
		return $this->get_custom_prop( 'phone', 'show_on_store', $context );
	}

	/**
	 * Get value.
	 *
	 * @param  string $context What the value is for. Valid values are 'view' and 'edit'.
	 * @return string
	 */
	public function get_show_on_store_address( $context = 'view' ) {
		return $this->get_custom_prop( 'address', 'show_on_store', $context );
	}

	/**
	 * Get value.
	 *
	 * @param  string $context What the value is for. Valid values are 'view' and 'edit'.
	 * @return string
	 */
	public function get_show_on_store_map( $context = 'view' ) {
		return $this->get_custom_prop( 'map', 'show_on_store', $context );
	}

	/**
	 * Get value.
	 *
	 * @param  string $context What the value is for. Valid values are 'view' and 'edit'.
	 * @return string
	 */
	public function get_show_on_store_contact( $context = 'view' ) {
		return $this->get_custom_prop( 'contact', 'show_on_store', $context );
	}

	/**
	 * Get value.
	 *
	 * @param  string $context What the value is for. Valid values are 'view' and 'edit'.
	 * @return string
	 */
	public function get_show_on_store_about( $context = 'view' ) {
		return $this->get_custom_prop( 'about', 'show_on_store', $context );
	}

	/**
	 * Get value.
	 *
	 * @param  string $context What the value is for. Valid values are 'view' and 'edit'.
	 * @return string
	 */
	public function get_show_on_store_best_selling_products( $context = 'view' ) {
		return $this->get_custom_prop( 'best_selling_products', 'show_on_store', $context );
	}

	/**
	 * Get value.
	 *
	 * @param  string $context What the value is for. Valid values are 'view' and 'edit'.
	 * @return string
	 */
	public function get_show_on_store_latest_products( $context = 'view' ) {
		return $this->get_custom_prop( 'latest_products', 'show_on_store', $context );
	}

	/**
	 * Get value.
	 *
	 * @param  string $context What the value is for. Valid values are 'view' and 'edit'.
	 * @return string
	 */
	public function get_show_on_store_top_rated_products( $context = 'view' ) {
		return $this->get_custom_prop( 'top_rated_products', 'show_on_store', $context );
	}

	/**
	 * Get value.
	 *
	 * @param  string $context What the value is for. Valid values are 'view' and 'edit'.
	 * @return string
	 */
	public function get_show_on_store_featured_products( $context = 'view' ) {
		return $this->get_custom_prop( 'featured_products', 'show_on_store', $context );
	}

	/**
	 * Get value.
	 *
	 * @param  string $context What the value is for. Valid values are 'view' and 'edit'.
	 * @return string
	 */
	public function get_banner_id( $context = 'view' ) {
		return $this->get_prop( 'banner_id', $context );
	}

	/**
	 * Get value.
	 *
	 * @param  string $context What the value is for. Valid values are 'view' and 'edit'.
	 * @return string
	 */
	public function get_banner_src( $context = 'view' ) {
		$src                      = wp_get_attachment_image_src( (int) $this->get_banner_id() ) ? wp_get_attachment_image_src( (int) $this->get_banner_id() ) : array();
		$this->data['banner_src'] = current( $src );

		return $this->data['banner_src'];
	}
	/**
	 * Get value.
	 *
	 * @param  string $context What the value is for. Valid values are 'view' and 'edit'.
	 * @return string
	 */
	public function get_profile_picture_id( $context = 'view' ) {
		return $this->get_prop( 'profile_picture_id', $context );
	}

	/**
	 * Get value.
	 *
	 * @param  string $context What the value is for. Valid values are 'view' and 'edit'.
	 * @return string
	 */
	public function get_profile_picture_src( $context = 'view' ) {
		$src                               = wp_get_attachment_image_src( (int) $this->get_profile_picture_id() ) ? wp_get_attachment_image_src( (int) $this->get_profile_picture_id() ) : array();
		$this->data['profile_picture_src'] = current( $src );

		return $this->data['profile_picture_src'];
	}

	/**
	 * Get value.
	 *
	 * @param  string $context What the value is for. Valid values are 'view' and 'edit'.
	 * @return string
	 */
	public function get_payment_method( $context = 'view' ) {
		return $this->data['payment_method'];
	}

	/**
	 * Get value.
	 *
	 * @param  string $context What the value is for. Valid values are 'view' and 'edit'.
	 * @return string
	 */
	public function get_payment_method_paypal_email( $context = 'view' ) {
		return $this->get_custom_prop( 'paypal_email', 'payment_method', $context );
	}

	/**
	 * Get value.
	 *
	 * @param  string $context What the value is for. Valid values are 'view' and 'edit'.
	 * @return string
	 */
	public function get_payment_method_skrill_email( $context = 'view' ) {
		return $this->get_custom_prop( 'skrill_email', 'payment_method', $context );
	}

	/**
	 * Get value.
	 *
	 * @param  string $context What the value is for. Valid values are 'view' and 'edit'.
	 * @return string
	 */
	public function get_payment_method_bank_ac_name( $context = 'view' ) {
		return $this->get_custom_prop( 'bank_ac_name', 'payment_method', $context );
	}

	/**
	 * Get value.
	 *
	 * @param  string $context What the value is for. Valid values are 'view' and 'edit'.
	 * @return string
	 */
	public function get_payment_method_bank_ac_number( $context = 'view' ) {
		return $this->get_custom_prop( 'bank_ac_number', 'payment_method', $context );
	}

	/**
	 * Get value.
	 *
	 * @param  string $context What the value is for. Valid values are 'view' and 'edit'.
	 * @return string
	 */
	public function get_payment_method_bank_name( $context = 'view' ) {
		return $this->get_custom_prop( 'bank_name', 'payment_method', $context );
	}

	/**
	 * Get value.
	 *
	 * @param  string $context What the value is for. Valid values are 'view' and 'edit'.
	 * @return string
	 */
	public function get_payment_method_bank_address( $context = 'view' ) {
		return $this->get_custom_prop( 'bank_address', 'payment_method', $context );
	}

	/**
	 * Get value.
	 *
	 * @param  string $context What the value is for. Valid values are 'view' and 'edit'.
	 * @return string
	 */
	public function get_payment_method_bank_routing_number( $context = 'view' ) {
		return $this->get_custom_prop( 'bank_routing_number', 'payment_method', $context );
	}

	/**
	 * Get value.
	 *
	 * @param  string $context What the value is for. Valid values are 'view' and 'edit'.
	 * @return string
	 */
	public function get_payment_method_bank_iban( $context = 'view' ) {
		return $this->get_custom_prop( 'bank_iban', 'payment_method', $context );
	}

	/**
	 * Get value.
	 *
	 * @param  string $context What the value is for. Valid values are 'view' and 'edit'.
	 * @return string
	 */
	public function get_payment_method_bank_swift( $context = 'view' ) {
		return $this->get_custom_prop( 'bank_swift', 'payment_method', $context );
	}

	/**
	 * Return the seller's social media addresses.
	 *
	 * @param  string $context What the value is for. Valid values are 'view' and 'edit'.
	 * @return string
	 */
	public function get_social_media( $context = 'view' ) {
		return $this->get_prop( 'social_media', $context );
	}

	/**
	 * Return the seller requires product publishing admin review before product publish
	 *
	 * @param  string $context What the value is for. Valid values are 'view' and 'edit'.
	 * @return string
	 */
	public function get_requires_product_review( $context = 'view' ) {
		return $this->get_prop( 'requires_product_review', $context );
	}

	/**
	 * Get value.
	 *
	 * @param  string $context What the value is for. Valid values are 'view' and 'edit'.
	 * @return string
	 */
	public function get_admin_commission( $context = 'view' ) {
		return $this->data['admin_commission'];
	}

	/**
	 * Get value.
	 *
	 * @param  string $context What the value is for. Valid values are 'view' and 'edit'.
	 * @return string
	 */
	public function get_admin_commission_rate( $context = 'view' ) {
		return $this->get_custom_prop( 'rate', 'admin_commission', $context );
	}

	/**
	 * Get value.
	 *
	 * @param  string $context What the value is for. Valid values are 'view' and 'edit'.
	 * @return string
	 */
	public function get_admin_commission_type( $context = 'view' ) {
		return $this->get_custom_prop( 'type', 'admin_commission', $context );
	}

	/**
	 * Return value.
	 *
	 * @param  string $context What the value is for. Valid values are 'view' and 'edit'.
	 * @return string
	 */
	public function get_withdraw_threshold_day( $context = 'view' ) {
		return $this->get_prop( 'withdraw_threshold_day', $context );
	}

	/**
	 * Get value.
	 *
	 * @param  string $context What the value is for. Valid values are 'view' and 'edit'.
	 * @return string
	 */
	public function get_geolocation( $context = 'view' ) {
		return $this->data['geolocation'];
	}

	/**
	 * Get value.
	 *
	 * @param  string $context What the value is for. Valid values are 'view' and 'edit'.
	 * @return string
	 */
	public function get_geolocation_latitude( $context = 'view' ) {
		return $this->get_custom_prop( 'latitude', 'geolocation', $context );
	}

	/**
	 * Get value.
	 *
	 * @param  string $context What the value is for. Valid values are 'view' and 'edit'.
	 * @return string
	 */
	public function get_geolocation_longitude( $context = 'view' ) {
		return $this->get_custom_prop( 'longitude', 'geolocation', $context );
	}

	/**
	 * Return value.
	 *
	 * @param  string $context What the value is for. Valid values are 'view' and 'edit'.
	 * @return string
	 */
	public function get_featured( $context = 'view' ) {
		return $this->get_prop( 'featured', $context );
	}

	/**
	 * Get value.
	 *
	 * @param  string $context What the value is for. Valid values are 'view' and 'edit'.
	 * @return string
	 */
	public function get_store_time( $context = 'view' ) {
		return $this->data['store_time'];
	}

	/**
	 * Get value.
	 *
	 * @param  string $context What the value is for. Valid values are 'view' and 'edit'.
	 * @return string
	 */
	public function get_store_time_enabled( $context = 'view' ) {
		return $this->get_custom_prop( 'enabled', 'store_time', $context );
	}

	/**
	 * Get value.
	 *
	 * @param  string $context What the value is for. Valid values are 'view' and 'edit'.
	 * @return string
	 */
	public function get_store_time_open_notice( $context = 'view' ) {
		return $this->get_custom_prop( 'open_notice', 'store_time', $context );
	}

	/**
	 * Get value.
	 *
	 * @param  string $context What the value is for. Valid values are 'view' and 'edit'.
	 * @return string
	 */
	public function get_store_time_close_notice( $context = 'view' ) {
		return $this->get_custom_prop( 'close_notice', 'store_time', $context );
	}

	/**
	 * Get value.
	 *
	 * @param  string $context What the value is for. Valid values are 'view' and 'edit'.
	 * @return string
	 */
	public function get_store_time_off_day_notice( $context = 'view' ) {
		return $this->get_custom_prop( 'off_day_notice', 'store_time', $context );
	}

	/**
	 * Get value.
	 *
	 * @param  string $context What the value is for. Valid values are 'view' and 'edit'.
	 * @return string
	 */
	public function get_store_time_open_24_hours_notice( $context = 'view' ) {
		return $this->get_custom_prop( 'open_24_hours_notice', 'store_time', $context );
	}

	/**
	 * Get value.
	 *
	 * @param  string $context What the value is for. Valid values are 'view' and 'edit'.
	 * @return string
	 */
	public function get_store_time_open_sunday( $context = 'view' ) {
		return $this->get_custom_prop( 'open_sunday', 'store_time', $context );
	}

	/**
	 * Get value.
	 *
	 * @param  string $context What the value is for. Valid values are 'view' and 'edit'.
	 * @return string
	 */
	public function get_store_time_open_monday( $context = 'view' ) {
		return $this->get_custom_prop( 'open_monday', 'store_time', $context );
	}

	/**
	 * Get value.
	 *
	 * @param  string $context What the value is for. Valid values are 'view' and 'edit'.
	 * @return string
	 */
	public function get_store_time_open_tuesday( $context = 'view' ) {
		return $this->get_custom_prop( 'open_tuesday', 'store_time', $context );
	}

	/**
	 * Get value.
	 *
	 * @param  string $context What the value is for. Valid values are 'view' and 'edit'.
	 * @return string
	 */
	public function get_store_time_open_wednesday( $context = 'view' ) {
		return $this->get_custom_prop( 'open_wednesday', 'store_time', $context );
	}

	/**
	 * Get value.
	 *
	 * @param  string $context What the value is for. Valid values are 'view' and 'edit'.
	 * @return string
	 */
	public function get_store_time_open_thursday( $context = 'view' ) {
		return $this->get_custom_prop( 'open_thursday', 'store_time', $context );
	}

	/**
	 * Get value.
	 *
	 * @param  string $context What the value is for. Valid values are 'view' and 'edit'.
	 * @return string
	 */
	public function get_store_time_open_friday( $context = 'view' ) {
		return $this->get_custom_prop( 'open_friday', 'store_time', $context );
	}

	/**
	 * Get value.
	 *
	 * @param  string $context What the value is for. Valid values are 'view' and 'edit'.
	 * @return string
	 */
	public function get_store_time_open_saturday( $context = 'view' ) {
		return $this->get_custom_prop( 'open_saturday', 'store_time', $context );
	}

	/**
	 * Get value.
	 *
	 * @param  string $context What the value is for. Valid values are 'view' and 'edit'.
	 * @return string
	 */
	public function get_store_time_sunday_opening_hours( $context = 'view' ) {
		return $this->get_custom_prop( 'sunday_opening_hours', 'store_time', $context );
	}

	/**
	 * Get value.
	 *
	 * @param  string $context What the value is for. Valid values are 'view' and 'edit'.
	 * @return string
	 */
	public function get_store_time_monday_opening_hours( $context = 'view' ) {
		return $this->get_custom_prop( 'monday_opening_hours', 'store_time', $context );
	}

	/**
	 * Get value.
	 *
	 * @param  string $context What the value is for. Valid values are 'view' and 'edit'.
	 * @return string
	 */
	public function get_store_time_tuesday_opening_hours( $context = 'view' ) {
		return $this->get_custom_prop( 'tuesday_opening_hours', 'store_time', $context );
	}

	/**
	 * Get value.
	 *
	 * @param  string $context What the value is for. Valid values are 'view' and 'edit'.
	 * @return string
	 */
	public function get_store_time_wednesday_opening_hours( $context = 'view' ) {
		return $this->get_custom_prop( 'wednesday_opening_hours', 'store_time', $context );
	}
	/**
	 * Get value.
	 *
	 * @param  string $context What the value is for. Valid values are 'view' and 'edit'.
	 * @return string
	 */
	public function get_store_time_thursday_opening_hours( $context = 'view' ) {
		return $this->get_custom_prop( 'thursday_opening_hours', 'store_time', $context );
	}

	/**
	 * Get value.
	 *
	 * @param  string $context What the value is for. Valid values are 'view' and 'edit'.
	 * @return string
	 */
	public function get_store_time_friday_opening_hours( $context = 'view' ) {
		return $this->get_custom_prop( 'friday_opening_hours', 'store_time', $context );
	}

	/**
	 * Get value.
	 *
	 * @param  string $context What the value is for. Valid values are 'view' and 'edit'.
	 * @return string
	 */
	public function get_store_time_saturday_opening_hours( $context = 'view' ) {
		return $this->get_custom_prop( 'saturday_opening_hours', 'store_time', $context );
	}

	/**
	 * Get value.
	 *
	 * @param  string $context What the value is for. Valid values are 'view' and 'edit'.
	 * @return string
	 */
	public function get_tnc( $context = 'view' ) {
		return $this->data['tnc'];
	}

	/**
	 * Get value.
	 *
	 * @param  string $context What the value is for. Valid values are 'view' and 'edit'.
	 * @return string
	 */
	public function get_tnc_enabled( $context = 'view' ) {
		return $this->get_custom_prop( 'enabled', 'tnc', $context );
	}

	/**
	 * Get value.
	 *
	 * @param  string $context What the value is for. Valid values are 'view' and 'edit'.
	 * @return string
	 */
	public function get_tnc_text( $context = 'view' ) {
		return $this->get_custom_prop( 'text', 'tnc', $context );
	}

	/**
	 * Return value.
	 *
	 * @param  string $context What the value is for. Valid values are 'view' and 'edit'.
	 * @return string
	 */
	public function get_about( $context = 'view' ) {
		return $this->get_prop( 'about', $context );
	}

	/**
	 * Return value.
	 *
	 * @param  string $context What the value is for. Valid values are 'view' and 'edit'.
	 * @return string
	 */
	public function get_storage_limit( $context = 'view' ) {
		return $this->get_prop( 'storage_limit', $context );
	}

	/**
	 * Return value.
	 *
	 * @param  string $context What the value is for. Valid values are 'view' and 'edit'.
	 * @return string
	 */
	public function get_store_products_per_page( $context = 'view' ) {
		return $this->get_prop( 'store_products_per_page', $context );
	}

	/**
	 * Return value.
	 *
	 * @param  string $context What the value is for. Valid values are 'view' and 'edit'.
	 * @return string
	 */
	public function get_store_url( $context = 'view' ) {
		$this->data['store_url'] = superstore_get_store_url( $this->get_id() );

		return $this->data['store_url'];
	}
}
