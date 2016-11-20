<?php
/**
 * Content for Restricted KB Articles
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
 * Article Content for restricted content.
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

		if ( kbs_article_is_restricted() )	{

			// Remove comments
			kbs_article_remove_comments();

			$content = kbs_article_content_is_restricted();
			$action  = is_archive() ? 'archive' : 'single';

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
 * Remove comments for restricted articles.
 *
 * @since	1.0
 * @return	void
 */
function kbs_article_remove_comments()	{
	add_filter( 'comments_open', '__return_false');
	add_filter( 'get_comments_number', '__return_false');
} // kbs_article_remove_comments
