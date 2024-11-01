<?php
/**
 * Superstore contact seller plain text email template
 *
 * @package Superstore\Templates\Emails\Plain
 * @version 1.0
 */

defined( 'ABSPATH' ) || exit;

echo "=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n";
echo esc_html( wp_strip_all_tags( $email_heading ) );
echo "\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

esc_html_e( 'Customer details are below - ', 'superstore' );
echo "\n-----------\n\n";

/* translators: %s: Customer name */
echo sprintf( esc_html__( 'Name: %s,', 'superstore' ), esc_html( $data['customer_name'] ) );
echo " \n";

/* translators: %s: Customer email */
echo sprintf( esc_html__( 'Email: %s,', 'superstore' ), esc_html( $data['customer_email'] ) );
echo " \n";

/* translators: %s: Message */
echo sprintf( esc_html__( 'Message: %s,', 'superstore' ), esc_url( $data['customer_message'] ) );
echo " \n";

echo "\n\n----------------------------------------\n\n";
echo esc_url( $data['site_url'] );
echo " \n";

echo wp_kses_post( apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) ) );
