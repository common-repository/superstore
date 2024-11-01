<?php
/**
 * Superstore new seller email template to notify admin
 *
 * @package Superstore\Templates\Emails
 * @version 1.0
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_email_header', $email_heading, $email ); ?>

<p><?php esc_html_e( 'Hi there,', 'superstore' ); ?></p>

<p><?php esc_html_e( 'A new seller has registered in your marketplace.', 'superstore' ); ?></p>

<p><?php esc_html_e( 'Seller details are below - ', 'superstore' ); ?></p>

<?php /* translators: %s: Seller seller_name */ ?>
<p><?php echo sprintf( esc_html__( 'Seller name: %s,', 'superstore' ), esc_html( $data['seller_name'] ) ); ?></p>

<?php /* translators: %s: Seller email */ ?>
<p><?php echo sprintf( esc_html__( 'Email: %s,', 'superstore' ), esc_html( $data['email'] ) ); ?></p>

<?php /* translators: %s: Seller store_name */ ?>
<p><?php echo sprintf( esc_html__( 'Store name: %s,', 'superstore' ), esc_html( $data['store_name'] ) ); ?></p>

<p><a href="<?php echo esc_url( $data['profile_url'] ); ?>"><?php esc_html_e( 'Edit or view seller profile', 'superstore' ); ?></a></p>
<p><a href="<?php echo esc_url( $data['store_url'] ); ?>"><?php esc_html_e( 'Visit store', 'superstore' ); ?></a></p>

<strong><a href="<?php echo esc_url( $data['site_url'] ); ?>"><?php /* translators: %s: Site name */ echo sprintf( esc_html__( '%s,', 'superstore' ), esc_html( $data['site_name'] ) ); ?></a></strong>

<?php do_action( 'woocommerce_email_footer', $email ); ?>
