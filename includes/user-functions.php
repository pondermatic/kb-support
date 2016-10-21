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
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Retrieve customer tickets
 *
 * @since	1.0
 * @param	int|obj		$customer	The customer ID or a KBS_Customer object.
 * @param	arr			$args		Args that can be passed to kbs_get_tickets()
 * @param	bool		$can_select	True to only return selectable status. False for all.
 * @return	obj			Array of customer ticket objects.
 */
function kbs_get_customer_tickets( $customer, $args = array(), $can_select = true )	{

	$customer_id = $customer;

	if ( is_object( $customer ) )	{
		$customer_id = $customer->id;
	}

	if ( empty( $customer_id ) )	{
		return false;
	}

	$ticket_statuses = kbs_get_ticket_statuses( $can_select );

	$defaults = array(
		'customer' => $customer_id,
		'status'   => array_keys( $ticket_statuses ),
		'number'   => 10
	);

	$args = wp_parse_args( $args, $defaults );

	return kbs_get_tickets( $args );

} // kbs_get_customer_tickets

/**
 * Retrieve users by role.
 *
 * @since	1.0
 * @param	str	$role	Name of the role to retrieve.
 * @return	mixed
 */
function kbs_get_users_by_role( $role = array( 'support_agent', 'support_manager' ) )	{
	global $wpdb;

	$user_query = new WP_User_Query( array(
		'orderby'    => 'display_name',
		'role__in' => $role
	) );
	
	$users = $user_query->get_results();
	
	return $users;
} // kbs_get_users_by_role

/**
 * Retrieve all customers.
 *
 * @since	1.0
 * @param
 * @return	mixed
 */
function kbs_get_customers()	{
	$role = array( 'support_customer' );
	$role = apply_filters( 'kbs_customer_roles', $role );

	$customers = kbs_get_users_by_role( $role );
	
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
 * Retrieve all agents.
 *
 * @since	1.0
 * @param
 * @return	mixed
 */
function kbs_get_agents()	{
	$role  = array( 'support_agent', 'support_manager' );

	if ( kbs_get_option( 'admin_agents', false ) )	{
		$role[] = 'administrator';
	}

	$users = kbs_get_users_by_role( $role );
	
	return $users;
} // kbs_get_agents

/**
 * Validate a potential username
 *
 * @access      public
 * @since       1.0
 * @param       str		$username	The username to validate
 * @return      bool
 */
function kbs_validate_username( $username ) {
	$sanitized = sanitize_user( $username, false );
	$valid     = ( $sanitized == $username );

	return (bool) apply_filters( 'kbs_validate_username', $valid, $username );
} // kbs_validate_username

/**
 * Attach the newly created user_id to a customer, if one exists
 *
 * @since	1.0
 * @param 	int		$user_id	The User ID that was created
 * @return	void
 */
function kbs_connect_existing_customer_to_new_user( $user_id ) {
	$email = get_the_author_meta( 'user_email', $user_id );

	// Update the user ID on the customer
	$customer = new KBS_Customer( $email );

	if( $customer->id > 0 ) {
		$customer->update( array( 'user_id' => $user_id ) );
	}
}
add_action( 'user_register', 'kbs_connect_existing_customer_to_new_user', 10, 1 );
