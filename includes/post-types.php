<?php
/**
 * Post Type Functions
 *
 * @package     KBS
 * @subpackage  Functions
 * @copyright   Copyright (c) 2016, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Registers and sets up the KBS custom post types
 *
 * @since	1.3
 * @param
 * @return	void
 */

function kbs_register_post_types()	{
	
	$archives = defined( 'KBS_KB_DISABLE_ARCHIVE' ) && KBS_KB_DISABLE_ARCHIVE ? false : true;
	$slug     = defined( 'KBS_KB_SLUG' ) ? KBS_KB_SLUG : 'tickets';
	$rewrite  = defined( 'KBS_KB_DISABLE_REWRITE' ) && KBS_KB_DISABLE_REWRITE ? false : array('slug' => $slug, 'with_front' => false);
	
	$ticket_labels = array(
		'name'               => _x( '%2$s', 'post type general name', 'kb-support' ),
		'singular_name'      => _x( '%1$s', 'post type singular name', 'kb-support' ),
		'menu_name'          => _x( '%2$s', 'admin menu', 'kb-support' ),
		'name_admin_bar'     => _x( '%1$s', 'add new on admin bar', 'kb-support' ),
		'add_new'            => __( 'Create %1$s', 'kb-support' ),
		'add_new_item'       => __( 'Create New %1$s', 'kb-support' ),
		'new_item'           => __( 'New %1$s', 'kb-support' ),
		'edit_item'          => __( 'Edit %1$s', 'kb-support' ),
		'view_item'          => __( 'View %1$s', 'kb-support' ),
		'all_items'          => __( 'All %2$s', 'kb-support' ),
		'search_items'       => __( 'Search %2$s', 'kb-support' ),
		'not_found'          => __( 'No %3$s found.', 'kb-support' ),
		'not_found_in_trash' => __( 'No %3$s found in Trash.', 'kb-support' )
	);

	$ticket_labels = apply_filters( 'kbs-ticket_labels', $ticket_labels );	
	
	foreach ( $ticket_labels as $key => $value ) {
		$ticket_labels[ $key ] = sprintf(
			$value,
			kbs_get_ticket_label_singular(),
			kbs_get_ticket_label_plural(),
			kbs_get_ticket_label_plural( true )
		);
	}

	$ticket_args = array(
		'labels'                => $ticket_labels,
		'description'           => __( 'Stores tickets created within KB Support', 'mobile-dj-manager' ),
		'publicly_queryable'    => true,
		'show_ui'				=> true,
		'show_in_menu'		    => true,
		'menu_position'         => defined( 'KBS_MENU_POS' ) ? KBS_MENU_POS : 58.5,
		'show_in_admin_bar'     => true,
		'has_archive'        	=> true,
		'supports'              => apply_filters( 'kbs_ticket_supports', array( 'title', 'content' ) ),
		'taxonomies'            => array( 'kbs-ticket' )
	);
	
	register_post_type( 'kbs-ticket', apply_filters( 'kbs-ticket_post_type_args', $ticket_args ) );

} // kbs_register_post_types
add_action( 'init', 'kbs_register_post_types', 1 );

/**
 * Get Default Labels
 *
 * @since	0.1
 * @return	arr		$defaults	Default labels
 */
function kbs_get_ticket_default_labels() {

	$defaults = array(
	   'singular' => __( 'Ticket', 'kbs-support' ),
	   'plural'   => __( 'Tickets','kbs-support' )
	);

	return apply_filters( 'kbs_default_ticket_name', $defaults );

} // kbs_get_ticket_default_labels

/**
 * Get Singular Label
 *
 * @since	0.1
 * @param	bool	$lowercase
 * @return	str		$defaults['singular']	Singular label
 */
function kbs_get_ticket_label_singular( $lowercase = false ) {

	$defaults = kbs_get_ticket_default_labels();

	return ( $lowercase ) ? strtolower( $defaults['singular'] ) : $defaults['singular'];

} // kbs_get_ticket_label_singular

/**
 * Get Plural Label
 *
 * @since	0.1
 * @param	bool	$lowercase
 * @return	str		$defaults['plural']	Plural label
 */
function kbs_get_ticket_label_plural( $lowercase = false ) {
	
	$defaults = kbs_get_ticket_default_labels();
	
	return ( $lowercase ) ? strtolower( $defaults['plural'] ) : $defaults['plural'];

} // kbs_get_ticket_label_plural