<?php
/**
 * Functions for forms
 *
 * @package     KBS
 * @subpackage  Functions/Forms
 * @copyright   Copyright (c) 2016, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Retrieve all forms.
 *
 * @since	0.1
 * @param	arr		$args	Arguments. See $defaults / WP_Query.
 * @return	obj		WP_Query Object
 */
function kbs_get_forms()	{
	
	$defaults = array(
		'post_type'         => 'kbs_form',
		'post_status'       => 'any',
		'posts_per_page'	=> -1
	);
	
} // kbs_get_forms

/**
 * Retrieve a form.
 *
 * @since	0.1
 * @param	int		$form_id	Post ID.
 * @return	obj		WP_Post
 */
function kbs_get_form( $form_id )	{
	
	$form = new KBS_Form( $form_id );
	
	return apply_filters( 'kbs_get_form', $form, $form_id );
	
} // kbs_get_form

/**
 * Retrieve all form fields.
 *
 * @since	0.1
 * @param	int		$form_id	Post ID.
 * @param	arr		$args		Arguments. See $defaults / WP_Query.
 * @return	obj		WP_Query Object.
 */
function kbs_get_fields( $form_id )	{
	
	$form = new KBS_Form( $form_id );
	
	return $form->get_fields();
	
} // kbs_get_fields

/**
 * Retrieve a single form field.
 *
 * Also retrieves settings into $field->settings.
 *
 * @since	0.1
 * @param	int		$field_id	Post ID.
 * @return	obj		WP_Query Object.
 */
function kbs_get_field( $field_id )	{
	
	$field = get_post( $field_id );
	
	if ( ! $field )	{
		return false;
	}
	
	$field->settings = kbs_get_field_settings( $field->ID );
	
	return apply_filters( 'kbs_get_field', $field );
	
} // kbs_get_field

/**
 * Delete a form field.
 *
 * @since	0.1
 * @param	int		$field_id	Post ID.
 * @return	obj		WP_Query Object.
 */
function kbs_delete_field( $field_id )	{
	
	do_action( 'kbs_pre_delete_field', $field_id );
	
	$result = wp_delete_post( $field_id );	
	
	do_action( 'kbs_post_delete_field', $field_id, $result );
	
	return $result;

} // kbs_delete_field

/**
 * Returns the field type in readable format.
 *
 * @since	0.1
 * @param	str		$type	The type to return
 * @return	str		The field type in readable format.
 */
function kbs_get_field_type( $type )	{
	
	$field_types = kbs_get_field_types();
	
	return $field_types[ $type ];
	
} // kbs_get_field_type

/**
 * Returns the field settings.
 *
 * @since	0.1
 * @param	str		$field_id	The post ID.
 * @return	arr		The field settings.
 */
function kbs_get_field_settings( $field_id )	{
	
	$field_settings = get_post_meta( $field_id, '_kbs_field_settings', true );
		
	return apply_filters( 'kbs_field_settings', $field_settings, $field_id );
	
} // kbs_get_field_settings

/**
 * Returns all possible form fields types.
 *
 * @since	0.1
 * @param
 * @return	arr
 */
function kbs_get_field_types()	{
	
	$field_types = array(
		'checkbox'                    => __( 'Checkbox', 'kb-support' ),
		'checkbox_list'               => __( 'Checkbox List', 'kb-support' ),
		'date_field'                  => __( 'Date Field', 'kb-support' ),
		'email'                       => __( 'Email Field', 'kb-support' ),
		'file_upload'                 => __( 'File Upload', 'kb-support' ),
		'hidden'                      => __( 'Hidden Field', 'kb-support' ),
		'kb_category_dropdown'        => sprintf( __( '%s Select List', 'kb-support' ), kbs_get_kb_label_singular() ),
		'number'                      => __( 'Number Field', 'kb-support' ),
		'radio'                       => __( 'Radio Buttons', 'kb-support' ),
		'recaptcha'                   => __( 'Google reCaptcha', 'kb-support' ),
		'select'                      => __( 'Select List', 'kb-support' ),
		'text'                        => __( 'Text Field', 'kb-support' ),
		'textarea'                    => __( 'Textarea', 'kb-support' ),
		'ticket_category_dropdown'    => sprintf( __( '%s Select List', 'kb-support' ), kbs_get_ticket_label_singular() ),
		'url'                         => __( 'URL Field', 'kb-support' ),		
	);
	
	if ( kbs_get_option( 'file_uploads', 0 ) < 1 )	{
		unset( $field_types['file_uploads'] );
	}
	
	if ( ! kbs_get_option( 'recaptcha_site_key', false ) )	{
		unset( $field_types['recaptcha'] );
	}
	
	/**
	 * Filter the field types to allow for custom fields to be added.
	 *
	 * @since	0.1
	 * @param	$field_types
	 */
	$field_types = apply_filters( 'kbs_field_types', $field_types );
	
	asort( $field_types );
	
	return $field_types;
	
} // kbs_get_field_types

