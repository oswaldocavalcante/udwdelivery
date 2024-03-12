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

require_once UDW_ABSPATH . 'integrations/uberdirect/class-udw-ud-api.php';

class Udw_Admin
{

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	private $udw_ud_api;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct($plugin_name, $version)
	{
		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->udw_ud_api = new Udw_Ud_Api();
	}

	public function is_woocommerce_active()
	{
		$active_plugins = (array) get_option('active_plugins', array());
		if (is_multisite()) {
			$active_plugins = array_merge($active_plugins, get_site_option('active_sitewide_plugins', array()));
		}
		if (in_array('woocommerce/woocommerce.php', $active_plugins) || array_key_exists('woocommerce/woocommerce.php', $active_plugins)) {
			return true;
		} else {
			return false;
		}
	}

	public function add_integration($integrations)
	{
		if ($this->is_woocommerce_active()) {
			include_once  UDW_ABSPATH . 'integrations/woocommerce/class-udw-wc-integration.php';
			$integrations[] = 'Udw_Wc_Integration';

			return $integrations;
		} else {
			wp_admin_notice(
				esc_html(__('Please install and activate WooCommerce to use Uber Direct!', 'uberdirect')),
				array('type' => 'error')
			);
		}

		return null;
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles()
	{
		wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'assets/css/udw-admin.css', array(), $this->version, 'all');
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts()
	{
		wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'assets/js/udw-admin.js', array('jquery'), $this->version, false);

		wp_localize_script(
			$this->plugin_name,
			'udw_delivery_params',
			array(
				'url' => admin_url('admin-ajax.php'),
				'nonce' => wp_create_nonce('udw_nonce_delivery'),
			)
		);
	}

	public function register_settings()
	{
		register_setting('uberdirect_settings', 'udw-api-customer-id', 		array('type' => 'string', 'default' => ''));
		register_setting('uberdirect_settings', 'udw-api-client-id', 		array('type' => 'string', 'default' => ''));
		register_setting('uberdirect_settings', 'udw-api-client-secret', 	array('type' => 'string', 'default' => ''));
	}

	public function add_meta_box()
	{
		if (!empty($_SERVER['REQUEST_URI'])) {
			if (strstr($_SERVER['REQUEST_URI'], 'wc-orders') !== false && strstr($_SERVER['REQUEST_URI'], 'edit') !== false) {
				$screen = class_exists('\Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController') && wc_get_container()->get(CustomOrdersTableController::class)->custom_orders_table_usage_is_enabled()
					? wc_get_page_screen_id('shop_order')
					: 'shop_order';
				add_meta_box('wc-udw-widget', __('Envio (Uber Direct)', 'udw-widget'), array($this, 'render_meta_box'),  $screen, 'side', 'high');
			} else {
				$array = explode('/', esc_url_raw(wp_unslash($_SERVER['REQUEST_URI'])));
				if (substr(end($array), 0, strlen('Uberdirect-new.php')) !== 'post-new.php') {
					add_meta_box('wc-udw-widget', __('Envio (Uber Direct)', 'udw-widget'), array($this, 'render_meta_box'), 'shop_order', 'side', 'high');
				}
			}
		}
	}

	public function render_meta_box($wc_post)
	{
		global $post;
		$order_id = isset($post) ? $post->ID : $wc_post->get_id();
		$order = wc_get_order($order_id);
		$order_address = $order->get_shipping_address_1();
		$udw_shipping_status = __('undefined', 'uberdirect');

		if ($order->meta_exists('udw_shipping_status')) {
			$udw_shipping_status = $order->get_meta('udw_shipping_status');
		} else {
			$udw_shipping_status = __('undelivered', 'uberdirect');
		}

		?>
		<div id="udw-metabox-container">
			<div id="udw-metabox-status">
				<p>
					<? echo sprintf(__('Status da entrega: %s', 'uberdirect'), $udw_shipping_status); ?>
				</p>
			</div>
			<div id="udw-metabox-quote">
				<p>
					<?php
					$udw_shipping_quote = $this->udw_ud_api->create_quote($order_address);
					$udw_shipping_price = wc_price($udw_shipping_quote['fee'] / 100);
					echo sprintf(__('Custo da entrega: %s', 'uberdirect'), $udw_shipping_price);
					?>
				</p>
			</div>
			<div id="udw-metabox-action">
				<a class="button button-primary" id="udw-button-pre-send" data-order-id="<?php echo $order_id ?>" href="#">Enviar</a>
			</div>
		</div>
		<?php
	}

	public function ajax_get_delivery()
	{
		if(isset($_POST['security']) && check_ajax_referer('udw_nonce_delivery', 'security')) {
			$order_id = $_POST['order_id'];
			$order = wc_get_order($order_id);
	
			if ($order->meta_exists('_udw_delivery_id')) {
				$delivery_id = $order->get_meta('_udw_delivery_id');
				$delivery = $this->udw_ud_api->get_delivery($delivery_id);
				wp_send_json_success($delivery);
			} else {
				wp_send_json_success(json_decode($order));
			}
		}
	}

	public function ajax_create_delivery()
	{
		if (isset($_POST['security']) && check_ajax_referer('udw_nonce_delivery', 'security')) {
			$order_id = $_POST['order_id'];
			$order = wc_get_order($order_id);

			$dropoff_name 			= $order->get_shipping_first_name() . ' ' . $order->get_shipping_last_name();
			$dropoff_address 		= $order->get_shipping_address_1() . ', ' . $order->get_shipping_postcode();
			$dropoff_notes 			= $order->get_shipping_address_2();
			$dropoff_phone_number 	= $order->get_billing_phone();

			$manifest_items 		= array();
			foreach ($order->get_items() as $item) {
				$manifest_items[] = new ManifestItem($item->get_name(), $item->get_quantity());
			}

			$ud_delivery = $this->udw_ud_api->create_delivery($order_id, $dropoff_name, $dropoff_address, $dropoff_notes, $dropoff_phone_number, $manifest_items);

			$tip = (float) $order->get_shipping_total() - ($ud_delivery->fee / 100); // Fee is in cents
			if ($tip > 0) {
				// Updates the delivery repassing the difference between the quote and the real delivery to Uber for tax issues
				$ud_delivery = $this->udw_ud_api->update_delivery($ud_delivery->id, $tip);
			}

			if (!$order->meta_exists('_udw_delivery_id')) {
				$order->add_meta_data('_udw_delivery_id', $ud_delivery->id);
				$order->save_meta_data();
			}

			wp_send_json_success($ud_delivery);
		}
	}

	public function add_modal_templates(): void
	{
		include_once 'views/udw-modal-quote.php';
		include_once 'views/udw-modal-delivery.php';
	}
}
