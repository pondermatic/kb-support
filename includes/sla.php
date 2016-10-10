<?php
/**
 * SLA
 *
 * @package     KBS
 * @subpackage  Functions/SLA
 * @copyright   Copyright (c) 2016, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Whether or not to track SLA.
 *
 * @since	1.0
 * @return	bool	True to track, otherwise false.
 */
function kbs_track_sla()	{
	$track_sla = kbs_get_option( 'sla_tracking', false );

	return apply_filters( 'kbs_track_sla', $track_sla );
} // kbs_track_sla

/**
 * Determines the target response time for the ticket.
 *
 * @since	1.0
 * @param
 * @return	str		Date/Time for targetted response time.
 */
function kbs_calculate_sla_target_response()	{
	if ( ! kbs_track_sla() )	{
		return false;
	}

	$now    = current_time( 'timestamp' );
	$target = strtotime( '+' . kbs_get_option( 'sla_response_time' ), $now );
	
	return apply_filters( 'kbs_calculate_sla_target_response', date( 'Y-m-d H:i:s', $target ) );
} // kbs_calculate_sla_target_response

/**
 * Determines the target resolution time for the ticket.
 *
 * @since	1.0
 * @param
 * @return	str		Date/Time for targetted resolution time.
 */
function kbs_calculate_sla_target_resolution()	{
	if ( ! kbs_track_sla() )	{
		return false;
	}

	$now    = current_time( 'timestamp' );
	$target = strtotime( '+' . kbs_get_option( 'sla_resolve_time' ), $now );
	
	return apply_filters( 'kbs_calculate_sla_target_resolution', date( 'Y-m-d H:i:s', $target ) );
} // kbs_calculate_sla_target_resolution

/**
 * Retrieve the target SLA response time.
 *
 * @since	1.0
 * @param	int	$ticket_id		The ticket ID
 * @return	str	The target response date/time.
 */
function kbs_get_target_respond( $ticket_id )	{
	$kbs_ticket = new KBS_Ticket( $ticket_id );
	
	return $kbs_ticket->get_target_respond();
} // kbs_get_target_respond

/**
 * Retrieve the target SLA resolve time.
 *
 * @since	1.0
 * @param	int	$ticket_id		The ticket ID
 * @return	str	The target resolve date/time.
 */
function kbs_get_target_resolve( $ticket_id )	{
	$kbs_ticket = new KBS_Ticket( $ticket_id );
	
	return $kbs_ticket->get_target_resolve();
} // kbs_get_target_resolve

/**
 * Whether or not the target SLA time has passed.
 *
 * @since	1.0
 * @param	int	$ticket_id	The ticket ID
 * @param	str	$target		The SLA target to check. 'response' or 'resolve'
 * @return	bool			False if still within SLA, otherwise true.
 */
function kbs_sla_has_passed( $ticket_id, $target = 'response' ) {
	
	if ( $target == 'resolve' )	{
		$target_time = strtotime( kbs_get_target_resolve( $ticket_id ) );
	} else	{
		$target_time = strtotime( kbs_get_target_respond( $ticket_id ) );
	}

	$return = true;

	if ( current_time( 'timestamp' ) < $target_time )	{
		$return = false;
	}

	return (bool) apply_filters( 'kbs_sla_has_passed', $return, $ticket_id, $target );
} // kbs_sla_has_passed

/**
 * Returns the time until, or since, SLA target response.
 *
 * @since	1.0
 * @param	int	$ticket_id	The ticket ID
 * @param	str	$target		The SLA target to check. 'response' or 'resolve'
 * @return	str				Time until (or since) targetted response time.
 */
function kbs_time_to_target( $ticket_id )	{
	$kbs_ticket = new KBS_Ticket( $ticket_id );
	
	return $kbs_ticket->get_sla_remain();
} // kbs_time_to_target_response
