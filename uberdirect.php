<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://oswaldocavalcante.com
 * @since             1.0.0
 * @package           Udw
 *
 * @wordpress-plugin
 * Plugin Name:       Uber Direct for WooCommerce
 * Plugin URI:        https://github.com/oswaldocavalcante/uberdirect
 * Description:       Uber direct delivery service for WooCommerce.
 * Version:           1.4.1
 * Author:            Oswaldo Cavalcante
 * Author URI:        https://oswaldocavalcante.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       uberdirect
 * Domain Path:       /languages
 * Requires Plugins:  woocommerce
 * Tested up to: 6.6.2
 * Requires PHP: 7.2
 * WC requires at least: 4.0
 * WC tested up to: 9.3.3
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if (!defined('UDW_PLUGIN_FILE')) {
	define('UDW_PLUGIN_FILE', __FILE__);
}

define('UDW_VERSION', '1.4.1');
define('UDW_ABSPATH', dirname(UDW_PLUGIN_FILE) . '/');
define('UDW_URL', plugins_url('/', __FILE__));

/**
 * The code that runs during plugin activation.
 */
function activate_udw() 
{
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-uberdirect-activator.php';
	UberDirect_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_udw() 
{
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-uberdirect-deactivator.php';
	UberDirect_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_udw' );
register_deactivation_hook( __FILE__, 'deactivate_udw' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-uberdirect.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_udw() 
{
	$plugin = new UberDirect();
}
run_udw();
