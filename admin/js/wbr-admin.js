(function( $ ) {
	'use strict';

	if ( typeof wbr_delivery_params === 'undefined' ) {
		return false;
	}
	/**
	 * All of the code for your admin-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */
var WCOrdersTable = function () {

	$(document).on('click', '#wbr-button-pre-send:not(.disabled)', function () {
		var $button = $(this);
		var $order_id = $button.data('order-id');

        $.ajax({
			url: wbr_delivery_params.url,
            data: {
                order_id: $order_id,
				action: 'woober_get_order',
				security: wbr_delivery_params.nonce,
            },
            type: 'POST',
            success: function (response) {
                if (response.success) {
					$(this).WCBackboneModal({
						template: 'wbr-modal-view-delivery',
						variable: response.data
					});
                } else {
                    console.error(response.data);
                }
            },
            error: function (xhr, status, error) {
				console.error(error);
            }
        });
    });

	$(document).on('click', '#wbr-button-create-delivery:not(.disabled)', function () {
		var $button = $(this);
		var $order_id = $button.data('order-id');

		$.ajax({
			url: wbr_delivery_params.url,
			data: {
				order_id: $order_id,
				action: 'woober_create_delivery',
				security: wbr_delivery_params.nonce,
			},
			type: 'POST',
			success: function (response) {
				if (response.success) {
					console.log(response.data);
				} else {
					console.error(response.data);
				}
			},
			error: function (xhr, status, error) {
				console.error(error);
			}
		});
	});

};

/**
 * Init WCOrdersTable.
 */
new WCOrdersTable();

})( jQuery );
