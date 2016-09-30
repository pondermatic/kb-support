var kbs_vars;
jQuery(document).ready(function ($) {

	if ( kbs_vars.post_type && 'kbs_ticket' == kbs_vars.post_type )	{
		var icons = {
		  header: "ui-icon-circle-arrow-e",
		  activeHeader: "ui-icon-circle-arrow-s"
		};
		$( ".kbs_accordion" ).accordion({
			icons: icons,
			collapsible: true
		});
		$( "#toggle" ).button().on( "click", function() {
		if ( $( ".kbs_accordion" ).accordion( "option", "icons" ) ) {
			$( ".kbs_accordion" ).accordion( "option", "icons", null );
		} else {
			$( ".kbs_accordion" ).accordion( "option", "icons", icons );
		}
    });
	}

	/**
	 * Settings screen JS
	 */
	var KBS_Settings = {

		init : function() {
			this.general();
		},

		general : function() {

			var kbs_color_picker = $('.kbs-color-picker');

			if( kbs_color_picker.length ) {
				kbs_color_picker.wpColorPicker();
			}

			// Settings Upload field JS
			if ( typeof wp === "undefined" || '1' !== kbs_vars.new_media_ui ) {
				//Old Thickbox uploader
				var kbs_settings_upload_button = $( '.kbs_settings_upload_button' );
				if ( kbs_settings_upload_button.length > 0 ) {
					window.formfield = '';

					$( document.body ).on('click', kbs_settings_upload_button, function(e) {
						e.preventDefault();
						window.formfield = $(this).parent().prev();
						window.tbframe_interval = setInterval(function() {
							jQuery('#TB_iframeContent').contents().find('.savesend .button').val(kbs_vars.use_this_file).end().find('#insert-gallery, .wp-post-thumbnail').hide();
						}, 2000);
						tb_show( kbs_vars.add_new_ticket, 'media-upload.php?TB_iframe=true' );
					});

					window.kbs_send_to_editor = window.send_to_editor;
					window.send_to_editor = function (html) {
						if (window.formfield) {
							imgurl = $('a', '<div>' + html + '</div>').attr('href');
							window.formfield.val(imgurl);
							window.clearInterval(window.tbframe_interval);
							tb_remove();
						} else {
							window.kbs_send_to_editor(html);
						}
						window.send_to_editor = window.kbs_send_to_editor;
						window.formfield = '';
						window.imagefield = false;
					};
				}
			} else {
				// WP 3.5+ uploader
				var file_frame;
				window.formfield = '';

				$( document.body ).on('click', '.kbs_settings_upload_button', function(e) {

					e.preventDefault();

					var button = $(this);

					window.formfield = $(this).parent().prev();

					// If the media frame already exists, reopen it.
					if ( file_frame ) {
						//file_frame.uploader.uploader.param( 'post_id', set_to_post_id );
						file_frame.open();
						return;
					}

					// Create the media frame.
					file_frame = wp.media.frames.file_frame = wp.media({
						frame: 'post',
						state: 'insert',
						title: button.data( 'uploader_title' ),
						button: {
							text: button.data( 'uploader_button_text' )
						},
						multiple: false
					});

					file_frame.on( 'menu:render:default', function( view ) {
						// Store our views in an object.
						var views = {};

						// Unset default menu items
						view.unset( 'library-separator' );
						view.unset( 'gallery' );
						view.unset( 'featured-image' );
						view.unset( 'embed' );

						// Initialize the views in our view object.
						view.set( views );
					} );

					// When an image is selected, run a callback.
					file_frame.on( 'insert', function() {

						var selection = file_frame.state().get('selection');
						selection.each( function( attachment, index ) {
							attachment = attachment.toJSON();
							window.formfield.val(attachment.url);
						});
					});

					// Finally, open the modal
					file_frame.open();
				});


				// WP 3.5+ uploader
				var file_frame;
				window.formfield = '';
			}

		}

	}
	KBS_Settings.init();
	
	/**
	 * Tickets screen JS
	 */
	var KBS_Tickets = {
		init : function() {
			this.reply();
		},
		
		reply : function() {
			// Reply to ticket Requests
			$( document.body ).on( 'click', '#kbs-reply-close, #kbs-reply-update', function(event) {
				
				event.preventDefault();

				var ticketResponse = '';
				var tinymceActive  = (typeof tinyMCE != 'undefined') && tinyMCE.activeEditor && ! tinyMCE.activeEditor.isHidden();

				if (tinymceActive) {
					tinyMCE.triggerSave();
				}

				ticketResponse = $('#kbs_ticket_reply' ).val();

				if ( ticketResponse.length === 0 )	{
					alert( kbs_vars.no_ticket_reply_content );
					return false;
				}

				if ( 'kbs-reply-close' == event.target.id )	{
					var confirmClose = confirm( kbs_vars.ticket_confirm_close );

					if (confirmClose == false) {
						return;
					}
				}

				var postData         = {
					ticket_id    : kbs_vars.post_id,
					response     : ticketResponse,
					close_ticket : ( 'kbs-reply-close' == event.target.id ? 1 : 0 ),
					action       : 'kbs_reply_to_ticket'
				};

				$.ajax({
					type: "POST",
					dataType: "json",
					data: postData,
					url: ajaxurl,
					beforeSend: function()	{
						$("input").prop('disabled', true);
						$("#kbs-ticket-reply-container").addClass('kbs-hidden');
						$("#kbs-loading").removeClass('kbs-hidden');
					},
					success: function (response) {
						if (response.error)	{
							window.location.href = kbs_vars.current_url + '&kbs-message=' + response.message;
						} else	{
							window.location.href = kbs_vars.current_url + '&kbs-message=' + response.message;
							return true;
						}
					}
				}).fail(function (data) {
					$("input").prop('disabled', false);
					$("#kbs-ticket-reply-container").removeClass('kbs-hidden');
					$("#kbs-loading").addClass('kbs-hidden');
					if ( window.console && window.console.log ) {
						console.log( data );
					}
				});

			});
		}
	}
	KBS_Tickets.init();
	
	/**
	 * Forms screen JS
	 */
	var KBS_Forms = {

		init : function() {
			this.forms();
			this.move();
		},

		forms : function() {

			var toggleFieldOptions = function(kbs_selected_field)	{
					if ( 'text'          == kbs_selected_field
						 || 'date_field' == kbs_selected_field
						 || 'email'      == kbs_selected_field
						 || 'number'     == kbs_selected_field
						 || 'textarea'   == kbs_selected_field
						 || 'url'        == kbs_selected_field
					)	{
							 
						$('#kbs_meta_field_placeholder_wrap').show();
						$('#kbs_meta_field_hide_label_wrap').show();
					} else	{
						$('#kbs_meta_field_placeholder_wrap').hide();
						$('#kbs_meta_field_hide_label_wrap').hide();
					}
					
					if ( 'select'          == kbs_selected_field
						|| 'checkbox_list' == kbs_selected_field
						|| 'radio'         == kbs_selected_field
					)	{
						$('#kbs_meta_field_select_options_wrap').show();
					} else	{
						$('#kbs_meta_field_select_options_wrap').hide();
					}
					
					if ( 'select' == kbs_selected_field )	{
						$('#kbs_meta_field_select_multiple_wrap').show();
					} else	{
						$('#kbs_meta_field_select_multiple_wrap').hide();
					}
					
					if ( 'select' == kbs_selected_field
						|| 'kb_category_dropdown'     == kbs_selected_field
						|| 'ticket_category_dropdown' == kbs_selected_field
					)	{
						$('#kbs_meta_field_select_searchable_wrap').show();
					} else	{
						$('#kbs_meta_field_select_searchable_wrap').hide();
					}
					
					if ( 'checkbox' == kbs_selected_field )	{
						$('#kbs_meta_field_option_selected_wrap').show();
					} else	{
						$('#kbs_meta_field_option_selected_wrap').hide();
					}
					
					if ( 'checkbox'      == kbs_selected_field
						|| 'file_upload' == kbs_selected_field
					)	{
						$('#kbs_meta_field_required_wrap').hide();
					} else	{
						$('#kbs_meta_field_required_wrap').show();
					}
					
					if ( 'file_upload' == kbs_selected_field )	{
						$('#kbs_meta_field_maxfiles_wrap').show();
					} else	{
						$('#kbs_meta_field_maxfiles_wrap').hide();
					}
					
					if ( 'recaptcha' == kbs_selected_field )	{
						$('#kbs_meta_field_required_wrap').hide();
						$('#kbs_meta_field_label_class_wrap').hide();
						$('#kbs_meta_field_input_class_wrap').hide();
					} else	{
						$('#kbs_meta_field_required_wrap').show();
						$('#kbs_meta_field_label_class_wrap').show();
						$('#kbs_meta_field_input_class_wrap').show();
					}
	
					if ( 'text'          == kbs_selected_field
						|| 'email'       == kbs_selected_field
						|| 'textarea'    == kbs_selected_field
						|| 'rich_editor' == kbs_selected_field )	{
						$('#kbs_meta_field_mapping_wrap').show();
					} else	{
						$('#kbs_meta_field_mapping_wrap').hide();
					}
				}

			// Preload field options when editing
			if ( kbs_vars.editing_field_type )	{
				toggleFieldOptions(kbs_vars.editing_field_type);
			}

			var kbs_field_type = $('.kbs_field_type');

			$( document.body ).on('change', kbs_field_type, function(e)	{

				toggleFieldOptions(kbs_field_type.val());

			});
			
			// Send Add New Field Requests
			$( document.body ).on( 'click', '#kbs-add-form-field', function(event) {
				
				event.preventDefault();
				
				if ( $('#kbs_field_label').val().length < 1 )	{
					alert( kbs_vars.field_label_missing );
					return false;
				}
				if ( $('#kbs_field_type').val() == '-1' )	{
					alert( kbs_vars.field_type_missing );
					return false;
				}

				var return_url       = $('#form_return_url').val();			
				var postData         = {
					form_id          : kbs_vars.post_id,
					label            : $('#kbs_field_label').val(),
					type             : $('#kbs_field_type').val(),
					mapping          : $('#kbs_field_mapping').val(),
					required         : ( $('#kbs_field_required').is(':checked') )        ? $('#kbs_field_required').val()        : 0,
					label_class      : $('#kbs_field_label_class').val(),
					input_class      : $('#kbs_field_input_class').val(),
					select_options   : $('textarea#kbs_field_select_options').val(),
					select_multiple  : ( $('#kbs_field_select_multiple').is(':checked') ) ? $('#kbs_field_select_multiple').val() : 0,
					selected         : ( $('#kbs_field_option_selected').is(':checked') ) ? $('#kbs_field_option_selected').val() : 0,
					maxfiles         : $('#kbs_field_maxfiles').val(),
					chosen           : ( $('#kbs_field_select_chosen').is(':checked') )   ? $('#kbs_field_select_chosen').val()   : 0,
					description      : $('#kbs_field_description').val(),
					description_pos  : $('input[name=kbs_field_description_pos]').filter(':checked').val(),
					placeholder      : $('#kbs_field_placeholder').val(),
					hide_label       : ( $('#kbs_field_hide_label').is(':checked') )      ? $('#kbs_field_hide_label').val()      : 0,
					action           : 'kbs_add_form_field',
				};
				
				$.ajax({
					type: "POST",
					dataType: "json",
					data: postData,
					url: ajaxurl,
					beforeSend: function()	{
						$("#kbs-field-add").addClass('kbs-hidden');
						$("#kbs-loading").removeClass('kbs-hidden');
					},
					success: function (response) {
						window.location.href = return_url + '&kbs-message=' + response.message;
						return true;
					}
				}).fail(function (data) {
					$("#kbs-field-add").removeClass('kbs-hidden');
					$("#kbs-loading").addClass('kbs-hidden');
					if ( window.console && window.console.log ) {
						console.log( data );
					}
				});
				
			});
			
			// Send Edit Field Requests
			$( document.body ).on( 'click', '#kbs-save-form-field', function(event) {
				
				event.preventDefault();
				
				if ( $('#kbs_field_label').val().length < 1 )	{
					alert( kbs_vars.field_label_missing );
					return false;
				}
				if ( $('#kbs_field_type').val() == '-1' )	{
					alert( kbs_vars.field_type_missing );
					return false;
				}

				var return_url       = $('#form_return_url').val();			
				var postData         = {
					form_id          : kbs_vars.post_id,
					field_id         : $('#kbs_edit_field').val(), 
					label            : $('#kbs_field_label').val(),
					type             : $('#kbs_field_type').val(),
					mapping          : $('#kbs_field_mapping').val(),
					required         : ( $('#kbs_field_required').is(':checked') ) ? $('#kbs_field_required').val() : 0,
					label_class      : $('#kbs_field_label_class').val(),
					input_class      : $('#kbs_field_input_class').val(),
					select_options   : $('textarea#kbs_field_select_options').val(),
					select_multiple  : ( $('#kbs_field_select_multiple').is(':checked') ) ? $('#kbs_field_select_multiple').val() : 0,
					selected         : ( $('#kbs_field_option_selected').is(':checked') ) ? $('#kbs_field_option_selected').val() : 0,
					maxfiles         : $('#kbs_field_maxfiles').val(),
					chosen           : ( $('#kbs_field_select_chosen').is(':checked') )   ? $('#kbs_field_select_chosen').val()   : 0,
					placeholder      : $('#kbs_field_placeholder').val(),
					description      : $('#kbs_field_description').val(),
					description_pos  : $('input[name=kbs_field_description_pos]').filter(':checked').val(),
					hide_label       : ( $('#kbs_field_hide_label').is(':checked') )      ? $('#kbs_field_hide_label').val()      : 0,
					action           : 'kbs_save_form_field',
				};
				
				$.ajax({
					type: "POST",
					dataType: "json",
					data: postData,
					url: ajaxurl,
					beforeSend: function()	{
						$("#kbs-field-save").addClass('kbs-hidden');
						$("#kbs-loading").removeClass('kbs-hidden');
					},
					success: function (response) {
						window.location.href = return_url + '&kbs-message=' + response.message;
						return true;
					}
				}).fail(function (data) {
					$("#kbs-field-save").removeClass('kbs-hidden');
					$("#kbs-loading").addClass('kbs-hidden');
					if ( window.console && window.console.log ) {
						console.log( data );
					}
				});
				
			});
			
		},
		
		move : function() {

			$(".kbs_sortable_table tbody").sortable({
				handle: '.kbs_draghandle', items: '.kbs_sortable_row', opacity: 0.6, cursor: 'move', axis: 'y', update: function() {
					var order = $(this).sortable('serialize') + '&action=kbs_order_form_fields';
						
					$.post(ajaxurl, order, function(response)	{
						// Success
					});
				}
			});

		}

	}
	KBS_Forms.init();

	// Date picker
	var kbs_datepicker = $( '.kbs_datepicker' );
	if ( kbs_datepicker.length > 0 ) {
		var dateFormat = 'mm/dd/yy';
		kbs_datepicker.datepicker( {
			dateFormat: dateFormat
		} );
	}

});