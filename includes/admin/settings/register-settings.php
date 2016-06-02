<?php
/**
 * Register Settings.
 *
 * Taken from Easy Digital Downloads.
 *
 * @package     KBS
 * @subpackage  Admin/Settings
 * @copyright   Copyright (c) 2016, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Get an option
 *
 * Looks to see if the specified setting exists, returns default if not
 *
 * @since	0.1
 * @return	mixed
 */
function kbs_get_option( $key = '', $default = false ) {
	global $kbs_options;
	$value = ! empty( $kbs_options[ $key ] ) ? $kbs_options[ $key ] : $default;
	$value = apply_filters( 'kbs_get_option', $value, $key, $default );
	return apply_filters( 'kbs_get_option_' . $key, $value, $key, $default );
} // kbs_get_option

/**
 * Update an option
 *
 * Updates a kbs setting value in both the db and the global variable.
 * Warning: Passing in an empty, false or null string value will remove
 *          the key from the kbs_options array.
 *
 * @since	0.1
 * @param	str				$key	The Key to update
 * @param	str|bool|int	$value	The value to set the key to
 * @return	bool			True if updated, false if not.
 */
function kbs_update_option( $key = '', $value = false ) {

	// If no key, exit
	if ( empty( $key ) ){
		return false;
	}

	if ( empty( $value ) ) {
		$remove_option = kbs_delete_option( $key );
		return $remove_option;
	}

	// First let's grab the current settings
	$options = get_option( 'kbs_settings' );

	// Let's let devs alter that value coming in
	$value = apply_filters( 'kbs_update_option', $value, $key );

	// Next let's try to update the value
	$options[ $key ] = $value;
	$did_update = update_option( 'kbs_settings', $options );

	// If it updated, let's update the global variable
	if ( $did_update ){
		global $kbs_options;
		$kbs_options[ $key ] = $value;

	}

	return $did_update;
} // kbs_update_option

/**
 * Remove an option.
 *
 * Removes a kbs setting value in both the db and the global variable.
 *
 * @since	0.1
 * @param	str		$key	The Key to delete.
 * @return	bool	True if updated, false if not.
 */
function kbs_delete_option( $key = '' ) {

	// If no key, exit
	if ( empty( $key ) ){
		return false;
	}

	// First let's grab the current settings
	$options = get_option( 'kbs_settings' );

	// Next let's try to update the value
	if( isset( $options[ $key ] ) ) {

		unset( $options[ $key ] );

	}

	$did_update = update_option( 'kbs_settings', $options );

	// If it updated, let's update the global variable
	if ( $did_update ){
		global $kbs_options;
		$kbs_options = $options;
	}

	return $did_update;
} // kbs_delete_option

/**
 * Get Settings.
 *
 * Retrieves all plugin settings.
 *
 * @since	0.1
 * @return	arr		KBS settings.
 */
function kbs_get_settings() {
	$settings = get_option( 'kbs_settings' );
	
	if( empty( $settings ) ) {

		$settings = array();

		update_option( 'kbs_settings', $settings );
		
	}

	return apply_filters( 'kbs_get_settings', $settings );
} // kbs_get_settings

/**
 * Add all settings sections and fields.
 *
 * @since	0.1
 * @return	void
*/
function kbs_register_settings() {

	if ( false == get_option( 'kbs_settings' ) ) {
		add_option( 'kbs_settings' );
	}

	foreach ( kbs_get_registered_settings() as $tab => $sections ) {
		foreach ( $sections as $section => $settings) {

			// Check for backwards compatibility
			$section_tabs = kbs_get_settings_tab_sections( $tab );
			if ( ! is_array( $section_tabs ) || ! array_key_exists( $section, $section_tabs ) ) {
				$section = 'main';
				$settings = $sections;
			}

			add_settings_section(
				'kbs_settings_' . $tab . '_' . $section,
				__return_null(),
				'__return_false',
				'kbs_settings_' . $tab . '_' . $section
			);

			foreach ( $settings as $option ) {
				// For backwards compatibility
				if ( empty( $option['id'] ) ) {
					continue;
				}

				$name = isset( $option['name'] ) ? $option['name'] : '';

				add_settings_field(
					'kbs_settings[' . $option['id'] . ']',
					$name,
					function_exists( 'kbs_' . $option['type'] . '_callback' ) ? 'kbs_' . $option['type'] . '_callback' : 'kbs_missing_callback',
					'kbs_settings_' . $tab . '_' . $section,
					'kbs_settings_' . $tab . '_' . $section,
					array(
						'section'     => $section,
						'id'          => isset( $option['id'] )          ? $option['id']          : null,
						'desc'        => ! empty( $option['desc'] )      ? $option['desc']        : '',
						'name'        => isset( $option['name'] )        ? $option['name']        : null,
						'size'        => isset( $option['size'] )        ? $option['size']        : null,
						'options'     => isset( $option['options'] )     ? $option['options']     : '',
						'std'         => isset( $option['std'] )         ? $option['std']         : '',
						'min'         => isset( $option['min'] )         ? $option['min']         : null,
						'max'         => isset( $option['max'] )         ? $option['max']         : null,
						'step'        => isset( $option['step'] )        ? $option['step']        : null,
						'chosen'      => isset( $option['chosen'] )      ? $option['chosen']      : null,
						'placeholder' => isset( $option['placeholder'] ) ? $option['placeholder'] : null,
						'allow_blank' => isset( $option['allow_blank'] ) ? $option['allow_blank'] : true,
						'readonly'    => isset( $option['readonly'] )    ? $option['readonly']    : false,
						'faux'        => isset( $option['faux'] )        ? $option['faux']        : false,
					)
				);
			}
		}

	}

	// Creates our settings in the options table
	register_setting( 'kbs_settings', 'kbs_settings', 'kbs_settings_sanitize' );

} // kbs_register_settings
add_action( 'admin_init', 'kbs_register_settings' );

