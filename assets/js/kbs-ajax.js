var kbs_scripts;
jQuery(document).ready(function ($) {

	/* = Datepicker
	====================================================================================== */
	var kbs_datepicker = $( '.kbs_datepicker' );
	if ( kbs_datepicker.length > 0 ) {
		var dateFormat = 'mm/dd/yy';
		kbs_datepicker.datepicker( {
			dateFormat: dateFormat
		} );
	}

	/* = Ticket form validation and submission
	====================================================================================== */
	$(document).on('click', '#kbs_ticket_form #kbs_ticket_submit', function(e) {
		var kbsTicketForm = document.getElementById('kbs_ticket_form');

		if( typeof kbsTicketForm.checkValidity === "function" && false === kbsTicketForm.checkValidity() ) {
			return;
		}

		e.preventDefault();
		$(this).val(kbs_scripts.submit_ticket_loading);
		$(this).prop("disabled", true);
		$(this).after(' <span id="kbs-loading" class="kbs-loader kbs-hidden"><img src="' + kbs_scripts.ajax_loader + '" /></span>');
		$('input').removeClass("error");

		var $form      = $("#kbs_ticket_form");
		var ticketData = $("#kbs_ticket_form").serialize();

		var ticketContent;

		var tinymceActive = (typeof tinyMCE != 'undefined') && tinyMCE.activeEditor && ! tinyMCE.activeEditor.isHidden();

		if (tinymceActive) {
			ticketContent = tinyMCE.activeEditor.getContent();
			ticketData[tinymce.editors[0].id] = ticketContent;
		}

		$.ajax({
			type       : 'POST',
			dataType   : 'json',
			data       : ticketData,
			url        : kbs_scripts.ajaxurl,
			success    : function (response) {
				if ( response.error )	{
					$form.find('.kbs_alert').show("fast");
					$form.find('.kbs_alert').html(response.error);
					$form.find('#' + response.field).addClass("error");
					$form.find('#' + response.field).focus();
					$('#kbs_ticket_submit').prop("disabled", false);
					$('#kbs-loading').remove();
					$('#kbs_ticket_submit').val(kbs_scripts.submit_ticket);
				} else	{
					$form.append( '<input type="hidden" name="kbs_action" value="submit_ticket" />' );
					$form.get(0).submit();
				}
			}
		}).fail(function (data) {
			if ( window.console && window.console.log ) {
				console.log( data );
			}
		});

	});

});
