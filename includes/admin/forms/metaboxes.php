<?php

/**
 * Contains all metabox functions for the kbs_form post type
 *
 * @package		KBS
 * @subpackage	Forms
 * @since		0.1
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Define and add the metaboxes for the mdjm-event post type.
 * Apply the `mdjm_event_add_metaboxes` filter to allow for filtering of metaboxes and settings.
 * Uses function_exists to verify the callback function exists.
 *
 * @since	1.3
 * @param
 * @return
 */
function kbs_form_add_meta_boxes( $post )	{

	// Runs before metabox output
	do_action( 'kbs_form_before_metaboxes' );
	
	add_meta_box(
		'kbs_form_mb',
		__( 'Form Fields', 'kb-support' ),
		'kbs_form_mb_callback',
		'kbs_form',
		'normal',
		'high'
	);
	
	// Runs after metabox output
	do_action( 'kbs_form_after_metaboxes' );

} // kbs_form_add_meta_boxes
add_action( 'add_meta_boxes_kbs_form', 'kbs_form_add_meta_boxes' );

/**
 * Render form fields meta box.
 *
 * @since	0.1
 * @param	obj		$post		The form post object (WP_Post).
 * @return
 */
function kbs_form_mb_callback( $post )	{
	
	global $post;

	/*
	 * Output the form fields
	 * @since	0.1
	 */
	do_action( 'kbs_form_mb_form_fields', $post->ID );
	
} // kbs_form_mb_callback

/**
 * Output the existing form fields.
 *
 * @since	0.1
 * @param	int		$post_id	The form post ID.
 * @return	str
 */
function kbs_display_meta_box_form_fields( $post_id )	{

	$form   = new KBS_Form( $post_id );

	?>
	<div id="kbs_form_fields" class="kbs_meta_table_wrap">
        <table class="widefat kbs_repeatable_table" width="100%" cellpadding="0" cellspacing="0">
            <thead>
                <tr>
                    <th style="width: 20px"></th>
                    <th style="width: 20%"><?php _e( 'Field Label', 'kb-support' ); ?></th>
                    <th><?php _e( 'Type', 'kb-support' ); ?></th>
                    <th class="settings" style="width: 20%;"><?php _e( 'Settings', 'kb-support' ); ?></th>
                    <th style="width: 15px"></th>
                    <?php do_action( 'kbs_form_field_table_head', $post_id ); ?>
                    <th style="width: 2%"></th>
                </tr>
            </thead>
            <tbody>
            <?php if ( ! empty( $form->fields ) ) : ?>
            	<tr class="kbs_repeatable_upload_wrapper kbs_repeatable_row" data-key="<?php echo esc_attr( $key ); ?>">
					<?php do_action( 'kbs_render_field_row', $key, $args, $post_id, $index ); ?>
                </tr>
            <?php else : ?>
            	<tr>
            		<td colspan="6"><?php _e( 'No fields exist for this form', 'kb-support' ); ?></td>
                </tr>
            <?php endif; ?>
            </tbody>
            <tfoot>
                <tr>
                    <th style="width: 20px"></th>
                    <th style="width: 20%"><?php _e( 'Field Label', 'kb-support' ); ?></th>
                    <th><?php _e( 'Type', 'kb-support' ); ?></th>
                    <th class="settings" style="width: 20%;"><?php _e( 'Settings', 'kb-support' ); ?></th>
                    <th style="width: 15px"></th>
                    <?php do_action( 'kbs_form_field_table_foot', $post_id ); ?>
                    <th style="width: 2%"></th>
                </tr>
            </tfoot>
        </table>
    </div>
	<?php
	
} // kbs_display_meta_box_form_fields
add_action( 'kbs_form_mb_form_fields', 'kbs_display_meta_box_form_fields', 10 );
