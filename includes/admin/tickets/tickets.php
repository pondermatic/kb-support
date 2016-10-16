<?php	
/**
 * Manage kbs-ticket posts.
 * 
 * @since		1.0
 * @package		KBS
 * @subpackage	Posts
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Define the columns that should be displayed for the KBS ticket post lists screen
 *
 * @since	1.0
 * @param	arr		$columns	An array of column name ⇒ label. The label is shown as the column header.
 * @return	arr		$columns	Filtered array of column name => label to be shown as the column header.
 */
function kbs_set_kbs_ticket_post_columns( $columns ) {
    
	$columns = array(
        'cb'               => '<input type="checkbox" />',
        'id'               => '#',
		'dates'            => __( 'Date', 'kb-support' ),
		'title'            => __( 'Title', 'kb-support' ),
        'customer'         => __( 'Customer', 'kb-support' ),
		'categories'       => __( 'Category', 'kb-support' ),
        'agent'            => __( 'Agent', 'kb-support' )
    );
	
	if ( kbs_track_sla() )	{
		$columns['sla'] = __( 'SLA Status', 'kbs-support' );
	}
	
	return apply_filters( 'kbs_ticket_post_columns', $columns );
	
} // kbs_set_kbs_ticket_post_columns
add_filter( 'manage_kbs_ticket_posts_columns' , 'kbs_set_kbs_ticket_post_columns' );

/**
 * Define the data to be displayed within the KBS ticket post custom columns.
 *
 * @since	1.0
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

		case 'customer':
			echo kb_tickets_post_column_customer( $post_id, $kbs_ticket );
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

} // kbs_set_kbs_ticket_column_data
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

	$output = '<a href="' . get_edit_post_link( $ticket_id ) . '">' . kbs_get_ticket_id( $ticket_id ) . '</a>';
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

	$output  = date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $kbs_ticket->date ) );
	$output .= '<br />';
	$output .= date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $kbs_ticket->modified_date ) );

	do_action( 'kb_post_tickets_column_date', $kbs_ticket );

	return apply_filters( 'kb_tickets_post_column_date', $output, $ticket_id );
} // kb_tickets_post_column_date

/**
 * Output the Customer row.
 *
 * @since	1.0
 * @param	int	$ticket_id	The ticket ID
 * @param	obj	$kbs_ticket	The ticket WP_Post object
 * @return	str
 */
function kb_tickets_post_column_customer( $ticket_id, $kbs_ticket )	{
	do_action( 'kb_pre_tickets_column_customer', $kbs_ticket );

	if ( ! empty( $kbs_ticket->customer_id ) )	{

		$customer = new KBS_Customer( $kbs_ticket->customer_id );

		$customer_page = add_query_arg( array(
			'post_type' => 'kbs_ticket',
			'page'      => 'kbs-customers',
			'view'      => 'userdata',
			'id'        => $kbs_ticket->customer_id
		), admin_url( 'edit.php' ) );

		$output = '<a href="' . $customer_page . '">' . $customer->name . '</a>';

	} else	{
		$output = __( 'No Customer Assigned', 'kb-support' );
	}

	do_action( 'kb_post_tickets_column_customer', $kbs_ticket );

	return apply_filters( 'kb_tickets_post_column_customer', $output, $ticket_id );
} // kb_tickets_post_column_customer

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

	if ( ! empty( $kbs_ticket->agent_id ) )	{
		$output = sprintf( '<a href="%s">%s</a>',
			get_edit_user_link( $kbs_ticket->agent_id ),
			get_userdata( $kbs_ticket->agent_id )->display_name
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
 * Filter posts by customer.
 *
 * @since	1.0
 * @return	void
 */
function kbs_filter_customer_posts( $query )	{
	if ( ! is_admin() || 'kbs_ticket' != $query->get( 'post_type' ) || ! isset( $_GET['customer'] ) )	{
		return;
	}

	$query->set( 'meta_key', '_kbs_ticket_customer_id' );
	$query->set( 'meta_value', $_GET['customer'] );
	$query->set( 'meta_type', 'NUMERIC' );
} // kbs_filter_customer_posts
add_action( 'pre_get_posts', 'kbs_filter_customer_posts' );

/**
 * Remove action items from the bulk item menu and post row action list.
 *
 * @since	1.0
 * @param	arr		$actions	The action items array
 * @return	arr		Filtered action items array
 */
function kbs_tickets_remove_trash_action( $actions )	{
	if ( 'kbs_ticket' == get_post_type() )	{

		$remove_actions = array( 'edit', 'trash', 'inline hide-if-no-js' );

		foreach( $remove_actions as $remove_actions )	{

			if ( isset( $actions[ $remove_actions ] ) )	{
				unset( $actions[ $remove_actions ] );
			}

		}

	}

	return $actions;
} // kbs_tickets_remove_bulk_trash
add_filter( 'bulk_actions-edit-kbs_ticket', 'kbs_tickets_remove_trash_action' );
add_filter( 'post_row_actions', 'kbs_tickets_remove_trash_action' );

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

	if ( ! isset( $_POST['kbs_ticket_meta_box_nonce'] ) || ! wp_verify_nonce( $_POST['kbs_ticket_meta_box_nonce'], 'kbs_ticket_meta_save' ) ) {
		return;
	}
	
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )	{
		return;
	}

	if ( isset( $post->post_type ) && 'revision' == $post->post_type ) {
		return;
	}

	if ( ! $update && is_admin() )	{
		add_post_meta( $post_id, '_kbs_ticket_created_by', get_current_user_id(), true );
	}

	// The default fields that get saved
	$fields = kbs_ticket_metabox_fields();

	$ticket = new KBS_Ticket( $post_id );

	foreach ( $fields as $field )	{
		$meta_field    = str_replace( 'kbs_', '_kbs_ticket_', $field );

		if ( ! empty( $_POST[ $field ] ) ) {
			$new_value = apply_filters( 'kbs_ticket_metabox_save_' . $field, $_POST[ $field ] );

			$ticket->update_meta( $meta_field, $new_value );
		} else {
			delete_post_meta( $ticket->ID, $meta_field );
		}

	}

	if ( ! empty( $_POST['ticket_status'] ) && $_POST['ticket_status'] != $post->post_status )	{
		$ticket->update_status( $_POST['ticket_status'] );
	}

	do_action( 'kbs_save_ticket', $post_id, $post );

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
} // kbs_ticket_post_save
add_action( 'save_post_kbs_ticket', 'kbs_ticket_post_save', 10, 3 );
