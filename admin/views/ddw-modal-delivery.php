<script type="text/template" id="tmpl-ddw-modal-delivery">
    <div class="wc-backbone-modal">
        <div class="wc-backbone-modal-content">
            <section class="wc-backbone-modal-main" role="main">
                
                <header class="wc-backbone-modal-header">
                    <mark class="order-status status-{{ data.status }}" style="float: right; margin-right: 54px">
                        <span id="delivery-status"><?php esc_html_e('{{ data.status_translated }}', 'directdelivery') ?></span>
                    </mark>
                    <h1>
                    <?php esc_html_e('Order shipment ', 'directdelivery'); ?>
                    <# if ( data.external_id ) { #>
                        <?php echo esc_html('{{ data.external_id }}'); ?>
                    <# } #>
                    </h1>
                    <button class="modal-close modal-close-link dashicons dashicons-no-alt">
                        <span class="screen-reader-text"><?php esc_html_e('Close modal panel', 'directdelivery'); ?></span>
                    </button>
                </header>

                <article>
                    <div id="ddw-delivery-container">

                        <# if( data.tracking_url ) { #>
                        <div id="ddw-delivery-tracking_url-container" class="ddw-delivery-block">
                            <h2><?php esc_html_e('Package', 'directdelivery'); ?></h2>
                            <div class="ddw-delivery-data-wrapper">
                                <label><?php esc_html_e('Tracking URL', 'directdelivery'); ?></label>
                                <input id="ddw-delivery-tracking_url" type="text" value="{{{ data.tracking_url }}}" readonly />
                                <button id="ddw-delivery-btn_coppy-tracking_url" class="button"><?php esc_html_e('Copy', 'directdelivery'); ?></button>
                                <a href="{{{ data.tracking_url }}}" target="_blank" class="button button-primary dashicons-before dashicons-external"><?php esc_html_e('Open', 'directdelivery'); ?></a>
                            </div>
                        </div>
                        <# } #>

                        <# if( data.courier ) { #>
                        <h2 id="ddw-delivery-courier-title"><?php esc_html_e('Courier', 'directdelivery'); ?></h2> 
                        <div id="ddw-delivery-courier" class="ddw-delivery-block">

                            <# if( data.courier.img_href ) { #>
                            <div id="ddw-delivery-courier-photo">
                                <img src="{{{ data.courier.img_href }}}" />
                            </div>
                            <# } #>

                            <div class="ddw-delivery-block-container">
                                <# if( data.courier.name ) { #>
                                <div class="ddw-delivery-data-wrapper">
                                    <small><?php esc_html_e('Name', 'directdelivery'); ?></small>
                                    <p>{{ data.courier.name }}</p>
                                </div>
                                <# } #>

                                <# if( data.courier.vehicle_type ) { #>
                                <div class="ddw-delivery-data-wrapper">
                                    <small><?php esc_html_e('Vehicle', 'directdelivery'); ?></small>
                                    <p>{{ data.courier.vehicle_type }}</p>
                                </div>
                                <# } #>

                                <# if( data.courier.phone_number ) { #>
                                <div class="ddw-delivery-data-wrapper">
                                    <small><?php esc_html_e('Phone number', 'directdelivery'); ?></small>
                                    <p>{{ data.courier.phone_number }}</p>
                                </div>
                                <# } #>
                            </div>

                        </div>
                        <# } #>

                        <div id="ddw-delivery-dropoff" class="ddw-delivery-block">

                            <h2 class="ddw-delivery-block-title"><?php esc_html_e('Recipient', 'directdelivery'); ?></h2>
                            <div class="ddw-delivery-block-container">
                                <# if ( data.dropoff.name ) { #>
                                <div class="ddw-delivery-data-wrapper">
                                    <small><?php esc_html_e('Recipient Name', 'directdelivery') ?></small>
                                    <p>{{ data.dropoff.name }}</p>
                                </div>
                                <# } #>

                                <# if ( data.dropoff.phone_number ) { #>
                                <div class="ddw-delivery-data-wrapper">
                                    <small><?php esc_html_e('Phone number', 'directdelivery') ?></small>
                                    <p>{{ data.dropoff.phone_number }}</p>
                                </div>
                                <# } #>

                                <# if ( data.dropoff.address ) { #>
                                <div class="ddw-delivery-data-wrapper">
                                    <small><?php esc_html_e('Delivery Address', 'directdelivery') ?></small>
                                    <p>{{ data.dropoff.address }}</p>
                                </div>
                                <# } #>

                                <# if( data.dropoff.notes ) { #>
                                <div class="ddw-delivery-data-wrapper">
                                    <small><?php esc_html_e('Notes', 'directdelivery') ?></small>
                                    <p>{{ data.dropoff.notes }}</p>
                                </div>
                                <# } #>
                            </div>

                        </div>

                    </div>
                </article>

                <footer>

                    <a id="ddw-button-cancel-delivery" class="button <?php echo '{{ data.complete }}' ? 'disabled' : ''; ?>" data-order-id="{{ data.external_id }}" >
                        <?php esc_html_e('Cancel delivery', 'directdelivery'); ?>
                    </a>

                    <div class="inner">

                        <?php $currency_symbol = get_woocommerce_currency_symbol(get_woocommerce_currency()); ?>
                        
                        <# if( data.fee ) { #>
                        <h3  id="ddw-delivery-fee">
                            <?php /* translators: 1: currency symbol 2: user ID 3: fee */ ?>
                            <?php echo esc_html(sprintf(__('Shipping cost: %1$s %2$s', 'directdelivery'), $currency_symbol, '{{data.fee}}')); ?>
                        </h3>
                        <# } #>

                        <# if( data.tip ) { #>
                        <p id="ddw-delivery-tip">
                            <?php /* translators: 1: currency symbol 2: user ID 3: tip */ ?>
                            <?php echo esc_html(sprintf(__('Tip: %1$s %2$s', 'directdelivery'), $currency_symbol, '{{data.tip}}')); ?>
                        </p>
                        <# } #>

                    </div>
                </footer>

            </section>
        </div>
    </div>
    <div class="wc-backbone-modal-backdrop modal-close"></div>
</script>