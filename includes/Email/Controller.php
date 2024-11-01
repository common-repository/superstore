<?php

namespace Binarithm\Superstore\Email;

/**
 * Superstore email controller class
 */
class Controller {

	/**
	 * Class constructor
	 */
	public function __construct() {
		add_filter( 'woocommerce_email_classes', array( $this, 'add_classes' ), 40 );
		add_filter( 'woocommerce_template_directory', array( $this, 'add_directory' ), 20, 2 );
		add_filter( 'woocommerce_email_actions', array( $this, 'add_actions' ) );
	}

	/**
	 * Superstore email classes in WC Email
	 *
	 * @param array $wc_emails WC Emails.
	 * @return $wc_emails
	 */
	public function add_classes( $wc_emails ) {
		$wc_emails['Superstore_Email_New_Seller']        = new NewSeller();
		$wc_emails['Superstore_Email_New_Product']       = new NewProduct();
		$wc_emails['Superstore_Email_Product_Published'] = new ProductPublished();
		$wc_emails['Superstore_Email_Contact_Seller']    = new ContactSeller();
		$wc_emails['Superstore_Email_New_Payment']       = new NewPayment();
		$wc_emails['Superstore_Email_Update_Payment']    = new UpdatePayment();

		return apply_filters( 'superstore_email_classes', $wc_emails );
	}

	/**
	 * Overridden template directory for superstore emails
	 *
	 * @param string $template_dir Template directory.
	 * @param string $template Template.
	 * @return string
	 */
	public function add_directory( $template_dir, $template ) {
		$templates = apply_filters(
			'superstore_email_templates',
			array(
				'new-seller.php',
				'new-product.php',
				'product-published.php',
				'contact-seller.php',
				'new-payment.php',
				'update-payment.php',
			)
		);

		$template_name = basename( $template );

		if ( in_array( $template_name, $templates, true ) ) {
			return 'superstore';
		}

		return $template_dir;
	}

	/**
	 * Register superstore email actions for WC
	 *
	 * @param array $actions Actions.
	 * @return $actions
	 */
	public function add_actions( $actions ) {
		$superstore_actions = apply_filters(
			'superstore_email_actions',
			array(
				'superstore_new_seller',
				'superstore_rest_insert_product_object',
				'superstore_product_published',
				'superstore_contact_seller',
				'superstore_new_payment',
				'superstore_update_payment', // Sends email only if withdraw status is approved or cancelled.
			)
		);

		foreach ( $superstore_actions as $action ) {
			$actions[] = $action;
		}

		return $actions;
	}
}
