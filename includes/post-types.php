<?php
/**
 * Post Type Functions
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
 * Registers and sets up the Tickets and Articles custom post types
 *
 * @since	1.0
 * @return	void
 */
function kbs_setup_post_types() {
	
	$article_archives = defined( 'KBS_ARTICLE_DISABLE_ARCHIVE' ) && KBS_ARTICLE_DISABLE_ARCHIVE ? false : true;
	$articles_slug    = defined( 'KBS_SLUG' ) ? KBS_SLUG : 'articles';
	$articles_rewrite = defined( 'KBS_ARTICLE_DISABLE_REWRITE' ) && KBS_ARTICLE_DISABLE_REWRITE ? false : array( 'slug' => $articles_slug, 'with_front' => false );

	$ticket_labels =  apply_filters( 'kbs_ticket_labels', array(
		'name'                  => _x( '%2$s', 'kbs_ticket post type name', 'kb-support' ),
		'singular_name'         => _x( '%1$s', 'singular kbs_ticket post type name', 'kb-support' ),
		'add_new'               => __( 'Open %1$s', 'kb-support' ),
		'add_new_item'          => __( 'Add New %1$s', 'kb-support' ),
		'edit_item'             => __( 'Edit %1$s', 'kb-support' ),
		'new_item'              => __( 'New %1$s', 'kb-support' ),
		'all_items'             => __( '%2$s', 'kb-support' ),
		'view_item'             => __( 'View %1$s', 'kb-support' ),
		'search_items'          => __( 'Search %2$s', 'kb-support' ),
		'not_found'             => __( 'No %2$s found', 'kb-support' ),
		'not_found_in_trash'    => __( 'No %2$s found in Trash', 'kb-support' ),
		'parent_item_colon'     => '',
		'menu_name'             => _x( '%2$s', 'ticket post type menu name', 'kb-support' ),
		'featured_image'        => __( '%1$s Image', 'kb-support' ),
		'set_featured_image'    => __( 'Set %1$s Image', 'kb-support' ),
		'remove_featured_image' => __( 'Remove %1$s Image', 'kb-support' ),
		'use_featured_image'    => __( 'Use as %1$s Image', 'kb-support' ),
		'filter_items_list'     => __( 'Filter %2$s list', 'kb-support' ),
		'items_list_navigation' => __( '%2$s list navigation', 'kb-support' ),
		'items_list'            => __( '%2$s list', 'kb-support' )
	) );

	foreach ( $ticket_labels as $key => $value ) {
		$ticket_labels[ $key ] = sprintf( $value, kbs_get_ticket_label_singular(), kbs_get_ticket_label_plural() );
	}

	$ticket_args = array(
		'labels'             => $ticket_labels,
		'public'             => false,
		'publicly_queryable' => false,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'menu_icon'          => 'dashicons-tickets-alt',
		'query_var'          => true,
		'rewrite'            => false,
		'capability_type'    => 'ticket',
		'map_meta_cap'       => true,
		'has_archive'        => false,
		'hierarchical'       => false,
		'supports'           => apply_filters( 'kbs_ticket_supports', array( 'title', 'editor' ) )
	);

	register_post_type( 'kbs_ticket', apply_filters( 'kbs_ticket_post_type_args', $ticket_args ) );

	/** kbs_ticket_reply Post Type */
	$ticket_reply_labels =  apply_filters( 'kbs_ticket_reply_labels', array(
		'name'                  => _x( '%1$s Reply', 'kbs_ticket post type name', 'kb-support' ),
		'singular_name'         => _x( 'Reply', 'singular kbs_ticket post type name', 'kb-support' ),
		'add_new'               => __( 'Add Reply', 'kb-support' ),
		'add_new_item'          => __( 'Add New Reply', 'kb-support' ),
		'edit_item'             => __( 'Edit Reply', 'kb-support' ),
		'new_item'              => __( 'New Reply', 'kb-support' ),
		'all_items'             => __( 'Replies', 'kb-support' ),
		'view_item'             => __( 'View Reply', 'kb-support' ),
		'search_items'          => __( 'Search Replies', 'kb-support' ),
		'not_found'             => __( 'No Replies found', 'kb-support' ),
		'not_found_in_trash'    => __( 'No Replies found in Trash', 'kb-support' )
	) );

	foreach ( $ticket_reply_labels as $key => $value ) {
		$ticket_reply_labels[ $key ] = sprintf( $value, kbs_get_ticket_label_singular(), kbs_get_ticket_label_plural() );
	}

	$ticket_reply_args = array(
		'labels'             => $ticket_reply_labels,
		'public'             => false,
		'show_ui'            => true,
		'show_in_menu'       => false,
		'rewrite'            => false,
		'capability_type'    => 'ticket',
		'map_meta_cap'       => true,
		'has_archive'        => false,
		'hierarchical'       => false,
		'supports'           => apply_filters( 'kbs_ticket_reply_supports', array() ),
		'can_export'         => true
	);

	register_post_type( 'kbs_ticket_reply', apply_filters( 'kbs_ticket_reply_post_type_args', $ticket_reply_args ) );

	/** Article Post Type */
	$article_labels = array(
		'name'               => _x( '%2$s', 'article type general name', 'kb-support' ),
		'singular_name'      => _x( '%1$s', 'article type singular name', 'kb-support' ),
		'add_new'            => __( 'New %1$s', 'kb-support' ),
		'add_new_item'       => __( 'New %1$s', 'kb-support' ),
		'edit_item'          => __( 'Edit %1$s', 'kb-support' ),
		'new_item'           => __( 'New %1$s', 'kb-support' ),
		'all_items'          => __( '%2$s', 'kb-support' ),
		'view_item'          => __( 'View %1$s', 'kb-support' ),
		'search_items'       => __( 'Search %2$s', 'kb-support' ),
		'not_found'          => __( 'No %2$s found', 'kb-support' ),
		'not_found_in_trash' => __( 'No %2$s found in Trash', 'kb-support' ),
		'parent_item_colon'  => '',
		'menu_name'          => __( '%2$s', 'kb-support' )
	);

	foreach ( $article_labels as $key => $value ) {
		$article_labels[ $key ] = sprintf( $value, kbs_get_article_label_singular(), kbs_get_article_label_plural() );
	}

	$article_args = array(
		'labels'             => $article_labels,
		'public'             => true,
		'show_in_menu'       => true,
		'menu_icon'          => 'dashicons-welcome-learn-more',
		'query_var'          => true,
		'rewrite'            => $articles_rewrite,
		'capability_type'    => 'ticket',
		'map_meta_cap'       => true,
		'has_archive'        => $article_archives,
		'hierarchical'       => false,
		'supports'           => apply_filters( 'kbs_article_supports', array( 'title', 'editor', 'excerpt', 'thumbnail', 'revisions', 'author', 'trackbacks', 'comments' ) ),
		'can_export'         => true
	);

	register_post_type( 'article', $article_args );
	
	/** KB Form Type */
	$form_labels = array(
		'name'               => _x( 'Forms', 'kbs_form type general name', 'kb-support' ),
		'singular_name'      => _x( 'Form', 'kbs_form type singular name', 'kb-support' ),
		'add_new'            => __( 'New Form', 'kb-support' ),
		'add_new_item'       => __( 'New Form', 'kb-support' ),
		'edit_item'          => __( 'Edit Form', 'kb-support' ),
		'new_item'           => __( 'New Form', 'kb-support' ),
		'all_items'          => __( 'Submission Forms', 'kb-support' ),
		'view_item'          => __( 'View Form', 'kb-support' ),
		'search_items'       => __( 'Search Forms', 'kb-support' ),
		'not_found'          => __( 'No Forms found', 'kb-support' ),
		'not_found_in_trash' => __( 'No Forms found in Trash', 'kb-support' ),
		'parent_item_colon'  => '',
		'menu_name'          => __( 'Submission Forms', 'kb-support' )
	);

	$form_args = array(
		'labels'             => $form_labels,
		'public'             => false,
		'show_ui'            => true,
		'show_in_menu'       => 'edit.php?post_type=kbs_ticket',
		'menu_icon'          => 'dashicons-book-alt',
		'rewrite'            => false,
		'capability_type'    => 'ticket',
		'map_meta_cap'       => true,
		'has_archive'        => false,
		'hierarchical'       => false,
		'supports'           => apply_filters( 'kbs_form_supports', array( 'title' ) ),
		'can_export'         => true
	);
	
	register_post_type( 'kbs_form', $form_args );
	
	/** KB Form Field Type */
	$field_labels = array(
		'name'               => _x( 'Fields', 'kbs_form type general name', 'kb-support' ),
		'singular_name'      => _x( 'Field', 'kbs_form type singular name', 'kb-support' ),
		'add_new'            => __( 'New Field', 'kb-support' ),
		'add_new_item'       => __( 'New Field', 'kb-support' ),
		'edit_item'          => __( 'Edit Field', 'kb-support' ),
		'new_item'           => __( 'New Field', 'kb-support' ),
		'all_items'          => __( 'Fields', 'kb-support' ),
		'view_item'          => __( 'View Field', 'kb-support' ),
		'search_items'       => __( 'Search Fields', 'kb-support' ),
		'not_found'          => __( 'No Fields found', 'kb-support' ),
		'not_found_in_trash' => __( 'No Fields found in Trash', 'kb-support' ),
		'parent_item_colon'  => '',
		'menu_name'          => __( 'Fields', 'kb-support' )
	);

	$field_args = array(
		'labels'             => $field_labels,
		'public'             => false,
		'rewrite'            => false,
		'has_archive'        => false,
		'hierarchical'       => false,
		'supports'           => array(),
		'can_export'         => true
	);

	register_post_type( 'kbs_form_field', $field_args );

} // kbs_setup_post_types
add_action( 'init', 'kbs_setup_post_types', 1 );

