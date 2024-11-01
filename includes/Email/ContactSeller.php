<?php

namespace Binarithm\Superstore\Email;

use WC_Email;

/**
 * Superstore contact seller email class
 */
class ContactSeller extends WC_Email {

	/**
	 * Class contructor
	 */
	public function __construct() {
		$this->id             = 'superstore_contact_seller';
		$this->title          = __( 'Superstore contact seller', 'superstore' );
		$this->description    = __( 'Sends customer contact message to seller from store contact form.', 'superstore' );
		$this->template_html  = 'emails/contact-seller.php';
		$this->template_plain = 'emails/plain/contact-seller.php';
		$this->template_base  = SUPERSTORE_ABSPATH . 'templates/';
		$this->recipient      = 'seller@ofthe.store';

		parent::__construct();

		// Trigger for this email.
		add_action( 'superstore_contact_seller', array( $this, 'trigger' ), 30, 4 );
	}

	/**
	 * Get email subject.
	 *
	 * @return string
	 */
	public function get_default_subject() {
		return __( 'Message from customer', 'superstore' );
	}

	/**
	 * Get email heading.
	 *
	 * @return string
	 */
	public function get_default_heading() {
		return __( '{customer_name} - Sent a message', 'superstore' );
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
			'recipient'  => array(
				'title'       => __( 'Recipient(s)', 'superstore' ),
				'type'        => 'text',
				/* translators: %s: Default recipient(aadmin) */
				'description' => sprintf( __( 'Enter recipients (comma separated) for this email. Defaults to %s.', 'superstore' ), '<code>' . esc_attr( get_option( 'admin_email' ) ) . '</code>' ),
				'placeholder' => '',
				'default'     => '',
				'desc_tip'    => true,
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
	 * @param string $recipient_email Recipient email.
	 * @param string $customer_name Customer name.
	 * @param string $customer_email Customer email.
	 * @param string $customer_message Customer message.
	 */
	public function trigger( $recipient_email, $customer_name, $customer_email, $customer_message ) {
		if ( ! $this->is_enabled() || ! $this->get_recipient() ) {
			return;
		}

		$this->find['customer_name']    = '{customer_name}';
		$this->find['customer_email']   = '{customer_email}';
		$this->find['customer_message'] = '{customer_message}';
		$this->find['site_name']        = '{site_name}';
		$this->find['site_url']         = '{site_url}';

		$this->replace['customer_name']    = $customer_name;
		$this->replace['customer_email']   = $customer_email;
		$this->replace['customer_message'] = $customer_message;
		$this->replace['site_name']        = $this->get_from_name();
		$this->replace['site_url']         = site_url();

		$this->setup_locale();
		$this->send( $recipient_email, $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
		$this->restore_locale();
	}
}
