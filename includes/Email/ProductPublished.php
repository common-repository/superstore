<?php

namespace Binarithm\Superstore\Email;

use WC_Email;

/**
 * Superstore product published email class
 */
class ProductPublished extends WC_Email {

	/**
	 * Class contructor
	 */
	public function __construct() {
		$this->id             = 'superstore_product_published';
		$this->title          = __( 'Superstore product status changed to publish', 'superstore' );
		$this->description    = __( 'Notify seller when their products are published by admin.', 'superstore' );
		$this->template_html  = 'emails/product-published.php';
		$this->template_plain = 'emails/plain/product-published.php';
		$this->template_base  = SUPERSTORE_ABSPATH . 'templates/';
		$this->recipient      = 'seller@ofthe.product';

		parent::__construct();

		// Trigger for this email.
		add_action( 'superstore_product_published', array( $this, 'trigger' ), 30 );
	}

	/**
	 * Get email subject.
	 *
	 * @return string
	 */
	public function get_default_subject() {
		return __( 'Product published', 'superstore' );
	}

	/**
	 * Get email heading.
	 *
	 * @return string
	 */
	public function get_default_heading() {
		return __( 'Your product {name} is now published', 'superstore' );
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
	 * @param object $object Post object.
	 */
	public function trigger( $object ) {
		if ( ! $this->is_enabled() || ! $this->get_recipient() ) {
			return;
		}

		$seller  = superstore()->seller->crud_seller( $object->post_author );
		$product = wc_get_product( $object->ID );

		$this->find['name']      = '{name}';
		$this->find['price']     = '{price}';
		$this->find['site_name'] = '{site_name}';
		$this->find['site_url']  = '{site_url}';

		$this->replace['name']              = $product->get_name();
		$this->replace['price']             = $product->get_price();
		$this->replace['product_edit_link'] = superstore_get_page_permalink( 'seller_account' ) . '#/product/' . $product->get_id();
		$this->replace['site_name']         = $this->get_from_name();
		$this->replace['site_url']          = site_url();

		$this->setup_locale();
		$this->send( $seller->get_email(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
		$this->restore_locale();
	}
}
