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
 * @global	$kbs_articles_page
 * @return	void
 */
function kbs_add_options_link() {
	global $kbs_articles_page;

	$kbs_article            = get_post_type_object( 'kbs_article' );

	$kbs_articles_page      = add_submenu_page( 'edit.php?post_type=kbs_ticket', $kbs_article->labels->name, $kbs_article->labels->menu_name, 'edit_tickets', 'edit.php?post_type=kbs_article' );

} // kbs_add_options_link
add_action( 'admin_menu', 'kbs_add_options_link', 10 );