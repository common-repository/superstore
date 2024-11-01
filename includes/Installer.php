<?php

namespace Binarithm\Superstore;

use WP_Roles;

/**
 * Superstore installer
 */
class Installer {

	/**
	 * Superstore installer constructor
	 */
	public static function install() {
		self::create_pages();
		self::create_user_roles();
		self::create_tables();
	}

	/**
	 * Create required superstore pages.
	 *
	 * @return mixed
	 */
	public static function create_pages() {
		if ( ! empty( get_option( 'superstore_pages_created' ) ) ) {
			return;
		}

		$pages = array();

		foreach ( self::superstore_pages() as $page ) {
			$page_id = self::create_page( $page );

			if ( $page_id ) {
				$pages[ $page['page_id'] ] = $page_id;

				if ( isset( $page['child'] ) && count( $page['child'] ) > 0 ) {
					foreach ( $page['child'] as $child_page ) {
						$child_page_id = self::create_page( $child_page );

						if ( $child_page_id ) {
							$pages[ $child_page['page_id'] ] = $child_page_id;

							wp_update_post(
								array(
									'ID'          => $child_page_id,
									'post_parent' => $page_id,
								)
							);
						}
					}
				}
			}

			update_option( 'superstore_' . $page['slug'] . '_page_id', $page_id );
		}

		update_option( 'superstore_pages', $pages );
		update_option( 'superstore_pages_created', true );
	}

	/**
	 * Default superstore pages
	 *
	 * @return array
	 */
	public static function superstore_pages() {
		$pages = array(
			array(
				'post_title' => __( 'Stores', 'superstore' ),
				'slug'       => 'stores',
				'page_id'    => 'stores',
				'content'    => '[superstore-stores]',
			),
			array(
				'post_title' => __( 'Seller Account', 'superstore' ),
				'slug'       => 'seller-account',
				'page_id'    => 'seller_account',
				'content'    => '[superstore-seller-account]',
				'template'   => 'superstore-seller-dashboard.php',
			),
		);

		return apply_filters( 'superstore_pages', $pages );
	}

	/**
	 * Superstore page creator
	 *
	 * @param array $page An array of pages.
	 * @return array | boolean
	 */
	public static function create_page( $page ) {
		$meta_key = '_wp_page_template';
		$page_obj = get_page_by_path( $page['post_title'] );

		if ( ! $page_obj ) {
			$page_id = wp_insert_post(
				array(
					'post_title'     => $page['post_title'],
					'post_name'      => $page['slug'],
					'post_content'   => $page['content'],
					'post_status'    => 'publish',
					'post_type'      => 'page',
					'comment_status' => 'closed',
				)
			);

			if ( $page_id && ! is_wp_error( $page_id ) ) {

				if ( isset( $page['template'] ) ) {
					update_post_meta( $page_id, $meta_key, $page['template'] );
				}

				return $page_id;
			}
		}

		return false;
	}

	/**
	 * Create superstore seller user roles
	 */
	public static function create_user_roles() {
		global $wp_roles;

		if ( ! class_exists( 'WP_Roles' ) ) {
			return;
		}

		if ( ! isset( $wp_roles ) ) {
			// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
			$wp_roles = new WP_Roles();
		}

		add_role(
			'superstore_seller',
			__( 'Seller', 'superstore' ),
			array(
				'read'                      => true,
				'publish_posts'             => true,
				'edit_posts'                => true,
				'edit_published_posts'      => true,
				'delete_published_posts'    => true,
				'delete_posts'              => true,
				'manage_categories'         => true,
				'moderate_comments'         => true,
				'unfiltered_html'           => true,
				'upload_files'              => true,
				'edit_shop_orders'          => true,
				'edit_product'              => true,
				'read_product'              => true,
				'delete_product'            => true,
				'edit_products'             => true,
				'publish_products'          => true,
				'read_private_products'     => true,
				'delete_products'           => true,
				'delete_products'           => true,
				'delete_private_products'   => true,
				'delete_published_products' => true,
				'delete_published_products' => true,
				'edit_private_products'     => true,
				'edit_published_products'   => true,
				'manage_product_terms'      => true,
				'delete_product_terms'      => true,
				'assign_product_terms'      => true,
				'manage_superstore'         => true,
			)
		);

		$capabilities    = array();
		$superstore_caps = superstore_get_capabilities();

		foreach ( $superstore_caps as $key => $cap ) {
			$capabilities = array_merge( $capabilities, array_keys( $cap ) );
		}

		$wp_roles->add_cap( 'shop_manager', 'manage_superstore' );
		$wp_roles->add_cap( 'administrator', 'manage_superstore' );

		foreach ( $capabilities as $key => $capability ) {
			$wp_roles->add_cap( 'superstore_seller', $capability );
			$wp_roles->add_cap( 'administrator', $capability );
			$wp_roles->add_cap( 'shop_manager', $capability );
		}
	}