/**
 * Retrieve the array of plugin settings.
 *
 * @since	0.1
 * @return	arr
*/
function kbs_get_registered_settings() {

	/**
	 * 'Whitelisted' KBS settings, filters are provided for each settings
	 * section to allow extensions and other plugins to add their own settings.
	 */
	$kbs_settings = array(
		/** General Settings */
		'general' => apply_filters( 'kbs_settings_general',
			array(
				'pages' => array(
					'page_settings' => array(
						'id'   => 'page_settings',
						'name' => '<h3>' . __( 'Page Settings', 'kb-support' ) . '</h3>',
						'desc' => '',
						'type' => 'header'
					),
					'ticket_page'   => array(
						'id'      => 'ticket_page',
						'name'    => sprintf( __( '%s Page', 'kb-support' ), kbs_get_ticket_label_singular() ),
						'desc'    => sprintf( __( 'This is the page where customers will submit their %s', 'kb-support' ), kbs_get_ticket_label_plural( true ) ),
						'type'    => 'select',
						'options' => kbs_get_pages()
					)
				)
			)
		),
		/** SLA Settings */
		'sla' => apply_filters( 'kbs_settings_sla',
			array(
				'main' => array(
					'sla_settings_header' => array(
						'id'   => 'sla_settings_header',
						'name' => '<h3>' . __( 'SLA Settings', 'kb-support' ) . '</h3>',
						'type' => 'header'
					),
					'sla_tracking' => array(
						'id'      => 'sla_tracking',
						'name'    => __( 'Enable SLA Tracking', 'kb-support' ),
						'type'    => 'checkbox',
						'std'     => '1'
					),
					'sla_response_time' => array(
						'id'      => 'sla_response_time',
						'name'    => __( 'Target Response Time', 'kb-support' ),
						'type'    => 'select',
						'options' => kbs_get_response_time_options(),
						'std'     => '4 hours'
					)
				)
			)
		),
		/** Emails Settings */
		'emails' => apply_filters( 'kbs_settings_emails',
			array(
				'main' => array(
					'email_settings_header' => array(
						'id'   => 'email_settings_header',
						'name' => '<h3>' . __( 'Email Settings', 'kb-support' ) . '</h3>',
						'type' => 'header'
					),
					'email_template' => array(
						'id'      => 'email_template',
						'name'    => __( 'Email Template', 'kb-support' ),
						'desc'    => sprintf( __( 'Choose a template. Click "Save Changes" then "Preview %s Received" to see the new template.', 'kb-support' ), kbs_get_ticket_label_singular() ),
						'type'    => 'select',
						'options' => kbs_get_email_templates()
					),
					'email_logo' => array(
						'id'   => 'email_logo',
						'name' => __( 'Logo', 'kb-support' ),
						'desc' => sprintf( __( 'Upload or choose a logo to be displayed at the top of the %s received emails. Displayed on HTML emails only.', 'kb-support' ), kbs_get_ticket_label_singular( true ) ),
						'type' => 'upload'
					),
					'email_settings' => array(
						'id'   => 'email_settings',
						'name' => '',
						'desc' => '',
						'type' => 'hook'
					)
				),
				'ticket_received' => array(
					'ticket_received_settings' => array(
						'id'   => 'ticket_received_settings',
						'name' => '<h3>' . sprintf( __( '%s Received', 'kb-support' ), kbs_get_ticket_label_singular() ) . '</h3>',
						'type' => 'header'
					),
					'from_name' => array(
						'id'   => 'from_name',
						'name' => __( 'From Name', 'kb-support' ),
						'desc' => sprintf( __( 'The name %s received emails are said to come from. This should probably be your site name.', 'kb-support' ), kbs_get_ticket_label_singular( true ) ),
						'type' => 'text',
						'std'  => get_bloginfo( 'name' )
					),
					'from_email' => array(
						'id'   => 'from_email',
						'name' => __( 'From Email', 'kb-support' ),
						'desc' => sprintf( __( 'Email to send %s received email from. This will act as the "from" and "reply-to" address.', 'kb-support' ), kbs_get_ticket_label_singular( true ) ),
						'type' => 'text',
						'std'  => get_bloginfo( 'admin_email' )
					),
					'received_subject' => array(
						'id'   => 'received_subject',
						'name' => __( 'Received Email Subject', 'kb-support' ),
						'desc' => sprintf( __( 'Enter the subject line for the %s received email', 'kb-support' ), kbs_get_ticket_label_singular( true ) ),
						'type' => 'text',
						'std'  => sprintf( __( '%s Recieved', 'kb-support' ), kbs_get_ticket_label_singular() )
					),
					'received_heading' => array(
						'id'   => 'received_heading',
						'name' => __( 'Received Email Heading', 'kb-support' ),
						'desc' => sprintf( __( 'Enter the heading for the %s received email', 'kb-support' ), kbs_get_ticket_label_singular( true ) ),
						'type' => 'text',
						'std'  => sprintf( __( '%s Recieved', 'kb-support' ), kbs_get_ticket_label_singular() )
					),
					'received_content' => array(
						'id'   => 'received_content',
						'name' => sprintf( __( '%s Received', 'kb-support' ), kbs_get_ticket_label_singular() ),
						'desc' => sprintf( __( 'Enter the text that is sent as a %1$s received email to users after submission of a %1$s. HTML is accepted.', 'kb-support' ), kbs_get_ticket_label_singular( true ) ),
						'type' => 'rich_editor',
						'std'  => __( "Dear", "kb-support" ) . " {name},\n\n" . __( "Thank you for logging your support ticket. Details of your ticket are displayed below.", "kb-support" ) . "\n\n" . '{ticket_details}'
					),
				),
				'ticket_notifications' => array(
					'ticket_notification_settings' => array(
						'id'   => 'ticket_notification_settings',
						'name' => '<h3>' . sprintf( __( '%s Notifications', 'kb-support' ), kbs_get_ticket_label_singular() ) . '</h3>',
						'type' => 'header'
					),
					'ticket_notification_subject' => array(
						'id'   => 'ticket_notification_subject',
						'name' => sprintf( __( '%s Notification Subject', 'kb-support' ), kbs_get_ticket_label_singular() ),
						'desc' => sprintf( __( 'Enter the subject line for the %s notification email', 'kb-support' ), kbs_get_ticket_label_singular( true ) ),
						'type' => 'text',
						'std'  => sprintf( __( 'New %s received - Case #{ticket_id}', 'kb-support' ), kbs_get_ticket_label_singular( true ) )
					),
					'ticket_notification' => array(
						'id'   => 'ticket_notification',
						'name' => sprintf( __( '%s Notification', 'kb-support' ), kbs_get_ticket_label_singular() ) . "\n\n" . '{ticket_details}',
						'desc' => sprintf( __( 'Enter the text that is sent as %s received notification email after submission of a case. HTML is accepted.' ), kbs_get_ticket_label_singular( true ) ),
						'type' => 'rich_editor',
						'std'  => '',//kbs_get_default_ticket_notification_email()
					),
					'admin_notice_emails' => array(
						'id'   => 'admin_notice_emails',
						'name' => sprintf( __( '%s Notification Emails', 'kb-support' ), kbs_get_ticket_label_singular() ),
						'desc' => sprintf( __( 'Enter the email address(es) that should receive a notification anytime a %s is logged, one per line', 'kb-support' ), kbs_get_ticket_label_singular( true ) ),
						'type' => 'textarea',
						'std'  => get_bloginfo( 'admin_email' )
					),
					'disable_admin_notices' => array(
						'id'   => 'disable_admin_notices',
						'name' => __( 'Disable Admin Notifications', 'kb-support' ),
						'desc' => sprintf( __( 'Check this box if you do not want to receive %s notification emails.', 'kb-support' ), kbs_get_ticket_label_singular() ),
						'type' => 'checkbox'
					)
				)
			)
		),
		/** Styles Settings */
		'styles' => apply_filters( 'kbs_settings_styles',
			array(
				'main' => array(
					'style_settings' => array(
						'id'   => 'style_settings',
						'name' => '<h3>' . __( 'Style Settings', 'kb-support' ) . '</h3>',
						'type' => 'header'
					),
					'disable_styles' => array(
						'id'   => 'disable_styles',
						'name' => __( 'Disable Styles', 'kb-support' ),
						'desc' => __( 'Check this to disable all included styling of buttons, checkout fields, and all other elements.', 'kb-support' ),
						'type' => 'checkbox'
					),
					'button_header' => array(
						'id'   => 'button_header',
						'name' => '<strong>' . __( 'Buttons', 'kb-support' ) . '</strong>',
						'desc' => sprintf( __( 'Options for submit %s buttons', 'kb-support' ), kbs_get_ticket_label_singular( true ) ),
						'type' => 'header'
					),
					'button_style' => array(
						'id'      => 'button_style',
						'name'    => __( 'Default Button Style', 'kb-support' ),
						'desc'    => __( 'Choose the style you want to use for the buttons.', 'kb-support' ),
						'type'    => 'select',
						'options' => kbs_get_button_styles()
					),
					'checkout_color' => array(
						'id'      => 'checkout_color',
						'name'    => __( 'Default Button Color', 'kb-support' ),
						'desc'    => __( 'Choose the color you want to use for the buttons.', 'kb-support' ),
						'type'    => 'color_select',
						'options' => kbs_get_button_colors()
					)
				)
			)
		),
		/** Extension Settings */
		'extensions' => apply_filters( 'kbs_settings_extensions',
			array()
		),
		/** License Settings */
		'licenses' => apply_filters( 'kbs_settings_licenses',
			array()
		),
		/** Misc Settings */
		'misc' => apply_filters( 'kbs_settings_misc',
			array(
				'main' => array(
					'misc_settings_header' => array(
						'id'   => 'misc_settings_header',
						'name' => '<h3>' . __( 'Misc Settings', 'kb-support' ) . '</h3>',
						'type' => 'header'
					),
					'remove_on_uninstall' => array(
						'id'      => 'remove_on_uninstall',
						'name'    => __( 'Remove Data on Uninstall?', 'kb-support' ),
						'desc'    => __( 'Check this box if you would like KBS to completely remove all of its data when the plugin is deleted.', 'kb-support' ),
						'type'    => 'checkbox'
					)
				),
				'submit' => array(
					'submit_settings_header' => array(
						'id'   => 'submit_settings_header',
						'name' => '<h3>' . __( 'Submission Settings', 'kb-support' ) . '</h3>',
						'type' => 'header'
					),
					'file_uploads' => array(
						'id'      => 'file_uploads',
						'name'    => __( 'Allow File Uploads', 'kb-support' ),
						'desc'    => sprintf( __( 'Maximum number of files that can be attached during %s creation or response.', 'kb-support' ), kbs_get_ticket_label_singular( true ) ),
						'type'    => 'number',
						'size'    => 'small',
						'max'     => '10',
						'std'     => '0'
					),
					'logged_in_only' => array(
						'id'      => 'logged_in_only',
						'name'    => __( 'Disable Guest Submissions?', 'kb-support' ),
						'desc'    => sprintf( __( 'Require that users be logged in to submit %s.', 'kb-support' ), kbs_get_ticket_label_plural( true ) ),
						'type'    => 'checkbox',
						'std'     => '1'
					),
					'show_register_form' => array(
						'id'      => 'show_register_form',
						'name'    => __( 'Show Register / Login Form?', 'kb-support' ),
						'desc'    => __( 'Display the registration and login forms on the submission page for non-logged-in users.', 'kb-support' ),
						'type'    => 'select',
						'std'     => 'none',
						'options' => array(
							'both'         => __( 'Registration and Login Forms', 'kb-support' ),
							'registration' => __( 'Registration Form Only', 'kb-support' ),
							'login'        => __( 'Login Form Only', 'kb-support' ),
							'none'         => __( 'None', 'kb-support' ),
						)
					)
				),
				'recaptcha'     => array(
					'recaptcha_settings' => array(
						'id'   => 'recaptcha_settings',
						'name' => '<h3>' . __( 'Google reCaptcha Settings', 'kb-support' ) . '</h3>',
						'type' => 'header'
					),
					'recaptcha_site_key' => array(
						'id'   => 'recaptcha_site_key',
						'name' => __( 'Site Key', 'kb-support' ),
						'desc' => sprintf( __( 'Visit <a href="%s" target="_blank">Google reCaptcha</a> to register your site and obtain your site key.', 'kb-support' ), 'https://www.google.com/recaptcha/' ),
						'type' => 'text'
					),
					'recaptcha_theme' => array(
						'id'      => 'recaptcha_theme',
						'name'    => __( 'reCaptcha Theme', 'kb-support' ),
						'desc'    => __( 'Select your preferred color scheme.', 'kb-support' ),
						'type'    => 'select',
						'options' => array( 'dark' => __( 'Dark', 'kb-support' ), 'light' => __( 'Light', 'kb-support' ) ),
						'std'     => 'light'
					),
					'recaptcha_type' => array(
						'id'      => 'recaptcha_type',
						'name'    => __( 'reCaptcha Type', 'kb-support' ),
						'desc'    => __( 'Choose to render an audio reCaptcha or an image. Default is image.', 'kb-support' ),
						'type'    => 'select',
						'options' => array( 'audio' => __( 'Audio', 'kb-support' ), 'image' => __( 'Image', 'kb-support' ) ),
						'std'     => 'image'
					),
					'recaptcha_size' => array(
						'id'      => 'recaptcha_size',
						'name'    => __( 'reCaptcha Size', 'kb-support' ),
						'desc'    => __( 'Select your preferred size for the reCaptcha.', 'kb-support' ),
						'type'    => 'select',
						'options' => array( 'compact' => __( 'Compact', 'kb-support' ), 'normal' => __( 'Normal', 'kb-support' ) ),
						'std'     => 'normal'
					)
					
				),
				'site_terms'     => array(
					'terms_settings' => array(
						'id'   => 'terms_settings',
						'name' => '<h3>' . __( 'Agreement Settings', 'kb-support' ) . '</h3>',
						'type' => 'header',
					),
					'show_agree_to_terms' => array(
						'id'   => 'show_agree_to_terms',
						'name' => __( 'Agree to Terms', 'kb-support' ),
						'desc' => sprintf( __( 'Check this to show an agree to terms on the submission page that users must agree to before submitting their %s.', 'kb-support' ), kbs_get_ticket_label_singular( true ) ),
						'type' => 'checkbox',
					),
					'agree_label' => array(
						'id'   => 'agree_label',
						'name' => __( 'Agree to Terms Label', 'kb-support' ),
						'desc' => __( 'Label shown next to the agree to terms check box.', 'kb-support' ),
						'type' => 'text',
						'size' => 'regular',
					),
					'agree_text' => array(
						'id'   => 'agree_text',
						'name' => __( 'Agreement Text', 'kb-support' ),
						'desc' => __( 'If Agree to Terms is checked, enter the agreement terms here.', 'kb-support' ),
						'type' => 'rich_editor',
					),
				)
			)
		)
	);

	return apply_filters( 'kbs_registered_settings', $kbs_settings );
}

