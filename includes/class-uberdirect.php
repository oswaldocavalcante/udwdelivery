<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://oswaldocavalcante.com
 * @since      1.0.0
 *
 * @package    Udw
 * @subpackage Udw/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Udw
 * @subpackage Udw/includes
 * @author     Oswaldo Cavalcante <contato@oswaldocavalcante.com>
 */
class UberDirect
{
	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct()
	{
		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Udw_Loader. Orchestrates the hooks of the plugin.
	 * - Udw_i18n. Defines internationalization functionality.
	 * - Udw_Admin. Defines all hooks for the admin area.
	 * - Udw_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies()
	{
		require_once UDW_ABSPATH . 'includes/class-uberdirect-loader.php';
		require_once UDW_ABSPATH . 'includes/class-uberdirect-i18n.php';
		require_once UDW_ABSPATH . 'admin/class-udw-admin.php';
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Udw_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale()
	{
		$plugin_i18n = new UberDirect_i18n();
		add_action('plugins_loaded', array($plugin_i18n, 'load_plugin_textdomain'));
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks()
	{
		$plugin_admin = new Udw_Admin();

		add_action('admin_enqueue_scripts', 	array($plugin_admin, 'enqueue_styles'));
		add_action('admin_enqueue_scripts', 	array($plugin_admin, 'enqueue_scripts'));

		add_action('admin_init', 				array($plugin_admin, 'register_settings'));
		add_filter('woocommerce_integrations', 	array($plugin_admin, 'add_integration'));
		add_action('add_meta_boxes', 			array($plugin_admin, 'add_meta_box'));

		add_action('wp_ajax_udw_get_delivery', 	array($plugin_admin, 'ajax_get_delivery'));
		add_action('wp_ajax_udw_create_delivery', array($plugin_admin, 'ajax_create_delivery'));
		add_action('admin_footer', 				array($plugin_admin, 'add_modal_templates'));

		// Register admin-specific webhook
		add_action('rest_api_init', 			array($plugin_admin, 'register_webhook'));
		add_action('udw_change_order_status', 	array($plugin_admin, 'change_order_status'), 10, 2);
	}
}
