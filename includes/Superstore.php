<?php
/**
 * Superstore setup
 *
 * @package Superstore
 */

defined( 'ABSPATH' ) || exit;
defined( 'SUPERSTORE_PLUGIN_FILE' ) || exit;

/**
 * Main Superstore class
 *
 * @class Superstore
 */
final class Superstore {

	/**
	 * Plugin version
	 *
	 * @var string
	 */
	public $version = '1.0.0';

	/**
	 * PHP version requirements
	 *
	 * @var string
	 */
	private $min_php_version = '7.0.0';

	/**
	 * The single instance of the class.
	 *
	 * @var Superstore
	 */
	private static $instance = null;

	/**
	 * Contains core classes
	 *
	 * @var array
	 */
	private $container = array();

	/**
	 * Main Superstore instance.
	 *
	 * Ensures only one instance of Superstore is loaded or can be loaded.
	 *
	 * @static
	 * @see superstore()
	 * @return Superstore - Main instance.
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Auto-load in-accessible class instances.
	 *
	 * @param string $key Key.
	 * @return Class instances
	 */
	public function __get( $key ) {
		if ( array_key_exists( $key, $this->container ) ) {
			return $this->container[ $key ];
		}
	}

	/**
	 * Superstore constructor.
	 */
	private function __construct() {
		require_once dirname( SUPERSTORE_PLUGIN_FILE ) . '/vendor/autoload.php';
		$this->define_constants();
		$this->define_tables();
		$this->includes();
		$this->init_hooks();
		do_action( 'superstore_loaded' );
	}

	/**
	 * Define superstore constants.
	 */
	private function define_constants() {
		$this->define( 'SUPERSTORE_PLUGIN_VERSION', $this->version );
		$this->define( 'SUPERSTORE_BASENAME', plugin_basename( SUPERSTORE_PLUGIN_FILE ) );
		$this->define( 'SUPERSTORE_ABSPATH', dirname( SUPERSTORE_PLUGIN_FILE ) . '/' );
		$this->define( 'SUPERSTORE_ASSETS_DIR', plugins_url( 'assets', SUPERSTORE_PLUGIN_FILE ) );
		$this->define( 'SUPERSTORE_INCLUDE_DIR', dirname( SUPERSTORE_PLUGIN_FILE ) . '/includes' );

		// Turn off/on Superstore styles and scripts.
		$this->define( 'SUPERSTORE_STYLES_LOADED', true );
		$this->define( 'SUPERSTORE_SCRIPTS_LOADED', true );
	}

	/**
	 * Register superstore tables prefix and define shortcuts.
	 */
	private function define_tables() {
		global $wpdb;

		$tables = $this->tables_without_prefix();

		foreach ( $tables as $name => $table ) {
			$wpdb->$name = $wpdb->prefix . $name;
		}
	}

	/**
	 * Include files.
	 */
	public function includes() {
		include_once SUPERSTORE_INCLUDE_DIR . '/functions.php';
	}

	/**
	 * Initialize main actions and filters
	 */
	public function init_hooks() {
		register_activation_hook( SUPERSTORE_PLUGIN_FILE, array( $this, 'on_activation' ) );
		add_filter( 'page_template', array( $this, 'seller_dashboard_blank_page_template' ) );
		add_action( 'woocommerce_loaded', array( $this, 'init_superstore' ) );
		add_action( 'admin_notices', array( $this, 'notices' ) );
		add_action( 'widgets_init', array( $this, 'register_widgets' ) );
	}

	/**
	 * Register superstore widgets
	 */
	public function register_widgets() {
		$this->container['widgets'] = new \Binarithm\Superstore\Widgets\Controller();
	}

	/**
	 * Runs on plugin activation
	 */
	public function on_activation() {
		if ( ! class_exists( 'WooCommerce' ) ) {
			set_transient( 'superstore_requires_wc', true );
		}

		if ( version_compare( PHP_VERSION, $this->min_php_version, '<' ) ) {
			require_once WC_ABSPATH . 'includes/wc-notice-functions.php';

			/* translators: 1: Version number 2: ZIP code */
			wc_print_notice( sprintf( __( 'PHP version requires at least %1$s for <b>Superstore</b>. Current version is %2$s .', 'superstore' ), $this->min_php_version, phpversion(), 'error' ) );
			exit;
		}

		\Binarithm\Superstore\Installer::install();
	}

	/**
	 * Custom blank template redirect for full screen superstore seller dashboard
	 *
	 * @param string $template A blank template.
	 * @return string
	 */
	public function seller_dashboard_blank_page_template( $template ) {
		$full_width = superstore_get_option( 'full_width_seller_dashboard', 'superstore_appearance', 'yes' );

		if ( 'yes' !== $full_width ) {
			return;
		}

		if ( ! function_exists( 'WC' ) ) {
			return;
		}

		if ( ! is_user_logged_in() ) {
			return;
		}

		if ( ! superstore_is_user_seller( get_current_user_id() ) ) {
			return;
		}

		$post          = get_post();
		$page_template = get_post_meta( $post->ID, '_wp_page_template', true );
		if ( 'superstore-seller-dashboard.php' === basename( $page_template ) ) {
			$template = SUPERSTORE_ABSPATH . 'templates/superstore-seller-dashboard.php';
		}

		return $template;
	}

