<?php
/**
 * Company Functions
 *
 * Functions related to companies
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
 * Sanitize the company id.
 *
 * @since	1.0
 * @param	int		$value	The posted value
 * @return	int		The santized value
 */
function kbs_sanitize_company_id( $value = 0 )	{
	$value = absint( $value );

	if ( 'kbs_company' != get_post_type( $value ) )	{
		$value = 0;
	}

	return $value;
} // kbs_sanitize_company_id

/**
 * Whether or not any companies exist.
 *
 * @since	1.0
 * @return	bool
 */
function kbs_has_companies()	{
	$companies = get_posts( array(
		'post_type'      => 'kbs_company',
		'post_status'    => 'publish',
		'posts_per_page' => 1
	) );

	if ( ! empty( $companies ) )	{
		return true;
	}

	return false;
} // kbs_has_companies

/**
 * Counts the total number of companies.
 *
 * @since	1.0
 * @return	int		The total number of companies.
 */
function kbs_count_total_companies() {
	return wp_count_posts( 'kbs_company' )->publish;
} // kbs_count_total_companies

/**
 * Retrieve the company ticket count.
 *
 * @since	1.0
 * @param	obj|int		$company	Company WP_Post object or ID
 * @return	int			Count of tickets
 */
function kbs_count_company_tickets( $company )	{
	if ( ! is_numeric( $company ) )	{
		$company = $company->ID;
	}

	$tickets = kbs_count_tickets( array( 'company' => $company ) );
	$count   = 0;

	if ( ! empty( $tickets ) )	{
		foreach( $tickets as $status )	{
			if ( ! empty( $status ) )	{
				$count += $status;
			}
		}
	}

	return $count;
} // kbs_count_company_tickets

/**
 * Counts the total number of customers within a company.
 *
 * @since	1.0
 * @param	int		$company_id		The company ID
 * @return	int		The total number of customers in the company.
 */
function kbs_count_customers_in_company( $company_id = 0 ) {
	global $wpdb;

	if ( empty( $company_id ) )	{
		return false;
	}

	return KBS()->customers->count( array( 'company_id' => $company_id ) );
} // kbs_count_customers_in_company

/**
 * Retrieve company name.
 *
 * @since	1.0
 * @param	obj|int		$company		WP_Post object or ID
 * @return	str			Company name or false if not found
 */
function kbs_get_company_name( $company )	{
	return get_the_title( $company );
} // kbs_get_company_name
