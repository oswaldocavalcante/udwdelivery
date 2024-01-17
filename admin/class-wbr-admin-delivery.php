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

include_once 'actions/class-wbr-admin-create-quote.php';

class Wbr_Admin_Delivery {

	private $base_url;
	private $customer_id;
	private $access_token;

	private $create_quote_endpoint;

	public function __construct() {
		
		$this->base_url = 'https://api.uber.com/v1/customers/';
		$this->customer_id = get_option( 'wbr-api-customer-id' );
		$this->access_token = get_option( 'wbr-api-access-token' );

		$this->create_quote_endpoint = '/delivery_quotes';
	}

	public function create_quote( $pickup_address, $dropoff_address ) {

		$create_quote = new Wbr_Admin_Create_Quote( $this->base_url, $this->create_quote_endpoint, $this->customer_id, $this->access_token );
		$create_quote->execute( $pickup_address, $dropoff_address );
	}

	public function display() {

		require_once 'partials/wbr-admin-display-delivery.php';
	}
}
