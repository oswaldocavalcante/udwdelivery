<?php

use Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController;

require_once 'shipping/class-udw-ud-api.php';
require_once 'shipping/class-udw-ud-manifest-item.php';

class Udw_Wc_Integration extends WC_Integration {

	private $udw_ud_api;

	public function __construct() {
		$this->id = 'uberdirect';
		$this->method_title = __('Uber Direct');
		$this->method_description = __('Integrates Uber Direct delivery for Woocommerce.', 'uberdirect');

		$this->init_form_fields();
		$this->init_settings();

		$this->udw_ud_api = new Udw_Ud_Api();
		$this->define_woocommerce_hooks();
	}

	public function init_form_fields() {
		$this->form_fields = array(
			'wck-credentials-section' => array(
				'title'       => __( 'Access Credentials', 'uberdirect' ),
				'type'        => 'title',
				'description' => sprintf(__('See how to create your account and get your credentials in <a href="https://developer.uber.com/docs/deliveries/get-started" target="blank">%s</a>', 'uberdirect'), 'https://developer.uber.com/docs/deliveries/get-started'),
			),
			'udw-api-customer-id' => array(
				'title'       	=> __( 'Customer ID', 'uberdirect' ),
				'type'        	=> 'text',
				'description' 	=> __( 'Your Customer (Business ID) in Uber Direct settings.', 'uberdirect' ),
				'default'     	=> '',
			),
			'udw-api-client-id' => array(
				'title'       	=> __( 'Client ID', 'uberdirect' ),
				'type'        	=> 'text',
				'description' 	=> __( 'Your Client ID in Uber Direct settings.', 'uberdirect' ),
				'default'     	=> '',
			),
			'udw-api-client-secret' => array(
				'title'       	=> __( 'Client Secret', 'uberdirect' ),
				'type'        	=> 'text',
				'description' 	=> __( 'Your Client Secret in Uber Direct settings.', 'uberdirect' ),
				'default'     	=> '',
			),
		);
	}

	public function admin_options() {
		update_option( 'udw-api-customer-id', 	$this->get_option('udw-api-customer-id') );
		update_option( 'udw-api-client-id', 	$this->get_option('udw-api-client-id') );
		update_option( 'udw-api-client-secret', $this->get_option('udw-api-client-secret') );
		
		echo '<div id="udw-settings">';
		echo '<h2>' . esc_html( $this->get_method_title() ) . '</h2>';
        if( $this->udw_ud_api->get_access_token() ) {
			echo '<span class="udw-integration-connection dashicons-before dashicons-yes-alt">' . __('Connected', 'uberdirect') . '</span>';
        } else {
			wp_admin_notice( __( 'Uber Direct: Set your API access credentials.', 'uberdirect' ), array( 'type' => 'error' ) );
        }
		echo wp_kses_post( wpautop( $this->get_method_description() ) );
		echo '<div><input type="hidden" name="section" value="' . esc_attr( $this->id ) . '" /></div>';
		echo '<table class="form-table">' . $this->generate_settings_html( $this->get_form_fields(), false ) . '</table>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '</div>';
	}
	
	private function define_woocommerce_hooks() {
		add_action(	'woocommerce_update_options_integration_' . $this->id, array($this, 'process_admin_options'));
		add_action( 'woocommerce_shipping_init', 				array( $this, 'create_shipping_method' ) );
		add_filter( 'woocommerce_shipping_methods', 			array( $this, 'add_shipping_method' ) );
		add_filter( 'manage_edit-shop_order_columns', 			array( $this, 'add_order_list_column' ), 20 );
		add_action( 'manage_shop_order_posts_custom_column',	array( $this, 'add_order_list_column_buttons' ), 20, 2 );
		add_action( 'add_meta_boxes', 							array( $this, 'add_meta_box' ) );
		add_action( 'wp_ajax_uberdirect_get_delivery_data',		array( $this, 'ajax_get_delivery_data'), 20 );
		add_action( 'wp_ajax_uberdirect_create_delivery',		array( $this, 'ajax_create_delivery'), 20 );
		add_action( 'admin_footer', 							array( $this, 'add_modal_templates' ) );
	}

	public function create_shipping_method() {
		include_once('shipping/class-udw-wc-shipping-method.php');
	}

	public function add_shipping_method( $methods ) {
		$methods['UBERDIRECT_SHIPPING_METHOD'] = 'Udw_Wc_Shipping_Method';
		return $methods;
	}
	
