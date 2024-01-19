<?php

use Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController;
require_once 'shipping/class-wbr-ud-api.php';

if ( ! class_exists( 'Wbr_Wc_Integration' ) ) {

	class Wbr_Wc_Integration {

		private $wbr_ud_api;

		public function __construct() {

			$this->wbr_ud_api = new Wbr_Ud_Api();
			$this->define_woocommerce_hooks();
		}
		
		private function define_woocommerce_hooks() {
			add_action( 'add_meta_boxes', 							array( $this, 'add_meta_box' ) );
			add_filter( 'manage_edit-shop_order_columns', 			array( $this, 'add_column_in_order_list' ), 20 );
			add_action( 'manage_shop_order_posts_custom_column',	array( $this, 'order_column_fecth_data' ), 20, 2 );
			add_action( 'wp_ajax_woober_get_delivery_details',		array( $this, 'get_delivery_details'), 20 );
			add_action( 'admin_footer', 							array( $this, 'echo_delivery_preview_template' ) );
			add_action( 'woocommerce_shipping_init', 				array( $this, 'create_shipping_method' ) );
			add_filter( 'woocommerce_shipping_methods', 			array( $this, 'add_shipping_method' ) );
		}

		public function create_shipping_method() {
			include_once('shipping/class-wbr-wc-shipping-method.php');
		}

		public function add_shipping_method( $methods ) {
			$methods['WOOBER_SHIPPING_METHOD'] = 'Wbr_Wc_Shipping_Method';
			return $methods;
		}

		public function add_meta_box() {
			if ( ! empty( $_SERVER['REQUEST_URI'] ) ) {
				if (strstr( $_SERVER['REQUEST_URI'],'wc-orders') !== false && strstr( $_SERVER['REQUEST_URI'],'edit') !== false) {
					$screen = class_exists( '\Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController' ) && wc_get_container()->get( CustomOrdersTableController::class )->custom_orders_table_usage_is_enabled()
					? wc_get_page_screen_id( 'shop_order' )
					: 'shop_order';
					add_meta_box( 'wc-wbr-widget', __( 'Woober', 'wbr-widget' ), array( $this, 'render_meta_box' ),  $screen , 'side', 'high' );
				} else {
					$array = explode( '/', esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) );
					if ( substr( end( $array ), 0, strlen( 'Woober-new.php' ) ) !== 'post-new.php') {
						add_meta_box( 'wc-wbr-widget', __( 'Woober', 'wbr-widget' ), array( $this, 'render_meta_box' ), 'shop_order', 'side', 'high' );
					}
				}
			}
		}

		public function render_meta_box($wc_post) {
			// global $post;
			// $wc_order_id            = isset($post) ? $post->ID : $wc_post->get_id();
			// $lalamove_order_id      = lalamove_get_single_order_id( $wc_order_id );
			// $send_again_with_status = lalamove_get_send_again_with_status();

			// if ( is_null( $lalamove_order_id ) ) {
			// 	$lalamove_order_status = null;
			// 	$button_text           = 'Send with Uber Direct';
			// 	$button_background     = 'background: #F16622;';
			// 	$delivery_status_text  = lalamove_get_order_status_string( -1 );
			// } else {
			// 	$order_detail              = lalamove_get_order_detail( $lalamove_order_id );
			// 	$lalamove_order_display_id = $order_detail->order_display_id ?? null;
			// 	$lalamove_order_status     = $order_detail->order_status ?? null;
			// 	$lalamove_share_link       = $order_detail->share_link ?? '';

			// 	$delivery_status_text = lalamove_get_order_status_string( $lalamove_order_status );

			// 	if ( is_null( $lalamove_order_status ) ) {
			// 		$button_text       = 'Send with Uber Direct';
			// 		$button_background = 'background: #F16622;';
			// 	} elseif ( in_array( $lalamove_order_status, $send_again_with_status ) ) {
			// 		$button_text       = 'Send Again with Uber Direct';
			// 		$button_background = 'background: #F16622;';
			// 	} else {
			// 		$button_text       = 'View Records';
			// 		$button_background = 'background: #1228E9;';
			// 	}
			// }

			// echo '<div class="delivery-status-container" style="margin-top: 10px;display: flex;align-items: center;"">';
			// echo '<label style="font-size: 14px">Delivery Status:</label>';
			// echo '<div id="delivery-status" style="margin-left:8px; padding: 4px 8px;background: #F7F7F7;border: 1px solid #000000;border-radius: 10px;font-size: 14px">' . esc_html( $delivery_status_text ) . '</div>';
			// echo '</div>';

			// if ( ! is_null( $lalamove_order_status ) && ! in_array( $lalamove_order_status, $send_again_with_status ) ) {
			// 	if ( ! is_null( $lalamove_order_display_id ) ) {
			// 		echo '<div style="display: flex;align-items: center;height: 30px;margin-top: 5px;" >
			// 				<p style="font-size: 14px">Lalamove Order: ' . $lalamove_order_display_id . '</p>
			// 		  	  </div>';
			// 	}
			// 	echo '<div style="display: flex;align-items: center;height: 30px" >
			// 			<p style="font-size: 14px"> Track Your Order: </p>
			// 			<a rel="noopener" target="_blank" style="line-height: 1.5;font-size: 14px;margin-left: 5px;" href="' . esc_url( $lalamove_share_link ) . '"> Lalamove Sharelink </a>
			// 		  </div>';
			// }
			// echo '<div class="send-with-container" style="margin-top: 10px">';

			// if ( is_null( $lalamove_order_status ) || in_array( $lalamove_order_status, $send_again_with_status ) ) {
			// 	$cta_button_href = lalamove_get_current_admin_url() . '?page=Lalamove&sub-page=place-order&id=' . $wc_order_id;
			// } else {
			// 	$cta_button_href = Lalamove_App::$wc_llm_web_app_host . '/orders/' . $lalamove_order_id;
			// }

			// echo '<a href="' . esc_html( $cta_button_href ) . '"  target="_blank" class="button button-send-with" style="font-weight: bold;text-align: center;color: #FFFFFF;font-size: 14px;border-radius: 10px;display: block;line-height: 40px;height: 40px;' . esc_html( $button_background ) . ';" >
			// 	' . esc_html( $button_text ) . '</a>';
			// echo '</div>';
		}

		function add_column_in_order_list( $columns ) {
			$reordered_columns = array();

			foreach ( $columns as $key => $column ) {
				$reordered_columns[ $key ] = $column;
				if ( $key == 'order_status' ) {
					// Inserting after "Status" column
					$reordered_columns[ 'wbr-sending' ] = __('Envio (Uber)');
				}
			}
			return $reordered_columns;
		}

		function order_column_fecth_data( $column, $order_id ) {
			if ( $column === 'wbr-sending' ) {

				$wc_order = wc_get_order( $order_id );
				$shipping_address = $wc_order->get_shipping_address_1() . ', ' . $wc_order->get_shipping_city() . ', ' . $wc_order->get_shipping_postcode();
				$quote = $this->wbr_ud_api->create_quote( $shipping_address );
				$quote_fee = $quote['fee'] / 100;

				echo ('
					<a 
						href="' . esc_html( '#' ) . '" 
						id="wbr-send-button"
						data-order-id="' . $order_id . '"
						class="button action">' . 
						esc_html( __('Enviar (R$ ' . $quote_fee . ')') ) .
					'</a>
				');
			}
		}

		public function get_delivery_details() {
			
			$order_id = $_POST['order_id'];
			$order = wc_get_order( $order_id );
			wp_send_json_success( json_decode($order) );
		}

		public function echo_delivery_preview_template(): void {
			echo $this->get_delivery_preview_template();
		}

		public function get_delivery_preview_template(): string {
			ob_start();
			?>
			<script type="text/template" id="tmpl-wbr-modal-view-delivery">
				<div class="wc-backbone-modal">
					<div class="wc-backbone-modal-content">
						<section class="wc-backbone-modal-main" role="main">
							
							<header class="wc-backbone-modal-header">
								<mark class="order-status status-{{ data.status }}" style="float: right; margin-right: 54px"><span>{{ data.status }}</span></mark>
								<?php /* translators: %s: order ID */ ?>
								<h1><?php echo esc_html( sprintf( __( 'Envio do pedido #%s', 'woober' ), '{{ data.number }}' ) ); ?></h1>
								<button class="modal-close modal-close-link dashicons dashicons-no-alt">
									<span class="screen-reader-text"><?php esc_html_e( 'Close modal panel', 'woocommerce' ); ?></span>
								</button>
							</header>

							<article>
								<div id="wbr-shipping-preview">
									<div id="wbr-shipping-preview-address" class="wbr-shipping-preview-block">
										<h2><?php esc_html_e( 'Informações do envio', 'woober' ); ?></h2>
										<div class="wbr-shipping-preview-input-wrapper">
											<label><?php echo  __( 'Enderço de entrega', 'woober' ) ?></label>
											<input type="text" class="short wbr-shipping-input-address-1" value="{{{ data.shipping.address_1 }}}" />
										</div>
										<div class="wbr-shipping-preview-input-wrapper">
											<label><?php echo  __( 'Complemento', 'woober' ) ?></label>
											<input type="text" class="wbr-shipping-input-address-2" value="{{{ data.shipping.address_2 }}}" />
										</div>
									</div>
									<div id="wbr-shipping-preview-buyer" class="wbr-shipping-preview-block">
										<h2><?php esc_html_e( 'Informações do destinatário', 'woober' ); ?></h2>

										<div class="wbr-shipping-preview-input-names-container">
											<div class="wbr-shipping-preview-input-wrapper name">
												<label><?php esc_html_e( 'Nome', 'woober' ); ?></label>
												<input type="text" class="wbr-shipping-input-first_name" value="{{{ data.shipping.first_name }}}" />
											</div>

											<div class="wbr-shipping-preview-input-wrapper name">
												<label><?php esc_html_e( 'Sobrenome', 'woober' ); ?></label>
												<input type="text" class="wbr-shipping-input-last_name" value="{{{ data.shipping.last_name }}}" />
											</div>
										</div>

										<div class="wbr-shipping-preview-input-wrapper">
											<label><?php esc_html_e( 'Telefone', 'woober' ); ?></label>
											<input type="text" class="wbr-shipping-input-phone" value="{{{ data.shipping.phone }}}" />
										</div>

										<# if ( data.customer_note ) { #>
										<div class="wbr-shipping-preview-input-wrapper">
											<div class="wc-order-preview-note">
												<strong><?php esc_html_e( 'Observações', 'woober' ); ?></strong>
												{{ data.customer_note }}
											</div>
										</div>
										<# } #>
									</div>
									<div id="wbr-shipping-preview-package" class="wbr-shipping-preview-block">
										<h2><?php esc_html_e( 'Informações do pacote', 'woober' ); ?></h2>
										<div class="wbr-shipping-preview-input-wrapper">
											<label><?php esc_html_e( 'ID do Pedido', 'woober' ); ?></label>
											<input type="text" class="wbr-shipping-order-id" value="{{{ data.number }}}" disabled />
										</div>
									</div>
								</div>
							</article>
							<footer>
								<div class="inner">
									<h3 style="float: left;"><?php echo esc_html( sprintf( __( 'Custo do envio: R$ %s', 'woober' ), '{{ data.shipping_total }}' ) ); ?></h3>
									<a class="button button-primary button-large inner" aria-label="<?php esc_attr_e( 'Enviar', 'woober' ); ?>" href="<?php echo '#'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>" ><?php esc_html_e( 'Enviar', 'woober' ); ?></a>
								</div>
							</footer>
						</section>
					</div>
				</div>
				<div class="wc-backbone-modal-backdrop modal-close"></div>
			</script>
			<?php

			$html = ob_get_clean();

			return $html;
		}

	}
}
