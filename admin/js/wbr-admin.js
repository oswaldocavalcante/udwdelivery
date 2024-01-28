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
				action: 'woober_get_delivery_data',
				security: wbr_delivery_params.nonce,
            },
            type: 'POST',
            success: function (response) {
                if (response.success) {
					// Selects which template to display
					if(response.data.dropoff) {
						$(this).WCBackboneModal({
							template: 'wbr-modal-delivery',
							variable: response.data
						});
					} else {
						$(this).WCBackboneModal({
							template: 'wbr-modal-quote',
							variable: response.data
						});
					}
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
				document.getElementById('wbr-button-create-delivery').remove();
				document.getElementById('wbr-quote-container').innerHTML = '<div>Solicitando motorista...</div>';
			},
			success: function (response) {
				if (response.success) {
					document.getElementById('wbr-modal-quote-container').remove();
					$("a[data-order-id='" + $order_id + "']").text('Ver envio');
					$(this).WCBackboneModal({
						template: 'wbr-modal-delivery',
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

	$(document).on('click', '#wbr-delivery-btn_coppy-tracking_url', function () {

		var $url = document.getElementById('wbr-delivery-tracking_url');

		$url.select();
		$url.setSelectionRange(0, 99999);

		navigator.clipboard.writeText($url.value);
	});

};

new WCOrdersTable();

})( jQuery );
