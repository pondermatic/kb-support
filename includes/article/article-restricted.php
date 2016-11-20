<?php
/**
 * Restricted KB Article Functions
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
 * Whether or not restricted articles should be hidden.
 *
 * @since	1.0
 * @return	bool
 */
function kbs_hide_restricted_articles()	{
	return kbs_get_option( 'article_hide_restricted', false );
} // kbs_hide_restricted_articles

/**
 * Checks whether the article has Restricted Access.
 *
 * @since	1.0
 * @param	int		$article_id		The article post ID
 * @return	bool	True if the article has restricted access, or false.
 */
function kbs_article_is_restricted( $article_id = 0 )	{

	if ( empty( $article_id ) || ! is_int( $article_id ) )	{
		$article_id = get_the_ID();
	}

	$restricted = false;

	$is_restricted = get_post_meta( $article_id, '_kbs_article_restricted', true );

	if ( $is_restricted ) {
		$restricted = true;
	}

	return (bool) apply_filters( 'kbs_article_restricted', $restricted, $article_id );

} // kbs_article_is_restricted

/**
 * Get Restricted KB Article IDs
 *
 * Retrieve Restricted KB Articles from the database.
 *
 * This is a simple wrapper for KBS_Articles_Query.
 *
 * @since	1.0
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
 * Retrieve article terms.
 *
 * @since	1.0
 * @param	int			$article_id		The article post ID
 * @return	arr			Array of term ids that are associated with the article
 */
function kbs_get_article_terms( $article_id = 0 )	{
	if ( empty( $article_id ) || ! is_int( $article_id ) )	{
		$article_id = get_the_ID();
	}

	return wp_get_post_terms( $article_id, 'article_category', array( 'fields' => 'ids' ) );
} // kbs_get_article_terms

/**
 * Get Restricted Term ID's
 *
 * Retrieve Restricted KB Article terms the database.
 *
 * This is a simple wrapper for WP_Terms_Query.
 *
 * @since	1.0
 * @param	arr		$args		Arguments passed to WP_Terms_Query
 * @return	arr		Array of term IDs retrieved from the database
 */
function kbs_get_restricted_terms( $args = array() )	{
	$defaults = array(
		'taxonomy'   => array( 'article_category', 'article_tag' ),
		'hide_empty' => false,
		'fields'     => 'ids',
		'meta_key'   => '_kbs_term_restricted',
		'meta_value' => '1'
	);

	$args = wp_parse_args( $args, $defaults );

	$query = new WP_Term_Query( $args );

	return kbs_get_articles( $args );
} // kbs_get_restricted_terms

/**
 * Whether or not a term is restricted.
 *
 * @since	1.0
 * @param	int		$term_id	The term ID.
 * @return	bool	True if restricted, or false
 */
function kbs_article_is_term_restricted( $term_id )	{
	return get_term_meta( $term_id, '_kbs_term_restricted', true );
} // kbs_article_is_term_restricted

/**
 * Whether or not a user can view a KB Article.
 *
 * @since	1.0
 * @param	int|obj		$article	A KB Article ID or post object.
 * @param	int			$user_id	The user ID.
 * @return	bool		True if the user can view the KB Article.
 */
function kbs_user_can_view_article( $article, $user_id = 0 )	{
	if ( is_int( $article ) )	{
		$article = get_post( $article );
	}

	$can_view = true;

	if ( ! is_user_logged_in() || ( kbs_hide_restricted_articles() && kbs_article_is_restricted( $article->ID ) ) )	{
		$can_view = false;
	}

	/**
	 * Allow plugins to filter the response.
	 *
	 * @since	1.0
	 */
	return apply_filters( 'kbs_user_can_view_article', $can_view, $article, $user_id );
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
 * Exclude restricted posts.
 *
 * @since	1.0
 * @return	void
 */
function kbs_articles_exclude_restricted( $query )	{

	if ( defined( 'DOING_AJAX' ) && DOING_AJAX )	{
		return;
	}

	if ( is_admin() || ! is_post_type_archive( 'article' ) || ! $query->is_main_query() )	{
		return;
	}

	if ( is_user_logged_in() || ! kbs_hide_restricted_articles() )	{
		return;
	}

	$hidden_ids = kbs_get_restricted_articles();

	$query->set( 'post__not_in', $hidden_ids );

} // kbs_articles_exclude_restricted
add_action( 'pre_get_posts', 'kbs_articles_exclude_restricted' );
