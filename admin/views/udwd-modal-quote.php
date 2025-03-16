<script type="text/template" id="tmpl-udwd-modal-quote">
    <div id="udwd-modal-quote-container">
        <div class="wc-backbone-modal">
            <div class="wc-backbone-modal-content">
                <section class="wc-backbone-modal-main" role="main">
                    
                    <header class="wc-backbone-modal-header">
                        <mark class="order-status status-{{ data.status }}" style="float: right; margin-right: 54px"><span>{{ data.status }}</span></mark>
                        <?php /* translators: %s: order ID */ ?>
                        <h1><?php echo esc_html(sprintf(__('Envio do pedido #%s', 'udwdelivery'), '{{ data.number }}')); ?></h1>
                        <button class="modal-close modal-close-link dashicons dashicons-no-alt">
                            <span class="screen-reader-text"><?php esc_html_e('Close modal panel', 'udwdelivery'); ?></span>
                        </button>
                    </header>

                    <article>
                        <div id="udwd-quote-container">
                            <# if ( data.shipping ) { #>

                            <div id="udwd-shipping-preview-address" class="udwd-shipping-preview-block">
                                <h2><?php esc_html_e('Shipping Information', 'udwdelivery'); ?></h2>
                                <div class="udwd-shipping-preview-input-wrapper">
                                    <label><?php esc_html_e('Delivery Address', 'udwdelivery') ?></label>
                                    <input type="text" class="short udwd-shipping-input-address-1" value="{{{ data.shipping.address_1 }}}" disabled/>
                                </div>
                                <div class="udwd-shipping-preview-input-wrapper">
                                    <label><?php esc_html_e('Complement', 'udwdelivery') ?></label>
                                    <input type="text" class="udwd-shipping-input-address-2" value="{{{ data.shipping.address_2 }}}" disabled/>
                                </div>
                                <div class="udwd-shipping-preview-input-wrapper">
                                    <label><?php esc_html_e('Paid Shipping', 'udwdelivery') ?></label>
                                    <input type="text" class="udwd-shipping-input-shipping_total" value="R$ {{{ data.shipping_total }}}" disabled/>
                                </div>
                            </div>

                            <div id="udwd-shipping-preview-buyer" class="udwd-shipping-preview-block">
                                <h2><?php esc_html_e('Recipient', 'udwdelivery'); ?></h2>
                                <div class="udwd-shipping-preview-input-names-container">
                                    <div class="udwd-shipping-preview-input-wrapper name">
                                        <label><?php esc_html_e('Name', 'udwdelivery'); ?></label>
                                        <input type="text" class="udwd-shipping-input-first_name" value="{{{ data.shipping.first_name }}}" disabled/>
                                    </div>

                                    <div class="udwd-shipping-preview-input-wrapper name">
                                        <label><?php esc_html_e('Sobrenome', 'udwdelivery'); ?></label>
                                        <input type="text" class="udwd-shipping-input-last_name" value="{{{ data.shipping.last_name }}}" disabled/>
                                    </div>
                                </div>

                                <div class="udwd-shipping-preview-input-wrapper">
                                    <label><?php esc_html_e('Phone number', 'udwdelivery'); ?></label>
                                    <input type="text" class="udwd-shipping-input-phone" value="{{{ data.billing.phone }}}" disabled/>
                                </div>

                                <# if ( data.customer_note ) { #>
                                <div class="udwd-shipping-preview-input-wrapper">
                                    <div class="wc-order-preview-note">
                                        <strong><?php esc_html_e('Notes', 'udwdelivery'); ?></strong>
                                        {{ data.customer_note }}
                                    </div>
                                </div>
                                <# } #>
                            </div>

                            <div id="udwd-shipping-preview-package" class="udwd-shipping-preview-block">
                                <h2><?php esc_html_e('Package', 'udwdelivery'); ?></h2>
                                <div class="udwd-shipping-preview-input-wrapper">
                                    <label><?php esc_html_e('Order ID', 'udwdelivery'); ?></label>
                                    <input type="text" class="udwd-shipping-order-id" value="{{{ data.number }}}" disabled />
                                </div>
                            </div>

                            <# } #>
                        </div>

                    </article>

                    <footer>
                        <div class="inner">
                            <a id="udwd-button-create-delivery" data-order-id="{{data.number}}" class="button button-primary button-large inner" aria-label="<?php esc_attr_e('Request courier', 'udwdelivery'); ?>" href="<?php echo '#'; ?>" ><?php esc_html_e('Request courier', 'udwdelivery'); ?></a>
                        </div>
                    </footer>

                </section>
            </div>
        </div>
        <div class="wc-backbone-modal-backdrop modal-close"></div>
    </div>
</script>