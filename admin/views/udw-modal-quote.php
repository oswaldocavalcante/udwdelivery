<script type="text/template" id="tmpl-udw-modal-quote">
    <div id="udw-modal-quote-container">
        <div class="wc-backbone-modal">
            <div class="wc-backbone-modal-content">
                <section class="wc-backbone-modal-main" role="main">
                    
                    <header class="wc-backbone-modal-header">
                        <mark class="order-status status-{{ data.status }}" style="float: right; margin-right: 54px"><span>{{ data.status }}</span></mark>
                        <?php /* translators: %s: order ID */ ?>
                        <h1><?php echo esc_html(sprintf(__('Envio do pedido #%s', 'uberdirect'), '{{ data.number }}')); ?></h1>
                        <button class="modal-close modal-close-link dashicons dashicons-no-alt">
                            <span class="screen-reader-text"><?php esc_html_e('Close modal panel', 'woocommerce'); ?></span>
                        </button>
                    </header>

                    <article>
                        <div id="udw-quote-container">
                            <# if ( data.shipping ) { #>

                            <div id="udw-shipping-preview-address" class="udw-shipping-preview-block">
                                <h2><?php esc_html_e('Shipping Information', 'uberdirect'); ?></h2>
                                <div class="udw-shipping-preview-input-wrapper">
                                    <label><?php echo  __('Delivery Address', 'uberdirect') ?></label>
                                    <input type="text" class="short udw-shipping-input-address-1" value="{{{ data.shipping.address_1 }}}" disabled/>
                                </div>
                                <div class="udw-shipping-preview-input-wrapper">
                                    <label><?php echo  __('Complement', 'uberdirect') ?></label>
                                    <input type="text" class="udw-shipping-input-address-2" value="{{{ data.shipping.address_2 }}}" disabled/>
                                </div>
                                <div class="udw-shipping-preview-input-wrapper">
                                    <label><?php echo  __('Paid Shipping', 'uberdirect') ?></label>
                                    <input type="text" class="udw-shipping-input-shipping_total" value="R$ {{{ data.shipping_total }}}" disabled/>
                                </div>
                            </div>

                            <div id="udw-shipping-preview-buyer" class="udw-shipping-preview-block">
                                <h2><?php esc_html_e('Recipient Information', 'uberdirect'); ?></h2>
                                <div class="udw-shipping-preview-input-names-container">
                                    <div class="udw-shipping-preview-input-wrapper name">
                                        <label><?php esc_html_e('Name', 'uberdirect'); ?></label>
                                        <input type="text" class="udw-shipping-input-first_name" value="{{{ data.shipping.first_name }}}" disabled/>
                                    </div>

                                    <div class="udw-shipping-preview-input-wrapper name">
                                        <label><?php esc_html_e('Sobrenome', 'uberdirect'); ?></label>
                                        <input type="text" class="udw-shipping-input-last_name" value="{{{ data.shipping.last_name }}}" disabled/>
                                    </div>
                                </div>

                                <div class="udw-shipping-preview-input-wrapper">
                                    <label><?php esc_html_e('Phone number', 'uberdirect'); ?></label>
                                    <input type="text" class="udw-shipping-input-phone" value="{{{ data.billing.phone }}}" disabled/>
                                </div>

                                <# if ( data.customer_note ) { #>
                                <div class="udw-shipping-preview-input-wrapper">
                                    <div class="wc-order-preview-note">
                                        <strong><?php esc_html_e('Notes', 'uberdirect'); ?></strong>
                                        {{ data.customer_note }}
                                    </div>
                                </div>
                                <# } #>
                            </div>

                            <div id="udw-shipping-preview-package" class="udw-shipping-preview-block">
                                <h2><?php esc_html_e('Package information', 'uberdirect'); ?></h2>
                                <div class="udw-shipping-preview-input-wrapper">
                                    <label><?php esc_html_e('Order ID', 'uberdirect'); ?></label>
                                    <input type="text" class="udw-shipping-order-id" value="{{{ data.number }}}" disabled />
                                </div>
                            </div>

                            <# } #>
                        </div>

                    </article>

                    <footer>
                        <div class="inner">
                            <a id="udw-button-create-delivery" data-order-id="{{data.number}}" class="button button-primary button-large inner" aria-label="<?php esc_attr_e('Request courier', 'uberdirect'); ?>" href="<?php echo '#'; ?>" ><?php esc_html_e('Request courier', 'uberdirect'); ?></a>
                        </div>
                    </footer>

                </section>
            </div>
        </div>
        <div class="wc-backbone-modal-backdrop modal-close"></div>
    </div>
</script>