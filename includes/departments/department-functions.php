<?php
/**
 * Functions for departments
 *
 * @package     KBS
 * @subpackage  Functions/Departments
 * @copyright   Copyright (c) 2017, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.2
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Whether or not departments are enabled.
 * @since	1.2
 * @return	bool
 */
function kbs_departments_enabled()	{
	return kbs_get_option( 'enable_departments', false );
} // kbs_departments_enabled

/**
 * Retrieve all departments.
 *
 * Simple wrapper for get_terms()
 *
 * @since	1.2
 * @param	array	$args		See WP_Term_Query::__construct
 * @return	array|	Array of department WP_Term objects or false if no departments exist
 */
function kbs_get_departments( $args = array() )	{
	$defaults = array(
		'hide_empty' => false
	);

	$args = wp_parse_args( $args, $defaults );
	$args['taxonomy'] = 'department';

	$departments = get_terms( $args );

	if ( empty( $departments ) || is_wp_error( $departments ) )	{
		return false;
	}

	return $departments;
} // kbs_get_departments

/**
 * Retrieve all department agents.
 *
 * @since	1.2
 * @param	int		$department_id	The department (term) ID
 * @return	array	Array of department member ID's
 */
function kbs_get_department_agents( $department_id = '' )	{

	$agents = array();

	if ( ! empty( $department_id ) )	{
		$agents = get_term_meta( $department_id, 'kbs_department_agents', true );
		if ( ! $agents )	{
			$agents = array();
		}
	}

	return $agents;

} // kbs_get_department_agents

/**
 * Retrieve the count of a departments agents.
 *
 * @since	1.2
 * @param	int		$department_id	The department (term) ID
 * @return	int		Count of agents
 */
function kbs_get_department_agent_count( $department_id )	{
	return count( kbs_get_department_agents( $department_id ) );
} // kbs_get_department_agent_count
