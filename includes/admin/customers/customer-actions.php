<?php
/**
 * Customer Actions
 *
 * @package     KBS
 * @subpackage  Customers/Functions
 * @copyright   Copyright (c) 2016, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Processes a custom edit
 *
 * @since	1.0
 * @param	arr		$args	The $_POST array being passeed
 * @return	arr		$output	Response messages
 */
function kbs_edit_customer( $args ) {
	$customer_edit_role = apply_filters( 'kbs_edit_customers_role', 'manage_ticket_settings' );

	if ( ! is_admin() || ! current_user_can( $customer_edit_role ) ) {
		wp_die( __( 'You do not have permission to edit this customer.', 'kb-support' ) );
	}

	if ( empty( $args ) ) {
		return;
	}

	$customer_info = $args['customerinfo'];
	$customer_id   = (int)$args['customerinfo']['id'];
	$nonce         = $args['_wpnonce'];

	if ( ! wp_verify_nonce( $nonce, 'edit-customer' ) ) {
		wp_die( __( "Cheatin' eh?!", 'kb-support' ) );
	}

	$customer = new KBS_Customer( $customer_id );
	if ( empty( $customer->id ) ) {
		return false;
	}

	$defaults = array(
		'name'    => '',
		'email'   => '',
		'user_id' => 0
	);

	$customer_info = wp_parse_args( $customer_info, $defaults );

	if ( ! is_email( $customer_info['email'] ) ) {
		$error = __( 'Please enter a valid email address.', 'kb-support' );
	}

	if ( (int) $customer_info['user_id'] != (int) $customer->user_id ) {

		// Make sure we don't already have this user attached to a customer
		if ( ! empty( $customer_info['user_id'] ) && false !== KBS()->customers->get_customer_by( 'user_id', $customer_info['user_id'] ) ) {
			$error = sprintf( __( 'The User ID %d is already associated with a different customer.', 'kb-support' ), $customer_info['user_id'] );
		}

		// Make sure it's actually a user
		$user = get_user_by( 'id', $customer_info['user_id'] );
		if ( ! empty( $customer_info['user_id'] ) && false === $user ) {
			$error = sprintf( __( 'The User ID %d does not exist. Please assign an existing user.', 'kb-support' ), $customer_info['user_id'] );
		}

	}

	if ( ! empty( $customer_info['website'] ) )	{
		$website = filter_var( $customer_info['website'], FILTER_SANITIZE_URL );

		if ( filter_var( $website, FILTER_VALIDATE_URL ) === false ) {
			$error = __( 'Please enter a valid website address.', 'kb-support' );
		}

	} else	{
		$website = '';
	}

	// Record this for later
	$previous_user_id  = $customer->user_id;

	if ( ! empty( $error ) ) {
		return;
	}

	// Setup the customer address, if present
	$address = array();

	$current_address = $customer->get_meta( 'address', true );

	if ( false === $current_address ) {
		$address['line1']   = isset( $customer_info['line1'] )   ? $customer_info['line1']   : '';
		$address['line2']   = isset( $customer_info['line2'] )   ? $customer_info['line2']   : '';
		$address['city']    = isset( $customer_info['city'] )    ? $customer_info['city']    : '';
		$address['country'] = isset( $customer_info['country'] ) ? $customer_info['country'] : '';
		$address['zip']     = isset( $customer_info['zip'] )     ? $customer_info['zip']     : '';
		$address['state']   = isset( $customer_info['state'] )   ? $customer_info['state']   : '';
	} else {
		$current_address    = wp_parse_args( $current_address, array( 'line1', 'line2', 'city', 'zip', 'state', 'country' ) );
		$address['line1']   = isset( $customer_info['line1'] )   ? $customer_info['line1']   : $current_address['line1']  ;
		$address['line2']   = isset( $customer_info['line2'] )   ? $customer_info['line2']   : $current_address['line2']  ;
		$address['city']    = isset( $customer_info['city'] )    ? $customer_info['city']    : $current_address['city']   ;
		$address['country'] = isset( $customer_info['country'] ) ? $customer_info['country'] : $current_address['country'];
		$address['zip']     = isset( $customer_info['zip'] )     ? $customer_info['zip']     : $current_address['zip']    ;
		$address['state']   = isset( $customer_info['state'] )   ? $customer_info['state']   : $current_address['state']  ;
	}

	$primary_phone    = isset( $customer_info['primary_phone'] )    ? $customer_info['primary_phone']    : '';
	$additional_phone = isset( $customer_info['additional_phone'] ) ? $customer_info['additional_phone'] : '';

	// Sanitize the inputs
	$customer_data            = array();
	$customer_data['name']    = strip_tags( stripslashes( $customer_info['name'] ) );
	$customer_data['email']   = $customer_info['email'];
	$customer_data['user_id'] = $customer_info['user_id'];

	$customer_data    = apply_filters( 'kbs_edit_customer_info', $customer_data, $customer_id );
	$address          = apply_filters( 'kbs_edit_customer_address', $address, $customer_id );
	$primary_phone    = apply_filters( 'kbs_edit_customer_primary_phone', $primary_phone, $customer_id );
	$additional_phone = apply_filters( 'kbs_edit_customer_additional_phone', $additional_phone, $customer_id );
	$website          = apply_filters( 'kbs_edit_customer_website', $website, $customer_id );

	$customer_data = array_map( 'sanitize_text_field', $customer_data );
	$address       = array_map( 'sanitize_text_field', $address );
	$website       = ! empty( $website ) ? esc_url_raw( $website ) : $website;

	do_action( 'kbs_pre_edit_customer', $customer_id, $customer_data, $address );

	$output         = array();
	$previous_email = $customer->email;

	if ( $customer->update( $customer_data ) ) {

		$customer->update_meta( 'address', $address );
		$customer->update_meta( 'primary_phone', $primary_phone );
		$customer->update_meta( 'additional_phone', $additional_phone );
		$customer->update_meta( 'website', $website );

		// Update some ticket meta if we need to
		$tickets_array = explode( ',', $customer->ticket_ids );

		if ( $customer->email != $previous_email ) {
			foreach ( $tickets_array as $ticket_id ) {
				kbs_update_ticket_meta( $ticket_id, 'email', $customer->email );
			}
		}

		if ( $customer->user_id != $previous_user_id ) {
			foreach ( $tickets_array as $ticket_id ) {
				kbs_update_ticket_meta( $ticket_id, '_kbs_ticket_user_id', $customer->user_id );
			}
		}

		$output['success']       = true;
		$customer_data           = array_merge( $customer_data, $address );
		$output['customer_info'] = $customer_data;

	} else {

		$output['success'] = false;

	}

	do_action( 'kbs_post_edit_customer', $customer_id, $customer_data );

	if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
		header( 'Content-Type: application/json' );
		echo json_encode( $output );
		wp_die();
	}

	return $output;

} // kbs_edit_customer
add_action( 'kbs-edit-customer', 'kbs_edit_customer', 10, 1 );

