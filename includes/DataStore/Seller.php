<?php

namespace Binarithm\Superstore\DataStore;

use Binarithm\Superstore\Exceptions\SuperstoreException;
use WP_Error;
use WP_User;
use Exception;
use WP_User_Query;

/**
 * Superstore seller data store class
 */
class Seller {

	/**
	 * Data validation for creating new item.
	 *
	 * @param obj $obj Object.
	 * @return bool|WP_Error
	 */
	public function validate_before_create_item( $obj ) {
		$valid_data = array();

		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			if ( 'yes' !== superstore_get_option( 'enable_registration', 'superstore_general', 'yes' ) ) {
				return new WP_Error( 'registration-not-enabled', __( 'Registration is not enabled', 'superstore' ) );
			}
		}

		if ( empty( $obj->get_email() ) ) {
			return new WP_Error( 'registration-error-email-required', __( 'Email address is required', 'superstore' ) );
		}

		if ( ! is_email( $obj->get_email() ) ) {
			return new WP_Error( 'registration-error-invalid-email', __( 'Please provide a valid email address', 'superstore' ) );
		}

		if ( email_exists( $obj->get_email() ) ) {
			return new WP_Error( 'registration-error-email-exists', apply_filters( 'superstore_registration_error_email_exists', __( 'An account is already registered with your email address. Please choose another.', 'superstore' ), $obj->get_email() ) );
		}

		if ( ! in_array( $obj->get_enabled(), array( 'yes', 'no' ), true ) ) {
			return new WP_Error( 'registration-error-invalid-enable', __( 'Enabled must be yes or no', 'superstore' ) );
		}

		if ( ! in_array( $obj->get_featured(), array( 'yes', 'no' ), true ) ) {
			return new WP_Error( 'registration-error-invalid-featured', __( 'Featured must be yes or no', 'superstore' ) );
		}

		if ( ! in_array( $obj->get_store_time_enabled(), array( 'yes', 'no' ), true ) ) {
			return new WP_Error( 'registration-error-invalid-store-time-enabled', __( 'Store time enabled must be yes or no', 'superstore' ) );
		}

		if ( ! in_array( $obj->get_store_time_open_sunday(), array( 'yes', 'no' ), true ) ) {
			return new WP_Error( 'registration-error-invalid-store-time-open-sunday', __( 'Store time open sunday must be yes or no', 'superstore' ) );
		}

		if ( ! in_array( $obj->get_store_time_open_monday(), array( 'yes', 'no' ), true ) ) {
			return new WP_Error( 'registration-error-invalid-store-time-open-monday', __( 'Store time open monday must be yes or no', 'superstore' ) );
		}

		if ( ! in_array( $obj->get_store_time_open_tuesday(), array( 'yes', 'no' ), true ) ) {
			return new WP_Error( 'registration-error-invalid-store-time-open-tuesday', __( 'Store time open tuesday must be yes or no', 'superstore' ) );
		}

		if ( ! in_array( $obj->get_store_time_open_wednesday(), array( 'yes', 'no' ), true ) ) {
			return new WP_Error( 'registration-error-invalid-store-time-open-wednesday', __( 'Store time open wednesday must be yes or no', 'superstore' ) );
		}

		if ( ! in_array( $obj->get_store_time_open_thursday(), array( 'yes', 'no' ), true ) ) {
			return new WP_Error( 'registration-error-invalid-store-time-open-thursday', __( 'Store time open thursday must be yes or no', 'superstore' ) );
		}

		if ( ! in_array( $obj->get_store_time_open_friday(), array( 'yes', 'no' ), true ) ) {
			return new WP_Error( 'registration-error-invalid-store-time-open-friday', __( 'Store time open friday must be yes or no', 'superstore' ) );
		}

		if ( ! in_array( $obj->get_store_time_open_saturday(), array( 'yes', 'no' ), true ) ) {
			return new WP_Error( 'registration-error-invalid-store-time-open-saturday', __( 'Store time open saturday must be yes or no', 'superstore' ) );
		}

		if ( ! in_array( $obj->get_requires_product_review(), array( 'yes', 'no' ), true ) ) {
			return new WP_Error( 'registration-error-invalid-requires-product-review', __( 'Requires product review must be yes or no', 'superstore' ) );
		}

