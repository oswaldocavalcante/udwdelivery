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
class Wbr_Admin_Delivery {

	private $base_url;
	private $customer_id;
	private $access_token;

	private $quote;

	public function __construct() {
		
		$this->base_url = 'https://api.uber.com/v1/customers/';
		$this->customer_id = get_option( 'wbr-api-customer-id' );
		$this->access_token = get_option( 'wbr-api-access-token' );
	}

	// TODO: Conveter em uma classe
	public function create_quote( $pickup_address, $dropoff_address ) {

		$url = $this->base_url . $this->customer_id . '/delivery_quotes';

		$headers = array(
			'Content-Type' => 'application/json',
			'Authorization' => 'Bearer ' . $this->access_token,
		);

		$body = array(
			'pickup_address' => $pickup_address,
			'dropoff_address' => $dropoff_address,
		);

		$response = wp_remote_post( 
			$url, 
			array (
				'headers' => $headers,
				'body' => json_encode($body),
			) 
		);

		$this->quote = json_decode(wp_remote_retrieve_body($response), true);
		var_dump($this->quote);
		update_option( 'wbr-api-quote', $this->quote );

		return $this->quote;
	}

	public function display() {

		require_once 'partials/wbr-admin-display-delivery.php';
	}
}