/**
 * Settings Sanitization.
 *
 * Adds a settings error (for the updated message)
 * At some point this will validate input.
 *
 * @since	0.1
 *
 * @param	arr	$input	The value inputted in the field.
 *
 * @return	str	$input	Sanitizied value.
 */
function kbs_settings_sanitize( $input = array() ) {

	global $kbs_options;

	if ( empty( $_POST['_wp_http_referer'] ) ) {
		return $input;
	}

	parse_str( $_POST['_wp_http_referer'], $referrer );

	$settings = kbs_get_registered_settings();
	$tab      = isset( $referrer['tab'] ) ? $referrer['tab'] : 'general';
	$section  = isset( $referrer['section'] ) ? $referrer['section'] : 'main';

	$input = $input ? $input : array();

	$input = apply_filters( 'kbs_settings_' . $tab . '-' . $section . '_sanitize', $input );
	if ( 'main' === $section )  {
		// Check for extensions that aren't using new sections
		$input = apply_filters( 'kbs_settings_' . $tab . '_sanitize', $input );

		// Check for an override on the section for when main is empty
		if ( ! empty( $_POST['kbs_section_override'] ) ) {
			$section = sanitize_text_field( $_POST['kbs_section_override'] );
		}
	}

	// Loop through each setting being saved and pass it through a sanitization filter
	foreach ( $input as $key => $value ) {

		// Get the setting type (checkbox, select, etc)
		$type = isset( $settings[ $tab ][ $key ]['type'] ) ? $settings[ $tab ][ $key ]['type'] : false;

		if ( $type ) {
			// Field type specific filter
			$input[ $key ] = apply_filters( 'kbs_settings_sanitize_' . $type, $value, $key );
		}

		// General filter
		$input[ $key ] = apply_filters( 'kbs_settings_sanitize', $input[ $key ], $key );
	}

	// Loop through the whitelist and unset any that are empty for the tab being saved
	$main_settings    = $section == 'main' ? $settings[ $tab ] : array(); // Check for extensions that aren't using new sections
	$section_settings = ! empty( $settings[ $tab ][ $section ] ) ? $settings[ $tab ][ $section ] : array();

	$found_settings = array_merge( $main_settings, $section_settings );

	if ( ! empty( $found_settings ) ) {
		foreach ( $found_settings as $key => $value ) {

			// Settings used to have numeric keys, now they have keys that match the option ID. This ensures both methods work
			if ( is_numeric( $key ) ) {
				$key = $value['id'];
			}

			if ( empty( $input[ $key ] ) && isset( $kbs_options[ $key ] ) ) {
				unset( $kbs_options[ $key ] );
			}

		}
	}

	// Merge our new settings with the existing
	$output = array_merge( $kbs_options, $input );

	add_settings_error( 'kbs-notices', '', __( 'Settings updated.', 'kb-support' ), 'updated' );

	return $output;
} // kbs_settings_sanitize

