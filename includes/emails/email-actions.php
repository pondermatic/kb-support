<?php
/**
 * Email Actions
 *
 * @package     KBS
 * @subpackage  Emails
 * @copyright   Copyright (c) 2017, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Triggers Ticket Received email to be sent after the ticket status is updated
 *
 * @since	1.0
 * @param	int		$ticket_id	Ticket ID
 * @return	void
 */
function kbs_trigger_ticket_received( $ticket_id ) {
	// Make sure we don't send while editing a ticket
	if ( isset( $_POST['kbs-action'] ) && 'edit_ticket' == $_POST['kbs-action'] ) {
		return;
	}

	kbs_email_ticket_received( $ticket_id );
} // kbs_trigger_ticket_received
add_action( 'kbs_add_ticket',  'kbs_trigger_ticket_received', 999, 1 );

/**
 * Trigger the agent assigned email
 *
 * @since   1.1
 * @param   str     $meta_key   The meta key being updated
 * @param   int     $meta_value The new value of the meta key
 * @param   int     $prev_value The previous value of the meta key
 * @param   int     $ticket_id  The ticket ID
 * @return  void
 */
function kbs_trigger_agent_assigned_email( $meta_key, $meta_value, $prev_value, $ticket_id ) {
    if ( '_kbs_ticket_agent_id' != $meta_key )  {
        return;
    }

    if ( $prev_value == $meta_value )   {
        return;
    }

    kbs_email_agent_assigned_to_ticket( $ticket_id, $prev_value );
} // kbs_trigger_agent_assigned_email
add_action( 'kbs_update_ticket_meta', 'kbs_trigger_agent_assigned_email', 999, 4 );

/**
 * Trigger the sending of a Test Email
 *
 * @since	1.0
 * @return	void
 */
function kbs_send_test_email()	{

	if ( ! isset( $_GET['kbs_action'] ) || 'send_test_email' != $_GET['kbs_action'] )	{
		return;
	}

	if ( ! wp_verify_nonce( $_GET['_wpnonce'], 'kbs-test-email' ) ) {
		return;
	}

	// Send a test email
	kbs_email_test_ticket_received();

	// Remove the test email query arg
	wp_redirect( remove_query_arg( 'kbs_action' ) ); exit;
} // kbs_send_test_email
add_action( 'init', 'kbs_send_test_email' );
