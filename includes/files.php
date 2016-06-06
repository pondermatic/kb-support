<?php
/**
 * File Functions
 *
 * @package     KBS
 * @subpackage  Functions
 * @copyright   Copyright (c) 2016, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Attach files to a ticket.
 *
 * @since	1.0
 * @param	arr	$attachment	$_FILES
 * @param	int	$ticket_id		The ticket ID
 * @return	int	The attachment ID.
 */
function kbs_attach_file_to_ticket( $attachment, $ticket_id )	{

	if ( ! kbs_file_uploads_are_enabled() )	{
		return false;
	}

	add_filter( 'upload_dir', 'kbs_set_upload_dir' );

	if ( $_FILES[ $attachment ]['error'] !== UPLOAD_ERR_OK )	{
		return false;
	}
 
	require_once ABSPATH . 'wp-admin/includes/image.php';
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
 
	$attach_id = media_handle_upload( $attachment, $ticket_id );
 
	return $attach_id;

} // kbs_attach_file_to_ticket

/**
 * Determine if the ticket has files attached.
 *
 * @since	1.0
 * @param	int		$ticket_id	The Ticket ID
 * @return	mixed	Object of attachment posts if they exist, otherwise false.
 */
function kbs_ticket_has_files( $ticket_id )	{

	$files = get_posts(
		array(
			'post_type'      => 'attachment',
			'posts_per_page' => -1,
			'post_parent'    => $ticket_id
		)
	);
	
	return apply_filters( 'kbs_ticket_has_files', $files, $ticket_id );
	
} // kbs_ticket_has_files
