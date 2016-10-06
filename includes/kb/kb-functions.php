<?php
/**
 * KB Article Functions
 *
 * @package     KBS
 * @subpackage  Functions
 * @copyright   Copyright (c) 2016, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Get KB Articles
 *
 * Retrieve KB Articles from the database.
 *
 * This is a simple wrapper for KBS_Articles_Query.
 *
 * @since	1.0
 * @param	arr		$args		Arguments passed to get_articles
 * @return	obj		$articles	Articles retrieved from the database
 */
function kbs_get_articles( $args = array() ) {

	// Fallback to post objects to ensure backwards compatibility
	if( ! isset( $args['output'] ) ) {
		$args['output'] = 'posts';
	}

	$args     = apply_filters( 'kbs_get_articles_args', $args );
	$articles = new KBS_KB_Articles_Query( $args );
	return $articles->get_articles();
} // kbs_get_articles

/**
 * Get Hidden KB Article IDs
 *
 * Retrieve Hidden KB Articles from the database.
 *
 * This is a simple wrapper for KBS_Articles_Query.
 *
 * @since	1.0
 * @param	arr		$args		Arguments passed to get_articles
 * @return	arr		Array of article IDs retrieved from the database
 */
function kbs_get_restricted_articles()	{
	$args = array(
		'restricted' => true,
		'fields'     => 'ids'
	);

	return kbs_get_articles( $args );
} // kbs_get_restricted_articles

/**
 * Whether or not a user can view a KB Article.
 *
 * @since	1.0
 * @param	int|obj		$kb_article	A KB Article ID or post object.
 * @param	int			$user_id	The user ID.
 * @return	bool		True if the user can view the KB Article.
 */
function kbs_user_can_view_article( $kb_article, $user_id = 0 )	{
	if ( is_int( $kb_article ) )	{
		$kb_article = get_post( $kb_article );
	}

	$can_view = true;

	$logged_in_only = get_post_meta( $kb_article->ID, '_kbs_kb_logged_in_only', true );

	if ( $logged_in_only && ! is_user_logged_in() )	{
		$can_view = false;
	}

	/**
	 * Allow plugins to filter the response.
	 *
	 * @since	1.0
	 */
	return apply_filters( 'kbs_user_can_view_article', $can_view, $kb_article, $user_id );
} // kbs_user_can_view_article

/**
 * When a user is trying to view restricted content.
 *
 * @since	1.0
 * @return	str		Message displayed when content is restricted
 */
function kbs_article_content_is_restricted( $post = null )	{
	global $post;

	if ( is_archive() )	{
		$notice  = kbs_get_notices( 'article_restricted', true );
		$content = $notice;
	} else	{
		$content = kbs_display_notice( 'article_restricted_login' );
		$content .= kbs_login_form();
	}

	return $content;
} // kbs_article_content_is_restricted

/**
 * Hides restricted posts.
 *
 * @since	1.0
 * @param
 *
 */
function kbs_kb_hide_restricted_articles( $query )	{

	if ( is_admin() || ! is_post_type_archive( 'kbs_kb' ) || ! $query->is_main_query() )	{
		return;
	}

	if ( ! kbs_get_option( 'kb_hide_restricted', false ) )	{
		return;
	}

	$hidden_ids = kbs_get_restricted_articles();

	$query->set( 'post__not_in', $hidden_ids );

} // kbs_kb_hide_restricted_articles
add_action( 'pre_get_posts', 'kbs_kb_hide_restricted_articles' );

/**
 * Retrieve the total view count for a KB Article.
 *
 * @since	1.0
 * @param	int		$article_id		Post ID
 * @return	int
 */
function kbs_get_article_view_count( $article_id )	{
	$view_count = get_post_meta( $article_id, '_kb_article_views', true );
	
	if ( ! $view_count )	{
		$view_count = 0;
	}
	
	(int)$view_count;
	
	return $view_count;
} // kbs_get_article_view_count

/**
 * Increment the total view count for a KB Article.
 *
 * @since	1.0
 * @param	int		$article_id		Post ID
 * @return	int
 */
function kbs_increment_article_view_count( $article_id )	{
	$view_count = kbs_get_article_view_count( $article_id );
	
	return update_post_meta( $article_id, '_kb_article_views', $view_count++ );
} // kbs_get_article_view_count


