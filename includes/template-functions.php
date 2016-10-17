<?php
/**
 * Template Functions
 *
 * @package     KBS
 * @subpackage  Functions/Templates
 * @copyright   Copyright (c) 2016, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Display front end notices.
 *
 * @since	1.0
 * @return	str
 */
function kbs_notices()	{

	if( ! isset( $_GET, $_GET['kbs_notice'] ) )	{
		return;
	}

	echo kbs_display_notice( $_GET['kbs_notice'] );

} // kbs_display_notice
add_action( 'kbs_notices', 'kbs_notices' );

/**
 * The form submit button label.
 *
 * @since	1.0
 * @return	str		The label for the form submit button.
 */
function kbs_get_form_submit_label()	{
	return kbs_get_option( 'form_submit_label', sprintf( __( 'Submit %s', 'kb-support' ), kbs_get_ticket_label_singular() ) );
} // kbs_get_form_submit_label

/**
 * The ticket reply submit button label.
 *
 * @since	1.0
 * @return	str		The label for the ticket reply form submit button.
 */
function kbs_get_ticket_reply_label()	{
	return kbs_get_option( 'ticket_reply_label', __( 'Reply', 'kb-support' ) );
} // kbs_get_ticket_reply_label

/**
 * Output the hidden form fields.
 *
 * @since	1.0
 * @param	$form_id	The ID of the form on display.
 * @return	str
 */
function kbs_render_hidden_form_fields( $form_id )	{
	$hidden_fields = array(
		'kbs_form_id'  => $form_id,
		'kbs_honeypot' => '',
		'redirect'     => kbs_get_current_page_url(),
		'action'       => 'kbs_validate_ticket_form'
	);

	$hidden_fields = apply_filters( 'kbs_form_hidden_fields', $hidden_fields, $form_id );

	ob_start(); ?>

	<?php foreach( $hidden_fields as $key => $value ) : ?>
    	<input type="hidden" name="<?php echo $key; ?>" value="<?php echo $value; ?>" />
    <?php endforeach; ?>

    <?php echo ob_get_clean();
} // kbs_render_hidden_form_fields

/**
 * Output the hidden reply form fields.
 *
 * @since	1.0
 * @global	obj		$current_user	If logged in, the current user object
 * @param	int		$ticket_id		The ID of the form on display.
 * @return	arr		Hidden form fields for reply form
 */
function kbs_render_hidden_reply_fields( $ticket_id )	{
	global $current_user;

	$current_page  = kbs_get_current_page_url();
	remove_query_arg( array( 'kbs_notice', 'ticket' ), $current_page );

	$hidden_fields = array(
		'kbs_ticket_id'  => $ticket_id,
		'kbs_honeypot'   => '',
		'redirect'       => add_query_arg( 'ticket', $_GET['ticket'], $current_page ),
		'action'         => 'kbs_validate_ticket_reply_form'
	);

	// If logged in we don't need to display the email input but we still need to capture it
	// for form validation
	if ( is_user_logged_in() )	{
		$hidden_fields['kbs_confirm_email'] = $current_user->user_email;
	}

	$hidden_fields = apply_filters( 'kbs_reply_hidden_fields', $hidden_fields, $ticket_id );

	ob_start(); ?>

	<?php foreach( $hidden_fields as $key => $value ) : ?>
    	<input type="hidden" name="<?php echo $key; ?>" value="<?php echo $value; ?>" />
    <?php endforeach; ?>

    <?php echo ob_get_clean();
} // kbs_render_hidden_reply_fields

/**
 * Before Article Content
 *
 * Adds an action to the beginning of kb article post content that can be hooked to
 * by other functions.
 *
 * @since	1.0
 * @global	$post
 *
 * @param	str		$content	The the_content field of the kb article object
 * @return	str		The content with any additional data attached
 */
function kbs_before_article_content( $content ) {
	global $post;

	if ( $post && $post->post_type == 'article' && is_singular( 'article' ) && is_main_query() && ! post_password_required() ) {
		ob_start();
		do_action( 'kbs_before_article_content', $post->ID );
		$content = ob_get_clean() . $content;
	}

	return $content;
} // kbs_before_article_content
add_filter( 'the_content', 'kbs_before_article_content' );

/**
 * After Article Content
 *
 * Adds an action to the end of kb article post content that can be hooked to by
 * other functions.
 *
 * @since	1.0
 * @global	$post
 *
 * @param	str		$content	The the_content field of the kb article object
 * @return	str		The content with any additional data attached
 */
