<?php
/**
 * User Functions
 *
 * Functions related to users / customers
 *
 * @package     KBS
 * @subpackage  Functions
 * @copyright   Copyright (c) 2016, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Get Users Tickets
 *
 * Retrieves a list of all tickets by a specific user.
 *
 * @since  0.1
 *
 * @param	int		$user User ID or email address
 * @param	int		$number Number of tickets to retrieve
 * @param	bool	$pagination
 * @param	str		$status
 *
 * @return	bool|obj	List of all user tickets
 */
function kbs_get_users_tickets( $user = 0, $number = 20, $pagination = false, $status = 'closed' ) {

	if ( empty( $user ) ) {
		$user = get_current_user_id();
	}

	if ( 0 === $user ) {
		return false;
	}

	$status = $status === 'closed' ? 'publish' : $status;

	if ( $pagination ) {
		if ( get_query_var( 'paged' ) )
			$paged = get_query_var('paged');
		else if ( get_query_var( 'page' ) )
			$paged = get_query_var( 'page' );
		else
			$paged = 1;
	}

	$args = array(
		'user'    => $user,
		'number'  => $number,
		'status'  => $status,
		'orderby' => 'date'
	);

	if ( $pagination ) {

		$args['page'] = $paged;

	} else {

		$args['nopaging'] = true;

	}

	$by_user_id = is_numeric( $user ) ? true : false;
	/*$customer   = new EDD_Customer( $user, $by_user_id );

	if( ! empty( $customer->payment_ids ) ) {

		unset( $args['user'] );
		$args['post__in'] = array_map( 'absint', explode( ',', $customer->payment_ids ) );

	}

	$purchases = edd_get_payments( apply_filters( 'edd_get_users_purchases_args', $args ) );

	// No purchases
	if ( ! $purchases )
		return false;

	return $purchases;*/
	return;
} // kbs_get_users_tickets

/**
 * Validate a potential username
 *
 * @access      public
 * @since       0,1
 * @param       str		$username	The username to validate
 * @return      bool
 */
function kbs_validate_username( $username ) {
	$sanitized = sanitize_user( $username, false );
	$valid = ( $sanitized == $username );
	return (bool) apply_filters( 'kbs_validate_username', $valid, $username );
} // kbs_validate_username

