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

	if ( $screen->id != 'kbs_ticket_page_kbs-settings' )	{
		return;
	}

	$article_singular = kbs_get_article_label_singular();
	$article_plural   = kbs_get_article_label_plural();
	$ticket_singular  = kbs_get_ticket_label_singular();
	$ticket_plural    = kbs_get_ticket_label_plural();

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
				'<li>' . __( '<strong>Administrators are Agents?</strong> - Select this option if users with the WordPress Administrator role should be seen as agents.', 'kb-support' ) . '</li>' .
				'<li>' . sprintf( 
					__( '<strong>Display Agent Status?</strong> - If selected, customers will be able to see an indicator that shows whether the agent assigned to their %1$s is actively online or not when looking at their %1$s details.', 'kb-support' ),
					strtolower( $ticket_singular )
				) . '</li>' .
			'</ul>' .
			'<p>' . __( '<strong>Submission Settings</strong>', 'kb-support' ) . '</p>' .
			'<ul>' .
				'<li>' . sprintf( 
					__( '<strong>Allow File Uploads</strong> - Enter the number of files a customer can upload when submitting a %1$s. Set to <code>0</code> to disable file uploads.', 'kb-support' ),
					$ticket_singular
				) . '</li>' .
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
			'</ul>' .
			'<p>' . sprintf( __( '<strong>%s Assignment</strong>', 'kb-support' ), $ticket_singular ) . '</p>' .
			'<ul>' .
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

	do_action( 'kbs_settings_contextual_help', $screen );
} // kbs_settings_contextual_help
add_action( 'load-kbs_ticket_page_kbs-settings', 'kbs_settings_contextual_help' );
