<?php

namespace Binarithm\Superstore;

use WP_Error;
use Exception;
use Binarithm\Superstore\Exceptions\SuperstoreException;

/**
 * Superstore ajax class
 */
class Ajax {

	/**
	 * Class constructor
	 */
	public function __construct() {
		if ( is_admin() ) {
			add_action( 'wp_ajax_superstore_save_settings', array( $this, 'save_admin_settings' ), 10 );
			add_action( 'wp_ajax_superstore_close_admin_wizard', array( $this, 'close_admin_wizard' ) );
			add_action( 'wp_ajax_superstore_dismiss_upgrade_notice', array( $this, 'dismiss_upgrade_notice' ) );
		}
		add_action( 'wp_ajax_superstore_save_seller_settings', array( $this, 'save_seller_settings' ), 10 );
		add_action( 'wp_ajax_superstore_close_seller_wizard', array( $this, 'close_seller_wizard' ) );
		add_action( 'wp_ajax_superstore_upload_file', array( $this, 'upload_file' ), 10 );
		add_action( 'wp_ajax_nopriv_superstore_store_nicename_available', array( $this, 'check_availability' ), 10 );
		add_action( 'wp_ajax_superstore_get_seller_settings_values', array( $this, 'get_seller_settings_values' ), 10 );
		add_action( 'wp_ajax_superstore_change_password', array( $this, 'change_password' ), 10 );
		add_action( 'wp_ajax_superstore_logout_seller', array( $this, 'logout_seller' ), 10 );
		add_action( 'wp_ajax_superstore_export_order_csv', array( $this, 'export_order_csv' ), 10 );
		add_action( 'wp_ajax_superstore_contact_seller', array( $this, 'contact_seller' ) );
		add_action( 'wp_ajax_nopriv_superstore_contact_seller', array( $this, 'contact_seller' ) );
	}

