<?php	
/**
 * Manage kbs_kb post metaboxes.
 * 
 * @since		1.0
 * @package		KBS
 * @subpackage	Functions/Metaboxes
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Returns default KBS KB meta fields.
 *
 * @since	1.0
 * @return	arr		$fields		Array of fields.
 */
function kbs_kb_metabox_fields() {

	$fields = array(
			'_kbs_kb_logged_in_only'
		);

	return apply_filters( 'kbs_kb_metabox_fields_save', $fields );

} // kbs_kb_metabox_fields

/**
 * Define and add the metaboxes for the kbs_ticket post type.
 *
 * @since	1.0
 * @param
 * @return
 */
function kbs_kb_add_meta_boxes( $post )	{

	global $kbs_kb_update;

	$kbs_kb_update = false;
	
	if ( 'draft' != $post->post_status && 'auto-draft' != $post->post_status )	{
		$kbs_kb_update = true;
	}

	add_meta_box(
		'kbs-kb-metabox-options',
		__( 'Options', 'kb-support' ),
		'kbs_kb_metabox_options_callback',
		'kbs_kb',
		'side',
		'',
		array()
	);
	
} // kbs_kb_add_meta_boxes
add_action( 'add_meta_boxes_kbs_kb', 'kbs_kb_add_meta_boxes' );

/**
 * The callback function for the save metabox.
 *
 * @since	1.0
 * @global	obj		$post				WP_Post object
 * @global	bool	$kbs_ticket_update	True if this article is being updated, false if new
 * @return	void
 */
function kbs_kb_metabox_options_callback()	{
	global $post, $kbs_kb_update;

	wp_nonce_field( 'kbs_kb_meta_save', 'kbs_kb_meta_box_nonce' );

	/*
	 * Output the items for the options metabox
	 * @since	1.0
	 * @param	int	$post_id	The KB post ID
	 */
	do_action( 'kbs_kb_options_fields', $post->ID );
} // kbs_kb_metabox_options_callback

/**
 * Display the KB options metabox fields.
 *
 * @since	1.0
 * @global	bool	$kbs_ticket_update	True if this ticket is being updated, false if new.
 * @param	int		$post_id			The KB post ID.
 * @return	str
 */
function kbs_kb_metabox_options_fields( $post_id )	{
	global $kbs_kb_update;

	if ( $kbs_kb_update )	{
		$logged_in_only = get_post_meta( $post_id, '_kbs_kb_logged_in_only', true );
	} else	{	
		$logged_in_only = kbs_get_option( 'kb_logged_in', false );
	}

	?>
	<div id="kbs-kn-options">
    	<p><?php echo KBS()->html->checkbox( array(
			'name'    => '_kbs_kb_logged_in_only',
			'current' => $logged_in_only
		) ); ?> <label for="_kbs_kb_logged_in_only"></label><?php _e( 'Only logged in users can access?', 'kb-support' ); ?></label></p>
    </div>

    <?php
} // kbs_kb_metabox_options_fields
add_action( 'kbs_kb_options_fields', 'kbs_kb_metabox_options_fields', 10, 1 );