	/**
	 * Load superstore after woocommerce loaded
	 */
	public function init_superstore() {
		add_filter( 'plugin_action_links_' . SUPERSTORE_BASENAME, array( $this, 'add_action_links' ) );
		add_filter( 'plugin_row_meta', array( $this, 'add_row_meta' ), 10, 2 );
		add_action( 'in_plugin_update_message-superstore/superstore.php', array( \Binarithm\Superstore\Installer::class, 'in_plugin_update_message' ) );
		add_action( 'init', array( $this, 'init' ), 4 );
	}

	/**
	 * Show action links on the plugin screen.
	 *
	 * @param mixed $links Plugin Action links.
	 * @return array
	 */
	public function add_action_links( $links ) {

		$action_links = array(
			'settings' => '<a href="' . admin_url( 'admin.php?page=superstore#/settings' ) . '">' . __( 'Settings', 'superstore' ) . '</a>',
		);

		if ( ! $this->superstore_pro_exists() ) {
			$links[] = '<a href="https://binarithm.com/superstore-pricing" style="color: #c20051;font-weight: bold;" target="_blank">' . __( 'Get Pro', 'superstore' ) . '</a>';
		}

		return array_merge( $action_links, $links );
	}

	/**
	 * Checks if any superstore premium plan(startup/growing/mature) exists
	 *
	 * @return boolean
	 */
	public function superstore_pro_exists() {
		return apply_filters( 'superstore_pro_exists', false );
	}

	/**
	 * Show row meta on the plugin screen.
	 *
	 * @param mixed $links Plugin Row Meta.
	 * @param mixed $file  Plugin Base file.
	 * @return array
	 */
	public function add_row_meta( $links, $file ) {
		if ( SUPERSTORE_BASENAME !== $file ) {
			return $links;
		}

		$row_meta = array(
			'docs' => '<a href="' . esc_url( 'https://binarithm.com/superstore-docs' ) . '" target="_blank">' . esc_html__( 'Docs', 'superstore' ) . '</a>',
		);

		return array_merge( $links, $row_meta );
	}

	/**
	 * Load text domain and all classes
	 */
	public function init() {
		load_plugin_textdomain( 'superstore', false, dirname( SUPERSTORE_BASENAME ) . '/languages/' );
		$this->init_classes();
	}

	/**
	 * Initialize all superstore classes
	 */
	protected function init_classes() {

		if ( is_admin() ) {
			new \Binarithm\Superstore\Menus();
		}

		new \Binarithm\Superstore\Hooks\Controller();

		$this->container['asset']      = new \Binarithm\Superstore\Assets();
		$this->container['shortcode']  = new \Binarithm\Superstore\Shortcode\Controller();
		$this->container['restapi']    = new \Binarithm\Superstore\RESTAPI\Controller();
		$this->container['seller']     = new \Binarithm\Superstore\Seller\Controller();
		$this->container['media']      = new \Binarithm\Superstore\Media();
		$this->container['product']    = new \Binarithm\Superstore\Product();
		$this->container['order']      = new \Binarithm\Superstore\Order();
		$this->container['payment']    = new \Binarithm\Superstore\Payment\Controller();
		$this->container['commission'] = new \Binarithm\Superstore\Commission();
		$this->container['report']     = new \Binarithm\Superstore\Report();
		$this->container['localize']   = new \Binarithm\Superstore\Localize\Controller();
		$this->container['email']      = new \Binarithm\Superstore\Email\Controller();

		$this->container = apply_filters( 'superstore_classes', $this->container );

		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			new \Binarithm\Superstore\Ajax();
		}
	}

	/**
	 * Display required notices
	 */
	public function notices() {
		$this->woocommerce_required_notice();
		$this->upgrade_to_pro_notice();
	}

	/**
	 * Woocommerce is required for superstore notice
	 */
	public function woocommerce_required_notice() {
		if ( class_exists( 'WooCommerce' ) && get_transient( 'superstore_requires_wc' ) ) {
			delete_transient( 'superstore_requires_wc' );
			return;
		}

		if ( ! class_exists( 'WooCommerce' ) || get_transient( 'superstore_requires_wc' ) ) {
			$woocommerce_url = self_admin_url( 'plugin-install.php?s=woocommerce&tab=search&type=term' );

			/* translators: 1: WooCommerce install link */
			$message = sprintf( esc_html__( 'Superstore does not work without WooCommerce. You can find %s.', 'superstore' ), '<a href="' . $woocommerce_url . '">WooCommerce here</a>' );

			echo wp_kses_post( sprintf( '<div class="error"><p><strong>%1$s</strong></p></div>', $message ) );
		}
	}

	/**
	 * Superstore pro upgrading notice
	 */
	public function upgrade_to_pro_notice() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}

		if ( class_exists( 'SuperstorePro' ) ) {
			return;
		}

		$dismissed = get_option( 'superstore_upgrade_notice_dismissed', false );

		if ( $dismissed ) {
			return;
		}

		superstore_get_template_part( 'upgrade-notice' );
	}

	/**
	 * Superstore wpdb tables shortcut without prefix
	 *
	 * @return array
	 */
	private function tables_without_prefix() {

		$tables = array(
			'superstore_payments'           => 'superstore_payments',
			'superstore_payment_statements' => 'superstore_payment_statements',
		);

		return apply_filters( 'superstore_tables_without_prefix', $tables );
	}

	/**
	 * Define constant if not already set.
	 *
	 * @param string      $name  Constant name.
	 * @param string|bool $value Constant value.
	 */
	private function define( $name, $value ) {
		if ( ! defined( $name ) ) {
			define( $name, $value );
		}
	}
}
