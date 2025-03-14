<?php

require_once UDW_ABSPATH . 'integrations/uberdirect/class-udw-ud-api.php';
require_once UDW_ABSPATH . 'integrations/uberdirect/class-udw-ud-manifest-item.php';

class UDW_WC_Integration extends WC_Integration
{
	private $udw_ud_api;

	public function __construct()
	{
		$this->id = 'udwdelivery';
		$this->method_title = 'Uber Direct';
		$this->method_description = __('Uber Direct delivery service for Woocommerce.', 'udwdelivery');

		$this->init_form_fields();
		$this->init_settings();

		$this->udw_ud_api = new UDW_UD_API();
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
			'udw-api-section' => array
			(
				'title'       => __('Access Credentials', 'udwdelivery'),
				'type'        => 'title',
				/* translators: %s: Uber Direct API documentation URL */
				'description' => sprintf(__('See how to create your account and get your credentials in <a href="%1$s" target="blank">%2$s</a>.', 'udwdelivery'), 'https://developer.uber.com/docs/deliveries/get-started', 'https://developer.uber.com/docs/deliveries/get-started'),
			),
			'udw-api-customer-id' => array
			(
				'title'       	=> __('Customer ID', 'udwdelivery'),
				'type'        	=> 'text',
				'description' 	=> __('Your Customer (Business ID) in Uber Direct settings.', 'udwdelivery'),
				'default'     	=> '',
			),
			'udw-api-client-id' => array
			(
				'title'       	=> __('Client ID', 'udwdelivery'),
				'type'        	=> 'text',
				'description' 	=> __('Your Client ID in Uber Direct settings.', 'udwdelivery'),
				'default'     	=> '',
			),
			'udw-api-client-secret' => array
			(
				'title'       	=> __('Client Secret', 'udwdelivery'),
				'type'        	=> 'text',
				'description' 	=> __('Your Client Secret in Uber Direct settings.', 'udwdelivery'),
				'default'     	=> '',
			),
			
			// Delivery deadlines settings
			'udw-pickup_time-section' => array
			(
				'title'       	=> __('Pickup daily time', 'udwdelivery'),
				'type'        	=> 'title',
				'description' 	=> __('Set the daily time for the delivery service. This will be used to show to the clients the delivery deadlines.', 'udwdelivery')
			),
			'udw-pickup_time-start' => array
			(
				'title' 		=> __('Starting pickups', 'udwdelivery'),
				'type' 			=> 'time',
				'description' 	=> __('Enter the starting time that couriers can pickup orders at your store to delivery.', 'udwdelivery'),
				'default' 		=> '08:00',
			),
			'udw-pickup_time-end' => array
			(
				'title' 		=> __('Ending pickups', 'udwdelivery'),
				'type' 			=> 'time',
				'description' 	=> __('Enter the ending time that couriers can pickup orders at your store to delivery. After this time, the delivery deadline shown will consider the next avaliable day.', 'udwdelivery'),
				'default' 		=> '16:00',
			),
			'udw-pickup_time-weekend' => array
			(
				'title' 		=> __('Weekend pickups', 'udwdelivery'),
				'type' 			=> 'checkbox',
				'default' 		=> 'no',
				'description' 	=> __('Select if couriers can pickup orders at your store in the weekends.', 'udwdelivery'),
			),
			'udw-pickup_time-processing' => array(
				'title' 		=> __('Processing time', 'udwdelivery'),
				'type' 			=> 'number',
				'description' 	=> __('The average time needed to prepare the package to be ready for pickup.', 'udwdelivery'),
				'default' 		=> 40,
			),
			
			// Fee settings
			'udw-general-section' => array
			(
				'title'       	=> __('General settings', 'udwdelivery'),
				'type'        	=> 'title',
				'description' 	=> __('Set the store\'s phone number and an extra fee to compensate the variation between the quote price and the actual delivery price. This difference, if positive, will be given as a tip for the driver.', 'udwdelivery')
			),
			'udw-phone_number' => array(
				'title' 		=> __('Phone number', 'udwdelivery'),
				'type' 			=> 'tel',
				'description' 	=> __('Enter the complete store\'s phone number, including the country\'s calling code with (+) symbol.', 'udwdelivery'),
				'default' 		=> '',
				'placeholder' 	=> '+55 11 1234-5678'
			),
			'udw-extra_fee' => array
			(
				'title' 		=> __('Extra fee', 'udwdelivery'),
				'type' 			=> 'price',
				'description' 	=> __('Enter a value with one monetary decimal point (,) without thousand separators and currency symbols.', 'udwdelivery'),
				'default' 		=> 0,
				'placeholder' 	=> '0,00'
			),
		);
	}

	public function admin_options()
	{
		$changed_customer_id 	= update_option('udw-api-customer-id', 		$this->get_option('udw-api-customer-id'));
		$changed_client_id 		= update_option('udw-api-client-id', 		$this->get_option('udw-api-client-id'));
		$changed_client_secret 	= update_option('udw-api-client-secret', 	$this->get_option('udw-api-client-secret'));

		update_option('udw-pickup_time-start', 		$this->get_option('udw-pickup_time-start'));
		update_option('udw-pickup_time-end', 		$this->get_option('udw-pickup_time-end'));
		update_option('udw-pickup_time-weekend',	$this->get_option('udw-pickup_time-weekend'));
		update_option('udw-pickup_time-processing', $this->get_option('udw-pickup_time-processing'));
		update_option('udw-extra_fee', 				$this->get_option('udw-extra_fee') ? $this->get_option('udw-extra_fee') : 0);
		update_option('udw-phone_number', 			$this->get_option('udw-phone_number'));

		if($changed_customer_id | $changed_client_id | $changed_client_secret)
		{
			set_transient('udw-api-access-token', false);
			$this->udw_ud_api->get_access_token();
		}

		echo '<div id="udw-settings">';
		echo '<h2>' . esc_html($this->get_method_title()) . '</h2>';
		if ($this->udw_ud_api->get_access_token()) {
			echo '<span class="udw-integration-connection dashicons-before dashicons-yes-alt">' . esc_html__('Connected', 'udwdelivery') . '</span>';
		} else {
			wp_admin_notice(__('Uber Direct: Set your API access credentials.', 'udwdelivery'), array('type' => 'error'));
		}
		echo wp_kses_post(wpautop($this->get_method_description()));
		echo '<div><input type="hidden" name="section" value="' . esc_attr($this->id) . '" /></div>';
		echo '<table class="form-table">' . $this->generate_settings_html($this->get_form_fields(), false) . '</table>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '</div>';
	}

	public function create_shipping_method()
	{
		include_once UDW_ABSPATH . 'integrations/woocommerce/class-udw-wc-shipping-method.php';
	}

	public function add_shipping_method($methods)
	{
		$methods['UBERDIRECT_SHIPPING_METHOD'] = 'UDW_WC_Shipping_Method';

		return $methods;
	}

	function add_order_list_column($columns)
	{
		if (!$this->udw_ud_api->get_access_token()) 
		{
			wp_admin_notice(__('Uber Direct: Set your API access credentials.', 'udwdelivery'), array('type' => 'error'));
		}

		$reordered_columns = array();

		foreach ($columns as $key => $column)
		{
			$reordered_columns[$key] = $column;
			if ($key == 'order_status') 
			{
				// Inserting after "Status" column
				$reordered_columns['udw-shipping'] = __('Uber Direct', 'udwdelivery');
			}
		}

		return $reordered_columns;
	}

	function add_order_list_column_buttons_legacy($column, $order_id)
	{
		if ($column === 'udw-shipping')
		{
			$order = wc_get_order($order_id);
			if(!$order) return;

			if ($order->get_shipping_total() == 0) // Checks if the order isnt set to delivery
			{
				echo esc_html($order->get_shipping_method());
			}
			else
			{
				$css_classes = 'button button-large ';
				$button_label = '';

				if ($order->meta_exists('_udw_delivery_id')) // Checks if the order has not been sended
				{ 
					$button_label = __('See delivery', 'udwdelivery');
				} 
				else
				{
					$css_classes .= 'button-primary ';
					$button_label = __('Send now', 'udwdelivery');

					if (!$order->is_paid() | $order->get_status() == 'completed') {
						$css_classes .= 'disabled ';
					}
				}

				echo 
				'<a 
					id="udw-button-pre-send"
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
		if ($column === 'udw-shipping')
		{

			$order = ($post_or_order_object instanceof WP_Post) ? wc_get_order($post_or_order_object->ID) : $post_or_order_object;
			// Note: $post_or_order_object should not be used directly below this point.
			if (!$order) return;
			
			if ($order->get_shipping_total() == 0) // Checks if the order isnt set to delivery
			{
				echo esc_html($order->get_shipping_method());
			}
			else
			{
				$css_classes = 'button button-large ';
				$button_label = '';

				if ($order->meta_exists('_udw_delivery_id')) // Checks if the order has not been sended
				{ 
					$button_label = __('See delivery', 'udwdelivery');
				}
				else
				{
					$css_classes .= 'button-primary ';
					$button_label = __('Send now', 'udwdelivery');

					if (!$order->is_paid() | $order->get_status() == 'completed')
					{
						$css_classes .= 'disabled ';
					}
				}

				echo
				'<a 
					id="udw-button-pre-send"
					data-order-id="' . esc_attr($order->get_id()) . '"
					class="' . esc_attr($css_classes) . '"
				>';
				echo esc_html($button_label);
				echo '</a>';
			}
		}
	}
}
