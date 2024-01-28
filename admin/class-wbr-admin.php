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
	}
	
	public function is_woocommerce_active() {

		$active_plugins = (array) get_option( 'active_plugins', array() );
		if ( is_multisite() ) {
			$active_plugins = array_merge( $active_plugins, get_site_option( 'active_sitewide_plugins', array() ) );
		}
		if ( in_array( 'woocommerce/woocommerce.php', $active_plugins ) || array_key_exists( 'woocommerce/woocommerce.php', $active_plugins ) ) {
			return true;
		} else {
			return false;
		}
	}

	public function add_integration( $integrations ) {

		if ( $this->is_woocommerce_active() ) {
			include_once 'class-wbr-wc-integration.php';
			$integrations[] = 'Wbr_Wc_Integration';

			return $integrations;
		} else {
			add_action( 'admin_notices', array( $this, 'notice_activate_wc' ) );
		}

		return null;
	}	

	public function notice_activate_wc() { ?>
		<div class="error">
			<p>
				<?php
				printf( esc_html__( 'Please install and activate %1$sWooCommerce%2$s to use Woober!' ), '<a href="' . esc_url( admin_url( 'plugin-install.php?tab=search&s=WooCommerce&plugin-search-input=Search+Plugins' ) ) . '">', '</a>' );
				?>
			</p>
		</div>
		<?php
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/wbr-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/wbr-admin.js', array( 'jquery' ), $this->version, false );
		
		wp_localize_script( $this->plugin_name, 'wbr_delivery_params', array(
			'url' => admin_url('admin-ajax.php'),
			'nonce' => wp_create_nonce('wbr_delivery_nonce'),
		));

	}

	public function register_settings() {
		register_setting( 'woober_settings', 'wbr-api-customer-id', 	array( 'type' => 'string', 'default' => '' ) );
		register_setting( 'woober_settings', 'wbr-api-client-id', 		array( 'type' => 'string', 'default' => '' ) );
		register_setting( 'woober_settings', 'wbr-api-client-secret', 	array( 'type' => 'string', 'default' => '' ) );
	}

}
