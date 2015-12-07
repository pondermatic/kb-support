<?php
	defined( 'ABSPATH' ) or die( "Direct access to this page is disabled!!!" );
	
/**
 * Define the custom metabox fields
 */
$kbs_mb_args = array(
	array(
		'id' => 'kbs_ticket_history',
        'title' => __( 'Ticket History', 'mobile-dj-manager' ),
        'post_type' => 'kbs_tickets',
        'context' => 'normal',
        'priority' => 'default',
        'args' => array(
			'fields' => array(
				'id'	=> '',
				'label' => 'Open link',
				'field' => 'text'
			)
		)
	)
);

/**
 * Instantiate the KBS_Metaboxes class
 */
require_once( KBS_PLUGIN_DIR . '/admin/posts/kbs-metaboxes.php' );
new KBS_Metaboxes( $kbs_mb_args );

/**
 * Remove unwanted metaboxes from our custom post displays
 */
/**
* remove all meta boxes, and display the form
*/
function kbs_remove_metaboxes( $post )	{
    if( 'kbs_tickets' != get_post_type() )
		return;
	
	global $wp_meta_boxes;
	
	wp_die( print_r( $wp_meta_boxes ) );
	
	$wp_meta_boxes = array( 
		'my_custom_post_type' => array(
			'advanced' => array(),
			'side' => array(),
			'normal' => array(),
		)
	); 
} // kbs_remove_metaboxes
add_action( 'edit_form_after_title', 'kbs_remove_metaboxes', 100 );