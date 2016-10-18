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
	$args = shortcode_atts( array(
			'redirect' => '',
	), $atts, 'kbs_login' );

	return kbs_login_form( $args['redirect'] );
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
	$args = shortcode_atts( array(
		'redirect' => '',
	), $atts, 'kbs_register' );

	return kbs_register_form( $args['redirect'] );
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
		/**
		 * Allow plugins to change the screen displayed when a user cannot submit.
		 *
		 * @since	1.0
		 */
		if ( has_action( 'kbs_user_cannot_submit' ) )	{
			do_action( 'kbs_user_cannot_submit' );			
		} else	{
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
	} else	{

		$args = shortcode_atts( array(
			'form' => 0,
			), $atts, 'kbs_submit' );
	
		return kbs_display_form( $args['form'] );

	}
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
		'post__in'        => null,    // Article IDs to display
		'posts_per_page'  => 20,      // Number of posts to display
		'orderby'         => 'views', // Order by
		'order'           => 'DESC',  // Order
		'tags'            => 0,       // Tag IDs to include, comma seperate
		'categories'      => 0,       // Category IDs to include, comma seperate
		'excerpt'         => 1,       // Whether to display an excerpt
		'length'          => (int) kbs_get_option( 'kbs_article_excerpt_length', 100 ), // Length of excerpt. -1 for full content
		'hide_restricted' => (bool) kbs_hide_restricted_articles() // Whether to hide restricted articles
	), $atts, 'kbs_articles' );

	if ( ! empty( $args['tags'] ) )	{
		$args['tag__in'] = array( $args['tags'] );
	}

	if ( ! empty( $args['categories'] ) )	{
		$args['category__in'] = array( $args['categories'] );
	}

	if ( 'views' == $args['orderby'] )	{
		$args['meta_key'] = '_kbs_article_views';
		$args['orderby']  = 'meta_value_num';
	}

	$args['post_type'] = 'article';

	if ( empty ( $args['hide_restricted'] ) )	{
		remove_action( 'pre_get_posts', 'kbs_articles_exclude_restricted' );
	}

	$articles_query = new WP_Query( $args );

	if ( empty( $args['hide_restricted'] ) )	{
		add_action( 'pre_get_posts', 'kbs_articles_exclude_restricted' );
	}

	ob_start();

	if ( $articles_query->have_posts() ) : ?>
        <div class="kbs_articles_list">
            <ul>
    
            <?php while( $articles_query->have_posts() ) :
    
                $articles_query->the_post();
				$article_id     = get_the_ID(); ?>
    
                <?php if ( ! $args['hide_restricted'] || kbs_user_can_view_article( $article_id ) ) : ?>
    
                    <li>
                    	<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                        <?php if ( ! empty( $args['excerpt'] ) ) : ?>
                        	<span>
								<?php echo kbs_get_article_excerpt( $article_id ); ?>
                            </span>
                        <?php endif; ?>
                    </li>
    
                <?php endif; ?>
    
            <?php endwhile; ?>
    
            </ul>
        </div>

        <?php wp_reset_postdata(); ?>
    <?php else : ?>
        <p><?php printf( __( 'No %s found', 'kb-support' ), kbs_get_articles_label_plural( true ) ); ?></p>
    <?php endif;

	return ob_get_clean();

} // kbs_articles_shortcode
add_shortcode( 'kbs_articles', 'kbs_articles_shortcode' );

/**
 * Search Form Shortcode
 *
 * Shows a search form allowing users to search KB Articles. This function simply
 * calls the kbs_article_search_form function to display the search form.
 *
 * @since	1.0
 * @param	att		$atts	Shortcode attributes
 * @uses	kbs_article_search_form()
 * @return	str
 */
function kbs_article_search_form_shortcode()	{
	return kbs_article_search_form();
} // kbs_article_search_form_shortcode
add_shortcode( 'kbs_search', 'kbs_article_search_form_shortcode' );
