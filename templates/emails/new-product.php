<?php
/**
 * Superstore new product email template to notify admin
 *
 * @package Superstore\Templates\Emails
 * @version 1.0
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_email_header', $email_heading, $email ); ?>

<p><?php esc_html_e( 'Hi there,', 'superstore' ); ?></p>

<p><?php esc_html_e( 'A new product has added in your marketplace.', 'superstore' ); ?></p>

<p><?php esc_html_e( 'Product details are below - ', 'superstore' ); ?></p>

<?php /* translators: %s: Product name */ ?>
<p><a href="<?php echo esc_url( $data['product_edit_link'] ); ?>"><?php echo sprintf( esc_html__( 'Name: %s,', 'superstore' ), esc_html( $data['name'] ) ); ?></a></p>

<?php /* translators: %s: Price */ ?>
<p><?php echo sprintf( esc_html__( 'Price: %s,', 'superstore' ), wc_price( $data['price'] ) ); ?></p>

<?php /* translators: %s: Seller name */ ?>
<p><a href="<?php echo esc_url( $data['profile_url'] ); ?>"><?php echo sprintf( esc_html__( 'Seller name: %s,', 'superstore' ), esc_html( $data['seller_name'] ) ); ?></a></p>

<strong><a href="<?php echo esc_url( $data['site_url'] ); ?>"><?php echo sprintf( esc_html__( '%s,', 'superstore' ), esc_html( $data['site_name'] ) ); ?></a></strong>

<?php do_action( 'woocommerce_email_footer', $email ); ?>