/**
 * Get Default Ticket Labels
 *
 * @since	1.0
 * @return	arr		$defaults	Default labels
 */
function kbs_get_default_ticket_labels() {
	$defaults = array(
	   'singular' => __( 'Ticket', 'kb-support' ),
	   'plural'   => __( 'Tickets','kb-support' )
	);
	return apply_filters( 'kbs_default_tickets_name', $defaults );
} // kbs_get_default_ticket_labels

/**
 * Get Singular Ticket Label
 *
 * @since	1.0
 *
 * @param	bool	$lowercase
 * @return	str		$defaults['singular']	Singular Ticket label
 */
function kbs_get_ticket_label_singular( $lowercase = false ) {
	$defaults = kbs_get_default_ticket_labels();
	return ($lowercase) ? strtolower( $defaults['singular'] ) : $defaults['singular'];
} // kbs_get_ticket_label_singular

/**
 * Get Plural Ticket Label
 *
 * @since	1.0
 *
 * @param	bool	$lowercase
 * @return	str		$defaults['plural']		Plural Ticket label
 */
function kbs_get_ticket_label_plural( $lowercase = false ) {
	$defaults = kbs_get_default_ticket_labels();
	return ( $lowercase ) ? strtolower( $defaults['plural'] ) : $defaults['plural'];
} // kbs_get_ticket_label_plural

