<?php

require_once UDW_ABSPATH . 'integrations/uberdirect/class-udw-ud-api.php';

if ( ! class_exists( 'Udw_Wc_Shipping_Method' ) ) {
	class Udw_Wc_Shipping_Method extends WC_Shipping_Method
	{
		private $ud_api;

		public function __construct( $instance_id = 0 )
		{
			$this->instance_id 			= absint( $instance_id );
			$this->id                 	= 'UBERDIRECT_SHIPPING_METHOD';
			$this->method_title       	= 'Uber Direct';
			$this->title              	= 'Uber Direct';
			$this->method_description 	= __('Uber Direct delivery service.', 'uberdirect');
			$this->instance_settings  	= array('title' => 'Uber Direct');
			$this->supports 			= array('shipping-zones', 'instance-settings', 'instance-settings-modal');

			$this->ud_api = new Udw_Ud_Api();
			$this->init();
		}

		function init() 
		{
			$this->init_settings();
			add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
		}

		/**
		 * @param array $package
		 */
		public function calculate_shipping($package = array()) 
		{
			$destination 	= $package['destination']['address'] . ', ' . $package['destination']['postcode'];
			$delivery_quote = $this->ud_api->create_quote($destination);

			if(isset($delivery_quote['fee']) && $delivery_quote['fee'])
			{
				$delivery_variation = floatval(get_option('udw-extra_fee')); // Maximum variable of variation price for delivery
				$delivery_cost 		= ($delivery_quote['fee'] / 100) + $delivery_variation;

				// Convert delivery ETA to WooCommerce format
				$dropoff_eta = new WC_DateTime($delivery_quote['dropoff_eta']);
				$current_date = new WC_DateTime();

				if ($dropoff_eta->format('Y-m-d') === $current_date->format('Y-m-d')) {
					$formatted_eta = __('today, ', 'uberdirect') . $dropoff_eta->format('H:i');
				}
				else {
					$formatted_eta = wc_format_datetime($dropoff_eta, 'D, d/m, H:m');
				}
				
				$rate = array
				(
					'id'       => $this->id,
					'label'    => $this->title . sprintf(' (%s)', $formatted_eta),
					'cost'     => $delivery_cost,
					'calc_tax' => 'per_order'
				);
				
				$this->add_rate($rate);
			}
		}
	}
}
