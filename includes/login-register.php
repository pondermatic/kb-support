<?php
/**
 * Login / Register Functions
 *
 * @package     KBS
 * @subpackage  Functions/Login
 * @copyright   Copyright (c) 2016, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Login Form
 *
 * @since	0.1
 * @global	$post
 * @param	str		$redirect	Redirect page URL
 * @return	str		Login form
 */
function kbs_login_form( $redirect = '' ) {
	global $kbs_login_redirect;

	if ( empty( $redirect ) ) {
		$redirect = kbs_get_current_page_url();
	}

	$kbs_login_redirect = $redirect;

	ob_start();

	kbs_get_template_part( 'shortcode', 'login' );

	return apply_filters( 'kbs_login_form', ob_get_clean() );
} // kbs_login_form

/**
 * Registration Form
 *
 * @since	0.1
 * @global	$post
 * @param	str		$redirect	Redirect page URL
 * @return	str		Login form
 */
function kbs_register_form( $redirect = '' ) {
	global $kbs_register_redirect;

	if ( empty( $redirect ) ) {
		$redirect = kbs_get_current_page_url();
	}

	$kbs_register_redirect = $redirect;

	ob_start();

	if( ! is_user_logged_in() ) {
		kbs_get_template_part( 'shortcode', 'register' );
	}

	return apply_filters( 'kbs_register_form', ob_get_clean() );
} // kbs_register_form

/**
 * Process Login Form
 *
 * @since	0.1
 * @param	arr		$data	Data sent from the login form
 * @return void
 */
function kbs_process_login_form( $data ) {
	if ( wp_verify_nonce( $data['kbs_login_nonce'], 'kbs-login-nonce' ) ) {
		$user_data = get_user_by( 'login', $data['kbs_user_login'] );

		if ( ! $user_data ) {
			$user_data = get_user_by( 'email', $data['kbs_user_login'] );
		}

		if ( $user_data ) {

			$user_ID = $user_data->ID;
			$user_email = $user_data->user_email;
			if ( wp_check_password( $data['kbs_user_pass'], $user_data->user_pass, $user_data->ID ) ) {
				kbs_log_user_in( $user_data->ID, $data['kbs_user_login'], $data['kbs_user_pass'] );
			} else {
				$message = 'password_incorrect';
			}

		} else {

			$message = 'username_incorrect';

		}

		if ( ! empty( $message ) )	{
			$url = remove_query_arg( 'message' );
			wp_redirect( add_query_arg( 'message', $message, $url ) );
			die();
		}

		$redirect = apply_filters( 'kbs_login_redirect', $data['kbs_redirect'], $user_ID );
		wp_redirect( $redirect );
		die();

	}
} // kbs_process_login_form
add_action( 'kbs_user_login', 'kbs_process_login_form' );

/**
 * Log User In
 *
 * @since	0.1
 * @param	int		$user_id	User ID
 * @param	str		$user_login Username
 * @param	str		$user_pass	Password
 * @return	void
 */
function kbs_log_user_in( $user_id, $user_login, $user_pass ) {

	if ( $user_id < 1 )	{
		return;
	}

	wp_set_auth_cookie( $user_id );
	wp_set_current_user( $user_id, $user_login );
	do_action( 'wp_login', $user_login, get_userdata( $user_id ) );
	do_action( 'kbs_log_user_in', $user_id, $user_login, $user_pass );

} // kbs_log_user_in


/**
 * Process Register Form
 *
 * @since	0.1
 * @param	arr		$data	Data sent from the register form
 * @return	void
*/
function kbs_process_register_form( $data ) {

	if( is_user_logged_in() ) {
		return;
	}

	if( empty( $_POST['kbs_register_submit'] ) ) {
		return;
	}

	do_action( 'kbs_pre_process_register_form' );

	if( empty( $data['kbs_user_login'] ) ) {
		$message = 'empty_username';
	} elseif( username_exists( $data['kbs_user_login'] ) ) {
		$message = 'username_unavailable';
	} elseif( ! validate_username( $data['kbs_user_login'] ) ) {
		$message = 'username_invalid';
	} elseif( email_exists( $data['kbs_user_email'] ) ) {
		$message = 'email_unavailable';
	} elseif( empty( $data['kbs_user_email'] ) || ! is_email( $data['kbs_user_email'] ) ) {
		$message = 'email_invalid';
	} elseif( empty( $_POST['kbs_user_pass'] ) ) {
		$message = 'empty_password';
	} elseif( ( ! empty( $_POST['kbs_user_pass'] ) && empty( $_POST['kbs_user_pass2'] ) ) || ( $_POST['kbs_user_pass'] !== $_POST['kbs_user_pass2'] ) ) {
		$message = 'password_mismatch';
	} else	{
		
	}

	do_action( 'kbs_process_register_form' );

	if ( ! empty( $message ) )	{
		$url = remove_query_arg( 'message' );
		wp_redirect( add_query_arg( 'message', $message, $url ) );
		die();
	}

	$redirect = apply_filters( 'kbs_register_redirect', $data['kbs_redirect'] );

	kbs_register_and_login_new_user( array(
		'user_login'      => $data['kbs_user_login'],
		'user_pass'       => $data['kbs_user_pass'],
		'user_email'      => $data['kbs_user_email'],
		'user_registered' => date( 'Y-m-d H:i:s' ),
		'role'            => get_option( 'default_role' )
	) );

	wp_redirect( $redirect );
	die();

} // kbs_process_register_form
add_action( 'kbs_user_register', 'kbs_process_register_form' );
