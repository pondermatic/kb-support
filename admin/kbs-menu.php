<?php
	defined( 'ABSPATH' ) or die( "Direct access to this page is disabled!!!" );
	
/**
 * Define the the sidebar menu
 *
 *
 *
 *
 */
function kbs_sidebar()	{
	global $kbs_articles_page;
	
	$kbs_articles         = get_post_type_object( 'kbs_kb' );
	
	$kbs_articles_page       = add_submenu_page( 'edit.php?post_type=kbs_tickets', $kbs_articles->labels->name, $kbs_articles->labels->menu_name, 'kbs_user', 'edit.php?post_type=kbs_kb' );
	
} // kbs_sidebar
add_action( 'admin_menu', 'kbs_sidebar' );
	
?>