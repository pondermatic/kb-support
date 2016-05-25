<?php
/**
 * Scripts
 *
 * @package     KBS
 * @subpackage  Functions
 * @copyright   Copyright (c) 2016, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Load Scripts
 *
 * Enqueues the required scripts.
 *
 * @since	0.1
 * @return	void
 */
function kbs_load_scripts() {

	$js_dir = KBS_PLUGIN_URL . 'assets/js/';

	// Use minified libraries if SCRIPT_DEBUG is turned off
	$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

	// Load AJAX scripts, if enabled
	if ( ! kbs_is_ajax_disabled() ) {
		wp_register_script( 'kbs-ajax', $js_dir . 'kbs-ajax' . $suffix . '.js', array( 'jquery' ), KBS_VERSION );
		wp_enqueue_script( 'kbs-ajax' );

		wp_localize_script( 'kbs-ajax', 'kbs_scripts', apply_filters( 'kbs_ajax_script_vars', array(
			'ajaxurl'                 => kbs_get_ajax_url(),
			'permalinks'              => get_option( 'permalink_structure' ) ? '1' : '0'
		) ) );

	}
} // kbs_load_scripts
add_action( 'wp_enqueue_scripts', 'kbs_load_scripts' );

/**
 * Register Styles
 *
 * Checks the styles option and hooks the required filter.
 *
 * @since	0.1
 * @return	void
 */
function kbs_register_styles() {

	if ( kbs_get_option( 'disable_styles', false ) ) {
		return;
	}

	// Use minified libraries if SCRIPT_DEBUG is turned off
	$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

	$file          = 'kbs' . $suffix . '.css';
	$templates_dir = kbs_get_theme_template_dir_name();

	$child_theme_style_sheet    = trailingslashit( get_stylesheet_directory() ) . $templates_dir . $file;
	$child_theme_style_sheet_2  = trailingslashit( get_stylesheet_directory() ) . $templates_dir . 'kbs.css';
	$parent_theme_style_sheet   = trailingslashit( get_template_directory()   ) . $templates_dir . $file;
	$parent_theme_style_sheet_2 = trailingslashit( get_template_directory()   ) . $templates_dir . 'kbs.css';
	$kbs_plugin_style_sheet     = trailingslashit( kbs_get_templates_dir()    ) . $file;

	// Look in the child theme directory first, followed by the parent theme, followed by the KBS core templates directory
	// Also look for the min version first, followed by non minified version, even if SCRIPT_DEBUG is not enabled.
	// This allows users to copy just kbs.css to their theme
	if ( file_exists( $child_theme_style_sheet ) || ( ! empty( $suffix ) && ( $nonmin = file_exists( $child_theme_style_sheet_2 ) ) ) ) {
		if( ! empty( $nonmin ) ) {
			$url = trailingslashit( get_stylesheet_directory_uri() ) . $templates_dir . 'kbs.css';
		} else {
			$url = trailingslashit( get_stylesheet_directory_uri() ) . $templates_dir . $file;
		}
	} elseif ( file_exists( $parent_theme_style_sheet ) || ( ! empty( $suffix ) && ( $nonmin = file_exists( $parent_theme_style_sheet_2 ) ) ) ) {
		if( ! empty( $nonmin ) ) {
			$url = trailingslashit( get_template_directory_uri() ) . $templates_dir . 'kbs.css';
		} else {
			$url = trailingslashit( get_template_directory_uri() ) . $templates_dir . $file;
		}
	} elseif ( file_exists( $kbs_plugin_style_sheet ) || file_exists( $kbs_plugin_style_sheet ) ) {
		$url = trailingslashit( kbs_get_templates_url() ) . $file;
	}

	wp_register_style( 'kbs-styles', $url, array(), KBS_VERSION, 'all' );
	wp_enqueue_style( 'kbs-styles' );

} // kbs_register_styles
add_action( 'wp_enqueue_scripts', 'kbs_register_styles' );

/**
 * Load Admin Scripts
 *
 * Enqueues the required admin scripts.
 *
 * @since	0.1
 * @global	$post
 * @param	str		$hook	Page hook
 * @return	void
 */
function kbs_load_admin_scripts( $hook ) {

	if ( ! apply_filters( 'kbs_load_admin_scripts', kbs_is_admin_page(), $hook ) ) {
		return;
	}

	global $wp_version, $post;

	$js_dir  = KBS_PLUGIN_URL . 'assets/js/';
	$css_dir = KBS_PLUGIN_URL . 'assets/css/';

	// Use minified libraries if SCRIPT_DEBUG is turned off
	$suffix  = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

	$admin_deps = array();
	if ( ! kbs_is_admin_page( $hook, 'edit' ) && ! kbs_is_admin_page( $hook, 'new' ) ) {
		$admin_deps = array( 'jquery', 'inline-edit-post' );
	} else {
		$admin_deps = array( 'jquery' );
	}

	wp_register_script( 'kbs-admin-scripts', $js_dir . 'admin-scripts' . $suffix . '.js', $admin_deps, KBS_VERSION, false );
	wp_enqueue_script( 'kbs-admin-scripts' );

	wp_localize_script( 'kbs-admin-scripts', 'kbs_vars', array(
		'post_id'                 => isset( $post->ID ) ? $post->ID : null,
		'kbs_version'             => KBS_VERSION,
		'add_new_ticket'          => sprintf( __( 'Add New %s', 'kb-support' ), kbs_get_ticket_label_singular() ),
		'new_media_ui'            => apply_filters( 'kbs_use_35_media_ui', 1 ),
		'type_to_search'          => sprintf( __( 'Type to search %s', 'kb-support' ), kbs_get_kb_label_plural() ),
		'search_placeholder'      => sprintf( __( 'Type to search all %s', 'kb-support' ), kbs_get_kb_label_plural() )
	));

	if( function_exists( 'wp_enqueue_media' ) && version_compare( $wp_version, '3.5', '>=' ) ) {
		// Call for new media manager
		wp_enqueue_media();
	}

	wp_enqueue_script( 'jquery-ui-datepicker' );
	wp_enqueue_script( 'jquery-ui-dialog' );

	$ui_style = ( 'classic' == get_user_option( 'admin_color' ) ) ? 'classic' : 'fresh';
	wp_register_style( 'jquery-ui-css', $css_dir . 'jquery-ui-' . $ui_style . $suffix . '.css' );
	wp_enqueue_style( 'jquery-ui-css' );

	wp_enqueue_script( 'media-upload' );
	wp_enqueue_script( 'thickbox' );
	wp_enqueue_style( 'thickbox' );

	wp_register_style( 'kbs-admin', $css_dir . 'kbs-admin' . $suffix . '.css', KBS_VERSION );
	wp_enqueue_style( 'kbs-admin' );

} // kbs_load_admin_scripts
add_action( 'admin_enqueue_scripts', 'kbs_load_admin_scripts', 100 );