/**
 * Get Default Article Labels
 *
 * @since	1.0
 * @return	arr		$defaults	Default labels
 */
function kbs_get_default_article_labels() {
	$defaults = array(
	   'singular' => __( 'KB Article', 'kb-support' ),
	   'plural'   => __( 'KB Articles','kb-support' )
	);
	return apply_filters( 'kbs_default_articles_name', $defaults );
} // kbs_get_default_article_labels

/**
 * Get Singular Article Label
 *
 * @since	1.0
 *
 * @param	bool	$lowercase
 * @return	str		$defaults['singular']	Singular Ticket label
 */
function kbs_get_article_label_singular( $lowercase = false ) {
	$defaults = kbs_get_default_article_labels();
	return ($lowercase) ? strtolower( $defaults['singular'] ) : $defaults['singular'];
} // kbs_get_article_label_singular

/**
 * Get Plural Article Label
 *
 * @since	1.0
 *
 * @param	bool	$lowercase
 * @return	str		$defaults['plural']		Plural Ticket label
 */
function kbs_get_article_label_plural( $lowercase = false ) {
	$defaults = kbs_get_default_article_labels();
	return ( $lowercase ) ? strtolower( $defaults['plural'] ) : $defaults['plural'];
} // kbs_get_article_label_plural

/**
 * Change default "Enter title here" input
 *
 * @since	1.0
 * @param	str		$title	Default title placeholder text
 * @return	str		$title	New placeholder text
 */
