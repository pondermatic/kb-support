<?php
/**
 * Contextual Help
 *
 * @package     KBS
 * @subpackage  Admin/Settings
 * @copyright   Copyright (c) 2016, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Settings contextual help.
 *
 * @since       1.0
 * @return      void
 */
function kbs_settings_contextual_help() {
	$screen = get_current_screen();

	$article_singular = kbs_get_article_label_singular();
	$article_plural   = kbs_get_article_label_plural();
	$ticket_singular  = kbs_get_ticket_label_singular();
	$ticket_plural    = kbs_get_ticket_label_plural();
	$settings         = kbs_get_registered_settings();

	$screen->set_help_sidebar(
		'<p><strong>' . __( 'More Information:', 'kb-support' ) . '</strong></p>' .
		'<p>' . sprintf( 
			__( '<a href="%s" target="_blank">Documentation</a>', 'kb-support' ), 
			esc_url( 'https://kb-support.com/support/' )
		) . '</p>' .
		'<p>' . sprintf( 
			__( '<a href="%s" target="_blank">Twitter</a>', 'kb-support' ), 
			esc_url( 'https://twitter.com/kbsupport_wp/' )
		) . '</p>' .
		'<p>' . sprintf( 
			__( '<a href="%s" target="_blank">Facebook</a>', 'kb-support' ), 
			esc_url( 'https://www.facebook.com/kbsupport/' )
		) . '</p>' .
		'<p>' . sprintf(
			__( '<a href="%s" target="_blank">Post an issue</a> on <a href="%s" target="_blank">GitHub</a>', 'kb-support' ),
			esc_url( 'https://github.com/KB-Support/kb-support/issues' ),
			esc_url( 'https://github.com/KB-Support/kb-support' )
		) . '</p>' .
		'<p>' . sprintf(
			__( '<a href="%s" target="_blank">Extensions</a>', 'kb-support' ),
			esc_url( 'https://kb-support.com/downloads/' )
		) . '</p>'
	);

	do_action( 'kbs_settings_before_general_contextual_help', $screen );
	$screen->add_help_tab( array(
		'id'      => 'kbs-settings-general',
		'title'   => __( 'General', 'kb-support' ),
		'content' =>
			'<p>' . __( '<strong>Page Settings</strong>', 'kb-support' ) . '</p>' .
			'<ul>' .
				'<li>' . sprintf( 
					__( '<strong>Submissions Page</strong> - The selected page should contain the shortcode <code>[kbs_submit]</code> and will be the page that your customers use to submit their %1$s.', 'kb-support' ),
					strtolower( $ticket_plural )
				) . '</li>' .
				'<li>' . sprintf( 
					__( '<strong>%1$s</strong> - The selected page should contain the shortcode <code>[kbs_tickets]</code> and will be the page that customers can use view and manage their %2$s, including submitting replies.', 'kb-support' ),
					$ticket_plural,
					strtolower( $ticket_plural )
				) . '</li>' .
			'</ul>'
	) );

	do_action( 'kbs_settings_before_tickets_contextual_help', $screen );
	$screen->add_help_tab( array(
		'id'      => 'kbs-settings-tickets',
		'title'   => $ticket_plural,
		'content' =>
			'<p>' . sprintf( __( '<strong>%s Settings</strong>', 'kb-support' ), $ticket_singular ) . '</p>' .
			'<ul>' .
				'<li>' . sprintf( 
					__( '<strong>Prefix for %1$s ID\'s</strong> - Enter what you would like your %2$s ID\'s to be prefixed with.', 'kb-support' ),
					$ticket_singular,
					strtolower( $ticket_singular )
				) . '</li>' .
				'<li>' . sprintf( 
					__( '<strong>Suffix for %1$s ID\'s</strong> - Enter what you would like your %2$s ID\'s to be suffixed with.', 'kb-support' ),
					$ticket_singular,
					strtolower( $ticket_singular )
				) . '</li>' .
				'<li>' . sprintf( 
					__( '<strong>Hide Closed %s?</strong> - By default when you view the admin %2$s screen, the <code>All</code> view includes all %2$s with all stauses. With this option enabled, closed %3$s will not be displayed unless you click the Closed view.', 'kb-support' ),
					$ticket_plural,
					strtolower( $ticket_singular ),
					strtolower( $ticket_plural )
				) . '</li>' .
			'</ul>' .
			'<p>' . __( '<strong>Submission Settings</strong>', 'kb-support' ) . '</p>' .
			'<ul>' .
				'<li>' . sprintf( 
					__( '<strong>Enforce SSL for Submissions?</strong> - If selected, the %1$s submission page must be presented securely via HTTPS. A valid SSL certificate is required.', 'kb-support' ),
					strtolower( $ticket_singular )
				) . '</li>' .
				'<li>' . sprintf(
					__( '<strong>Disable Guest Submissions?</strong> - Whether or not a guest user can submit a %1$s. If selected, the customer must be logged in.', 'kb-support' ),
					 strtolower( $ticket_singular )
				). '</li>' .
				'<li>' . __( '<strong>Show Register / Login Form?</strong> - If <strong>Disable Guest Submissions?</strong> is enabled, you can select to display the login form, registration form, or both if the customer is not logged in.', 'kb-support' ) . '</li>' .
				'<li>' . sprintf( 
					__( '<strong>Submit Label</strong> - Enter the text you would like displayed on the %1$s submission forms submit button.', 'kb-support' ),
					strtolower( $ticket_singular )
				) . '</li>' .
				'<li>' . sprintf( 
					__( '<strong>Reply Label</strong> - Enter the text you would like displayed on the %1$s Reply form submit button.', 'kb-support' ),
					strtolower( $ticket_singular )
				) . '</li>' .
				'<li>' . sprintf( 
					__( '<strong>Allow File Uploads</strong> - Enter the number of files a customer can upload when submitting a %1$s. Set to <code>0</code> to disable file uploads.', 'kb-support' ),
					$ticket_singular
				) . '</li>' .
				'<li>' . sprintf( 
					__( '<strong>Allowed File Types</strong> - Enter the file extensions that a customer can upload when submitting a %1$s. Seperate each file extension with a comma. If a customer attempts to upload a file with an extension that is not listed, they will receive an error.', 'kb-support' ),
					$ticket_singular
				) . '</li>' .
			'</ul>' .
			'<p>' . __( '<strong>Agent Settings</strong>', 'kb-support' ) . '</p>' .
			'<ul>' .
				'<li>' . sprintf(
					__( '<strong>Administrators are Agents?</strong> - Select this option if users with the WordPress Administrator role should be seen as agents. If not enabled, users with the Administrator role will not be able to create or view any %1$s.', 'kb-support' ),
					strtolower( $ticket_plural )
				) . '</li>' .
				'<li>' . sprintf( 
					__( '<strong>Restritct Agent %1$s View?</strong> - Enabling this option will result in Support Workers not being able to view %2$s that are assigned to other agents. The only %2$s they will be able to view will be those that are assigned to them, or those that are not assigned to any agent.', 'kb-support' ),
					$ticket_singular,
					strtolower( $ticket_plural )
				) . '</li>' .
				'<li>' . sprintf( 
					__( '<strong>Display Agent Status?</strong> - If selected, customers will be able to see an indicator that shows whether the agent assigned to their %1$s is actively online or not when looking at their %1$s details.', 'kb-support' ),
					strtolower( $ticket_singular )
				) . '</li>' .
				'<li>' . sprintf( 
					__( '<strong>Auto Assign new %1$s</strong> - You can automatically assign an agent to a new %2$s submitted via a submission form. Disable, or choose to auto assign to an agent with the least amount of active %3$s, or a random agent.', 'kb-support' ),
					$ticket_plural,
					strtolower( $ticket_singular ),
					strtolower( $ticket_plural )
				) . '</li>' .
				'<li>' . sprintf( 
					__( '<strong>Auto Assign on Access?</strong> - Unassigned %1$s can be automatically assigned to agents on access. Can be useful in stopping agents selectively choosing which %1$s to work on.', 'kb-support' ),
					strtolower( $ticket_plural )
				) . '</li>' .
			'</ul>'
	) );

	do_action( 'kbs_settings_before_articles_contextual_help', $screen );
	$screen->add_help_tab( array(
		'id'      => 'kbs-settings-articles',
		'title'   => $article_plural,
		'content' =>
			'<p>' . sprintf( __( '<strong>%s Settings</strong>', 'kb-support' ), $article_singular ) . '</p>' .
			'<ul>' .
				'<li>' . sprintf( 
					__( '<strong>Restrict %1$s</strong> - If selected then each newly created article, by default, will have the <strong>Restrict Access</strong> option enabled meaning that only logged in users can access. This option can be adjusted per individual %2$s.', 'kb-support' ),
					$article_plural,
					$article_singular
				) . '</li>' .
				'<li>' . sprintf( 
					__( '<strong>Hide Restricted %1$s</strong> - Select to hide restricted %1$s from archives if the user is logged out. They are always hidden from website search results.', 'kb-support' ),
					$article_plural
				) . '</li>' .
				'<li>' . sprintf( 
					__( '<strong>Restrict %1$s Search</strong> - If selected, restricted %1$s will be hidden from ajax search results - i.e on the %2$s submission form.', 'kb-support' ),
					$article_plural,
					strtolower( $ticket_singular )
				) . '</li>' .
				'<li>' . sprintf( 
					__( '<strong>Number of Results from Ajax</strong> - Enter the number of results you want an ajax search to return - i.e on the %1$s submission form.', 'kb-support' ),
					strtolower( $ticket_singular )
				) . '</li>' .
				'<li>' . sprintf( 
					__( '<strong>Search Excerpt Length</strong> - Enter the number of characters that should be displayed from a %1$s during an ajax search.', 'kb-support' ),
					strtolower( $ticket_singular )
				) . '</li>' .
			'</ul>'
	) );

	do_action( 'kbs_settings_before_emails_contextual_help', $screen );
	$screen->add_help_tab( array(
		'id'      => 'kbs-settings-emails',
		'title'   => __( 'Emails', 'kb-support' ),
		'content' =>
			'<p>' . __( '<strong>Email Settings</strong>', 'kb-support' ) . '</p>' .
			'<ul>' .
				'<li>' . __( '<strong>From Name</strong> - Enter the name you want KB Support generated emails to come from.', 'kb-support' ) . '</li>' .
				'<li>' . __( '<strong>From Email</strong> - Enter the email address KB Support generated emails should come from.', 'kb-support' ) . '</li>' .
				'<li>' . __( '<strong>Email Template</strong> - Select <code>Default Template</code> to send HTML emails, or select plain text only (no formatting).', 'kb-support' ) . '</li>' .
				'<li>' . __( '<strong>Logo</strong> - Upload your logo and it will appear at the top of all KB Support generated HTML emails.', 'kb-support' ) . '</li>' .
			'</ul>' .
			'<p>' . sprintf( __( '<strong>%s Logged</strong>', 'kb-support' ), $ticket_singular ) . '<br />' .
				sprintf( __( '<em>Adjust the settings for emails that are sent to a customer when they have logged a %1$s via a submission form.</em>', 'kb-support' ), strtolower( $ticket_singular ) ) . '</p>' .
			'<ul>' .
				'<li>' . sprintf( __( '<strong>Disable this Email</strong> - Select to stop emails being sent to the customer when they have logged a %1$s.', 'kb-support' ),
				strtolower( $ticket_singular )
				) . '</li>' .
				'<li>' . sprintf( __( '<strong>Email Subject</strong> - Enter the subject for the email sent to customers when a %1$s is logged by them.', 'kb-support' ),
				strtolower( $ticket_singular )
				) . '</li>' .
				'<li>' . sprintf( __( '<strong>Email Heading</strong> - Enter the heading to be displayed at the top of the email content for the email sent to customers when a %1$s is logged by them.', 'kb-support' ),
				strtolower( $ticket_singular )
				) . '</li>' .
				'<li>' . sprintf( __( '<strong>Content</strong> - Enter the content of the email that is sent to a customer when they have logged a %1$s. A list of email tags you can use are displayed under the textarea.', 'kb-support' ),
				strtolower( $ticket_singular )
				) . '</li>' .
			'</ul>' .
			'<p>' . __( '<strong>Reply Added</strong>', 'kb-support' ) . '<br />' .
				sprintf( __( '<em>Adjust the settings for emails that are sent to a customer when a Support Worker adds a reply to their %1$s.</em>', 'kb-support' ), strtolower( $ticket_singular ) ) . '</p>' .
			'<ul>' .
				'<li>' . sprintf( __( '<strong>Disable this Email</strong> - Select to stop emails being sent to the customer when they receive a reply to their %1$s.', 'kb-support' ),
				strtolower( $ticket_singular )
				) . '</li>' .
				'<li>' . sprintf( __( '<strong>Email Subject</strong> - Enter the subject for the email sent to customers when a Support Worker has added a reply to their %1$s.', 'kb-support' ),
				strtolower( $ticket_singular )
				) . '</li>' .
				'<li>' . sprintf( __( '<strong>Email Heading</strong> - Enter the heading to be displayed at the top of the email content for the email sent to customers when a reply is added to their %1$s.', 'kb-support' ),
				strtolower( $ticket_singular )
				) . '</li>' .
				'<li>' . sprintf( __( '<strong>Content</strong> - Enter the content of the email that is sent to a customer when they receive a reply to their %1$s. A list of email tags you can use are displayed under the textarea.', 'kb-support' ),
				strtolower( $ticket_singular )
				) . '</li>' .
			'</ul>' .
			'<p>' . sprintf( __( '<strong>%1$s Closed</strong>', 'kb-support' ), $ticket_singular ) . '<br />' .
				sprintf( __( '<em>Adjust the settings for emails that are sent to a customer when a Support Worker closes their %1$s.</em>', 'kb-support' ), strtolower( $ticket_singular ) ) . '</p>' .
			'<ul>' .
				'<li>' . sprintf( __( '<strong>Disable this Email</strong> - Select to stop emails being sent to the customer when their %1$s is closed.', 'kb-support' ),
				strtolower( $ticket_singular )
				) . '</li>' .
				'<li>' . sprintf( __( '<strong>Email Subject</strong> - Enter the subject for the email sent to customers when a Support Worker has closed their %1$s.', 'kb-support' ),
				strtolower( $ticket_singular )
				) . '</li>' .
				'<li>' . sprintf( __( '<strong>Email Heading</strong> - Enter the heading to be displayed at the top of the email content for the email sent to customers when a their %1$s is closed.', 'kb-support' ),
				strtolower( $ticket_singular )
				) . '</li>' .
				'<li>' . sprintf( __( '<strong>Content</strong> - Enter the content of the email that is sent to a customer when their %1$s is closed. A list of email tags you can use are displayed under the textarea.', 'kb-support' ),
				strtolower( $ticket_singular )
				) . '</li>' .
			'</ul>' .
			'<p>' . __( '<strong>Notifications</strong>', 'kb-support' ) . '<br />' .
				sprintf( __( '<em>Adjust the settings for emails that are sent to Support Workers when a new %1$s is logged.</em>', 'kb-support' ), strtolower( $ticket_singular ) ) . '</p>' .
			'<ul>' .
				'<li>' . sprintf( __( '<strong>Disable Admin Notifications</strong> - Select to stop emails being sent to Support Workers when a new %1$s is logged.', 'kb-support' ),
				strtolower( $ticket_singular )
				) . '</li>' .
				'<li>' . sprintf( __( '<strong>%1$s Notification Subject</strong> - Enter the subject for the email sent to Support Workers a new %2$s is logged.', 'kb-support' ),
				$ticket_singular,
				strtolower( $ticket_singular )
				) . '</li>' .
				'<li>' . sprintf( __( '<strong>%1$s Notification</strong> - Enter the content of the email that is sent to Support Workers when a %2$s reply is submitted from the a customer. A list of email tags you can use are displayed under the textarea.', 'kb-support' ),
				$ticket_singular,
				strtolower( $ticket_singular )
				) . '</li>' .
				'<li>' . __( '<strong>Notification Subject</strong> - Enter the subject for the email sent to Support Workers a new reply is added to a %2$s.', 'kb-support' ) . '</li>' .
				'<li>' . sprintf( __( '<strong>%1$s Reply Notification</strong> - Enter the content of the email that is sent to Support Workers when a %2$s is logged. A list of email tags you can use are displayed under the textarea.', 'kb-support' ),
				$ticket_singular,
				strtolower( $ticket_singular )
				) . '</li>' .
				'<li>' . sprintf( __( '<strong>%1$s Notification Emails</strong> - Enter a list of email addresses (one per line) that should be notified when a new %2$s is logged by a customer. Enter <code>%3$s</code> to include the assigned agent\'s email address.', 'kb-support' ),
				$ticket_singular,
				strtolower( $ticket_singular ),
				'{agent}'
				) . '</li>' .
			'</ul>'
	) );

	do_action( 'kbs_settings_before_styles_contextual_help', $screen );
	$screen->add_help_tab( array(
		'id'      => 'kbs-settings-styles',
		'title'   => __( 'Styles', 'kb-support' ),
		'content' =>
			'<p>' . __( '<strong>Disable Styles</strong> - Select this option to stop KB Support loading its CSS style sheet. All default formatting of forms, fields and all other elements will be inherited from your currently active theme.', 'kb-support' ) . '</p>'
	) );

	if ( ! empty( $settings['extensions'] ) )	{
		do_action( 'kbs_settings_before_extensions_contextual_help', $screen );
		$screen->add_help_tab( array(
			'id'      => 'kbs-settings-extensions',
			'title'   => __( 'Extensions', 'kb-support' ),
			'content' => apply_filters( 'kbs_settings_extensions_contextual_help',
				'<p>' . __( 'The configuration settings for any KB Support extensions you have installed are controlled here.', 'kb-support' ) . '</p>'
			)
		) );
	}

	if ( ! empty( $settings['licenses'] ) )	{
		do_action( 'kbs_settings_before_licenses_contextual_help', $screen );
		$screen->add_help_tab( array(
			'id'      => 'kbs-settings-licenses',
			'title'   => __( 'Licenses', 'kb-support' ),
			'content' =>
				'<p>' . __( 'If you have any of the KB Support premium extensions installed, you should enter their license keys here to ensure you receive the latest product updates.', 'kb-support' ) . '</p>'
		) );
		do_action( 'kbs_settings_licenses_contextual_help', $screen );
	}

	do_action( 'kbs_settings_before_misc_contextual_help', $screen );
	$screen->add_help_tab( array(
		'id'      => 'kbs-settings-misc',
		'title'   => __( 'Misc', 'kb-support' ),
		'content' =>
			'<p>' . __( '<strong>Misc Settings</strong>', 'kb-support' ) . '</p>' .
			'<ul>' .
				'<li>' . sprintf( __( '<strong>Remove Data on Uninstall?</strong> - Select to remove all KB Support data when the plugin is uninstalled. All %1$s, %2$s, Submission Forms, Customers and settings will be permanently deleted.', 'kb-support' ),
				$ticket_plural,
				$article_plural ) . '</li>' .
			'</ul>' .
			'<p>' . __( '<strong>Google reCaptcha</strong>', 'kb-support' ) . '<br />' .
				sprintf( __( '<em>If you want to use a Google reCaptcha within your %1$s submission form, you\'ll need to enter the settings here.</em>', 'kb-support' ), $ticket_singular ) . '</p>' .
			'<ul>' .
				'<li>' . __( '<strong>Site Key</strong> - Enter your Google reCaptcha site key here otherwise your reCaptcha field will not work.', 'kb-support' ) . '</li>' .
				'<li>' . __( '<strong>reCaptcha Theme</strong> - Select a theme for your reCaptcha that fits in best with your website.', 'kb-support' ) . '</li>' .
				'<li>' . __( '<strong>reCaptcha Type</strong> - Choose between a reCaptcha image or audio.', 'kb-support' ) . '</li>' .
				'<li>' . __( '<strong>reCaptcha Size</strong> - Select a compact or normal sized reCaptcha.', 'kb-support' ) . '</li>' .
			'</ul>' .
			'<p>' . __( '<strong>Terms and Conditions</strong>', 'kb-support' ) . '<br />' .
				sprintf( __( '<em>You may choose to display a terms and conditions agreement field on your %1$s submission forms. You can define the settings here.</em>', 'kb-support' ), $ticket_singular ) . '</p>' .
			'<ul>' .
				'<li>' . sprintf( __( '<strong>Agree to Terms</strong> - Select this option to insert a field into your %1$s submission form that customers must select to indicate they have read and agreed to your Terms and Conditions.', 'kb-support' ), strtolower( $ticket_singular ) ) . '</li>' .
				'<li>' . __( '<strong>Agree to Terms Label</strong> - This is the label that will accompany the checkbox for terms agreement.', 'kb-support' ) . '</li>' .
				'<li>' . __( '<strong>Terms Heading</strong> - Enter a heading that will appear at the top of the Terms and Conditions pop-up window.', 'kb-support' ) . '</li>' .
				'<li>' . __( '<strong>Agreement Text</strong> - Enter your Terms and Conditions here.', 'kb-support' ) . '</li>' .
			'</ul>'
	) );

	do_action( 'kbs_settings_contextual_help', $screen );

} // kbs_settings_contextual_help
add_action( 'load-kbs_ticket_page_kbs-settings', 'kbs_settings_contextual_help' );
