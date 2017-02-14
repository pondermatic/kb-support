<?php
/**
 * Ticket Actions
 *
 * @package     KBS
 * @subpackage  Tickets/Functions
 * @copyright   Copyright (c) 2017, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Process ticket form submissions.
 *
 * @since	1.0
 * @return	void
 */
function kbs_process_ticket_submission()	{

	if ( ! isset( $_POST['kbs_action'] ) || 'submit_ticket' != $_POST['kbs_action'] )	{
		return;
	}

	if ( ! isset( $_POST['kbs_log_ticket'] ) || ! wp_verify_nonce( $_POST['kbs_log_ticket'], 'kbs-form-validate' ) )	{
		wp_die( __( 'Security failed.', 'kb-support' ) );
	}

	kbs_do_honeypot_check( $_POST );

	$form_id  = ! empty( $_POST['kbs_form_id'] ) ? $_POST['kbs_form_id'] : '';
	$redirect = ! empty( $_POST['redirect'] )    ? $_POST['redirect']    : '';

	$posted = array();
	$ignore = kbs_form_ignore_fields();

	foreach ( $_POST as $key => $value )	{
		if ( ! in_array( $key, $ignore ) )	{

			if ( is_string( $value ) || is_int( $value ) )	{
				$posted[ $key ] = $value;

			} elseif( is_array( $value ) )	{
				$posted[ $key ] = array_map( 'absint', $value );
			}

		}
	}

	$ticket_id = kbs_add_ticket_from_form( $form_id, $posted );

	if ( $ticket_id )	{
		$message  = 'ticket_submitted';
		$redirect = add_query_arg( array(
			'ticket' => kbs_get_ticket_key( $ticket_id )
			), get_permalink( kbs_get_option( 'tickets_page' ) )
		);
	} else	{
		$message = 'ticket_failed';
	}

	wp_redirect( add_query_arg(
		array( 'kbs_notice' => $message ),
		$redirect
	) );

	die();

} // kbs_process_ticket_form
add_action( 'init', 'kbs_process_ticket_submission' );

/**
 * When a reply is added by a customer.
 *
 * @since	1.0
 * @return	void
 */
function kbs_ticket_customer_reply_action()	{

	if ( ! isset( $_POST['kbs_action'] ) || 'submit_ticket_reply' != $_POST['kbs_action'] )	{
		return;
	}

	if ( ! isset( $_POST['kbs_ticket_reply'] ) || ! wp_verify_nonce( $_POST['kbs_ticket_reply'], 'kbs-reply-validate' ) )	{
		wp_die( __( 'Security failed.', 'kb-support' ) );
	}

	$ticket   = new KBS_Ticket( $_POST['kbs_ticket_id'] );
	$redirect = $_POST['redirect'];

	$reply_data = array(
		'ticket_id'   => $_POST['kbs_ticket_id'],
		'response'    => $_POST['kbs_reply'],
		'close'       => isset( $_POST['kbs_close_ticket'] ) ? true : false,
		'customer_id' => (int) $ticket->customer_id,
		'author'      => 0
	);

	$reply_id = $ticket->add_reply( $reply_data );

	if ( $reply_id )	{
		if ( ! empty( $_FILES['kbs_files'] ) )	{
			kbs_attach_files_to_reply( $reply_id );
		}

		do_action( 'kbs_ticket_customer_reply', $reply_id );
		$redirect = add_query_arg( 'kbs_notice', 'reply_success', $redirect );
	} else	{
		$redirect = add_query_arg( 'kbs_notice', 'reply_fail', $redirect );
	}

	wp_safe_redirect( $redirect );
	exit;

} // kbs_ticket_customer_reply_action
add_action( 'init', 'kbs_ticket_customer_reply_action' );

/**
 * When a reply is added via admin.
 *
 * @since	1.0
 * @return	void
 */