function kbs_change_default_title( $title ) {

	 // If a frontend plugin uses this filter (check extensions before changing this function)
	 if ( ! is_admin() ) {
		$label = kbs_get_ticket_label_singular();
		$title = sprintf( __( 'Enter %s title here', 'kb-support' ), $label );
		return $title;
	 }

	 $screen = get_current_screen();

	 if ( 'kbs_ticket' == $screen->post_type ) {
		$label = kbs_get_ticket_label_singular();
		$title = sprintf( __( 'Enter %s title here', 'kb-support' ), $label );
	 } elseif ( 'article' == $screen->post_type ) {
		$label = kbs_get_article_label_singular();
		$title = sprintf( __( 'Enter %s title here', 'kb-support' ), $label );
	 } elseif ( 'kbs_form' == $screen->post_type )	{
		$title = __( 'Enter form name here', 'kb-support' ); 
	 }

	 return $title;

} // kbs_change_default_title
add_filter( 'enter_title_here', 'kbs_change_default_title' );

/**
 * Registers the custom taxonomies for the kbs_ticket and article custom post types.
 *
 * @since	1.0
 * @return	void
*/
function kbs_setup_custom_taxonomies() {

	$articles_slug = defined( 'KBS_SLUG' ) ? KBS_SLUG : 'articles';

	/** Ticket Categories */
	$ticket_category_labels = array(
		'name'              => sprintf( _x( 'Categories', 'taxonomy general name', 'kb-support' ), kbs_get_ticket_label_singular() ),
		'singular_name'     => sprintf( _x( 'Category', 'taxonomy singular name', 'kb-support' ), kbs_get_ticket_label_singular() ),
		'search_items'      => sprintf( __( 'Search %s Categories', 'kb-support' ), kbs_get_ticket_label_singular() ),
		'all_items'         => sprintf( __( 'All %s Categories', 'kb-support' ), kbs_get_ticket_label_singular() ),
		'parent_item'       => sprintf( __( 'Parent %s Category', 'kb-support' ), kbs_get_ticket_label_singular() ),
		'parent_item_colon' => sprintf( __( 'Parent %s Category:', 'kb-support' ), kbs_get_ticket_label_singular() ),
		'edit_item'         => sprintf( __( 'Edit %s Category', 'kb-support' ), kbs_get_ticket_label_singular() ),
		'update_item'       => sprintf( __( 'Update %s Category', 'kb-support' ), kbs_get_ticket_label_singular() ),
		'add_new_item'      => sprintf( __( 'Add New %s Category', 'kb-support' ), kbs_get_ticket_label_singular() ),
		'new_item_name'     => sprintf( __( 'New %s Category Name', 'kb-support' ), kbs_get_ticket_label_singular() ),
		'menu_name'         => sprintf( __( 'Categories', 'kb-support' ), kbs_get_ticket_label_singular() )
	);

	$ticket_category_args = apply_filters( 'kbs_ticket_category_args', array(
			'hierarchical' => true,
			'labels'       => apply_filters( 'kbs_ticket_category_labels', $ticket_category_labels),
			'show_ui'      => true,
			'query_var'    => 'ticket_category',
			'rewrite'      => false,
			'capabilities' => array( 'manage_terms' => 'manage_ticket_terms','edit_terms' => 'edit_ticket_terms','assign_terms' => 'assign_ticket_terms','delete_terms' => 'delete_ticket_terms' )
		)
	);

	register_taxonomy( 'ticket_category', array( 'kbs_ticket' ), $ticket_category_args );
	register_taxonomy_for_object_type( 'ticket_category', 'kbs_ticket' );

	/** Ticket Tags */
	$ticket_tag_labels = array(
		'name'                  => sprintf( _x( 'Tags', 'taxonomy general name', 'kb-support' ), kbs_get_ticket_label_singular() ),
		'singular_name'         => sprintf( _x( 'Tag', 'taxonomy singular name', 'kb-support' ), kbs_get_ticket_label_singular() ),
		'search_items'          => sprintf( __( 'Search %s Tags', 'kb-support' ), kbs_get_ticket_label_singular() ),
		'all_items'             => sprintf( __( 'All %s Tags', 'kb-support' ), kbs_get_ticket_label_singular() ),
		'parent_item'           => sprintf( __( 'Parent %s Tag', 'kb-support' ), kbs_get_ticket_label_singular() ),
		'parent_item_colon'     => sprintf( __( 'Parent %s Tag:', 'kb-support' ), kbs_get_ticket_label_singular() ),
		'edit_item'             => sprintf( __( 'Edit %s Tag', 'kb-support' ), kbs_get_ticket_label_singular() ),
		'update_item'           => sprintf( __( 'Update %s Tag', 'kb-support' ), kbs_get_ticket_label_singular() ),
		'add_new_item'          => sprintf( __( 'Add New %s Tag', 'kb-support' ), kbs_get_ticket_label_singular() ),
		'new_item_name'         => sprintf( __( 'New %s Tag Name', 'kb-support' ), kbs_get_ticket_label_singular() ),
		'menu_name'             => sprintf( __( 'Tags', 'kb-support' ), kbs_get_ticket_label_singular() ),
		'choose_from_most_used' => sprintf( __( 'Choose from most used %s tags', 'kb-support' ), kbs_get_ticket_label_singular() ),
	);

	$ticket_tag_args = apply_filters( 'kbs_ticket_tag_args', array(
			'hierarchical' => false,
			'labels'       => apply_filters( 'kbs_ticket_tag_labels', $ticket_tag_labels ),
			'show_ui'      => true,
			'query_var'    => 'ticket_tag',
			'rewrite'      => false,
			'capabilities' => array( 'manage_terms' => 'manage_ticket_terms','edit_terms' => 'edit_ticket_terms','assign_terms' => 'assign_ticket_terms','delete_terms' => 'delete_ticket_terms' )
		)
	);

	register_taxonomy( 'ticket_tag', array( 'kbs_ticket' ), $ticket_tag_args );
	register_taxonomy_for_object_type( 'ticket_tag', 'kbs_ticket' );

	/** Article Categories */
	$article_category_labels = array(
		'name'              => sprintf( _x( 'Categories', 'taxonomy general name', 'kb-support' ), kbs_get_ticket_label_singular() ),
		'singular_name'     => sprintf( _x( 'Category', 'taxonomy singular name', 'kb-support' ), kbs_get_article_label_singular() ),
		'search_items'      => sprintf( __( 'Search %s Categories', 'kb-support' ), kbs_get_article_label_singular() ),
		'all_items'         => sprintf( __( 'All %s Categories', 'kb-support' ), kbs_get_article_label_singular() ),
		'parent_item'       => sprintf( __( 'Parent %s Category', 'kb-support' ), kbs_get_article_label_singular() ),
		'parent_item_colon' => sprintf( __( 'Parent %s Category:', 'kb-support' ), kbs_get_article_label_singular() ),
		'edit_item'         => sprintf( __( 'Edit %s Category', 'kb-support' ), kbs_get_article_label_singular() ),
		'update_item'       => sprintf( __( 'Update %s Category', 'kb-support' ), kbs_get_article_label_singular() ),
		'add_new_item'      => sprintf( __( 'Add New %s Category', 'kb-support' ), kbs_get_article_label_singular() ),
		'new_item_name'     => sprintf( __( 'New %s Category Name', 'kb-support' ), kbs_get_article_label_singular() ),
		'menu_name'         => sprintf( __( 'Categories', 'kb-support' ), kbs_get_article_label_singular() )
	);

	$article_category_args = apply_filters( 'kbs_article_category_args', array(
			'hierarchical' => true,
			'labels'       => apply_filters( 'kbs_article_category_labels', $article_category_labels),
			'show_ui'      => true,
			'query_var'    => 'article_category',
			'rewrite'      => array( 'slug' => $articles_slug . '/category', 'with_front' => false, 'hierarchical' => true ),
			'capabilities' => array( 'manage_terms' => 'manage_ticket_terms','edit_terms' => 'edit_ticket_terms','assign_terms' => 'assign_ticket_terms','delete_terms' => 'delete_ticket_terms' )
		)
	);

	register_taxonomy( 'article_category', array( 'article' ), $article_category_args );
	register_taxonomy_for_object_type( 'article_category', 'article' );

	/** Article Tags */
	$article_tag_labels = array(
		'name'                  => sprintf( _x( 'Tags', 'taxonomy general name', 'kb-support' ), kbs_get_article_label_singular() ),
		'singular_name'         => sprintf( _x( 'Tag', 'taxonomy singular name', 'kb-support' ), kbs_get_article_label_singular() ),
		'search_items'          => sprintf( __( 'Search %s Tags', 'kb-support' ), kbs_get_article_label_singular() ),
		'all_items'             => sprintf( __( 'All %s Tags', 'kb-support' ), kbs_get_article_label_singular() ),
		'parent_item'           => sprintf( __( 'Parent %s Tag', 'kb-support' ), kbs_get_article_label_singular() ),
		'parent_item_colon'     => sprintf( __( 'Parent %s Tag:', 'kb-support' ), kbs_get_article_label_singular() ),
		'edit_item'             => sprintf( __( 'Edit %s Tag', 'kb-support' ), kbs_get_article_label_singular() ),
		'update_item'           => sprintf( __( 'Update %s Tag', 'kb-support' ), kbs_get_article_label_singular() ),
		'add_new_item'          => sprintf( __( 'Add New %s Tag', 'kb-support' ), kbs_get_article_label_singular() ),
		'new_item_name'         => sprintf( __( 'New %s Tag Name', 'kb-support' ), kbs_get_article_label_singular() ),
		'menu_name'             => sprintf( __( 'Tags', 'kb-support' ), kbs_get_article_label_singular() ),
		'choose_from_most_used' => sprintf( __( 'Choose from most used %s tags', 'kb-support' ), kbs_get_article_label_singular() ),
	);

	$article_tag_args = apply_filters( 'kbs_article_tag_args', array(
			'hierarchical' => false,
			'labels'       => apply_filters( 'kbs_article_tag_labels', $article_tag_labels ),
			'show_ui'      => true,
			'query_var'    => 'article_tag',
			'rewrite'      => array( 'slug' => $articles_slug . '/tag', 'with_front' => false, 'hierarchical' => true  ),
			'capabilities' => array( 'manage_terms' => 'manage_ticket_terms','edit_terms' => 'edit_ticket_terms','assign_terms' => 'assign_ticket_terms','delete_terms' => 'delete_ticket_terms' )
		)
	);

	register_taxonomy( 'article_tag', array( 'article' ), $article_tag_args );
	register_taxonomy_for_object_type( 'article_tag', 'article' );

} // kbs_setup_custom_taxonomies
add_action( 'init', 'kbs_setup_custom_taxonomies', 0 );

