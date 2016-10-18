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

	/* = Ticket submission form article search
	====================================================================================== */
	$('#close-search').click(function(e) {
        $('.kbs-article-search-results').hide("slow");
    });

	$('.kbs-article-search').focusout(function( event )	{

		$('#kbs-article-list').html('');

		if ( $(this).val().length < 3 ) {
			$('.kbs-article-search-results').hide("slow");
			return;
		}

		$('.kbs-article-search-results').hide("fast");
		$('#kbs-loading').html('<img src="' + kbs_scripts.ajax_loader + '" />');
		$('#kbs-loading').show("fast");

		var postData = {
			term   : $(this).val(),
			action : 'kbs_ajax_article_search'
		};

		$.ajax({
			type       : 'POST',
			dataType   : 'json',
			data       : postData,
			url        : kbs_scripts.ajaxurl,
			success    : function (response) {
				if ( response.articles && '' !== response.articles )	{
					$('#kbs-article-list').html(response.articles);
					$('.kbs-article-search-results').show("slow");
				} else	{
					$('#kbs-article-list').html();
					$('.kbs-article-search-results').hide("slow");
				}
			},
			complete: function()	{
				$('#kbs-loading').hide("fast");
				$('#kbs-loading').html('');
			}
		}).fail(function (data) {
			if ( window.console && window.console.log ) {
				console.log( data );
			}
		});

	});

	/* = Ticket submission form validation and submission
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

		var tinymceActive = (typeof tinyMCE != 'undefined') && tinyMCE.activeEditor && ! tinyMCE.activeEditor.isHidden();

		if (tinymceActive) {
			tinyMCE.triggerSave();
		}

		var $form      = $("#kbs_ticket_form");
		var ticketData = $("#kbs_ticket_form").serialize();

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

	/* = Ticket reply form validation and submission
	====================================================================================== */
	$(document).on('click', '.ticket_reply_content', function()	{
		var reply_id = $(this).data("key");
		$('#ticket_response_' + reply_id).toggle("fast");
	});

	$(document).on('click', '#kbs_ticket_reply_form #kbs_reply_submit', function(e) {
		var kbsReplyForm = document.getElementById('kbs_ticket_reply_form');

		if( typeof kbsReplyForm.checkValidity === "function" && false === kbsReplyForm.checkValidity() ) {
			return;
		}

		e.preventDefault();
		$(this).val(kbs_scripts.submit_ticket_loading);
		$(this).prop("disabled", true);
		$(this).after(' <span id="kbs-loading" class="kbs-loader kbs-hidden"><img src="' + kbs_scripts.ajax_loader + '" /></span>');
		$('input').removeClass("error");

		var tinymceActive = (typeof tinyMCE != 'undefined') && tinyMCE.activeEditor && ! tinyMCE.activeEditor.isHidden();

		if (tinymceActive) {
			tinyMCE.triggerSave();
		}

		var $form       = $("#kbs_ticket_reply_form");
		var ticketData  = $("#kbs_ticket_reply_form").serialize();

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
					$('#kbs_reply_submit').prop("disabled", false);
					$('#kbs-loading').remove();
					$('#kbs_reply_submit').val(kbs_scripts.reply_label);
				} else	{
					$form.append( '<input type="hidden" name="kbs_action" value="submit_ticket_reply" />' );
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
