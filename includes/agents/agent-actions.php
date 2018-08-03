<?php
/**
 * Agent Actions
 *
 * @package     KBS
 * @subpackage  Agent/Functions
 * @copyright   Copyright (c) 2018, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.2.5
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Adds default values to the user meta when a new agent is added.
 *
 * @since   1.2.5
 * @param   int     $user_id    WP User ID
 * @return  void
 */
function kbs_add_default_agent_data_to_user( $user_id ) {

    if ( is_admin() && kbs_is_agent( $user_id ) ) {
        $agent = new KBS_Agent( $user_id );
    }

} // kbs_add_default_agent_data_to_user
add_action( 'user_register', 'kbs_add_default_agent_data_to_user' );
add_action( 'profile_update', 'kbs_add_default_agent_data_to_user' );

/**
 * Adjust agent ticket count on assignment.
 *
 * @since   1.2.5
 * @param   int     $ticket_id  Ticket ID
 * @param   int     $new_agent  Agent being assigned
 * @param   int     $old_agent  Previous agent
 * @return  void
 */
function kbs_update_agent_open_ticket_count_assignment( $ticket_id, $new_agent = 0, $old_agent = 0 ) {

    if ( $new_agent == $old_agent ) {
        return;
    }

    $agent  = new KBS_Agent( $new_agent );
    $_agent = new KBS_Agent( $old_agent );

    if ( ! $agent ) {
        return;
    }

    $statuses = $agent->get_open_statuses();

    if ( in_array( get_post_status( $ticket_id ), $statuses ) )   {
        $agent->increase_open_tickets();

        if ( $_agent )  {
            $_agent->decrease_open_tickets();
        }
    }
} // kbs_update_agent_open_ticket_count_assignment
add_action( 'kbs_assigned_agent', 'kbs_update_agent_open_ticket_count_assignment', 10, 3 );

/**
 * Adjust agent open ticket count when a ticket's status changes.
 *
 * @since   1.2.5
 * @param   int     $ticket_id  The ticket ID
 * @param   string  $new_status The new status of the ticket
 * @param   string  $old_status The old status of the ticket
 * @param   object  $ticket     KBS_Ticket class object
 * @return  void
 */
function kbs_update_agent_open_ticket_count_status_change( $ticket_id, $new_status, $old_status, $ticket ) {
    if ( empty( $ticket->agent_id ) || $old_status == $new_status )   {
        return;
    }

    $agent = new KBS_Agent( $ticket->agent_id );

    if ( ! $agent ) {
        return;
    }

    $statuses = $agent->get_open_statuses();

    if ( in_array( $new_status, $statuses ) && in_array( $old_status, $statuses ) ) {
        return;
    }

    if ( in_array( $new_status, $agent->get_open_statuses() ) )   {
        $agent->increase_open_tickets();
    } else  {
        $agent->decrease_open_tickets();
    }
} // kbs_update_agent_open_ticket_count_status_change
add_action( 'kbs_update_ticket_status', 'kbs_update_agent_open_ticket_count_status_change', 10, 4 );