		if ( ! in_array( $obj->get_tnc_enabled(), array( 'yes', 'no' ), true ) ) {
			return new WP_Error( 'registration-error-tnc-enabled', __( 'Tnc enabled must be yes or no', 'superstore' ) );
		}

		if ( ! in_array( $obj->get_admin_commission_type(), array( 'percentage', 'flat' ), true ) ) {
			return new WP_Error( 'registration-error-invalid-admin_commission_type', __( 'Admin commission type must be percentage or flat', 'superstore' ) );
		}

		if ( ! empty( $obj->get_banner_id() ) && ! wp_attachment_is_image( $obj->get_banner_id() ) ) {
			/* translators: %s: banner id */
			return new WP_Error( 'registration-error-invalid-banner-id', sprintf( __( '#%s is an invalid banner ID.', 'superstore' ), $attachment_id ) );
		}

		if ( ! empty( $obj->get_profile_picture_id() ) && ! wp_attachment_is_image( $obj->get_profile_picture_id() ) ) {
			/* translators: %s: profile picture id */
			return new WP_Error( 'registration-error-invalid-profile-picture-id', sprintf( __( '#%s is an invalid profile picture ID.', 'superstore' ), $attachment_id ) );
		}

		if ( current_user_can( 'manage_woocommerce' ) ) {
			$username = $obj->get_user_login() ? $obj->get_user_login() : wc_create_new_customer_username( $obj->get_email() );
		} else {
			if ( 'yes' === superstore_get_option( 'registration_generate_username', 'superstore_general', 'yes' ) && empty( $obj->get_user_login() ) ) {
				$username = wc_create_new_customer_username( $obj->get_email() );
			} else {
				$username = ! empty( $obj->get_user_login() ) ? $obj->get_user_login() : wc_create_new_customer_username( $obj->get_email() );
			}
		}

		$username = sanitize_user( $username );

		if ( empty( $username ) || ! validate_username( $username ) ) {
			return new WP_Error( 'registration-error-invalid-username', __( 'Please enter a valid account username.', 'superstore' ) );
		}

		if ( username_exists( $username ) ) {
			return new WP_Error( 'registration-error-username-exists', __( 'An account is already registered with that username. Please choose another.', 'superstore' ) );
		}

		$valid_data['username'] = $username;

		$store_exists = get_user_by( 'slug', $obj->get_store_url_nicename() );

		if ( $store_exists ) {
			return new WP_Error( 'registration-error-storeurl-exists', __( 'Store url already taken. Please choose another.', 'superstore' ) );
		}

		// Handle password creation.
		if ( current_user_can( 'manage_woocommerce' ) ) {
			$password = $obj->get_password() ? $obj->get_password() : wp_generate_password();
		} else {
			if ( 'yes' === superstore_get_option( 'registration_generate_password', 'superstore_general', 'no' ) && empty( $obj->get_password() ) ) {
				$password = wp_generate_password();
			} else {
				$password = $obj->get_password();
			}
		}

		if ( empty( $password ) ) {
			return new WP_Error( 'registration-error-missing-password', __( 'Please enter an account password.', 'superstore' ) );
		}

		$valid_data['password'] = $password;

