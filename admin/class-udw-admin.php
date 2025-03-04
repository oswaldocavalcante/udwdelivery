<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://oswaldocavalcante.com
 * @since      1.0.0
 *
 * @package    Udw
 * @subpackage Udw/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Udw
 * @subpackage Udw/admin
 * @author     Oswaldo Cavalcante <contato@oswaldocavalcante.com>
 */

use Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController;

require_once UDW_ABSPATH . 'integrations/uberdirect/class-udw-ud-api.php';

class Udw_Admin
{
	private $udw_ud_api;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct()
	{
		$this->udw_ud_api = new Udw_Ud_Api();
	}

	public function register_settings()
	{
		register_setting('uberdirect_settings', 'udw-api-customer-id', 		array('type' => 'string', 'default' => ''));
		register_setting('uberdirect_settings', 'udw-api-client-id', 		array('type' => 'string', 'default' => ''));
		register_setting('uberdirect_settings', 'udw-api-client-secret', 	array('type' => 'string', 'default' => ''));
	}

	public function declare_wc_compatibility()
	{
		if (class_exists(\Automattic\WooCommerce\Utilities\FeaturesUtil::class))
		{
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', UDW_PLUGIN_FILE, true);
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
		register_rest_route(
			'uberdirect/v1',
			'/status',
			array(
				'methods'  => 'POST',
				'callback' => array($this, 'handle_webhook'),
				'permission_callback' => '__return_true',
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
			do_action('udw_change_order_status', $data['data']['external_id'], 'completed');
		}

		return new WP_REST_Response('Status Webhook received', 200);
	}

	public function change_order_status($order_id, $status)
	{
		$order = wc_get_order($order_id);

		if ($order && $status)
		{
			$order->set_status($status);
			$order->save();
		}
	}

	public function add_integration($integrations)
	{
		include_once  UDW_ABSPATH . 'integrations/woocommerce/class-udw-wc-integration.php';
		$integrations[] = 'Udw_Wc_Integration';

		return $integrations;
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles()
	{
		wp_enqueue_style('uberdirect', plugin_dir_url(__FILE__) . 'assets/css/udw-admin.css', array(), UDW_VERSION, 'all');
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts()
	{
		wp_enqueue_script('uberdirect', plugin_dir_url(__FILE__) . 'assets/js/udw-admin.js', array('jquery'), UDW_VERSION, false);

		wp_localize_script(
			'uberdirect',
			'udw_delivery_params',
			array(
				'url' => admin_url('admin-ajax.php'),
				'nonce' => wp_create_nonce('udw_nonce_delivery'),
				'translations' => array(
					'pending' 			=> __('Pending', 'uberdirect'),
					'pickup' 			=> __('Pickup', 'uberdirect'),
					'pickup_complete' 	=> __('Pickup complete', 'uberdirect'),
					'dropoff' 			=> __('Dropoff', 'uberdirect'),
					'delivered' 		=> __('Delivered', 'uberdirect'),
					'canceled' 			=> __('Canceled', 'uberdirect'),
					'returned' 			=> __('Returned', 'uberdirect')
				)
			)
		);
	}

	public function add_meta_box()
	{
		$screen = class_exists('\Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController') && wc_get_container()->get(CustomOrdersTableController::class)->custom_orders_table_usage_is_enabled()
			? wc_get_page_screen_id('shop-order')
			: 'shop_order';

		add_meta_box('wc-udw-widget', 'Uber Direct', array($this, 'render_meta_box'), $screen, 'side', 'high');
	}

	public function render_meta_box($post_or_order_object)
	{
		// Get order or legacy post.
		/**
		 * @var WC_Order $order
		 */
		$order = ($post_or_order_object instanceof WP_Post) ? wc_get_order($post_or_order_object->ID) : $post_or_order_object;
		// Note: $post_or_order_object should not be used directly below this point.

		$delivery_status = false;

		if ($order->meta_exists('_udw_delivery_id'))
		{
			$delivery_id = $order->get_meta('_udw_delivery_id');
			$delivery = $this->udw_ud_api->get_delivery($delivery_id);
			$delivery_status = $delivery->status;
		}
		?>

		<div id="udw-metabox-container">

			<div id="udw-metabox-status">
				<?php if ($order->meta_exists('_udw_delivery_id')) : ?>
					<h4><?php echo esc_html(sprintf(__('Status: %s', 'uberdirect'), $delivery_status)); ?></h4>
				<?php endif; ?>
			</div>

			<?php if ($order->meta_exists('_udw_delivery_id')) : ?>

				<h4><?php _e('Courier', 'uberdirect'); ?></h4>
				<span><?php echo $delivery->courier->name ?? ''; ?></span>

				<h4><?php _e('Vehicle', 'uberdirect'); ?></h4>
				<span><?php echo $delivery->courier->vehicle_type ?? ''; ?></span>

				<h4><?php _e('Phone number', 'uberdirect'); ?></h4>
				<span><?php echo $delivery->courier->phone_number ?? ''; ?></span>

				<h4><?php _e('Tracking URL', 'uberdirect'); ?></h4>
				<input id="udw-delivery-tracking_url" type="text" value="<?php echo esc_url($delivery->tracking_url ?? ''); ?>" readonly />
				<button id="udw-delivery-btn_coppy-tracking_url" class="button"><?php _e('Copy', 'uberdirect'); ?></button>
				<a href="<?php echo esc_url($delivery->tracking_url ?? '') ?>" target="_blank" class="button button-primary dashicons-before dashicons-external"><?php _e('Open', 'uberdirect'); ?></a>

			<?php else : ?>

				<?php
				$delivery_quote = $this->udw_ud_api->create_quote($order->get_shipping_address_1() . ', ' . $order->get_shipping_postcode());
				$delivery_variation = floatval(get_option('udw-extra_fee')); // Maximum variable of variation price for delivery
				$delivery_cost = ($delivery_quote['fee'] / 100) + $delivery_variation;
				?>
				<h4><?php echo esc_html(__('Shipping cost', 'uberdirect')); ?></h4>
				<span><?php echo $delivery_quote['fee'] ? wc_price($delivery_cost) : ''; ?></span>

				<h4><?php echo esc_html(__('Delivery time', 'uberdirect')); ?></h4>
				<span><?php echo $delivery_quote['duration'] ? $delivery_quote['duration'] . ' ' . __('minutes', 'uberdirect') : ''; ?></span>

				<div id="udw-metabox-action">
					<a href="#" class="button button-primary" id="udw-button-pre-send" data-order-id="<?php esc_attr_e($order->ID); ?>">
						<?php _e('Send now', 'uberdirect'); ?>
					</a>
				</div>

			<?php endif; ?>
		</div>
		<?php
	}

	public function ajax_get_delivery()
	{
		if (isset($_POST['security']) && check_ajax_referer('udw_nonce_delivery', 'security'))
		{
			$order_id = $_POST['order_id'];
			$order = wc_get_order($order_id);

			if ($order->meta_exists('_udw_delivery_id'))
			{
				$delivery_id = $order->get_meta('_udw_delivery_id');
				$delivery = $this->udw_ud_api->get_delivery($delivery_id);
				wp_send_json_success($delivery);
			}
			else
			{
				wp_send_json_success(json_decode($order));
			}
		}
	}

	public function ajax_create_delivery()
	{
		if (isset($_POST['security']) && check_ajax_referer('udw_nonce_delivery', 'security'))
		{
			$order_id = $_POST['order_id'];
			$order = wc_get_order($order_id);

			$dropoff_name 			= $order->get_shipping_first_name() . ' ' . $order->get_shipping_last_name();
			$dropoff_address 		= str_replace('<br/>', ', ', $order->get_formatted_shipping_address());
			$dropoff_notes 			= $order->get_shipping_address_2();
			$dropoff_phone_number 	= $order->get_billing_phone();
			$manifest_items 		= array();

			foreach ($order->get_items() as $item)
			{
				$manifest_items[] = new ManifestItem($item->get_name(), $item->get_quantity());
			}

			$delivery = $this->udw_ud_api->create_delivery($order_id, $dropoff_name, $dropoff_address, $dropoff_notes, $dropoff_phone_number, $manifest_items);

			$tip = (float) $order->get_shipping_total() - ($delivery->fee / 100); // Fee is in cents
			if ($tip > 0)
			{
				$delivery = $this->udw_ud_api->update_delivery($delivery->id, $tip); // Updates the delivery repassing the difference between the quote and the real delivery to Uber for tax issues
			}

			if (!$order->meta_exists('_udw_delivery_id'))
			{
				$order->add_meta_data('_udw_delivery_id', $delivery->id);
				$order->save_meta_data();
			}

			wp_send_json_success($delivery);
		}
	}

	public function ajax_cancel_delivery()
	{
		if (isset($_POST['security']) && check_ajax_referer('udw_nonce_delivery', 'security'))
		{
			$order_id = $_POST['order_id'];
			$order = wc_get_order($order_id);

			if ($order->meta_exists('_udw_delivery_id'))
			{
				$delivery_id = $order->get_meta('_udw_delivery_id');
				$delivery = $this->udw_ud_api->cancel_delivery($delivery_id);
				wp_send_json_success($delivery);
			}
			else
			{
				wp_send_json_success(json_decode($order));
			}
		}
	}

	public function add_modal_templates(): void
	{
		include_once 'views/udw-modal-quote.php';
		include_once 'views/udw-modal-delivery.php';
	}
}
