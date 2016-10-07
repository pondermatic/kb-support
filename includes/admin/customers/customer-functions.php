<?php
/**
 * Customer Functions
 *
 * @package     KBS
 * @subpackage  Customers/Functions
 * @copyright   Copyright (c) 2016, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Register a view for the single customer view
 *
 * @since	1.0
 * @param	1.0		$views	An array of existing views
 * @return	arr		The altered list of views
 */
function kbs_register_default_customer_views( $views ) {

	$default_views = array(
		'overview'  => 'edd_customers_view',
		'delete'    => 'edd_customers_delete_view'
	);

	return array_merge( $views, $default_views );

} // kbs_register_default_customer_views
add_filter( 'kbs_customer_views', 'kbs_register_default_customer_views', 1, 1 );

/**
 * Register a tab for the single customer view
 *
 * @since	1.0
 * @param	arr		$tabs	An array of existing tabs
 * @return	arr		The altered list of tabs
 */
function kbs_register_default_customer_tabs( $tabs ) {

	$default_tabs = array(
		'overview' => array( 'dashicon' => 'dashicons-admin-users', 'title' => __( 'Customer Profile', 'kb-support' ) )
	);

	return array_merge( $tabs, $default_tabs );
} // kbs_register_default_customer_tabs
add_filter( 'kbs_customer_tabs', 'kbs_register_default_customer_tabs', 1, 1 );