	/**
	 * Save admin settings values
	 *
	 * @return void
	 * @throws SuperstoreException On error.
	 */
	public function save_admin_settings() {
		try {
			if ( ! current_user_can( 'manage_woocommerce' ) ) {
				throw new SuperstoreException( 'superstore_settings_unauthorized_operation', __( 'You are not allowed to save admin settings', 'superstore' ), 401 );
			}

			if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['nonce'] ), 'superstore_admin' ) ) {
				throw new SuperstoreException( 'superstore_settings_invalid_nonce', __( 'Nonce is not valid', 'superstore' ), 401 );
			}

			if ( ! isset( $_POST['section'] ) ) {
				throw new SuperstoreException( 'superstore_settings_no_section', __( '`section` parameter is required.', 'superstore' ), 401 );
			}

			$option_name = sanitize_text_field( wp_unslash( $_POST['section'] ) );
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$option_value = isset( $_POST['values'] ) ? wp_unslash( $_POST['values'] ) : array(); // Sanitized on line no 64, 67.
			$option_value = apply_filters( 'superstore_save_settings_value', $option_value, $option_name );
			$data         = get_option( $option_name ) ? get_option( $option_name ) : array();

			foreach ( $option_value as $key => $value ) {
				if ( is_array( $option_value[ $key ] ) ) {
					foreach ( $option_value[ $key ] as $key2 => $value2 ) {
						$data[ $key ][ $key2 ] = sanitize_text_field( $option_value[ $key ][ $key2 ] );
					}
				} else {
					$data[ $key ] = sanitize_text_field( $option_value[ $key ] );
				}
			}

			do_action( 'superstore_before_saving_settings', $option_name, $option_value );

			update_option( $option_name, $data );

			do_action( 'superstore_after_saving_settings', $option_name, $option_value );

			wp_send_json_success( array( 'message' => __( 'Settings has been saved successfully', 'superstore' ) ) );
		} catch ( Exception $e ) {
			$error_code = $e->getCode() ? $e->getCode() : 401;

			wp_send_json_error( new WP_Error( 'superstore_admin_settings_error', $e->getMessage() ), $error_code );
		}
	}

	/**
	 * Close the admin setup wizard.
	 */
	public function close_admin_wizard() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error( __( 'You have no permission to do this action', 'superstore' ) );
		}

		update_option( 'superstore_admin_setup_wizard_run', 'no' );

		wp_send_json_success();
	}

	/**
	 * Save seller settings value
	 *
	 * @return void
	 * @throws SuperstoreException On error.
	 */
	public function save_seller_settings() {
		try {
			if ( ! current_user_can( 'manage_superstore' ) ) {
				throw new SuperstoreException( 'superstore_seller_settings_unauthorized_operation', __( 'You are not authorized to perform this action.', 'superstore' ), 408 );
			}

			if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['nonce'] ), 'superstore_frontend' ) ) {
				throw new SuperstoreException( 'superstore_seller_settings_invalid_nonce', __( 'Nonce is not valid', 'superstore' ), 402 );
			}

			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$field_values = apply_filters( 'superstore_save_seller_settings', isset( $_POST['values'] ) ? wp_unslash( $_POST['values'] ) : array() ); // Sanitized in foreach loop.

			$obj = superstore()->seller->crud_seller( get_current_user_id() );

			foreach ( $obj->get_data() as $key => $value ) {
				if ( is_array( $value ) ) {
					foreach ( $value as $key2 => $value2 ) {
						$callable = 'set_' . $key . '_' . $key2;
						if ( is_callable( array( $obj, $callable ) ) ) {
							$field_key = $key . '_' . $key2;
							if ( isset( $field_values[ $field_key ] ) ) {
								$obj->{$callable}( sanitize_text_field( $field_values[ $field_key ] ) );
							}
						}
					}
				} else {
					if ( is_callable( array( $obj, "set_$key" ) ) ) {
						if ( isset( $field_values[ $key ] ) ) {
							$obj->{"set_$key"}( sanitize_text_field( $field_values[ $key ] ) );
						}

						if ( $field_values['banner_id'] && is_array( $field_values['banner_id'] ) ) {
							$obj->set_banner_id( sanitize_text_field( $field_values['banner_id']['id'] ) );
						}

						if ( $field_values['profile_picture_id'] && is_array( $field_values['profile_picture_id'] ) ) {
							$obj->set_profile_picture_id( sanitize_text_field( $field_values['profile_picture_id']['id'] ) );
						}
					}
				}
			}

			do_action( 'superstore_before_saving_seller_settings', get_current_user_id(), $field_values );

			$obj->save();

			do_action( 'superstore_after_saving_seller_settings', get_current_user_id(), $field_values );

			wp_send_json_success( __( 'Settings has been saved successfully.', 'superstore' ) );
		} catch ( Exception $e ) {
			$error_code = $e->getCode() ? $e->getCode() : 422;

			wp_send_json_error( new WP_Error( 'superstore_seller_settings_error', $e->getMessage() ), $error_code );
		}
	}

	/**
	 * Close the seller setup wizard.
	 */
	public function close_seller_wizard() {
		if ( ! current_user_can( 'manage_superstore' ) ) {
			wp_send_json_error( __( 'You have no permission to do this action', 'superstore' ) );
		}

		update_user_meta( get_current_user_id(), 'superstore_seller_setup_wizard_run', 'no' );

		wp_send_json_success();
	}

	/**
	 * Upload seller files
	 *
	 * @throws SuperstoreException On error.
	 */
	public function upload_file() {
		try {
			if ( ! current_user_can( 'manage_superstore' ) ) {
				throw new SuperstoreException( 'superstore_media_add_unauthorized_operation', __( 'You are not allowed to upload file', 'superstore' ), 409 );
			}

			if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['nonce'] ), 'superstore_frontend' ) ) {
				throw new SuperstoreException( 'superstore_media_add_invalid_nonce', __( 'Nonce is not valid', 'superstore' ), 409 );
			}

			$obj                              = superstore()->seller->crud_seller( get_current_user_id() );
			$disabled_ac_add_media_permission = superstore_get_option( 'disabled_seller_can', 'superstore_seller', array( 'add_media' => 'no' ) );

			if ( ! current_user_can( 'manage_woocommerce' ) ) {
				if ( 'yes' !== $obj->get_enabled() ) {
					if ( 'yes' !== $disabled_ac_add_media_permission['add_media'] ) {
						throw new SuperstoreException( 'superstore_media_add_seller_not_enabled', __( 'Account is not enabled', 'superstore' ), 409 );
					}
				}
			}

			$uploaded_file_size = isset( $_FILES['file']['size'] ) ? (float) sanitize_text_field( wp_unslash( $_FILES['file']['size'] ) ) : 0;
			$storage_occupied   = (float) superstore()->media->get_storage_occupied( array( 'author' => get_current_user_id() ) ) + $uploaded_file_size;
			$storage_available  = (float) superstore()->media->get_seller_storage_available( array( 'author' => get_current_user_id() ) );
			$storage_available  = -1 === (int) $storage_available ? 'unlimited' : $storage_available;
			$storage_limit      = (float) $obj->get_storage_limit() * 1000000;

			if ( 'unlimited' !== $storage_available ) {
				if ( $storage_limit <= $storage_occupied ) {
					throw new SuperstoreException( 'superstore_media_add_seller_not_enough_storage', __( 'Failed: Storage limit exceed.', 'superstore' ), 409 );
				}
			}

			if ( isset( $_FILES['file'] ) ) {
				$file_name = isset( $_FILES['file']['name'] ) ? sanitize_file_name( wp_unslash( $_FILES['file']['name'] ) ) : '';
				// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
				$file_temp_name = isset( $_FILES['file']['tmp_name'] ) ? sanitize_text_field( $_FILES['file']['tmp_name'] ) : '';
				// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
				$uploaded_file = wp_upload_bits( $file_name, null, file_get_contents( $file_temp_name ) );
			}

			$attachment = array(
				'post_mime_type' => $uploaded_file['type'],
				'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $uploaded_file['url'] ) ),
				'post_content'   => '',
				'post_status'    => 'inherit',
				'post_author'    => get_current_user_id(),
				'guid'           => $uploaded_file['url'],
			);

			$attachment_id = wp_insert_attachment( $attachment, $uploaded_file['file'] );
			$metadata      = wp_generate_attachment_metadata( $attachment_id, $uploaded_file['file'] );
			wp_update_attachment_metadata( $attachment_id, $metadata );

			wp_send_json_success(
				array(
					'message' => __( 'Successfully uploaded', 'superstore' ),
				)
			);
		} catch ( Exception $e ) {
			$error_code = $e->getCode() ? $e->getCode() : 409;

			wp_send_json_error( new WP_Error( 'superstore_media_delete_error', $e->getMessage() ), $error_code );
		}
	}

	/**
	 * Check store name prefix is available or not.
	 *
	 * @throws SuperstoreException On error.
	 */
	public function check_availability() {
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['nonce'] ), 'superstore_frontend' ) ) {
			wp_send_json_error( new WP_Error( 'superstore_store_name_check_invalid_nonce', __( 'Nonce is not valid', 'superstore' ) ), 408 );
		}

		$store_nicename = isset( $_POST['store_nicename'] ) ? sanitize_text_field( wp_unslash( $_POST['store_nicename'] ) ) : '';

		if ( get_user_by( 'slug', $store_nicename ) ) {
			wp_send_json_success( __( 'Not available', 'superstore' ) );
		} else {
			wp_send_json_success( __( 'Available', 'superstore' ) );
		}
	}

	/**
	 * Get seller settings values
	 *
	 * @return void
	 * @throws SuperstoreException On error.
	 */
	public function get_seller_settings_values() {
		try {
			if ( ! current_user_can( 'manage_superstore' ) ) {
				throw new SuperstoreException( 'superstore_seller_settings_unauthorized_operation', __( 'You are not a seller.', 'superstore' ), 408 );
			}

			if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['nonce'] ), 'superstore_frontend' ) ) {
				throw new SuperstoreException( 'superstore_seller_settings_invalid_nonce', __( 'Nonce is not valid', 'superstore' ), 408 );
			}

			$obj = superstore()->seller->crud_seller( get_current_user_id() );

			wp_send_json_success( $obj->get_data() );
		} catch ( Exception $e ) {
			$error_code = $e->getCode() ? $e->getCode() : 408;

			wp_send_json_error( new WP_Error( 'superstore_seller_settings_error', $e->getMessage() ), $error_code );
		}
	}

	/**
	 * Change seller password
	 *
	 * @throws SuperstoreException On error.
	 */
	public function change_password() {

		try {
			if ( ! current_user_can( 'manage_superstore' ) ) {
				throw new SuperstoreException( 'superstore_seller_settings_unauthorized_operation', __( 'You are not authorized to perform this action.', 'superstore' ), 408 );
			}

			if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['nonce'] ), 'superstore_frontend' ) ) {
				throw new SuperstoreException( 'superstore_seller_settings_invalid_nonce', __( 'Nonce is not valid', 'superstore' ), 408 );
			}

			$obj = superstore()->seller->crud_seller( get_current_user_id() );

			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
			$current_password = isset( $_POST['current_password'] ) ? $_POST['current_password'] : '';
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
			$confirm_new_password = isset( $_POST['confirm_new_password'] ) ? $_POST['confirm_new_password'] : '';

			if ( ! wp_check_password( $current_password, wp_get_current_user()->data->user_pass ) ) {
				throw new SuperstoreException( 'superstore_seller_settings_incorrect_current_password', __( 'Current password is incorrect', 'superstore' ), 408 );
			}

			if ( wp_check_password( $confirm_new_password, wp_get_current_user()->data->user_pass ) ) {
				throw new SuperstoreException( 'superstore_seller_settings_same_password', __( 'Choose a different password', 'superstore' ), 408 );
			}

			if ( ! isset( $confirm_new_password ) ) {
				throw new SuperstoreException( 'superstore_seller_settings_password_not_set', __( 'Password not set', 'superstore' ), 408 );
			}

			if ( empty( $confirm_new_password ) ) {
				throw new SuperstoreException( 'superstore_seller_settings_no_empty_password', __( 'Can not change empty password', 'superstore' ), 408 );
			}

			if ( current_user_can( 'manage_woocommerce' ) ) {
				$obj->set_password( $confirm_new_password );
			} else {
				if ( 'yes' === superstore_get_option( 'seller_can_edit_password', 'superstore_seller', 'yes' ) ) {
					$obj->set_password( $confirm_new_password );
				}
			}

			$obj->save();

			if ( 'yes' === superstore_get_option( 'require_login_after_change_password', 'superstore_seller', 'yes' ) ) {
				wp_logout();
			}

			wp_send_json_success( array( 'message' => __( 'Password has been changed successfully.', 'superstore' ) ) );
		} catch ( Exception $e ) {
			$error_code = $e->getCode() ? $e->getCode() : 408;

			wp_send_json_error( new WP_Error( 'superstore_seller_settings_change_password_error', $e->getMessage() ), $error_code );
		}
	}

	/**
	 * Logout seller
	 *
	 * @throws SuperstoreException On error.
	 */
	public function logout_seller() {
		try {
			if ( ! current_user_can( 'manage_superstore' ) ) {
				throw new SuperstoreException( 'superstore_seller_logout_unauthorized_operation', __( 'You are not authorized to perform this action.', 'superstore' ), 408 );
			}

			if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['nonce'] ), 'superstore_frontend' ) ) {
				throw new SuperstoreException( 'superstore_seller_logout_invalid_nonce', __( 'Nonce is not valid', 'superstore' ), 408 );
			}

			wp_logout();

			wp_send_json_success( array( 'message' => __( 'Successfully logged out.', 'superstore' ) ) );
		} catch ( Exception $e ) {
			$error_code = $e->getCode() ? $e->getCode() : 408;

			wp_send_json_error( new WP_Error( 'superstore_seller_logout_error', $e->getMessage() ), $error_code );
		}
	}

	/**
	 * Export seller orders csv file
	 *
	 * @throws SuperstoreException On error.
	 */
	public function export_order_csv() {
		try {
			if ( ! current_user_can( 'manage_superstore' ) ) {
				throw new SuperstoreException( 'superstore_csv_export_unauthorized_operation', __( 'You are not authorized to perform this action.', 'superstore' ), 409 );
			}

			if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['nonce'] ), 'superstore_frontend' ) ) {
				throw new SuperstoreException( 'superstore_csv_export_invalid_nonce', __( 'Nonce is not valid', 'superstore' ), 409 );
			}

			if ( ! isset( $_POST['ids'] ) ) {
				throw new SuperstoreException( 'superstore_csv_export_no_ids', __( 'No order id found', 'superstore' ), 409 );
			}

			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$ids = ! empty( $_POST['ids'] ) ? wp_unslash( $_POST['ids'] ) : array();

			$fp = fopen( 'php://output', 'w' );
			fputcsv( $fp, $this->get_order_export_csv_headers() );

			foreach ( $ids as $order_id ) {
				$line         = array();
				$order_owner  = (int) superstore_get_seller_by_order( $order_id )->get_id();
				$current_user = (int) get_current_user_id();

				if ( $current_user !== $order_owner ) {
					continue;
				}

				$order = wc_get_order( $order_id );

				if ( $order->get_meta( 'superstore_has_sub_order' ) ) {
					continue;
				}

				foreach ( $this->get_order_export_csv_headers() as $row_key => $label ) {
					switch ( $row_key ) {
						case 'order_id':
							$line[ $row_key ] = $order->get_id();
							break;
						case 'order_items':
							$line[ $row_key ] = $this->get_order_items_name( $order );
							break;
						case 'order_shipping':
							$line[ $row_key ] = $order->get_shipping_method();
							break;
						case 'order_shipping_cost':
							$line[ $row_key ] = $order->get_total_shipping();
							break;
						case 'order_payment_method':
							$line[ $row_key ] = $order->get_payment_method_title();
							break;
						case 'order_total':
							$line[ $row_key ] = $order->get_total();
							break;
						case 'earnings':
							$line[ $row_key ] = superstore_get_earnings_by_order( $order->get_id() );
							break;
						case 'order_status':
							$line[ $row_key ] = $order->get_status();
							break;
						case 'order_date':
							$line[ $row_key ] = wc_format_datetime( $order->get_date_created(), get_option( 'date_format' ) . ', ' . get_option( 'time_format' ) );
							break;

						// Billing details.
						case 'billing_company':
							$line[ $row_key ] = $order->get_billing_company();
							break;
						case 'billing_first_name':
							$line[ $row_key ] = $order->get_billing_first_name();
							break;
						case 'billing_last_name':
							$line[ $row_key ] = $order->get_billing_last_name();
							break;
						case 'billing_full_name':
							$line[ $row_key ] = $order->get_formatted_billing_full_name();
							break;
						case 'billing_email':
							$line[ $row_key ] = $order->get_billing_email();
							break;
						case 'billing_phone':
							$line[ $row_key ] = $order->get_billing_phone();
							break;
						case 'billing_address_1':
							$line[ $row_key ] = $order->get_billing_address_1();
							break;
						case 'billing_address_2':
							$line[ $row_key ] = $order->get_billing_address_2();
							break;
						case 'billing_city':
							$line[ $row_key ] = $order->get_billing_city();
							break;
						case 'billing_state':
							$line[ $row_key ] = $order->get_billing_state();
							break;
						case 'billing_postcode':
							$line[ $row_key ] = $order->get_billing_postcode();
							break;
						case 'billing_country':
							$line[ $row_key ] = $order->get_billing_country();
							break;

						// Shipping details.
						case 'shipping_company':
							$line[ $row_key ] = $order->get_shipping_company();
							break;
						case 'shipping_first_name':
							$line[ $row_key ] = $order->get_shipping_first_name();
							break;
						case 'shipping_last_name':
							$line[ $row_key ] = $order->get_shipping_last_name();
							break;
						case 'shipping_full_name':
							$line[ $row_key ] = $order->get_formatted_billing_full_name();
							break;
						case 'shipping_address_1':
							$line[ $row_key ] = $order->get_shipping_address_1();
							break;
						case 'shipping_address_2':
							$line[ $row_key ] = $order->get_shipping_address_2();
							break;
						case 'shipping_city':
							$line[ $row_key ] = $order->get_shipping_city();
							break;
						case 'shipping_state':
							$line[ $row_key ] = $order->get_shipping_state();
							break;
						case 'shipping_postcode':
							$line[ $row_key ] = $order->get_shipping_postcode();
							break;
						case 'shipping_country':
							$line[ $row_key ] = $order->get_shipping_country();
							break;

						// Customer details.
						case 'customer_ip':
							$line[ $row_key ] = $order->get_customer_ip_address();
							break;
						case 'customer_note':
							$line[ $row_key ] = $order->get_customer_note();
							break;

						default:
							$line[ $row_key ] = '';
							break;
					}
				}

				$line = apply_filters( 'superstore_order_export_csv_lines', $line, $order );

				fputcsv( $fp, $line );
			}

			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fclose
			fclose( $fp );

			exit();

		} catch ( Exception $e ) {
			$error_code = $e->getCode() ? $e->getCode() : 409;

			wp_send_json_error( new WP_Error( 'superstore_order_csv_export_error', $e->getMessage() ), $error_code );
		}
	}

	/**
	 * Get product items name list from order
	 *
	 * @param  object $order Order.
	 * @return string List of product names.
	 */
	public function get_order_items_name( $order ) {
		$product_names = '';
		$order_item    = $order->get_items();

		foreach ( $order_item as $product ) {
			$prodct_name[] = $product['name'];
		}

		$product_names = implode( ', ', $prodct_name );

		return $product_names;
	}

	/**
	 * CSV order headers for export
	 *
	 * @return array
	 */
	public function get_order_export_csv_headers() {
		$headers = array(
			'order_id'             => __( 'Order No', 'superstore' ),
			'order_items'          => __( 'Order Items', 'superstore' ),
			'order_shipping'       => __( 'Shipping method', 'superstore' ),
			'order_shipping_cost'  => __( 'Shipping Cost', 'superstore' ),
			'order_payment_method' => __( 'Payment method', 'superstore' ),
			'order_total'          => __( 'Order Total', 'superstore' ),
			'earnings'             => __( 'Earnings', 'superstore' ),
			'order_status'         => __( 'Order Status', 'superstore' ),
			'order_date'           => __( 'Order Date', 'superstore' ),
			'billing_company'      => __( 'Billing Company', 'superstore' ),
			'billing_first_name'   => __( 'Billing First Name', 'superstore' ),
			'billing_last_name'    => __( 'Billing Last Name', 'superstore' ),
			'billing_full_name'    => __( 'Billing Full Name', 'superstore' ),
			'billing_email'        => __( 'Billing Email', 'superstore' ),
			'billing_phone'        => __( 'Billing Phone', 'superstore' ),
			'billing_address_1'    => __( 'Billing Address 1', 'superstore' ),
			'billing_address_2'    => __( 'Billing Address 2', 'superstore' ),
			'billing_city'         => __( 'Billing City', 'superstore' ),
			'billing_state'        => __( 'Billing State', 'superstore' ),
			'billing_postcode'     => __( 'Billing Postcode', 'superstore' ),
			'billing_country'      => __( 'Billing Country', 'superstore' ),
			'shipping_company'     => __( 'Shipping Company', 'superstore' ),
			'shipping_first_name'  => __( 'Shipping First Name', 'superstore' ),
			'shipping_last_name'   => __( 'Shipping Last Name', 'superstore' ),
			'shipping_full_name'   => __( 'Shipping Full Name', 'superstore' ),
			'shipping_address_1'   => __( 'Shipping Address 1', 'superstore' ),
			'shipping_address_2'   => __( 'Shipping Address 2', 'superstore' ),
			'shipping_city'        => __( 'Shipping City', 'superstore' ),
			'shipping_state'       => __( 'Shipping State', 'superstore' ),
			'shipping_postcode'    => __( 'Shipping Postcode', 'superstore' ),
			'shipping_country'     => __( 'Shipping Country', 'superstore' ),
			'customer_ip'          => __( 'Customer IP', 'superstore' ),
			'customer_note'        => __( 'Customer Note', 'superstore' ),
		);

		return apply_filters( 'superstore_order_csv_export_headers', $headers );
	}

	/**
	 * Contact seller throw email from store page contact form
	 */
	public function contact_seller() {
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['nonce'] ), 'superstore_frontend' ) ) {
			wp_send_json_error( __( 'Not valid request.', 'superstore' ) );
		}

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$posted = isset( $_POST['values'] ) ? wp_unslash( $_POST['values'] ) : array(); // Sanitized on line no 623, 624, 625.

		if ( empty( $posted ) ) {
			wp_send_json_error( __( 'Empty values', 'superstore' ) );
		}

		$contact_name    = sanitize_text_field( $posted['name'] );
		$contact_email   = sanitize_email( $posted['email'] );
		$contact_message = wp_strip_all_tags( $posted['message'] );

		if ( empty( $contact_name ) ) {
			$message = __( 'Please provide your name.', 'superstore' );
			wp_send_json_error( $message );
		}

		$seller_id = isset( $_POST['seller_id'] ) ? sanitize_text_field( wp_unslash( $_POST['seller_id'] ) ) : 0;
		$seller    = get_user_by( 'id', (int) $seller_id );

		if ( ! $seller ) {
			$message = __( 'Contact seller not found!', 'superstore' );
			wp_send_json_error( $message );
		}

		do_action( 'superstore_contact_seller', $seller->user_email, $contact_name, $contact_email, $contact_message );

		$success = __( 'Message sent successfully!', 'superstore' );
		wp_send_json_success( $success );
	}

	/**
	 * Dismiss superstore pro upgrading admin notice.
	 */
	public function dismiss_upgrade_notice() {
		if ( ! current_user_can( 'manage_superstore' ) ) {
			wp_send_json_error( __( 'You have no permission to dismiss the notice', 'superstore' ) );
		}

		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['nonce'] ), 'superstore_admin' ) ) {
			wp_send_json_error( __( 'Nonce is not valid to dismiss the notice', 'superstore' ) );
		}

		if ( isset( $_POST['superstore_upgrade_notice_dismissed'] ) ) {
			$dismissed = (bool) sanitize_text_field( wp_unslash( $_POST['superstore_upgrade_notice_dismissed'] ) );

			update_option( 'superstore_upgrade_notice_dismissed', $dismissed );
			wp_send_json_success();
		}
	}
}
