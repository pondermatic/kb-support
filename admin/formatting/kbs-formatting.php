<?php
	defined( 'ABSPATH' ) or die( "Direct access to this page is disabled!!!" );
	
/**
 * Custom formatting for KBS 
 * 
 * 
 *
 *
 */
/**
 * Remove the Add Media button from the TinyMCE editor for KBS Tickets
 *
 * @called	hook	tiny_mce_before_init				WP filter before TinyMCE initialises
 *
 * @hooks
 *
 * @param
 *
 * @return
 */
function kbs_ticket_no_media()	{
	if( 'kbs_tickets' == get_post_type() )
		remove_action( 'media_buttons', 'media_buttons' );
}
add_action( 'admin_head', 'kbs_ticket_no_media' );
?>