/**
 * Disconnect a user ID from a customer.
 *
 * @since	1.0
 * @param	arr		$args	Array of arguments
 * @return	bool	If the disconnect was sucessful
 */
function kbs_disconnect_customer_user_id( $args ) {

	$customer_edit_role = apply_filters( 'kbs_edit_customers_role', 'manage_ticket_settings' );

	if ( ! is_admin() || ! current_user_can( $customer_edit_role ) ) {
		wp_die( __( 'You do not have permission to edit this customer.', 'kb-support' ) );
	}

	if ( empty( $args ) ) {
		return;
	}

	$customer_id = (int)$args['customer_id'];
	$nonce       = $args['_wpnonce'];

	if ( ! wp_verify_nonce( $nonce, 'edit-customer' ) ) {
		wp_die( __( "Cheatin' eh?!", 'kb-support' ) );
	}

	$customer = new KBS_Customer( $customer_id );
	if ( empty( $customer->id ) ) {
		return false;
	}

	do_action( 'kbs_pre_customer_disconnect_user_id', $customer_id, $customer->user_id );

	$customer_args = array( 'user_id' => 0 );

	if ( $customer->update( $customer_args ) ) {
		global $wpdb;

		if ( ! empty( $customer->ticket_ids ) ) {
			$wpdb->query( "UPDATE $wpdb->postmeta SET meta_value = 0 WHERE meta_key = '_kbs_ticket_user_id' AND post_id IN ( $customer->ticket_ids )" );
		}

		$output['success'] = true;
		$output['message'] = '&kbs-message=disconnect_user';

	} else {
		$output['success'] = false;
		$output['message'] = '&kbs-message=disconnect_user_fail';
	}

	do_action( 'kbs_post_customer_disconnect_user_id', $customer_id );

	if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
		header( 'Content-Type: application/json' );
		echo json_encode( $output );
		wp_die();
	}

	return $output;

} // kbs_disconnect_customer_user_id
add_action( 'kbs_disconnect-userid', 'kbs_disconnect_customer_user_id', 10, 1 );

