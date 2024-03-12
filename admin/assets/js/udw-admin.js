(function( $ ) {
	'use strict';

	if ( typeof udw_delivery_params === 'undefined' ) {
		return false;
	}

var WCOrdersTable = function () {

	$(document).on('click', '#udw-button-pre-send:not(.disabled)', function () {
		var $button = $(this);
		var $order_id = $button.data('order-id');

        $.ajax({
			url: udw_delivery_params.url,
            data: {
                order_id: $order_id,
				action: 'udw_get_delivery',
				security: udw_delivery_params.nonce,
            },
            type: 'POST',
            success: function (response) {
                if (response.success) {
					// Selects which template to display
					if(response.data.dropoff) {
						$(this).WCBackboneModal({
							template: 'udw-modal-delivery',
							variable: response.data
						});
					} else {
						$(this).WCBackboneModal({
							template: 'udw-modal-quote',
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

	$(document).on('click', '#udw-button-create-delivery:not(.disabled)', function () {
		var $button = $(this);
		var $order_id = $button.data('order-id');

		$.ajax({
			url: udw_delivery_params.url,
			type: 'POST',
			data: {
				order_id: $order_id,
				action: 'udw_create_delivery',
				security: udw_delivery_params.nonce,
			},
			beforeSend: function() {
				document.getElementById('udw-button-create-delivery').remove();
				document.getElementById('udw-quote-container').innerHTML = '<div>Solicitando motorista...</div>';
			},
			success: function (response) {
				if (response.success) {
					document.getElementById('udw-modal-quote-container').remove();
					$("a[data-order-id='" + $order_id + "']").text('Ver envio');
					$(this).WCBackboneModal({
						template: 'udw-modal-delivery',
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

	$(document).on('click', '#udw-delivery-btn_coppy-tracking_url', function () {

		var $url = document.getElementById('udw-delivery-tracking_url');

		$url.select();
		$url.setSelectionRange(0, 99999);

		navigator.clipboard.writeText($url.value);
	});

};

new WCOrdersTable();

})( jQuery );
