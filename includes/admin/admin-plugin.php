<?php
/**
 * Admin Plugin
 *
 * @package     KBS
 * @subpackage  Admin/Functions
 * @copyright   Copyright (c) 2017, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Plugins row action links
 *
 * @since	1.0
 * @param	arr		$links	Defined action links
 * @param	str		$file	Plugin file path and name being processed
 * @return	srr		Filtered action links
 */
function kbs_plugin_action_links( $links, $file )	{

	$settings_link = '<a href="' . admin_url( 'edit.php?post_type=kbs_ticket&page=kbs-settings' ) . '">' . esc_html__( 'Settings', 'kb-support' ) . '</a>';

	if ( $file == 'kb-support/kb-support.php' )	{
		array_unshift( $links, $settings_link );
	}

	return $links;

} // kbs_plugin_action_links
add_filter( 'plugin_action_links', 'kbs_plugin_action_links', 10, 2 );

/**
 * Plugin row meta links
 *
 * @since	1.0
 * @param	arr		$input	Defined meta links
 * @param	str		$file	Plugin file path and name being processed
 * @return	arr		Filtered meta links
 */
function kbs_plugin_row_meta( $input, $file )	{

	if ( $file != 'kb-support/kb-support.php' )	{
		return $input;
	}

	$links = array(
		'<a href="' . esc_url( 'https://kb-support.com/support/' ) . '" target="_blank">' . esc_html__( 'Documentation', 'kb-support' ) . '</a>',
		'<a href="' . esc_url( 'https://kb-support.com/extensions/' ) . '" target="_blank">' . esc_html__( 'Extensions', 'kb-support' ) . '</a>'
	);

	$input = array_merge( $input, $links );

	return $input;

} // kbs_plugin_row_meta
add_filter( 'plugin_row_meta', 'kbs_plugin_row_meta', 10, 2 );

/**
 * Adds rate us text to admin footer when KB Support admin pages are viewed.
 *
 * @since	1.0
 * @param	str		$footer_text	The footer text to output
 * @return	str		Filtered footer text for output
 */
function kbs_admin_footer_rate_us( $footer_text )	{
	global $typenow;

    $disable = kbs_get_option( 'remove_rating' );

	if ( ! $disable && ( 'kbs_ticket' == $typenow || KBS()->KB->post_type == $typenow || 'kbs_form' == $typenow ) )	{

		$footer_text = sprintf(
			__( 'If <strong>KB Support</strong> is helping you support your customers, please <a href="%s" target="_blank">leave us a ★★★★★ rating</a>. A <strong style="text-decoration: underline;">huge</strong> thank you in advance!', 'kb-support'
			),
			'https://wordpress.org/support/view/plugin-reviews/kb-support?rate=5#postform'
		);

	}

	return $footer_text;
} // kbs_admin_footer_rate_us
add_filter( 'admin_footer_text', 'kbs_admin_footer_rate_us' );

/**
 * Premium extensions data.
 *
 * @since	1.4.6
 * @return	array	Array of premium extension data
 */
