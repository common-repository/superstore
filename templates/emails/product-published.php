<?php
/**
 * Superstore product published email template to notify seller
 *
 * @package Superstore\Templates\Emails
 * @version 1.0
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_email_header', $email_heading, $email ); ?>

<p><?php esc_html_e( 'Hi there,', 'superstore' ); ?></p>

<p><?php esc_html_e( 'Your product is published now.', 'superstore' ); ?></p>

<p><?php esc_html_e( 'Product details are below - ', 'superstore' ); ?></p>

<?php /* translators: %s: Product name */ ?>
<p><?php echo sprintf( esc_html__( 'Name: %s,', 'superstore' ), esc_html( $data['name'] ) ); ?></p>

<?php /* translators: %s: Product price */ ?>
<p><?php echo sprintf( esc_html__( 'Price: %s,', 'superstore' ), wc_price( $data['price'] ) ); ?></p>

<p><a href="<?php echo esc_url( $data['product_edit_link'] ); ?>"><?php esc_html_e( 'Edit product', 'superstore' ); ?></a></p>

<?php /* translators: %s: Site name */ ?>
<strong><a href="<?php echo esc_url( $data['site_url'] ); ?>"><?php echo sprintf( esc_html__( '%s,', 'superstore' ), esc_html( $data['site_name'] ) ); ?></a></strong>

<?php do_action( 'woocommerce_email_footer', $email ); ?>
