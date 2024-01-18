<?php

function wbr_shipping_method() {
	if ( ! class_exists( 'Wbr_Wc_Shipping_Method' ) ) {
		class Wbr_Wc_Shipping_Method extends WC_Shipping_Method {

			public function __construct( $instance_id = 0 ) {
				$this->instance_id = absint( $instance_id );

				$this->id                 = 'WOOBER_SHIPPING_METHOD';
				$this->method_title       = 'Woober';
				$this->title              = 'Woober';
				$this->method_description = __( 'Uber Direct delivery.' );
				$settings                 = array( 'title' => 'Woober' );
				$this->instance_settings  = $settings;

				$this->supports = array(
					'shipping-zones',
					'instance-settings',
					'instance-settings-modal',
				);

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
				var_dump( $package );
			}
		}
	}
}
