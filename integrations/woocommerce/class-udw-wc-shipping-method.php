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

			$current_time 		= current_datetime();
			$pickup_time_start 	= DateTime::createFromFormat('H:i', get_option('udw-pickup_time-start'), wp_timezone());
			$pickup_time_end 	= DateTime::createFromFormat('H:i', get_option('udw-pickup_time-end'), wp_timezone());
			$pickup_time 		= '';

			// Checks if the shop doesn't delivers at weekends ant the current day is Saturday (6) or Sunday (7) using ISO-8601 format (Monday = 1, ... Sunday = 7)
			if(in_array($current_time->format('N'), array(6, 7)) && get_option('udw-pickup_time-weekend') == 'no')
			{
				// Clones the pickup start time to avoid modifying the original object
				$pickup_time = clone $pickup_time_start;

				// Calculates how many days until next Monday. If it's Saturday (6): 8 - 6 = 2 days; if it's Sunday (7): 8 - 7 = 1 day.
				$days_to_add = 8 - $current_time->format('N');
				$pickup_time->modify("+{$days_to_add} days");
			}
			elseif ($current_time->format('H:i') > $pickup_time_end->format('H:i'))
			{
				$pickup_time = clone $pickup_time_start;

				// If current day is friday
				if($current_time->format('N') == 5 && get_option('udw-pickup_time-weekend') == 'no')
				{
					// If it's Friday and the current time exceeds the defined limit, schedules for the next Monday
					$pickup_time->modify("+3 days");
				}
				else
				{
					// If the current time exceeds the defined limit, schedules for the next day
					$pickup_time->modify('+1 day');
				}
			}
			else
			{
				// Otherwise, retains the current time to calculate the delivery
				$pickup_time = $current_time;
			}

			$delivery_quote = $this->ud_api->create_quote($destination, $pickup_time->format(DateTimeInterface::RFC3339));

			if(isset($delivery_quote['fee']) && $delivery_quote['fee'])
			{
				$delivery_variation = floatval(get_option('udw-extra_fee')); // Maximum variable of variation price for delivery
				$delivery_cost 		= ($delivery_quote['fee'] / 100) + $delivery_variation;

				// Convert delivery ETA to WooCommerce format
				$dropoff_eta = DateTime::createFromFormat(DateTimeInterface::RFC3339, $delivery_quote['dropoff_eta']);
				$dropoff_eta->setTimezone(wp_timezone());

				$dropoff_deadline = DateTime::createFromFormat(DateTimeInterface::RFC3339, $delivery_quote['dropoff_deadline']);
				$dropoff_deadline->setTimezone(wp_timezone());
				
				$rate = array
				(
					'id'       	=> $this->id,
					'label'    	=> $this->title,
					'cost'     	=> $delivery_cost,
					'meta_data' => array
						(
							'duration' 			=> $delivery_quote['duration'],
							'dropoff_eta' 		=> $dropoff_eta,
							'dropoff_deadline' 	=> $dropoff_deadline
						)
				);
				
				$this->add_rate($rate);
			}
		}
	}
}
