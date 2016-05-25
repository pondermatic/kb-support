<?php
/**
 * Admin Notices
 *
 * @package     KBS
 * @subpackage  Admin/Notices
 * @copyright   Copyright (c) 2016, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Admin Messages
 *
 * @since	0.1
 * @global	$kbs_options Array of all the KBS Options
 * @return	void
 */
function kbs_admin_messages() {
	global $kbs_options;

	settings_errors( 'kbs-notices' );
}
add_action( 'admin_notices', 'kbs_admin_messages' );

/**
 * Admin Add-ons Notices
 *
 * @since	0.1
 * @return	void
*/
function kbs_admin_addons_notices() {
	add_settings_error( 'kbs-notices', 'kbs-addons-feed-error', __( 'There seems to be an issue with the server. Please try again in a few minutes.', 'kb-support' ), 'error' );
	settings_errors( 'kbs-notices' );
} // kbs_admin_addons_notices

/**
 * Dismisses admin notices when Dismiss links are clicked
 *
 * @since	0.1
 * @return	void
*/
function kbs_dismiss_notices() {

	if( ! is_user_logged_in() ) {
		return;
	}

	$notice = isset( $_GET['kbs_notice'] ) ? $_GET['kbs_notice'] : false;

	if( ! $notice )	{
		return;
	}

	update_user_meta( get_current_user_id(), '_kbs_' . $notice . '_dismissed', 1 );

	wp_redirect( remove_query_arg( array( 'kbs_action', 'kbs_notice' ) ) ); exit;

} // kbs_dismiss_notices
add_action( 'kbs_dismiss_notices', 'kbs_dismiss_notices' );
