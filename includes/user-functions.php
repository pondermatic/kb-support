<?php
/**
 * User Functions
 *
 * Functions related to users
 *
 * @package     KBS
 * @subpackage  Functions
 * @copyright   Copyright (c) 2017, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Registers user profile fields.
 *
 * Fields should be registered as field_name => bool (true for agent only, otherwise false for all users)
 *
 * @since	1.0
 * @return	arr		Array of user profile field ids
 */
function kbs_register_user_profile_fields()	{
	$fields = array();

    if ( kbs_departments_enabled() )    {
        $fields[] = 'kbs_departments';
    }

	return apply_filters( 'kbs_user_profile_fields', $fields );
} // kbs_register_user_profile_fields

/**
 * Output user profile fields.
 *
 * @since	1.0
 * @param	obj		$user	The WP_User object
 * @return	arr		Array of user profile fields
 */
function kbs_output_user_profile_fields( $user )	{

	$fields = kbs_register_user_profile_fields();

	if ( ! empty( $fields ) )	{
		ob_start(); ?>

		<h2><?php _e( 'KB Support', 'kb-support' ); ?></h2>
		<table class="form-table">
			<?php do_action( 'kbs_display_user_profile_fields', $user, $fields ); ?>
		</table>

		<?php echo ob_get_clean();
	}

} // kbs_output_user_profile_fields
add_action( 'show_user_profile', 'kbs_output_user_profile_fields' );
add_action( 'edit_user_profile', 'kbs_output_user_profile_fields' );

/**
 * Adds the department options field to the user profile for agents.
 *
 * @since	1.2
 * @param   obj		$user	The WP_User object
 */
function kbs_render_user_profile_department_field( $user )  {
    if ( ! kbs_is_agent( $user->ID ) || ( get_current_user_id() != $user->ID && ! current_user_can( 'manage_ticket_settings' ) ) )  {
        return;
    }

    $departments = kbs_get_departments();

    if ( $departments ) {
		$output = array();
        ob_start(); ?>

        <tr>
            <th><label for="kbs_department"><?php _e( 'Departments', 'kb-support' ); ?></label></th>
            <td>
                <?php foreach( $departments as $department ) : ?>
                    <?php $output[] = sprintf(
						'<input type="checkbox" name="kbs_departments[]" id="%s" value="%s"%s /> <label for="%s">%s</label>',
						$department->slug,
						$department->term_id,
						kbs_agent_is_in_department( $department->term_id, $user->ID ) ? ' checked="checked"' : '',
						$department->slug,
						$department->name
					); ?>
                <?php endforeach; ?>
                <?php echo implode( '<br />', $output ); ?>
            </td>
        </tr>

        <?php echo ob_get_clean();
        
    }
} // kbs_render_user_profile_department_field
add_action( 'kbs_display_user_profile_fields', 'kbs_render_user_profile_department_field', 10 );

/**
 * Saves the signature field.
 *
 * @since	1.0
 */
function kbs_save_user_departments( $user_id ) {

	if ( ! kbs_departments_enabled() || ! current_user_can( 'edit_user', $user_id ) )	{
		return false;
	}

	$departments = kbs_get_departments();
	$add_departments  = ! empty( $_POST['kbs_departments'] ) ? $_POST['kbs_departments'] : array();

	foreach( $departments as $department )	{
		if ( in_array( $department->term_id, $add_departments ) )	{
			kbs_add_agent_to_department( $department->term_id, $user_id );
		} else	{
			kbs_remove_agent_from_department( $department->term_id, $user_id );
		}
	}

} // kbs_save_user_departments
add_action( 'personal_options_update', 'kbs_save_user_departments' );
add_action( 'edit_user_profile_update', 'kbs_save_user_departments' );

/**
 * Retrieve users by role.
 *
 * @since	1.0
 * @param	str		$role	Name of the role to retrieve.
 * @param	bool	$ids	True to return array of IDs, false for array of user objects
 * @return	mixed
 */
function kbs_get_users_by_role( $role = array( 'support_agent', 'support_manager' ), $ids = false )	{
	global $wpdb;

	$args = array(
		'orderby'    => 'display_name',
		'role__in' => $role
	);

	if ( ! empty( $ids ) )	{
		$args['fields'] = 'ID';
	}

	$args = apply_filters( 'kbs_users_by_role', $args );

	$user_query = new WP_User_Query( $args );
	
	$users = $user_query->get_results();
	
	return $users;
} // kbs_get_users_by_role

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

/**
 * Attach the newly created user_id to a customer, if one exists
 *
 * @since	1.0
 * @param 	int		$user_id	The User ID that was created
 * @return	void
 */
function kbs_connect_existing_customer_to_new_user( $user_id ) {
	$email = get_the_author_meta( 'user_email', $user_id );

	// Update the user ID on the customer
	$customer = new KBS_Customer( $email );

	if( $customer->id > 0 ) {
		$customer->update( array( 'user_id' => $user_id ) );
	}
}
add_action( 'user_register', 'kbs_connect_existing_customer_to_new_user', 10, 1 );

/**
 * Process Profile Updates from the Editor Form
 *
 * @since	1.0
 * @param	arr		$data	Data sent from the profile editor
 * @return void
 */