/**
 * Add an email address to the customer from within the admin and log a customer note.
 *
 * @since	1.0
 * @param	1.0		$args	Array of arguments: nonce, customer id, and email address
 * @return	mixed	If DOING_AJAX echos out JSON, otherwise returns array of success (bool) and message (string)
 */
function kbs_add_customer_email( $args ) {
	$customer_edit_role = apply_filters( 'kbs_edit_customers_role', 'manage_ticket_settings' );

	if ( ! is_admin() || ! current_user_can( $customer_edit_role ) ) {
		wp_die( __( 'You do not have permission to edit this customer.', 'kb-support' ) );
	}

	$output = array();

	if ( empty( $args ) || empty( $args['email'] ) || empty( $args['customer_id'] ) ) {

		$output['success'] = false;

		if ( empty( $args['email'] ) ) {
			$output['message'] = __( 'Email address is required.', 'kb-support' );
		} else if ( empty( $args['customer_id'] ) ) {
			$output['message'] = __( 'Customer ID is required.', 'kb-support' );
		} else {
			$output['message'] = __( 'An error has occured. Please try again.', 'kb-support' );
		}

	} else if ( ! wp_verify_nonce( $args['_wpnonce'], 'kbs-add-customer-email' ) ) {

		$output = array(
			'success' => false,
			'message' => __( 'Nonce verification failed.', 'kb-support' ),
		);

	} else if ( ! is_email( $args['email'] ) ) {

		$output = array(
			'success' => false,
			'message' => __( 'Invalid email address.', 'kb-support' ),
		);

	} else {

		$email       = sanitize_email( $args['email'] );
		$customer_id = (int) $args['customer_id'];
		$primary     = 'true' === $args['primary'] ? true : false;
		$customer    = new KBS_Customer( $customer_id );

		if ( false === $customer->add_email( $email, $primary ) ) {

			if ( in_array( $email, $customer->emails ) ) {

				$output = array(
					'success'  => false,
					'message'  => __( 'Email already assocaited with this customer.', 'kb-support' ),
				);

			} else {

				$output = array(
					'success' => false,
					'message' => __( 'Email address is already associated with another customer.', 'kb-support' ),
				);

			}

		} else {

			$redirect = admin_url( 'edit.php?post_type=kbs_ticket&page=kbs-customers&view=userdata&id=' . $customer_id . '&kbs-message=email_added' );
			$output = array(
				'success'  => true,
				'message'  => __( 'Email successfully added to customer.', 'kb-support' ),
				'redirect' => $redirect,
			);

			$user          = wp_get_current_user();
			$user_login    = ! empty( $user->user_login ) ? $user->user_login : 'KBSBot';
			$customer_note = __( sprintf( 'Email address %s added by %s', $email, $user_login ), 'kb-support' );
			$customer->add_note( $customer_note );

			if ( $primary ) {
				$customer_note = __( sprintf( 'Email address %s set as primary by %s', $email, $user_login ), 'kb-support' );
				$customer->add_note( $customer_note );
			}

		}

	}

	do_action( 'kbs_post_add_customer_email', $customer_id, $args );

	if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
		header( 'Content-Type: application/json' );
		echo json_encode( $output );
		wp_die();
	}

	return $output;

}
add_action( 'kbs_customer-add-email', 'kbs_add_customer_email', 10, 1 );

