<?php
/**
 * Scripts
 *
 * @package     KBS
 * @subpackage  Shortcodes
 * @copyright   Copyright (c) 2016, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Login Shortcode
 *
 * Shows a login form allowing users to users to log in. This function simply
 * calls the kbs_login_form function to display the login form.
 *
 * @since	1.0
 * @param	att		$atts	Shortcode attributes
 * @uses	kbs_login_form()
 * @return	str
 */
function kbs_login_form_shortcode( $atts ) {
	extract( shortcode_atts( array(
			'redirect' => '',
		), $atts, 'kbs_login' )
	);
	return kbs_login_form( $redirect );
} // kbs_login_form_shortcode
add_shortcode( 'kbs_login', 'kbs_login_form_shortcode' );

/**
 * Register Shortcode
 *
 * Shows a registration form allowing users to users to register for the site.
 *
 * @since	1.0
 * @param	arr		$atts		Shortcode attributes
 * @uses	kbs_register_form()
 * @return	str
 */
function kbs_register_form_shortcode( $atts ) {
	extract( shortcode_atts( array(
			'redirect' => '',
		), $atts, 'kbs_register' )
	);
	return kbs_register_form( $redirect );
} // kbs_register_form_shortcode
add_shortcode( 'kbs_register', 'kbs_register_form_shortcode' );

/**
 * Ticket Form Shortcode
 *
 * Displays the ticket submission form
 *
 * @since	1.0
 * @param	arr		$atts		Shortcode attributes
 * @return	str
 */
function kbs_submit_form_shortcode( $atts ) {
	extract( shortcode_atts( array(
			'form' => 0,
		), $atts, 'kbs_submit' )
	);
	return kbs_display_form( $form );
} // kbs_submit_form_shortcode
add_shortcode( 'kbs_submit', 'kbs_submit_form_shortcode' );
