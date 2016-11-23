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
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Checks if file uploads are enabled
 *
 * @since	1.0
 * @return	bool	$ret	True if guest checkout is enabled, false otherwise
 */
function kbs_file_uploads_are_enabled() {
	$return = false;

	if ( kbs_get_max_file_uploads() > 0 )	{
		$return = true;
	}
	
	return (bool) apply_filters( 'kbs_file_uploads', $return );
} // kbs_file_uploads_are_enabled

/**
 * Retrieve the maximum number of files which can be uploaded.
 *
 * @since	1.0
 * @return	int		The maximum number of files that can be uploaded
 */
function kbs_get_max_file_uploads() {
	return (int) kbs_get_option( 'file_uploads', 0 );
} // kbs_get_max_file_uploads

/**
 * Set Upload Directory
 *
 * Sets the upload dir to kbs.
 *
 * @since	1.0
 * @return	arr		Upload directory information.
 */
function kbs_set_upload_dir( $upload ) {

	// Override the year / month being based on the post publication date, if year/month organization is enabled
	if ( get_option( 'uploads_use_yearmonth_folders' ) )	{

		$time             = current_time( 'mysql' );
		$y                = substr( $time, 0, 4 );
		$m                = substr( $time, 5, 2 );
		$upload['subdir'] = "/$y/$m";

	}

	$upload['subdir'] = '/kbs' . $upload['subdir'];
	$upload['path']   = $upload['basedir'] . $upload['subdir'];
	$upload['url']    = $upload['baseurl'] . $upload['subdir'];
	
	return apply_filters( 'kbs_set_upload_dir', $upload );

} // kbs_set_upload_dir

/**
 * Retrieve the current upload DIR for tickets.
 *
 * @since	1.0
 * @param	$key	The array key to return or false for the entire array.
 * @return	str
 */
function kbs_get_upload_dir( $key = false )	{
	add_filter( 'upload_dir', 'kbs_set_upload_dir' );

	$upload_dir = wp_upload_dir();

	if ( $key && isset( $upload_dir[ $key ] ) )	{
		$dir = $upload_dir[ $key ];
	} else	{
		$dir = $upload_dir;
	}

	return $dir;
} // kbs_get_upload_dir

/**
 * Change Tickets Upload Directory.
 *
 * This function works by hooking on the WordPress Media Uploader
 * and moving the uploading files that are used for KBS to a kbs
 * directory under wp-content/uploads/ therefore,
 * the new directory is wp-content/uploads/kbs/{year}/{month}.
 *
 * @since	1.0
 * @global	$pagenow
 * @return	void
 */
function kbs_change_tickets_upload_dir() {

	global $pagenow;

	if ( ! empty( $_REQUEST['post_id'] ) && ( 'async-upload.php' == $pagenow || 'media-upload.php' == $pagenow ) )	{

		if ( 'kbs_ticket' == get_post_type( $_REQUEST['post_id'] ) ) {
			add_filter( 'upload_dir', 'kbs_set_upload_dir' );
		}

	}

} // kbs_change_tickets_upload_dir
add_action( 'admin_init', 'kbs_change_tickets_upload_dir', 999 );

/**
 * Sets the enctype for file upload forms.
 *
 * @since	1.0
 * @return	str
 */
function kbs_maybe_set_enctype() {
	if ( kbs_file_uploads_are_enabled() )	{
		$output = ' enctype="multipart/form-data"';
		
		echo apply_filters( 'kbs_maybe_set_enctype', $output );
	}
} // kbs_maybe_set_enctype

/**
 * Whether or not there are files to upload.
 *
 * @since	1.0
 * @param	str		$name	The file name
 * @return	bool	True if there are files, or false.
 */
function kbs_has_files_to_upload( $name )	{
	return ( ! empty( $_FILES ) ) && isset( $_FILES[ $name ] );
} // kbs_has_files_to_upload

/**
 * Attach files to a ticket or reply.
 *
 * @since	1.0
 * @param	arr	$attachment		$_FILES
 * @param	int	$ticket_id		The ticket/reply ID
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
 * Attach files to a ticket reply.
 *
 * @since	1.0
 * @global	$_FILES
 * @param	int		$reply_id	The ticket reply ID to which were attaching.
 * @return	void
 */
function kbs_attach_files_to_reply( $reply_id )	{
	$attachments = $_FILES['kbs_files'];
	$attachments = ! is_array( $attachments ) ? array( $attachments ) : $attachments;

	foreach( $attachments['name'] as $key => $value )	{
		if ( $attachments['name'][ $key ] )	{
			$attachment = array(
				'name'     => $attachments['name'][ $key ],
				'type'     => $attachments['type'][ $key ],
				'tmp_name' => $attachments['tmp_name'][ $key ],
				'error'    => $attachments['error'][ $key ],
				'size'     => $attachments['size'][ $key ]
			);

			$_FILES = array( 'kbs_reply_attachments' => $attachment );

			foreach( $_FILES as $attachment => $array )	{
				$file_id = kbs_attach_file_to_ticket( $attachment, $reply_id );
			}
		}
	}
} // kbs_attach_files_to_reply

/**
 * Determine if the ticket (or reply) has files attached.
 *
 * @since	1.0
 * @param	int		$ticket_id	The Ticket/Reply ID
 * @return	mixed	Object of attachment posts if they exist, otherwise false.
 */
function kbs_ticket_has_files( $ticket_id )	{

	$files = get_posts(
		array(
			'post_type'      => 'attachment',
			'posts_per_page' => -1,
			'post_parent'    => $ticket_id,
			'post_status'    => array( 'inherit' )
		)
	);
	
	return apply_filters( 'kbs_ticket_has_files', $files, $ticket_id );
	
} // kbs_ticket_has_files