/**
 * Sanitize text fields
 *
 * @since	0.1
 * @param	arr		$input	The field value
 * @return	str		$input	Sanitizied value
 */
function kbs_sanitize_text_field( $input ) {
	return trim( $input );
} // kbs_sanitize_text_field
add_filter( 'kbs_settings_sanitize_text', 'kbs_sanitize_text_field' );

/**
 * Retrieve settings tabs
 *
 * @since	0.1
 * @return	arr		$tabs
 */
function kbs_get_settings_tabs() {

	$settings = kbs_get_registered_settings();

	$tabs             = array();
	$tabs['general']  = __( 'General', 'kb-support' );
	$tabs['sla']      = __( 'Service Levels', 'kb-support' );
	$tabs['emails']   = __( 'Emails', 'kb-support' );
	$tabs['styles']   = __( 'Styles', 'kb-support' );

	if( ! empty( $settings['extensions'] ) ) {
		$tabs['extensions'] = __( 'Extensions', 'kb-support' );
	}
	if( ! empty( $settings['licenses'] ) ) {
		$tabs['licenses'] = __( 'Licenses', 'kb-support' );
	}
	
	$tabs['misc']   = __( 'Misc', 'kb-support' );

	return apply_filters( 'kbs_settings_tabs', $tabs );
} // kbs_get_settings_tabs

/**
 * Retrieve settings tabs
 *
 * @since	0.1
 * @return	arr		$section
 */
function kbs_get_settings_tab_sections( $tab = false ) {

	$tabs     = false;
	$sections = kbs_get_registered_settings_sections();

	if( $tab && ! empty( $sections[ $tab ] ) ) {
		$tabs = $sections[ $tab ];
	} else if ( $tab ) {
		$tabs = false;
	}

	return $tabs;
} // kbs_get_settings_tab_sections

/**
 * Get the settings sections for each tab
 * Uses a static to avoid running the filters on every request to this function
 *
 * @since	0.1
 * @return	arr		Array of tabs and sections
 */
