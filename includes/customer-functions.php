<?php
/**
 * Customer Functions
 *
 * Functions related to customers
 *
 * @package     KBS
 * @subpackage  Functions
 * @copyright   Copyright (c) 2017, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Retrieve the customer ID from a ticket.
 *
 * @since	1.0
 * @param	int		$ticket_id	The ticket ID
 * @return	int		The customer ID
 */
function kbs_get_customer_id_from_ticket( $ticket_id )	{
	return get_post_meta( $ticket_id, '_kbs_ticket_customer_id', true );
} // kbs_get_customer_id_from_ticket

/**
 * Retrieve the customer company ID.
 *
 * @since	1.0
 * @param	int		$customer_id	The customer ID
 * @return	int		The customer company ID
 */
function kbs_get_customer_company_id( $customer_id )	{
	global $wpdb;

	$company_id = 0;

	$results = $wpdb->get_col( $wpdb->prepare(
		"SELECT company_id
		FROM " . $wpdb->prefix . "kbs_customers
		WHERE id = %d",
		$customer_id
	) );

	if ( $results )	{
		$company_id = $results[0];
	}

	return $company_id;
} // kbs_get_customer_company_id

/**
 * Whether or not a customer can access the ticket.
 *
 * @since	1.2
 * @param	int|object	$ticket		The ticket ID or a KBS_Ticket class object
 * @param	int|object	$customer	The customer ID or a KBS_Customer class object.
 * @return	bool		True if customer can view, otherwise false
 */
function kbs_customer_can_access_ticket( $ticket = '', $customer = '' )	{

	if ( empty( $ticket ) )	{
		return false;
	}

	if ( is_numeric( $ticket ) )	{
		$ticket = new KBS_Ticket( $ticket );
		if ( empty( $ticket->ID ) )	{
			return false;
		}
	}

	if ( is_numeric( $customer ) )	{
		$by_user_id = false;

		if ( get_current_user_id() == $customer )	{
			$by_user_id = true;
		}

		$customer = new KBS_Customer( $customer, $by_user_id );

		if ( empty( $customer->id ) )	{
			return false;
		}
	}

	$can_access = false;

	if ( $customer->id == $ticket->customer_id )	{
		$can_access = true;
	}

	/**
	 * Enable extensions to overide the $can_access result
	 *
	 * @since	1.2
	 * @param	bool	$can_access		True if customer can view, otherwise false
	 * @param	object	$ticket			KBS_Ticket class object
	 * @param	object	$customer		KBS_Customer class object
	 */
	$can_access = apply_filters( 'kbs_customer_can_access_ticket', $can_access, $ticket, $customer );

	return (bool)$can_access;
} // kbs_customer_can_access_ticket

/**
 * Retrieve customer tickets.
 *
 * @since	1.0
 * @param	int|obj		$customer	The customer ID or a KBS_Customer object.
 * @param	arr			$args		Args that can be passed to kbs_get_tickets()
 * @param	bool		$can_select	True to only return selectable status. False for all.
 * @return	obj			Array of customer ticket objects.
 */
function kbs_get_customer_tickets( $customer, $args = array(), $can_select = true, $pagination = false )	{

	$customer_id = $customer;

	if ( is_object( $customer ) )	{
		$customer_id = $customer->id;
	}

	if ( empty( $customer_id ) )	{
		return false;
	}

	$ticket_statuses = kbs_get_ticket_statuses( $can_select );

	if ( $pagination )	{
		if ( get_query_var( 'paged' ) )	{
			$paged = get_query_var('paged');
		} else if ( get_query_var( 'page' ) )	{
			$paged = get_query_var( 'page' );
		} else	{
			$paged = 1;
		}
	}

	$defaults = array(
		'customer' => $customer_id,
		'status'   => array_keys( $ticket_statuses ),
		'number'   => 10
	);

	$args = wp_parse_args( $args, $defaults );

	if ( $pagination )	{
		$args['page'] = $paged;
	} else	{
		$args['nopaging'] = true;
	}

	return kbs_get_tickets( $args );

} // kbs_get_customer_tickets

/**
 * Retrieve customer ticket count.
 *
 * @since	1.0
 * @param	int|obj		$customer	The customer ID or a KBS_Customer object.
 * @return	int			Array of customer ticket objects.
 */
function kbs_get_customer_ticket_count( $customer )	{

	$customer_id = $customer;

	if ( is_object( $customer ) )	{
		$customer_id = $customer->id;
	}

	if ( empty( $customer_id ) )	{
		return false;
	}

	$tickets = kbs_count_tickets( array( 'customer' => $customer_id ) );
	$count   = 0;

	if ( ! empty( $tickets ) )	{
		foreach( $tickets as $status )	{
			if ( ! empty( $status ) )	{
				$count += $status;
			}
		}
	}

	return $count;
} // kbs_get_customer_ticket_count

/**
 * Retrieve all customers.
 *
 * @since	1.0
 * @param	bool	$ids	True to return array of IDs, false for array of user objects
 * @return	mixed
 */
function kbs_get_customers( $ids = false )	{
	$role = array( 'support_customer' );
	$role = apply_filters( 'kbs_customer_roles', $role );

	$customers = kbs_get_users_by_role( $role, $ids );
	
	return apply_filters( 'kbs_customers', $customers );
} // kbs_get_customers

/**
 * Counts the total number of customers.
 *
 * @since	1.0
 * @return	int		The total number of customers.
 */
function kbs_count_total_customers( $args = array() ) {
	return KBS()->customers->count( $args );
} // kbs_count_total_customers

/**
 * Whether or not customers can reopen a ticket.
 *
 * @since	1.0
 * @return	bool	True if they can re-open tickets, otherwise false
 */
function kbs_customers_can_repoen_tickets() {
    $can_reopen = kbs_get_option( 'customer_can_repoen' );
    $can_reopen = apply_filters( 'kbs_customers_can_repoen_tickets', $can_reopen );

    return (bool) $can_reopen;
} // kbs_customers_can_repoen_tickets

/**
 * Whether or not a specific customer can reopen a ticket.
 *
 * This function allows devs to perform further functions before retuning an answer.
 *
 * @since	1.0
 * @param   int|str The customer ID or email address
 * @param   int     The ticket ID
 * @return	bool	True if they can re-open the ticket, otherwise false
 */
function kbs_customer_can_repoen_ticket( $id_or_email, $ticket_id ) {
    $customer   = new KBS_Customer( $id_or_email );
    $can_reopen = kbs_customers_can_repoen_tickets();
    $can_reopen = apply_filters( 'kbs_customer_can_repoen_ticket', $can_reopen, $customer, $ticket_id );

    return $can_reopen;
} // kbs_customer_can_repoen_ticket

