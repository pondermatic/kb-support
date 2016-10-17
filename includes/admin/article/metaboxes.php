<?php	
/**
 * Manage article post metaboxes.
 * 
 * @since		1.0
 * @package		KBS
 * @subpackage	Functions/Metaboxes
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Returns default KBS Article meta fields.
 *
 * @since	1.0
 * @return	arr		$fields		Array of fields.
 */
function kbs_article_metabox_fields() {

	$fields = array(
			'_kbs_article_restricted'
		);

	return apply_filters( 'kbs_article_metabox_fields_save', $fields );

} // kbs_article_metabox_fields

/**
 * Define and add the metaboxes for the article post type.
 *
 * @since	1.0
 * @return	void
 */
function kbs_article_add_meta_boxes( $post )	{

	global $kbs_article_update;

	$kbs_article_update = false;
	
	if ( 'draft' != $post->post_status && 'auto-draft' != $post->post_status )	{
		$kbs_article_update = true;
	}

	add_meta_box(
		'kbs-article-metabox-options',
		__( 'Options', 'kb-support' ),
		'kbs_article_metabox_options_callback',
		'article',
		'side',
		'',
		array()
	);
	
} // kbs_article_add_meta_boxes
add_action( 'add_meta_boxes_article', 'kbs_article_add_meta_boxes' );

/**
 * The callback function for the save metabox.
 *
 * @since	1.0
 * @global	obj		$post				WP_Post object
 * @global	bool	$kbs_ticket_update	True if this article is being updated, false if new
 * @return	void
 */
function kbs_article_metabox_options_callback()	{
	global $post, $kbs_article_update;

	wp_nonce_field( 'kbs_article_meta_save', 'kbs_article_meta_box_nonce' );

	/*
	 * Output the items for the options metabox
	 * @since	1.0
	 * @param	int	$post_id	The KB post ID
	 */
	do_action( 'kbs_article_options_fields', $post->ID );
} // kbs_article_metabox_options_callback

/**
 * Display the KB options metabox fields.
 *
 * @since	1.0
 * @global	bool	$kbs_ticket_update	True if this ticket is being updated, false if new.
 * @param	int		$post_id			The KB post ID.
 * @return	str
 */
function kbs_article_metabox_options_fields( $post_id )	{
	global $kbs_article_update;

	if ( $kbs_article_update )	{
		$logged_in_only = get_post_meta( $post_id, '_kbs_article_restricted', true );
	} else	{	
		$logged_in_only = kbs_get_option( 'article_restricted', false );
	}

	?>
	<div id="kbs-kn-options">
    	<p><?php echo KBS()->html->checkbox( array(
			'name'    => '_kbs_article_restricted',
			'current' => $logged_in_only
		) ); ?> <label for="_kbs_article_restricted"></label><?php _e( 'Restrict access?', 'kb-support' ); ?></label></p>
    </div>

    <?php
} // kbs_article_metabox_options_fields
add_action( 'kbs_article_options_fields', 'kbs_article_metabox_options_fields', 10, 1 );