function kbs_get_premium_extension_data()	{
	$extensions = array(
		'advanced_ticket_assignment' => array(
			'name'         => 'Advanced Ticket Assignment',
			'desc'         => __( 'Automate ticket assignment based on a number of rules that can be defined to provide an efficient support workflow.', 'kb-support' ),
			'plugin_url'   => 'kbs-advanced-ticket-assignment/kbs-advanced-ticket-assignment.php',
			'demo_url'     => 'https://demo.kb-support.com/?demo_ref=1892bfbbcd6410b5b34a2a6ee35e50fb',
			'purchase_url' => 'https://kb-support.com/downloads/advanced-ticket-assignment/'
		),
		'canned_replies' => array(
			'name'         => 'Canned Replies',
			'desc'         => __( 'Save time by enabling instant content to be added to ticket replies with the single click of a button.', 'kb-support' ),
			'plugin_url'   => 'kbs-canned-replies/kbs-canned-replies.php',
			'demo_url'     => 'https://demos.easy-plugin-demo.com/demos/kb-support-plugin-demo/',
			'purchase_url' => 'https://kb-support.com/downloads/canned-replies/'
		),
		'custom_ticket_status' => array(
			'name'         => 'Custom Ticket Status',
			'desc'         => __( 'Create additional ticket statuses to meet your business needs.', 'kb-support' ),
			'plugin_url'   => 'kbs-custom-status/kbs-custom-status.php',
			'demo_url'     => 'https://demo.kb-support.com/?demo_ref=60cfff6b93cc42216c03bc3886a5cb11',
			'purchase_url' => 'https://kb-support.com/downloads/custom-ticket-status/'
		),
		'easy_digital_downloads' => array(
			'name'         => 'Easy Digital Downloads',
			'desc'         => __( 'Integrate KB Support with your EDD store and Software Licensing extension for the ultimate customer experience.', 'kb-support' ),
			'plugin_url'   => 'kbs-edd/kbs-edd.php',
			'demo_url'     => 'https://demo.kb-support.com/register/?demo_ref=cb0277e636b56fe9ef4d1fcbd8603ae6',
			'purchase_url' => 'https://kb-support.com/downloads/easy-digital-downloads/'
		),
		'email_signatures' => array(
			'name'         => 'Email Signatures',
			'desc'         => __( 'Enable agents to register custom signatures which can be inserted into customer emails.', 'kb-support' ),
			'plugin_url'   => 'kbs-email-signatures/kbs-email-signatures.php',
			'purchase_url' => 'https://kb-support.com/downloads/email-signatures/'
		),
		'email_support' => array(
			'name'         => 'Email Support',
			'desc'         => __( 'Enable customers and agents to respond to tickets via email.', 'kb-support' ),
			'plugin_url'   => 'kbs-email-support/kbs-email-support.php',
			'demo_url'     => 'https://demo.kb-support.com/register/?demo_ref=6c847b75c663cf62807249618cc80a40',
			'purchase_url' => 'https://kb-support.com/downloads/email-support/'
		),
		'knowledge_base_integrations' => array(
			'name'         => 'Knowledge Base Integrations',
			'desc'         => __( 'Integrate your favourite knowledge base with KB Support.', 'kb-support' ),
			'plugin_url'   => 'kbs-kb-integrations/kbs-kb-integrations.php',
			'purchase_url' => 'https://kb-support.com/downloads/knowledge-base-integrations/'
		),
		'mailchimp_integration' => array(
			'name'         => 'MailChimp Integration',
			'desc'         => __( 'Enable customers to subscribe to your MailChimp lists during ticket submission.', 'kb-support' ),
			'plugin_url'   => 'kbs-mailchimp-integration/kbs-mailchimp-integration.php',
			'purchase_url' => 'https://kb-support.com/downloads/mailchimp-integration/'
		),
		'ratings_and_satisfaction' => array(
			'name'         => 'Ratings and Satisfaction',
			'desc'         => __( 'Get feedback on your performance for support tickets and quality of documentation.', 'kb-support' ),
			'plugin_url'   => 'kbs-ratings-satisfaction/kbs-ratings-satisfaction.php',
			'purchase_url' => 'https://kb-support.com/downloads/ratings-and-satisfaction/'
		),
		'reply_approvals' => array(
			'name'         => 'Reply Approvals',
			'desc'         => __( 'Add an approval process to selected agent ticket replies.', 'kb-support' ),
			'plugin_url'   => 'kbs-reply-approvals/kbs-reply-approvals.php',
			'purchase_url' => 'https://kb-support.com/downloads/reply-approvals/'
		),
		'woocommerce' => array(
			'name'         => 'WooCommerce',
			'desc'         => __( 'Integrate KB Support with your WooCommerce store for the ultimate customer experience.', 'kb-support' ),
			'plugin_url'   => 'kbs-woocommerce/kbs-woocommerce.php',
			'demo_url'     => 'https://demo.kb-support.com/register/?demo_ref=11c28e3c2627aabf93a2b1a6c1836fe2',
			'purchase_url' => 'https://kb-support.com/downloads/woocommerce/'
		)
	);

	return $extensions;
} // kbs_get_premium_extension_data