	function add_order_list_column( $columns ) {
		if (!$this->udw_ud_api->get_access_token()) {
			wp_admin_notice(__('Uber Direct: Set your API access credentials.', 'uberdirect'), array('type' => 'error'));
		}

		$reordered_columns = array();

		foreach ( $columns as $key => $column ) {
			$reordered_columns[ $key ] = $column;
			if ( $key == 'order_status' ) {
				// Inserting after "Status" column
				$reordered_columns[ 'udw-shipping' ] = __('Envio (Uber Direct)');
			}
		}
		return $reordered_columns;
	}

	function add_order_list_column_buttons( $column, $order_id ) {
		if ( $column === 'udw-shipping' ) {

			$order = wc_get_order( $order_id );

			// Checks if the order isnt set to delivery
			if ( $order->get_shipping_total() == 0 ) {
				echo $order->get_shipping_method();
			} else {
				echo '<a 
					href="' . esc_html( '#' ) . '" 
					id="udw-button-pre-send"
					data-order-id="' . $order_id . '"
				';
				// Checks if the order has not been sended
				if( !$order->meta_exists('_udw_delivery_id') ) {
					echo 'class="button button-primary button-large" >';
					echo __( 'Enviar agora', 'uberdirect');
				} else {
					echo 'class="button button-large" >';
					echo __( 'Ver envio', 'uberdirect');
				}
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
				add_meta_box( 'wc-udw-widget', __( 'Envio (Uber Direct)', 'udw-widget' ), array( $this, 'render_meta_box' ),  $screen , 'side', 'high' );
			} else {
				$array = explode( '/', esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) );
				if ( substr( end( $array ), 0, strlen( 'Uberdirect-new.php' ) ) !== 'post-new.php') {
					add_meta_box( 'wc-udw-widget', __( 'Envio (Uber Direct)', 'udw-widget' ), array( $this, 'render_meta_box' ), 'shop_order', 'side', 'high' );
				}
			}
		}
	}

	public function render_meta_box( $wc_post ) {

		global $post;
		$order_id = isset($post) ? $post->ID : $wc_post->get_id();
		$order = wc_get_order( $order_id );
		$order_address = $order->get_shipping_address_1();
		$udw_shipping_status = __( 'undefined', 'uberdirect');

		if ( $order->meta_exists('udw_shipping_status') ) {
			$udw_shipping_status = $order->get_meta('udw_shipping_status');
		} else {
			$udw_shipping_status = __( 'undelivered', 'uberdirect' );
		}

		?>
		<div id="udw-metabox-container">
			<div id="udw-metabox-status">
				<p>
					<? echo sprintf(__('Status da entrega: %s', 'uberdirect'), $udw_shipping_status ); ?>
				</p>
			</div>
			<div id="udw-metabox-quote">
				<p>
					<? 
						$udw_shipping_quote = $this->udw_ud_api->create_quote($order_address);
						$udw_shipping_price = wc_price( $udw_shipping_quote['fee'] / 100 );
						echo sprintf(__('Custo da entrega: %s', 'uberdirect'), $udw_shipping_price );
					?>
				</p>
			</div>
			<div id="udw-metabox-action">
				<a class="button button-primary" id="udw-button-pre-send" data-order-id="<?php echo $order_id ?>" href="#">Enviar</a>
			</div>
		</div>
		<?
	}

	public function ajax_get_delivery_data() {
		
		$order_id = $_POST['order_id'];
		$order = wc_get_order( $order_id );

		if( $order->meta_exists('_udw_delivery_id') ) {
			$delivery_id = $order->get_meta('_udw_delivery_id');
			$delivery = $this->udw_ud_api->get_delivery($delivery_id);
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

		$ud_delivery = $this->udw_ud_api->create_delivery( $order_id, $dropoff_name, $dropoff_address, $dropoff_notes, $dropoff_phone_number, $manifest_items );

		$tip = (float) $order->get_shipping_total() - ($ud_delivery->fee / 100); // Fee is in cents
		if( $tip > 0 ) {
			// Updates the delivery repassing the difference between the quote and the real delivery to Uber for tax issues
			$ud_delivery = $this->udw_ud_api->update_delivery( $ud_delivery->id, $tip);
		}

		if( !$order->meta_exists('_udw_delivery_id') ) {
			$order->add_meta_data( '_udw_delivery_id', $ud_delivery->id );
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
		<script type="text/template" id="tmpl-udw-modal-quote">
			<div id="udw-modal-quote-container">
				<div class="wc-backbone-modal">
					<div class="wc-backbone-modal-content">
						<section class="wc-backbone-modal-main" role="main">
							
							<header class="wc-backbone-modal-header">
								<mark class="order-status status-{{ data.status }}" style="float: right; margin-right: 54px"><span>{{ data.status }}</span></mark>
								<?php /* translators: %s: order ID */ ?>
								<h1><?php echo esc_html( sprintf( __( 'Envio do pedido #%s', 'uberdirect' ), '{{ data.number }}' ) ); ?></h1>
								<button class="modal-close modal-close-link dashicons dashicons-no-alt">
									<span class="screen-reader-text"><?php esc_html_e( 'Close modal panel', 'woocommerce' ); ?></span>
								</button>
							</header>

							<article>
								<div id="udw-quote-container">
									<# if ( data.shipping ) { #>

									<div id="udw-shipping-preview-address" class="udw-shipping-preview-block">
										<h2><?php esc_html_e( 'Informações do envio', 'uberdirect' ); ?></h2>
										<div class="udw-shipping-preview-input-wrapper">
											<label><?php echo  __( 'Endereço de entrega', 'uberdirect' ) ?></label>
											<input type="text" class="short udw-shipping-input-address-1" value="{{{ data.shipping.address_1 }}}" disabled/>
										</div>
										<div class="udw-shipping-preview-input-wrapper">
											<label><?php echo  __( 'Complemento', 'uberdirect' ) ?></label>
											<input type="text" class="udw-shipping-input-address-2" value="{{{ data.shipping.address_2 }}}" disabled/>
										</div>
										<div class="udw-shipping-preview-input-wrapper">
											<label><?php echo  __( 'Frete pago', 'uberdirect' ) ?></label>
											<input type="text" class="udw-shipping-input-shipping_total" value="R$ {{{ data.shipping_total }}}" disabled/>
										</div>
									</div>

									<div id="udw-shipping-preview-buyer" class="udw-shipping-preview-block">
										<h2><?php esc_html_e( 'Informações do destinatário', 'uberdirect' ); ?></h2>
										<div class="udw-shipping-preview-input-names-container">
											<div class="udw-shipping-preview-input-wrapper name">
												<label><?php esc_html_e( 'Nome', 'uberdirect' ); ?></label>
												<input type="text" class="udw-shipping-input-first_name" value="{{{ data.shipping.first_name }}}" disabled/>
											</div>

											<div class="udw-shipping-preview-input-wrapper name">
												<label><?php esc_html_e( 'Sobrenome', 'uberdirect' ); ?></label>
												<input type="text" class="udw-shipping-input-last_name" value="{{{ data.shipping.last_name }}}" disabled/>
											</div>
										</div>

										<div class="udw-shipping-preview-input-wrapper">
											<label><?php esc_html_e( 'Telefone', 'uberdirect' ); ?></label>
											<input type="text" class="udw-shipping-input-phone" value="{{{ data.billing.phone }}}" disabled/>
										</div>

										<# if ( data.customer_note ) { #>
										<div class="udw-shipping-preview-input-wrapper">
											<div class="wc-order-preview-note">
												<strong><?php esc_html_e( 'Observações', 'uberdirect' ); ?></strong>
												{{ data.customer_note }}
											</div>
										</div>
										<# } #>
									</div>

									<div id="udw-shipping-preview-package" class="udw-shipping-preview-block">
										<h2><?php esc_html_e( 'Informações do pacote', 'uberdirect' ); ?></h2>
										<div class="udw-shipping-preview-input-wrapper">
											<label><?php esc_html_e( 'ID do Pedido', 'uberdirect' ); ?></label>
											<input type="text" class="udw-shipping-order-id" value="{{{ data.number }}}" disabled />
										</div>
									</div>

									<# } #>
								</div>
							</article>

							<footer>
								<div class="inner">
									<a id="udw-button-create-delivery" data-order-id="{{data.number}}" class="button button-primary button-large inner" aria-label="<?php esc_attr_e( 'Solicitar entregador', 'uberdirect' ); ?>" href="<?php echo '#'; ?>" ><?php esc_html_e( 'Solicitar entregador', 'uberdirect' ); ?></a>
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
		<script type="text/template" id="tmpl-udw-modal-delivery">
			<div class="wc-backbone-modal">
				<div class="wc-backbone-modal-content">
					<section class="wc-backbone-modal-main" role="main">
						
						<header class="wc-backbone-modal-header">
							<mark class="order-status status-{{ data.status }}" style="float: right; margin-right: 54px"><span>{{ data.status }}</span></mark>
							<?php /* translators: %s: order ID */ ?>
							<# if ( data.external_id ) { #>
							<h1><?php echo esc_html( sprintf( __( 'Envio do pedido #%s', 'uberdirect' ), '{{ data.external_id }}' ) ); ?></h1>
							<# } #>
							<button class="modal-close modal-close-link dashicons dashicons-no-alt">
								<span class="screen-reader-text"><?php esc_html_e( 'Close modal panel', 'woocommerce' ); ?></span>
							</button>
						</header>

						<article>
							<div id="udw-delivery-container">

								<div id="udw-delivery-dropoff" class="udw-delivery-block">
									<h2><?php esc_html_e( 'Informações do destinatário', 'uberdirect' ); ?></h2>

									<# if ( data.dropoff.name ) { #>
									<div class="udw-delivery-data-wrapper">
										<small><?php echo  __( 'Nome do destinatário', 'uberdirect' ) ?></small>
										<p>{{ data.dropoff.name }}</p>
									</div>
									<# } #>

									<# if ( data.dropoff.phone_number ) { #>
									<div class="udw-delivery-data-wrapper">
										<small><?php echo  __( 'Telefone', 'uberdirect' ) ?></small>
										<p>{{ data.dropoff.phone_number }}</p>
									</div>
									<# } #>

									<# if ( data.dropoff.address ) { #>
									<div class="udw-delivery-data-wrapper">
										<small><?php echo  __( 'Enderço de entrega', 'uberdirect' ) ?></small>
										<p>{{ data.dropoff.address }}</p>
									</div>
									<# } #>

									<# if( data.dropoff.notes ) { #>
									<div class="udw-delivery-data-wrapper">
										<small><?php echo  __( 'Observações', 'uberdirect' ) ?></small>
										<p>{{ data.dropoff.notes }}</p>
									</div>
									<# } #>
								</div>

								<# if( data.courier ) { #>
								<div id="udw-delivery-courier" class="udw-delivery-block">
									<h2><?php esc_html_e( 'Informações do motorista', 'uberdirect' ); ?></h2>
									<div class="udw-delivery-courier-container">

										<# if( data.courier.img_href ) { #>
										<div class="udw-delivery-data-wrapper">
											<img src="{{{data.courier.img_href}}}" />
										</div>
										<# } #>

										<# if( data.courier.name ) { #>
										<div class="udw-delivery-data-wrapper">
											<small><?php esc_html_e( 'Nome', 'uberdirect' ); ?></small>
											<p>{{ data.courier.name }}</p>
										</div>
										<# } #>

										<# if( data.courier.vehicle_type ) { #>
										<div class="udw-delivery-data-wrapper">
											<small><?php esc_html_e( 'Veículo', 'uberdirect' ); ?></small>
											<p>{{ data.courier.vehicle_type }}</p>
										</div>
										<# } #>

										<# if( data.courier.phone_number ) { #>
										<div class="udw-delivery-data-wrapper">
											<small><?php esc_html_e( 'Telefone', 'uberdirect' ); ?></small>
											<p>{{ data.courier.phone_number }}</p>
										</div>
										<# } #>
									</div>
								</div>
								<# } #>

								<# if( data.tracking_url ) { #>
								<div id="udw-delivery-tracking_url-container" class="udw-delivery-block">
									<h2><?php esc_html_e( 'Informações do pacote', 'uberdirect' ); ?></h2>
									<div class="udw-delivery-data-wrapper">
										<label><?php esc_html_e( 'URL do acompanhamento ', 'uberdirect' ); ?></label>
										<input id="udw-delivery-tracking_url" type="text" value="{{{ data.tracking_url }}}" readonly />
										<button id="udw-delivery-btn_coppy-tracking_url" class="button">Copiar</button>
									</div>
								</div>
								<# } #>

							</div>
						</article>

						<footer>
							<div class="inner">
								<# if( data.fee ) { #>
								<h3  id="udw-delivery-fee">
									<?php echo 'Custo do envio: R$ ' . '{{data.fee}}'; ?>
								</h3>
								<# } #>
								<# if( data.tip ) { #>
								<p id="udw-delivery-tip">
									<?php echo 'Gorjeta: R$ ' . '{{data.tip}}'; ?>
								</p>
								<# } #>
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
