<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController;

require_once UDWD_ABSPATH . 'integrations/uberdirect/class-udwd-ud-api.php';

/**
 * The admin-specific functionality of the plugin.
 *
 * @author     Oswaldo Cavalcante <contato@oswaldocavalcante.com>
 * @link       https://oswaldocavalcante.com
 * @since      1.0.0
 * 
 * @package    UDWDelivery
 * @subpackage UDWDelivery/admin
 */
class UDWD_Admin
{
	private $udwd_ud_api;

	public function __construct()
	{
		$this->udwd_ud_api = new UDWD_UD_API();
	}

	public function declare_wc_compatibility()
	{
		if (class_exists(\Automattic\WooCommerce\Utilities\FeaturesUtil::class))
		{
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', UDWD_PLUGIN_FILE, true);
		}
	}

	/**
	 * Register the admin-specific webhook endpoint.
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function register_webhook()
	{
		register_rest_route
		(
			'uberdirect/v1',
			'/status',
			array
			(
				'methods'  => 'POST',
				'callback' => array($this, 'handle_webhook'),
				'permission_callback' => function ()
				{
					return current_user_can('manage_options');
				}
			)
		);
	}

	/**
	 * Handle the admin-specific webhook request.
	 *
	 * @since    1.0.0
	 * @param    WP_REST_Request    $request    The request object.
	 * @return   WP_REST_Response
	 */
	public function handle_webhook(WP_REST_Request $request)
	{
		$data = $request->get_json_params();

		if (isset($data['status']) && $data['status'] == 'delivered')
		{
			do_action('udwd_change_order_status', $data['data']['external_id'], 'completed');
		}

		return new WP_REST_Response('Status Webhook received', 200);
	}

	/**
	 * Change the order status to the desired one.
	 *
	 * @since    1.0.0
	 * @param    int    $order_id    The order ID.
	 * @param    string $status      The desired status.
	 * @return   void
	 */
	public function change_order_status($order_id, $status)
	{
		$order = wc_get_order($order_id);

		if ($order && $status)
		{
			$order->set_status($status);
			$order->save();
		}
	}

	/**
	 * Add the Uber Direct integration to WooCommerce.
	 *
	 * @since    1.0.0
	 * @param    array    $integrations    The integrations array.
	 * @return   array
	 */
	public function add_integration($integrations)
	{
		include_once UDWD_ABSPATH . 'integrations/woocommerce/class-udwd-wc-integration.php';
		$integrations[] = 'UDWD_WC_Integration';

		return $integrations;
	}

