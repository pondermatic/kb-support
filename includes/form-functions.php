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