	/**
	 * Custom wpdb tables creation
	 */
	private static function create_tables() {
		include_once ABSPATH . 'wp-admin/includes/upgrade.php';

		self::create_payments_table();
		self::create_payment_statements_table();
	}

	/**
	 * Type: request,schedule,instant
	 * Status: complete,pending,cancell
	 */
	private static function create_payments_table() {
		global $wpdb;

		$sql = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}superstore_payments` (
               `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
               `user_id` bigint(20) unsigned NOT NULL,
               `amount` decimal(19,4) NOT NULL,
               `method` varchar(30),
               `type` varchar(30) NOT NULL,
               `note` text,
               `status` varchar(30) NOT NULL,
               `ip` varchar(50) NOT NULL,
               `date_created` timestamp NOT NULL,
              PRIMARY KEY (id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;";

		dbDelta( $sql );
	}

	/**
	 * Stores seller debit credit balance, transaction/txn details etc
	 */
	private static function create_payment_statements_table() {
		global $wpdb;

		$sql = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}superstore_payment_statements` (
               `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
               `user_id` bigint(20) unsigned NOT NULL,
               `txn_id` bigint(20) unsigned NOT NULL,
               `debit` decimal(19,4) NOT NULL,
               `credit` decimal(19,4) NOT NULL,
               `type` varchar(30) NOT NULL,
               `status` varchar(30) DEFAULT NULL,
               `txn_date` timestamp NOT NULL,
               `date_created` timestamp NOT NULL,
              PRIMARY KEY (id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;";

		dbDelta( $sql );
	}

	/**
	 * Show plugin changes on the plugins screen.
	 *
	 * @param array $args Args.
	 */
	public static function in_plugin_update_message( $args ) {
		$transient_name = 'superstore_upgrade_notice_' . $args['Version'];
		$upgrade_notice = get_transient( $transient_name );

		if ( ! $upgrade_notice ) {
			$response = wp_safe_remote_get( 'https://plugins.svn.wordpress.org/superstore/trunk/readme.txt' );

			if ( ! is_wp_error( $response ) && ! empty( $response['body'] ) ) {
				$upgrade_notice = self::parse_update_notice( $response['body'], $args['new_version'] );
				set_transient( $transient_name, $upgrade_notice, DAY_IN_SECONDS );
			}
		}

		echo wp_kses_post( $upgrade_notice );
	}

	/**
	 * Parse update notice from readme file.
	 *
	 * @param  string $content Superstore readme file content.
	 * @param  string $new_version Superstore new version.
	 * @return string
	 */
	private static function parse_update_notice( $content, $new_version ) {
		$matches        = null;
		$regexp         = '~==\s*Upgrade Notice\s*==\s*=\s*(.*)\s*=(.*)(=\s*' . preg_quote( SUPERSTORE_PLUGIN_VERSION, '/' ) . '\s*=|$)~Uis';
		$upgrade_notice = '';

		if ( preg_match( $regexp, $content, $matches ) ) {
			$notices = (array) preg_split( '~[\r\n]+~', trim( $matches[2] ) );

			// Convert the full version strings to minor versions.
			$notice_version_parts  = explode( '.', trim( $matches[1] ) );
			$current_version_parts = explode( '.', SUPERSTORE_PLUGIN_VERSION );

			if ( 3 !== count( $notice_version_parts ) ) {
				return;
			}

			$notice_version  = $notice_version_parts[0] . '.' . $notice_version_parts[1];
			$current_version = $current_version_parts[0] . '.' . $current_version_parts[1];

			// Check the latest stable version and ignore trunk.
			if ( version_compare( $current_version, $notice_version, '<' ) ) {
				$upgrade_notice .= '</p><p class="superstore_plugin_upgrade_notice">';

				foreach ( $notices as $index => $line ) {
					$upgrade_notice .= preg_replace( '~\[([^\]]*)\]\(([^\)]*)\)~', '<a href="${2}">${1}</a>', $line );
				}
			}
		}

		return wp_kses_post( $upgrade_notice );
	}
}
