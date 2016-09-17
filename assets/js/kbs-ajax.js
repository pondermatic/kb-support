var kbs_scripts;
jQuery(document).ready(function ($) {

	// Date picker
	var kbs_datepicker = $( '.kbs_datepicker' );
	if ( kbs_datepicker.length > 0 ) {
		var dateFormat = 'mm/dd/yy';
		kbs_datepicker.datepicker( {
			dateFormat: dateFormat
		} );
	}

	$(document).on('click', '#kbs_ticket_form #kbs_ticket_submit', function(e) {
		var kbsTicketForm = document.getElementById('kbs_ticket_form');

		if( typeof kbsTicketForm.checkValidity === "function" && false === kbsTicketForm.checkValidity() ) {
			return;
		}

		e.preventDefault();
		$(this).val(kbs_scripts.submit_ticket_loading);
		$(this).prop("disabled", true);
		$(this).after('<span class="kbs_ticket_ajax"><i class="kbs-icon-spinner kbs-icon-spin"></i></span>');

		var $form    = $("#kbs_ticket_form");
		var postData = $("#kbs_ticket_form").serialize();

		$.ajax({
			type       : 'POST',
			dataType   : 'json',
			data       : postData,
			url        : kbs_scripts.ajaxurl,
			success    : function (response) {
				if ( '' != response.error )	{
					$form.find('.kbs_alert').show("fast");
					$form.find('.kbs_alert').html(response.error);
					$('#kbs_ticket_submit').prop("disabled", false);
					$('#kbs_ticket_submit').val(kbs_scripts.submit_ticket);
				} else	{
					$form.append( '<input type="hidden" name="kbs_action" value="submit_ticket" />' );
					//$form.get(0).submit();
					alert('submit');
				}
			}
		}).fail(function (data) {
			if ( window.console && window.console.log ) {
				console.log( data );
			}
		});

	});

});
