<?php
/**
 * Email Actions
 *
 * @package     KBS
 * @subpackage  Emails
 * @copyright   Copyright (c) 2016, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Triggers Ticket Received email to be sent after the ticket status is updated
 *
 * @since	0.1
 * @param	int		$ticket_id	Ticket ID
 * @return	void
 */
function kbs_trigger_ticket_received( $ticket_id ) {
	// Make sure we don't send while editing a ticket
	if ( isset( $_POST['kbs-action'] ) && 'edit_ticket' == $_POST['kbs-action'] ) {
		return;
	}

	// Send email with ticket details
	kbs_email_ticket_received( $ticket_id );
}
add_action( 'kbs_ticket_logged', 'kbs_trigger_ticket_received', 999, 1 );

/**
 * Trigger the sending of a Test Email
 *
 * @since	0.1
 * @param	arr		$data	Parameters sent from Settings page
 * @return	void
 */
function kbs_send_test_email( $data ) {
	if ( ! wp_verify_nonce( $data['_wpnonce'], 'kbs-test-email' ) ) {
		return;
	}

	// Send a test email
	kbs_email_test_ticket_received();

	// Remove the test email query arg
	wp_redirect( remove_query_arg( 'kbs_action' ) ); exit;
} // kbs_send_test_email
add_action( 'kbs_send_test_email', 'kbs_send_test_email' );
