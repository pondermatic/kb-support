<?php
	defined( 'ABSPATH' ) or die( "Direct access to this page is disabled!!!" );
	
/**
 * Handle function hooks for the KBS tickets custom posts types.
 * - Determines columns and column data
 * - Manages post saves
 *
 *
 */
/**
 * Define the columns that should be displayed for the KBS ticket post lists screen
 *
 * @called	filter	manage_${post_type}_posts_columns	WP filter for custom post columns
 *
 * @hooks	filter	kbs_ticket_post_columns				Allows columns to be progmatically changed
 *
 * @param	arr		$columns	An array of column name ⇒ label. The label is shown as the column header.
 *
 * @return	arr		$columns	Filtered array of column name => label to be shown as the column header.
 */
function set_kbs_ticket_post_columns( $columns ) {
    return apply_filters( 'kbs_ticket_post_columns', array(
        'cb'               => '<input type="checkbox" />',
        'id'               => '#',
		'date'             => __( 'Date', 'mobile-dj-manager' ),
		'title'            => __( 'Title' ),
        'author'           => __( 'Customer', 'mobile-dj-manager' ),
		'categories'       => __( 'Category' ),
        'agent'            => __( 'Agent', 'mobile-dj-manager' ),
		'priority'         => __( 'Priority', 'mobile-dj-manager' )
    ) );
}
add_filter( 'manage_kbs_tickets_posts_columns' , 'set_kbs_ticket_post_columns' );

/**
 * Define the data to be displayed within the KBS ticket post custom columns
 *
 * @called	hook	manage_posts_custom_column			WP filter for custom post column data
 *
 * @hooks	filter	kb_tickets_post_column_id			Allows id column data to be manipulated
 *			filter	kb_tickets_post_column_agent		Allows agent column data to be manipulated
 *			filter	kb_tickets_post_column_id			Allows priority column data to be manipulated
 *
 * @param	str		$column_name	The name of the current column for which data should be displayed.
 *			int		$post_id		The ID of the current post for which data is being displayed.
 *
 * @return							This function should echo the required output for the column		
 */
function set_kb_tickets_column_data( $column_name, $post_id ) {
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
add_action( 'manage_posts_custom_column' , 'set_kb_tickets_column_data', 10, 2 );

/**
 * Save the KBS Ticket custom posts
 *
 * @called	hook	save_post_{$post->post_type}		WP filter for custom post saves
 *
 * @hooks	action	kb_tickets_before_save				Allows functions to be executed before the KBS Tickets save
 *			action	kb_tickets_after_save				Allows functions to be executed after the KBS Tickets save
 *
 * @param	int		$post_id		The ID of the post being saved.
 *			obj		$post			The WP_Post object of the post being saved.
 *			bool	$update			Whether an existing post if being updated or not
 *
 * @return	void
 */
function kbs_ticket_post_save( $post_id, $post, $update )	{	
	// Remove the save post action to avoid loops
	remove_action( 'save_post_kbs_tickets', 'kbs_ticket_post_save', 10, 3 );
	
	// Fire the before save action but only if this is not a new ticket creation (i.e $post->post_status == 'draft')
	if( $update === true )
		do_action( 'kb_tickets_before_save', $post_id, $post, $update );
	
	// Fire the after save action
	do_action( 'kb_tickets_after_save', $post_id, $post, $update );
	
	// Re-add the save post action
	add_action( 'save_post_kbs_tickets', 'kbs_ticket_post_save', 10, 3 );
}
add_action( 'save_post_kbs_tickets', 'kbs_ticket_post_save', 10, 3 );
?>