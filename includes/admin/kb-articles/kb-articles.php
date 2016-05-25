<?php
	defined( 'ABSPATH' ) or die( "Direct access to this page is disabled!!!" );
	
/**
 * Manage kbs-article posts.
 * 
 * @since		0.1
 * @package		KBS
 * @subpackage	Posts
 */

/**
 * Define the columns that should be displayed for the KBS Article post lists screen
 *
 * @since	0.1
 * @param	arr		$columns	An array of column name ⇒ label. The label is shown as the column header.
 * @return	arr		$columns	Filtered array of column name => label to be shown as the column header.
 */
function kbs_set_kbs_article_post_columns( $columns ) {
    
	$columns = array(
        'cb'               => '<input type="checkbox" />',
		'title'            => __( 'Title', 'kb-support' ),
		'date'             => __( 'Date', 'kb-support' ),
		'categories'       => __( 'Categories', 'kb-support' ),
        'tags'             => __( 'Tags', 'kb-support' ),
		'author'           => __( 'Author', 'kb-support' ),
		'views'            => __( 'Views', 'kb-support' ),
		'linked'           => sprintf( __( 'Linked %s', 'kb-support' ), kbs_get_ticket_label_plural() )
    );
	
	return apply_filters( 'kbs_article_post_columns', $columns );
	
} // kbs_set_kbs_article_post_columns
add_filter( 'manage_kbs_article_posts_columns' , 'kbs_set_kbs_article_post_columns' );

/**
 * Define the data to be displayed within the KBS Article post custom columns.
 *
 * @since	0.1
 * @param	str		$column_name	The name of the current column for which data should be displayed.
 * @param	int		$post_id		The ID of the current post for which data is being displayed.
 * @return	str
 */
function kbs_set_kbs_article_column_data( $column_name, $post_id ) {

	switch ( $column_name ) {
		case 'views':
			echo $output = '';
			echo apply_filters( 'kb_articles_post_column_views', $output, $post_id );
			break;

		case 'linked':
			echo $output = '';
			echo apply_filters( 'kb_articles_post_column_linked', $output, $post_id );
			break;
			
		default:
			echo __( 'No callback found for post column', 'kb-support' );
			break;
	}

} // kbs_set_kbs_article_column_data
add_action( 'manage_kbs_article_posts_custom_column' , 'kbs_set_kbs_article_column_data', 10, 2 );

/**
 * Save the KBS Article custom posts
 *
 * @since	1.3
 * @param	int		$post_id		The ID of the post being saved.
 * @param	obj		$post			The WP_Post object of the post being saved.
 * @param	bool	$update			Whether an existing post if being updated or not.
 *
 * @return	void
 */
function kbs_article_post_save( $post_id, $post, $update )	{	

	// Remove the save post action to avoid loops
	remove_action( 'save_post_kbs_article', 'kbs_article_post_save', 10, 3 );

	// Fire the before save action but only if this is not a new article creation (i.e $post->post_status == 'draft')
	if( $update === true )	{
		do_action( 'kbs_article_before_save', $post_id, $post, $update );
	}

	// Fire the after save action
	do_action( 'kbs_article_after_save', $post_id, $post, $update );

	// Re-add the save post action
	add_action( 'save_post_kbs_article', 'kbs_article_post_save', 10, 3 );
}
add_action( 'save_post_kbs_article', 'kbs_article_post_save', 10, 3 );