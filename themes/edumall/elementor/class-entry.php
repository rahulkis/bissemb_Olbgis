<?php

namespace Edumall_Elementor;

defined( 'ABSPATH' ) || exit;

/**
 * Main Elementor Class
 *
 * The main class that initiates and runs the plugin.
 *
 * @since 1.0.0
 */
final class Entry {

	/**
	 * Minimum Elementor Version
	 *
	 * @since 1.0.0
	 *
	 * @var string Minimum Elementor version required to run the plugin.
	 */
	const MINIMUM_ELEMENTOR_VERSION = '2.8.5';

	/**
	 * Minimum PHP Version
	 *
	 * @since 1.0.0
	 *
	 * @var string Minimum PHP version required to run the plugin.
	 */
	const MINIMUM_PHP_VERSION = '5.6';

	private static $_instance = null;

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * The real constructor to initialize
	 *
	 * @since  1.0.0
	 *
	 * @access public
	 */
	public function initialize() {
		$this->check_validate();
	}

	public function check_validate() {

		// Check if Elementor installed and activated.
		if ( ! defined( 'ELEMENTOR_VERSION' ) ) {
			add_action( 'admin_notices', [ $this, 'admin_notice_missing_main_plugin' ] );

			return;
		}

		// Check for required Elementor version.
		if ( ! version_compare( ELEMENTOR_VERSION, self::MINIMUM_ELEMENTOR_VERSION, '>=' ) ) {
			add_action( 'admin_notices', [ $this, 'admin_notice_minimum_elementor_version' ] );

			return;
		}

		// Check for required PHP version.
		if ( version_compare( PHP_VERSION, self::MINIMUM_PHP_VERSION, '<' ) ) {
			add_action( 'admin_notices', [ $this, 'admin_notice_minimum_php_version' ] );

			return;
		}

		add_action( 'elementor/theme/register_locations', [ $this, 'register_theme_locations' ] );

		add_action( 'after_switch_theme', [ $this, 'add_cpt_support' ] );

		add_action( 'elementor/editor/after_enqueue_styles', [ $this, 'elementor_editor_scripts' ] );

		require_once EDUMALL_ELEMENTOR_DIR . '/module-query.php';
		require_once EDUMALL_ELEMENTOR_DIR . '/class-control-init.php';

		require_once EDUMALL_ELEMENTOR_DIR . '/class-widget-utils.php';
		require_once EDUMALL_ELEMENTOR_DIR . '/class-widget-init.php';

		/**
		 * WPML supported.
		 */
		require_once EDUMALL_ELEMENTOR_DIR . '/wpml/class-wpml-translatable-nodes.php';
	}

	function elementor_editor_scripts() {
		wp_enqueue_style( 'edumall-elementor-editor', EDUMALL_ELEMENTOR_ASSETS . '/css/editor.css' );
	}

	/**
	 * @param \ElementorPro\Modules\ThemeBuilder\Classes\Locations_Manager $elementor_theme_manager
	 *
	 * Register theme locations
	 */
	public function register_theme_locations( $elementor_theme_manager ) {
		$elementor_theme_manager->register_location( 'header' );
		$elementor_theme_manager->register_location( 'footer' );
		$elementor_theme_manager->register_location( 'single' );
		$elementor_theme_manager->register_location( 'archive' );
	}

	/**
	 * Enable default Elementor Editor for custom post type.
	 */
	public function add_cpt_support() {
		//if exists, assign to $cpt_support var.
		$cpt_support = get_option( 'elementor_cpt_support' );

		//check if option DOESN'T exist in db.
		if ( ! $cpt_support ) {
			// Create array of our default supported post types.
			$cpt_support = [
				'page',
				'post',
				'ic_mega_menu',
			];
			update_option( 'elementor_cpt_support', $cpt_support );
		} else {
			if ( ! in_array( 'ic_mega_menu', $cpt_support ) ) {
				$cpt_support[] = 'ic_mega_menu';
			}

			update_option( 'elementor_cpt_support', $cpt_support );
		}
	}

	/**
	 * Admin notice
	 *
	 * Warning when the site doesn't have Elementor installed or activated.
	 *
	 * @since  1.0.0
	 *
	 * @access public
	 */
	public function admin_notice_missing_main_plugin() {

		if ( isset( $_GET['activate'] ) ) {
			unset( $_GET['activate'] );
		}

		$message = sprintf(
		/* translators: 1: Plugin name 2: Elementor */
			esc_html__( '"%1$s" requires "%2$s" to be installed and activated.', 'edumall' ),
			'<strong>' . esc_html__( 'Edumall', 'edumall' ) . '</strong>',
			'<strong>' . esc_html__( 'Elementor', 'edumall' ) . '</strong>'
		);

		printf( '<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message );

	}

	/**
	 * Admin notice
	 *
	 * Warning when the site doesn't have a minimum required Elementor version.
	 *
	 * @since  1.0.0
	 *
	 * @access public
	 */
	public function admin_notice_minimum_elementor_version() {

		if ( isset( $_GET['activate'] ) ) {
			unset( $_GET['activate'] );
		}

		$message = sprintf(
		/* translators: 1: Plugin name 2: Elementor 3: Required Elementor version */
			esc_html__( '"%1$s" requires "%2$s" version %3$s or greater.', 'edumall' ),
			'<strong>' . esc_html__( 'Edumall', 'edumall' ) . '</strong>',
			'<strong>' . esc_html__( 'Elementor', 'edumall' ) . '</strong>',
			self::MINIMUM_ELEMENTOR_VERSION
		);

		printf( '<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message );

	}

	/**
	 * Admin notice
	 *
	 * Warning when the site doesn't have a minimum required PHP version.
	 *
	 * @since  1.0.0
	 *
	 * @access public
	 */
	public function admin_notice_minimum_php_version() {

		if ( isset( $_GET['activate'] ) ) {
			unset( $_GET['activate'] );
		}

		$message = sprintf(
		/* translators: 1: Plugin name 2: PHP 3: Required PHP version */
			esc_html__( '"%1$s" requires "%2$s" version %3$s or greater.', 'edumall' ),
			'<strong>' . esc_html__( 'Edumall', 'edumall' ) . '</strong>',
			'<strong>' . esc_html__( 'PHP', 'edumall' ) . '</strong>',
			self::MINIMUM_PHP_VERSION
		);

		printf( '<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message );

	}
}

Entry::instance()->initialize();
