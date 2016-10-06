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
 * @since	1.0
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
 * @since	1.0
 * @return	bool	True when KBS AJAX is disabled, false otherwise.
 */
function kbs_is_ajax_disabled() {
	$retval = ! kbs_get_option( 'enable_ajax_ticket' );
	return apply_filters( 'kbs_is_ajax_disabled', $retval );
} // kbs_is_ajax_disabled

/**
 * Get AJAX URL
 *
 * @since	1.0
 * @return	str		URL to the AJAX file to call during AJAX requests.
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

/**
 * Reply to a ticket.
 *
 * @since	1.0
 * @return	void
 */
function kbs_ajax_insert_ticket_reply()	{

	$ticket = new KBS_Ticket( $_POST['ticket_id'] );

	$reply_data = array(
		'ticket_id'   => $_POST['ticket_id'],
		'response'    => $_POST['response'],
		'close'       => $_POST['close_ticket'],
		'customer_id' => $ticket->customer_id,
		'agent'       => $ticket->agent,
		'key'         => $ticket->key
	);

	$reply_id = $ticket->add_reply( $reply_data );

	wp_send_json( array( 'reply_id' => $reply_id ) );

} // kbs_ajax_reply_to_ticket
add_action( 'wp_ajax_kbs_insert_ticket_reply', 'kbs_ajax_insert_ticket_reply' );

/**
 * Display replies for ticket post metabox.
 *
 * @since	1.0
 * @return	str
 */
function kbs_ajax_display_ticket_replies()	{
	$output = '';

	if ( ! empty( $_POST['kbs_reply_id'] ) )	{
		$output .= kbs_ticket_get_reply_html( $_POST['kbs_reply_id'], $_POST['kbs_ticket_id'] );
	} else	{

		$replies  = kbs_get_ticket_replies( $_POST['kbs_ticket_id'] );
	
		if ( ! empty( $replies ) )	{
			foreach( $replies as $reply )	{
				$output .= kbs_ticket_get_reply_html( $reply, $_POST['kbs_ticket_id'] );
			}
		}

	}

	echo $output;
	die();
} // kbs_ajax_display_ticket_replies
add_action( 'wp_ajax_kbs_display_ticket_replies', 'kbs_ajax_display_ticket_replies' );

/**
 * Adds a note to a ticket.
 *
 * @since	1.0
 * @return	void
 */
function kbs_ajax_ticket_insert_note()	{
	$note_id = kbs_ticket_insert_note( $_POST['ticket_id'], $_POST['note_content'] );

	wp_send_json( array( 'note_id' => $note_id ) );
} // kbs_ajax_ticket_insert_note
add_action( 'wp_ajax_kbs_insert_ticket_note', 'kbs_ajax_ticket_insert_note' );

/**
 * Display notes for ticket post metabox.
 *
 * @since	1.0
 * @return	str
 */
function kbs_ajax_display_ticket_notes()	{
	$output = '';

	if ( ! empty( $_POST['kbs_note_id'] ) )	{
		$output .= kbs_ticket_get_note_html( $_POST['kbs_note_id'], $_POST['kbs_ticket_id'] );
	} else	{

		$notes  = kbs_ticket_get_notes( $_POST['kbs_ticket_id'] );
	
		if ( ! empty( $notes ) )	{
			foreach( $notes as $note )	{
				$output .= kbs_ticket_get_note_html( $note, $_POST['kbs_ticket_id'] );
			}
		}

	}

	echo $output;
	die();
} // kbs_ajax_display_ticket_notes
add_action( 'wp_ajax_kbs_display_ticket_notes', 'kbs_ajax_display_ticket_notes' );

/**
 * Adds a new field to a form.
 *
 * @since	1.0
 * @return	void
 */
function kbs_ajax_add_form_field()	{

	if ( ! empty( $_POST['form_id'] ) )	{
		$form     = new KBS_Form( $_POST['form_id'] );
		$field_id = $form->add_field( $_POST );
	}

	if ( ! empty( $field_id ) )	{
		$results['id']      = $field_id;
		$results['message'] = 'field_added';
	} else	{
		$results['message'] = 'field_add_fail';
	}
	
	wp_send_json( $results );

} // kbs_ajax_add_form_field
add_action( 'wp_ajax_kbs_add_form_field', 'kbs_ajax_add_form_field' );

/**
 * Updates a field.
 *
 * @since	1.0
 * @return	void
 */
function kbs_ajax_save_form_field()	{

	if ( ! empty( $_POST['field_id'] ) )	{
		$form     = new KBS_Form( $_POST['form_id'] );
		$field_id = $form->save_field( $_POST );
	}

	if ( ! empty( $field_id ) )	{
		$results['id']      = $field_id;
		$results['message'] = 'field_saved';
	} else	{
		$results['message'] = 'field_save_fail';
	}
	
	wp_send_json( $results );

} // kbs_ajax_save_form_field
add_action( 'wp_ajax_kbs_save_form_field', 'kbs_ajax_save_form_field' );

/**
 * Sets the order of the form fields.
 *
 * @since	1.0
 * @return	void
 */
function kbs_ajax_order_form_fields()	{
	
	foreach( $_POST['fields'] as $order => $id )	{
		wp_update_post( array(
			'ID'			=> $id,
			'menu_order'	=> $order++
		) );
	}
}
add_action( 'wp_ajax_kbs_order_form_fields', 'kbs_ajax_order_form_fields' );

/**
 * Validate a ticket submission form.
 *
 * @since	1.0
 * @return	void
 */
function kbs_ajax_validate_form_submission()	{

	$form           = new KBS_Form( $_POST['kbs_form_id'] );
	$error          = false;
	$agree_to_terms = kbs_get_option( 'show_agree_to_terms', false );

	if ( kbs_user_must_be_logged_in() && ! is_user_logged_in() )	{
		wp_send_json( array(
			'error' => kbs_get_notices( 'need_login', true ),
			'field' => 'kbs_empty_field'
		) );
	}

	foreach ( $form->get_fields() as $field )	{

		$settings = $form->get_field_settings( $field->ID );

		if ( ! empty( $settings['required'] ) && empty( $_POST[ $field->post_name ] ) )	{

			$error = kbs_form_submission_errors( $field->ID, 'required' );
			$field = $field->post_name;

		} elseif ( 'email' == $settings['type'] || 'customer_email' == $settings['mapping'] )	{

			if ( ! is_email( $_POST[ $field->post_name ] ) )	{
				$error = kbs_form_submission_errors( $field->ID, 'invalid_email' );
				$field = $field->post_name;
			}

		}

		if ( $error )	{
			wp_send_json( array(
				'error' => $error,
				'field' => $field
			) );
		}

	}

	if ( $agree_to_terms && empty( $_POST['kbs_agree_terms'] ) )	{
		wp_send_json( array(
			'error' => kbs_form_submission_errors( 0, 'agree_to_terms' ),
			'field' => 'kbs_agree_terms'
		) );
	}

	/**
	 * Allow plugins to perform additional validation.
	 *
	 * @since	1.0
	 */
	do_action( 'kbs_validate_form_submission', $form );

	wp_send_json_success( array( 'error' => $error ) );

} // kbs_ajax_validate_form_submission
add_action( 'wp_ajax_kbs_validate_ticket_form', 'kbs_ajax_validate_form_submission' );
add_action( 'wp_ajax_nopriv_kbs_validate_ticket_form', 'kbs_ajax_validate_form_submission' );
