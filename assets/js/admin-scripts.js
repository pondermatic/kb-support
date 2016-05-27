jQuery(document).ready(function ($) {

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
	 * Forms screen JS
	 */
	var KBS_Forms = {

		init : function() {
			this.forms();
			this.move();
		},

		forms : function() {

			var kbs_field_type = $('.kbs_field_type');
			
			$( document.body ).on('change', kbs_field_type, function(e)	{

				var kbs_selected_field = kbs_field_type.val();

				if ( 'text' == kbs_field_type.val()
					 || 'date_field' == kbs_selected_field
					 || 'email'      == kbs_selected_field
					 || 'number'     == kbs_selected_field
					 || 'textarea'   == kbs_selected_field
					 || 'url'        == kbs_selected_field )	{
						 
					document.getElementById('kbs_meta_field_placeholder_wrap').style.display = "block";
					document.getElementById('kbs_meta_field_hide_label_wrap').style.display = "block";
				} else	{
					document.getElementById('kbs_meta_field_placeholder_wrap').style.display = "none";
					document.getElementById('kbs_meta_field_hide_label_wrap').style.display = "none";
				}
				
				if ( 'select' == kbs_selected_field || 'checkbox_list' == kbs_selected_field || 'radio' == kbs_selected_field )	{
					document.getElementById('kbs_meta_field_select_options_wrap').style.display = "block";
				} else	{
					document.getElementById('kbs_meta_field_select_options_wrap').style.display = "none";
				}
				
				if ( 'select' == kbs_selected_field || 'kb_category_dropdown' == kbs_selected_field || 'ticket_category_dropdown' == kbs_selected_field )	{
					document.getElementById('kbs_meta_field_select_searchable_wrap').style.display = "block";
				} else	{
					document.getElementById('kbs_meta_field_select_searchable_wrap').style.display = "none";
				}
				
				if ( 'checkbox' == kbs_selected_field )	{
					document.getElementById('kbs_meta_field_option_selected_wrap').style.display = "block";
				} else	{
					document.getElementById('kbs_meta_field_option_selected_wrap').style.display = "none";
				}
				
				if ( 'recaptcha' == kbs_selected_field )	{
					document.getElementById('kbs_meta_field_required_wrap').style.display = "none";
					document.getElementById('kbs_meta_field_label_class_wrap').style.display = "none";
					document.getElementById('kbs_meta_field_input_class_wrap').style.display = "none";
				} else	{
					document.getElementById('kbs_meta_field_required_wrap').style.display = "block";
					document.getElementById('kbs_meta_field_label_class_wrap').style.display = "block";
					document.getElementById('kbs_meta_field_input_class_wrap').style.display = "block";
				}

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
					required         : ( $('#kbs_field_required').is(':checked') ) ? $('#kbs_field_required').val() : 0,
					label_class      : $('#kbs_field_label_class').val(),
					input_class      : $('#kbs_field_input_class').val(),
					select_options   : $('textarea#kbs_field_select_options').val(),
					selected         : ( $('#kbs_field_option_selected').is(':checked') ) ? $('#kbs_field_option_selected').val() : 0,
					chosen           : ( $('#kbs_field_select_chosen').is(':checked') ) ? $('#kbs_field_select_chosen').val() : 0,
					placeholder      : $('#kbs_field_placeholder').val(),
					hide_label       : ( $('#kbs_field_hide_label').is(':checked') ) ? $('#kbs_field_hide_label').val() : 0,
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