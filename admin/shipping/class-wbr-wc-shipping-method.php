<?php

require_once 'class-wbr-ud-api.php';

if ( ! class_exists( 'Wbr_Wc_Shipping_Method' ) ) {
	class Wbr_Wc_Shipping_Method extends WC_Shipping_Method {

		private $ud_api;

		public function __construct( $instance_id = 0 ) {
			$this->instance_id = absint( $instance_id );

			$this->id                 = 'WOOBER_SHIPPING_METHOD';
			$this->method_title       = 'Uber Direct';
			$this->title              = 'Entrega (Uber Direct)';
			$this->method_description = __( 'Uber Direct delivery service.', 'woober' );
			$settings                 = array( 'title' => 'Woober' );
			$this->instance_settings  = $settings;

			$this->supports = array(
				'shipping-zones',
				'instance-settings',
				'instance-settings-modal',
			);

			$this->ud_api = new Wbr_Ud_Api();
			$this->init();
		}

		function init() {
			$this->init_settings();
			add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
		}

		/**
		 * @param array $package
		 */
		public function calculate_shipping( $package = array() ) {

			$destination = $package['destination']['address'] . ', ' . $package['destination']['postcode'];
			
			$delivery_quote = $this->ud_api->create_quote($destination);

			$rate = array(
				'id'       => $this->id,
				'label'    => $this->title,
				'cost'     => $delivery_quote['fee'] / 100,
				'calc_tax' => 'per_order',
			);

			$this->add_rate( $rate );
		}
	}
}
