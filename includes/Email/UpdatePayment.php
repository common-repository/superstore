<?php

namespace Binarithm\Superstore\Email;

use WP_User;
use WC_Email;

/**
 * Superstore update payment status email class
 */
class UpdatePayment extends WC_Email {

	/**
	 * Superstore update payment status email contructor
	 */
	public function __construct() {
		$this->id             = 'superstore_update_payment';
		$this->title          = __( 'Superstore update payment witdraw status.', 'superstore' );
		$this->description    = __( 'Notify seller when their payment witdraw is approved or cancelled.', 'superstore' );
		$this->template_html  = 'emails/update-payment.php';
		$this->template_plain = 'emails/plain/update-payment.php';
		$this->template_base  = SUPERSTORE_ABSPATH . 'templates/';
		$this->recipient      = 'seller-or-admin@ofthewithdraw.request';

		parent::__construct();

		// Trigger for this email.
		add_action( 'superstore_update_payment', array( $this, 'trigger' ), 30 );
	}

	/**
	 * Get email subject.
	 *
	 * @return string
	 */
	public function get_default_subject() {
		return __( 'Update withdraw request status', 'superstore' );
	}

	/**
	 * Get email heading.
	 *
	 * @return string
	 */
	public function get_default_heading() {
		return __( 'Withdraw request is {status}', 'superstore' );
	}

	/**
	 * Get content html.
	 *
	 * @return string
	 */
	public function get_content_html() {
			ob_start();
				wc_get_template(
					$this->template_html,
					array(
						'seller'        => $this->object,
						'email_heading' => $this->get_heading(),
						'sent_to_admin' => true,
						'plain_text'    => false,
						'email'         => $this,
						'data'          => $this->replace,
					),
					'superstore/',
					$this->template_base
				);
			return ob_get_clean();
	}

	/**
	 * Get content plain.
	 *
	 * @return string
	 */
	public function get_content_plain() {
			ob_start();
				wc_get_template(
					$this->template_html,
					array(
						'seller'        => $this->object,
						'email_heading' => $this->get_heading(),
						'sent_to_admin' => true,
						'plain_text'    => true,
						'email'         => $this,
						'data'          => $this->replace,
					),
					'superstore/',
					$this->template_base
				);
			return ob_get_clean();
	}

	/**
	 * Initialise settings form fields.
	 */
	public function init_form_fields() {
		$this->form_fields = array(
			'enabled'    => array(
				'title'   => __( 'Enable/Disable', 'superstore' ),
				'type'    => 'checkbox',
				'label'   => __( 'Enable this email notification', 'superstore' ),
				'default' => 'yes',
			),
			'subject'    => array(
				'title'       => __( 'Subject', 'superstore' ),
				'type'        => 'text',
				'desc_tip'    => true,
				/* translators: %s: list of placeholders */
				'description' => sprintf( __( 'Available placeholders: %s', 'superstore' ), '<code>{site_name}, {store_name}, {seller_name}</code>' ),
				'placeholder' => $this->get_default_subject(),
				'default'     => '',
			),
			'heading'    => array(
				'title'       => __( 'Email heading', 'superstore' ),
				'type'        => 'text',
				'desc_tip'    => true,
				/* translators: %s: list of placeholders */
				'description' => sprintf( __( 'Available placeholders: %s', 'superstore' ), '<code>{site_name}, {store_name}, {seller_name}</code>' ),
				'placeholder' => $this->get_default_heading(),
				'default'     => '',
			),
			'email_type' => array(
				'title'       => __( 'Email type', 'superstore' ),
				'type'        => 'select',
				'description' => __( 'Choose which format of email to send.', 'superstore' ),
				'default'     => 'html',
				'class'       => 'email_type wc-enhanced-select',
				'options'     => $this->get_email_type_options(),
				'desc_tip'    => true,
			),
		);
	}

	/**
	 * Send email.
	 *
	 * @param object $object Withdraw request object.
	 */
	public function trigger( $object ) {
		if ( ! $this->is_enabled() || ! $this->get_recipient() ) {
			return;
		}

		if ( ! in_array( $object->get_status(), array( 'approved', 'cancelled' ), true ) ) {
			return;
		}

		$seller_obj = superstore()->seller->crud_seller( $object->get_user_id() );

		$this->find['status']    = '{status}';
		$this->find['amount']    = '{amount}';
		$this->find['method']    = '{method}';
		$this->find['site_name'] = '{site_name}';
		$this->find['site_url']  = '{site_url}';

		$this->replace['status']    = $object->get_status();
		$this->replace['amount']    = $object->get_amount();
		$this->replace['method']    = $object->get_method();
		$this->replace['site_name'] = $this->get_from_name();
		$this->replace['site_url']  = site_url();

		$this->setup_locale();
		if ( 'approved' === $object->get_status() ) {
			$recipient_email = $seller_obj->get_email();
			$this->send( $recipient_email, $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
		}
		if ( 'cancelled' === $object->get_status() ) {
			$recipient_email1 = get_option( 'admin_email' );
			$recipient_email2 = $seller_obj->get_email();
			$this->send( $recipient_email1, $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
			$this->send( $recipient_email2, $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
		}
		$this->restore_locale();
	}
}
