<?php
	defined( 'ABSPATH' ) or die( "Direct access to this page is disabled!!!" );
	
/**
 * Manage kbs-tickets posts.
 * 
 * @since		0.1
 * @package		KBS
 * @subpackage	Posts
 */

/**
 * Define the columns that should be displayed for the KBS ticket post lists screen
 *
 * @since	0.1
 * @param	arr		$columns	An array of column name ⇒ label. The label is shown as the column header.
 * @return	arr		$columns	Filtered array of column name => label to be shown as the column header.
 */
function kbs_set_kbs_ticket_post_columns( $columns ) {
    
	$columns = array(
        'cb'               => '<input type="checkbox" />',
        'id'               => '#',
		'date'             => __( 'Date', 'kb-support' ),
		'title'            => __( 'Title', 'kb-support' ),
        'author'           => __( 'Customer', 'kb-support' ),
		'status'           => __( 'Status', 'kb-support' ),
		'categories'       => __( 'Category', 'kb-support' ),
        'agent'            => __( 'Agent', 'kb-support' ),
		'priority'         => __( 'Priority', 'kb-support' )
    );
	
	return apply_filters( 'kbs_ticket_post_columns', $columns );
	
}
add_filter( 'manage_kbs-ticket_posts_columns' , 'kbs_set_kbs_ticket_post_columns' );

/**
 * Define the data to be displayed within the KBS ticket post custom columns.
 *
 * @since	0.1
 * @param	str		$column_name	The name of the current column for which data should be displayed.
 * @param	int		$post_id		The ID of the current post for which data is being displayed.
 * @return	str
 */
function kbs_set_kbs_ticket_column_data( $column_name, $post_id ) {

	switch ( $column_name ) {
		case 'id':
			$output = '<a href="' . get_edit_post_link( $post_id ) . '">#' . $post_id . '</a>';
			$output .= '<br />';
			$output .= $post->post_status;
			echo apply_filters( 'kb_tickets_post_column_id', $output, $post_id );
			break;

		case 'agent':
			echo $output = '';
			echo apply_filters( 'kb_tickets_post_column_agent', $output, $post_id );
			break;

		case 'priority':
			echo $output = '';
			echo apply_filters( 'kb_tickets_post_column_priority', $output, $post_id );
			break;
	}

}
add_action( 'manage_kbs-ticket_posts_custom_column' , 'kbs_set_kbs_ticket_column_data', 10, 2 );

/**
 * Save the KBS Ticket custom posts
 *
 * @since	1.3
 * @param	int		$post_id		The ID of the post being saved.
 * @param	obj		$post			The WP_Post object of the post being saved.
 * @param	bool	$update			Whether an existing post if being updated or not.
 *
 * @return	void
 */
function kbs_ticket_post_save( $post_id, $post, $update )	{	

	// Remove the save post action to avoid loops
	remove_action( 'save_post_kbs-ticket', 'kbs_ticket_post_save', 10, 3 );

	// Fire the before save action but only if this is not a new ticket creation (i.e $post->post_status == 'draft')
	if( $update === true )	{
		do_action( 'kbs_ticket_before_save', $post_id, $post, $update );
	}

	// Fire the after save action
	do_action( 'kbs_ticket_after_save', $post_id, $post, $update );

	// Re-add the save post action
	add_action( 'save_post_kbs-ticket', 'kbs_ticket_post_save', 10, 3 );
}
add_action( 'save_post_kbs-ticket', 'kbs_ticket_post_save', 10, 3 );