		return $valid_data;
	}

	/**
	 * Data validation for updating item.
	 *
	 * @param obj $obj Object.
	 * @return bool|WP_Error
	 */
	public function validate_before_update_item( $obj ) {
		$old = superstore()->seller->crud_seller( $obj->get_id() );

		if ( empty( $obj->get_id() ) ) {
			return new WP_Error( 'update-error-no-id-found', __( 'An valid id is required to update seller', 'superstore' ) );
		}

		if ( ! current_user_can( 'manage_woocommerce' ) && (int) get_current_user_id() !== (int) $obj->get_id() ) {
			return new WP_Error( 'update-error-can-not-edit-other', __( 'You can not edit other sellers', 'superstore' ) );
		}

		if ( ! superstore_is_user_seller( get_current_user_id() ) ) {
			return new WP_Error( 'update-error-not-a-seller', __( 'You are not a seller', 'superstore' ) );
		}

		if ( $old->get_email() !== $obj->get_email() && $obj->get_email() ) {
			if ( ! is_email( $obj->get_email() ) ) {
				return new WP_Error( 'update-error-invalid-email', __( 'Please provide a valid email address.' ) );
			}

			if ( email_exists( $obj->get_email() ) ) {
				return new WP_Error( 'update-error-email-exists', __( 'An account is already registered with your email address. Please choose another one.' ) );
			}
		}

		if ( $old->get_store_url_nicename() !== $obj->get_store_url_nicename() && $obj->get_store_url_nicename() ) {
			$store_url_exists = get_user_by( 'slug', $obj->get_store_url_nicename() );

			if ( $store_url_exists ) {
				return new WP_Error( 'update-error-storename-exists', __( 'Store url already registered with this store url. Please choose another one.' ) );
			}
		}

		return true;
	}

	/**
	 * Data validation for updating item.
	 *
	 * @param obj $obj Object.
	 * @return bool|WP_Error
	 */
	public function validate_before_read_item( $obj ) {
		if ( ! get_userdata( $obj->get_id() ) ) {
			return new WP_Error( 'user-not-exists', __( 'This user does not exist', 'superstore' ) );
		}

		return true;
	}

	/**
	 * Method to create a new object in the database.
	 *
	 * @param obj $obj Object.
	 * @throws SuperstoreException If unable to create new item.
	 */
	public function create( &$obj ) {
		$validate = $this->validate_before_create_item( $obj );

		if ( is_wp_error( $validate ) ) {
			throw new SuperstoreException( $validate->get_error_code(), $validate->get_error_message(), 400 );
		}

		$new_seller_data = apply_filters(
			'superstore_new_seller_data',
			array(
				'user_login' => $validate['username'],
				'user_pass'  => $validate['password'],
				'user_email' => sanitize_email( $obj->get_email() ),
				'role'       => 'superstore_seller',
			)
		);

		$seller_id = wp_insert_user( $new_seller_data );

		if ( is_wp_error( $seller_id ) ) {
			return new WP_Error( 'registration-error-insert-user', __( 'Registration failed to insert user' ) );
		}

		$user_obj = new WP_User( $seller_id );

		$update_user = wp_update_user(
			apply_filters(
				'superstore_update_seller_args',
				array(
					'ID'            => $seller_id,
					'role'          => 'superstore_seller',
					'display_name'  => $obj->get_display_name(),
					'user_nicename' => $obj->get_store_url_nicename() ? $obj->get_store_url_nicename() : $user_obj->user_nicename,
					'first_name'    => wc_clean( $obj->get_first_name() ),
					'last_name'     => wc_clean( $obj->get_last_name() ),
				),
				$obj
			)
		);

		if ( is_wp_error( $update_user ) ) {
			return new WP_Error( 'registration-error-update-user', __( 'Registration failed to update user' ) );
		}

		$obj->set_id( $seller_id );
		$this->update_user_meta( $obj );

		do_action( 'superstore_new_seller', $obj );
	}

	/**
	 * Method to read a object.
	 *
	 * @param obj $obj Object.
	 * @throws SuperstoreException On error.
	 */
	public function read( &$obj ) {
		$validate = $this->validate_before_read_item( $obj );

		if ( is_wp_error( $validate ) ) {
			throw new SuperstoreException( $validate->get_error_code(), $validate->get_error_message(), 402 );
		}

		$user_obj = $obj->get_id() ? get_user_by( 'id', $obj->get_id() ) : false;

		// User object is required.
		if ( ! $user_obj || empty( $user_obj->ID ) ) {
			throw new SuperstoreException( 'invalid_user_object', __( 'Invalid seller.', 'superstore' ), 402 );
		}

		$user_meta = array();

		foreach ( $this->meta_keys_without_prefix() as $key ) {
			if ( get_user_meta( $obj->get_id(), "superstore_$key", true ) ) {
				$user_meta[ $key ] = get_user_meta( $obj->get_id(), "superstore_$key", true );
			}
		}

		$obj->set_props( $user_meta );

		$obj->set_props(
			array(
				'first_name'         => $user_obj->first_name,
				'last_name'          => $user_obj->last_name,
				'email'              => $user_obj->user_email,
				'user_login'         => $user_obj->user_login,
				'display_name'       => $user_obj->display_name,
				'date_created'       => $user_obj->user_registered,
				'date_modified'      => gmdate( 'Y-m-j H:i:s', (int) get_user_meta( $obj->get_id(), 'last_update', true ) ),
				'store_url_nicename' => $user_obj->user_nicename,
			)
		);

		// Set only readable props.
		$obj->get_store_url();
		$obj->get_banner_src();
		$obj->get_profile_picture_src();

		$obj->set_object_read( true );
		do_action( 'superstore_seller_loaded', $obj );
	}

	/**
	 * Updates a object in the database.
	 *
	 * @param obj $obj Object.
	 * @throws SuperstoreException On error.
	 */
	public function update( &$obj ) {
		$validate = $this->validate_before_update_item( $obj );
		if ( is_wp_error( $validate ) ) {
			throw new SuperstoreException( $validate->get_error_code(), $validate->get_error_message(), 401 );
		}

		$wp_user = new WP_User( $obj->get_id() );

		wp_update_user(
			apply_filters(
				'superstore_update_seller_args',
				array(
					'ID'            => $obj->get_id(),
					'display_name'  => ! empty( $obj->get_display_name() ) ? $obj->get_display_name() : $wp_user->display_name,
					'user_nicename' => ! empty( $obj->get_store_url_nicename() ) ? $obj->get_store_url_nicename() : $wp_user->user_nicename,
					'first_name'    => ! empty( $obj->get_first_name() ) ? wc_clean( $obj->get_first_name() ) : $wp_user->first_name,
					'last_name'     => ! empty( $obj->get_last_name() ) ? wc_clean( $obj->get_last_name() ) : $wp_user->last_name,
					'user_email'    => ! empty( $obj->get_email() ) ? $obj->get_email() : sanitize_email( $wp_user->user_email ),
				),
				$obj
			)
		);

		// Only update password if a new one was set with set_password.
		if ( $obj->get_password() ) {
			wp_update_user(
				array(
					'ID'        => $obj->get_id(),
					'user_pass' => $obj->get_password(),
				)
			);
			$obj->set_password( '' );
		}

		// Do no update admin role.
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_update_user(
				array(
					'ID'   => $obj->get_id(),
					'role' => ! empty( $obj->get_role() ) ? $obj->get_role() : 'superstore_seller',
				)
			);
		}

		$this->update_user_meta( $obj );
		$obj->apply_changes();
		do_action( 'superstore_update_seller', $obj );
	}

	/**
	 * Deletes a object from the database.
	 *
	 * @param obj   $obj Object.
	 * @param array $args Array of args to pass to the delete method.
	 * @throws SuperstoreException On error.
	 */
	public function delete( &$obj, $args = array() ) {
		if ( empty( $obj->get_id() ) ) {
			throw new SuperstoreException( 'delete-error-no-id-found', __( 'An valid id is required to delete seller', 'superstore' ), 402 );
		}

		$args = wp_parse_args(
			$args,
			array(
				'reassign' => 0,
			)
		);

		require_once ABSPATH . 'wp-admin/includes/user.php';
		wp_delete_user( $obj->get_id(), $args['reassign'] );

		do_action( 'superstore_delete_seller', $obj->get_id() );
	}

	/**
	 * Superstore seller meta keys. Prefix is `superstore`.
	 *
	 * @return array
	 */
	public function meta_keys_without_prefix() {
		$keys = array(
			'first_name',
			'last_name',
			'store_name',
			'store_url_nicename',
			'email',
			'phone',
			'address_country',
			'address_state',
			'address_postcode',
			'address_city',
			'address_street_1',
			'address_street_2',
			'enabled',
			'withdraw_threshold_day',
			'payment_method_paypal_email',
			'payment_method_skrill_email',
			'payment_method_bank_ac_name',
			'payment_method_bank_ac_number',
			'payment_method_bank_name',
			'payment_method_bank_address',
			'payment_method_bank_routing_number',
			'payment_method_bank_iban',
			'payment_method_bank_swift',
			'requires_product_review',
			'banner_id',
			'profile_picture_id',
			'display_name',
			'user_login',
			'geolocation_latitude',
			'geolocation_longitude',
			'featured',
			'admin_commission_rate',
			'admin_commission_type',
			'store_time_enabled',
			'store_time_open_notice',
			'store_time_close_notice',
			'store_time_off_day_notice',
			'store_time_open_24_hours_notice',
			'store_time_open_sunday',
			'store_time_open_monday',
			'store_time_open_tuesday',
			'store_time_open_wednesday',
			'store_time_open_thursday',
			'store_time_open_friday',
			'store_time_open_saturday',
			'store_time_sunday_opening_hours',
			'store_time_monday_opening_hours',
			'store_time_tuesday_opening_hours',
			'store_time_wednesday_opening_hours',
			'store_time_thursday_opening_hours',
			'store_time_friday_opening_hours',
			'store_time_saturday_opening_hours',
			'tnc_enabled',
			'tnc_text',
			'about',
			'storage_limit',
			'show_on_store_email',
			'show_on_store_phone',
			'show_on_store_address',
			'show_on_store_map',
			'show_on_store_contact',
			'show_on_store_about',
			'show_on_store_best_selling_products',
			'show_on_store_latest_products',
			'show_on_store_top_rated_products',
			'show_on_store_featured_products',
			'store_products_per_page',
		);

		return apply_filters( 'superstore_seller_meta_keys_without_prefix', $keys );
	}

	/**
	 * Helper method that updates all the meta for a seller. Used for update & create.
	 *
	 * @param obj $seller Seller object.
	 */
	private function update_user_meta( $seller ) {
		$updated_props                = array();
		$auto_enable                  = superstore_get_option( 'new_seller_auto_enable', 'superstore_general', 'no' );
		$auto_requires_product_review = superstore_get_option( 'new_seller_auto_requires_product_publishing_review', 'superstore_general', 'yes' );
		$default_hiding_capabilities  = array(
			'email'                 => 'yes',
			'phone'                 => 'yes',
			'address'               => 'yes',
			'map'                   => 'yes',
			'contact'               => 'no',
			'about'                 => 'no',
			'best_selling_products' => 'no',
			'latest_products'       => 'no',
			'top_rated_products'    => 'no',
			'featured_products'     => 'no',
		);
		$seller_can_hide              = superstore_get_option( 'seller_can_hide_on_store', 'superstore_seller', $default_hiding_capabilities );
		$allowed_methods              = superstore_get_option(
			'allowed_payment_methods',
			'superstore_payment',
			array(
				'paypal' => 'yes',
				'skrill' => 'yes',
				'bank'   => 'yes',
			)
		);

		foreach ( $this->meta_keys_without_prefix() as $meta_key ) {
			if ( current_user_can( 'manage_woocommerce' ) ) {
				$meta[ $meta_key ] = $seller->{"get_$meta_key"}( 'edit' );

				if ( 'enabled' === $meta_key ) {
					if ( ! get_user_meta( $seller->get_id(), 'superstore_enabled', true ) ) {
						if ( 'yes' === $auto_enable ) {
							$seller->set_enabled( 'yes' );
						}
					}
				}

				if ( 'requires_product_review' === $meta_key ) {
					if ( ! get_user_meta( $seller->get_id(), 'superstore_requires_product_review', true ) ) {
						if ( 'yes' !== $auto_requires_product_review ) {
							$seller->set_requires_product_review( 'no' );
						}
					}
				}

				if ( 'admin_commission_rate' === $meta_key ) {
					if ( ( $seller->get_admin_commission_rate() === null ) || ( $seller->get_admin_commission_rate() === '' ) || ( $seller->get_admin_commission_rate() === false ) ) {
						$seller->set_admin_commission_rate( null );
					}
				}

				if ( update_user_meta( $seller->get_id(), "superstore_$meta_key", $seller->{"get_$meta_key"}( 'edit' ) ) ) {
					$updated_props[] = $meta_key;
				}
			} else {
				if ( 'enabled' === $meta_key ) {
					if ( ! get_user_meta( $seller->get_id(), 'superstore_enabled', true ) ) {
						if ( 'yes' === $auto_enable ) {
							$seller->set_enabled( 'yes' );
						} else {
							$seller->set_enabled( 'no' );
						}
					} else {
						continue;
					}
				}

				if ( 'featured' === $meta_key ) {
					if ( ! get_user_meta( $seller->get_id(), 'superstore_featured', true ) ) {
						$seller->set_featured( 'no' );
					} else {
						continue;
					}
				}

				if ( 'requires_product_review' === $meta_key ) {
					if ( ! get_user_meta( $seller->get_id(), 'superstore_requires_product_review', true ) ) {
						if ( 'requires_product_review' === $meta_key && 'yes' !== $auto_requires_product_review ) {
							$seller->set_requires_product_review( 'no' );
						} else {
							$seller->set_requires_product_review( 'yes' );
						}
					} else {
						continue;
					}
				}

				if ( 'admin_commission_rate' === $meta_key ) {
					if ( ! get_user_meta( $seller->get_id(), 'superstore_admin_commission_rate', true ) ) {
						$seller->set_admin_commission_rate( null );
					} else {
						continue;
					}
				}

				if ( 'admin_commission_type' === $meta_key ) {
					if ( ! get_user_meta( $seller->get_id(), 'superstore_admin_commission_type', true ) ) {
						$seller->set_admin_commission_type( 'percentage' );
					} else {
						continue;
					}
				}

				if ( 'storage_limit' === $meta_key ) {
					if ( ! get_user_meta( $seller->get_id(), 'superstore_storage_limit', true ) ) {
						$seller->set_storage_limit( -1 );
					} else {
						continue;
					}
				}

				if ( 'withdraw_threshold_day' === $meta_key ) {
					if ( ! get_user_meta( $seller->get_id(), 'superstore_withdraw_threshold_day', true ) ) {
						$seller->set_withdraw_threshold_day( 0 );
					} else {
						continue;
					}
				}

				$show_on_store_key        = substr( $meta_key, 14 );
				$show_on_store_key        = 'show_on_store_' . $show_on_store_key;
				$show_on_store_hiding_key = str_replace( 'show_on_store_', '', $meta_key );
				if ( $show_on_store_key === $meta_key ) {
					if ( 'yes' !== $seller_can_hide[ $show_on_store_hiding_key ] ) {
						continue;
					}
				}

				if ( 'payment_method_paypal_email' === $meta_key ) {
					if ( 'yes' !== $allowed_methods['paypal'] ) {
						continue;
					}
				}

				if ( 'payment_method_skrill_email' === $meta_key ) {
					if ( 'yes' !== $allowed_methods['skrill'] ) {
						continue;
					}
				}

				$payment_method_bank_key = substr( $meta_key, 20 );
				$payment_method_bank_key = 'payment_method_bank_' . $payment_method_bank_key;
				if ( $payment_method_bank_key === $meta_key ) {
					if ( 'yes' !== $allowed_methods['bank'] ) {
						continue;
					}
				}

				if ( update_user_meta( $seller->get_id(), "superstore_$meta_key", $seller->{"get_$meta_key"}( 'edit' ) ) ) {
					$updated_props[] = $meta_key;
				}
			}
		}
	}

	/**
	 * Filtered data.
	 *
	 * @param  array $query_vars Array of query.
	 * @return array
	 */
	public function query( $query_vars = array() ) {
		$objects = array();

		$defaults = array(
			'role__in'       => array( 'superstore_seller' ),
			'role__not_in'   => 'Administrator',
			'offset'         => 0,
			'number'         => -1,
			'search_columns' => array(
				'user_login',
				'user_nicename',
				'user_email',
				'user_url',
			),
			'orderby'        => 'registered',
			'order'          => 'DESC',
		);

		$args = wp_parse_args( $query_vars, $defaults );

		$user_query = new WP_User_Query( $args );
		$results    = $user_query->get_results();

		foreach ( $results as $result ) {
			$objects[] = superstore()->seller->crud_seller( $result->ID );
		}

		return $objects;
	}
}
