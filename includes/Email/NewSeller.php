<?php

namespace Binarithm\Superstore\Email;

use WC_Email;

/**
 * Superstore new seller email class
 */
class NewSeller extends WC_Email {

	/**
	 * Class contructor
	 */
	public function __construct() {
		$this->id             = 'superstore_new_seller';
		$this->title          = __( 'Superstore new seller account', 'superstore' );
		$this->description    = __( 'Notify admin when new sellers are registered.', 'superstore' );
		$this->template_html  = 'emails/new-seller.php';
		$this->template_plain = 'emails/plain/new-seller.php';
		$this->template_base  = SUPERSTORE_ABSPATH . 'templates/';
		$this->recipient      = $this->get_option( 'recipient', get_option( 'admin_email' ) );

		parent::__construct();

		// Trigger for this email.
		add_action( 'superstore_new_seller', array( $this, 'trigger' ), 30 );
	}

	/**
	 * Get email subject.
	 *
	 * @return string
	 */
	public function get_default_subject() {
		return __( 'New seller registration', 'superstore' );
	}

	/**
	 * Get email heading.
	 *
	 * @return string
	 */
	public function get_default_heading() {
		return __( 'New seller registered', 'superstore' );
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
				/* translators: %s: Default recipient(admin) */
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
	 * @param object $object Seller object.
	 */
	public function trigger( $object ) {
		if ( ! $this->is_enabled() || ! $this->get_recipient() ) {
			return;
		}

		$this->object              = $object;
		$this->find['seller_name'] = '{seller_name}';
		$this->find['email']       = '{email}';
		$this->find['store_url']   = '{store_url}';
		$this->find['store_name']  = '{store_name}';
		$this->find['profile_url'] = '{profile_url}';
		$this->find['site_name']   = '{site_name}';
		$this->find['site_url']    = '{site_url}';

		$this->replace['seller_name'] = $this->object->get_first_name() . ' ' . $this->object->get_last_name();
		$this->replace['email']       = $this->object->get_email();
		$this->replace['store_url']   = superstore_get_store_url( $this->object->get_id() );
		$this->replace['store_name']  = $object->get_store_name();
		$this->replace['profile_url'] = admin_url( 'admin.php?page=superstore#/seller/' . $object->get_id() );
		$this->replace['site_name']   = $this->get_from_name();
		$this->replace['site_url']    = site_url();

		$this->setup_locale();
		$this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
		$this->restore_locale();
	}
}
