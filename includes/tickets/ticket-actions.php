<?php
/**
 * Ticket Actions
 *
 * @package     KBS
 * @subpackage  Tickets/Functions
 * @copyright   Copyright (c) 2016, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * When a reply is added.
 *
 * @since	1.0
 * @param	arr		$data	Array of data passed to action.
 * @return	void
 */
function kbs_ticket_reply_added_action( $data )	{

	$url = add_query_arg( array( 'kbs-message' => 'ticket_reply_added' ),
		kbs_get_ticket_url( $data['ticket_id'], true )
	);

	wp_safe_redirect( $url );
	exit;

} // kbs_ticket_reply_added_action
add_action( 'kbs-ticket_reply_added', 'kbs_ticket_reply_added_action' );

/**
 * Assigns the currently logged in agent to the ticket if the current
 * is unassigned.
 *
 * @since	1.0
 * @return	void
 */
function kbs_auto_assign_agent_to_ticket_action()	{

	if ( ! isset( $_GET['post'] ) || 'kbs_ticket' != get_post_type( $_GET['post'] ) || ! kbs_get_option( 'auto_assign_agent', false ) )	{
		return;
	}
	
	$kbs_ticket = new KBS_Ticket( $_GET['post'] );
	
	if ( 'new' != $kbs_ticket->post_status || ! empty( $kbs_ticket->agent_id ) )	{
		return;
	}

	kbs_assign_agent( $kbs_ticket->ID );

} // kbs_ticket_auto_assign_agent_action
add_action( 'load-post.php', 'kbs_auto_assign_agent_to_ticket_action' );

/**
 * Deletes a note from a ticket.
 *
 * @since	1.0
 * @return	void
 */
function kbs_delete_ticket_note_action( $data )	{

	$ticket_id = $data['ticket_id'];
	$note_id   = $data['note_id'];

	if ( ! isset( $data['kbs_note_nonce'] ) || ! wp_verify_nonce( $data['kbs_note_nonce'], 'kbs_delete_ticket_note_' . $note_id ) )	{
		die();
	}

	if ( kbs_ticket_delete_note( $note_id, $ticket_id ) )	{
		$message = 'note_deleted';
	} else	{
		$message = 'note_not_deleted';
	}

	wp_safe_redirect( add_query_arg( array( 'kbs-message' => $message ), kbs_get_ticket_url( $ticket_id, true ) ) );

	die();

} // kbs_delete_ticket_note_action
add_action( 'kbs-delete_ticket_note', 'kbs_delete_ticket_note_action' );

/**
 * View a single ticket.
 *
 * @since	1.0
 * @return str
 */
function kbs_view_ticket_action( $data )	{
	$redirect = remove_query_arg( array(
		'kbs_action',
		'key',
		'ticket'
	), get_permalink( kbs_get_option( 'tickets_page' ) ) );

	if ( isset( $data['key'] ) )	{
		$ticket = $data['key'];
	} elseif ( isset( $data['ticket'] ) && is_user_logged_in() )	{
		$ticket = $data['ticket'];
	} else	{
		$ticket = '';
	}

	wp_safe_redirect( add_query_arg( array(
		'ticket' => $ticket
	), $redirect ) );
	die();

} // kbs_view_ticket_action
add_action( 'kbs_view_ticket', 'kbs_view_ticket_action' );
