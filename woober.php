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
 * @package           Wbr
 *
 * @wordpress-plugin
 * Plugin Name:       Woober
 * Plugin URI:        https://http://github.com/oswaldocavalcante/woober
 * Description:       Uber delivery service for WooCommerce.
 * Version:           1.0.0
 * Author:            Oswaldo Cavalcante
 * Author URI:        https://oswaldocavalcante.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wbr
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'WBR_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-wbr-activator.php
 */
function activate_wbr() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wbr-activator.php';
	Wbr_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-wbr-deactivator.php
 */
function deactivate_wbr() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wbr-deactivator.php';
	Wbr_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_wbr' );
register_deactivation_hook( __FILE__, 'deactivate_wbr' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-wbr.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_wbr() {

	$plugin = new Wbr();
	$plugin->run();

}
run_wbr();