/**
 * Get the singular and plural labels for a ticket taxonomy.
 *
 * @since	1.0
 * @param	str		$taxonomy	The Taxonomy to get labels for
 * @return	arr		Associative array of labels (name = plural)
 */
function kbs_get_taxonomy_labels( $taxonomy = 'ticket_category' ) {

	$allowed_taxonomies = apply_filters(
		'kbs_allowed_taxonomies',
		array( 'ticket_category', 'ticket_tag', 'article_category', 'article_tag' )
	);

	if ( ! in_array( $taxonomy, $allowed_taxonomies ) ) {
		return false;
	}

	$labels   = array();
	$taxonomy = get_taxonomy( $taxonomy );

	if ( false !== $taxonomy ) {
		$singular  = $taxonomy->labels->singular_name;
		$name      = $taxonomy->labels->name;
		$menu_name = $taxonomy->labels->menu_name;

		$labels = array(
			'name'          => $name,
			'singular_name' => $singular,
			'menu_name'     => $menu_name
		);
	}

	return apply_filters( 'kbs_get_taxonomy_labels', $labels, $taxonomy );

} // kbs_get_taxonomy_labels

/**
 * Registers Custom Post Statuses which are used by the Tickets.
 *
 *
 * @since	1.0
 * @return	void
 */
