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

	if ( ! kbs_user_can_submit() )	{
		ob_start();
		echo kbs_display_notice( 'need_login' );

		$register_login = kbs_get_option( 'show_register_form', 'none' );

		if ( 'both' == $register_login || 'login' == $register_login )	{
			echo kbs_login_form( kbs_get_current_page_url() );
		}

		if ( 'both' == $register_login || 'registration' == $register_login )	{
			echo kbs_register_form( kbs_get_current_page_url() );
		}

		return ob_get_clean();
	}

	extract( shortcode_atts( array(
		'form' => 0,
		), $atts, 'kbs_submit' )
	);

	return kbs_display_form( $form );
} // kbs_submit_form_shortcode
add_shortcode( 'kbs_submit', 'kbs_submit_form_shortcode' );

/**
 * View Tickets Shortcode.
 *
 * Displays a customers ticket.
 *
 * @since	1.0
 * @param	arr		$atts		Shortcode attributes
 * @return	str
 */
function kbs_tickets_shortcode( $atts ) {
	ob_start();
	if ( ! isset( $_GET['ticket'] ) )	{
		echo kbs_display_notice( 'no_ticket' );
		return ob_get_clean();
	}

	kbs_get_template_part( 'view', 'ticket' );
	return ob_get_clean();
} // kbs_tickets_shortcode
add_shortcode( 'kbs_tickets', 'kbs_tickets_shortcode' );
