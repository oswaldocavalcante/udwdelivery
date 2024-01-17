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
class Wbr_Admin_Settings {

	private $api_client_id;
	private $api_client_secret;
	private $api_access_token;

	private $api_pickup_address;

	public function __construct() {

		$this->api_client_id = 		get_option( 'wbr-api-client-id' );
		$this->api_client_secret = 	get_option( 'wbr-api-client-secret' );
		$this->api_access_token = 	get_option( 'wbr-api-access-token' );
	}

	public function get_api_access_token() {

		if( get_option( 'wbr-api-access-token' ) == '' ) {
			$response = wp_remote_post( 
				'https://auth.uber.com/oauth/v2/token', 
				array(
					'headers' => array(
						'Content-Type' => 'application/x-www-form-urlencoded',
					),
					'body' => array(
						'client_id' => $this->api_client_id,
						'client_secret' => $this->api_client_secret,
						'grant_type' => 'client_credentials',
						'scope' => 'eats.deliveries',
					)
				)
			);

			$body = json_decode( wp_remote_retrieve_body($response), true );
			$this->api_access_token = $body['access_token'];

			update_option( 'wbr-api-access-token', $this->api_access_token );
		}

		return $this->api_access_token;
	}

	public function display() {
		
		$this->get_api_access_token();
		require_once 'partials/wbr-admin-display-settings.php';
	}

	public function get_api_pickup_address() {

		return $this->api_pickup_address;
	}
}
