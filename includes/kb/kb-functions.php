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

	$logged_in_only = kbs_get_option( 'kb_logged_in', false );

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
		$notice  = kbs_get_notices( 'article_restricted' );
		$content = $notice['notice'];
	} else	{
		$content = kbs_display_notice( 'article_restricted_login' );
		$content .= kbs_login_form();
	}

	return $content;
} // kbs_article_content_is_restricted

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


