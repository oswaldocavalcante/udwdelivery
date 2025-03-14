<script type="text/template" id="tmpl-udw-modal-delivery">
    <div class="wc-backbone-modal">
        <div class="wc-backbone-modal-content">
            <section class="wc-backbone-modal-main" role="main">
                
                <header class="wc-backbone-modal-header">
                    <mark class="order-status status-{{ data.status }}" style="float: right; margin-right: 54px">
                        <span id="delivery-status"><?php esc_html_e('{{ data.status_translated }}', 'udwdelivery') ?></span>
                    </mark>
                    <h1>
                    <?php esc_html_e('Order shipment ', 'udwdelivery'); ?>
                    <# if ( data.external_id ) { #>
                        <?php echo esc_html('{{ data.external_id }}'); ?>
                    <# } #>
                    </h1>
                    <button class="modal-close modal-close-link dashicons dashicons-no-alt">
                        <span class="screen-reader-text"><?php esc_html_e('Close modal panel', 'udwdelivery'); ?></span>
                    </button>
                </header>

                <article>
                    <div id="udw-delivery-container">

                        <# if( data.tracking_url ) { #>
                        <div id="udw-delivery-tracking_url-container" class="udw-delivery-block">
                            <h2><?php esc_html_e('Package', 'udwdelivery'); ?></h2>
                            <div class="udw-delivery-data-wrapper">
                                <label><?php esc_html_e('Tracking URL', 'udwdelivery'); ?></label>
                                <input id="udw-delivery-tracking_url" type="text" value="{{{ data.tracking_url }}}" readonly />
                                <button id="udw-delivery-btn_coppy-tracking_url" class="button"><?php esc_html_e('Copy', 'udwdelivery'); ?></button>
                                <a href="{{{ data.tracking_url }}}" target="_blank" class="button button-primary dashicons-before dashicons-external"><?php esc_html_e('Open', 'udwdelivery'); ?></a>
                            </div>
                        </div>
                        <# } #>

                        <# if( data.courier ) { #>
                        <h2 id="udw-delivery-courier-title"><?php esc_html_e('Courier', 'udwdelivery'); ?></h2> 
                        <div id="udw-delivery-courier" class="udw-delivery-block">

                            <# if( data.courier.img_href ) { #>
                            <div id="udw-delivery-courier-photo">
                                <img src="{{{ data.courier.img_href }}}" />
                            </div>
                            <# } #>

                            <div class="udw-delivery-block-container">
                                <# if( data.courier.name ) { #>
                                <div class="udw-delivery-data-wrapper">
                                    <small><?php esc_html_e('Name', 'udwdelivery'); ?></small>
                                    <p>{{ data.courier.name }}</p>
                                </div>
                                <# } #>

                                <# if( data.courier.vehicle_type ) { #>
                                <div class="udw-delivery-data-wrapper">
                                    <small><?php esc_html_e('Vehicle', 'udwdelivery'); ?></small>
                                    <p>{{ data.courier.vehicle_type }}</p>
                                </div>
                                <# } #>

                                <# if( data.courier.phone_number ) { #>
                                <div class="udw-delivery-data-wrapper">
                                    <small><?php esc_html_e('Phone number', 'udwdelivery'); ?></small>
                                    <p>{{ data.courier.phone_number }}</p>
                                </div>
                                <# } #>
                            </div>

                        </div>
                        <# } #>

                        <div id="udw-delivery-dropoff" class="udw-delivery-block">

                            <h2 class="udw-delivery-block-title"><?php esc_html_e('Recipient', 'udwdelivery'); ?></h2>
                            <div class="udw-delivery-block-container">
                                <# if ( data.dropoff.name ) { #>
                                <div class="udw-delivery-data-wrapper">
                                    <small><?php esc_html_e('Recipient Name', 'udwdelivery') ?></small>
                                    <p>{{ data.dropoff.name }}</p>
                                </div>
                                <# } #>

                                <# if ( data.dropoff.phone_number ) { #>
                                <div class="udw-delivery-data-wrapper">
                                    <small><?php esc_html_e('Phone number', 'udwdelivery') ?></small>
                                    <p>{{ data.dropoff.phone_number }}</p>
                                </div>
                                <# } #>

                                <# if ( data.dropoff.address ) { #>
                                <div class="udw-delivery-data-wrapper">
                                    <small><?php esc_html_e('Delivery Address', 'udwdelivery') ?></small>
                                    <p>{{ data.dropoff.address }}</p>
                                </div>
                                <# } #>

                                <# if( data.dropoff.notes ) { #>
                                <div class="udw-delivery-data-wrapper">
                                    <small><?php esc_html_e('Notes', 'udwdelivery') ?></small>
                                    <p>{{ data.dropoff.notes }}</p>
                                </div>
                                <# } #>
                            </div>

                        </div>

                    </div>
                </article>

                <footer>

                    <a id="udw-button-cancel-delivery" class="button <?php echo '{{ data.complete }}' ? 'disabled' : ''; ?>" data-order-id="{{ data.external_id }}" >
                        <?php esc_html_e('Cancel delivery', 'udwdelivery'); ?>
                    </a>

                    <div class="inner">

                        <?php $currency_symbol = get_woocommerce_currency_symbol(get_woocommerce_currency()); ?>
                        
                        <# if( data.fee ) { #>
                        <h3  id="udw-delivery-fee">
                            <?php /* translators: 1: currency symbol 2: user ID 3: fee */ ?>
                            <?php echo esc_html(sprintf(__('Shipping cost: %1$s %2$s', 'udwdelivery'), $currency_symbol, '{{data.fee}}')); ?>
                        </h3>
                        <# } #>

                        <# if( data.tip ) { #>
                        <p id="udw-delivery-tip">
                            <?php /* translators: 1: currency symbol 2: user ID 3: tip */ ?>
                            <?php echo esc_html(sprintf(__('Tip: %1$s %2$s', 'udwdelivery'), $currency_symbol, '{{data.tip}}')); ?>
                        </p>
                        <# } #>

                    </div>
                </footer>

            </section>
        </div>
    </div>
    <div class="wc-backbone-modal-backdrop modal-close"></div>
</script>