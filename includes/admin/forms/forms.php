<?php
	defined( 'ABSPATH' ) or die( "Direct access to this page is disabled!!!" );
	
/**
 * Manage kbs-article posts.
 * 
 * @since		0.1
 * @package		KBS
 * @subpackage	Forms
 */

/**
 * Define the columns that should be displayed for the KBS Forms post lists screen
 *
 * @since	0.1
 * @param	arr		$columns	An array of column name ⇒ label. The label is shown as the column header.
 * @return	arr		$columns	Filtered array of column name => label to be shown as the column header.
 */
function kbs_set_kbs_form_post_columns( $columns ) {
    
	$columns = array(
        'cb'               => '<input type="checkbox" />',
		'title'            => __( 'Name', 'kb-support' ),
		'author'           => __( 'Author', 'kb-support' ),
    );
	
	return apply_filters( 'kbs_form_post_columns', $columns );
	
} // kbs_set_kbs_kb_post_columns
add_filter( 'manage_kbs_form_posts_columns' , 'kbs_set_kbs_form_post_columns' );

/**
 * Save the KBS Form custom posts
 *
 * @since	0.1
 * @param	int		$post_id		The ID of the post being saved.
 * @param	obj		$post			The WP_Post object of the post being saved.
 * @param	bool	$update			Whether an existing post if being updated or not.
 *
 * @return	void
 */
function kbs_form_post_save( $post_id, $post, $update )	{	

	// Remove the save post action to avoid loops
	remove_action( 'save_post_kbs_kb', 'kbs_kb_post_save', 10, 3 );

	// Fire the before save action but only if this is not a new article creation (i.e $post->post_status == 'draft')
	if( $update === true )	{
		do_action( 'kbs_kb_before_save', $post_id, $post, $update );
	}

	// Fire the after save action
	do_action( 'kbs_kb_after_save', $post_id, $post, $update );

	// Re-add the save post action
	add_action( 'save_post_kbs_kb', 'kbs_kb_post_save', 10, 3 );
}
add_action( 'save_post_kbs_form', 'kbs_form_post_save', 10, 3 );