function kbs_after_article_content( $content ) {
	global $post;

	if ( $post && 'article' == $post->post_type && is_singular( 'article' ) && is_main_query() && ! post_password_required() ) {
		ob_start();
		do_action( 'kbs_after_article_content', $post->ID );
		$content .= ob_get_clean();
	}

	return $content;
} // kbs_after_article_content
add_filter( 'the_content', 'kbs_after_article_content', 100 );

/**
 * After Article Content for restricted content.
 *
 * Remove content if it should be restricted.
 *
 * @since	1.0
 * @global	$post
 *
 * @param	str		$content	The the_content field of the kb article object
 * @return	str		The content with any additional data attached
 */
function kbs_restrict_article_content( $content ) {
	global $post;

	if ( $post && 'article' == $post->post_type )	{

		if ( kbs_article_is_restricted() && ! is_user_logged_in() )	{

			// Remove comments
			add_filter( 'comments_open', '__return_false');
			add_filter( 'get_comments_number', '__return_false');

			if ( is_archive() )	{
				$content = kbs_article_content_is_restricted();
				$action = 'archive';
			} else	{
				$content = kbs_article_content_is_restricted();
				$action = 'single';
			}

			/**
			 * Allow plugins to hook into the actions taken when content is restricted.
			 *
			 * @param	obj		$post	The Article post object
			 * @since	1.0
			 */
			do_action( 'kbs_resctricted_article_' . $action, $post );

		}

	}

	if ( ! isset( $action ) || ! has_action( 'kbs_resctricted_article_' . $action ) )	{
		return $content;
	}
} // kbs_restrict_article_content
add_filter( 'the_content', 'kbs_restrict_article_content', 999 );

/**
 * Increment the post view count for articles when accessed.
 *
 * @since	1.0
 * @return	void
 */
function kbs_article_maybe_increment_views()	{
	if ( is_admin() && ! ( defined( 'DOING_AJAX' ) && DOING_AJAX ) )	{
		return;
	}

	if ( 'article' != get_post_type() || ! is_singular( 'article' ) )	{
		return;
	}

	if ( ! kbs_user_can_view_article( get_the_ID() ) )	{
		return;
	}

	kbs_increment_article_view_count( get_the_ID() );

} // kbs_article_maybe_increment_views
add_action( 'wp', 'kbs_article_maybe_increment_views' );

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
} // kbs_get_button_colors

/**
 * Get Button Styles
 *
 * Returns an array of button styles.
 *
 * @since	1.0
 * @return	arr		$styles		Button styles
 */
function kbs_get_button_styles() {
	$styles = array(
		'button'	=> __( 'Button', 'kb-support' ),
		'plain'     => __( 'Plain Text', 'kb-support' )
	);

	return apply_filters( 'kbs_button_styles', $styles );
} // kbs_get_button_styles

/**
 * Returns the path to the KBS templates directory
 *
 * @since	1.0
 * @return 	str
 */
function kbs_get_templates_dir() {
	return KBS_PLUGIN_DIR . 'templates';
} // kbs_get_templates_dir

/**
 * Returns the URL to the KBS templates directory
 *
 * @since	1.0
 * @return	str
 */
function kbs_get_templates_url() {
	return KBS_PLUGIN_URL . 'templates';
} // kbs_get_templates_url

/**
 * Retrieves a template part
 *
 * @since	1.0
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

	if ( isset( $name ) )	{
		$templates[] = $slug . '-' . $name . '.php';
	}

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
 * @since	1.0
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

		if ( $located ) {
			break;
		}
	}

	if ( ( true == $load ) && ! empty( $located ) )	{
		load_template( $located, $require_once );
	}

	return $located;
} // kbs_locate_template

/**
 * Returns a list of paths to check for template locations
 *
 * @since	1.0
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
 * @since	1.0
 * @return	str
*/
function kbs_get_theme_template_dir_name() {
	return trailingslashit( apply_filters( 'kbs_templates_dir', 'kbs_templates' ) );
} // kbs_get_theme_template_dir_name

/**
 * Adds KBS Version to the <head> tag
 *
 * @since	1.0
 * @return	void
*/
function kbs_version_in_header(){
	echo '<meta name="generator" content="KB Support v' . KBS_VERSION . '" />' . "\n";
}
add_action( 'wp_head', 'kbs_version_in_header' );