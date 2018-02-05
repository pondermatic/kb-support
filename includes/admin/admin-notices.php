<?php
/**
 * Admin Notices
 *
 * @package     KBS
 * @subpackage  Admin/Notices
 * @copyright   Copyright (c) 2017, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Admin Messages
 *
 * @since	0.1
 * @global	$kbs_options Array of all the KBS Options
 * @return	void
 */
function kbs_admin_messages() {
	global $kbs_options;

	if ( isset( $_GET['kbs-message'] ) && 'nonce_fail' == $_GET['kbs-message'] )	{
		add_settings_error(
			'kbs-notices',
			'kbs-nonce-fail',
			__( 'Security verification failed.', 'kb-support' ),
			'error'
		);
	}

	if ( isset( $_GET['kbs-message'] ) && 'ticket_reopened' == $_GET['kbs-message'] )	{
		add_settings_error(
			'kbs-notices',
			'kbs-ticket-reopened',
			sprintf( __( '%s reopened.', 'kb-support' ), kbs_get_ticket_label_singular() ),
			'updated'
		);
	}

	if ( isset( $_GET['kbs-message'] ) && 'ticket_not_closed' == $_GET['kbs-message'] )	{
		add_settings_error(
			'kbs-notices',
			'kbs-ticket-not-closed',
			sprintf( __( 'The %s cannot be re-opened. It is not closed.', 'kb-support' ), kbs_get_ticket_label_singular() ),
			'error'
		);
	}

	if ( isset( $_GET['kbs-message'] ) && 'ticket_reply_added' == $_GET['kbs-message'] )	{
		$closed = '';
		if ( isset( $_GET['post'] ) && 'kbs_ticket' == get_post_type( $_GET['post'] ) && 'closed' == get_post_status( $_GET['post'] ) )	{

			$create_article_link = add_query_arg( array(
				'kbs-action' => 'create_article',
				'ticket_id'  => $_GET['post']
			), admin_url() );

			$create_article_link = apply_filters( 'kbs_create_article_link', $create_article_link, $_GET['post'] );

			$closed = sprintf( __( ' and the %1$s was closed.', 'kb-support' ), kbs_get_ticket_label_singular() );
			$closed .= ' ';
			$closed .= sprintf( __( 'Create <a href="%s">%s</a>', 'kb-support' ), $create_article_link, kbs_get_article_label_singular() );
		}

		add_settings_error(
			'kbs-notices',
			'kbs-ticket-reply-added',
			sprintf( __( 'The reply was successfully added%s.', 'kb-support' ), $closed ),
			'updated'
		);
	}

	if ( isset( $_GET['kbs-message'] ) && 'ticket_reply_failed' == $_GET['kbs-message'] )	{
		add_settings_error(
			'kbs-notices',
			'kbs-ticket-reply-failed',
			__( 'The reply could not be added.', 'kb-support' ),
			'error'
		);
	}

	if ( isset( $_GET['kbs-message'] ) && 'note_deleted' == $_GET['kbs-message'] )	{
		add_settings_error(
			'kbs-notices',
			'kbs-ticket-note-deleted',
			__( 'The note was deleted.', 'kb-support' ),
			'updated'
		);
	}

	if ( isset( $_GET['kbs-message'] ) && 'note_not_deleted' == $_GET['kbs-message'] )	{
		add_settings_error(
			'kbs-notices',
			'kbs-ticket-note-not-deleted',
			__( 'The note could not be deleted.', 'kb-support' ),
			'error'
		);
	}

	if ( isset( $_GET['kbs-message'] ) && 'field_added' == $_GET['kbs-message'] )	{
		add_settings_error(
			'kbs-notices',
			'kbs-field-added',
			__( 'Field was added.', 'kb-support' ),
			'updated'
		);
	}

	if ( isset( $_GET['kbs-message'] ) && 'field_add_fail' == $_GET['kbs-message'] )	{
		add_settings_error(
			'kbs-notices',
			'kbs-field-notadded',
			__( 'Field added.', 'kb-support' ),
			'error'
		);
	}

	if ( isset( $_GET['kbs-message'] ) && 'field_saved' == $_GET['kbs-message'] )	{
		add_settings_error(
			'kbs-notices',
			'kbs-field-saved',
			__( 'Field updated.', 'kb-support' ),
			'updated'
		);
	}

	if ( isset( $_GET['kbs-message'] ) && 'field_save_fail' == $_GET['kbs-message'] )	{
		add_settings_error(
			'kbs-notices',
			'kbs-field-notsaved',
			__( 'Field not saved.', 'kb-support' ),
			'error'
		);
	}

	if ( isset( $_GET['kbs-message'] ) && 'field_deleted' == $_GET['kbs-message'] )	{
		add_settings_error(
			'kbs-notices',
			'kbs-field-delete',
			__( 'Field deleted.', 'kb-support' ),
			'updated'
		);
	}

	if ( isset( $_GET['kbs-message'] ) && 'field_delete_fail' == $_GET['kbs-message'] )	{
		add_settings_error(
			'kbs-notices',
			'kbs-field-notdeleted',
			__( 'Field not deleted.', 'kb-support' ),
			'error'
		);
	}

	if ( isset( $_GET['kbs-message'], $_GET['field_id'] ) && 'editing_field' == $_GET['kbs-message'] )	{
		echo '<div class="notice notice-info">';
		echo '<p><strong>' .
				sprintf( __( 'Editing: %s.', 'kb-support' ), get_the_title( $_GET['field_id'] ) ) .
			'</strong></p>';
		echo '</div>';
	}

	if ( isset( $_GET['kbs-message'] ) && 'customer_created' == $_GET['kbs-message'] )	{
		add_settings_error(
			'kbs-notices',
			'kbs-customer-added',
			__( 'Customer added successfully.', 'kb-support' ),
			'updated'
		);
	}

	if ( isset( $_GET['kbs-message'] ) && 'customer_list_permission' == $_GET['kbs-message'] )	{
		add_settings_error(
			'kbs-notices',
			'kbs-customer-list-permission',
			__( 'You do not have permission to view the customer list.', 'kb-support' ),
			'error'
		);
	}

	if ( isset( $_GET['kbs-message'] ) && 'invalid_customer_id' == $_GET['kbs-message'] )	{
		add_settings_error(
			'kbs-notices',
			'kbs-invalid-customer-id',
			__( 'An invalid customer ID was provided.', 'kb-support' ),
			'error'
		);
	}

	if ( isset( $_GET['kbs-message'] ) && 'email_added' == $_GET['kbs-message'] )	{
		add_settings_error(
			'kbs-notices',
			'kbs-customer-email-added',
			__( 'Email address added.', 'kb-support' ),
			'updated'
		);
	}

	if ( isset( $_GET['kbs-message'] ) && 'email_removed' == $_GET['kbs-message'] )	{
		add_settings_error(
			'kbs-notices',
			'kbs-customer-email-removed',
			__( 'Email address removed.', 'kb-support' ),
			'updated'
		);
	}

	if ( isset( $_GET['kbs-message'] ) && 'email_remove_failed' == $_GET['kbs-message'] )	{
		add_settings_error(
			'kbs-notices',
			'kbs-customer-email-remove-failed',
			__( 'Email address could not be removed.', 'kb-support' ),
			'error'
		);
	}

	if ( isset( $_GET['kbs-message'] ) && 'primary_email_updated' == $_GET['kbs-message'] )	{
		add_settings_error(
			'kbs-notices',
			'kbs-customer-email-primary-updated',
			__( 'Primary email address updated.', 'kb-support' ),
			'updated'
		);
	}

	if ( isset( $_GET['kbs-message'] ) && 'primary_email_failed' == $_GET['kbs-message'] )	{
		add_settings_error(
			'kbs-notices',
			'kbs-customer-email-primary-remove-failed',
			__( 'Primary email address could not be updated.', 'kb-support' ),
			'error'
		);
	}

	if ( isset( $_GET['kbs-message'] ) && 'customer_delete_no_confirm' == $_GET['kbs-message'] )	{
		add_settings_error(
			'kbs-notices',
			'kbs-customer-delete-no-confirm',
			__( 'Please confirm you wish to delete this customer.', 'kb-support' ),
			'error'
		);
	}

	if ( isset( $_GET['kbs-message'] ) && 'customer_deleted' == $_GET['kbs-message'] )	{
		add_settings_error(
			'kbs-notices',
			'kbs-customer-deleted',
			__( 'Customer deleted.', 'kb-support' ),
			'updated'
		);
	}

	if ( isset( $_GET['kbs-message'] ) && 'kbs_customer_delete_failed' == $_GET['kbs-message'] )	{
		add_settings_error(
			'kbs-notices',
			'kbs-customer-deleted-failed',
			__( 'Could not delete customer.', 'kb-support' ),
			'error'
		);
	}

	if ( isset( $_GET['kbs-message'] ) && 'kbs_customer_delete_invalid_id' == $_GET['kbs-message'] )	{
		add_settings_error(
			'kbs-notices',
			'kbs-customer-delete-invalid-id',
			__( 'A customer with the specified ID could not be found.', 'kb-support' ),
			'error'
		);
	}

	if ( isset( $_GET['kbs-message'] ) && 'disconnect_user' == $_GET['kbs-message'] )	{
		add_settings_error(
			'kbs-notices',
			'kbs-customer-disconnect-user',
			__( 'Customer disconnected from user ID.', 'kb-support' ),
			'updated'
		);
	}

	if ( isset( $_GET['kbs-message'] ) && 'disconnect_user_fail' == $_GET['kbs-message'] )	{
		add_settings_error(
			'kbs-notices',
			'kbs-customer-disconnect-user-failed',
			__( 'Could not disconnect customer from user ID.', 'kb-support' ),
			'error'
		);
	}

	if ( isset( $_GET['kbs-message'] ) && 'article_created' == $_GET['kbs-message'] )	{
		add_settings_error(
			'kbs-notices',
			'kbs-create-article-success',
			sprintf( __( 'Draft %1$s created from %2$s. Review, edit and publish the new %1$s below.', 'kb-support' ), kbs_get_article_label_singular( true ), kbs_get_ticket_label_singular( true ) ),
			'updated'
		);
	}

	if ( isset( $_GET['kbs-message'] ) && 'create_article_failed' == $_GET['kbs-message'] )	{
		add_settings_error(
			'kbs-notices',
			'kbs-create-article-failed',
			sprintf( __( 'Could not create new %1$s from %2$s.', 'kb-support' ), kbs_get_article_label_singular( true ), kbs_get_ticket_label_singular( true ) ),
			'error'
		);
	}

	if ( isset( $_GET['kbs-message'] ) && 'reset_article_views' == $_GET['kbs-message'] )	{
		add_settings_error(
			'kbs-notices',
			'kbs-reset-article-views',
			sprintf( __( 'View count reset for %s.', 'kb-support' ), kbs_get_article_label_singular() ),
			'updated'
		);
	}

	if ( isset( $_GET['kbs-message'] ) && 'reset_article_views_failed' == $_GET['kbs-message'] )	{
		add_settings_error(
			'kbs-notices',
			'kbs-reset-article-views-failed',
			sprintf( __( 'Failed to reset %s view count.', 'kb-support' ), kbs_get_article_label_singular() ),
			'error'
		);
	}

    if ( isset( $_GET['kbs-message'] ) && 'settings-imported' == $_GET['kbs-message'] )	{
		add_settings_error(
			'kbs-notices',
			'kbs-settings-imported',
			__( 'Settings imported.', 'kb-support' ),
			'updated'
		);
	}

    if ( isset( $_GET['kbs-message'] ) && 'settings-import-missing-file' == $_GET['kbs-message'] )	{
		add_settings_error(
			'kbs-notices',
			'kbs-settings-import-file-missing',
			__( 'Please upload a valid .json file.', 'kb-support' ),
			'error'
		);
	}

    if ( isset( $_GET['kbs-message'] ) && 'sequential-numbers-updated' == $_GET['kbs-message'] )	{
		add_settings_error(
			'kbs-notices',
			'kbs-sequential-numbers-updated',
			sprintf( __( '%s numbers have been successfully upgraded.', 'kb-support' ), kbs_get_ticket_label_singular() ),
			'updated'
		);
	}

	settings_errors( 'kbs-notices' );
}
add_action( 'admin_notices', 'kbs_admin_messages' );

/**
 * Admin Add-ons Notices
 *
 * @since	0.1
 * @return	void
*/
function kbs_admin_addons_notices() {
	add_settings_error( 'kbs-notices', 'kbs-addons-feed-error', __( 'There seems to be an issue with the server. Please try again in a few minutes.', 'kb-support' ), 'error' );
	settings_errors( 'kbs-notices' );
} // kbs_admin_addons_notices

/**
 * Dismisses admin notices when Dismiss links are clicked
 *
 * @since	0.1
 * @return	void
*/
function kbs_dismiss_notices() {

	if( ! is_user_logged_in() ) {
		return;
	}

	$notice = isset( $_GET['kbs_notice'] ) ? $_GET['kbs_notice'] : false;

	if( ! $notice )	{
		return;
	}

	update_user_meta( get_current_user_id(), '_kbs_' . $notice . '_dismissed', 1 );

	wp_redirect( remove_query_arg( array( 'kbs_action', 'kbs_notice' ) ) ); exit;

} // kbs_dismiss_notices
add_action( 'kbs_dismiss_notices', 'kbs_dismiss_notices' );
