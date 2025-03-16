(function( $ ) {
	'use strict';

	if ( typeof udwdelivery_params === 'undefined' ) {
		return false;
	}

var WCOrdersTable = function ()
{
	$(document).on('click', '#udwd-button-pre-send:not(.disabled)', function ()
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
			url: udwdelivery_params.url,
			type: 'POST',
            data: 
			{
                order_id: $order_id,
				action: 'udwd_get_delivery',
				security: udwdelivery_params.nonce,
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
					response.data.status_translated = udwdelivery_params.translations[response.data.status];

					// Selects the template to display
					if(response.data.dropoff)
					{
						response.data.fee = (parseFloat(response.data.fee) * 0.01).toFixed(2);
						response.data.tip = (parseFloat(response.data.tip) * 0.01).toFixed(2);

						$(this).WCBackboneModal
						({
							template: 'udwd-modal-delivery',
							variable: response.data
						});
					} 
					else 
					{
						$(this).WCBackboneModal
						({
							template: 'udwd-modal-quote',
							variable: response.data
						});
					}
                } 
				else 
				{
					$(this).WCBackboneModal
					({
						template: 'udwd-modal-error',
						variable: response.data
					});
                }
            },
            error: function (xhr, status, error)
			{
				console.error(error);
            }
        });
    });

	$(document).on('click', '#udwd-button-create-delivery:not(.disabled)', function ()
	{
		var $button = $(this);
		var $order_id = $button.data('order-id');

		$.ajax
		({
			url: udwdelivery_params.url,
			type: 'POST',
			data: {
				order_id: $order_id,
				action: 'udwd_create_delivery',
				security: udwdelivery_params.nonce,
			},
			beforeSend: function() 
			{
				document.getElementById('udwd-button-create-delivery').remove();
				document.getElementById('udwd-quote-container').innerHTML = '<div>Solicitando motorista...</div>';
			},
			success: function (response) 
			{
				if (response.success) 
				{
					document.getElementById('udwd-modal-quote-container').remove();
					$("a[data-order-id='" + $order_id + "']").text('Ver envio'); //Configure translation
					$(this).WCBackboneModal
					({
						template: 'udwd-modal-delivery',
						variable: response.data
					});
				}
				else
				{
					$(this).WCBackboneModal
					({
						template: 'udwd-modal-error',
						variable: response.data
					});
				}
			},
			error: function (xhr, status, error) 
			{
				console.error(error);
			}
		});
	});

	$(document).on('click', '#udwd-delivery-btn_coppy-tracking_url', function () 
	{
		var $url = document.getElementById('udwd-delivery-tracking_url');

		$url.select();
		$url.setSelectionRange(0, 99999);

		navigator.clipboard.writeText($url.value);
	});

	$(document).on('click', '#udwd-button-cancel-delivery:not(.disabled)', function () 
	{
		var $button = $(this);
		var $order_id = $button.data('order-id');

		var loaderContainer = $(this).closest('.wc-backbone-modal-content');
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
			url: udwdelivery_params.url,
			type: 'POST',
			data: {
				order_id: $order_id,
				action: 'udwd_cancel_delivery',
				security: udwdelivery_params.nonce,
			},
			beforeSend: function () 
			{
				loaderContainer.block(loaderProperties);
			},
			complete: function () 
			{
				loaderContainer.unblock();
			},
			success: function (response) 
			{
				if (response.success) 
				{
					console.log(response.data);
					var status_translated = udwdelivery_params.translations[response.data.status];
					$('#delivery-status').html(status_translated);
					$('#udwd-delivery-fee').html(status_translated);
					$('#udwd-delivery-tip').remove();
					$button.addClass('disabled');
				} 
				else
				{
					$(this).WCBackboneModal
					({
						template: 'udwd-modal-error',
						variable: response.data
					});
				}
			},
			error: function (xhr, status, error) 
			{
				console.error(error);
			}
		});
	});
};

new WCOrdersTable();

})( jQuery );
