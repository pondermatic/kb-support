<?php
/**
 * Template Functions
 *
 * @package     KBS
 * @subpackage  Functions/Templates
 * @copyright   Copyright (c) 2016, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Before Ticket Content
 *
 * Adds an action to the beginning of ticket post content that can be hooked to
 * by other functions.
 *
 * @since	0.1
 * @global	$post
 *
 * @param	str		$content	The the_content field of the download object
 * @return	str		The content with any additional data attached
 */
function kbs_before_ticket_content( $content ) {
	global $post;

	if ( $post && $post->post_type == 'kbs_ticket' && is_singular( 'kbs_ticket' ) && is_main_query() && ! post_password_required() ) {
		ob_start();
		do_action( 'kbs_before_ticket_content', $post->ID );
		$content = ob_get_clean() . $content;
	}

	return $content;
} // kbs_before_ticket_content
add_filter( 'the_content', 'kbs_before_ticket_content' );

/**
 * After Ticket Content
 *
 * Adds an action to the end of ticket post content that can be hooked to by
 * other functions.
 *
 * @since	0.1
 * @global	$post
 *
 * @param	str		$content	The the_content field of the download object
 * @return	str		The content with any additional data attached
 */
function kbs_after_ticket_content( $content ) {
	global $post;

	if ( $post && $post->post_type == 'kbs_ticket' && is_singular( 'kbs_ticket' ) && is_main_query() && !post_password_required() ) {
		ob_start();
		do_action( 'kbs_after_ticket_content', $post->ID );
		$content .= ob_get_clean();
	}

	return $content;
}
add_filter( 'the_content', 'kbs_after_ticket_content' );

/**
 * Get Button Colors
 *
 * Returns an array of button colors.
 *
 * @since	1.0
 * @return	arr		$colors		Button colors
 */
function kbs_get_button_colors() {
	$colors = array(
		'white'     => array(
			'label' => __( 'White', 'kb-support' ),
			'hex'   => '#ffffff'
		),
		'gray'      => array(
			'label' => __( 'Gray', 'kb-support' ),
			'hex'   => '#f0f0f0'
		),
		'blue'      => array(
			'label' => __( 'Blue', 'kb-support' ),
			'hex'   => '#428bca'
		),
		'red'       => array(
			'label' => __( 'Red', 'kb-support' ),
			'hex'   => '#d9534f'
		),
		'green'     => array(
			'label' => __( 'Green', 'kb-support' ),
			'hex'   => '#5cb85c'
		),
		'yellow'    => array(
			'label' => __( 'Yellow', 'kb-support' ),
			'hex'   => '#f0ad4e'
		),
		'orange'    => array(
			'label' => __( 'Orange', 'kb-support' ),
			'hex'   => '#ed9c28'
		),
		'dark-gray' => array(
			'label' => __( 'Dark Gray', 'kb-support' ),
			'hex'   => '#363636'
		),
		'inherit'	=> array(
			'label' => __( 'Inherit', 'kb-support' ),
			'hex'   => ''
		)
	);

	return apply_filters( 'kbs_button_colors', $colors );
} // kbs_button_colors

/**
 * Get Button Styles
 *
 * Returns an array of button styles.
 *
 * @since	0.1
 * @return	arr		$styles		Button styles
 */
function kbs_button_styles() {
	$styles = array(
		'button'	=> __( 'Button', 'kb-support' ),
		'plain'     => __( 'Plain Text', 'kb-support' )
	);

	return apply_filters( 'kbs_button_styles', $styles );
} // kbs_button_styles

/**
 * Returns the path to the KBS templates directory
 *
 * @since	0.1
 * @return 	str
 */
function kbs_get_templates_dir() {
	return KBS_PLUGIN_DIR . 'templates';
} // kbs_get_templates_dir

/**
 * Returns the URL to the KBS templates directory
 *
 * @since	0.1
 * @return	str
 */
function kbs_get_templates_url() {
	return KBS_PLUGIN_URL . 'templates';
} // kbs_get_templates_url

/**
 * Retrieves a template part
 *
 * @since	0.1
 *
 * Taken from bbPress
 *
 * @param	str		$slug
 * @param	str		$name 	Default null
 * @param	bool	$load
 *
 * @return	str
 *
 * @uses kbs_locate_template()
 * @uses load_template()
 * @uses get_template_part()
 */
function kbs_get_template_part( $slug, $name = null, $load = true ) {
	// Execute code for this part
	do_action( 'get_template_part_' . $slug, $slug, $name );

	// Setup possible parts
	$templates = array();
	if ( isset( $name ) )
		$templates[] = $slug . '-' . $name . '.php';
	$templates[] = $slug . '.php';

	// Allow template parts to be filtered
	$templates = apply_filters( 'kbs_get_template_part', $templates, $slug, $name );

	// Return the part that is found
	return kbs_locate_template( $templates, $load, false );
} // kbs_get_template_part

/**
 * Retrieve the name of the highest priority template file that exists.
 *
 * Searches in the STYLESHEETPATH before TEMPLATEPATH so that themes which
 * inherit from a parent theme can just overload one file. If the template is
 * not found in either of those, it looks in the theme-compat folder last.
 *
 * Taken from bbPress
 *
 * @since	0.1
 *
 * @param	str|arr		$template_names		Template file(s) to search for, in order.
 * @param	bool		$load				If true the template file will be loaded if it is found.
 * @param	bool		$require_once		Whether to require_once or require. Default true.
 *   Has no effect if $load is false.
 * @return atr			The template filename if one is located.
 */
function kbs_locate_template( $template_names, $load = false, $require_once = true ) {
	// No file found yet
	$located = false;

	// Try to find a template file
	foreach ( (array) $template_names as $template_name ) {

		// Continue if template is empty
		if ( empty( $template_name ) )
			continue;

		// Trim off any slashes from the template name
		$template_name = ltrim( $template_name, '/' );

		// try locating this template file by looping through the template paths
		foreach( kbs_get_theme_template_paths() as $template_path ) {

			if( file_exists( $template_path . $template_name ) ) {
				$located = $template_path . $template_name;
				break;
			}
		}

		if( $located ) {
			break;
		}
	}

	if ( ( true == $load ) && ! empty( $located ) )
		load_template( $located, $require_once );

	return $located;
} // kbs_locate_template

/**
 * Returns a list of paths to check for template locations
 *
 * @since	0.1
 * @return mixed|void
 */
function kbs_get_theme_template_paths() {

	$template_dir = kbs_get_theme_template_dir_name();

	$file_paths = array(
		1 => trailingslashit( get_stylesheet_directory() ) . $template_dir,
		10 => trailingslashit( get_template_directory() ) . $template_dir,
		100 => kbs_get_templates_dir()
	);

	$file_paths = apply_filters( 'kbs_template_paths', $file_paths );

	// sort the file paths based on priority
	ksort( $file_paths, SORT_NUMERIC );

	return array_map( 'trailingslashit', $file_paths );
} // kbs_get_theme_template_paths

/**
 * Returns the template directory name.
 *
 * Themes can filter this by using the kbs_templates_dir filter.
 *
 * @since	0.1
 * @return	str
*/
function kbs_get_theme_template_dir_name() {
	return trailingslashit( apply_filters( 'kbs_templates_dir', 'kbs_templates' ) );
} // kbs_get_theme_template_dir_name

/**
 * Adds KBS Version to the <head> tag
 *
 * @since	0.1
 * @return	void
*/
function kbs_version_in_header(){
	echo '<meta name="generator" content="KB Support v' . KBS_VERSION . '" />' . "\n";
}
add_action( 'wp_head', 'kbs_version_in_header' );