<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://oswaldocavalcante.com
 * @since      1.0.0
 *
 * @package    Udw
 * @subpackage Udw/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Udw
 * @subpackage Udw/admin
 * @author     Oswaldo Cavalcante <contato@oswaldocavalcante.com>
 */

class Udw_Ud_Api
{
	private $access_token;
	private $base_url;
	private $customer_id;

	private $endpoint_create_quote;
	private $endpoint_create_delivery;
	private $endpoint_update_delivery;
	private $endpoint_cancel_delivery;
	private $endpoint_get_delivery;

	public function __construct()
	{
		$this->base_url 	= 'https://api.uber.com/v1/customers/';
		$this->access_token = get_transient('udw-api-access-token');
		$this->customer_id 	= get_option('udw-api-customer-id');

		$this->endpoint_create_quote 	= $this->base_url . $this->customer_id . '/delivery_quotes';
		$this->endpoint_create_delivery = $this->base_url . $this->customer_id . '/deliveries';
		$this->endpoint_update_delivery = $this->base_url . $this->customer_id . '/deliveries/';
		$this->endpoint_cancel_delivery = $this->base_url . $this->customer_id . '/deliveries/';
		$this->endpoint_get_delivery 	= $this->base_url . $this->customer_id . '/deliveries/';
	}

	public function get_access_token()
	{
		// Checks if the Access Token is expired to regenate it
		if (false === ($this->access_token = get_transient('udw-api-access-token')))
		{
			$response = wp_remote_post(
				'https://auth.uber.com/oauth/v2/token',
				array(
					'headers' => array(
						'Content-Type' => 'application/x-www-form-urlencoded',
					),
					'body' => array(
						'client_id' => get_option('udw-api-client-id'),
						'client_secret' => get_option('udw-api-client-secret'),
						'grant_type' => 'client_credentials',
						'scope' => 'eats.deliveries',
					)
				)
			);

			$response_body = json_decode(wp_remote_retrieve_body($response), true);

			if (array_key_exists('access_token', $response_body)) {
				$this->access_token = $response_body['access_token'];
				set_transient('udw-api-access-token', $this->access_token, $response_body['expires_in']);
			} else {
				return false;
			}
		}

		return $this->access_token;
	}

	public function create_quote($dropoff_address, $pickup_ready_dt = '', $pickup_address = '')
	{
		$headers = array
		(
			'Content-Type' => 'application/json',
			'Authorization' => 'Bearer ' . $this->get_access_token(),
		);

		$body = array
		(
			'pickup_address' => ($pickup_address == '') ? get_option('woocommerce_store_address') : $pickup_address,
			'pickup_ready_dt' => ($pickup_ready_dt == '') ? current_datetime()->format(DateTimeInterface::RFC3339) : $pickup_ready_dt,
			'dropoff_address' => $dropoff_address,
		);

		$response = wp_remote_post
		(
			$this->endpoint_create_quote,
			array
			(
				'headers' => $headers,
				'body' => json_encode($body),
			)
		);

		$quote = json_decode(wp_remote_retrieve_body($response), true);
		return $quote;
	}

	public function create_delivery($order_id, $dropoff_name, $dropoff_address, $dropoff_notes, $dropoff_phone_number, $manifest_items = array())
	{
		$headers = array
		(
			'Content-Type' => 'application/json',
			'Authorization' => 'Bearer ' . $this->get_access_token(),
		);

		$body = array
		(
			'pickup_name' 			=> get_bloginfo('name'),
			'pickup_business_name'	=> get_bloginfo('name'),
			'pickup_address' 		=> get_option('woocommerce_store_address'),
			'pickup_phone_number' 	=> '+558232355224',
			'dropoff_name' 			=> $dropoff_name,
			'dropoff_address' 		=> $dropoff_address,
			'dropoff_notes'			=> $dropoff_notes,
			'dropoff_phone_number' 	=> '+55' . $dropoff_phone_number,
			'manifest_items' 		=> $manifest_items,
			'external_id' 			=> $order_id,
			'idempotency_key'		=> $order_id,
		);

		$response = wp_remote_post
		(
			$this->endpoint_create_delivery,
			array
			(
				'headers' 	=> $headers,
				'body' 		=> wp_json_encode($body),
			)
		);

		$delivery = json_decode(wp_remote_retrieve_body($response));
		return $delivery;
	}

	public function update_delivery($delivery_id, $tip)
	{
		$headers = array
		(
			'Content-Type' 	=> 'application/json',
			'Authorization' => 'Bearer ' . $this->get_access_token(),
		);

		$body = array
		(
			'tip_by_customer' => $tip * 100, // Tip is an integer amount in cents
		);

		$response = wp_remote_post
		(
			$this->endpoint_update_delivery . $delivery_id,
			array
			(
				'headers' 	=> $headers,
				'body' 		=> wp_json_encode($body),
			)
		);

		$delivery = json_decode(wp_remote_retrieve_body($response));
		return $delivery;
	}

	public function get_delivery($delivery_id)
	{
		$headers = array
		(
			'Content-Type' 	=> 'application/json',
			'Authorization' => 'Bearer ' . $this->get_access_token(),
		);

		$response = wp_remote_get
		(
			$this->endpoint_get_delivery . $delivery_id, 
			array('headers' => $headers)
		);

		$delivery = json_decode(wp_remote_retrieve_body($response));
		return $delivery;
	}

	public function cancel_delivery($delivery_id)
	{
		$headers = array
		(
			'Content-Type' 	=> 'application/json',
			'Authorization' => 'Bearer ' . $this->get_access_token(),
		);

		$response = wp_remote_post
		(
			$this->endpoint_cancel_delivery . $delivery_id . '/cancel', 
			array('headers' => $headers)
		);

		$delivery = json_decode(wp_remote_retrieve_body($response));

		return $delivery;
	}
}
