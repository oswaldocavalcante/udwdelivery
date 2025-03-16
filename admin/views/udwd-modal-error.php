<script type="text/template" id="tmpl-udwd-modal-error">
    <div id="udwd-modal-quote-container">
        <div class="wc-backbone-modal">
            <div class="wc-backbone-modal-content">
                <section class="wc-backbone-modal-main" role="main">
                    
                    <header class="wc-backbone-modal-header">
                        <h1><?php esc_html_e('Error', 'udwdelivery'); ?></h1>
                        <button class="modal-close modal-close-link dashicons dashicons-no-alt">
                            <span class="screen-reader-text"><?php esc_html_e('Close modal panel', 'udwdelivery'); ?></span>
                        </button>
                    </header>

                    <article>
                        <ul>
                            <# _.each( data, function( error ) { #>
                                <li>{{ error.message }}</li>
                            <# }); #>
                        </ul>
                    </article>

                    <footer>
                        <div class="inner">

                        </div>
                    </footer>

                </section>
            </div>
        </div>
        <div class="wc-backbone-modal-backdrop modal-close"></div>
    </div>
</script>