/**
 * Output the icons for the field settings.
 *
 * @since	0.1
 * @param	arr		$settings	The field ID.
 * @return	str
 */
function kbs_display_field_setting_icons( $field_id )	{

	$settings = kbs_get_field_settings( $field_id );
	$output   = array();
	
	if ( $settings )	{
		if ( ! empty( $settings['hide_label'] ) )	{
			$output[] = '<i title="' . __( 'Label Hidden', 'kb-support' ) . '" class="fa fa-tag" aria-hidden="true"></i>';
		} else	{
			$output[] = '&nbsp;&nbsp;&nbsp;';
		}

		if ( ! empty( $settings['required'] ) )	{
			$output[] = '<i title="' . __( 'Required Field', 'kb-support' ) . '" class="fa fa-asterisk" aria-hidden="true"></i>';
		} else	{
			$output[] = '&nbsp;&nbsp;&nbsp;';
		}
		
		if ( ! empty( $settings['placeholder'] ) )	{
			$output[] = '<i title="' . sprintf( __( 'Placeholder: %s', 'kb-support' ), stripslashes( $settings['placeholder'] ) ) . '" class="fa fa-info-circle" aria-hidden="true"></i>';
		} else	{
			$output[] = '&nbsp;&nbsp;&nbsp;';
		}
	}

	return implode( "\t", $output );

} // kbs_display_field_setting_icons

/**
 * Display Form
 *
 * @since	0.1
 * @global	$kbs_form
 * @param	str			$form_id	Form post ID
 * @return	str			Form
 */
function kbs_display_form( $form_id = 0 ) {
	global $kbs_form;

	if ( empty( $form_id ) ) {
		return __( 'Submission form not found', 'kb-support' );
	}

	$kbs_form = new KBS_Form( $form_id );

	if ( ! $form_id ) {
		return __( 'Submission form not found', 'kb-support' );
	}

	ob_start();

	kbs_get_template_part( 'shortcode', apply_filters( 'kbs_form_template', 'form' ) );

	return apply_filters( 'kbs_submit_form', ob_get_clean() );
} // kbs_display_form

/**
 * Display a form text input field.
 *
 * This function is also the callback for email and URL fields.
 *
 * @since	0.1
 * @param	obj			$field		Field post object
 * @param	arr			$settings	Field settings
 * @return	str			$type input field
 */
function kbs_display_form_text_field( $field, $settings )	{
	
	$type        = ! empty( $settings['type'] )        ? $settings['type']                                             : 'text';
	$placeholder = ! empty( $settings['placeholder'] ) ? ' placeholder="' . esc_attr( $settings['placeholder'] ) . '"' : '';
	$class       = ! empty( $settings['input_class'] ) ? esc_attr( $settings['input_class'] )                          : '';
	$required    = ! empty( $settings['required'] )    ? ' required'                                                   : '';

	if ( $type == 'date_field' )	{
		if( empty( $class ) ) {
			$class = 'kbs_datepicker';
		} elseif( ! strpos( $class, 'kbs_datepicker' ) ) {
			$class .= ' kbs_datepicker';
		}
		$type = 'text';
		
		wp_enqueue_script( 'jquery-ui-datepicker' );
		wp_register_style( 'jquery-ui-css', KBS_PLUGIN_URL . 'assets/css/jquery-ui-fresh.min.css' );
		wp_enqueue_style( 'jquery-ui-css' );
	}

	$class = implode( ' ', array_map( 'sanitize_html_class', explode( ' ', $class ) ) );

	$output = sprintf( '<input type="%1$s" name="%2$s" id="%2$s"%3$s%4$s%5$s />',
		esc_attr( $type ),
		esc_attr( $field->post_name ),
		! empty( $class ) ? ' class="' . $class . '"' : '',
		$placeholder,
		$required
	);
	
	$output = apply_filters( 'kbs_display_form_' . $type . '_field', $output, $field, $settings );
	
	echo $output;
	
} // kbs_display_form_text_field
add_action( 'kbs_form_display_text_field', 'kbs_display_form_text_field', 10, 2 );
add_action( 'kbs_form_display_date_field_field', 'kbs_display_form_text_field', 10, 2 );
add_action( 'kbs_form_display_email_field', 'kbs_display_form_text_field', 10, 2 );
add_action( 'kbs_form_display_number_field', 'kbs_display_form_text_field', 10, 2 );
add_action( 'kbs_form_display_url_field', 'kbs_display_form_text_field', 10, 2 );

