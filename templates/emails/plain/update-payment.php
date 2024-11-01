<?php
/**
 * Superstore payment withdraw status change plain text email template to notify seller
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

if ( 'approved' === $data['status'] ) {
	esc_html_e( 'Thanks for being with us. The amount will be transferred to your preferred payment method shortly.', 'superstore' );
	echo " \n";
} elseif ( 'cancelled' === $data['status'] ) {
	esc_html_e( 'Payment withdraw is cancelled. Please recheck all valid requirements.', 'superstore' );
	echo " \n";
}

esc_html_e( 'Request details are below - ', 'superstore' );
echo "\n-----------\n\n";

/* translators: %s: Amount */
echo sprintf( esc_html__( 'Amount: %s,', 'superstore' ), $data['amount'] );
echo " \n";

/* translators: %s: method */
echo sprintf( esc_html__( 'Method: %s,', 'superstore' ), esc_html( $data['method'] ) );
echo " \n";

echo "\n\n----------------------------------------\n\n";
echo esc_url( $data['site_url'] );
echo " \n";

echo wp_kses_post( apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) ) );
