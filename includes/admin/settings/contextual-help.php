<?php
/**
 * Contextual Help
 *
 * @package     KBS
 * @subpackage  Admin/Settings
 * @copyright   Copyright (c) 2016, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Settings contextual help.
 *
 * @access      private
 * @since       0.1
 * @return      void
 */
function kbs_settings_contextual_help() {
	$screen = get_current_screen();

	if ( $screen->id != 'kbs_ticket_page_kbs-settings' )	{
		return;
	}

	$screen->set_help_sidebar(
		'<p><strong>' . sprintf( __( 'For more information:', 'kb-support' ) . '</strong></p>' .
		'<p>' . sprintf( __( 'Visit the <a href="%s">documentation</a> on the KB Support website.', 'kb-support' ), esc_url( 'http://kbsupport.com/' ) ) ) . '</p>' .
		'<p>' . sprintf(
					__( '<a href="%s">Post an issue</a> on <a href="%s">GitHub</a>. View <a href="%s">extensions</a> or <a href="%s">themes</a>.', 'kb-support' ),
					esc_url( 'https://github.com/KB-Support/kb-support/issues' ),
					esc_url( 'https://github.com/KB-Support/kb-support' ),
					esc_url( 'http://kbsupport.com/extensions/' ),
					esc_url( 'http://kbsupport.com/themes/' )
				) . '</p>'
	);

	$screen->add_help_tab( array(
		'id'	    => 'kbs-settings-general',
		'title'	    => __( 'General', 'kb-support' ),
		'content'	=> '<p>' . __( 'This screen provides the most basic settings for configuring your service desk.', 'kb-support' ) . '</p>'
	) );

	do_action( 'kbs_settings_contextual_help', $screen );
}
add_action( 'load-kbs_ticket_page_kbs-settings', 'kbs_settings_contextual_help' );
