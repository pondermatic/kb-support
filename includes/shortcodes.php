<?php
/**
 * Scripts
 *
 * @package     KBS
 * @subpackage  Shortcodes
 * @copyright   Copyright (c) 2016, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Login Shortcode
 *
 * Shows a login form allowing users to users to log in. This function simply
 * calls the kbs_login_form function to display the login form.
 *
 * @since	1.0
 * @param	att		$atts	Shortcode attributes
 * @uses	kbs_login_form()
 * @return	str
 */
function kbs_login_form_shortcode( $atts ) {
	extract( shortcode_atts( array(
			'redirect' => '',
		), $atts, 'kbs_login' )
	);
	return kbs_login_form( $redirect );
} // kbs_login_form_shortcode
add_shortcode( 'kbs_login', 'kbs_login_form_shortcode' );

/**
 * Register Shortcode
 *
 * Shows a registration form allowing users to users to register for the site.
 *
 * @since	1.0
 * @param	arr		$atts		Shortcode attributes
 * @uses	kbs_register_form()
 * @return	str
 */
function kbs_register_form_shortcode( $atts ) {
	extract( shortcode_atts( array(
			'redirect' => '',
		), $atts, 'kbs_register' )
	);
	return kbs_register_form( $redirect );
} // kbs_register_form_shortcode
add_shortcode( 'kbs_register', 'kbs_register_form_shortcode' );

/**
 * Ticket Form Shortcode
 *
 * Displays the ticket submission form
 *
 * @since	1.0
 * @param	arr		$atts		Shortcode attributes
 * @return	str
 */
function kbs_submit_form_shortcode( $atts ) {

	if ( ! kbs_user_can_submit() )	{
		ob_start();
		echo kbs_display_notice( 'need_login' );

		$register_login = kbs_get_option( 'show_register_form', 'none' );

		if ( 'both' == $register_login || 'login' == $register_login )	{
			echo kbs_login_form( kbs_get_current_page_url() );
		}

		if ( 'both' == $register_login || 'registration' == $register_login )	{
			echo kbs_register_form( kbs_get_current_page_url() );
		}

		return ob_get_clean();
	}

	extract( shortcode_atts( array(
		'form' => 0,
		), $atts, 'kbs_submit' )
	);

	return kbs_display_form( $form );
} // kbs_submit_form_shortcode
add_shortcode( 'kbs_submit', 'kbs_submit_form_shortcode' );

/**
 * View Tickets Shortcode.
 *
 * Displays a customers ticket.
 *
 * @since	1.0
 * @param	arr		$atts		Shortcode attributes
 * @return	str
 */
function kbs_tickets_shortcode( $atts )	{
	ob_start();
	if ( ! isset( $_GET['ticket'] ) )	{
		echo kbs_display_notice( 'no_ticket' );
		return ob_get_clean();
	}

	kbs_get_template_part( 'view', 'ticket' );
	return ob_get_clean();
} // kbs_tickets_shortcode
add_shortcode( 'kbs_tickets', 'kbs_tickets_shortcode' );

/**
 * Articles Shortcode
 *
 * Displays Articles that meet the criteria set within given arguments.
 *
 * @since	1.0
 * @param	att		$atts	Shortcode attributes
 * @return	str
 */
function kbs_articles_shortcode( $atts )	{

	$args = shortcode_atts( array(
		'post__in'    => null,    // Article IDs to display
		'numberposts' => 20,      // Number of posts to display
		'orderby'     => 'views', // Order by
		'order'       => 'DESC',  // Order
		'tags'        => null,    // Tags to include
		'categories'  => null,    // Categories to include
		'excerpt'     => true,    // Whether to display an excerpt
		'length'      => kbs_get_option( 'kbs_article_excerpt_length', 100 ) // Length of excerpt. -1 for full content
		), $atts, 'kbs_articles'
	);

	$length  = $args['length'];
	$excerpt = $args['excerpt'];
	unset( $args['length'], $args['excerpt'] );

	if ( 'views' == $args['orderby'] )	{
		$args['meta_key'] = '_kb_article_views';
		$args['orderby']  = 'meta_value_num';
	}

	$args['post_type'] = 'kbs_kb';

	$articles_query = new WP_Query( $args );

	ob_start();

	if ( $articles_query->have_posts() ) : ?>
        <ul>

        <?php while ( $articles_query->have_posts() ) :
            $articles_query->the_post(); ?>
            <li><?php the_title(); ?></li>
        <?php endwhile; ?>

        </ul>

        <?php wp_reset_postdata(); ?>
    <?php else : ?>
        // no posts found
    <?php endif;

	return ob_get_clean();

} // kbs_articles_shortcode
add_shortcode( 'kbs_articles', 'kbs_articles_shortcode' );

/**
 * Search Form Shortcode
 *
 * Shows a search form allowing users to search KB Articles. This function simply
 * calls the kbs_kb_article_search_form function to display the search form.
 *
 * @since	1.0
 * @param	att		$atts	Shortcode attributes
 * @uses	kbs_kb_article_search_form()
 * @return	str
 */
function kbs_article_search_form_shortcode()	{
	return kbs_kb_article_search_form();
} // kbs_article_search_form_shortcode
add_shortcode( 'kbs_search', 'kbs_article_search_form_shortcode' );
