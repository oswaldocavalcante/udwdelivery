<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://oswaldocavalcante.com
 * @since      1.0.0
 *
 * @package    Wbr
 * @subpackage Wbr/admin
 */

include_once 'class-wbr-admin-settings.php';
include_once 'class-wbr-admin-delivery.php';

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Wbr
 * @subpackage Wbr/admin
 * @author     Oswaldo Cavalcante <contato@oswaldocavalcante.com>
 */
class Wbr_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	private $wbr_admin_settings;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

		$this->wbr_admin_settings = new Wbr_Admin_Settings();
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Wbr_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Wbr_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/wbr-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Wbr_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Wbr_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/wbr-admin.js', array( 'jquery' ), $this->version, false );

	}

	public function wbr_admin_register_settings() {

		register_setting( 'woober_settings', 'wbr-api-customer-id', 	array( 'type' => 'string', 'default' => '' ) );
		register_setting( 'woober_settings', 'wbr-api-client-id', 		array( 'type' => 'string', 'default' => '' ) );
		register_setting( 'woober_settings', 'wbr-api-client-secret', 	array( 'type' => 'string', 'default' => '' ) );
		register_setting( 'woober_settings', 'wbr-api-access-token', 	array( 'type' => 'string', 'default' => '' ) );
	}

	public function wbr_admin_add_menu() {

		add_menu_page( 'Woober', 'Woober', 'manage_options', 'woober', array($this, 'wbr_admin_display_settings'), '', 57 );
		add_submenu_page( 'woober', 'Delivery', 'Delivery' ,'manage_options', 'woober-delivery', array($this, 'wbr_admin_display_delivery'), 1 );
	}

	public function wbr_admin_display_settings() {

		$this->wbr_admin_settings->display();
	}

	public function wbr_admin_display_delivery() {

		$admin_delivery = new Wbr_Admin_Delivery();
		$admin_delivery->create_quote(
			'Rua Hamilton de Barros Soutinho, 1460 - Jatiúca, Maceió - AL, 57035-690',
			'Av. Comendador Gustavo Paiva, 3741 - Mangabeiras, Maceió - AL, 57037-285'
		);
		$admin_delivery->display();
	}

}
