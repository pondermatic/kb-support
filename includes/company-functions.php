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
 * Whether or not any companies exist.
 *
 * @since	1.0
 * @return	bool
 */
function kbs_has_companies()	{
	$companies = KBS()->companies->get_companies( array(
		'number' => 1
	) );

	if ( ! empty( $companies ) )	{
		return true;
	}

	return false;
} // kbs_has_companies

/**
 * Reretrieve company name.
 *
 * @since	1.0
 * @param	int		$id		Company ID
 * @return	str		Company name or false if not found
 */
function kbs_get_company_name( $id )	{
	$company = KBS()->companies->get_company_by( 'id', $id );

	if ( $company )	{
		return $company->name;
	}

	return false;
} // kbs_get_company_name
