<?php
/**
 * Company Functions
 *
 * @package     KBS
 * @subpackage  Companies/Functions
 * @copyright   Copyright (c) 2017, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Register a view for the single company view
 *
 * @since	1.0
 * @param	1.0		$views	An array of existing views
 * @return	arr		The altered list of views
 */
function kbs_register_default_company_views( $views ) {

	$default_views = array(
		'companydata'  => 'kbs_companies_view',
		'delete'       => 'kbs_companies_delete_view',
		'notes'        => 'kbs_company_notes_view',
		'add'          => 'kbs_render_add_company_view'
	);

	return array_merge( $views, $default_views );

} // kbs_register_default_company_views
add_filter( 'kbs_company_views', 'kbs_register_default_company_views', 1, 1 );

/**
 * Register a tab for the single company view
 *
 * @since	1.0
 * @param	arr		$tabs	An array of existing tabs
 * @return	arr		The altered list of tabs
 */
function kbs_register_default_company_tabs( $tabs ) {

	$default_tabs = array(
		'companydata' => array( 'dashicon' => 'dashicons-admin-building', 'title' => __( 'Company Profile', 'kb-support' ) ),
		'notes'       => array( 'dashicon' => 'dashicons-admin-comments', 'title' => __( 'Company Notes', 'kb-support' ) )
	);

	return array_merge( $tabs, $default_tabs );
} // kbs_register_default_company_tabs
add_filter( 'kbs_company_tabs', 'kbs_register_default_company_tabs', 1, 1 );

/**
 * Register the Delete icon as late as possible so it's at the bottom.
 *
 * @since	1.0
 * @param	1.0		$tabs	An array of existing tabs
 * @return	arr		The altered list of tabs, with 'delete' at the bottom
 */
function kbs_register_delete_company_tab( $tabs ) {

	$tabs['delete'] = array( 'dashicon' => 'dashicons-trash', 'title' => __( 'Delete Company', 'kb-support' ) );

	return $tabs;
} // kbs_register_delete_company_tab
add_filter( 'kbs_company_tabs', 'kbs_register_delete_company_tab', PHP_INT_MAX, 1 );
