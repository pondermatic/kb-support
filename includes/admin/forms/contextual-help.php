<?php
/**
 * Contextual Help
 *
 * @package     KBS
 * @subpackage  Admin/Forms
 * @copyright   Copyright (c) 2017, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * New KB Form contextual help.
 *
 * @since       1.0
 * @return      void
 */
function kbs_form_new_contextual_help() {
	$screen = get_current_screen();

	if ( $screen->id != 'kbs_form' )	{
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
			esc_url( 'https://kb-support.com/extensions/' )
		) . '</p>'
	);

	$screen->add_help_tab( array(
		'id'      => 'kbs-form-add',
		'title'   => __( 'Add Form', 'kb-support' ),
		'content' =>
			'<p>' . __( 'Enter a title for your new submission form and then publish it to begin adding fields.', 'kb-support' ) . '</p>'
	) );

} // kbs_form_new_contextual_help
add_action( 'load-post-new.php', 'kbs_form_new_contextual_help' );

/**
 * KB Form contextual help.
 *
 * @since       1.0
 * @return      void
 */
function kbs_form_contextual_help() {
	$screen = get_current_screen();

	if ( $screen->id != 'kbs_form' )	{
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
			esc_url( 'https://kb-support.com/extensions/' )
		) . '</p>'
	);

	do_action( 'kbs_form_before_general_contextual_help' );
	$screen->add_help_tab( array(
		'id'      => 'kbs-ticket-general',
		'title'   => __( 'General', 'kb-support' ),
		'content' =>
			'<p>' . sprintf(
				__( 'Manage your submission field here by adding the fields you require. Once you\'re ready, add the shortcode %1$s to your submission page.', 'kb-support' ),
				'<code>' . kbs_get_form_shortcode( $_GET['post'] ) . '</code>'
			) . '</p>' .
			'<p>' .
				__( 'Re-arrange your fields using the drag and drop functionality. Changes are automatically saved.', 'kb-support' ) .
			'</p>' .
			'<p>' .
				__( 'We\'ve created the default fields for you. These cannot be deleted, but you can edit them to rename or adjust their settings.', 'kb-support' ) .
			'</p>'
	) );

	do_action( 'kbs_form_before_add_field_contextual_help' );
	$screen->add_help_tab( array(
		'id'      => 'kbs-ticket-add-field',
		'title'   => __( 'Add a New Field', 'kb-support' ),
		'content' =>
			'<ul>' .
				'<li>' .
					__( '<strong>Label</strong> - This will be the label for the field when displayed on your form.', 'kb-support' ) .
				'</li>' .
				'<li>' .
					__( '<strong>Description</strong> - If you want to display a description for your field, enter it here (optional). Select <em>After Label</em> to display the description after the field label but before the input field, or <em>After Field</em> to display the description after the input field.', 'kb-support' ) .
				'</li>' .
				'<li>' .
					__( '<strong>Type</strong> - The type of field you select will determine which options you have for the field.', 'kb-support' ) .
				'</li>' .
				'<li>' .
					__( '<strong>Required</strong> - Make this a required field. The form cannot be submitted if the field is not completed', 'kb-support' ) .
				'</li>' .
				'<li>' .
					__( '<strong>Label Class</strong> - Enter a custom CSS class you want to apply to the label element for this field (optional).', 'kb-support' ) .
				'</li>' .
				'<li>' .
					__( '<strong>Input Class</strong> - Enter a custom CSS class you want to apply to the input element for this field (optional).', 'kb-support' ) .
				'</li>' .
				'<li>' . sprintf(
					__( '<strong>Maps to</strong> - Certain fields can be mapped to specific %1$s fields. For example a text input field you are using as a Subject field, can be mapped to the %1$s title field and a textarea or Rich Text Editor field can be mapped to the %1$s content field. Each mapping can only be used once per form.', 'kb-support' ),
					strtolower( $ticket_singular )
				) . '</li>' .
				'<li>' . sprintf(
					__( '<strong>Enable %s Ajax Search?</strong> - If this option is selected for your field, once the customer has entered data into the field and focus moves to another element, an ajax search will be performed and potential %2$s solutions will be presented to the customer.', 'kb-support' ),
					$article_plural,
					$article_singular
				) . '</li>' .
			'<li>' .
				__( '<strong>Options</strong> - Displayed when the chosen field type is a select, checkbox or radio input. Enter the options that the customer can choose from (one per line).', 'kb-support' ) .
			'</li>' .
			'<li>' .
				__( '<strong>Multiple Select?</strong> - Displayed when the chosen field type is a select input. Enabling will render a select list where multiple options can be selected.', 'kb-support' ) .
			'</li>' .
			'<li>' .
				__( '<strong>Initially Selected?</strong> - For checkboxes, you can select this option to have it checked by default when the form is loaded.', 'kb-support' ) .
			'</li>' .
			'<li>' . sprintf( 
				__( '<strong>Searchable?</strong> - If the field type is a select field, you can choose to use the <a href="%s" target="_blank">jQuery Chosen plugin</a> which enables the customer to search the available options. Useful for select fields with many available options.', 'kb-support' ),
				'https://harvesthq.github.io/chosen/'
			) . '</li>' .
			'<li>' .
				__( '<strong>Placeholder</strong> - For a number of different field types you can set a placeholder here.', 'kb-support' ) .
			'</li>' .
			'<li>' .
				__( '<strong>Hide Label?</strong> - Choose to hide the field label. Perhaps use a placeholder instead.', 'kb-support' ) .
			'</li>'
	) );

	do_action( 'kbs_form_contextual_help' );

} // kbs_form_contextual_help
add_action( 'load-post.php', 'kbs_form_contextual_help' );
