<?php
/**
 * Post Type Functions
 *
 * @package     KBS
 * @subpackage  Admin/Functions
 * @copyright   Copyright (c) 2016, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Creates the admin submenu pages under the Tickets menu and assigns their
 * links to global variables.
 *
 * @since	0.1
 * @param
 * @return
 */
function kbs_sidebar()	{


} // kbs_sidebar
add_action( 'admin_menu', 'kbs_sidebar' );