function kbs_ticket_reply_added_action()	{

	if ( ! isset( $_GET['kbs-action'] ) || 'ticket_reply_added' != $_GET['kbs-action'] )	{
		return;
	}

	$url = add_query_arg( array( 'kbs-message' => 'ticket_reply_added' ),
		kbs_get_ticket_url( $_GET['ticket_id'], true )
	);

	wp_safe_redirect( $url );
	exit;

} // kbs_ticket_reply_added_action
add_action( 'init', 'kbs_ticket_reply_added_action' );

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
function kbs_delete_ticket_note_action()	{

	if ( ! isset( $_GET['kbs-action'] ) || 'delete_ticket_note' != $_GET['kbs-action'] )	{
		return;
	}

	$ticket_id = absint( $_GET['ticket_id'] );
	$note_id   = absint( $_GET['note_id'] );

	if ( ! isset( $_GET['kbs_note_nonce'] ) || ! wp_verify_nonce( $_GET['kbs_note_nonce'], 'kbs_delete_ticket_note_' . $note_id ) )	{
		die();
	}

	if ( kbs_delete_note( $note_id, $ticket_id ) )	{
		$message = 'note_deleted';
	} else	{
		$message = 'note_not_deleted';
	}

	wp_safe_redirect( add_query_arg( array( 'kbs-message' => $message ), kbs_get_ticket_url( $ticket_id, true ) ) );

	die();

} // kbs_delete_ticket_note_action
add_action( 'init', 'kbs_delete_ticket_note_action' );

/**
 * View a single ticket.
 *
 * @since	1.0
 * @return str
 */
function kbs_view_ticket_action()	{

	if ( ! isset( $_GET['kbs_action'] ) || 'view_ticket' != $_GET['kbs_action'] )	{
		return;
	}

	$redirect = remove_query_arg( array(
		'kbs_action',
		'key',
		'ticket'
	), get_permalink( kbs_get_option( 'tickets_page' ) ) );

	if ( isset( $_GET['key'] ) )	{
		$ticket = $_GET['key'];
	} elseif ( isset( $_GET['ticket'] ) && is_user_logged_in() )	{
		$ticket = $_GET['ticket'];
	} else	{
		$ticket = '';
	}

	wp_safe_redirect( add_query_arg( array(
		'ticket' => $ticket
	), $redirect ) );
	die();

} // kbs_view_ticket_action
add_action( 'init', 'kbs_view_ticket_action' );

/**
 * Before a ticket is deleted determine what needs to be removed with it
 * and store within transient so we can hook after the ticket is deleted.
 *
 * @since	1.0
 * @param	int		$ticket_id	The ticket ID
 * @return	void
 */
function kbs_before_ticket_is_deleted( $ticket_id = 0 )	{

	if ( defined( 'WP_UNINSTALL_PLUGIN' ) )	{
		exit;
	}

	if ( empty( $ticket_id ) )	{
		return;
	}

	if ( 'kbs_ticket' != get_post_type( $ticket_id ) )	{
		return;
	}

	global $wpdb;

	$types = "'" . implode( "','", kbs_ticket_deleted_item_post_types() ) . "'";
	$key   = '_kbs_deleted_ticket_items_' . $ticket_id;

	$items = $wpdb->get_col( $wpdb->prepare(
		"SELECT ID
		FROM $wpdb->posts
		WHERE post_parent = %d
		AND post_type IN( {$types} )",
		$ticket_id
	) );

	if ( $items )	{
		set_transient( $key, $items, HOUR_IN_SECONDS );
	}

} // kbs_before_ticket_is_deleted
add_action( 'delete_post', 'kbs_before_ticket_is_deleted' );

/**
 * After a ticket is deleted, perform necessary clean up tasks such as
 * deleting associate replies and log entries.
 *
 * @since	1.0
 * @param	int		$ticket_id	The ticket ID
 * @return	void
 */
function kbs_cleanup_after_deleting_ticket( $ticket_id = 0 )	{

	if ( defined( 'WP_UNINSTALL_PLUGIN' ) )	{
		exit;
	}

	if ( empty( $ticket_id ) )	{
		return;
	}

	global $wpdb;

	$key   = '_kbs_deleted_ticket_items_' . $ticket_id;
	$items = get_transient( $key );

	if ( false !== $items )	{
		$item_ids = implode( ',', array_map( 'intval', $items ) );

		$wpdb->query(
			"DELETE FROM $wpdb->posts
			 WHERE ID IN( {$item_ids} )"
		);

		$wpdb->query(
			"DELETE FROM $wpdb->postmeta
			 WHERE post_id IN( {$item_ids} )"
		);

		delete_transient( $key );
	}

} // kbs_cleanup_after_deleting_ticket
add_action( 'after_delete_post', 'kbs_cleanup_after_deleting_ticket' );

/**
 * The post types to be deleted when a ticket is deleted.
 *
 * @since	1.0
 * @return	arr		Array of post types to delete when a ticket is being deleted.
 */
function kbs_ticket_deleted_item_post_types()	{
	$post_types = array( 'kbs_ticket_reply', 'kbs_log' );
	return apply_filters( 'kbs_ticket_deleted_item_post_types', $post_types );
} // kbs_ticket_deleted_item_post_types
