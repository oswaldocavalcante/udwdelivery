<?php

/**
 * @package           UDWDelivery
 *
 * @wordpress-plugin
 * Plugin Name:       UDW Delivery - Uber Direct for WooCommerce
 * Plugin URI:        https://github.com/oswaldocavalcante/udwdelivery
 * Description:       Delivery service for WooCommerce integrating with Uber Direct API.
 * Version:           2.2.1
 * Author:            Oswaldo Cavalcante
 * Author URI:        https://oswaldocavalcante.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       udwdelivery
 * Domain Path:       /languages
 * Requires Plugins:  woocommerce
 * Requires PHP: 7.2
 * WC requires at least: 4.0
 * WC tested up to: 9.3.3
 */

// If this file is called directly, abort.
if (!defined( 'WPINC' )) die;

if (!defined('UDW_PLUGIN_FILE')) define('UDW_PLUGIN_FILE', __FILE__);
define('UDW_ABSPATH', dirname(UDW_PLUGIN_FILE) . '/');
define('UDW_BASENAME', plugin_basename(__FILE__));
define('UDW_VERSION', '2.2.1');

require plugin_dir_path( __FILE__ ) . 'includes/class-udw.php';

function run_udw() 
{
	$plugin = new UDW();
}

run_udw();