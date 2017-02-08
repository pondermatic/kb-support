<?php
/**
 * SLA
 *
 * @package     KBS
 * @subpackage  Functions/SLA
 * @copyright   Copyright (c) 2017, Mike Howard
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
	$now    = current_time( 'timestamp' );
	$target = kbs_get_option( 'sla_response_time' );

	if ( $target )	{
		$target = strtotime( '+' . $target, $now );
		$target = date( 'Y-m-d H:i:s', $target );
	}
	
	return apply_filters( 'kbs_calculate_sla_target_response', $target );
} // kbs_calculate_sla_target_response

/**
 * Determines the target resolution time for the ticket.
 *
 * @since	1.0
 * @param
 * @return	str		Date/Time for targetted resolution time.
 */
function kbs_calculate_sla_target_resolution()	{
	$now    = current_time( 'timestamp' );
	$target = kbs_get_option( 'sla_resolve_time' );

	if ( $target )	{
		$target = strtotime( '+' . $target, $now );
		$target = date( 'Y-m-d H:i:s', $target );
	}
	
	return apply_filters( 'kbs_calculate_sla_target_resolution', $target );
} // kbs_calculate_sla_target_resolution

/**
 * Log first respond value when an agent sends the first reply.
 *
 * @since	1.0
 * @param	int		$ticket_id	The ticket ID
 * @return	void
 */
function kbs_set_sla_first_respond( $ticket_id )	{
	add_post_meta( $ticket_id, '_kbs_ticket_sla_first_respond', date( 'Y-m-d H:i:s', current_time( 'timestamp' ) ), true );
	add_post_meta( $ticket_id, '_kbs_ticket_sla_first_respond_agent', get_current_user_id(), true );
} // kbs_calculate_sla_target_resolution
add_action( 'kbs_ticket_admin_reply', 'kbs_set_sla_first_respond', 10, 2 );

/**
 * Retrieve the tickets first response time value.
 *
 * @since	1.0
 * @param	int		$ticket_id	The ticket ID
 * @return	void
 */
function kbs_get_sla_first_response( $ticket_id )	{
	return get_post_meta( $ticket_id, '_kbs_ticket_sla_first_respond', current_time( 'timestamp' ) );
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
 * @param	obj|int	$ticket		The KBS Ticket object or a ticket ID
 * @param	str		$sla_target	The SLA target to check. 'response' or 'resolve'
 * @return	bool			False if still within SLA, otherwise true.
 */
function kbs_sla_has_passed( $ticket, $sla_target = 'response' ) {

	if ( ! kbs_track_sla() )	{
		return false;
	}

	if ( empty( $sla_target ) )	{
		return false;
	}

	if ( is_numeric( $ticket ) )	{
		$ticket = new KBS_Ticket( $ticket );
	}

	$sla = 'sla_' . $sla_target;

	if ( empty( $ticket->$sla ) )	{
		return false;
	}

	$return = false;
	$now    = current_time( 'timestamp' );
	$target = strtotime( $ticket->$sla );

	switch( $sla_target )	{
		case 'response':
			if ( ! empty( $ticket->first_response ) )	{
				if ( strtotime( $ticket->first_response ) > $target )	{
					$return = true;
				}
			} elseif ( $now > $target )	{
				$return = true;
			}
			break;

		case 'resolve':
				if ( $now > $target )	{
					$return = true;
				}
			break;
	}

	return (bool) apply_filters( 'kbs_sla_has_passed', $return, $ticket, $sla_target );
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
