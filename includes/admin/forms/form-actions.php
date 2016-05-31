<?php
/**
 * Form actions
 *
 * @package     KBS
 * @subpackage  Functions/Forms/Actions
 * @copyright   Copyright (c) 2016, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Delete's a form field.
 *
 * @since	0.1
 * @param	arr		$data	$_GET super global
 * @return	void
 */
function kbs_delete_field_action( $data )	{
	
	if ( ! isset( $data['kbs-action-nonce'] ) || ! wp_verify_nonce( $data['kbs-action-nonce'], 'delete_form_field' ) )	{
		return;
	}
	
	$url     = remove_query_arg( array( 'kbs-message', 'kbs-action', 'kbs_action_nonce', 'field_id' ) );
	$message = 'field_deleted';
	
	if ( ! kbs_delete_field( $data['field_id'] ) )	{
		$message = 'field_delete_fail';
	}

	wp_redirect(
		add_query_arg(
			'kbs-message', $message, $url
		)
	);

	die();

} // kbs_delete_field_action

add_action( 'kbs-delete_form_field', 'kbs_delete_field_action' );
