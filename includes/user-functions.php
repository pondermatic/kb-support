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
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * 
 *
 *
 *
 *
 */

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