/**
 * Remove an email address to the customer from within the admin and log a customer note
 * and redirect back to the customer interface for feedback
 *
 * @since	1.0
 * @return	1.0void
 */
function kbs_remove_customer_email() {
	if ( empty( $_GET['id'] ) || ! is_numeric( $_GET['id'] ) ) {
		return false;
	}

	if ( empty( $_GET['email'] ) || ! is_email( $_GET['email'] ) ) {
		return false;
	}

	if ( empty( $_GET['_wpnonce'] ) ) {
		return false;
	}

	$nonce = $_GET['_wpnonce'];
	if ( ! wp_verify_nonce( $nonce, 'kbs-remove-customer-email' ) ) {
		wp_die( __( 'Nonce verification failed', 'kb-support' ), __( 'Error', 'kb-support' ), array( 'response' => 403 ) );
	}

	$customer = new KBS_Customer( $_GET['id'] );
	if ( $customer->remove_email( $_GET['email'] ) ) {

		$url = add_query_arg( 'kbs-message', 'email_removed', admin_url( 'edit.php?post_type=kbs_ticket&page=kbs-customers&view=userdata&id=' . $customer->id ) );

		$user          = wp_get_current_user();
		$user_login    = ! empty( $user->user_login ) ? $user->user_login : 'KBSBot';
		$customer_note = __( sprintf( 'Email address %s removed by %s', $_GET['email'], $user_login ), 'kb-support' );
		$customer->add_note( $customer_note );

	} else {
		$url = add_query_arg( 'kbs-message', 'email_remove_failed', admin_url( 'edit.php?post_type=kbs_ticket&page=kbs-customers&view=userdata&id=' . $customer->id ) );
	}

	wp_safe_redirect( $url );
	exit;
}
add_action( 'kbs-customer-remove-email', 'kbs_remove_customer_email', 10 );

/**
 * Set an email address as the primary for a customer from within the admin and log a customer note
 * and redirect back to the customer interface for feedback
 *
 * @since	1.0
 * @return	void
 */
function kbs_set_customer_primary_email() {
	if ( empty( $_GET['id'] ) || ! is_numeric( $_GET['id'] ) ) {
		return false;
	}

	if ( empty( $_GET['email'] ) || ! is_email( $_GET['email'] ) ) {
		return false;
	}

	if ( empty( $_GET['_wpnonce'] ) ) {
		return false;
	}

	$nonce = $_GET['_wpnonce'];
	if ( ! wp_verify_nonce( $nonce, 'kbs-set-customer-primary-email' ) ) {
		wp_die( __( 'Nonce verification failed', 'kb-support' ), __( 'Error', 'kb-support' ), array( 'response' => 403 ) );
	}

	$customer = new KBS_Customer( $_GET['id'] );
	if ( $customer->set_primary_email( $_GET['email'] ) ) {

		$url = add_query_arg( 'kbs-message', 'primary_email_updated', admin_url( 'edit.php?post_type=kbs_ticket&page=kbs-customers&view=userdata&id=' . $customer->id ) );

		$user          = wp_get_current_user();
		$user_login    = ! empty( $user->user_login ) ? $user->user_login : 'KBSBot';
		$customer_note = __( sprintf( 'Email address %s set as primary by %s', $_GET['email'], $user_login ), 'kb-support' );
		$customer->add_note( $customer_note );

	} else {
		$url = add_query_arg( 'kbs-message', 'primary_email_failed', admin_url( 'edit.php?post_type=kbs_ticket&page=kbs-customers&view=userdata&id=' . $customer->id ) );
	}

	wp_safe_redirect( $url );
	exit;
}
add_action( 'kbs-customer-primary-email', 'kbs_set_customer_primary_email', 10 );

/**
 * Save a customer note being added.
 *
 * @since	1.0
 * @param	arr		$args	The $_POST array being passeed
 * @return	int		The Note ID that was saved, or 0 if nothing was saved
 */
