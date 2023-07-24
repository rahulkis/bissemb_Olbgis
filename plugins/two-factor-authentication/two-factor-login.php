<?php
/*
Plugin Name: Two Factor Authentication
Plugin URI: https://www.simbahosting.co.uk/s3/product/two-factor-authentication/
Description: Secure your WordPress login forms with two factor authentication - including WooCommerce login forms
Author: David Anderson, original plugin by Oskar Hane and enhanced by Dee Nutbourne
Author URI: https://www.simbahosting.co.uk
Version: 1.14.3
Text Domain: two-factor-authentication
Domain Path: /languages
License: GPLv2 or later
*/

// N.B. We don't use __DIR__ (PHP 5.4+) because we want to show a clean error on earlier versions - i.e. this file still supports earlier versions.
if (!empty($GLOBALS['simba_two_factor_authentication']) && file_exists(dirname(__FILE__).'/simba-tfa/premium/loader.php')) {
	throw new Exception('To activate Two Factor Authentication Premium, first de-activate the free version (only one can be active at once).');
}

if (!defined('SIMBA_TFA_TEXT_DOMAIN')) define('SIMBA_TFA_TEXT_DOMAIN', 'two-factor-authentication');
if (!class_exists('Simba_Two_Factor_Authentication')) require dirname(__FILE__).'/simba-tfa/simba-tfa.php';

/**
 * This parent-child relationship enables the two to be split without affecting backwards compatibility for developers making direct calls
 * 
 * This class is for the plugin encapsulation.
 */
class Simba_Two_Factor_Authentication_Plugin extends Simba_Two_Factor_Authentication {
	
	public $version = '1.14.3';
	
	const PHP_REQUIRED = '5.6';
	
	/**
	 * Constructor, run upon plugin initiation
	 *
	 * @uses __FILE__
	 */
	public function __construct() {
		
		add_action('plugins_loaded', array($this, 'plugins_loaded_load_textdomain'));
		
		if (version_compare(PHP_VERSION, self::PHP_REQUIRED, '<' )) {
			add_action('all_admin_notices', array($this, 'admin_notice_insufficient_php'));
			$abort = true;
		}
		
		if (!function_exists('mcrypt_get_iv_size') && !function_exists('openssl_cipher_iv_length')) {
			add_action('all_admin_notices', array($this, 'admin_notice_missing_mcrypt_and_openssl'));
			$abort = true;
		}
		
		if (!empty($abort)) return;
		
		// Menu entries
		add_action('admin_menu', array($this, 'menu_entry_for_admin'));
		add_action('admin_menu', array($this, 'menu_entry_for_user'));
		add_action('network_admin_menu', array($this, 'menu_entry_for_user'));
		
		// Add settings link in plugin list
		$plugin = plugin_basename(__FILE__); 
		add_filter("plugin_action_links_$plugin", array($this, 'add_plugin_settings_link'));
		add_filter("network_admin_plugin_action_links_$plugin", array($this, 'add_plugin_settings_link'));
		
		parent::__construct();
		
	}
	
	/**
	 * Give the filesystem path to the plugin's vendor directory
	 *
	 * @return String
	 */
	public function vendor_dir() {
		return __DIR__.'/vendor';
	}
	
	/**
	 * Runs upon the WP filters plugin_action_links_(plugin) and network_plugin_action_links_(plugin)
	 *
	 * @param Array $links
	 *
	 * @return Array
	 */
	public function add_plugin_settings_link($links) {
		if (!is_network_admin()) {
			$link = '<a href="options-general.php?page=two-factor-auth">'.__('Plugin settings', 'two-factor-authentication').'</a>';
			array_unshift($links, $link);
		} else {
			switch_to_blog(1);
			$link = '<a href="'.admin_url('options-general.php').'?page=two-factor-auth">'.__('Plugin settings', 'two-factor-authentication').'</a>';
			restore_current_blog();
			array_unshift($links, $link);
		}
		
		$link2 = '<a href="admin.php?page=two-factor-auth-user">'.__('User settings', 'two-factor-authentication').'</a>';
		array_unshift($links, $link2);
		
		return $links;
	}
	
	/**
	 * Runs upon the WP actions admin_menu and network_admin_menu
	 */
	public function menu_entry_for_user() {
		
		$this->get_totp_controller()->potentially_port_private_keys();
		
		global $current_user;
		if ($this->is_activated_for_user($current_user->ID)) {
			add_menu_page(__('Two Factor Authentication', 'two-factor-authentication'), __('Two Factor Auth', 'two-factor-authentication'), 'read', 'two-factor-auth-user', array($this, 'show_dashboard_user_settings_page'), $this->includes_url().'/tfa_admin_icon_16x16.png', 72);
		}
	}
	
	/**
	 * Runs upon the WP action admin_menu
	 */
	public function menu_entry_for_admin() {
		
		$this->get_totp_controller()->potentially_port_private_keys();
		
		if (is_multisite() && (!is_super_admin() || !is_main_site())) return;
		
		add_options_page(
			__('Two Factor Authentication', 'two-factor-authentication'),
			__('Two Factor Authentication', 'two-factor-authentication'),
			$this->get_management_capability(),
			'two-factor-auth',
			array($this, 'show_admin_settings_page')
		);
	}
	
	/**
	 * Include the admin settings page code
	 */
	public function show_admin_settings_page() {
		$totp_controller = $this->get_totp_controller();
		$totp_controller->setUserHMACTypes();
		if (!is_admin() || !current_user_can($this->get_management_capability())) return;
		$this->include_template('admin-settings.php', array('totp_controller' => $totp_controller));
	}
	
	/**
	 * Runs conditionally on the WP action all_admin_notices
	 */
	public function admin_notice_insufficient_php() {
		$this->show_admin_warning('<strong>'.__('Higher PHP version required', 'two-factor-authentication').'</strong><br> '.sprintf(__('The Two Factor Authentication plugin requires PHP version %s or higher - your current version is only %s.', 'two-factor-authentication'), self::PHP_REQUIRED, PHP_VERSION), 'error');
	}
	
	/**
	 * Runs conditionally on the WP action all_admin_notices
	 */
	public function admin_notice_missing_mcrypt_and_openssl() {
		$this->show_admin_warning('<strong>'.__('PHP OpenSSL or mcrypt module required', 'two-factor-authentication').'</strong><br> '.__('The Two Factor Authentication plugin requires either the PHP openssl (preferred) or mcrypt module to be installed. Please ask your web hosting company to install one of them.', 'two-factor-authentication'), 'error');
	}
	
	/**
	 * Run upon the WP plugins_loaded action. This method is called even if main loading aborts - so don't put anything else in it (use a separate method).
	 */
	public function plugins_loaded_load_textdomain() {
		load_plugin_textdomain(
			'two-factor-authentication',
			false,
			dirname(plugin_basename(__FILE__)).'/languages/'
		);
	}
}

$GLOBALS['simba_two_factor_authentication'] = new Simba_Two_Factor_Authentication_Plugin();
