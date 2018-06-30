<?php
/**
 * Participant Functions
 *
 * @package     KBS
 * @subpackage  Paricipant/Functions
 * @copyright   Copyright (c) 2017, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Whether participants are enabled.
 *
 * @since	1.2.4
 * @return	bool	True if participants are enabled, otherwise false
 */
function kbs_participants_enabled()	{
	return kbs_get_option( 'enable_participants', false );
} // kbs_get_ticket_participants

/**
 * Whether or not the given user is a participant of the current ticket.
 *
 * @since	1.2.4
 * @param	int		$ticket_id	Ticket ID
 * @param	string	$email		Email address of user
 * @return	bool	True if a participant of the ticket, otherwise false
 */
function kbs_is_ticket_participant( $ticket_id, $email )	{
	$ticket = new KBS_Ticket( $ticket_id );

	return $ticket->is_participant( $email );
} // kbs_is_ticket_participant

/**
 * Retrieve the list of ticket participants.
 *
 * @since	1.2.4
 * @param	int				$ticket_id	Ticket ID
 * @return	array			Array of participants
 */
function kbs_get_ticket_participants( $ticket_id )	{
	$ticket = new KBS_Ticket( $ticket_id );

	return $ticket->participants;
} // kbs_get_ticket_participants

/**
 * Update the list of ticket participants.
 *
 * @since	1.2.4
 * @param	int				$ticket_id	Ticket ID
 * @param	string|array	Email address, or array of addresses, to add
 * @return	array			Array of participants
 */
function kbs_add_ticket_participants( $ticket_id, $email_addresses )	{
	$ticket = new KBS_Ticket( $ticket_id );

	return $ticket->add_participants( $email_addresses );
} // kbs_add_ticket_participants
