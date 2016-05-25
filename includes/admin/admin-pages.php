<?php
/**
 * Admin Pages
 *
 * @package     KBS
 * @subpackage  Admin/Pages
 * @copyright   Copyright (c) 2016, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Creates the admin submenu pages under the Tickets menu and assigns their
 * links to global variables
 *
 * @since	0.1
 * @global	$kbs_settings_page
 * @return	void
 */
function kbs_add_options_link() {

	global $kbs_settings_page;

	$kbs_settings_page      = add_submenu_page( 'edit.php?post_type=kbs_ticket', __( 'KB Support Settings', 'kb-support' ), __( 'Settings', 'kb-support' ), 'manage_ticket_settings', 'kbs-settings', 'kbs_options_page' );

} // kbs_add_options_link
add_action( 'admin_menu', 'kbs_add_options_link', 10 );

/**
 *  Determines whether the current admin page is a specific KBS admin page.
 *
 *  Only works after the `wp_loaded` hook, & most effective
 *  starting on `admin_menu` hook. Failure to pass in $view will match all views of $main_page.
 *  Failure to pass in $main_page will return true if on any KBS page
 *
 *  @since	0.1
 *
 *  @param	str		$page	Main page's slug
 *  @param	str		$view	Page view ( ex: `edit` or `delete` )
 *  @return	bool	True if KBS admin page we're looking for or an KBS page or if $page is empty, any KBS page
 */
function kbs_is_admin_page( $passed_page = '', $passed_view = '' ) {

	global $pagenow, $typenow;

	$found      = false;
	$post_type  = isset( $_GET['post_type'] )  ? strtolower( $_GET['post_type'] )  : false;
	$action     = isset( $_GET['action'] )     ? strtolower( $_GET['action'] )     : false;
	$taxonomy   = isset( $_GET['taxonomy'] )   ? strtolower( $_GET['taxonomy'] )   : false;
	$page       = isset( $_GET['page'] )       ? strtolower( $_GET['page'] )       : false;
	$view       = isset( $_GET['view'] )       ? strtolower( $_GET['view'] )       : false;
	$kbs_action = isset( $_GET['kbs-action'] ) ? strtolower( $_GET['kbs-action'] ) : false;
	$tab        = isset( $_GET['tab'] )        ? strtolower( $_GET['tab'] )        : false;

	switch ( $passed_page ) {
		case 'kbs_ticket':
			switch ( $passed_view ) {
				case 'list-table':
					if ( ( 'kbs_ticket' == $typenow || 'kbs_ticket' === $post_type ) && $pagenow == 'edit.php' ) {
						$found = true;
					}
					break;
				case 'edit':
					if ( ( 'kbs_ticket' == $typenow || 'kbs_ticket' === $post_type ) && $pagenow == 'post.php' ) {
						$found = true;
					}
					break;
				case 'new':
					if ( ( 'kbs_ticket' == $typenow || 'kbs_ticket' === $post_type ) && $pagenow == 'post-new.php' ) {
						$found = true;
					}
					break;
				default:
					if ( ( 'kbs_ticket' == $typenow || 'kbs_ticket' === $post_type ) || 'kbs_ticket' === $post_type || ( 'post-new.php' == $pagenow && 'kbs_ticket' === $post_type ) ) {
						$found = true;
					}
					break;
			}
			break;
		case 'categories':
			switch ( $passed_view ) {
				case 'list-table':
				case 'new':
					if ( ( 'kbs_ticket' == $typenow || 'kbs_ticket' === $post_type ) && $pagenow == 'edit-tags.php' && 'edit' !== $action && 'ticket_category' === $taxonomy ) {
						$found = true;
					}
					break;
				case 'edit':
					if ( ( 'kbs_ticket' == $typenow || 'kbs_ticket' === $post_type ) && $pagenow == 'edit-tags.php' && 'edit' === $action && 'ticket_category' === $taxonomy ) {
						$found = true;
					}
					break;
				default:
					if ( ( 'kbs_ticket' == $typenow || 'kbs_ticket' === $post_type ) && $pagenow == 'edit-tags.php' && 'ticket_category' === $taxonomy ) {
						$found = true;
					}
					break;
			}
			break;
		case 'tags':
			switch ( $passed_view ) {
				case 'list-table':
				case 'new':
					if ( ( 'kbs_ticket' == $typenow || 'kbs_ticket' === $post_type ) && $pagenow == 'edit-tags.php' && 'edit' !== $action && 'ticket_tax' === $taxonomy ) {
						$found = true;
					}
					break;
				case 'edit':
					if ( ( 'kbs_ticket' == $typenow || 'kbs_ticket' === $post_type ) && $pagenow == 'edit-tags.php' && 'edit' === $action && 'ticket_tax' === $taxonomy ) {
						$found = true;
					}
					break;
				default:
					if ( ( 'kbs_ticket' == $typenow || 'kbs_ticket' === $post_type ) && $pagenow == 'edit-tags.php' && 'ticket_tax' === $taxonomy ) {
						$found = true;
					}
					break;
			}
			break;
		case 'kb':
			switch ( $passed_view ) {
				case 'list-table':
					if ( ( 'kb' == $typenow || 'kb' === $post_type ) && $pagenow == 'edit.php' ) {
						$found = true;
					}
					break;
				case 'edit':
					if ( ( 'kb' == $typenow || 'kb' === $post_type ) && $pagenow == 'post.php' ) {
						$found = true;
					}
					break;
				case 'new':
					if ( ( 'kb' == $typenow || 'kb' === $post_type ) && $pagenow == 'post-new.php' ) {
						$found = true;
					}
					break;
				default:
					if ( ( 'kb' == $typenow || 'kb' === $post_type ) || 'kb' === $post_type || ( 'post-new.php' == $pagenow && 'kb' === $post_type ) ) {
						$found = true;
					}
					break;
			}
			break;
		case 'settings':
			switch ( $passed_view ) {
				case 'general':
					if ( ( 'kbs_ticket' == $typenow || 'kbs_ticket' === $post_type ) && $pagenow == 'edit.php' && 'kbs-settings' === $page && ( 'general' === $tab || false === $tab ) ) {
						$found = true;
					}
					break;
				case 'sla':
					if ( ( 'kbs_ticket' == $typenow || 'kbs_ticket' === $post_type ) && $pagenow == 'edit.php' && 'kbs-settings' === $page && ( 'sla' === $tab || false === $tab ) ) {
						$found = true;
					}
					break;
				case 'emails':
					if ( ( 'kbs_ticket' == $typenow || 'kbs_ticket' === $post_type ) && $pagenow == 'edit.php' && 'kbs-settings' === $page && 'emails' === $tab ) {
						$found = true;
					}
					break;
				default:
					if ( ( 'kbs_ticket' == $typenow || 'kbs_ticket' === $post_type ) && $pagenow == 'edit.php' && 'kbs-settings' === $page ) {
						$found = true;
					}
					break;
			}
			break;
	}

	return (bool) apply_filters( 'kbs_is_admin_page', $found, $page, $view, $passed_page, $passed_view );
} // kbs_is_admin_page
