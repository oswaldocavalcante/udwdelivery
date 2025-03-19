<?php

if(!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * The UberDirect API class.
 *
 * This class is responsible for handling the communication with the UberDirect API.
 *
 * @link       https://oswaldocavalcante.com
 * @since      1.0.0
 *
 * @package    UDWDelivery
 * @subpackage UDWDelivery/integrations/uberdirect
 */
class UDWD_UD_API
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
		$this->access_token = get_transient('udwd-api-access-token');
		$this->customer_id 	= get_option('udwd-api-customer-id');

		$this->endpoint_create_quote 	= $this->base_url . $this->customer_id . '/delivery_quotes';
		$this->endpoint_create_delivery = $this->base_url . $this->customer_id . '/deliveries';
		$this->endpoint_update_delivery = $this->base_url . $this->customer_id . '/deliveries/';
		$this->endpoint_cancel_delivery = $this->base_url . $this->customer_id . '/deliveries/';
		$this->endpoint_get_delivery 	= $this->base_url . $this->customer_id . '/deliveries/';
	}

	/**
	 * Retrieves the Access Token from the UberDirect API.
	 *
	 * @return string | bool The Access Token or false if the request fails.
	 * @see https://developer.uber.com/docs/deliveries/guides/authentication
	 */
	public function get_access_token()
	{
		// Checks if the Access Token is expired to regenate it
		if (false == get_transient('udwd-api-access-token'))
		{
			$response = wp_remote_post
			(
				'https://auth.uber.com/oauth/v2/token',
				array
				(
					'headers' => array
					(
						'Content-Type' => 'application/x-www-form-urlencoded',
					),
					'body' => array
					(
						'client_id' 	=> get_option('udwd-api-client-id'),
						'client_secret' => get_option('udwd-api-client-secret'),
						'grant_type' 	=> 'client_credentials',
						'scope' 		=> 'eats.deliveries',
					)
				)
			);

			$response_body = json_decode(wp_remote_retrieve_body($response), true);

			if (is_array($response_body) && array_key_exists('access_token', $response_body)) 
			{
				$this->access_token = $response_body['access_token'];
				set_transient('udwd-api-access-token', $this->access_token, $response_body['expires_in']);

				return true;
			} 
			else 
			{
				$this->log('Get access token', $response);

				return false;
			}
		}

		return $this->access_token;
	}

	/**
	 * Creates a delivery quote for a given destination address.
	 * 
	 * @param string $dropoff_address The destination address.
	 * @param string $pickup_ready_dt The date and time the package will be ready for pickup.
	 * @param string $pickup_address The address where the package will be picked up.
	 * @return array | WP_Error The delivery quote data or an error message.
	 * @see https://developer.uber.com/docs/deliveries/api-reference/daas#tag/Quotes/paths/~1customers~1%7Bcustomer_id%7D~1delivery_quotes/post
	 */
	public function create_quote($dropoff_address, $pickup_ready_dt = '', $pickup_address = '')
	{
		if($pickup_address == '')
		{
			$raw_base_address = array
			(
				'address_1' => WC()->countries->get_base_address(),
				'city'      => WC()->countries->get_base_city(),
				'state'     => WC()->countries->get_base_state(),
				'country'   => WC()->countries->get_base_country(),
				'postcode'  => WC()->countries->get_base_postcode(),
			);
			
			$pickup_address = WC()->countries->get_formatted_address($raw_base_address, ', ');
		}

		$headers = array
		(
			'Content-Type' => 'application/json',
			'Authorization' => 'Bearer ' . $this->get_access_token(),
		);

		$body = array
		(
			'pickup_address' => $pickup_address,
			'dropoff_address' => $dropoff_address,
			'pickup_ready_dt' => ($pickup_ready_dt == '') ? current_datetime()->format(DateTimeInterface::RFC3339) : $pickup_ready_dt,
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

		if (is_wp_error($response) || isset($response['response']['code']) && $response['response']['code'] != 200)
		{
			return $this->log('Create quote', $response);
		}

		$quote = json_decode(wp_remote_retrieve_body($response), true);

		return $quote;
	}

	/**
	 * Creates a delivery for a given order ID.
	 * 
	 * @param int $order_id The order ID.
	 * @param string $dropoff_name The name of the recipient.
	 * @param string $dropoff_address The address of the recipient.
	 * @param string $dropoff_notes The notes for the recipient.
	 * @param string $dropoff_phone_number The phone number of the recipient.
	 * @param array $manifest_items The items to be delivered.
	 * @return array | WP_Error The delivery data or an error message.
	 * @see https://developer.uber.com/docs/deliveries/api-reference/daas#tag/Delivery/paths/~1customers~1%7Bcustomer_id%7D~1deliveries/post
	 */
	public function create_delivery($order_id, $dropoff_name, $dropoff_address, $dropoff_notes, $dropoff_phone_number, $manifest_items = array())
	{
		$headers = array
		(
			'Content-Type' => 'application/json',
			'Authorization' => 'Bearer ' . $this->get_access_token(),
		);

		$body = array
		(
			'pickup_business_name'	=> get_bloginfo('name'),
			'pickup_name' 			=> get_bloginfo('name'),
			'pickup_address' 		=> get_option('woocommerce_store_address'),
			'pickup_phone_number' 	=> get_option('udwd-phone_number'),
			'dropoff_name' 			=> $dropoff_name,
			'dropoff_address' 		=> $dropoff_address,
			'dropoff_phone_number' 	=> $dropoff_phone_number,
			'dropoff_notes'			=> $dropoff_notes,
			'manifest_items' 		=> $manifest_items,
			'external_id' 			=> (string) $order_id,
		);

		$response = wp_remote_post
		(
			$this->endpoint_create_delivery,
			array
			(
				'headers' 	=> $headers,
				'body' 		=> json_encode($body),
			)
		);

		if (is_wp_error($response) || isset($response['response']['code']) && $response['response']['code'] != 200)
		{
			return $this->log('Create delivery', $response);
		}
		
		$delivery = json_decode(wp_remote_retrieve_body($response));

		return $delivery;
	}

	/**
	 * Updates the tip amount for a given delivery ID.
	 * 
	 * @param string $delivery_id The delivery ID.
	 * @param float $tip The tip amount.
	 * @return array | WP_Error The delivery data or an error message.
	 * @see https://developer.uber.com/docs/deliveries/api-reference/daas#tag/Delivery/paths/~1customers~1%7Bcustomer_id%7D~1deliveries~1%7Bdelivery_id%7D/post
	 */
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

		if (is_wp_error($response) || isset($response['response']['code']) && $response['response']['code'] != 200)
		{
			return $this->log('Update delivery', $response);
		}

		$delivery = json_decode(wp_remote_retrieve_body($response));

		return $delivery;
	}

	/**
	 * Retrieves the delivery data for a given delivery ID.
	 * 
	 * @param string $delivery_id The delivery ID.
	 * @return array | WP_Error The delivery data or an error message.
	 * @see https://developer.uber.com/docs/deliveries/api-reference/daas#tag/Delivery/paths/~1customers~1%7Bcustomer_id%7D~1deliveries~1%7Bdelivery_id%7D/get
	 */
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

		if (is_wp_error($response) || isset($response['response']['code']) && $response['response']['code'] != 200)
		{
			return $this->log('Get delivery', $response);
		}

		$delivery = json_decode(wp_remote_retrieve_body($response));

		return $delivery;
	}

	/**
	 * Cancels a delivery for a given delivery ID.
	 * 
	 * @param string $delivery_id The delivery ID.
	 * @return array | WP_Error The delivery data or an error message.
	 * @see https://developer.uber.com/docs/deliveries/api-reference/daas#tag/Delivery/paths/~1customers~1%7Bcustomer_id%7D~1deliveries~1%7Bdelivery_id%7D~1cancel/post
	 */
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

		if (is_wp_error($response) || isset($response['response']['code']) && $response['response']['code'] != 200)
		{
			return $this->log('Cancel delivery', $response);
		}

		$delivery = json_decode(wp_remote_retrieve_body($response));

		return $delivery;
	}

	/**
	 * Checks the response from the UberDirect API for a given delivery ID.
	 *
	 * @param string $request_name The request name sent to the UberDirect API.
	 * @param array | WP_Error $response The response data received from the UberDirect API.
	 * @return WP_Error The error message logged in WooCommerce logs.
	 */
	private function log($request_name, $response)
	{
		$logger = function_exists('wc_get_logger') ? wc_get_logger() : new WC_Logger();
		$logger_context = array('source' => 'udwdelivery');

		$code = '';
		$message = '';
		
		if(isset($response['response']['code']) && $response['response']['code'] != 200)
		{
			$error = json_decode(wp_remote_retrieve_body($response));

			if(isset($error->code) && isset($error->message))
			{
				$code = $error->code;
				$message = $request_name . ' - ' . $code . ': ' . $error->message;
			}
			elseif(isset($error->error) && isset($error->error_description))
			{
				$code = $error->error;
				$message = $request_name . ' - ' . $code . ': ' . $error->error_description;
			}
			else
			{
				$code = $response['response']['code'];
				$message = $request_name . ': ' . $response['response']['message'];
			}

			$logger->error($message, $logger_context);
		}
		elseif(is_wp_error($response))
		{
			$code = $response->get_error_code();
			$message = $request_name . ': ' . $response->get_error_message();
			$logger->error($message, $logger_context);
		}

		return new WP_Error($code, $message);
	}
}