	public function plugin_action_links($links)
	{
		$action_links = array
		(
			'settings' => '<a href="' . esc_url(get_admin_url(null, 'admin.php?page=wc-settings&tab=integration&section=udwdelivery')) . '">' . __('Settings', 'udwdelivery') . '</a>',
		);

		return array_merge($action_links, $links);
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles()
	{
		wp_enqueue_style('udwdelivery', plugin_dir_url(__FILE__) . 'assets/css/udwd-admin.css', array(), UDWD_VERSION);
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts()
	{
		wp_enqueue_script('udwdelivery', plugin_dir_url(__FILE__) . 'assets/js/udwd-admin.js', array('jquery'), UDWD_VERSION, false);

		wp_localize_script
		(
			'udwdelivery',
			'udwdelivery_params',
			array
			(
				'url' => admin_url('admin-ajax.php'),
				'nonce' => wp_create_nonce('udwd_nonce_delivery'),
				'translations' => array
				(
					'pending' 			=> __('Pending', 'udwdelivery'),
					'pickup' 			=> __('Pickup', 'udwdelivery'),
					'pickup_complete' 	=> __('Pickup complete', 'udwdelivery'),
					'dropoff' 			=> __('Dropoff', 'udwdelivery'),
					'delivered' 		=> __('Delivered', 'udwdelivery'),
					'canceled' 			=> __('Canceled', 'udwdelivery'),
					'returned' 			=> __('Returned', 'udwdelivery')
				)
			)
		);
	}

	/**
	 * Add the Uber Direct meta box to the order page.
	 */
	public function add_meta_box()
	{
		$screen = class_exists('\Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController') && wc_get_container()->get(CustomOrdersTableController::class)->custom_orders_table_usage_is_enabled()
			? wc_get_page_screen_id('shop-order')
			: 'shop_order';

		add_meta_box('wc-udwd-widget', 'Uber Direct', array($this, 'set_meta_box'), $screen, 'side', 'high');
	}

	/**
	 * Set the meta box content.
	 *
	 * @param WP_Post|WC_Order $post_or_order_object The post or order object.
	 */
	public function set_meta_box($post_or_order_object)
	{
		/**
		 * Get order or legacy post.
		 * @var WC_Order $order
		 */
		$order = ($post_or_order_object instanceof WP_Post) ? wc_get_order($post_or_order_object->ID) : $post_or_order_object; // Note: $post_or_order_object should not be used directly below this point.

		if ($order->meta_exists('_udw_delivery_id')) // If there is a delivery ID, we can get the delivery
		{
			$delivery_id = $order->get_meta('_udw_delivery_id');
			$delivery = $this->udwd_ud_api->get_delivery($delivery_id);

			if (!is_wp_error($delivery)) 	$this->render_meta_box_delivery($delivery, $order->ID);
			else 							$this->render_meta_box_error($delivery->get_error_message());
		}
		else // If there is no delivery ID, we can create a quote
		{
			$address = $this->get_formatted_address($order->get_address('shipping'));
			$quote = $this->udwd_ud_api->create_quote($address);

			if (!is_wp_error($quote)) 	$this->render_meta_box_quote($quote, $order->ID);
			else 						$this->render_meta_box_error($quote->get_error_message());
		}
	}

	/**
	 * Render the meta box content if the order has a delivery.
	 *
	 * @param WC_Order $order The order object.
	 * @param int $order_id The order ID.
	 */
	private function render_meta_box_delivery($delivery, $order_id)
	{
		?>
		<div id="udwd-metabox-container">

			<?php /* translators: %s: delivery status */ ?>
			<h4><?php echo esc_html(sprintf(__('Status: %s', 'udwdelivery'), $delivery->status)); ?></h4>

			<h4><?php esc_html_e('Courier', 'udwdelivery'); ?></h4>
			<span><?php echo esc_html($delivery->courier->name ?? ''); ?></span>

			<h4><?php esc_html_e('Vehicle', 'udwdelivery'); ?></h4>
			<span><?php echo esc_html($delivery->courier->vehicle_type ?? ''); ?></span>

			<h4><?php esc_html_e('Phone number', 'udwdelivery'); ?></h4>
			<span><?php echo esc_html($delivery->courier->phone_number ?? ''); ?></span>

			<h4><?php esc_html_e('Tracking URL', 'udwdelivery'); ?></h4>
			<input id="udwd-delivery-tracking_url" type="text" value="<?php echo esc_url($delivery->tracking_url ?? ''); ?>" readonly />
			<button id="udwd-delivery-btn_coppy-tracking_url" class="button"><?php esc_html_e('Copy', 'udwdelivery'); ?></button>
			<a href="<?php echo esc_url($delivery->tracking_url ?? '') ?>" target="_blank" class="button button-primary dashicons-before dashicons-external"><?php esc_html_e('Open', 'udwdelivery'); ?></a>

			<div id="udwd-metabox-action">
				<a href="#" class="button button-primary" id="udwd-button-cancel" data-order-id="<?php echo esc_attr($order_id); ?>">
					<?php esc_html_e('Cancel', 'udwdelivery'); ?>
				</a>
			</div>

		</div>
		<?php
	}

	/**
	 * Render the meta box content with a quote.
	 *
	 * @param WC_Order $order The order object.	
	 * @param int $order_id The order ID.
	 */
	private function render_meta_box_quote($quote, $order_id)
	{
		$extra_fee 	= floatval(get_option('udwd-extra_fee')); // Maximum variable of variation price for delivery
		$total_cost	= isset($quote['fee']) ? wc_price(($quote['fee'] / 100) + $extra_fee) : '';
		?>
		<div id="udwd-metabox-container">

			<h4><?php echo esc_html(__('Shipping cost', 'udwdelivery')); ?></h4>
			<span><?php echo isset($quote['fee']) ? wp_kses_post($total_cost) : ''; ?></span>

			<h4><?php echo esc_html(__('Delivery time', 'udwdelivery')); ?></h4>
			<span><?php echo isset($quote['duration']) ? esc_attr($quote['duration']) . ' ' . esc_attr__('minutes', 'udwdelivery') : ''; ?></span>

			<div id="udwd-metabox-action">
				<a href="#" class="button button-primary" id="udwd-button-pre-send" data-order-id="<?php echo esc_attr($order_id); ?>">
					<?php esc_html_e('Send now', 'udwdelivery'); ?>
				</a>
			</div>

		</div>
		<?php
	}

	/**
	 * Render the meta box content with an error.
	 *
	 * @param string $error The error message.
	 */
	private function render_meta_box_error($error)
	{
		?>
		<div id="udwd-metabox-container">

			<h4><?php echo esc_html(__('Error', 'udwdelivery')); ?></h4>
			<span><?php echo esc_html($error); ?></span>

			<div id="udwd-metabox-action">
				<a href="https://wordpress.org/support/plugin/udwdelivery/" class="button button-primary" target="_blank">
					<?php esc_html_e('Get support', 'udwdelivery'); ?>
				</a>
			</div>

		</div>
		<?php
	}

	/**
	 * Get the formatted address.
	 *
	 * @param array $raw_address The raw address.
	 * @return string The formatted address.
	 */
	private function get_formatted_address($raw_address)
	{
		unset($raw_address['first_name'], $raw_address['last_name'], $raw_address['address_2'], $raw_address['company'], $raw_address['phone']); // Remove unnecessary fields
		
		return WC()->countries->get_formatted_address($raw_address, ', ');
	}

	/**
	 * Get the delivery.
	 */
	public function ajax_get_delivery()
	{
		if (isset($_POST['order_id']) && isset($_POST['security']) && check_ajax_referer('udwd_nonce_delivery', 'security'))
		{
			$order_id = absint(wp_unslash($_POST['order_id']));
			$order = wc_get_order($order_id);

			if ($order->meta_exists('_udw_delivery_id'))
			{
				$delivery_id = $order->get_meta('_udw_delivery_id');
				$delivery = $this->udwd_ud_api->get_delivery($delivery_id);

				if (is_wp_error($delivery)) wp_send_json_error($delivery);
				else wp_send_json_success($delivery);
			}
			else wp_send_json_success(json_decode($order));
		}
	}

	/**
	 * Create a delivery.
	 */
	public function ajax_create_delivery()
	{
		if (isset($_POST['order_id']) && isset($_POST['security']) && check_ajax_referer('udwd_nonce_delivery', 'security'))
		{
			$order_id = absint(wp_unslash($_POST['order_id']));
			$order = wc_get_order($order_id);

			$country_code = $order->get_billing_country();
			$calling_code = WC()->countries->get_country_calling_code($country_code);
			$dropoff_phone_number = $calling_code . $order->get_billing_phone();

			$dropoff_address = $this->get_formatted_address($order->get_address('shipping'));
			$dropoff_name 	= $order->get_shipping_first_name() . ' ' . $order->get_shipping_last_name();
			$dropoff_notes 	= $order->get_shipping_address_2() . '; ' . $order->get_customer_note();
			$manifest_items = array();

			foreach ($order->get_items() as $item)
			{
				$manifest_items[] = new ManifestItem($item->get_name(), $item->get_quantity()); // ManifestItem needs to be named like this to be recognized by Uber Direct API
			}

			$delivery = $this->udwd_ud_api->create_delivery($order_id, $dropoff_name, $dropoff_address, $dropoff_notes, $dropoff_phone_number, $manifest_items);

			if (is_wp_error($delivery)) wp_send_json_error($delivery);
			else
			{
				$tip = (float) $order->get_shipping_total() - ($delivery->fee / 100); // Fee is in cents
				if ($tip > 0) $delivery = $this->udwd_ud_api->update_delivery($delivery->id, $tip); // Updates the delivery repassing the difference between the quote and the real delivery to Uber for tax issues

				if (!$order->meta_exists('_udw_delivery_id'))
				{
					$order->add_meta_data('_udw_delivery_id', $delivery->id);
					$order->save_meta_data();
				}

				wp_send_json_success($delivery);
			}
		}
	}

	/**
	 * Cancel a delivery.
	 */
	public function ajax_cancel_delivery()
	{
		if (isset($_POST['order_id']) && isset($_POST['security']) && check_ajax_referer('udwd_nonce_delivery', 'security'))
		{
			$order_id = absint(wp_unslash($_POST['order_id']));
			$order = wc_get_order($order_id);

			if ($order->meta_exists('_udw_delivery_id'))
			{
				$delivery_id = $order->get_meta('_udw_delivery_id');
				$delivery = $this->udwd_ud_api->cancel_delivery($delivery_id);

				if (is_wp_error($delivery)) wp_send_json_error($delivery);
				else wp_send_json_success($delivery);
			}
			else wp_send_json_success(json_decode($order));
		}
	}

	/**
	 * Add the modal templates for AJAX requests.
	 */
	public function add_modal_templates(): void
	{
		include_once 'views/udwd-modal-quote.php';
		include_once 'views/udwd-modal-delivery.php';
		include_once 'views/udwd-modal-error.php';
	}
}
