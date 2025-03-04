<?php

/**
 * @package           DirectDelivery
 *
 * @wordpress-plugin
 * Plugin Name:       Direct Delivery for WooCommerce
 * Plugin URI:        https://github.com/oswaldocavalcante/uberdirect
 * Description:       Delivery service for WooCommerce integrating with Uber Direct API.
 * Version:           2.0.0
 * Author:            Oswaldo Cavalcante
 * Author URI:        https://oswaldocavalcante.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       directdelivery
 * Domain Path:       /languages
 * Requires Plugins:  woocommerce
 * Requires PHP: 7.2
 * WC requires at least: 4.0
 * WC tested up to: 9.3.3
 */

// If this file is called directly, abort.
if (!defined( 'WPINC' )) { die; }

if (!defined('DDW_PLUGIN_FILE')) { define('DDW_PLUGIN_FILE', __FILE__); }
define('DDW_ABSPATH', dirname(DDW_PLUGIN_FILE) . '/');
define('DDW_VERSION', '2.0.0');
define('DDW_URL', plugins_url('/', __FILE__));

require plugin_dir_path( __FILE__ ) . 'includes/class-directdelivery.php';

function run_ddw() 
{
	$plugin = new DirectDelivery();
}

run_ddw();