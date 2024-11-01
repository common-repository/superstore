<?php

namespace Binarithm\Superstore\Localize;

/**
 * Superstore seller login localize data conroller class
 */
class SellerLogin {

	/**
	 * Class constructor
	 */
	public function __construct() {
		add_filter( 'superstore_frontend_localize_data', array( $this, 'add_data' ) );
	}

	/**
	 * Add data
	 *
	 * @param array $data Data.
	 * @return array
	 */
	public function add_data( $data ) {
		$registration_enabled = superstore_get_option( 'enable_registration', 'superstore_general', 'yes' );
		$data['account']      = array(
			'first_name'                   => __( 'First name' ),
			'last_name'                    => __( 'Last name' ),
			'store_name'                   => __( 'Store name' ),
			'store_nicename'               => __( 'Store url prefix' ),
			'email'                        => __( 'Email' ),
			'username'                     => __( 'Username' ),
			'email_username'               => __( 'Email or username' ),
			'password'                     => __( 'Password' ),
			'lost_password'                => __( 'Lost password' ),
			'login'                        => __( 'Login' ),
			'register'                     => __( 'Register' ),
			'registration_enabled'         => $registration_enabled,
			'become_seller'                => superstore_get_option( 'seller_register_url_text', 'superstore_general', 'Become a seller' ),
			'back_login'                   => __( 'Back to login' ),
			'remember'                     => __( 'Remember me' ),
			'tab_style_items'              => 'yes' === $registration_enabled ? array( __( 'Login', 'superstore' ), __( 'Register', 'superstore' ) ) : array( __( 'Login', 'superstore' ) ),
			'email_required'               => __( 'Email is required.', 'superstore' ),
			'password_required'            => __( 'Password is required.', 'superstore' ),
			'register_success'             => __( 'Account successfully created.', 'superstore' ),
			'lost_password_url'            => wc_lostpassword_url(),
			'stores_list_url'              => esc_url_raw( superstore_get_page_permalink( 'stores' ) . '/#/' ),
			'set_logged_in_after_register' => superstore_get_option( 'set_logged_in_after_registration', 'superstore_general', 'yes' ),
			'tab_style_form'               => superstore_get_option( 'tab_style_login_and_register_form', 'superstore_appearance', 'yes' ),
			'generate_username'            => superstore_get_option( 'registration_generate_username', 'superstore_general', 'yes' ),
			'generate_password'            => superstore_get_option( 'registration_generate_password', 'superstore_general', 'no' ),
			'nonce'                        => wp_create_nonce( 'woocommerce-login' ),
		);

		return apply_filters( 'superstore_seller_account_localize_data', $data );
	}
}
