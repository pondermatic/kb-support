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

/**
 * Log an agents online status.
 *
 * Sets a transient that tells us the agent is actively logged on.
 *
 * @since	1.0
 * @return	void
 */
function kbs_set_agent_status()	{

	if ( is_admin() && is_user_logged_in() )	{

		$agent_id = get_current_user_id();

		$expire   = MINUTE_IN_SECONDS * 30;
		$expire   = apply_filters( 'kbs_agent_status_expire_time', $expire );
		$screen   = get_current_screen();

		if ( ! empty( $agent_id ) && kbs_is_agent( $agent_id ) )	{

			$transient_key = '_kbs_active_agent_' . $agent_id;
			set_transient( $transient_key, $screen->id, $expire );
		}

	}

} // kbs_set_agent_status
add_action( 'current_screen', 'kbs_set_agent_status' );

/**
 * Retrieve an agents status.
 *
 * This function is a wrapper for the get_transient function
 *
 * @since	1.0
 * @param	int		$agent_id	The user ID of the agent to check
 * @return	mixed	True if online, or false
 */
function kbs_get_agent_status( $agent_id )	{
	return get_transient( '_kbs_active_agent_' . $agent_id );
} // kbs_get_agent_status


/**
 * Whether or not an agent is online.
 *
 * @since	1.0
 * @param	int		$agent_id	The user ID of the agent to check
 * @return	bool	True if online, or false
 */
function kbs_agent_is_online( $agent_id )	{
	$status = kbs_get_agent_status( $agent_id );

	$online = false === $status ? false : true;

	return apply_filters( 'kbs_agent_is_online', $online );
} // kbs_agent_is_online

/**
 * Sets an agents status to offline during logoff.
 *
 * @since	1.0
 * @return	void
 */
function kbs_set_agent_offline( $agent_id = 0 )	{
	if ( empty( $agent_id ) )	{
		$agent_id = get_current_user_id();
	}

	if ( kbs_is_agent( $agent_id ) )	{
		delete_transient( '_kbs_active_agent_' . $agent_id );
	}
} // kbs_set_agent_offline
add_action( 'login_form_logout', 'kbs_set_agent_offline' );

/**
 * Displays an agents online status icon.
 *
 * @since	1.0
 * @param	int		$agent_id	The user ID of the agent to check
 * @param	bool	$echo		True to echo the indicator, false to return
 * @return	str		Online status indicator icon
 */
function kbs_agent_display_status_icon( $agent_id, $echo = true )	{

	$status = kbs_agent_is_online( $agent_id ) ? 'online' : 'offline';

	$img_src   = KBS_PLUGIN_URL . 'assets/images/agent_status_' . $status;
	$img_alt   = sprintf( __( 'Agent %s', 'kb-support' ), $status );
	$img_title = sprintf( __( '%s is currently %s', 'kb-support' ), get_userdata( $agent_id )->display_name, $status );

	$icon = ' <img class="kbs_agent_status_' . $status . '" src="' . $img_src . '.gif" alt="' . $img_alt . '" title="' . $img_title . '" height="10" width="10">';

	if ( $echo )	{
		echo $icon;
	} else	{
		return $icon;
	}

} // kbs_agent_display_status_icon
