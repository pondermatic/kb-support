<?php
	defined( 'ABSPATH' ) or die( "Direct access to this page is disabled!!!" );
/*
 * Class: KBS_Post_Types
 * 
 * 
 * Manage custom post types and taxonomies
 */
if( !class_exists( 'KBS_Post_Types' ) ) :
	class KBS_Post_Types	{
		/**
		 * Controller
		 */
		public static function controller()	{
			// Fires on the WP init hook
			add_action( 'init', array( __CLASS__, 'init' ), 0 );		
		} // controller
		
		/**
		 * Methods to execute as part of the WP init hook
		 */
		public static function init()	{
			self::register_post_types();
			self::register_post_statuses();
		} // init

		/**
		 * Register custom post types for KBS
		 *
		 * @param
		 *
		 * @return
		 */
		public static function register_post_types()	{
			register_post_type(
				'kbs_tickets',
				array(
					'label'                 => __( 'Case', 'kb-support' ),
					'description'           => __( 'KBS Customer Cases', 'kb-support' ),
					'labels'                => apply_filters( 'kb_support_ticket_labels', array(
						'name'                  => _x( 'Tickets', 'Post Type General Name', 'kb-support' ),
						'singular_name'         => _x( 'Ticket', 'Post Type Singular Name', 'kb-support' ),
						'menu_name'             => __( 'Tickets', 'kb-support' ),
						'name_admin_bar'        => __( 'Tickets', 'kb-support' ),
						'parent_item_colon'     => __( 'Parent Item:', 'kb-support' ),
						'all_items'             => __( 'All Tickets', 'kb-support' ),
						'add_new_item'          => __( 'New Ticket', 'kb-support' ),
						'add_new'               => __( 'New Ticket', 'kb-support' ),
						'new_item'              => __( 'New Ticket', 'kb-support' ),
						'edit_item'             => __( 'Edit Ticket', 'kb-support' ),
						'update_item'           => __( 'Update Ticket', 'kb-support' ),
						'view_item'             => __( 'View Ticket', 'kb-support' ),
						'search_items'          => __( 'Search Ticket', 'kb-support' ),
						'not_found'             => __( 'No tickets found', 'kb-support' ),
						'not_found_in_trash'    => __( 'No tickets in Trash', 'kb-support' ),
						'items_list'            => __( 'Tickets list', 'kb-support' ),
						'items_list_navigation' => __( 'Tickets list navigation', 'kb-support' ),
						'filter_items_list'     => __( 'Filter tickets list', 'kb-support' ) )
					),
					'supports'              => array( 'title', 'editor' ),
					'hierarchical'          => false,
					'public'                => true,
					'show_ui'               => true,
					'show_in_menu'          => true,
					'menu_position'         => 5,
					'show_in_admin_bar'     => false,
					'show_in_nav_menus'     => false,
					'can_export'            => true,
					'has_archive'           => true,		
					'exclude_from_search'   => true,
					'publicly_queryable'    => true,
					'rewrite'               => false,
					'capabilities'          => array(
						'edit_post'             => 'edit_ticket',
						'read_post'             => 'read_ticket',
						'delete_post'           => 'delete_ticket',
						'edit_posts'            => 'edit_tickets',
						'edit_others_posts'     => 'edit_others_tickets',
						'publish_posts'         => 'publish_tickets',
						'read_private_posts'    => 'read_private_tickets'
					),
					'map_meta_cap'			=> true
				) 
			); // kbs_tickets
			
			register_post_type(
				'kbs_kb',
				array(
					'label'                 => __( 'KB Article', 'kb-support' ),
					'description'           => __( 'Knowledge base articles', 'kb-support' ),
					'labels'                => apply_filters( 'kb_support_kb_labels', array(
						'name'                  => _x( 'KB Articles', 'Post Type General Name', 'kb-support' ),
						'singular_name'         => _x( 'KB Article', 'Post Type Singular Name', 'kb-support' ),
						'menu_name'             => __( 'KB Articles', 'kb-support' ),
						'name_admin_bar'        => __( 'Post Type', 'kb-support' ),
						'parent_item_colon'     => __( 'Parent Article:', 'kb-support' ),
						'all_items'             => __( 'All KB Articles', 'kb-support' ),
						'add_new_item'          => __( 'Add New KB Article', 'kb-support' ),
						'add_new'               => __( 'Add New', 'kb-support' ),
						'new_item'              => __( 'New KB Article', 'kb-support' ),
						'edit_item'             => __( 'Edit KB Article', 'kb-support' ),
						'update_item'           => __( 'Update KB Article', 'kb-support' ),
						'view_item'             => __( 'View KB Article', 'kb-support' ),
						'search_items'          => __( 'Search KB Article', 'kb-support' ),
						'not_found'             => __( 'No articles found', 'kb-support' ),
						'not_found_in_trash'    => __( 'No articles found in Trash', 'kb-support' ),
						'items_list'            => __( 'KB Articles list', 'kb-support' ),
						'items_list_navigation' => __( 'KB Articles list navigation', 'kb-support' ),
						'filter_items_list'     => __( 'Filter KB Articles list', 'kb-support' ) )
					),
					'supports'              => array( 
						'title', 'editor', 'excerpt', 'thumbnail', 'comments', 'trackbacks', 'revisions'
					),
					'taxonomies'            => array( 'kb', 'kb_tag' ),
					'hierarchical'          => false,
					'public'                => true,
					'show_ui'               => true,
					'show_in_menu'          => false,
					'menu_position'         => 5,
					'show_in_admin_bar'     => true,
					'show_in_nav_menus'     => false,
					'can_export'            => true,
					'has_archive'           => true,		
					'exclude_from_search'   => false,
					'publicly_queryable'    => true,
					'capabilities'          => array(
						'edit_post'             => 'edit_article',
						'read_post'             => 'read_article',
						'delete_post'           => 'delete_article',
						'edit_posts'            => 'edit_articles',
						'edit_others_posts'     => 'edit_others_articles',
						'publish_posts'         => 'publish_articles',
						'read_private_posts'    => 'read_private_articles'
					),
					'map_meta_cap'			=> true
				)
			);
		} // register_post_types
		
		/**
		 * Register the custom post statuses for KBS posts
		 *
		 * @param
		 *
		 * @return
		 */
		public static function register_post_statuses()	{
			register_post_status(
				'kb-open',
				apply_filters( 'kb_support_unassigned_labels', array(
					'label'                     => _x( 'Open', 'Status General Name', 'kb-support' ),
					'label_count'               => _n_noop( 'Open (%s)',  'Open (%s)', 'kb-support' ), 
					'public'                    => true,
					'show_in_admin_all_list'    => true,
					'show_in_admin_status_list' => true,
					'exclude_from_search'       => true
				) )
			); // kb-open
			register_post_status(
				'kb-hold',
				apply_filters( 'kb_support_onhold_labels', array(
					'label'                     => _x( 'On Hold', 'Status General Name', 'kb-support' ),
					'label_count'               => _n_noop( 'On Hold (%s)',  'On Hold (%s)', 'kb-support' ), 
					'public'                    => true,
					'show_in_admin_all_list'    => true,
					'show_in_admin_status_list' => true,
					'exclude_from_search'       => true
				) )
			); // kb-hold
			register_post_status(
				'kb-closed',
				apply_filters( 'kb_support_closed_labels', array(
					'label'                     => _x( 'Closed', 'Status General Name', 'kb-support' ),
					'label_count'               => _n_noop( 'Closed (%s)',  'Closed (%s)', 'kb-support' ), 
					'public'                    => true,
					'show_in_admin_all_list'    => true,
					'show_in_admin_status_list' => true,
					'exclude_from_search'       => true
				) )
			); // kb-hold
			do_action( 'kb_tickets_register_post_status' );
		} // register_post_statuses
	} // class KBS_Post_Types
endif;
	KBS_Post_Types::controller();