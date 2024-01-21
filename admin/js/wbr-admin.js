(function( $ ) {
	'use strict';

	if ( typeof wbr_delivery_params === 'undefined' ) {
		return false;
	}

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
					console.log(response.data);
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
			type: 'POST',
			data: {
				order_id: $order_id,
				action: 'woober_create_delivery',
				security: wbr_delivery_params.nonce,
			},
			beforeSend: function() {
				document.getElementById('wbr-shipping-preview').innerHTML = '<div>Solicitando motorista...</div>';
			},
			success: function (response) {
				document.getElementById('wbr-shipping-preview').innerHTML = '<div>Motorista encontrado...</div>';
				console.log(response.data);

				if (response.success) {
					$(this).WCBackboneModal({
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

};

new WCOrdersTable();

})( jQuery );