function kbs_register_post_type_statuses() {

	// Ticket Statuses
	register_post_status( 'new', apply_filters( 'kbs_register_post_status_new', array(
		'label'                     => sprintf( _x( 'New', 'New %s', 'kb-support' ), kbs_get_ticket_label_plural() ),
		'public'                    => true,
		'exclude_from_search'       => false,
		'show_in_admin_all_list'    => true,
		'show_in_admin_status_list' => true,
		'label_count'               => _n_noop( 'New <span class="count">(%s)</span>', 'New <span class="count">(%s)</span>', 'kb-support' ),
		'kb-support'                => true,
		'kbs_select_allowed'        => false
	) ) );
	register_post_status( 'open', apply_filters( 'kbs_register_post_status_open', array(
		'label'                     => sprintf( _x( 'Open', 'Open %s', 'kb-support' ), kbs_get_ticket_label_plural() ),
		'public'                    => true,
		'exclude_from_search'       => false,
		'show_in_admin_all_list'    => true,
		'show_in_admin_status_list' => true,
		'label_count'               => _n_noop( 'Open <span class="count">(%s)</span>', 'Open <span class="count">(%s)</span>', 'kb-support' ),
		'kb-support'                => true,
		'kbs_select_allowed'        => true
	) ) );
	register_post_status( 'hold', apply_filters( 'kbs_register_post_status_hold', array(
		'label'                     =>  sprintf( _x( 'On Hold', '%s on Hold', 'kb-support' ), kbs_get_ticket_label_plural() ),
		'public'                    => true,
		'exclude_from_search'       => false,
		'show_in_admin_all_list'    => true,
		'show_in_admin_status_list' => true,
		'label_count'               => _n_noop( 'On Hold <span class="count">(%s)</span>', 'On Hold <span class="count">(%s)</span>', 'kb-support' ),
		'kb-support'                => true,
		'kbs_select_allowed'        => true
	) ) );
	register_post_status( 'closed', apply_filters( 'kbs_register_post_status_closed', array(
		'label'                     => sprintf( _x( 'Closed', 'Closed %s', 'kb-support' ), kbs_get_ticket_label_plural() ),
		'public'                    => true,
		'exclude_from_search'       => false,
		'show_in_admin_all_list'    => true,
		'show_in_admin_status_list' => true,
		'label_count'               => _n_noop( 'Closed <span class="count">(%s)</span>', 'Closed <span class="count">(%s)</span>', 'kb-support' ),
		'kb-support'                => true,
		'kbs_select_allowed'        => true
	) ) );

} // kbs_register_post_type_statuses
add_action( 'init', 'kbs_register_post_type_statuses', 2 );

