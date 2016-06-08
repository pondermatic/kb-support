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
 * status is unassigned.
 *
 * @since	1.0
 * @param
 * @return	void
 */
function kbs_ticket_auto_assign_agent_action()	{
	global $post;
	
	if ( 'kbs_ticket' != get_post_type( $post->ID ) || 'unassigned' != get_post_status( $post->ID ) || ! kbs_get_option( 'auto_assign_agent', false ) )	{
		return;
	}

	kbs_assign_agent( $post->ID );
} // kbs_ticket_auto_assign_agent_action
add_action( 'load-post.php', 'kbs_ticket_auto_assign_agent_action' );