function kbs_process_profile_editor_updates( $data ) {

	if ( ! isset( $_POST['kbs_action'] ) || 'edit_user_profile' != $_POST['kbs_action'] )	{
		return;
	}

	if ( empty( $_POST['kbs_profile_editor_submit'] ) && ! is_user_logged_in() ) {
		return false;
	}

	// Nonce security
	if ( ! wp_verify_nonce( $_POST['kbs_profile_editor_nonce'], 'kbs-profile-editor-nonce' ) ) {
		return false;
	}

	$user_id       = get_current_user_id();
	$old_user_data = get_userdata( $user_id );

	$display_name = isset( $_POST['kbs_display_name'] )    ? sanitize_text_field( $_POST['kbs_display_name'] )    : $old_user_data->display_name;
	$first_name   = isset( $_POST['kbs_first_name'] )      ? sanitize_text_field( $_POST['kbs_first_name'] )      : $old_user_data->first_name;
	$last_name    = isset( $_POST['kbs_last_name'] )       ? sanitize_text_field( $_POST['kbs_last_name'] )       : $old_user_data->last_name;
	$email        = isset( $_POST['kbs_email'] )           ? sanitize_email( $_POST['kbs_email'] )                : $old_user_data->user_email;
	$line1        = isset( $_POST['kbs_address_line1'] )   ? sanitize_text_field( $_POST['kbs_address_line1'] )   : '';
	$line2        = isset( $_POST['kbs_address_line2'] )   ? sanitize_text_field( $_POST['kbs_address_line2'] )   : '';
	$city         = isset( $_POST['kbs_address_city'] )    ? sanitize_text_field( $_POST['kbs_address_city'] )    : '';
	$state        = isset( $_POST['kbs_address_state'] )   ? sanitize_text_field( $_POST['kbs_address_state'] )   : '';
	$zip          = isset( $_POST['kbs_address_zip'] )     ? sanitize_text_field( $_POST['kbs_address_zip'] )     : '';
	$country      = isset( $_POST['kbs_address_country'] ) ? sanitize_text_field( $_POST['kbs_address_country'] ) : '';

	$error    = false;
	$userdata = array(
		'ID'           => $user_id,
		'first_name'   => $first_name,
		'last_name'    => $last_name,
		'display_name' => $display_name,
		'user_email'   => $email
	);

	$address = array(
		'line1'    => $line1,
		'line2'    => $line2,
		'city'     => $city,
		'state'    => $state,
		'zip'      => $zip,
		'country'  => $country
	);

	do_action( 'kbs_pre_update_user_profile', $user_id, $userdata );

	if ( ! empty( $_POST['kbs_new_user_pass1'] ) ) {
		if ( $_POST['kbs_new_user_pass1'] !== $_POST['kbs_new_user_pass2'] ) {
			$error = 'password_mismatch';
		} else {
			$userdata['user_pass'] = $_POST['kbs_new_user_pass1'];
		}
	}

	if ( ! $error && $email != $old_user_data->user_email ) {

		if ( ! is_email( $email ) ) {
			$error = 'email_invalid';
		}

		if ( email_exists( $email ) ) {
			$error = 'email_unavailable';
		}

	}

	$url = remove_query_arg( 'kbs_notice', $_POST['kbs_redirect'] );

	if ( $error ) {
		$url = add_query_arg( 'kbs_notice', $error, $url );
		wp_safe_redirect( $url );
		die();
	}

	// Process updates
	$updated = wp_update_user( $userdata );

	$customer = new KBS_Customer( $user_id, true );

	if ( ! empty( $address ) )	{
		$meta     = update_user_meta( $user_id, '_kbs_user_address', $address );
		$customer->update_meta( 'address', $address );
	}

	if ( $customer->email === $email || ( is_array( $customer->emails ) && in_array( $email, $customer->emails ) ) ) {
		$customer->set_primary_email( $email );
	};

	if ( $customer->id > 0 ) {
		$update_args = array(
			'name'  => $first_name . ' ' . $last_name,
		);

		$customer->update( $update_args );
	}

	if ( $updated ) {
		do_action( 'kbs_user_profile_updated', $user_id, $userdata );
		wp_safe_redirect( add_query_arg( 'kbs_notice', 'profile_updated', $url ) );
		die();
	}

} // kbs_process_profile_editor_updates
add_action( 'init', 'kbs_process_profile_editor_updates' );

/**
 * Process the 'remove' email address action on the profile editor form.
 *
 * @since	1.0
 * @return	void
 */
function kbs_process_profile_editor_remove_email() {

	if ( ! isset( $_GET['kbs_action'] ) || 'profile-remove-email' != $_GET['kbs_action'] )	{
		return;
	}

	if ( ! is_user_logged_in() ) {
		return false;
	}

	// Nonce security
	if ( ! wp_verify_nonce( $_GET['_wpnonce'], 'kbs-remove-customer-email' ) ) {
		return false;
	}

	if ( empty( $_GET['email'] ) || ! is_email( $_GET['email'] ) ) {
		return false;
	}

	$customer = new KBS_Customer( get_current_user_id(), true );
	$url      = remove_query_arg( 'kbs_notice', $_GET['redirect'] );

	if ( $customer->remove_email( $_GET['email'] ) ) {

		$url = add_query_arg( 'kbs_notice', 'profile_updated', $_GET['redirect'] );

		$user          = wp_get_current_user();
		$user_login    = ! empty( $user->user_login ) ? $user->user_login : 'KBSBot';
		$customer_note = __( sprintf( 'Email address %s removed by %s', $_GET['email'], $user_login ), 'kb-support' );
		$customer->add_note( $customer_note );

		$url = add_query_arg( 'kbs_notice', 'email_removed', $url );

	} else {
		$url = add_query_arg( 'kbs_notice', 'email_remove_failed', $url );
	}

	wp_safe_redirect( $url );
	exit;
} // kbs_process_profile_editor_remove_email
add_action( 'init', 'kbs_process_profile_editor_remove_email' );