/**
 * Retrieve all KB Support Custom Ticket post statuses.
 *
 * @since	1.0
 * @uses	get_post_stati()
 * @param	str		$output			The type of output to return, either 'names' or 'objects'. Default 'names'.
 * @param	bool	$allowed_only	If true, only statuses with the kbs_select_allowed true value are returned. False for all.
 * @return	arr|obj		
 */
function kbs_get_post_statuses( $output = 'names', $allowed_only = false )	{
	$args['kb-support'] = true;
	
	if ( ! empty( $allowed_only ) )	{
		$args['kbs_select_allowed'] = true;
	}
	
	$kbs_post_statuses = get_post_stati( $args, $output );
	
	return $kbs_post_statuses;
} // kbs_get_post_statuses

/**
 * Updated Messages
 *
 * Returns an array of with all updated messages.
 *
 * @since	1.0
 * @param	arr		$messages	Post updated message
 * @return	arr		$messages	New post updated messages
 */
function kbs_updated_messages( $messages ) {

	global $post, $post_ID;

	$url1 = '<a href="' . get_permalink( $post_ID ) . '">';
	$url2 = kbs_get_ticket_label_singular();
	$url3 = kbs_get_article_label_singular();
	$url4 = '</a>';

	$messages['kbs_ticket'] = array(
		1 => sprintf( __( '%2$s updated. %1$sView %2$s%3$s.', 'kb-support'   ), $url1, $url2, $url4 ),
		4 => sprintf( __( '%2$s updated. %1$sView %2$s%3$s.', 'kb-support'   ), $url1, $url2, $url4 ),
		6 => sprintf( __( '%2$s opened. %1$sView %2$s%3$s.', 'kb-support'    ), $url1, $url2, $url4 ),
		7 => sprintf( __( '%2$s saved. %1$sView %2$s%3$s.', 'kb-support'     ), $url1, $url2, $url4 ),
		8 => sprintf( __( '%2$s submitted. %1$sView %2$s%3$s.', 'kb-support' ), $url1, $url2, $url4 )
	);
	
	$messages['article'] = array(
		1 => sprintf( __( '%2$s updated. %1$sView %2$s%3$s.', 'kb-support'   ), $url1, $url3, $url4 ),
		4 => sprintf( __( '%2$s updated. %1$sView %2$s%3$s.', 'kb-support'   ), $url1, $url3, $url4 ),
		6 => sprintf( __( '%2$s published. %1$sView %2$s%3$s.', 'kb-support' ), $url1, $url3, $url4 ),
		7 => sprintf( __( '%2$s saved. %1$sView %2$s%3$s.', 'kb-support'     ), $url1, $url3, $url4 ),
		8 => sprintf( __( '%2$s submitted. %1$sView %2$s%3$s.', 'kb-support' ), $url1, $url3, $url4 )
	);
	
	$messages['kbs_form'] = array(
		1 => sprintf( __( 'Form updated. %1$sView Form%2$s.', 'kb-support'   ), $url1, $url4 ),
		4 => sprintf( __( 'Form updated. %1$sView Form%2$s.', 'kb-support'   ), $url1, $url4 ),
		6 => sprintf( __( 'Form published. %1$sView Form%2$s.', 'kb-support' ), $url1, $url4 ),
		7 => sprintf( __( 'Form saved. %1$sView Form%2$s.', 'kb-support'     ), $url1, $url4 ),
		8 => sprintf( __( 'Form submitted. %1$sView Form%2$s.', 'kb-support' ), $url1, $url4 )
	);

	return $messages;

} // kbs_updated_messages
add_filter( 'post_updated_messages', 'kbs_updated_messages' );

