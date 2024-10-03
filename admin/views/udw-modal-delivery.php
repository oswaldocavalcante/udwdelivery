<script type="text/template" id="tmpl-udw-modal-delivery">
    <div class="wc-backbone-modal">
        <div class="wc-backbone-modal-content">
            <section class="wc-backbone-modal-main" role="main">
                
                <header class="wc-backbone-modal-header">
                    <mark class="order-status status-{{ data.status }}" style="float: right; margin-right: 54px">
                        <span>{{ data.status }}</span>
                    </mark>
                    <?php /* translators: %s: order ID */ ?>
                    <# if ( data.external_id ) { #>
                    <h1><?php esc_html_e(sprintf(__('Envio do pedido #%s', 'uberdirect'), '{{ data.external_id }}')); ?></h1>
                    <# } #>
                    <button class="modal-close modal-close-link dashicons dashicons-no-alt">
                        <span class="screen-reader-text"><?php esc_html_e('Close modal panel', 'woocommerce'); ?></span>
                    </button>
                </header>

                <form method="post">
                    <div id="udw-delivery-container">

                        <div id="udw-delivery-dropoff" class="udw-delivery-block">
                            <h2><?php esc_html_e('Recipient Information', 'uberdirect'); ?></h2>

                            <# if ( data.dropoff.name ) { #>
                            <div class="udw-delivery-data-wrapper">
                                <small><?php _e('Recipient Name', 'uberdirect') ?></small>
                                <p>{{ data.dropoff.name }}</p>
                            </div>
                            <# } #>

                            <# if ( data.dropoff.phone_number ) { #>
                            <div class="udw-delivery-data-wrapper">
                                <small><?php _e('Phone number', 'uberdirect') ?></small>
                                <p>{{ data.dropoff.phone_number }}</p>
                            </div>
                            <# } #>

                            <# if ( data.dropoff.address ) { #>
                            <div class="udw-delivery-data-wrapper">
                                <small><?php _e('Delivery Address', 'uberdirect') ?></small>
                                <p>{{ data.dropoff.address }}</p>
                            </div>
                            <# } #>

                            <# if( data.dropoff.notes ) { #>
                            <div class="udw-delivery-data-wrapper">
                                <small><?php _e('Notes', 'uberdirect') ?></small>
                                <p>{{ data.dropoff.notes }}</p>
                            </div>
                            <# } #>
                        </div>

                        <# if( data.courier ) { #>
                        <div id="udw-delivery-courier" class="udw-delivery-block">
                            <h2><?php esc_html_e('Driver information', 'uberdirect'); ?></h2>
                            <div class="udw-delivery-courier-container">

                                <# if( data.courier.img_href ) { #>
                                <div class="udw-delivery-data-wrapper">
                                    <img src="{{{data.courier.img_href}}}" />
                                </div>
                                <# } #>

                                <# if( data.courier.name ) { #>
                                <div class="udw-delivery-data-wrapper">
                                    <small><?php esc_html_e('Name', 'uberdirect'); ?></small>
                                    <p>{{ data.courier.name }}</p>
                                </div>
                                <# } #>

                                <# if( data.courier.vehicle_type ) { #>
                                <div class="udw-delivery-data-wrapper">
                                    <small><?php esc_html_e('Vehicle', 'uberdirect'); ?></small>
                                    <p>{{ data.courier.vehicle_type }}</p>
                                </div>
                                <# } #>

                                <# if( data.courier.phone_number ) { #>
                                <div class="udw-delivery-data-wrapper">
                                    <small><?php esc_html_e('Phone number', 'uberdirect'); ?></small>
                                    <p>{{ data.courier.phone_number }}</p>
                                </div>
                                <# } #>
                            </div>
                        </div>
                        <# } #>

                        <# if( data.tracking_url ) { #>
                        <div id="udw-delivery-tracking_url-container" class="udw-delivery-block">
                            <h2><?php esc_html_e('Package information', 'uberdirect'); ?></h2>
                            <div class="udw-delivery-data-wrapper">
                                <label><?php esc_html_e('Tracking URL ', 'uberdirect'); ?></label>
                                <input id="udw-delivery-tracking_url" type="text" value="{{{ data.tracking_url }}}" readonly />
                                <button id="udw-delivery-btn_coppy-tracking_url" class="button">Copiar</button>
                            </div>
                        </div>
                        <# } #>

                    </div>

                </form>

                <footer>
                    <div class="inner">
                        <?php
                            $currency           = get_woocommerce_currency();
                            $currency_symbol    = get_woocommerce_currency_symbol($currency);
                        ?>
                        <# if( data.fee ) { #>
                        <h3  id="udw-delivery-fee">
                            <?php _e(sprintf('Shipping cost: %s %s', $currency_symbol, '{{data.fee}}')); ?>
                        </h3>
                        <# } #>
                        <# if( data.tip ) { #>
                        <p id="udw-delivery-tip">
                            <?php _e(sprintf('Tip: %s %s', $currency_symbol, '{{data.tip}}')); ?>
                        </p>
                        <# } #>
                    </div>
                </footer>

            </section>
        </div>
    </div>
    <div class="wc-backbone-modal-backdrop modal-close"></div>
</script>