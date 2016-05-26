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
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Gets all the email templates that have been registerd. The list is extendable
 * and more templates can be added.
 *
 * This is simply a wrapper to EDD_Email_Templates->get_templates()
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
 * @param	arr		$ticket_data	Payment Data
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
	$download_list = '<ul>';
	$download_list .= '<li>' . __( 'Sample Product Title', 'kb-support' ) . '<br />';
	$download_list .= '<div>';
	$download_list .= '<a href="#">' . __( 'Sample Download File Name', 'kb-support' ) . '</a> - <small>' . __( 'Optional notes about this download.', 'kb-support' ) . '</small>';
	$download_list .= '</div>';
	$download_list .= '</li>';
	$download_list .= '</ul>';

	$file_urls = esc_html( trailingslashit( get_site_url() ) . 'test.zip?test=key&key=123' );

	$price = edd_currency_filter( edd_format_amount( 10.50 ) );

	$gateway = 'PayPal';

	$receipt_id = strtolower( md5( uniqid() ) );

	$notes = __( 'These are some sample notes added to a product.', 'kb-support' );

	$tax = edd_currency_filter( edd_format_amount( 1.00 ) );

	$sub_total = edd_currency_filter( edd_format_amount( 9.50 ) );

	$ticket_id = rand(1, 100);

	$user = wp_get_current_user();

	$message = str_replace( '{download_list}', $download_list, $message );
	$message = str_replace( '{file_urls}', $file_urls, $message );
	$message = str_replace( '{name}', $user->display_name, $message );
	$message = str_replace( '{fullname}', $user->display_name, $message );
 	$message = str_replace( '{username}', $user->user_login, $message );
	$message = str_replace( '{date}', date( get_option( 'date_format' ), current_time( 'timestamp' ) ), $message );
	$message = str_replace( '{subtotal}', $sub_total, $message );
	$message = str_replace( '{tax}', $tax, $message );
	$message = str_replace( '{price}', $price, $message );
	$message = str_replace( '{receipt_id}', $receipt_id, $message );
	$message = str_replace( '{payment_method}', $gateway, $message );
	$message = str_replace( '{sitename}', get_bloginfo( 'name' ), $message );
	$message = str_replace( '{product_notes}', $notes, $message );
	$message = str_replace( '{ticket_id}', $ticket_id, $message );
	$message = str_replace( '{receipt_link}', sprintf( __( '%1$sView it in your browser.%2$s', 'kb-support' ), '<a href="' . esc_url( add_query_arg( array ( 'payment_key' => $receipt_id, 'edd_action' => 'view_receipt' ), home_url() ) ) . '">', '</a>' ), $message );

	$message = apply_filters( 'kbs_email_preview_template_tags', $message );

	return apply_filters( 'kbs_email_template_wpautop', true ) ? wpautop( $message ) : $message;
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
	<a href="<?php echo esc_url( add_query_arg( array( 'kbs_action' => 'preview_email' ), home_url() ) ); ?>" class="button-secondary" target="_blank" title="<?php printf( __( '%s Received Preview', 'kb-support' ), kbs_get_ticket_label_singular() ); ?> "><?php printf( __( 'Preview %s Recieved', 'kb-support' ), kbs_get_ticket_label_singular() ); ?></a>
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

	if( empty( $_GET['edd_action'] ) ) {
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
 * @param	int 	$ticket_id		Payment ID
 * @param	arr		$ticket_data	Payment Data
 * @return	str		$email_body		Body of the email
 */
function kbs_get_email_body_content( $ticket_id = 0, $ticket_data = array() ) {
	$default_email_body = __( "Dear", "kb-support" ) . " {name},\n\n";
	$default_email_body .= sprintf( __( "Thank you for logging your %s.", "kb-support" ), kbs_get_ticket_label_singular( true ) ) . "\n\n";
	$default_email_body .= "{ticket_detailst}\n\n";
	$default_email_body .= "{sitename}";

	$email = kbs_get_option( 'ticket_received', false );
	$email = $email ? stripslashes( $email ) : $default_email_body;

	$email_body = apply_filters( 'kbs_email_template_wpautop', true ) ? wpautop( $email ) : $email;

	$email_body = apply_filters( 'kbs_ticket_received_' . KBS()->emails->get_template(), $email_body, $ticket_id, $ticket_data );

	return apply_filters( 'kbs_ticket_received', $email_body, $ticket_id, $ticket_data );
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
	//$email = edd_get_payment_user_email( $ticket_id );

	if( isset( $user_info['id'] ) && $user_info['id'] > 0 ) {
		$user_data = get_userdata( $user_info['id'] );
		$name = $user_data->display_name;
	} elseif( isset( $user_info['first_name'] ) && isset( $user_info['last_name'] ) ) {
		$name = $user_info['first_name'] . ' ' . $user_info['last_name'];
	} else {
		$name = $email;
	}

	$download_list = '';
	$downloads = maybe_unserialize( $ticket_data['downloads'] );

	if( is_array( $downloads ) ) {
		foreach( $downloads as $download ) {
			$id = isset( $ticket_data['cart_details'] ) ? $download['id'] : $download;
			$title = get_the_title( $id );
			if( isset( $download['options'] ) ) {
				if( isset( $download['options']['price_id'] ) ) {
					$title .= ' - ' . edd_get_price_option_name( $id, $download['options']['price_id'], $ticket_id );
				}
			}
			$download_list .= html_entity_decode( $title, ENT_COMPAT, 'UTF-8' ) . "\n";
		}
	}

	$gateway = edd_get_gateway_admin_label( get_post_meta( $ticket_id, '_edd_payment_gateway', true ) );

	$default_email_body = __( 'Hello', 'kb-support' ) . "\n\n" . sprintf( __( 'A %s purchase has been made', 'kb-support' ), edd_get_label_plural() ) . ".\n\n";
	$default_email_body .= sprintf( __( '%s sold:', 'kb-support' ), edd_get_label_plural() ) . "\n\n";
	$default_email_body .= $download_list . "\n\n";
	$default_email_body .= __( 'Purchased by: ', 'kb-support' ) . " " . html_entity_decode( $name, ENT_COMPAT, 'UTF-8' ) . "\n";
	$default_email_body .= __( 'Amount: ', 'kb-support' ) . " " . html_entity_decode( edd_currency_filter( edd_format_amount( edd_get_payment_amount( $ticket_id ) ) ), ENT_COMPAT, 'UTF-8' ) . "\n";
	$default_email_body .= __( 'Payment Method: ', 'kb-support' ) . " " . $gateway . "\n\n";
	$default_email_body .= __( 'Thank you', 'kb-support' );

	$email = edd_get_option( 'sale_notification', false );
	$email = $email ? stripslashes( $email ) : $default_email_body;

	//$email_body = edd_email_template_tags( $email, $ticket_data, $ticket_id, true );
	$email_body = edd_do_email_tags( $email, $ticket_id );

	$email_body = apply_filters( 'edd_email_template_wpautop', true ) ? wpautop( $email_body ) : $email_body;

	return apply_filters( 'edd_sale_notification', $email_body, $ticket_id, $ticket_data );
}

/**
 * Render Receipt in the Browser
 *
 * A link is added to the Purchase Receipt to view the email in the browser and
 * this function renders the Purchase Receipt in the browser. It overrides the
 * Purchase Receipt template and provides its only styling.
 *
 * @since 1.5
 * @author Sunny Ratilal
 */
function edd_render_receipt_in_browser() {
	if ( ! isset( $_GET['payment_key'] ) )
		wp_die( __( 'Missing purchase key.', 'kb-support' ), __( 'Error', 'kb-support' ) );

	$key = urlencode( $_GET['payment_key'] );

	ob_start();
	//Disallows caching of the page
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-store, no-cache, must-revalidate"); // HTTP/1.1
	header("Cache-Control: post-check=0, pre-check=0", false);
	header("Pragma: no-cache"); // HTTP/1.0
	header("Expires: Sat, 23 Oct 1977 05:00:00 PST"); // Date in the past
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<title><?php _e( 'Receipt', 'kb-support' ); ?></title>
		<meta charset="utf-8" />
		<meta name="robots" content="noindex, nofollow" />
		<?php wp_head(); ?>
	</head>
<body class="<?php echo apply_filters('edd_receipt_page_body_class', 'edd_receipt_page' ); ?>">
	<div id="edd_receipt_wrapper">
		<?php do_action( 'edd_render_receipt_in_browser_before' ); ?>
		<?php echo do_shortcode('[edd_receipt payment_key='. $key .']'); ?>
		<?php do_action( 'edd_render_receipt_in_browser_after' ); ?>
	</div>
<?php wp_footer(); ?>
</body>
</html>
<?php
	echo ob_get_clean();
	die();
}
add_action( 'edd_view_receipt', 'edd_render_receipt_in_browser' );