/**
 * Updated bulk messages
 *
 * @since	1.0
 * @param	arr		$bulk_messages	Post updated messages
 * @param	arr		$bulk_counts	Post counts
 * @return	arr		$bulk_messages	New post updated messages
 */
function kbs_bulk_updated_messages( $bulk_messages, $bulk_counts ) {

	$ticket_singular  = kbs_get_ticket_label_singular();
	$ticket_plural    = kbs_get_ticket_label_plural();
	$article_singular = kbs_get_article_label_singular();
	$article_plural   = kbs_get_article_label_plural();

	$bulk_messages['kbs_ticket'] = array(
		'updated'   => sprintf( _n( '%1$s %2$s updated.', '%1$s %3$s updated.', $bulk_counts['updated'], 'kb-support' ), $bulk_counts['updated'], $ticket_singular, $ticket_plural ),
		'locked'    => sprintf( _n( '%1$s %2$s not updated, somebody is editing it.', '%1$s %3$s not updated, somebody is editing them.', $bulk_counts['locked'], 'kb-support' ), $bulk_counts['locked'], $ticket_singular, $ticket_plural ),
		'deleted'   => sprintf( _n( '%1$s %2$s permanently deleted.', '%1$s %3$s permanently deleted.', $bulk_counts['deleted'], 'kb-support' ), $bulk_counts['deleted'], $ticket_singular, $ticket_plural ),
		'trashed'   => sprintf( _n( '%1$s %2$s moved to the Trash.', '%1$s %3$s moved to the Trash.', $bulk_counts['trashed'], 'kb-support' ), $bulk_counts['trashed'], $ticket_singular, $ticket_plural ),
		'untrashed' => sprintf( _n( '%1$s %2$s restored from the Trash.', '%1$s %3$s restored from the Trash.', $bulk_counts['untrashed'], 'kb-support' ), $bulk_counts['untrashed'], $ticket_singular, $ticket_plural )
	);
	
	$bulk_messages['article'] = array(
		'updated'   => sprintf( _n( '%1$s %2$s updated.', '%1$s %3$s updated.', $bulk_counts['updated'], 'kb-support' ), $bulk_counts['updated'], $article_singular, $article_plural ),
		'locked'    => sprintf( _n( '%1$s %2$s not updated, somebody is editing it.', '%1$s %3$s not updated, somebody is editing them.', $bulk_counts['locked'], 'kb-support' ), $bulk_counts['locked'], $article_singular, $article_plural ),
		'deleted'   => sprintf( _n( '%1$s %2$s permanently deleted.', '%1$s %3$s permanently deleted.', $bulk_counts['deleted'], 'kb-support' ), $bulk_counts['deleted'], $article_singular, $article_plural ),
		'trashed'   => sprintf( _n( '%1$s %2$s moved to the Trash.', '%1$s %3$s moved to the Trash.', $bulk_counts['trashed'], 'kb-support' ), $bulk_counts['trashed'], $article_singular, $article_plural ),
		'untrashed' => sprintf( _n( '%1$s %2$s restored from the Trash.', '%1$s %3$s restored from the Trash.', $bulk_counts['untrashed'], 'kb-support' ), $bulk_counts['untrashed'], $article_singular, $article_plural )
	);

	return $bulk_messages;

}
add_filter( 'bulk_post_updated_messages', 'kbs_bulk_updated_messages', 10, 2 );
