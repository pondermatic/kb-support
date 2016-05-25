<?php
/**
 * KB Article Functions
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
 * Retrieve the total view count for a KB Article.
 *
 * @since	0.1
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
 * @since	0.1
 * @param	int		$article_id		Post ID
 * @return	int
 */
function kbs_increment_article_view_count( $article_id )	{
	$view_count = kbs_get_article_view_count( $article_id );
	
	return update_post_meta( $article_id, '_kb_article_views', $view_count++ );
} // kbs_get_article_view_count
