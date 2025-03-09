<?php

require_once DDW_ABSPATH . 'integrations/uberdirect/class-ddw-ud-api.php';
require_once DDW_ABSPATH . 'integrations/uberdirect/class-ddw-ud-manifest-item.php';

class Ddw_Wc_Integration extends WC_Integration
{
	private $ddw_ud_api;

	public function __construct()
	{
		$this->id = 'directdelivery';
		$this->method_title = 'Direct Delivery';
		$this->method_description = __('Uber Direct delivery service for Woocommerce.', 'directdelivery');

		$this->init_form_fields();
		$this->init_settings();

		$this->ddw_ud_api = new Ddw_Ud_Api();
		$this->define_woocommerce_hooks();
	}

	private function define_woocommerce_hooks()
	{
		add_action('woocommerce_update_options_integration_'.$this->id, array($this, 'process_admin_options'));
		add_action('woocommerce_shipping_init', 						array($this, 'create_shipping_method'));
		add_filter('woocommerce_shipping_methods', 						array($this, 'add_shipping_method'));
		add_filter('manage_edit-shop_order_columns', 					array($this, 'add_order_list_column'), 20); 					// Legacy orders page.
		add_action('manage_shop_order_posts_custom_column',				array($this, 'add_order_list_column_buttons_legacy'), 20, 2); 	// Legacy orders page.
		add_filter('woocommerce_shop_order_list_table_columns', 		array($this, 'add_order_list_column'));							// HPOS orders page.
		add_action('woocommerce_shop_order_list_table_custom_column', 	array($this, 'add_order_list_column_buttons_hpos'),  10, 2);	// HPOS orders page.
	}

	public function init_form_fields()
	{
		$this->form_fields = array
		(
			// Access Credentials settings
			'ddw-api-section' => array
			(
				'title'       => __('Access Credentials', 'directdelivery'),
				'type'        => 'title',
				/* translators: %s: Uber Direct API documentation URL */
				'description' => sprintf(__('See how to create your account and get your credentials in <a href="%1$s" target="blank">%2$s</a>.', 'directdelivery'), 'https://developer.uber.com/docs/deliveries/get-started', 'https://developer.uber.com/docs/deliveries/get-started'),
			),
			'ddw-api-customer-id' => array
			(
				'title'       	=> __('Customer ID', 'directdelivery'),
				'type'        	=> 'text',
				'description' 	=> __('Your Customer (Business ID) in Uber Direct settings.', 'directdelivery'),
				'default'     	=> '',
			),
			'ddw-api-client-id' => array
			(
				'title'       	=> __('Client ID', 'directdelivery'),
				'type'        	=> 'text',
				'description' 	=> __('Your Client ID in Uber Direct settings.', 'directdelivery'),
				'default'     	=> '',
			),
			'ddw-api-client-secret' => array
			(
				'title'       	=> __('Client Secret', 'directdelivery'),
				'type'        	=> 'text',
				'description' 	=> __('Your Client Secret in Uber Direct settings.', 'directdelivery'),
				'default'     	=> '',
			),
			
			// Delivery deadlines settings
			'ddw-pickup_time-section' => array
			(
				'title'       	=> __('Pickup daily time', 'directdelivery'),
				'type'        	=> 'title',
				'description' 	=> __('Set the daily time for the delivery service. This will be used to show to the clients the delivery deadlines.', 'directdelivery')
			),
			'ddw-pickup_time-start' => array
			(
				'title' 		=> __('Starting pickups', 'directdelivery'),
				'type' 			=> 'time',
				'description' 	=> __('Enter the starting time that couriers can pickup orders at your store to delivery.', 'directdelivery'),
				'default' 		=> '08:00',
			),
			'ddw-pickup_time-end' => array
			(
				'title' 		=> __('Ending pickups', 'directdelivery'),
				'type' 			=> 'time',
				'description' 	=> __('Enter the ending time that couriers can pickup orders at your store to delivery. After this time, the delivery deadline shown will consider the next avaliable day.', 'directdelivery'),
				'default' 		=> '16:00',
			),
			'ddw-pickup_time-weekend' => array
			(
				'title' 		=> __('Weekend pickups', 'directdelivery'),
				'type' 			=> 'checkbox',
				'default' 		=> 'no',
				'description' 	=> __('Select if couriers can pickup orders at your store in the weekends.', 'directdelivery'),
			),
			'ddw-pickup_time-processing' => array(
				'title' 		=> __('Processing time', 'directdelivery'),
				'type' 			=> 'number',
				'description' 	=> __('The average time needed to prepare the package to be ready for pickup.', 'directdelivery'),
				'default' 		=> 40,
			),
			
			// Fee settings
			'ddw-general-section' => array
			(
				'title'       	=> __('General settings', 'directdelivery'),
				'type'        	=> 'title',
				'description' 	=> __('Set the store\'s phone number and an extra fee to compensate the variation between the quote price and the actual delivery price. This difference, if positive, will be given as a tip for the driver.', 'directdelivery')
			),
			'ddw-phone_number' => array(
				'title' 		=> __('Phone number', 'directdelivery'),
				'type' 			=> 'tel',
				'description' 	=> __('Enter the complete store\'s phone number, including the country\'s calling code with (+) symbol.', 'directdelivery'),
				'default' 		=> '',
				'placeholder' 	=> '+55 11 1234-5678'
			),
			'ddw-extra_fee' => array
			(
				'title' 		=> __('Extra fee', 'directdelivery'),
				'type' 			=> 'price',
				'description' 	=> __('Enter a value with one monetary decimal point (,) without thousand separators and currency symbols.', 'directdelivery'),
				'default' 		=> 0,
				'placeholder' 	=> '0,00'
			),
		);
	}

