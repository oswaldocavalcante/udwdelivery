<?php

use Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController;

require_once 'shipping/class-wbr-ud-api.php';
require_once 'shipping/class-wbr-ud-manifest-item.php';

if ( ! class_exists( 'Wbr_Wc_Integration' ) ) {

	class Wbr_Wc_Integration {

		private $wbr_ud_api;

		public function __construct() {

			$this->wbr_ud_api = new Wbr_Ud_Api();
			$this->define_woocommerce_hooks();
		}
		
		private function define_woocommerce_hooks() {
			add_action( 'add_meta_boxes', 							array( $this, 'add_meta_box' ) );
			add_filter( 'manage_edit-shop_order_columns', 			array( $this, 'add_order_list_column' ), 20 );
			add_action( 'manage_shop_order_posts_custom_column',	array( $this, 'add_order_list_column_buttons' ), 20, 2 );
			add_action( 'woocommerce_shipping_init', 				array( $this, 'create_shipping_method' ) );
			add_filter( 'woocommerce_shipping_methods', 			array( $this, 'add_shipping_method' ) );
			add_action( 'wp_ajax_woober_get_data',					array( $this, 'ajax_get_data'), 20 );
			add_action( 'wp_ajax_woober_create_delivery',			array( $this, 'ajax_create_delivery'), 20 );
			add_action( 'admin_footer', 							array( $this, 'add_modal_templates' ) );
		}

		public function create_shipping_method() {
			include_once('shipping/class-wbr-wc-shipping-method.php');
		}

		public function add_shipping_method( $methods ) {
			$methods['WOOBER_SHIPPING_METHOD'] = 'Wbr_Wc_Shipping_Method';
			return $methods;
		}
		
		function add_order_list_column( $columns ) {
			$reordered_columns = array();

			foreach ( $columns as $key => $column ) {
				$reordered_columns[ $key ] = $column;
				if ( $key == 'order_status' ) {
					// Inserting after "Status" column
					$reordered_columns[ 'wbr-shipping' ] = __('Envio (Uber)');
				}
			}
			return $reordered_columns;
		}

		function add_order_list_column_buttons( $column, $order_id ) {
			if ( $column === 'wbr-shipping' ) {

				$order = wc_get_order( $order_id );
				$shipping_address = $order->get_shipping_address_1() . ', ' . $order->get_shipping_city() . ', ' . $order->get_shipping_postcode();
				$quote = $this->wbr_ud_api->create_quote( $shipping_address );
				$quote_fee = $quote['fee'] / 100;

				if ( $order->get_shipping_total() == 0 ) {
					echo $order->get_shipping_method();
				} else {
					echo '<a 
						href="' . esc_html( '#' ) . '" 
						id="wbr-button-pre-send"
						data-order-id="' . $order_id . '"
						class="button action"
					>';
					if ( $order->get_meta('_wbr_delivery_id') )
						echo __( 'Ver envio', 'woober');
					else echo sprintf( 'Enviar (%s)', wc_price($quote_fee) );
					echo '</a>';
				}
			}
		}

		public function add_meta_box() {
			if ( ! empty( $_SERVER['REQUEST_URI'] ) ) {
				if (strstr( $_SERVER['REQUEST_URI'],'wc-orders') !== false && strstr( $_SERVER['REQUEST_URI'],'edit') !== false) {
					$screen = class_exists( '\Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController' ) && wc_get_container()->get( CustomOrdersTableController::class )->custom_orders_table_usage_is_enabled()
					? wc_get_page_screen_id( 'shop_order' )
					: 'shop_order';
					add_meta_box( 'wc-wbr-widget', __( 'Entrega (Uber Direct)', 'wbr-widget' ), array( $this, 'render_meta_box' ),  $screen , 'side', 'high' );
				} else {
					$array = explode( '/', esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) );
					if ( substr( end( $array ), 0, strlen( 'Woober-new.php' ) ) !== 'post-new.php') {
						add_meta_box( 'wc-wbr-widget', __( 'Entrega (Uber Direct)', 'wbr-widget' ), array( $this, 'render_meta_box' ), 'shop_order', 'side', 'high' );
					}
				}
			}
		}

		public function render_meta_box( $wc_post ) {

			global $post;
			$order_id = isset($post) ? $post->ID : $wc_post->get_id();
			$order = wc_get_order( $order_id );
			$order_address = $order->get_shipping_address_1();
			$wbr_shipping_status = __( 'undefined', 'woober');

			if ( $order->meta_exists('wbr_shipping_status') ) {
				$wbr_shipping_status = $order->get_meta('wbr_shipping_status');
				var_dump( $wbr_shipping_status );
			} else {
				$wbr_shipping_status = __( 'undelivered', 'woober' );
			}

			?>
				<div id="wbr-metabox-container">
					<div id="wbr-metabox-status">
						<p>
							<? echo sprintf(__('Status da entrega: %s', 'woober'), $wbr_shipping_status ); ?>
						</p>
					</div>
					<div id="wbr-metabox-quote">
						<p>
							<? 
								$wbr_shipping_quote = $this->wbr_ud_api->create_quote($order_address);
								$wbr_shipping_price = wc_price( $wbr_shipping_quote['fee'] / 100 );
								echo sprintf(__('Custo da entrega: %s', 'woober'), $wbr_shipping_price );
							?>
						</p>
					</div>
					<div id="wbr-metabox-action">
						<a class="button button-primary" id="wbr-button-pre-send" data-order-id="<?php echo $order_id ?>" href="#">Enviar</a>
					</div>
				</div>
			<?
		}

		public function ajax_get_data() {
			
			$order_id = $_POST['order_id'];
			$order = wc_get_order( $order_id );

			if( $order->meta_exists('_wbr_delivery_id') ) {
				$delivery_id = $order->get_meta('_wbr_delivery_id');
				$delivery = $this->wbr_ud_api->get_delivery($delivery_id);
				wp_send_json_success( $delivery );
			} else {
				wp_send_json_success( json_decode( $order ) );
			}
		}

		public function ajax_create_delivery() {

			$order_id = $_POST['order_id'];
			$order = wc_get_order( $order_id );

			$dropoff_name 			= $order->get_shipping_first_name() . ' ' . $order->get_shipping_last_name();
			$dropoff_address 		= $order->get_shipping_address_1() . ', ' . $order->get_shipping_postcode();
			$dropoff_notes 			= $order->get_shipping_address_2();
			$dropoff_phone_number 	= $order->get_billing_phone();

			$manifest_items 		= array();
			foreach ( $order->get_items() as $item ) {
				$manifest_items[] = new ManifestItem($item->get_name(), $item->get_quantity());
			}

			$ud_delivery = $this->wbr_ud_api->create_delivery( $order_id, $dropoff_name, $dropoff_address, $dropoff_notes, $dropoff_phone_number, $manifest_items );
			
			if( !$order->meta_exists('_wbr_delivery_id') ) {
				$order->add_meta_data( '_wbr_delivery_id', $ud_delivery->id );
				$order->save_meta_data();
			}

			wp_send_json_success( $ud_delivery );
		}

		public function add_modal_templates(): void {
			echo $this->render_modal_quote();
			echo $this->render_modal_delivery();
		}

		public function render_modal_quote(): string {
			ob_start();
			?>
			<script type="text/template" id="tmpl-wbr-modal-quote">
				<div id="wbr-modal-quote-container">
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
									<div id="wbr-quote-container">
										<# if ( data.shipping ) { #>

										<div id="wbr-shipping-preview-address" class="wbr-shipping-preview-block">
											<h2><?php esc_html_e( 'Informações do envio', 'woober' ); ?></h2>
											<div class="wbr-shipping-preview-input-wrapper">
												<label><?php echo  __( 'Enderço de entrega', 'woober' ) ?></label>
												<input type="text" class="short wbr-shipping-input-address-1" value="{{{ data.shipping.address_1 }}}" disabled/>
											</div>
											<div class="wbr-shipping-preview-input-wrapper">
												<label><?php echo  __( 'Complemento', 'woober' ) ?></label>
												<input type="text" class="wbr-shipping-input-address-2" value="{{{ data.shipping.address_2 }}}" disabled/>
											</div>
										</div>

										<div id="wbr-shipping-preview-buyer" class="wbr-shipping-preview-block">
											<h2><?php esc_html_e( 'Informações do destinatário', 'woober' ); ?></h2>
											<div class="wbr-shipping-preview-input-names-container">
												<div class="wbr-shipping-preview-input-wrapper name">
													<label><?php esc_html_e( 'Nome', 'woober' ); ?></label>
													<input type="text" class="wbr-shipping-input-first_name" value="{{{ data.shipping.first_name }}}" disabled/>
												</div>

												<div class="wbr-shipping-preview-input-wrapper name">
													<label><?php esc_html_e( 'Sobrenome', 'woober' ); ?></label>
													<input type="text" class="wbr-shipping-input-last_name" value="{{{ data.shipping.last_name }}}" disabled/>
												</div>
											</div>

											<div class="wbr-shipping-preview-input-wrapper">
												<label><?php esc_html_e( 'Telefone', 'woober' ); ?></label>
												<input type="text" class="wbr-shipping-input-phone" value="{{{ data.shipping.phone }}}" disabled/>
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

										<# } #>
									</div>
								</article>

								<footer>
									<div class="inner">
										<h3 style="float: left;">
										<?php echo 'Custo do envio: R$ ' . '{{data.shipping_total}}'; ?></h3>
										<a id="wbr-button-create-delivery" data-order-id="{{data.number}}" class="button button-primary button-large inner" aria-label="<?php esc_attr_e( 'Solicitar entregador', 'woober' ); ?>" href="<?php echo '#'; ?>" ><?php esc_html_e( 'Solicitar entregador', 'woober' ); ?></a>
									</div>
								</footer>

							</section>
						</div>
					</div>
					<div class="wc-backbone-modal-backdrop modal-close"></div>
				</div>
			</script>
			<?php

			$html = ob_get_clean();

			return $html;
		}

		public function render_modal_delivery(): string {
			ob_start();
			?>
			<script type="text/template" id="tmpl-wbr-modal-delivery">
				<div class="wc-backbone-modal">
					<div class="wc-backbone-modal-content">
						<section class="wc-backbone-modal-main" role="main">
							
							<header class="wc-backbone-modal-header">
								<mark class="order-status status-{{ data.status }}" style="float: right; margin-right: 54px"><span>{{ data.status }}</span></mark>
								<?php /* translators: %s: order ID */ ?>
								<# if ( data.external_id ) { #>
								<h1><?php echo esc_html( sprintf( __( 'Envio do pedido #%s', 'woober' ), '{{ data.external_id }}' ) ); ?></h1>
								<# } #>
								<button class="modal-close modal-close-link dashicons dashicons-no-alt">
									<span class="screen-reader-text"><?php esc_html_e( 'Close modal panel', 'woocommerce' ); ?></span>
								</button>
							</header>

							<article>
								<div id="wbr-delivery-container">

									<div id="wbr-delivery-dropoff" class="wbr-delivery-block">
										<h2><?php esc_html_e( 'Informações do destinatário', 'woober' ); ?></h2>

										<# if ( data.dropoff.name ) { #>
										<div class="wbr-delivery-dropoff-wrapper">
											<?php echo  __( 'Nome do destinatário', 'woober' ) ?>
											{{ data.dropoff.name }}
										</div>
										<# } #>

										<# if ( data.dropoff.phone_number ) { #>
										<div class="wbr-delivery-dropoff-wrapper">
											<?php echo  __( 'Telefone', 'woober' ) ?>
											{{ data.dropoff.phone_number }}
										</div>
										<# } #>

										<# if ( data.dropoff.address ) { #>
										<div class="wbr-delivery-dropoff-wrapper">
											<?php echo  __( 'Enderço de entrega', 'woober' ) ?>
											{{ data.dropoff.address }}
										</div>
										<# } #>

										<# if( data.dropoff.notes ) { #>
										<div class="wbr-delivery-dropoff-wrapper">
											<?php echo  __( 'Observações', 'woober' ) ?>
											{{ data.dropoff.notes }}
										</div>
										<# } #>
									</div>

									<# if( data.courier ) { #>
									<div id="wbr-delivery-courier" class="wbr-delivery-block">
										<h2><?php esc_html_e( 'Informações do motorista', 'woober' ); ?></h2>
										<div class="wbr-delivery-courier-container">

											<# if( data.courier.img_href ) { #>
											<div class="wbr-delivery-courier name">
												<img src="{{{data.courier.img_href}}}" />
											</div>
											<# } #>

											<# if( data.courier.name ) { #>
											<div class="wbr-delivery-courier name">
												<?php esc_html_e( 'Nome', 'woober' ); ?>
												{{ data.courier.name }}
											</div>
											<# } #>

											<# if( data.courier.vehicle_type ) { #>
											<div class="wbr-delivery-courier vehicle">
												<<?php esc_html_e( 'Veículo', 'woober' ); ?>
												{{ data.courier.vehicle_type }}
											</div>
											<# } #>

											<# if( data.courier.phone_number ) { #>
											<div class="wbr-delivery-courier phone">
												<<?php esc_html_e( 'Telefone', 'woober' ); ?>
												{{ data.courier.phone_number }}
											</div>
											<# } #>
										</div>
									</div>
									<# } #>

									<# if( data.tracking_url ) { #>
									<div id="wbr-delivery-tracking_url" class="wbr-delivery-block">
										<h2><?php esc_html_e( 'Informações do pacote', 'woober' ); ?></h2>
										<div class="wbr-delivery-tracking_url-wrapper">
											<label><?php esc_html_e( 'URL do acompanhamento ', 'woober' ); ?></label>
											<input type="text" class="wbr-shipping-order-id" value="{{{ data.tracking_url }}}" disabled />
										</div>
									</div>
									
									<# } #>

								</div>
							</article>

							<footer>
								<# if( data.fee ) { #>
								<div class="inner">
									<h3 style="float: left;">
										<?php 
										echo 'Custo do envio: R$ ' . '{{data.fee}}';										?>
									</h3>
									<!-- <a id="wbr-button-create-delivery" data-order-id="{{data.number}}" class="button button-primary button-large inner" aria-label="<?php esc_attr_e( 'Solicitar entregador', 'woober' ); ?>" href="<?php echo '#'; ?>" ><?php esc_html_e( 'Solicitar entregador', 'woober' ); ?></a> -->
								</div>
								<# } #>
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