function kbs_customer_save_note( $args ) {

	$customer_view_role = apply_filters( 'edd_view_customers_role', 'manage_ticket_settings' );

	if ( ! is_admin() || ! current_user_can( $customer_view_role ) ) {
		wp_die( __( 'You do not have permission to edit this customer.', 'kb-support' ) );
	}

	if ( empty( $args ) ) {
		return;
	}

	$customer_note = trim( sanitize_text_field( $args['customer_note'] ) );
	$customer_id   = (int)$args['customer_id'];
	$nonce         = $args['add_customer_note_nonce'];

	if ( ! wp_verify_nonce( $nonce, 'add-customer-note' ) ) {
		wp_die( __( "Cheatin' eh?!", 'kb-support' ) );
	}

	if ( empty( $customer_note ) ) {
		$error = 'empty-customer-note';
	}

	if ( $error ) {
		return;
	}

	$customer = new KBS_Customer( $customer_id );
	$new_note = $customer->add_note( $customer_note );

	do_action( 'kbs_pre_insert_customer_note', $customer_id, $new_note );

	if ( ! empty( $new_note ) && ! empty( $customer->id ) ) {

		ob_start();
		?>
		<div class="customer-note-wrapper dashboard-comment-wrap comment-item">
			<span class="note-content-wrap">
				<?php echo stripslashes( $new_note ); ?>
			</span>
		</div>
		<?php
		$output = ob_get_contents();
		ob_end_clean();

		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			echo $output;
			exit;
		}

		return $new_note;

	}

	return false;

} // kbs_customer_save_note
add_action( 'kbs_add-customer-note', 'kbs_customer_save_note', 10, 1 );

/**
 * Delete a customer.
 *
 * @since	1.0
 * @param	arr		$args	The $_POST array being passeed
 * @return	int		Wether it was a successful deletion
 */
function kbs_customer_delete( $args ) {

	$customer_edit_role = apply_filters( 'kbs_edit_customers_role', 'manage_ticket_settings' );

	if ( ! is_admin() || ! current_user_can( $customer_edit_role ) ) {
		wp_die( __( 'You do not have permission to delete this customer.', 'kb-support' ) );
	}

	if ( empty( $args ) ) {
		return;
	}

	$customer_id   = (int)$args['customer_id'];
	$confirm       = ! empty( $args['kbs-customer-delete-confirm'] ) ? true : false;
	$remove_data   = ! empty( $args['kbs-customer-delete-records'] ) ? true : false;
	$nonce         = $args['_wpnonce'];
	$error         = false;

	if ( ! wp_verify_nonce( $nonce, 'delete-customer' ) ) {
		wp_die( __( "Cheatin' eh?!", 'kb-support' ) );
	}

	if ( ! $confirm ) {
		$error = 'customer_delete_no_confirm';
	}

	if ( $error ) {
		wp_redirect( admin_url(
			'edit.php?post_type=kbs_ticket&page=kbs-customers&view=overview&id=' . $customer_id . ',&kbs-message=' . $error
		) );
		exit;
	}

	$customer = new KBS_Customer( $customer_id );

	do_action( 'kbs_pre_delete_customer', $customer_id, $confirm, $remove_data );

	$success = false;

	if ( $customer->id > 0 ) {

		$tickets_array = explode( ',', $customer->ticket_ids );
		$success       = KBS()->customers->delete( $customer->id );

		if ( $success )	{

			foreach ( $tickets_array as $ticket_id ) {
				kbs_update_ticket_meta( $ticket_id, '_kbs_ticket_customer_id', 0 );
			}

			$redirect = admin_url( 'edit.php?post_type=kbs_ticket&page=kbs-customers&kbs-message=customer_deleted' );

		} else {

			$error = 'kbs_customer_delete_failed';
			$redirect = admin_url(
				'edit.php?post_type=kbs_ticket&page=kbs-customers&view=delete&id=' . $customer_id . '&kbs-message=' . $error
			);

		}

	} else {
		$error    = 'kbs_customer_delete_invalid_id';
		$redirect = admin_url( 'edit.php?post_type=kbs_ticket&page=kbs-customers&kbs-message=' . $error );
	}

	wp_redirect( $redirect );
	exit;

} // kbs_customer_delete
add_action( 'kbs-delete-customer', 'kbs_customer_delete', 10, 1 );