	public function admin_options()
	{
		$changed_customer_id 	= update_option('ddw-api-customer-id', 		$this->get_option('ddw-api-customer-id'));
		$changed_client_id 		= update_option('ddw-api-client-id', 		$this->get_option('ddw-api-client-id'));
		$changed_client_secret 	= update_option('ddw-api-client-secret', 	$this->get_option('ddw-api-client-secret'));

		update_option('ddw-pickup_time-start', 		$this->get_option('ddw-pickup_time-start'));
		update_option('ddw-pickup_time-end', 		$this->get_option('ddw-pickup_time-end'));
		update_option('ddw-pickup_time-weekend',	$this->get_option('ddw-pickup_time-weekend'));
		update_option('ddw-pickup_time-processing', $this->get_option('ddw-pickup_time-processing'));
		update_option('ddw-extra_fee', 				$this->get_option('ddw-extra_fee') ? $this->get_option('ddw-extra_fee') : 0);
		update_option('ddw-phone_number', 			$this->get_option('ddw-phone_number'));

		if($changed_customer_id | $changed_client_id | $changed_client_secret)
		{
			set_transient('ddw-api-access-token', false);
			$this->ddw_ud_api->get_access_token();
		}

		echo '<div id="ddw-settings">';
		echo '<h2>' . esc_html($this->get_method_title()) . '</h2>';
		if ($this->ddw_ud_api->get_access_token()) {
			echo '<span class="ddw-integration-connection dashicons-before dashicons-yes-alt">' . esc_html__('Connected', 'directdelivery') . '</span>';
		} else {
			wp_admin_notice(__('Uber Direct: Set your API access credentials.', 'directdelivery'), array('type' => 'error'));
		}
		echo wp_kses_post(wpautop($this->get_method_description()));
		echo '<div><input type="hidden" name="section" value="' . esc_attr($this->id) . '" /></div>';
		echo '<table class="form-table">' . $this->generate_settings_html($this->get_form_fields(), false) . '</table>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '</div>';
	}

	public function create_shipping_method()
	{
		include_once DDW_ABSPATH . 'integrations/woocommerce/class-ddw-wc-shipping-method.php';
	}

	public function add_shipping_method($methods)
	{
		$methods['UBERDIRECT_SHIPPING_METHOD'] = 'Ddw_Wc_Shipping_Method';

		return $methods;
	}

	function add_order_list_column($columns)
	{
		if (!$this->ddw_ud_api->get_access_token()) 
		{
			wp_admin_notice(__('Uber Direct: Set your API access credentials.', 'directdelivery'), array('type' => 'error'));
		}

		$reordered_columns = array();

		foreach ($columns as $key => $column)
		{
			$reordered_columns[$key] = $column;
			if ($key == 'order_status') 
			{
				// Inserting after "Status" column
				$reordered_columns['ddw-shipping'] = __('Direct Delivery', 'directdelivery');
			}
		}

		return $reordered_columns;
	}

	function add_order_list_column_buttons_legacy($column, $order_id)
	{
		if ($column === 'ddw-shipping')
		{
			$order = wc_get_order($order_id);
			if(!$order) return;

			if ($order->get_shipping_total() == 0 || !$order->meta_exists('_udw_delivery_id')) // Checks if the order isnt set to delivery
			{
				echo esc_html($order->get_shipping_method());
			}
			else
			{
				$css_classes = 'button button-large ';
				$button_label = '';

				if ($order->meta_exists('_udw_delivery_id')) // Checks if the order has not been sended
				{ 
					$button_label = __('See delivery', 'directdelivery');
				} 
				else
				{
					$css_classes .= 'button-primary ';
					$button_label = __('Send now', 'directdelivery');

					if (!$order->is_paid() | $order->get_status() == 'completed') {
						$css_classes .= 'disabled ';
					}
				}

				echo 
				'<a 
					id="ddw-button-pre-send"
					data-order-id="' . esc_attr($order_id) . '"
					class="' . esc_attr($css_classes) . '"
				>';
				echo esc_html($button_label);
				echo '</a>';
			}
		}
	}

	function add_order_list_column_buttons_hpos($column, $post_or_order_object)
	{
		if ($column === 'ddw-shipping')
		{
			$order = ($post_or_order_object instanceof WP_Post) ? wc_get_order($post_or_order_object->ID) : $post_or_order_object;
			// Note: $post_or_order_object should not be used directly below this point.
			if (!$order) return;
			$order_existis = $order->meta_exists('_udw_delivery_id');
			$shipping_total = $order->get_shipping_total();

			if (!$order->meta_exists('_udw_delivery_id')) // Checks if the order isnt set to delivery
			{
				echo esc_html($order->get_shipping_method());
			}
			else
			{
				$css_classes = 'button button-large ';
				$button_label = '';

				if ($order->meta_exists('_udw_delivery_id')) // Checks if the order has not been sended
				{ 
					$button_label = __('See delivery', 'directdelivery');
				}
				else
				{
					$css_classes .= 'button-primary ';
					$button_label = __('Send now', 'directdelivery');

					if (!$order->is_paid() | $order->get_status() == 'completed')
					{
						$css_classes .= 'disabled ';
					}
				}

				echo
				'<a 
					id="ddw-button-pre-send"
					data-order-id="' . esc_attr($order->get_id()) . '"
					class="' . esc_attr($css_classes) . '"
				>';
				echo esc_html($button_label);
				echo '</a>';
			}
		}
	}
}
