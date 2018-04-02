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
		add_settings_error(
			'kbs-notices',
			'kbs-ticket-reply-added',
			__( 'The reply was successfully added.', 'kb-support' ),
			'updated'
		);
	}

	if ( isset( $_GET['kbs-message'] ) && 'ticket_reply_added_closed' == $_GET['kbs-message'] )	{
		$closed = '';

		$create_article_link = add_query_arg( array(
			'kbs-action' => 'create_article',
			'ticket_id'  => $_GET['kbs_ticket_id']
		), admin_url() );

		$create_article_link = apply_filters( 'kbs_create_article_link', $create_article_link, $_GET['kbs_ticket_id'] );

		$closed = sprintf( __( ' and the %1$s was closed.', 'kb-support' ), kbs_get_ticket_label_singular( true ) );
		$closed .= ' ';
		$closed .= sprintf( __( 'Create <a href="%s">%s</a>', 'kb-support' ), $create_article_link, kbs_get_article_label_singular() );

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
 * Request 5 star rating after 25 closed tickets.
 *
 * After 25 closed tickets we ask the admin for a 5 star rating
 *
 * @since	1.3
 * @return	void
 */
function kbs_request_wp_5star_rating() {

	// Do not show message if installed in an SAAS environment
	if ( defined( 'KBS_SAAS' ) && true === KBS_SAAS )	{
		return ;
	}				

	// Only show this message to admins
	if ( ! current_user_can( 'administrator' ) )	{
		return;
	}

	// Only show the notice on the plugin pages
	if ( ! kbs_is_admin_page() )	{
		return;
	}
	
	// If notice has been dismissed, return since everything else after this is expensive operations!
	if ( kbs_is_notice_dismissed( 'kbs_request_wp_5star_rating' ) )	{
		return ;
	}

	// How many tickets have been closed?
	$closed_tickets = kbs_get_tickets( array( 'status' => 'closed', 'number' => 25, 'output' => 'posts', 'fields' => 'ids' ) );
	
	// Show notice if number of closed tickets greater than 25.
	if ( count ( $closed_tickets ) >= 25 ) {
	
		WPAS()->admin_notices->add_notice( 'updated', 'kbs_request_wp_5star_rating', wp_kses( sprintf( __( 'Wow! It looks like you have closed a lot of tickets which is pretty awesome! We guess you must really like Awesome Support, huh? Could you please do us a favor and leave a 5 star rating on WordPress? It will only take a minute and helps to motivate our developers and volunteers. <a href="%1$s">Yes, you deserve it!</a>.', 'awesome-support' ), 'https://wordpress.org/support/plugin/awesome-support/reviews/' ) , 
		array( 'strong' => array(), 'a' => array( 'href' => array() ) ) ) );

	}
} // kbs_request_wp_5star_rating

/**
 * Retrieve all dismissed notices.
 *
 * @since	1.3
 * @return	array	Array of dismissed notices
 */
function kbs_dismissed_notices() {

	global $current_user;

	$user_notices = (array) get_user_option( 'kbs_dismissed_notices', $current_user->ID );

	return $user_notices;

} // kbs_dismissed_notices

/**
 * Check if a specific notice has been dismissed.
 *
 * @since	1.3
 * @param	string	$notice	Notice to check
 * @return	bool	Whether or not the notice has been dismissed
 */
function kbs_is_notice_dismissed( $notice ) {

	$dismissed = kbs_dismissed_notices();

	if ( array_key_exists( $notice, $dismissed ) ) {
		return true;
	} else {
		return false;
	}

} // kbs_is_notice_dismissed

/**
 * Dismiss a notice.
 *
 * @since	1.3
 * @param	string		$notice	Notice to dismiss
 * @return	bool|int	True on success, false on failure, meta ID if it didn't exist yet
 */
function kbs_dismiss_notice( $notice ) {

	global $current_user;

	$dismissed_notices = $new = (array) kbs_dismissed_notices();

	if ( ! array_key_exists( $notice, $dismissed_notices ) ) {
		$new[ $notice ] = 'true';
	}

	$update = update_user_option( $current_user->ID, 'kbs_dismissed_notices', $new );

	return $update;

} // kbs_dismiss_notice

/**
 * Restore a dismissed notice.
 *
 * @since	1.3
 * @param	string		$notice	Notice to restore
 * @return	bool|int	True on success, false on failure, meta ID if it didn't exist yet
 */
function kbs_restore_notice( $notice ) {

	global $current_user;

	$dismissed_notices = (array) kbs_dismissed_notices();

	if ( array_key_exists( $notice, $dismissed_notices ) ) {
		unset( $dismissed_notices[ $notice ] );
	}

	$update = update_user_option( $current_user->ID, 'kbs_dismissed_notices', $dismissed_notices );

	return $update;

} // kbs_restore_notice

/**
 * Check if there is a notice to dismiss.
 *
 * @since	1.3
 * @param	array	$data	Contains the notice ID
 * @return	void
 */
function kbs_grab_notice_dismiss( $data ) {

	$notice_id = isset( $data['notice_id'] ) ? $data['notice_id'] : false;

	if ( false === $notice_id ) {
		return;
	}

	kbs_dismiss_notice( $notice_id );

} // kbs_grab_notice_dismiss
add_action( 'kbs_do_dismiss_notice', 'kbs_grab_notice_dismiss' );

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
