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
 * Assigns the currently logged in agent to the ticket if the current
 * is unassigned.
 *
 * @since	1.0
 * @param
 * @return	void
 */
function kbs_auto_assign_agent_to_ticket_action()	{
	
	if ( ! isset( $_GET['post'] ) || 'kbs_ticket' != get_post_type( $_GET['post'] ) || ! kbs_get_option( 'auto_assign_agent', false ) )	{
		return;
	}
	
	$kbs_ticket = new KBS_Ticket( $_GET['post'] );
	
	if ( 'new' != $kbs_ticket->post_status || ! empty( $kbs_ticket->agent ) )	{
		return;
	}

	kbs_assign_agent( $kbs_ticket->ID );
} // kbs_ticket_auto_assign_agent_action
add_action( 'load-post.php', 'kbs_auto_assign_agent_to_ticket_action' );
