<?php
/**
 * Superstore payment withdraw request status change email template to notify seller
 *
 * @package Superstore\Templates\Emails
 * @version 1.0
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_email_header', $email_heading, $email );
?>

<p><?php esc_html_e( 'Hi there,', 'superstore' ); ?></p>

<p>
	<?php
	if ( 'approved' === $data['status'] ) {
		esc_html_e( 'Thanks for being with us. The requested amount will be transferred to your preferred payment method shortly.', 'superstore' );
	} elseif ( 'cancelled' === $data['status'] ) {
		esc_html_e( 'Withdraw request is cancelled. Please check all valid requirements.', 'superstore' );
	}
	?>
</p>

<p><?php esc_html_e( 'Request details are below - ', 'superstore' ); ?></p>

<?php /* translators: %s: Amount */ ?>
<p><?php echo sprintf( esc_html__( 'Amount: %s,', 'superstore' ), wc_price( $data['amount'] ) ); ?></p>

<?php /* translators: %s: method */ ?>
<p><?php echo sprintf( esc_html__( 'Method: %s,', 'superstore' ), esc_html( $data['method'] ) ); ?></p>

<strong><a href="<?php echo esc_url( $data['site_url'] ); ?>"><?php echo sprintf( esc_html__( '%s,', 'superstore' ), esc_html( $data['site_name'] ) ); ?></a></strong>

<?php do_action( 'woocommerce_email_footer', $email ); ?>