/**
 * Display a form textrea field
 *
 * @since	0.1
 * @param	obj			$field		Field post object
 * @param	arr			$settings	Field settings
 * @return	str			Field
 */
function kbs_display_form_textarea_field( $field, $settings )	{
	
	$placeholder = ! empty( $settings['placeholder'] ) ? ' placeholder="' . esc_attr( $settings['placeholder'] ) . '"' : '';
	$class       = ! empty( $settings['input_class'] ) ? ' class="' . esc_attr( $settings['input_class'] ) . '"'       : '';
	$required    = ! empty( $settings['required'] )    ? ' ' . ' required'                                             : '';
	
	$output = sprintf( '<textarea name="%1$s" id="%1$s"%2$s%3$s%4$s></textarea>',
		esc_attr( $field->post_name ),
		$class,
		$placeholder,
		$required
	);
	
	$output = apply_filters( 'kbs_display_form_textarea_field', $output, $field, $settings );
	
	echo $output;
	
} // kbs_display_form_textarea_field
add_action( 'kbs_form_display_textarea_field', 'kbs_display_form_textarea_field', 10, 2 );

/**
 * Display a form select field
 *
 * @since	0.1
 * @param	obj			$field		Field post object
 * @param	arr			$settings	Field settings
 * @return	str			Field
 */
function kbs_display_form_select_field( $field, $settings )	{
	
	$class       = ! empty( $settings['input_class'] )     ? esc_attr( $settings['input_class'] ) : '';
	$multiple    = ! empty( $settings['select_multiple'] ) ? ' ' . ' multiple'                    : false;
	$required    = ! empty( $settings['required'] )        ? ' ' . ' required'                    : '';

	if ( ! empty( $settings['chosen'] ) )	{
		$class .= 'kbs-select-chosen';
	}

	$class   = implode( ' ', array_map( 'sanitize_html_class', explode( ' ', $class ) ) );
	$options = $settings['select_options'];

	$output = sprintf( '<select name="%1$s" id="%1$s"%3$s%4$s>',
		esc_attr( $field->post_name ),
		' class="' . $class . '"',
		$multiple,
		$required
	);

	if ( ! empty( $options ) )	{
		foreach( $options as $option )	{
			$output .= '<option value="' . esc_attr( $option ) . '">' . esc_html( $option ) . '</option>';
		}
	}

	$output .= '</select>';
	
	$output = apply_filters( 'kbs_display_form_select_field', $output, $field, $settings );
	
	echo $output;
	
} // kbs_display_form_textarea_field
add_action( 'kbs_form_display_select_field', 'kbs_display_form_select_field', 10, 2 );

/**
 * Display a form checkbox field
 *
 * @since	0.1
 * @param	obj			$field		Field post object
 * @param	arr			$settings	Field settings
 * @return	str			Field
 */
function kbs_display_form_checkbox_field( $field, $settings )	{
	
	$class       = ! empty( $settings['input_class'] ) ? ' class="' . esc_attr( $settings['input_class'] ) . '"' : '';
	$checked     = ! empty( $settings['selected'] )        ? ' ' . ' checked'                                    : '';
	$required    = ! empty( $settings['required'] )        ? ' ' . ' required'                                   : '';

	$output = sprintf( '<input type="checkbox" name="%1$s" id="%1$s"%2$s%3$s%4$s />',
		esc_attr( $field->post_name ),
		$class,
		$checked,
		$required
	);
	
	$output = apply_filters( 'kbs_display_form_checkbox_field', $output, $field, $settings );
	
	echo $output;
	
} // kbs_display_form_textarea_field
add_action( 'kbs_form_display_checkbox_field', 'kbs_display_form_checkbox_field', 10, 2 );

