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

class Wbr_Ud_Api {

	private $base_url;
	private $customer_id;
	private $delivery_id;
	private $access_token;

	private $endpoint_create_quote;
	private $endpoint_create_delivery;

	public function __construct() {
		
		$this->base_url = 'https://api.uber.com/v1/customers/';
		$this->customer_id = get_option( 'wbr-api-customer-id' );
		$this->access_token = get_option( 'wbr-api-access-token' );

		$this->endpoint_create_quote = $this->base_url . $this->customer_id . '/delivery_quotes';
		$this->endpoint_create_delivery = $this->base_url . $this->customer_id . '/deliveries';
		$this->endpoint_get_delivery = $this->base_url . $this->customer_id . '/deliveries/';
	}

	public function create_quote( $dropoff_address, $pickup_address = '' ) {

		$headers = array(
			'Content-Type' => 'application/json',
			'Authorization' => 'Bearer ' . $this->access_token,
		);

		$body = array(
			'pickup_address' => ($pickup_address == '') ? get_option('woocommerce_store_address') : $pickup_address,
			'dropoff_address' => $dropoff_address,
		);

		$response = wp_remote_post( 
			$this->endpoint_create_quote, 
			array (
				'headers' => $headers,
				'body' => json_encode($body),
			) 
		);

		$quote = json_decode(wp_remote_retrieve_body($response), true);
		return $quote;
	}

	public function create_delivery( $order_id, $dropoff_name, $dropoff_address, $dropoff_notes, $dropoff_phone_number, $manifest_items = array() ) {

		$headers = array(
			'Content-Type' => 'application/json',
			'Authorization' => 'Bearer ' . $this->access_token,
		);

		$body = array(
			'pickup_name' 			=> get_bloginfo('name'),
			'pickup_address' 		=> get_option('woocommerce_store_address'),
			'pickup_phone_number' 	=> '+558232355224',
			'dropoff_name' 			=> $dropoff_name,
			'dropoff_address' 		=> $dropoff_address,
			'dropoff_notes'			=> $dropoff_notes,
			'dropoff_phone_number' 	=> '+55' . $dropoff_phone_number,
			'manifest_items' 		=> $manifest_items,
			'external_id' 			=> $order_id,
		);

		$response = wp_remote_post( 
			$this->endpoint_create_delivery, 
			array (
				'headers' => $headers,
				'body' => json_encode($body),
			) 
		);

		$delivery = json_decode(wp_remote_retrieve_body($response));
		return $delivery;
	}

	public function get_delivery( $delivery_id ) {

		$headers = array(
			'Content-Type' => 'application/json',
			'Authorization' => 'Bearer ' . $this->access_token,
		);

		$response = wp_remote_get( 
			$this->endpoint_get_delivery . $delivery_id, 
			array (
				'headers' => $headers,
			)
		);

		$delivery = json_decode(wp_remote_retrieve_body($response));
		return $delivery;
	}

	public function display() {

		require_once 'pages/wbr-admin-display-delivery.php';
	}
}