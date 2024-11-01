<?php
/**
 * Superstore new seller plain text email template to notify admin
 *
 * @package Superstore\Templates\Emails\Plain
 * @version 1.0
 */

defined( 'ABSPATH' ) || exit;

echo "=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n";
echo esc_html( wp_strip_all_tags( $email_heading ) );
echo "\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

esc_html_e( 'Hi there,', 'superstore' );
echo " \n";

esc_html_e( 'A new seller has registered in your marketplace.', 'superstore' );
echo " \n";

esc_html_e( 'Seller details are below - ', 'superstore' );
echo "\n-----------\n\n";

/* translators: %s: Seller seller_name */
echo sprintf( esc_html__( 'Seller name: %s,', 'superstore' ), esc_html( $data['seller_name'] ) );
echo " \n";

/* translators: %s: Seller email */
echo sprintf( esc_html__( 'Email: %s,', 'superstore' ), esc_html( $data['email'] ) );
echo " \n";

/* translators: %s: Seller store_name */
echo sprintf( esc_html__( 'Store name: %s,', 'superstore' ), esc_html( $data['store_name'] ) );
echo " \n";

/* translators: %s: Seller store_url */
echo sprintf( esc_html__( 'Store url: %s,', 'superstore' ), esc_url( $data['store_url'] ) );
echo " \n";

/* translators: %s: Seller profile_url */
echo sprintf( esc_html__( 'Profile url: %s,', 'superstore' ), esc_url( $data['profile_url'] ) );
echo " \n";

echo "\n\n----------------------------------------\n\n";
echo esc_url( $data['site_url'] );
echo " \n";

echo wp_kses_post( apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) ) );
