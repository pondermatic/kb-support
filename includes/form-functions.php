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
		'hidden'                      => __( 'Hidden Field', 'kb-support' ),
		'kb_category_dropdown'        => sprintf( __( '%s Select List', 'kb-support' ), kbs_get_kb_label_singular() ),
		'number'                      => __( 'Number Field', 'kb-support' ),
		'radio'                       => __( 'Radio Buttons', 'kb-support' ),
		'recaptcha'                   => __( 'Google reCaptcha', 'kb-support' ),
		'select'                      => __( 'Select List', 'kb-support' ),
		'text'                        => __( 'Text Field', 'kb-support' ),
		'textarea'                    => __( 'Textarea', 'kb-support' ),
		'time'                        => __( 'Time Field', 'kb-support' ),
		'ticket_category_dropdown'    => sprintf( __( '%s Select List', 'kb-support' ), kbs_get_ticket_label_singular() ),
		'url'                         => __( 'URL Field', 'kb-support' ),		
	);
	
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

	$settings = get_post_meta( $field_id, '_kbs_form_settings', true );
	$output   = array();
	
	if ( $settings )	{
		if ( ! empty( $settings['type'] ) )	{
			$output[] = '<i title="' . __( 'Required Field', 'kb-support' ) . '" class="fa fa-asterisk" aria-hidden="true"></i>';
		}
		if ( ! empty( $settings['required'] ) )	{
			$output[] = '<i title="' . __( 'Required Field', 'kb-support' ) . '" class="fa fa-asterisk" aria-hidden="true"></i>';
		}
	}
	
} // kbs_display_field_setting_icons
