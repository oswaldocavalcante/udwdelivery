<?php

/**
 * @package           UDWDelivery
 *
 * @wordpress-plugin
 * Plugin Name:       UDW Delivery - Uber Direct for WooCommerce
 * Plugin URI:        https://github.com/oswaldocavalcante/udwdelivery
 * Description:       Delivery service for WooCommerce integrating with Uber Direct API.
 * Version:           2.2.5
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
if (!defined('WPINC')) die;

if (!defined('UDWD_PLUGIN_FILE')) define('UDWD_PLUGIN_FILE', __FILE__);
define('UDWD_ABSPATH', dirname(UDWD_PLUGIN_FILE) . '/');
define('UDWD_BASENAME', plugin_basename(__FILE__));
define('UDWD_VERSION', '2.2.5');

require plugin_dir_path( __FILE__ ) . 'includes/class-udwd.php';

function udwd_run() 
{
	$plugin = new UDWD();
}

udwd_run();