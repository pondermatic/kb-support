<?php
/**
 * Post Taxonomy Functions
 *
 * @package     KBS
 * @subpackage  Functions
 * @copyright   Copyright (c) 2017, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.2
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Registers the Categories taxonomy for the kbs_ticket custom post types.
 *
 * @since	1.0
 * @return	void
*/
function kbs_setup_kbs_ticket_category_taxonomy()	{

	$ticket_category_labels = array(
		'name'              => _x( 'Categories', 'taxonomy general name', 'kb-support' ),
		'singular_name'     => _x( 'Category', 'taxonomy singular name', 'kb-support' ),
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
			'public'       => false,
			'show_in_menu' => true,
			'show_ui'      => true,
			'query_var'    => 'ticket_category',
			'rewrite'      => false,
			'capabilities' => array(
				'manage_terms' => 'manage_ticket_terms',
				'edit_terms'   => 'edit_ticket_terms',
				'assign_terms' => 'assign_ticket_terms',
				'delete_terms' => 'delete_ticket_terms'
			),
			'update_count_callback' => '_update_generic_term_count'
		)
	);

	register_taxonomy( 'ticket_category', array( 'kbs_ticket' ), $ticket_category_args );
	register_taxonomy_for_object_type( 'ticket_category', 'kbs_ticket' );

} // kbs_setup_kbs_ticket_category_taxonomy
add_action( 'init', 'kbs_setup_kbs_ticket_category_taxonomy', 0 );

/**
 * Registers the Tags taxonomy for the kbs_ticket custom post types.
 *
 * @since	1.0
 * @return	void
*/
function kbs_setup_kbs_ticket_tag_taxonomy()	{

	$ticket_tag_labels = array(
		'name'                  => _x( 'Tags', 'taxonomy general name', 'kb-support' ),
		'singular_name'         => _x( 'Tag', 'taxonomy singular name', 'kb-support' ),
		'search_items'          => sprintf( __( 'Search %s Tags', 'kb-support' ), kbs_get_ticket_label_singular() ),
		'all_items'             => sprintf( __( 'All %s Tags', 'kb-support' ), kbs_get_ticket_label_singular() ),
		'parent_item'           => sprintf( __( 'Parent %s Tag', 'kb-support' ), kbs_get_ticket_label_singular() ),
		'parent_item_colon'     => sprintf( __( 'Parent %s Tag:', 'kb-support' ), kbs_get_ticket_label_singular() ),
		'edit_item'             => sprintf( __( 'Edit %s Tag', 'kb-support' ), kbs_get_ticket_label_singular() ),
		'update_item'           => sprintf( __( 'Update %s Tag', 'kb-support' ), kbs_get_ticket_label_singular() ),
		'add_new_item'          => sprintf( __( 'Add New %s Tag', 'kb-support' ), kbs_get_ticket_label_singular() ),
		'new_item_name'         => sprintf( __( 'New %s Tag Name', 'kb-support' ), kbs_get_ticket_label_singular() ),
		'menu_name'             => __( 'Tags', 'kb-support' ),
		'choose_from_most_used' => sprintf( __( 'Choose from most used %s tags', 'kb-support' ), kbs_get_ticket_label_singular() )
	);

	$ticket_tag_args = apply_filters( 'kbs_ticket_tag_args', array(
			'hierarchical' => false,
			'labels'       => apply_filters( 'kbs_ticket_tag_labels', $ticket_tag_labels ),
			'public'       => false,
			'show_in_menu' => true,
			'show_ui'      => true,
			'query_var'    => 'ticket_tag',
			'rewrite'      => false,
			'capabilities' => array(
				'manage_terms' => 'manage_ticket_terms',
				'edit_terms'   => 'edit_ticket_terms',
				'assign_terms' => 'assign_ticket_terms',
				'delete_terms' => 'delete_ticket_terms'
			),
			'update_count_callback' => '_update_generic_term_count'
		)
	);

	register_taxonomy( 'ticket_tag', array( 'kbs_ticket' ), $ticket_tag_args );
	register_taxonomy_for_object_type( 'ticket_tag', 'kbs_ticket' );


} // kbs_setup_kbs_ticket_tag_taxonomy
add_action( 'init', 'kbs_setup_kbs_ticket_tag_taxonomy', 0 );

/**
 * Registers the Categories taxonomy for the kbs_ticket custom post types.
 *
 * @since	1.0
 * @return	void
*/
function kbs_setup_kbs_ticket_department_taxonomy()	{

	$department_labels = array(
		'name'              => _x( 'Departments', 'taxonomy general name', 'kb-support' ),
		'singular_name'     => _x( 'Department', 'taxonomy singular name', 'kb-support' ),
		'search_items'      => __( 'Search Departments', 'kb-support' ),
		'all_items'         => __( 'All Departments', 'kb-support' ),
		'parent_item'       => __( 'Parent Department', 'kb-support' ),
		'parent_item_colon' => __( 'Parent Department:', 'kb-support' ),
		'edit_item'         => __( 'Edit Department', 'kb-support' ),
		'update_item'       => __( 'Update Department', 'kb-support' ),
		'add_new_item'      => __( 'Add Department', 'kb-support' ),
		'new_item_name'     => __( 'New Department Name', 'kb-support' ),
		'menu_name'         => __( 'Departments', 'kb-support' ),
		'not_found'         => __( 'No departments found', 'kb-support' )
	);

	$department_args = apply_filters( 'kbs_ticket_department_args', array(
			'hierarchical' => false,
			'labels'       => apply_filters( 'kbs_ticket_department_labels', $department_labels ),
			'public'       => false,
			'show_in_menu' => true,
			'show_ui'      => true,
			'rewrite'      => false,
			'capabilities' => array(
				'manage_terms' => 'manage_ticket_terms',
				'edit_terms'   => 'edit_ticket_terms',
				'assign_terms' => 'assign_ticket_terms',
				'delete_terms' => 'delete_ticket_terms'
			),
			'update_count_callback' => '_update_generic_term_count'
		)
	);

	register_taxonomy( 'department', array( 'kbs_ticket' ), $department_args );
	register_taxonomy_for_object_type( 'department', 'kbs_ticket' );

} // kbs_setup_kbs_ticket_department_taxonomy
add_action( 'init', 'kbs_setup_kbs_ticket_department_taxonomy', 0 );

/**
 * Get the singular and plural labels for a taxonomy.
 *
 * @since	1.0
 * @param	str		$taxonomy	The Taxonomy to get labels for
 * @return	arr		Associative array of labels (name = plural)
 */
function kbs_get_taxonomy_labels( $taxonomy = 'ticket_category' ) {

	$allowed_taxonomies = apply_filters(
		'kbs_allowed_taxonomies',
		array( 'ticket_category', 'ticket_tag', 'agent_department', 'article_category', 'article_tag' )
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