/**
 * Display a form checkbox list field
 *
 * @since	0.1
 * @param	obj			$field		Field post object
 * @param	arr			$settings	Field settings
 * @return	str			Field
 */
function kbs_display_form_checkbox_list_field( $field, $settings )	{
	
	$class       = ! empty( $settings['input_class'] ) ? ' class="' . esc_attr( $settings['input_class'] ) . '"' : '';

	$options = $settings['select_options'];

	if ( empty ( $options ) )	{
		return;
	}

	foreach ( $options as $option )	{
		$output[] = sprintf( '<input type="checkbox" name="%1$s[]" id="%2$s"%3$s value="%4$s" /> %5$s',
			esc_attr( $field->post_name ),
			esc_attr( kbs_sanitize_key( $option ) ),
			$class,
			esc_attr( $option ),
			'<label for="' . esc_attr( kbs_sanitize_key( $option ) ) . '">' . esc_attr( $option ) . '</label>'
		);
		
	}

	$output = apply_filters( 'kbs_display_form_checkbox_field', $output, $field, $settings );
	
	echo implode( '<br />', $output );

} // kbs_display_form_textarea_field
add_action( 'kbs_form_display_checkbox_list_field', 'kbs_display_form_checkbox_list_field', 10, 2 );

/**
 * Display a form recaptcha field
 *
 * @since	0.1
 * @param	obj			$field		Field post object
 * @param	arr			$settings	Field settings
 * @return	str			Field
 */
function kbs_display_form_recaptcha_field( $field, $settings )	{

	$site_key = kbs_get_option( 'recaptcha_site_key', false );
	
	if ( ! $site_key )	{
		return;
	}

	wp_register_script( 'google-recaptcha', '//www.google.com/recaptcha/api.js"', '', KBS_VERSION, true );
	wp_enqueue_script( 'google-recaptcha' );

	$output  = sprintf( '<div class="g-recaptcha" data-sitekey="%1$s" data-theme="%2$s" data-type="%3$s" data-size="%4$s"></div>',
		$site_key,
		kbs_get_option( 'recaptcha_theme' ),
		kbs_get_option( 'recaptcha_type' ),
		kbs_get_option( 'recaptcha_size' )
	);
	$output .= sprintf( '<input type="hidden" name="%1$s" id="%1$s" value="" required />', esc_attr( $field->post_name ) );

	$output = apply_filters( 'kbs_display_form_recaptcha_field', $output, $field, $settings );
	
	echo $output;

} // kbs_display_form_textarea_field
add_action( 'kbs_form_display_recaptcha_field', 'kbs_display_form_recaptcha_field', 10, 2 );

/**
 * Display a form file upload field.
 *
 * @since	0.1
 * @param	obj			$field		Field post object
 * @param	arr			$settings	Field settings
 * @return	str			$type input field
 */
function kbs_display_form_file_upload_field( $field, $settings )	{
	
	$placeholder = ! empty( $settings['placeholder'] )  ? ' placeholder="' . esc_attr( $settings['placeholder'] ) . '"' : '';
	$class       = ! empty( $settings['input_class'] )  ? esc_attr( $settings['input_class'] )                          : '';
	$multiple    = kbs_get_option( 'file_uploads' ) > 1 ? ' multiple'                                                   : '';

	$class = implode( ' ', array_map( 'sanitize_html_class', explode( ' ', $class ) ) );

	$output = sprintf( '<input type="file" name="%1$s" id="%1$s"%2$s%3$s%4$s />',
		esc_attr( $field->post_name ),
		! empty( $class ) ? ' class="' . $class . '"' : '',
		$placeholder,
		$multiple
	);
	
	$output = apply_filters( 'kbs_display_form_file_upload_field', $output, $field, $settings );
	
	echo $output;
	
} // kbs_display_form_file_upload_field
add_action( 'kbs_form_display_file_upload_field', 'kbs_display_form_file_upload_field', 10, 2 );
