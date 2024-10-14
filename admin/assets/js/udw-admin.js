(function( $ ) {
	'use strict';

	if ( typeof udw_delivery_params === 'undefined' ) {
		return false;
	}

var WCOrdersTable = function ()
{
	$(document).on('click', '#udw-button-pre-send:not(.disabled)', function ()
	{
		var $button = $(this);
		var $order_id = $button.data('order-id');

		var loaderContainer = $button;
		var loaderProperties = 
		{
			message: null,
			overlayCSS:
			{
				background: '#fff',
				opacity: 0.6
			}
		};

        $.ajax
		({
			url: udw_delivery_params.url,
			type: 'POST',
            data: 
			{
                order_id: $order_id,
				action: 'udw_get_delivery',
				security: udw_delivery_params.nonce,
            },
			beforeSend: function ()
			{
				loaderContainer.addClass('disabled');
				loaderContainer.block(loaderProperties);
			},
			complete: function ()
			{
				loaderContainer.unblock();
				loaderContainer.removeClass('disabled');
			},
            success: function (response) 
			{
                if (response.success) 
				{
					// Translates the status string
					response.data.status = udw_delivery_params.translations[response.data.status];

					// Selects the template to display
					if(response.data.dropoff) 
					{
						response.data.fee = (parseFloat(response.data.fee) * 0.01).toFixed(2);
						response.data.tip = (parseFloat(response.data.tip) * 0.01).toFixed(2);

						$(this).WCBackboneModal
						({
							template: 'udw-modal-delivery',
							variable: response.data
						});
					} 
					else 
					{
						$(this).WCBackboneModal
						({
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

	$(document).on('click', '#udw-button-create-delivery:not(.disabled)', function ()
	{
		var $button = $(this);
		var $order_id = $button.data('order-id');

		$.ajax
		({
			url: udw_delivery_params.url,
			type: 'POST',
			data: {
				order_id: $order_id,
				action: 'udw_create_delivery',
				security: udw_delivery_params.nonce,
			},
			beforeSend: function() 
			{
				document.getElementById('udw-button-create-delivery').remove();
				document.getElementById('udw-quote-container').innerHTML = '<div>Solicitando motorista...</div>';
			},
			success: function (response) 
			{
				if (response.success) {
					document.getElementById('udw-modal-quote-container').remove();
					$("a[data-order-id='" + $order_id + "']").text('Ver envio'); //Configure translation
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
