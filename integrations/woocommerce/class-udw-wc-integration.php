<?php

use Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController;

require_once UDW_ABSPATH . 'integrations/uberdirect/class-udw-ud-api.php';
require_once UDW_ABSPATH . 'integrations/uberdirect/class-udw-ud-manifest-item.php';

class Udw_Wc_Integration extends WC_Integration
{

	private $udw_ud_api;

	public function __construct()
	{
		$this->id = 'uberdirect';
		$this->method_title = __('Uber Direct');
		$this->method_description = __('Integrates Uber Direct delivery for Woocommerce.', 'uberdirect');

		$this->init_form_fields();
		$this->init_settings();

		$this->udw_ud_api = new Udw_Ud_Api();
		$this->define_woocommerce_hooks();
	}

	private function define_woocommerce_hooks()
	{
		add_action('woocommerce_update_options_integration_' . $this->id, array($this, 'process_admin_options'));
		add_action('woocommerce_shipping_init', 			array($this, 'create_shipping_method'));
		add_filter('woocommerce_shipping_methods', 			array($this, 'add_shipping_method'));
		add_filter('manage_edit-shop_order_columns', 		array($this, 'add_order_list_column'), 20);
		add_action('manage_shop_order_posts_custom_column',	array($this, 'add_order_list_column_buttons'), 20, 2);
	}

	public function init_form_fields()
	{
		$this->form_fields = array
		(
			'udw-api-section' => array
			(
				'title'       => __('Access Credentials', 'uberdirect'),
				'type'        => 'title',
				'description' => sprintf(__('See how to create your account and get your credentials in <a href="https://developer.uber.com/docs/deliveries/get-started" target="blank">%s</a>', 'uberdirect'), 'https://developer.uber.com/docs/deliveries/get-started'),
			),
			'udw-api-customer-id' => array
			(
				'title'       	=> __('Customer ID', 'uberdirect'),
				'type'        	=> 'text',
				'description' 	=> __('Your Customer (Business ID) in Uber Direct settings.', 'uberdirect'),
				'default'     	=> '',
			),
			'udw-api-client-id' => array
			(
				'title'       	=> __('Client ID', 'uberdirect'),
				'type'        	=> 'text',
				'description' 	=> __('Your Client ID in Uber Direct settings.', 'uberdirect'),
				'default'     	=> '',
			),
			'udw-api-client-secret' => array
			(
				'title'       	=> __('Client Secret', 'uberdirect'),
				'type'        	=> 'text',
				'description' 	=> __('Your Client Secret in Uber Direct settings.', 'uberdirect'),
				'default'     	=> '',
			),
			'udw-extra_fee-section' => array
			(
				'title'       => __('Extra fee', 'uberdirect'),
				'type'        => 'title',
				'description' => __('Set an extra fee to compensate the variation between the quote price and the actual delivery price. This difference, if positive, will be given as a tip for the driver.', 'uberdirect')
			),
			'udw-extra_fee-value' => array
			(
				'title' => __('Extra fee', 'uberdirect'),
				'type' => 'price',
				'description' => __('Enter a value with one monetary decimal point (,) without thousand separators and currency symbols.', 'uberdirect'),
				'default' => 0,
				'placeholder' => '0,00'
			)
		);
	}

	public function admin_options()
	{
		update_option('udw-api-customer-id', 	$this->get_option('udw-api-customer-id'));
		update_option('udw-api-client-id', 		$this->get_option('udw-api-client-id'));
		update_option('udw-api-client-secret', 	$this->get_option('udw-api-client-secret'));
		update_option('udw-extra_fee', 			$this->get_option('udw-extra_fee-value') ? $this->get_option('udw-extra_fee-value') : 0);

		echo '<div id="udw-settings">';
		echo '<h2>' . esc_html($this->get_method_title()) . '</h2>';
		if ($this->udw_ud_api->get_access_token()) {
			echo '<span class="udw-integration-connection dashicons-before dashicons-yes-alt">' . __('Connected', 'uberdirect') . '</span>';
		} else {
			wp_admin_notice(__('Uber Direct: Set your API access credentials.', 'uberdirect'), array('type' => 'error'));
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
		$methods['UBERDIRECT_SHIPPING_METHOD'] = 'Udw_Wc_Shipping_Method';

		return $methods;
	}

	function add_order_list_column($columns)
	{
		if (!$this->udw_ud_api->get_access_token()) {
			wp_admin_notice(__('Uber Direct: Set your API access credentials.', 'uberdirect'), array('type' => 'error'));
		}

		$reordered_columns = array();

		foreach ($columns as $key => $column) {
			$reordered_columns[$key] = $column;
			if ($key == 'order_status') {
				// Inserting after "Status" column
				$reordered_columns['udw-shipping'] = __('Uber Direct', 'uberdirect');
			}
		}

		return $reordered_columns;
	}

	function add_order_list_column_buttons($column, $order_id)
	{
		if ($column === 'udw-shipping')
		{
			$order = wc_get_order($order_id);

			if(!$order) return;

			// Checks if the order isnt set to delivery
			if ($order->get_shipping_total() == 0) {
				echo $order->get_shipping_method();
			}
			else
			{
				$css_classes = 'button button-large ';
				$button_label = '';

				if ($order->meta_exists('_udw_delivery_id')) { // Checks if the order has not been sended
					$button_label = __('See delivery', 'uberdirect');
				} 
				else
				{
					$css_classes .= 'button-primary ';
					$button_label = __('Send now', 'uberdirect');

					if ($order->get_status() != 'processing') {
						$css_classes .= 'disabled ';
					}
				}

				echo 
				'<a 
					id="udw-button-pre-send"
					data-order-id="' . esc_attr($order_id) . '"
					class="' . esc_attr($css_classes) . '"
				>';
				echo $button_label;
				echo '</a>';
			}
		}
	}
}