function kbs_get_registered_settings_sections() {

	static $sections = false;

	if ( false !== $sections ) {
		return $sections;
	}

	$sections = array(
		'general'    => apply_filters( 'kbs_settings_sections_general', array(
			'main'                 => __( 'General Settings', 'kb-support' ),
			'pages'                => __( 'Pages', 'kb-support' )
		) ),
		'sla'        => apply_filters( 'kbs_settings_sections_sla', array(
			'main'                 => __( 'Service Levels', 'kb-support' )
		) ),
		'emails'     => apply_filters( 'kbs_settings_sections_emails', array(
			'main'                 => __( 'Emails', 'kb-support' ),
			'ticket_received'      => sprintf( __( '%s Received', 'kb-support' ), kbs_get_ticket_label_singular() ),
			'ticket_notifications' => sprintf( __( '%s Notifications', 'kb-support' ), kbs_get_ticket_label_singular() )
		) ),
		'styles'     => apply_filters( 'kbs_settings_sections_styles', array(
			'main'                 => __( 'Styles', 'kb-support' )
		) ),
		'extensions' => apply_filters( 'kbs_settings_sections_extensions', array(
			'main'                 => __( 'Main', 'kb-support' )
		) ),
		'licenses'   => apply_filters( 'kbs_settings_sections_licenses', array() ),
		'misc'       => apply_filters( 'kbs_settings_sections_misc', array(
			'main'                 => __( 'Misc Settings', 'kb-support' ),
			'submit'               => __( 'Submission Settings', 'kb-support' ),
			'recaptcha'            => __( 'Google reCaptcha', 'kb-support' ),
			'site_terms'           => __( 'Terms and Conditions', 'kb-support' )
		) )
	);

	$sections = apply_filters( 'kbs_settings_sections', $sections );

	return $sections;
} // kbs_get_registered_settings_sections

/**
 * Retrieve a list of all published pages.
 *
 * On large sites this can be expensive, so only load if on the settings page or $force is set to true
 *
 * @since	0.1
 * @param	bool	$force			Force the pages to be loaded even if not on settings
 * @return	arr		$pages_options	An array of the pages
 */
function kbs_get_pages( $force = false ) {

	$pages_options = array( '' => '' ); // Blank option

	if( ( ! isset( $_GET['page'] ) || 'kbs-settings' != $_GET['page'] ) && ! $force ) {
		return $pages_options;
	}

	$pages = get_pages();
	if ( $pages ) {
		foreach ( $pages as $page ) {
			$pages_options[ $page->ID ] = $page->post_title;
		}
	}

	return $pages_options;
} // kbs_get_pages

/**
 * Header Callback
 *
 * Renders the header.
 *
 * @since	0.1
 * @param	arr		$args	Arguments passed by the setting
 * @return	void
 */
function kbs_header_callback( $args ) {
	echo '';
} // kbs_header_callback

/**
 * Checkbox Callback
 *
 * Renders checkboxes.
 *
 * @since	0.1
 * @param	arr		$args	Arguments passed by the setting
 * @global	$kbs_options	Array of all the KBS Options
 * @return	void
 */
function kbs_checkbox_callback( $args ) {
	global $kbs_options;

	if ( isset( $args['faux'] ) && true === $args['faux'] ) {
		$name = '';
	} else {
		$name = 'name="kbs_settings[' . kbs_sanitize_key( $args['id'] ) . ']"';
	}

	$checked = isset( $kbs_options[ $args['id'] ] ) ? checked( 1, $kbs_options[ $args['id'] ], false ) : '';
	$html = '<input type="checkbox" id="kbs_settings[' . kbs_sanitize_key( $args['id'] ) . ']"' . $name . ' value="1" ' . $checked . '/>';
	$html .= '<label for="kbs_settings[' . kbs_sanitize_key( $args['id'] ) . ']"> '  . wp_kses_post( $args['desc'] ) . '</label>';

	echo $html;
} // kbs_checkbox_callback

/**
 * Multicheck Callback
 *
 * Renders multiple checkboxes.
 *
 * @since	0.1
 * @param	arr		$args	Arguments passed by the setting
 * @global	$kbs_options	Array of all the KBS Options
 * @return	void
 */
function kbs_multicheck_callback( $args ) {
	global $kbs_options;

	if ( ! empty( $args['options'] ) ) {
		foreach( $args['options'] as $key => $option ):
			if( isset( $kbs_options[$args['id']][$key] ) ) { $enabled = $option; } else { $enabled = NULL; }
			echo '<input name="kbs_settings[' . kbs_sanitize_key( $args['id'] ) . '][' . kbs_sanitize_key( $key ) . ']" id="kbs_settings[' . kbs_sanitize_key( $args['id'] ) . '][' . kbs_sanitize_key( $key ) . ']" type="checkbox" value="' . esc_attr( $option ) . '" ' . checked($option, $enabled, false) . '/>&nbsp;';
			echo '<label for="kbs_settings[' . kbs_sanitize_key( $args['id'] ) . '][' . kbs_sanitize_key( $key ) . ']">' . wp_kses_post( $option ) . '</label><br/>';
		endforeach;
		echo '<p class="description">' . $args['desc'] . '</p>';
	}
} // kbs_multicheck_callback

/**
 * Radio Callback
 *
 * Renders radio boxes.
 *
 * @since	0.1
 * @param	arr		$args	Arguments passed by the setting
 * @global	$kbs_options	Array of all the KBS Options
 * @return	void
 */
function kbs_radio_callback( $args ) {
	global $kbs_options;

	foreach ( $args['options'] as $key => $option ) :
		$checked = false;

		if ( isset( $kbs_options[ $args['id'] ] ) && $kbs_options[ $args['id'] ] == $key )
			$checked = true;
		elseif( isset( $args['std'] ) && $args['std'] == $key && ! isset( $kbs_options[ $args['id'] ] ) )
			$checked = true;

		echo '<input name="kbs_settings[' . kbs_sanitize_key( $args['id'] ) . ']" id="kbs_settings[' . kbs_sanitize_key( $args['id'] ) . '][' . kbs_sanitize_key( $key ) . ']" type="radio" value="' . kbs_sanitize_key( $key ) . '" ' . checked(true, $checked, false) . '/>&nbsp;';
		echo '<label for="kbs_settings[' . kbs_sanitize_key( $args['id'] ) . '][' . kbs_sanitize_key( $key ) . ']">' . esc_html( $option ) . '</label><br/>';
	endforeach;

	echo '<p class="description">' . wp_kses_post( $args['desc'] ) . '</p>';
} // kbs_radio_callback

/**
 * Text Callback
 *
 * Renders text fields.
 *
 * @since	0.1
 * @param	arr		$args	Arguments passed by the setting
 * @global	$kbs_options	Array of all the KBS Options
 * @return	void
 */
