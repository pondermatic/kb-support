<?php
/**
 * Email Template
 *
 * @package     KBS
 * @subpackage  Emails
 * @copyright   Copyright (c) 2016, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) )
	exit;

/**
 * Gets all the email templates that have been registerd. The list is extendable
 * and more templates can be added.
 *
 * This is simply a wrapper to KBS_Email_Templates->get_templates()
 *
 * @since	0.1
 * @return	arr		$templates	All the registered email templates
 */
function kbs_get_email_templates() {
	$templates = new KBS_Emails;
	return $templates->get_templates();
} // kbs_get_email_templates

/**
 * Email Template Tags
 *
 * @since	0.1
 *
 * @param	str		$message		Message with the template tags
 * @param	arr		$ticket_data	Ticket Data
 * @param	int		$ticket_id		Ticket ID
 * @param	bool	$admin_notice	Whether or not this is a notification email
 *
 * @return	str		$message		Fully formatted message
 */
function kbs_email_template_tags( $message, $ticket_data, $ticket_id, $admin_notice = false ) {
	return kbs_do_email_tags( $message, $ticket_id );
} // kbs_email_template_tags

/**
 * Email Preview Template Tags
 *
 * @since	0.1
 * @param string $message Email message with template tags
 * @return string $message Fully formatted message
 */
function kbs_email_preview_template_tags( $message ) {

} // kbs_email_preview_template_tags

/**
 * Email Template Preview
 *
 * @since	0.1
 * @param
 */
function kbs_email_template_preview() {
	if( ! current_user_can( 'manage_ticket_settings' ) ) {
		return;
	}

	ob_start();
	?>
	<a href="<?php echo esc_url( add_query_arg( array( 'kbs_action' => 'preview_email' ), home_url() ) ); ?>" class="button-secondary" target="_blank" title="<?php printf( __( '%s Logged Preview', 'kb-support' ), kbs_get_ticket_label_singular() ); ?> "><?php printf( __( 'Preview %s Logged', 'kb-support' ), kbs_get_ticket_label_singular() ); ?></a>
	<a href="<?php echo wp_nonce_url( add_query_arg( array( 'kbs_action' => 'send_test_email' ) ), 'kbs-test-email' ); ?>" class="button-secondary"><?php _e( 'Send Test Email', 'kb-support' ); ?></a>
	<?php
	echo ob_get_clean();
} // kbs_email_template_preview
add_action( 'kbs_email_settings', 'kbs_email_template_preview' );

/**
 * Displays the email preview
 *
 * @since	0.1
 * @return	void
 */
function kbs_display_email_template_preview() {

	if( empty( $_GET['kbs_action'] ) ) {
		return;
	}

	if( 'preview_email' !== $_GET['kbs_action'] ) {
		return;
	}

	if( ! current_user_can( 'manage_ticket_settings' ) ) {
		return;
	}


	KBS()->emails->heading = sprintf( __( '%s Received', 'kb-support' ), kbs_get_ticket_label_singular() );

	echo KBS()->emails->build_email( kbs_email_preview_template_tags( kbs_get_email_body_content( 0, array() ) ) );

	exit;

} // kbs_display_email_template_preview
add_action( 'template_redirect', 'kbs_display_email_template_preview' );

/**
 * Email Template Body
 *
 * @since	0.1
 * @param	int 	$ticket_id		Ticket ID
 * @param	arr		$ticket_data	Ticket Data
 * @return	str		$email_body		Body of the email
 */
function kbs_get_email_body_content( $ticket_id = 0, $ticket_data = array() ) {
	$default_email_body = __( "Dear", "kb-support" ) . " {name},\n\n";
	$default_email_body .= sprintf( __( "Thank you for logging your %s.", "kb-support" ), kbs_get_ticket_label_singular( true ) ) . "\n\n";
	$default_email_body .= "{ticket_details}\n\n";
	$default_email_body .= "{sitename}";

	$email = kbs_get_option( 'ticket_content', false );
	$email = $email ? stripslashes( $email ) : $default_email_body;

	$email_body = apply_filters( 'kbs_email_template_wpautop', true ) ? wpautop( $email ) : $email;

	$email_body = apply_filters( 'kbs_ticket_content_' . KBS()->emails->get_template(), $email_body, $ticket_id, $ticket_data );

	return apply_filters( 'kbs_ticket_content', $email_body, $ticket_id, $ticket_data );
} // kbs_get_email_body_content

/**
 * Ticket Notification Template Body
 *
 * @since	0.1
 * @param	int		$ticket_id		Ticket ID
 * @param	arr		$ticket_data	Ticket Data
 * @return	str		$email_body		Body of the email
 */
function kbs_get_ticket_notification_body_content( $ticket_id = 0, $ticket_data = array() ) {
	$user_info = maybe_unserialize( $ticket_data['user_info'] );

	if ( isset( $user_info['id'] ) && $user_info['id'] > 0 ) {
		$user_data = get_userdata( $user_info['id'] );
		$name = $user_data->display_name;
	} elseif ( isset( $user_info['first_name'] ) && isset( $user_info['last_name'] ) ) {
		$name = $user_info['first_name'] . ' ' . $user_info['last_name'];
	} else {
		$name = $email;
	}

	$default_email_body = __( 'Hello', 'kb-support' ) . "\n\n" . sprintf( __( 'A Support %s has been received', 'kb-support' ), kbs_get_ticket_label_plural() ) . ".\n\n";
	$default_email_body .= sprintf( __( '%s details:', 'kb-support' ), kbs_get_ticket_label_singular() ) . "\n\n";
	$default_email_body .= /*$download_list . */"\n\n";
	$default_email_body .= __( 'Submitted by: ', 'kb-support' ) . " " . html_entity_decode( $name, ENT_COMPAT, 'UTF-8' ) . "\n";
	$default_email_body .= __( 'Thank you', 'kb-support' );

	$email = kbs_get_option( 'ticket_notification', false );
	$email = $email ? stripslashes( $email ) : $default_email_body;

	$email_body = kbs_email_template_tags( $email, $ticket_data, $ticket_id, true );
	$email_body = kbs_do_email_tags( $email, $ticket_id );

	$email_body = apply_filters( 'kbs_email_template_wpautop', true ) ? wpautop( $email_body ) : $email_body;

	return apply_filters( 'kbs_ticket_notification', $email_body, $ticket_id, $ticket_data );
} // kbs_get_ticket_notification_body_content
