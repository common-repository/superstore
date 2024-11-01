<?php
/**
 * Superstore contact seller email
 *
 * @package Superstore\Templates\Emails
 * @version 1.0
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_email_header', $email_heading, $email ); ?>

<p><?php esc_html_e( 'Customer details are below - ', 'superstore' ); ?></p>

<?php /* translators: %s: Customer name */ ?>
<p><?php echo sprintf( esc_html__( 'Name: %s,', 'superstore' ), $data['customer_name'] ); ?></p>

<?php /* translators: %s: Customer email */ ?>
<p><?php echo sprintf( esc_html__( 'Email: %s,', 'superstore' ), esc_html( $data['customer_email'] ) ); ?></p>

<?php /* translators: %s: Message */ ?>
<p><?php echo sprintf( esc_html__( 'Message: %s,', 'superstore' ), $data['customer_message'] ); ?></p>

<strong><a href="<?php echo esc_url( $data['site_url'] ); ?>"><?php echo sprintf( esc_html__( '%s,', 'superstore' ), esc_html( $data['site_name'] ) ); ?></a></strong>

<?php do_action( 'woocommerce_email_footer', $email ); ?>
