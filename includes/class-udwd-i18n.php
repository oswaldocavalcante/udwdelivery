<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://oswaldocavalcante.com
 * @since      1.0.0
 *
 * @package    UDWDelivery
 * @subpackage UDWDelivery/includes
 */

if(!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    UDWDelivery
 * @subpackage UDWDelivery/includes
 * @author     Oswaldo Cavalcante <contato@oswaldocavalcante.com>
 */
class UDWD_i18n 
{
	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() 
	{
		load_plugin_textdomain
		(
			'udwdelivery',
			false,
			dirname(dirname(plugin_basename( __FILE__ ))) . '/languages/'
		);
	}
}
