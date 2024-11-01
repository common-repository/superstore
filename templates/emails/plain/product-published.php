<?php
/**
 * Superstore product published plain text email template to notify seller
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

esc_html_e( 'Your product is published now.', 'superstore' );
echo " \n";

esc_html_e( 'Product details are below:', 'superstore' );
echo "\n-----------\n\n";

/* translators: %s: Product name */
echo sprintf( esc_html__( 'Name: %s,', 'superstore' ), esc_html( $data['name'] ) );
echo " \n";

/* translators: %s: Product price */
echo sprintf( esc_html__( 'Price: %s,', 'superstore' ), esc_html( $data['price'] ) );
echo " \n";

/* translators: %s: Product edit link */
echo sprintf( esc_html__( 'Product edit url: %s,', 'superstore' ), esc_url( $data['product_edit_link'] ) );
echo " \n";

echo "\n\n----------------------------------------\n\n";
echo esc_url( $data['site_url'] );
echo " \n";

echo wp_kses_post( apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) ) );
