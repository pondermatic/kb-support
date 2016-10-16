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
	$args     = apply_filters( 'kbs_get_articles_args', $args );
	$articles = new KBS_Articles_Query( $args );

	return $articles->get_articles();
} // kbs_get_articles

/**
 * Count Articles
 *
 * Returns the total number of articles.
 *
 * @since	1.0
 * @param	arr	$args	List of arguments to base the article count on
 * @return	arr	$count	Number of articles sorted by article date
 */
function kbs_count_articles( $args = array() ) {

	global $wpdb;

	$defaults = array(
		'agent'      => null,
		'author'     => null,
		'restricted' => null,
		's'          => null,
		'start-date' => null,
		'end-date'   => null
	);

	$args = wp_parse_args( $args, $defaults );

	$select = "SELECT p.post_status,count( * ) AS num_posts";
	$join = '';
	$where = "WHERE p.post_type = 'kbs_kb'";

	// Count articles for a search
	if( ! empty( $args['s'] ) ) {

		$search = $wpdb->esc_like( $args['s'] );
		$search = '%' . $search . '%';

		$where .= $wpdb->prepare( "AND ((p.post_title LIKE %s) OR (p.post_content LIKE %s))", $search, $search );

	}

	// Limit article count by author
	if ( ! empty( $args['author'] ) )	{
		$where .= $wpdb->prepare( " AND p.post_author = '%s'", $args['author'] );
	}

	// Limit article count by received date
	if ( ! empty( $args['start-date'] ) && false !== strpos( $args['start-date'], '-' ) ) {

		$date_parts = explode( '-', $args['start-date'] );
		$year       = ! empty( $date_parts[0] ) && is_numeric( $date_parts[0] ) ? $date_parts[0] : 0;
		$month      = ! empty( $date_parts[1] ) && is_numeric( $date_parts[1] ) ? $date_parts[1] : 0;
		$day        = ! empty( $date_parts[2] ) && is_numeric( $date_parts[2] ) ? $date_parts[2] : 0;

		$is_date    = checkdate( $month, $day, $year );
		if ( false !== $is_date ) {

			$date   = new DateTime( $args['start-date'] );
			$where .= $wpdb->prepare( " AND p.post_date >= '%s'", $date->format( 'Y-m-d' ) );

		}

		// Fixes an issue with the articles list table counts when no end date is specified (partly with stats class)
		if ( empty( $args['end-date'] ) ) {
			$args['end-date'] = $args['start-date'];
		}

	}

	if ( ! empty ( $args['end-date'] ) && false !== strpos( $args['end-date'], '-' ) ) {

		$date_parts = explode( '-', $args['end-date'] );
		$year       = ! empty( $date_parts[0] ) && is_numeric( $date_parts[0] ) ? $date_parts[0] : 0;
		$month      = ! empty( $date_parts[1] ) && is_numeric( $date_parts[1] ) ? $date_parts[1] : 0;
		$day        = ! empty( $date_parts[2] ) && is_numeric( $date_parts[2] ) ? $date_parts[2] : 0;

		$is_date    = checkdate( $month, $day, $year );
		if ( false !== $is_date ) {

			$date   = new DateTime( $args['end-date'] );
			$where .= $wpdb->prepare( " AND p.post_date <= '%s'", $date->format( 'Y-m-d' ) );

		}

	}

	$where = apply_filters( 'kbs_count_articles_where', $where );
	$join  = apply_filters( 'kbs_count_articles_join', $join );

	$query = "
		$select
		FROM $wpdb->posts p
		$join
		$where
		GROUP BY p.post_status
	";

	$cache_key = md5( $query );

	$count = wp_cache_get( $cache_key, 'counts' );

	if ( false !== $count ) {
		return $count;
	}

	$count = $wpdb->get_results( $query, ARRAY_A );
	$stats    = array();
	$total    = 0;
	$statuses = get_post_stati();

	foreach ( $statuses as $state ) {
		$stats[ $state ] = 0;
	}

	foreach ( (array) $count as $row ) {
		if ( ! in_array( $row['post_status'], $statuses ) )	{
			continue;
		}
		$stats[ $row['post_status'] ] = $row['num_posts'];
	}

	$stats = (object) $stats;
	wp_cache_set( $cache_key, $stats, 'counts' );

	return $stats;
} // kbs_count_articles

/**
 * Whether or not restricted articles should be hidden.
 *
 * @since	1.0
 * @return	bool
 */
function kbs_hide_restricted_articles()	{
	return kbs_get_option( 'kb_hide_restricted', false );
} // kbs_hide_restricted_articles

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
 * Whether or not the article is restricted.
 *
 * @since	1.0
 * @param	int		$post_id	The post ID
 * @return	bool	True if restricted
 */
function kbs_article_is_restricted( $post_id = 0 )	{
	global $post;

	if ( empty( $post_id ) && ! empty( $post ) )	{
		$post_id = $post->ID;
	}

	$restricted = get_post_meta( $post_id, '_kbs_kb_restricted', true );
	$restricted = apply_filters( 'kbs_article_restricted', $restricted, $post_id );

	return $restricted;
} // kbs_article_is_restricted

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

	if ( kbs_article_is_restricted( $kb_article->ID ) && ! is_user_logged_in() )	{
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
 * @return	void
 */
function kbs_kb_hide_restricted_articles( $query )	{

	if ( defined( 'DOING_AJAX' ) && DOING_AJAX )	{
		return;
	}

	if ( is_admin() || ! is_post_type_archive( 'kbs_kb' ) || ! $query->is_main_query() )	{
		return;
	}

	if ( ! kbs_hide_restricted_articles() || is_user_logged_in() )	{
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
	
	return apply_filters( 'kbs_article_view_count', $view_count );
} // kbs_get_article_view_count

/**
 * Increment the total view count for a KB Article.
 *
 * @since	1.0
 * @param	int		$article_id		Post ID
 * @return	bool
 */
function kbs_increment_article_view_count( $article_id )	{
	$view_count = kbs_get_article_view_count( $article_id );

	if ( ! $view_count )	{
		$view_count = 0;
	}

	$view_count++;

	return update_post_meta( $article_id, '_kb_article_views', $view_count );
} // kbs_increment_article_view_count

/**
 * Retrieve the KB Article excerpt.
 *
 * @since	1.0
 * @param	int		$article_id		Article ID
 * @return	str		The article excerpt.
 */
function kbs_get_article_excerpt( $article_id ) {

	if ( empty( $article_id ) )	{
		return;
	}

	if ( has_excerpt( $article_id ) )	{
		$excerpt = get_post_field( 'post_excerpt', $article_id );
	} else	{
		$excerpt = get_post_field( 'post_content', $article_id );
	}

	$num_words = kbs_get_option( 'kbs_article_excerpt_length', 100 );
	$num_words = apply_filters( 'kbs_article_excerpt_length', $num_words );

	$excerpt = wp_trim_words( $excerpt, $num_words, '&hellip;' );

	return apply_filters( 'kbs_ticket_excerpt', $excerpt );

} // kbs_get_article_excerpt
