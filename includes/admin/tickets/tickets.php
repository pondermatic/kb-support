<?php
	defined( 'ABSPATH' ) or die( "Direct access to this page is disabled!!!" );
	
/**
 * Manage kbs-ticket posts.
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
		'dates'            => __( 'Date', 'kb-support' ),
		'title'            => __( 'Title', 'kb-support' ),
        'author'           => __( 'Customer', 'kb-support' ),
		'categories'       => __( 'Category', 'kb-support' ),
        'agent'            => __( 'Agent', 'kb-support' )
    );
	
	if ( kbs_get_option( 'sla_tracking' ) )	{
		$columns['sla'] = __( 'SLA Status', 'kbs-support' );
	}
	
	return apply_filters( 'kbs_ticket_post_columns', $columns );
	
}
add_filter( 'manage_kbs_ticket_posts_columns' , 'kbs_set_kbs_ticket_post_columns' );

/**
 * Define the data to be displayed within the KBS ticket post custom columns.
 *
 * @since	0.1
 * @param	str		$column_name	The name of the current column for which data should be displayed.
 * @param	int		$post_id		The ID of the current post for which data is being displayed.
 * @return	str
 */
function kbs_set_kbs_ticket_column_data( $column_name, $post_id ) {

	$kbs_ticket = new KBS_Ticket( $post_id );

	switch ( $column_name ) {
		case 'id':
			echo kb_tickets_post_column_id( $post_id, $kbs_ticket );
			break;

		case 'dates':
			echo kb_tickets_post_column_date( $post_id, $kbs_ticket );
			break;

		case 'agent':
			echo kb_tickets_post_column_agent( $post_id, $kbs_ticket );
			break;
			
		case 'sla':
			echo kb_tickets_post_column_sla( $post_id, $kbs_ticket );
			break;

		default:
			echo __( 'No callback found for post column', 'kb-support' );
			break;
	}

}
add_action( 'manage_kbs_ticket_posts_custom_column' , 'kbs_set_kbs_ticket_column_data', 10, 2 );

/**
 * Output the ID row.
 *
 * @since	1.0
 * @param	int	$ticket_id	The ticket ID
 * @param	obj	$kbs_ticket	The ticket WP_Post object
 * @return	str
 */
function kb_tickets_post_column_id( $ticket_id, $kbs_ticket )	{
	do_action( 'kb_pre_tickets_column_id', $kbs_ticket );

	$output = '<a href="' . get_edit_post_link( $ticket_id ) . '">#' . $ticket_id . '</a>';
	$output .= '<br />';
	$output .= get_post_status_object( $kbs_ticket->post_status )->label;

	do_action( 'kb_post_tickets_column_id', $kbs_ticket );

	return apply_filters( 'kb_tickets_post_column_id', $output, $ticket_id );
} // kb_tickets_post_column_id

/**
 * Output the Date row.
 *
 * @since	1.0
 * @param	int	$ticket_id	The ticket ID
 * @param	obj	$kbs_ticket	The ticket WP_Post object
 * @return	str
 */
function kb_tickets_post_column_date( $ticket_id, $kbs_ticket )	{
	do_action( 'kb_pre_tickets_column_date', $kbs_ticket );

	$output  = date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $kbs_ticket->post_date ) );
	$output .= '<br />';
	$output .= date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $kbs_ticket->post_modified ) );

	do_action( 'kb_post_tickets_column_date', $kbs_ticket );

	return apply_filters( 'kb_tickets_post_column_date', $output, $ticket_id );
} // kb_tickets_post_column_date

/**
 * Output the Agent row.
 *
 * @since	1.0
 * @param	int	$ticket_id	The ticket ID
 * @param	obj	$kbs_ticket	The ticket WP_Post object
 * @return	str
 */
function kb_tickets_post_column_agent( $ticket_id, $kbs_ticket )	{
	do_action( 'kb_pre_tickets_column_agent', $kbs_ticket );

	if ( ! empty( $kbs_ticket->agent ) )	{
		$output = sprintf( '<a href="%s">%s</a>',
			get_edit_user_link( $kbs_ticket->agent ),
			get_userdata( $kbs_ticket->agent )->display_name
		);
	} else	{
		$output = __( 'No Agent Assigned', 'kb-support' );
	}

	do_action( 'kb_post_tickets_column_agent', $kbs_ticket );

	return apply_filters( 'kb_tickets_post_column_agent', $output, $ticket_id );
} // kb_tickets_post_column_agent

/**
 * Output the SLA Status row.
 *
 * @since	1.0
 * @param	int	$ticket_id	The ticket ID
 * @param	obj	$kbs_ticket	The ticket WP_Post object
 * @return	str
 */
function kb_tickets_post_column_sla( $ticket_id, $kbs_ticket )	{
	do_action( 'kb_pre_tickets_column_sla', $kbs_ticket );

	$output  = $kbs_ticket->get_target_respond() . '<br />';
	$output .= $kbs_ticket->get_target_resolve();

	do_action( 'kb_post_tickets_column_sla', $kbs_ticket );

	return apply_filters( 'kb_tickets_post_column_sla', $output, $ticket_id );
} // kb_tickets_post_column_sla

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
	remove_action( 'save_post_kbs_ticket', 'kbs_ticket_post_save', 10, 3 );

	// Fire the before save action but only if this is not a new ticket creation (i.e $post->post_status == 'draft')
	if( $update === true )	{
		do_action( 'kbs_ticket_before_save', $post_id, $post, $update );
	}

	// Fire the after save action
	do_action( 'kbs_ticket_after_save', $post_id, $post, $update );

	// Re-add the save post action
	add_action( 'save_post_kbs_ticket', 'kbs_ticket_post_save', 10, 3 );
}
add_action( 'save_post_kbs_ticket', 'kbs_ticket_post_save', 10, 3 );
