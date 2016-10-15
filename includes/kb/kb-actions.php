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
		's'         => $data['s_kb'],
		'post_type' => 'kbs_kb'
	);

	do_action( 'kbs_search_articles', $data );

	$redirect = add_query_arg( $args, esc_url( home_url( '/' ) ) );

	wp_safe_redirect( $redirect );
	die();

} // kbs_search_articles_action
add_action( 'kbs_search_kb_articles', 'kbs_search_articles_action' );
