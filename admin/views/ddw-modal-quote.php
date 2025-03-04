<script type="text/template" id="tmpl-ddw-modal-quote">
    <div id="ddw-modal-quote-container">
        <div class="wc-backbone-modal">
            <div class="wc-backbone-modal-content">
                <section class="wc-backbone-modal-main" role="main">
                    
                    <header class="wc-backbone-modal-header">
                        <mark class="order-status status-{{ data.status }}" style="float: right; margin-right: 54px"><span>{{ data.status }}</span></mark>
                        <?php /* translators: %s: order ID */ ?>
                        <h1><?php echo esc_html(sprintf(__('Envio do pedido #%s', 'directdelivery'), '{{ data.number }}')); ?></h1>
                        <button class="modal-close modal-close-link dashicons dashicons-no-alt">
                            <span class="screen-reader-text"><?php esc_html_e('Close modal panel', 'directdelivery'); ?></span>
                        </button>
                    </header>

                    <article>
                        <div id="ddw-quote-container">
                            <# if ( data.shipping ) { #>

                            <div id="ddw-shipping-preview-address" class="ddw-shipping-preview-block">
                                <h2><?php esc_html_e('Shipping Information', 'directdelivery'); ?></h2>
                                <div class="ddw-shipping-preview-input-wrapper">
                                    <label><?php esc_html_e('Delivery Address', 'directdelivery') ?></label>
                                    <input type="text" class="short ddw-shipping-input-address-1" value="{{{ data.shipping.address_1 }}}" disabled/>
                                </div>
                                <div class="ddw-shipping-preview-input-wrapper">
                                    <label><?php esc_html_e('Complement', 'directdelivery') ?></label>
                                    <input type="text" class="ddw-shipping-input-address-2" value="{{{ data.shipping.address_2 }}}" disabled/>
                                </div>
                                <div class="ddw-shipping-preview-input-wrapper">
                                    <label><?php esc_html_e('Paid Shipping', 'directdelivery') ?></label>
                                    <input type="text" class="ddw-shipping-input-shipping_total" value="R$ {{{ data.shipping_total }}}" disabled/>
                                </div>
                            </div>

                            <div id="ddw-shipping-preview-buyer" class="ddw-shipping-preview-block">
                                <h2><?php esc_html_e('Recipient', 'directdelivery'); ?></h2>
                                <div class="ddw-shipping-preview-input-names-container">
                                    <div class="ddw-shipping-preview-input-wrapper name">
                                        <label><?php esc_html_e('Name', 'directdelivery'); ?></label>
                                        <input type="text" class="ddw-shipping-input-first_name" value="{{{ data.shipping.first_name }}}" disabled/>
                                    </div>

                                    <div class="ddw-shipping-preview-input-wrapper name">
                                        <label><?php esc_html_e('Sobrenome', 'directdelivery'); ?></label>
                                        <input type="text" class="ddw-shipping-input-last_name" value="{{{ data.shipping.last_name }}}" disabled/>
                                    </div>
                                </div>

                                <div class="ddw-shipping-preview-input-wrapper">
                                    <label><?php esc_html_e('Phone number', 'directdelivery'); ?></label>
                                    <input type="text" class="ddw-shipping-input-phone" value="{{{ data.billing.phone }}}" disabled/>
                                </div>

                                <# if ( data.customer_note ) { #>
                                <div class="ddw-shipping-preview-input-wrapper">
                                    <div class="wc-order-preview-note">
                                        <strong><?php esc_html_e('Notes', 'directdelivery'); ?></strong>
                                        {{ data.customer_note }}
                                    </div>
                                </div>
                                <# } #>
                            </div>

                            <div id="ddw-shipping-preview-package" class="ddw-shipping-preview-block">
                                <h2><?php esc_html_e('Package', 'directdelivery'); ?></h2>
                                <div class="ddw-shipping-preview-input-wrapper">
                                    <label><?php esc_html_e('Order ID', 'directdelivery'); ?></label>
                                    <input type="text" class="ddw-shipping-order-id" value="{{{ data.number }}}" disabled />
                                </div>
                            </div>

                            <# } #>
                        </div>

                    </article>

                    <footer>
                        <div class="inner">
                            <a id="ddw-button-create-delivery" data-order-id="{{data.number}}" class="button button-primary button-large inner" aria-label="<?php esc_attr_e('Request courier', 'directdelivery'); ?>" href="<?php echo '#'; ?>" ><?php esc_html_e('Request courier', 'directdelivery'); ?></a>
                        </div>
                    </footer>

                </section>
            </div>
        </div>
        <div class="wc-backbone-modal-backdrop modal-close"></div>
    </div>
</script>