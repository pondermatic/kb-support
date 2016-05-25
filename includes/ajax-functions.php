<?php
/**
 * AJAX Functions
 *
 * Process the front-end AJAX actions.
 *
 * @package     KBS
 * @subpackage  Functions/AJAX
 * @copyright   Copyright (c) 2016, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Check if AJAX works as expected
 *
 * @since	0.1
 * @return	bool	True if AJAX works, false otherwise
 */
function kbs_test_ajax_works() {

	// Check if the Airplane Mode plugin is installed
	if ( class_exists( 'Airplane_Mode_Core' ) ) {

		$airplane = Airplane_Mode_Core::getInstance();

		if ( method_exists( $airplane, 'enabled' ) ) {

			if ( $airplane->enabled() ) {
				return true;
			}

		} else {

			if ( $airplane->check_status() == 'on' ) {
				return true;
			}
		}
	}

	add_filter( 'block_local_requests', '__return_false' );

	if ( get_transient( '_kbs_ajax_works' ) ) {
		return true;
	}

	$params = array(
		'sslverify'  => false,
		'timeout'    => 30,
		'body'       => array(
			'action' => 'kbs_test_ajax'
		)
	);

	$ajax  = wp_remote_post( kbs_get_ajax_url(), $params );
	$works = true;

	if ( is_wp_error( $ajax ) ) {

		$works = false;

	} else {

		if( empty( $ajax['response'] ) ) {
			$works = false;
		}

		if( empty( $ajax['response']['code'] ) || 200 !== (int) $ajax['response']['code'] ) {
			$works = false;
		}

		if( empty( $ajax['response']['message'] ) || 'OK' !== $ajax['response']['message'] ) {
			$works = false;
		}

		if( ! isset( $ajax['body'] ) || 0 !== (int) $ajax['body'] ) {
			$works = false;
		}

	}

	if ( $works ) {
		set_transient( '_kbs_ajax_works', '1', DAY_IN_SECONDS );
	}

	return $works;
} // kbs_test_ajax_works

/**
 * Checks whether AJAX is disabled.
 *
 * @since	0.1
 * @return	bool	True when KBS AJAX is disabled, false otherwise.
 */
function kbs_is_ajax_disabled() {
	$retval = ! kbs_get_option( 'enable_ajax_ticket' );
	return apply_filters( 'kbs_is_ajax_disabled', $retval );
}


/**
 * Get AJAX URL
 *
 * @since	0.1
 * @return	0.1		URL to the AJAX file to call during AJAX requests.
*/
function kbs_get_ajax_url() {
	$scheme = defined( 'FORCE_SSL_ADMIN' ) && FORCE_SSL_ADMIN ? 'https' : 'admin';

	$current_url = kbs_get_current_page_url();
	$ajax_url    = admin_url( 'admin-ajax.php', $scheme );

	if ( preg_match( '/^https/', $current_url ) && ! preg_match( '/^https/', $ajax_url ) ) {
		$ajax_url = preg_replace( '/^http/', 'https', $ajax_url );
	}

	return apply_filters( 'kbs_ajax_url', $ajax_url );
} // kbs_get_ajax_url