function kbs_text_callback( $args ) {
	global $kbs_options;

	if ( isset( $kbs_options[ $args['id'] ] ) ) {
		$value = $kbs_options[ $args['id'] ];
	} else {
		$value = isset( $args['std'] ) ? $args['std'] : '';
	}

	if ( isset( $args['faux'] ) && true === $args['faux'] ) {
		$args['readonly'] = true;
		$value = isset( $args['std'] ) ? $args['std'] : '';
		$name  = '';
	} else {
		$name = 'name="kbs_settings[' . esc_attr( $args['id'] ) . ']"';
	}

	$readonly = $args['readonly'] === true ? ' readonly="readonly"' : '';
	$size     = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
	$html     = '<input type="text" class="' . sanitize_html_class( $size ) . '-text" id="kbs_settings[' . kbs_sanitize_key( $args['id'] ) . ']" ' . $name . ' value="' . esc_attr( stripslashes( $value ) ) . '"' . $readonly . '/>';
	$html    .= '<label for="kbs_settings[' . kbs_sanitize_key( $args['id'] ) . ']"> '  . wp_kses_post( $args['desc'] ) . '</label>';

	echo $html;
} // kbs_text_callback

/**
 * Number Callback
 *
 * Renders number fields.
 *
 * @since	0.1
 * @param	arr		$args	Arguments passed by the setting
 * @global	$kbs_options	Array of all the KBS Options
 * @return	void
 */
function kbs_number_callback( $args ) {
	global $kbs_options;

	if ( isset( $kbs_options[ $args['id'] ] ) ) {
		$value = $kbs_options[ $args['id'] ];
	} else {
		$value = isset( $args['std'] ) ? $args['std'] : '';
	}

	if ( isset( $args['faux'] ) && true === $args['faux'] ) {
		$args['readonly'] = true;
		$value = isset( $args['std'] ) ? $args['std'] : '';
		$name  = '';
	} else {
		$name = 'name="kbs_settings[' . esc_attr( $args['id'] ) . ']"';
	}

	$max  = isset( $args['max'] ) ? $args['max'] : 999999;
	$min  = isset( $args['min'] ) ? $args['min'] : 0;
	$step = isset( $args['step'] ) ? $args['step'] : 1;

	$size = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
	$html = '<input type="number" step="' . esc_attr( $step ) . '" max="' . esc_attr( $max ) . '" min="' . esc_attr( $min ) . '" class="' . sanitize_html_class( $size ) . '-text" id="kbs_settings[' . kbs_sanitize_key( $args['id'] ) . ']" ' . $name . ' value="' . esc_attr( stripslashes( $value ) ) . '"/>';
	$html .= '<label for="kbs_settings[' . kbs_sanitize_key( $args['id'] ) . ']"> '  . wp_kses_post( $args['desc'] ) . '</label>';

	echo $html;
} // kbs_number_callback

/**
 * Textarea Callback
 *
 * Renders textarea fields.
 *
 * @since	0.1
 * @param	arr		$args	Arguments passed by the setting
 * @global	$kbs_options	Array of all the KBS Options
 * @return	void
 */
function kbs_textarea_callback( $args ) {
	global $kbs_options;

	if ( isset( $kbs_options[ $args['id'] ] ) ) {
		$value = $kbs_options[ $args['id'] ];
	} else {
		$value = isset( $args['std'] ) ? $args['std'] : '';
	}

	$html = '<textarea class="large-text" cols="50" rows="5" id="kbs_settings[' . kbs_sanitize_key( $args['id'] ) . ']" name="kbs_settings[' . esc_attr( $args['id'] ) . ']">' . esc_textarea( stripslashes( $value ) ) . '</textarea>';
	$html .= '<label for="kbs_settings[' . kbs_sanitize_key( $args['id'] ) . ']"> '  . wp_kses_post( $args['desc'] ) . '</label>';

	echo $html;
} // kbs_textarea_callback

/**
 * Password Callback
 *
 * Renders password fields.
 *
 * @since	0.1
 * @param	arr		$args	Arguments passed by the setting
 * @global	$kbs_options	Array of all the KBS Options
 * @return	void
 */
function kbs_password_callback( $args ) {
	global $kbs_options;

	if ( isset( $kbs_options[ $args['id'] ] ) ) {
		$value = $kbs_options[ $args['id'] ];
	} else {
		$value = isset( $args['std'] ) ? $args['std'] : '';
	}

	$size = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
	$html = '<input type="password" class="' . sanitize_html_class( $size ) . '-text" id="kbs_settings[' . kbs_sanitize_key( $args['id'] ) . ']" name="kbs_settings[' . esc_attr( $args['id'] ) . ']" value="' . esc_attr( $value ) . '"/>';
	$html .= '<label for="kbs_settings[' . kbs_sanitize_key( $args['id'] ) . ']"> ' . wp_kses_post( $args['desc'] ) . '</label>';

	echo $html;
} // kbs_password_callback

/**
 * Missing Callback
 *
 * If a function is missing for settings callbacks alert the user.
 *
 * @since	0.1
 * @param	arr		$args	Arguments passed by the setting
 * @return	void
 */
function kbs_missing_callback($args) {
	printf(
		__( 'The callback function used for the %s setting is missing.', 'kb-support' ),
		'<strong>' . $args['id'] . '</strong>'
	);
} // kbs_missing_callback

/**
 * Select Callback
 *
 * Renders select fields.
 *
 * @since	0.1
 * @param	arr		$args	Arguments passed by the setting
 * @global	$kbs_options	Array of all the KBS Options
 * @return	void
 */
function kbs_select_callback($args) {
	global $kbs_options;

	if ( isset( $kbs_options[ $args['id'] ] ) ) {
		$value = $kbs_options[ $args['id'] ];
	} else {
		$value = isset( $args['std'] ) ? $args['std'] : '';
	}

	if ( isset( $args['placeholder'] ) ) {
		$placeholder = $args['placeholder'];
	} else {
		$placeholder = '';
	}

	if ( isset( $args['chosen'] ) ) {
		$chosen = 'class="kbs-chosen"';
	} else {
		$chosen = '';
	}

	$html = '<select id="kbs_settings[' . kbs_sanitize_key( $args['id'] ) . ']" name="kbs_settings[' . esc_attr( $args['id'] ) . ']" ' . $chosen . 'data-placeholder="' . esc_html( $placeholder ) . '" />';

	foreach ( $args['options'] as $option => $name ) {
		$selected = selected( $option, $value, false );
		$html .= '<option value="' . esc_attr( $option ) . '" ' . $selected . '>' . esc_html( $name ) . '</option>';
	}

	$html .= '</select>';
	$html .= '<label for="kbs_settings[' . kbs_sanitize_key( $args['id'] ) . ']"> ' . wp_kses_post( $args['desc'] ) . '</label>';

	echo $html;
} // kbs_select_callback

/**
 * Color select Callback
 *
 * Renders color select fields.
 *
 * @since	0.1
 * @param	arr		$args	Arguments passed by the setting
 * @global	$kbs_options Array of all the KBS Options
 * @return	void
 */
