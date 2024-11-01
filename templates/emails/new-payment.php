<?php
/**
 * Superstore new payment wihdraw email template to notify admin
 *
 * @package Superstore\Templates\Emails
 * @version 1.0
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_email_header', $email_heading, $email ); ?>

<p><?php esc_html_e( 'Hi there,', 'superstore' ); ?></p>

<p><?php esc_html_e( 'Request details are below - ', 'superstore' ); ?></p>

<?php /* translators: %s: Store name */ ?>
<p><a href="<?php echo esc_url( $data['store_profile_url'] ); ?>"><?php echo sprintf( esc_html__( 'Store name: %s,', 'superstore' ), esc_html( $data['store_name'] ) ); ?></a></p>

<?php /* translators: %s: Amount */ ?>
<p><?php echo sprintf( esc_html__( 'Amount: %s,', 'superstore' ), esc_html( $data['amount'] ) ); ?></p>

<?php /* translators: %s: Method */ ?>
<p><?php echo sprintf( esc_html__( 'Method: %s,', 'superstore' ), esc_html( $data['method'] ) ); ?></p>

<?php /* translators: %s: Requests list */ ?>
<p><a href="<?php echo esc_url( $data['requests_list'] ); ?>"><?php echo esc_html__( 'See requests list', 'superstore' ); ?></a></p>

<strong><a href="<?php echo esc_url( $data['site_url'] ); ?>"><?php echo sprintf( esc_html__( '%s,', 'superstore' ), esc_html( $data['site_name'] ) ); ?></a></strong>

<?php do_action( 'woocommerce_email_footer', $email ); ?>
