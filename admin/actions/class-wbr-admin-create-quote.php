<?php

class Wbr_Admin_Create_Quote {

	private $base_url;
    private $endpoint;
	private $customer_id;
	private $access_token;

    private $quote;
    private $kind;
    private $id;
    private $created;
    private $expires;
    private $fee;
    private $currence_type;
    private $dropoff_eta;
    private $duration;
    private $pickup_duration;
    private $dropoff_deadline;

    public function __construct( $base_url, $endopint, $customer_id, $access_token ) {

        $this->base_url = $base_url;
        $this->endpoint = $endopint;
        $this->customer_id = $customer_id;
        $this->access_token = $access_token;
    }
    
    public function execute( $pickup_address, $dropoff_address ) {

		$url = $this->base_url . $this->customer_id . $this->endpoint;

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
    }

    public function get_kind() {
        return $this->kind;
    }

    public function get_id() {
        return $this->id;
    }

    public function get_created() {
        return $this->created;
    }

    public function get_expires() {
        return $this->expires;
    }

    public function get_fee() {
        return $this->fee;
    }

    public function get_currency_type() {
        return $this->currency_type;
    }

    public function get_dropoff_area() {
        return $this->dropoff_area;
    }

    public function get_duration() {
        return $this->duration;
    }

    public function get_deadline() {
        return $this->deadline;
    }

}