function kbs_color_select_callback( $args ) {
	global $kbs_options;

	if ( isset( $kbs_options[ $args['id'] ] ) ) {
		$value = $kbs_options[ $args['id'] ];
	} else {
		$value = isset( $args['std'] ) ? $args['std'] : '';
	}

	$html = '<select id="kbs_settings[' . kbs_sanitize_key( $args['id'] ) . ']" name="kbs_settings[' . esc_attr( $args['id'] ) . ']"/>';

	foreach ( $args['options'] as $option => $color ) {
		$selected = selected( $option, $value, false );
		$html .= '<option value="' . esc_attr( $option ) . '" ' . $selected . '>' . esc_html( $color['label'] ) . '</option>';
	}

	$html .= '</select>';
	$html .= '<label for="kbs_settings[' . kbs_sanitize_key( $args['id'] ) . ']"> '  . wp_kses_post( $args['desc'] ) . '</label>';

	echo $html;
} // kbs_color_select_callback

/**
 * Rich Editor Callback
 *
 * Renders rich editor fields.
 *
 * @since	0.1
 * @param	arr		$args	Arguments passed by the setting
 * @global	$kbs_options	Array of all the KBS Options
 * @global	$wp_version		WordPress Version
 */
function kbs_rich_editor_callback( $args ) {
	global $kbs_options, $wp_version;

	if ( isset( $kbs_options[ $args['id'] ] ) ) {
		$value = $kbs_options[ $args['id'] ];

		if( empty( $args['allow_blank'] ) && empty( $value ) ) {
			$value = isset( $args['std'] ) ? $args['std'] : '';
		}
	} else {
		$value = isset( $args['std'] ) ? $args['std'] : '';
	}

	$rows = isset( $args['size'] ) ? $args['size'] : 20;

	if ( $wp_version >= 3.3 && function_exists( 'wp_editor' ) ) {
		ob_start();
		wp_editor( stripslashes( $value ), 'kbs_settings_' . esc_attr( $args['id'] ), array( 'textarea_name' => 'kbs_settings[' . esc_attr( $args['id'] ) . ']', 'textarea_rows' => absint( $rows ) ) );
		$html = ob_get_clean();
	} else {
		$html = '<textarea class="large-text" rows="10" id="kbs_settings[' . kbs_sanitize_key( $args['id'] ) . ']" name="kbs_settings[' . esc_attr( $args['id'] ) . ']">' . esc_textarea( stripslashes( $value ) ) . '</textarea>';
	}

	$html .= '<br/><label for="kbs_settings[' . kbs_sanitize_key( $args['id'] ) . ']"> ' . wp_kses_post( $args['desc'] ) . '</label>';

	echo $html;
} // kbs_rich_editor_callback

/**
 * Upload Callback
 *
 * Renders upload fields.
 *
 * @since	0.1
 * @param	arr		$args	Arguments passed by the setting
 * @global	$kbs_options	Array of all the KBS Options
 * @return	void
 */
function kbs_upload_callback( $args ) {
	global $kbs_options;

	if ( isset( $kbs_options[ $args['id'] ] ) ) {
		$value = $kbs_options[ $args['id'] ];
	} else {
		$value = isset( $args['std'] ) ? $args['std'] : '';
	}

	$size = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
	$html = '<input type="text" class="' . sanitize_html_class( $size ) . '-text" id="kbs_settings[' . kbs_sanitize_key( $args['id'] ) . ']" name="kbs_settings[' . esc_attr( $args['id'] ) . ']" value="' . esc_attr( stripslashes( $value ) ) . '"/>';
	$html .= '<span>&nbsp;<input type="button" class="kbs_settings_upload_button button-secondary" value="' . __( 'Upload File', 'kb-support' ) . '"/></span>';
	$html .= '<label for="kbs_settings[' . kbs_sanitize_key( $args['id'] ) . ']"> ' . wp_kses_post( $args['desc'] ) . '</label>';

	echo $html;
} // kbs_upload_callback


/**
 * Color picker Callback
 *
 * Renders color picker fields.
 *
 * @since	0.1
 * @param	arr		$args	Arguments passed by the setting
 * @global	$kbs_options	Array of all the KBS Options
 * @return	void
 */
function kbs_color_callback( $args ) {
	global $kbs_options;

	if ( isset( $kbs_options[ $args['id'] ] ) ) {
		$value = $kbs_options[ $args['id'] ];
	} else {
		$value = isset( $args['std'] ) ? $args['std'] : '';
	}

	$default = isset( $args['std'] ) ? $args['std'] : '';

	$html = '<input type="text" class="kbs-color-picker" id="kbs_settings[' . kbs_sanitize_key( $args['id'] ) . ']" name="kbs_settings[' . esc_attr( $args['id'] ) . ']" value="' . esc_attr( $value ) . '" data-default-color="' . esc_attr( $default ) . '" />';
	$html .= '<label for="kbs_settings[' . kbs_sanitize_key( $args['id'] ) . ']"> '  . wp_kses_post( $args['desc'] ) . '</label>';

	echo $html;
} // kbs_color_callback

/**
 * Descriptive text callback.
 *
 * Renders descriptive text onto the settings field.
 *
 * @since	0.1
 * @param	arr		$args	Arguments passed by the setting
 * @return	void
 */
function kbs_descriptive_text_callback( $args ) {
	echo wp_kses_post( $args['desc'] );
} // kbs_descriptive_text_callback

/**
 * Registers the license field callback for Software Licensing
 *
 * @since	0.1
 * @param	arr		$args	Arguments passed by the setting
 * @global	$kbs_options	Array of all the KBS options
 * @return void
 */
