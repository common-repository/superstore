<?php

namespace Binarithm\Superstore;

use DirectoryIterator;

/**
 * Superstore assets class
 */
class Assets {

	/**
	 * Class constructor
	 */
	public function __construct() {
		// phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion
		wp_enqueue_style( 'mdi-icon', 'https://cdn.jsdelivr.net/npm/@mdi/font@latest/css/materialdesignicons.min.css' );

		add_action( 'init', array( $this, 'register_scripts' ), 10 );
		add_action( 'init', array( $this, 'register_styles' ), 10 );

		if ( is_admin() ) {
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		} else {
			add_action( 'wp_enqueue_scripts', array( $this, 'frontend_enqueue_scripts' ) );
		}
	}

	/**
	 * Register all scripts
	 */
	public function register_scripts() {
		$scripts = $this->get_scripts();
		foreach ( $scripts as $handle => $script ) {
			$deps      = isset( $script['deps'] ) ? $script['deps'] : false;
			$in_footer = isset( $script['in_footer'] ) ? $script['in_footer'] : true;
			$version   = isset( $script['version'] ) ? $script['version'] : SUPERSTORE_PLUGIN_VERSION;

			wp_register_script( $handle, $script['src'], $deps, $version, $in_footer );
		}
	}

	/**
	 * Register all styles
	 */
	public function register_styles() {
		$styles = $this->get_styles();
		foreach ( $styles as $handle => $style ) {
			$deps    = isset( $style['deps'] ) ? $style['deps'] : false;
			$version = isset( $style['version'] ) ? $style['version'] : SUPERSTORE_PLUGIN_VERSION;

			wp_register_style( $handle, $style['src'], $deps, $version );
		}
	}

	/**
	 * Scripts list
	 *
	 * @return array
	 */
	public function get_scripts() {
		$chunks     = array();
		$main       = array();
		$abspath    = superstore()->superstore_pro_exists() ? SUPERSTORE_PRO_ABSPATH : SUPERSTORE_ABSPATH;
		$assets_dir = superstore()->superstore_pro_exists() ? SUPERSTORE_PRO_ASSETS_DIR : SUPERSTORE_ASSETS_DIR;

		if ( file_exists( $abspath . 'assets/js/chunks' ) ) {
			$chunk_files = new DirectoryIterator( $abspath . 'assets/js/chunks' );
			foreach ( $chunk_files as $file ) {
				if ( ! $file->isDot() && $file->isFile() ) {
					$handle            = str_replace( '.js', '-superstore', $file->getFilename() );
					$chunks[ $handle ] = array(
						'src' => $assets_dir . '/js/chunks/' . $file->getFilename(),
					);
				}
			}
		}

		if ( file_exists( $abspath . 'assets/js' ) ) {
			$main_files = new DirectoryIterator( $abspath . 'assets/js' );
			foreach ( $main_files as $file ) {
				if ( ! $file->isDot() && $file->isFile() ) {
					$handle          = str_replace( '.js', '-superstore', $file->getFilename() );
					$main[ $handle ] = array(
						'src' => $assets_dir . '/js/' . $file->getFilename(),
					);
				}
			}
		}

		$all_scripts = array_merge( $chunks, $main );

		return apply_filters( 'superstore_scripts', $all_scripts );
	}

	/**
	 * Styles list
	 *
	 * @return array
	 */
	public function get_styles() {
		$chunks     = array();
		$main       = array();
		$abspath    = superstore()->superstore_pro_exists() ? SUPERSTORE_PRO_ABSPATH : SUPERSTORE_ABSPATH;
		$assets_dir = superstore()->superstore_pro_exists() ? SUPERSTORE_PRO_ASSETS_DIR : SUPERSTORE_ASSETS_DIR;

		if ( file_exists( $abspath . 'assets/css/chunks' ) ) {
			$chunk_files = new DirectoryIterator( $abspath . 'assets/css/chunks' );
			foreach ( $chunk_files as $file ) {
				if ( ! $file->isDot() && $file->isFile() ) {
					$handle            = str_replace( '.css', '-superstore', $file->getFilename() );
					$chunks[ $handle ] = array(
						'src' => $assets_dir . '/css/chunks/' . $file->getFilename(),
					);
				}
			}
		}

		if ( file_exists( $abspath . 'assets/css' ) ) {
			$main_files = new DirectoryIterator( $abspath . 'assets/css' );
			foreach ( $main_files as $file ) {
				if ( ! $file->isDot() && $file->isFile() ) {
					$handle          = str_replace( '.css', '-superstore', $file->getFilename() );
					$main[ $handle ] = array(
						'src' => $assets_dir . '/css/' . $file->getFilename(),
					);
				}
			}
		}

		$all_styles = array_merge( $chunks, $main );

		return apply_filters( 'superstore_styles', $all_styles );
	}

