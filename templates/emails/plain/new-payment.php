<?php
/**
 * Superstore new payment withdraw plain text email template to notify admin
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

esc_html_e( 'Request details are below:', 'superstore' );
echo "\n-----------\n\n";

/* translators: %s: Store name */
echo sprintf( esc_html__( 'Store name: %s,', 'superstore' ), esc_html( $data['store_name'] ) );
echo " \n";
/* translators: %s: Seller store_url */
echo sprintf( esc_html__( 'Store profile url: %s,', 'superstore' ), esc_url( $data['store_profile_url'] ) );
echo " \n";

/* translators: %s: Amount */
echo sprintf( esc_html__( 'Amount: %s,', 'superstore' ), esc_html( $data['amount'] ) );
echo " \n";

/* translators: %s: Method */
echo sprintf( esc_html__( 'Method: %s,', 'superstore' ), esc_html( $data['method'] ) );
echo " \n";

/* translators: %s: Request list */
echo sprintf( esc_html__( 'Requests list: %s,', 'superstore' ), esc_url( $data['requests_list'] ) );
echo " \n";

echo "\n\n----------------------------------------\n\n";
echo esc_url( $data['site_url'] );
echo " \n";

echo wp_kses_post( apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) ) );
