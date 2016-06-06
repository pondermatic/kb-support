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
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Determines the target response time for the ticket.
 *
 * @since	1.0
 * @param
 * @return	str		Date/Time for targetted response time.
 */
function kbs_calculate_sla_target_response()	{
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
	$now    = current_time( 'timestamp' );
	$target = strtotime( '+' . kbs_get_option( 'sla_resolve_time' ), $now );
	
	return apply_filters( 'kbs_calculate_sla_target_resolution', date( 'Y-m-d H:i:s', $target ) );
} // kbs_calculate_sla_target_resolution