if ( ! function_exists( 'kbs_license_key_callback' ) ) {
	function kbs_license_key_callback( $args ) {
		global $kbs_options;

		$messages = array();
		$license  = get_option( $args['options']['is_valid_license_option'] );

		if ( isset( $kbs_options[ $args['id'] ] ) ) {
			$value = $kbs_options[ $args['id'] ];
		} else {
			$value = isset( $args['std'] ) ? $args['std'] : '';
		}

		if( ! empty( $license ) && is_object( $license ) ) {

			// activate_license 'invalid' on anything other than valid, so if there was an error capture it
			if ( false === $license->success ) {

				switch( $license->error ) {

					case 'expired' :

						$class = 'error';
						$messages[] = sprintf(
							__( 'Your license key expired on %s. Please <a href="%s" target="_blank" title="Renew your license key">renew your license key</a>.', 'kb-support' ),
							date_i18n( get_option( 'date_format' ), strtotime( $license->expires, current_time( 'timestamp' ) ) ),
							'http://kb-support.com/checkout/?edd_license_key=' . $value
						);

						$license_status = 'license-' . $class . '-notice';

						break;

					case 'missing' :

						$class = 'error';
						$messages[] = sprintf(
							__( 'Invalid license. Please <a href="%s" target="_blank" title="Visit account page">visit your account page</a> and verify it.', 'kb-support' ),
							'http://kb-support.com/your-account'
						);

						$license_status = 'license-' . $class . '-notice';

						break;

					case 'invalid' :
					case 'site_inactive' :

						$class = 'error';
						$messages[] = sprintf(
							__( 'Your %s is not active for this URL. Please <a href="%s" target="_blank" title="Visit account page">visit your account page</a> to manage your license key URLs.', 'kb-support' ),
							$args['name'],
							'http://kb-support.com/your-account'
						);

						$license_status = 'license-' . $class . '-notice';

						break;

					case 'item_name_mismatch' :

						$class = 'error';
						$messages[] = sprintf( __( 'This is not a %s.', 'kb-support' ), $args['name'] );

						$license_status = 'license-' . $class . '-notice';

						break;

					case 'no_activations_left':

						$class = 'error';
						$messages[] = sprintf( __( 'Your license key has reached its activation limit. <a href="%s">View possible upgrades</a> now.', 'kb-support' ), 'http://kb-support.com/your-account/' );

						$license_status = 'license-' . $class . '-notice';

						break;

				}

			} else {

				switch( $license->license ) {

					case 'valid' :
					default:

						$class = 'valid';

						$now        = current_time( 'timestamp' );
						$expiration = strtotime( $license->expires, current_time( 'timestamp' ) );

						if( 'lifetime' === $license->expires ) {

							$messages[] = __( 'License key never expires.', 'kb-support' );

							$license_status = 'license-lifetime-notice';

						} elseif( $expiration > $now && $expiration - $now < ( DAY_IN_SECONDS * 30 ) ) {

							$messages[] = sprintf(
								__( 'Your license key expires soon! It expires on %s. <a href="%s" target="_blank" title="Renew license">Renew your license key</a>.', 'kb-support' ),
								date_i18n( get_option( 'date_format' ), strtotime( $license->expires, current_time( 'timestamp' ) ) ),
								'http://kb-support.com/checkout/?edd_license_key=' . $value
							);

							$license_status = 'license-expires-soon-notice';

						} else {

							$messages[] = sprintf(
								__( 'Your license key expires on %s.', 'kb-support' ),
								date_i18n( get_option( 'date_format' ), strtotime( $license->expires, current_time( 'timestamp' ) ) )
							);

							$license_status = 'license-expiration-date-notice';

						}

						break;

				}

			}

		} else {
			$license_status = null;
		}

		$size = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
		$html = '<input type="text" class="' . sanitize_html_class( $size ) . '-text" id="kbs_settings[' . kbs_sanitize_key( $args['id'] ) . ']" name="kbs_settings[' . kbs_sanitize_key( $args['id'] ) . ']" value="' . esc_attr( $value ) . '"/>';

		if ( ( is_object( $license ) && 'valid' == $license->license ) || 'valid' == $license ) {
			$html .= '<input type="submit" class="button-secondary" name="' . $args['id'] . '_deactivate" value="' . __( 'Deactivate License',  'kb-support' ) . '"/>';
		}

		$html .= '<label for="kbs_settings[' . kbs_sanitize_key( $args['id'] ) . ']"> '  . wp_kses_post( $args['desc'] ) . '</label>';

		if ( ! empty( $messages ) ) {
			foreach( $messages as $message ) {

				$html .= '<div class="kbs-license-data kbs-license-' . $class . '">';
					$html .= '<p>' . $message . '</p>';
				$html .= '</div>';

			}
		}

		wp_nonce_field( kbs_sanitize_key( $args['id'] ) . '-nonce', kbs_sanitize_key( $args['id'] ) . '-nonce' );

		if ( isset( $license_status ) ) {
			echo '<div class="' . $license_status . '">' . $html . '</div>';
		} else {
			echo '<div class="license-null">' . $html . '</div>';
		}
	}
} // kbs_license_key_callback

/**
 * Hook Callback
 *
 * Adds a do_action() hook in place of the field
 *
 * @since	0.1
 * @param	arr		$args	Arguments passed by the setting
 * @return	void
 */
function kbs_hook_callback( $args ) {
	do_action( 'kbs_' . $args['id'], $args );
} // kbs_hook_callback

/**
 * Set manage_ticket_settings as the cap required to save KBS settings pages
 *
 * @since	0.1
 * @return	str		Capability required
 */
function kbs_set_settings_cap() {
	return 'manage_ticket_settings';
} // kbs_set_settings_cap
add_filter( 'option_page_capability_kbs_settings', 'kbs_set_settings_cap' );

/**
 * Returns a select list for target response time option.
 *
 * @since	0.1
 * @param
 * @return	str		Array of selectable options for target response times.
 */
function kbs_get_response_time_options()	{
	
	$response_times = array(
		'1 hour'    => __( '1 Hour', 'mobile-dj-manager' ),
		'2 hours'   => __( '2 Hours', 'mobile-dj-manager' ),
		'3 hours'   => __( '3 Hours', 'mobile-dj-manager' ),
		'4 hours'   => __( '4 Hours', 'mobile-dj-manager' ),
		'5 hours'   => __( '5 Hours', 'mobile-dj-manager' ),
		'6 hours'   => __( '6 Hours', 'mobile-dj-manager' ),
		'7 hours'   => __( '7 Hours', 'mobile-dj-manager' ),
		'1 day'     => __( '1 Day', 'mobile-dj-manager' ),
		'1 day'     => __( '1 Day', 'mobile-dj-manager' ),
		'2 days'    => __( '2 Days', 'mobile-dj-manager' ),
		'3 days'    => __( '3 Days', 'mobile-dj-manager' ),
		'4 days'    => __( '4 Days', 'mobile-dj-manager' ),
		'5 days'    => __( '5 Days', 'mobile-dj-manager' ),
		'6 days'    => __( '6 Days', 'mobile-dj-manager' ),
		'1 week'    => __( '1 Week', 'mobile-dj-manager' ),
		'2 weeks'   => __( '2 Weeks', 'mobile-dj-manager' ),
		'3 weeks'   => __( '3 Weeks', 'mobile-dj-manager' ),
		'4 weeks'   => __( '4 Weeks', 'mobile-dj-manager' )
	);
	
	return apply_filters( 'kbs_get_response_time_options', $response_times );
	
} // kbs_get_response_time_options
