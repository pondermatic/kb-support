<?php
/**
 * Agent Functions
 *
 * Functions related to agents
 *
 * @package     KBS
 * @subpackage  Functions
 * @copyright   Copyright (c) 2016, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Retrieve all agents.
 *
 * @since	1.0
 * @param	bool	$ids	True to return array of IDs, false for array of user objects
 * @return	mixed
 */
function kbs_get_agents( $ids = false )	{
	$role  = array( 'support_agent', 'support_manager' );

	if ( kbs_get_option( 'admin_agents', false ) )	{
		$role[] = 'administrator';
	}

	$users = kbs_get_users_by_role( $role, $ids );
	
	return $users;
} // kbs_get_agents

/**
 * Whether or not the user is an agent.
 *
 * @since	1.0
 * @param	int		$agent_id	The user ID to check.
 * @return	bool	True if the user is an agent, or false
 */
function kbs_is_agent( $agent_id = 0 )	{

	if ( empty( $agent_id ) )	{
		$agent_id = get_current_user_id();
	}

	$agents = kbs_get_agents( true );

	return in_array( $agent_id, $agents );

} // kbs_is_agent

/**
 * Retrieve a random agent.
 *
 * @since	1.0
 * @return	int		Random agent user ID
 */
function kbs_get_random_agent()	{
	$agents   = kbs_get_agents( true );
	$agent_id = 0;

	if ( ! empty( $agents ) )	{
		$random   = array_rand( $agents, 1 );
		$agent_id = $agents[ $random ];
	}

	return $agent_id;
} // kbs_get_random_agent

/**
 * Retrieve the count of agent tickets.
 *
 * @since	1.0
 * @param	int		$agent_id	The agent ID for which to retrieve the count.
 * @return	int		Count of agent tickets
 */
function kbs_agent_ticket_count( $agent_id )	{
	$tickets = kbs_count_tickets( array( 'agent' => $agent_id ) );

	$count   = 0;

	if ( ! empty( $tickets ) )	{
		$active_statuses = kbs_get_active_ticket_status_keys();
		foreach( $tickets as $status )	{
			if ( ! empty( $status ) && in_array( $status, $active_statuses ) )	{
				$count += $status;
			}
		}
	}

	return $count;
} // kbs_agent_ticket_count
