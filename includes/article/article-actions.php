<?php
/**
 * KB Article Actions
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
 * Process a search of the KB Articles.
 *
 * @since	1.0
 * @param	arr		$data	The search form post data.
 * @return	void
 */
function kbs_search_articles_action( $data )	{

	$args = array(
		's'         => $data['s_article'],
		'post_type' => 'article'
	);

	do_action( 'kbs_article_search', $data );

	$redirect = add_query_arg( $args, esc_url( home_url( '/' ) ) );

	wp_safe_redirect( $redirect );
	die();

} // kbs_search_articles_action
add_action( 'kbs_search_articles', 'kbs_search_articles_action' );

/**
 * Creates a new article when a ticket is closed.
 *
 * @since	1.0
 * @param	arr		$data	The search form post data.
 * @return	void
 */
function kbs_create_article_action( $data )	{

	$ticket = new KBS_Ticket( $data['ticket_id'] );

	if ( ! empty( $data['reply_id'] ) )	{
		$reply = get_post( $data['reply_id'] );
	} else	{
		$reply = kbs_get_last_reply( $ticket->ID );
	}

	if ( $reply )	{
		$args = array(
			'post_status'  => 'draft',
			'post_title'   => $ticket->ticket_title,
			'post_content' => $reply->post_content
		);

		$article_id = kbs_add_article( $args, $ticket->ID );
	} else	{
		$article_id = false;
	}

	if ( $article_id )	{

		$redirect_args = array(
			'post'        => $article_id,
			'kbs-message' => 'article_created',
			'action'      => 'edit'
		);

	} else	{

		$redirect_args = array(
			'post'        => $data['ticket_id'],
			'kbs-message' => 'create_article_failed',
			'action'      => 'edit'
		);

	}

	$redirect = add_query_arg( $redirect_args, admin_url( 'post.php' ) );

	wp_safe_redirect( $redirect );
	die();

} // kbs_create_article_action
add_action( 'kbs-create_article', 'kbs_create_article_action' );
