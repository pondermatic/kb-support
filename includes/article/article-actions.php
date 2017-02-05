<?php
/**
 * KB Article Actions
 *
 * @package     KBS
 * @subpackage  Functions
 * @copyright   Copyright (c) 2017, Mike Howard
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
 * @return	void
 */
function kbs_search_articles_action()	{

	if ( ! isset( $_GET['kbs_action'] ) || 'search_articles' != $_GET['kbs_action'] )	{
		return;
	}

	$args = array(
		's'         => $_GET['s_article'],
		'post_type' => 'article'
	);

	$args = apply_filters( 'kbs_article_search', $args );

	$redirect = add_query_arg( $args, esc_url( home_url( '/' ) ) );

	wp_safe_redirect( $redirect );
	die();

} // kbs_search_articles_action
add_action( 'init', 'kbs_search_articles_action' );

/**
 * Creates a new article when a ticket is closed.
 *
 * @since	1.0
 * @return	void
 */
function kbs_create_article_action()	{

	if ( ! isset( $_GET['kbs-action'] ) || 'create_article' != $_GET['kbs-action'] )	{
		return;
	}

	$ticket = new KBS_Ticket( $_GET['ticket_id'] );

	if ( ! empty( $_GET['reply_id'] ) )	{
		$reply = get_post( $_GET['reply_id'] );
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
			'post'        => $_GET['ticket_id'],
			'kbs-message' => 'create_article_failed',
			'action'      => 'edit'
		);

	}

	$redirect = add_query_arg( $redirect_args, admin_url( 'post.php' ) );

	wp_safe_redirect( $redirect );
	die();

} // kbs_create_article_action
add_action( 'init', 'kbs_create_article_action' );