	/**
	 * Enqueue scripts
	 *
	 * @param string $hook Hook.
	 */
	public function admin_enqueue_scripts( $hook ) {
		$localize_data = $this->get_admin_localize_data();
		$scripts       = $this->get_scripts();
		$styles        = $this->get_styles();

		if ( 'toplevel_page_superstore' === $hook ) {
			wp_enqueue_media();
			foreach ( $scripts as $script_handle => $script ) {
				wp_enqueue_script( $script_handle );
			}

			foreach ( $styles as $style_handle => $style ) {
				wp_enqueue_style( $style_handle );
			}
		}

		wp_localize_script( 'superstore-admin-dashboard-superstore', 'superstore', $localize_data );

		do_action( 'superstore_admin_enqueue_scripts' );
	}

	/**
	 * Localize admin data
	 *
	 * @return array
	 */
	public function get_admin_localize_data() {

		$global_data = apply_filters(
			'superstore_admin_localize_global_data',
			array(
				'ajaxurl'        => admin_url( 'admin-ajax.php' ),
				'nonce'          => wp_create_nonce( 'superstore_admin' ),
				'rest'           => array(
					'root'                     => esc_url_raw( get_rest_url() ),
					'nonce'                    => wp_create_nonce( 'wp_rest' ),
					'version'                  => 'superstore/v1',
					'pro_published_notify_url' => 'https://binarithm.com/wp-json/binarithm/v1/email-subscribers',
				),
				'wc_countries'   => WC()->countries->get_countries(),
				'wc_states'      => WC()->countries->get_states(),
				'admin_root_url' => admin_url(),
				'stores_url'     => esc_url_raw( superstore_get_page_permalink( 'stores' ) . '/#/' ),
			),
		);

		$data = array(
			'global' => $global_data,
		);

		return apply_filters( 'superstore_admin_localize_data', $data );
	}

	/**
	 * Localize frontend data
	 */
	public function get_frontend_localize_data() {

		$global_data = apply_filters(
			'superstore_frontend_localize_global_data',
			array(
				'ajaxurl'      => admin_url( 'admin-ajax.php' ),
				'nonce'        => wp_create_nonce( 'superstore_frontend' ),
				'rest'         => array(
					'root'    => esc_url_raw( get_rest_url() ),
					'nonce'   => wp_create_nonce( 'wp_rest' ),
					'version' => 'superstore/v1',
				),
				'wc_countries' => WC()->countries->get_countries(),
				'wc_states'    => WC()->countries->get_states(),
			),
		);

		$data = array(
			'global' => $global_data,
		);

		return apply_filters( 'superstore_frontend_localize_data', $data );
	}

	/**
	 * Enqueue frontend scripts
	 */
	public function frontend_enqueue_scripts() {
		if ( ! function_exists( 'WC' ) ) {
			return;
		}

		if ( SUPERSTORE_SCRIPTS_LOADED ) {

			$superstore_page_ids = get_option( 'superstore_pages' );
			foreach ( $superstore_page_ids as $key => $id ) {
				// Loads only in superstore pages.
				if ( get_page_link() === superstore_get_page_permalink( $key ) ) {

					$localize_data = $this->get_frontend_localize_data();
					$scripts       = $this->get_scripts();
					$styles        = $this->get_styles();

					wp_enqueue_media();

					foreach ( $scripts as $script_handle => $script ) {
						if ( 'superstore-admin-dashboard-superstore' === $script_handle && ! is_admin() ) {
							continue;
						}

						wp_enqueue_script( $script_handle );
						wp_localize_script( 'superstore-seller-dashboard-superstore', 'superstore', $localize_data );
					}

					foreach ( $styles as $style_handle => $style ) {
						wp_enqueue_style( $style_handle );
					}
				}
			}
		}

		do_action( 'superstore_frontend_enqueue_scripts' );
	}
}
