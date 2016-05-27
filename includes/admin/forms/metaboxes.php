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

	add_meta_box(
		'kbs_form_fields_mb',
		__( 'Form Fields', 'kb-support' ),
		'kbs_form_fields_mb_callback',
		'kbs_form',
		'normal',
		'high'
	);
			
	add_meta_box(
		'kbs_form_add_field_mb',
		__( 'Add a New Field', 'kb-support' ),
		'kbs_form_add_field_mb_callback',
		'kbs_form',
		'side',
		'high',
		$add_field_args
	);
	
} // kbs_form_add_meta_boxes
add_action( 'add_meta_boxes_kbs_form', 'kbs_form_add_meta_boxes' );

/**
 * Render form fields meta box.
 *
 * @since	0.1
 * @param	obj		$post		The form post object (WP_Post).
 * @return
 */
function kbs_form_fields_mb_callback( $post )	{
	
	global $post;

	/*
	 * Output the form fields
	 * @since	0.1
	 */
	do_action( 'kbs_form_mb_form_fields', $post->ID );
	
} // kbs_form_fields_mb_callback

/**
 * Render add field meta box.
 *
 * @since	0.1
 * @param	obj		$post		The form post object (WP_Post).
 * @param	arr		$args		Arguments passed to metabox.
 * @return
 */
function kbs_form_add_field_mb_callback( $post, $args )	{
	
	global $post;

	/*
	 * Output the new field form
	 * @since	0.1
	 */
	do_action( 'kbs_form_mb_add_form_field', $post->ID, $args );
	
} // kbs_form_add_field_mb_callback

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
            	<tr class="kbs_repeatable_field_wrapper kbs_repeatable_row" data-key="<?php echo esc_attr( $key ); ?>">
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

/**
 * Render the row for entering the label.
 *
 * @since	0.1
 * @param	int		$post_id	The form post ID.
 * @param	arr		$args		Function arguments
 * @return	str
 */
function kbs_render_field_label_row( $post_id, $args )	{
	?>
	<div id="kbs_meta_field_label_wrap">
		<p><strong><?php _e( 'Label', 'kb-support' ); ?></strong><br />
		<label for="kbs_field_label">
			<?php echo KBS()->html->text( array(
				'name'  => 'kbs_field_label',
				'value' => '',
				'class' => 'kbs_input'
			) ); ?>
		</label></p>
	</div>
	<?php

} // kbs_render_field_label_row
add_action( 'kbs_form_mb_add_form_field', 'kbs_render_field_label_row', 10, 2 );

/**
 * Render the row for selecting the type.
 *
 * @since	0.1
 * @param	int		$post_id	The form post ID.
 * @param	arr		$args		Function arguments
 * @return	str
 */
function kbs_render_field_type_row( $post_id, $args )	{
	?>
	<div id="kbs_meta_field_type_wrap">
		<p><strong><?php _e( 'Type', 'kb-support' ); ?></strong><br />
		<label for="kbs_field_type">
			<?php echo KBS()->html->select( array(
				'name'             => 'kbs_field_type',
				'selected'         => '',
				'class'            => 'kbs_select kbs_field_type',
				'show_option_all'  => false,
				'show_option_none' => __( 'Select Type', 'kb-support' ),
				'options'          => kbs_get_field_types()
			) ); ?>
		</label></p>
	</div>
	<?php

} // kbs_render_field_label_row
add_action( 'kbs_form_mb_add_form_field', 'kbs_render_field_type_row', 15, 2 );

/**
 * Render the row for setting as required.
 *
 * @since	0.1
 * @param	int		$post_id	The form post ID.
 * @param	arr		$args		Function arguments
 * @return	str
 */
function kbs_render_field_required_row( $post_id, $args )	{
	do_action( 'kbs_form_mb_field_options', $post_id, $args );
	?>
	<div id="kbs_meta_field_required_wrap">
		<p><label for="kbs_field_required">
			<?php echo KBS()->html->checkbox( array(
				'name' => 'kbs_field_required',
			) ); ?>
			<strong><?php _e( 'Required?', 'kb-support' ); ?></strong></label></p>
	</div>
	<?php

} // kbs_render_field_required_row
add_action( 'kbs_form_mb_add_form_field', 'kbs_render_field_required_row', 20, 2 );

/**
 * Render the row for entering the label class.
 *
 * @since	0.1
 * @param	int		$post_id	The form post ID.
 * @param	arr		$args		Function arguments
 * @return	str
 */
function kbs_render_field_label_class_row( $post_id, $args )	{
	?>
	<div id="kbs_meta_field_label_class_wrap">
		<p><strong><?php _e( 'Label Class', 'kb-support' ); ?></strong><br />
		<label for="kbs_field_label_class">
			<?php echo KBS()->html->text( array(
				'name'  => 'kbs_field_label_class',
				'value' => '',
				'class' => 'kbs_input'
			) ); ?>
		</label></p>
	</div>
	<?php

} // kbs_render_field_label_row
add_action( 'kbs_form_mb_add_form_field', 'kbs_render_field_label_class_row', 25, 2 );

/**
 * Render the row for entering the input class.
 *
 * @since	0.1
 * @param	int		$post_id	The form post ID.
 * @param	arr		$args		Function arguments
 * @return	str
 */
function kbs_render_field_input_class_row( $post_id, $args )	{
	?>
	<div id="kbs_meta_field_input_class_wrap">
		<p><strong><?php _e( 'Input Class', 'kb-support' ); ?></strong><br />
		<label for="kbs_field_input_class">
			<?php echo KBS()->html->text( array(
				'name'  => 'kbs_field_input_class',
				'value' => '',
				'class' => 'kbs_input'
			) ); ?>
		</label></p>
	</div>
	<?php

} // kbs_render_field_label_row
add_action( 'kbs_form_mb_add_form_field', 'kbs_render_field_input_class_row', 30, 2 );

/**
 * Render the row for adding the new field.
 *
 * @since	0.1
 * @param	int		$post_id	The form post ID.
 * @param	arr		$args		Function arguments
 * @return	str
 */
function kbs_render_field_add_field_btn_row( $post_id, $args )	{
	?>
	<div id="kbs_meta_field_add_form_btn_wrap">
		<p style="float: none; clear:both; background: #fff;">
			<a class="button-secondary kbs_add_repeatable" style="margin: 6px 0 10px;"><?php _e( 'Add Field', 'kb-support' ); ?></a>
        </p>
	</div>
	<?php

} // kbs_render_field_label_row
add_action( 'kbs_form_mb_add_form_field', 'kbs_render_field_add_field_btn_row', 90, 2 );

/**
 * Render the rows for setting field options.
 *
 * @since	0.1
 * @param	int		$post_id	The form post ID.
 * @param	arr		$args		Function arguments
 * @return	str
 */
function kbs_render_field_options_rows( $post_id )	{
	?>
    <div id="kbs_meta_field_select_options_wrap">
    	<p><strong><?php _e( 'Options', 'kb-support' ); ?></strong><br />
		<label for="kbs_field_select_options">
			<?php echo KBS()->html->textarea( array(
				'name'        => 'kbs_field_placeholder',
				'value'       => '',
				'placeholder' => __( 'One entry per line', 'kb-support' ),
				'class'       => 'kbs_input'
			) ); ?>
		</label></p>
    </div>
    
    <div id="kbs_meta_field_option_selected_wrap">
    	<p><label for="kbs_field_option_selected">
			<?php echo KBS()->html->checkbox( array(
				'name' => 'kbs_field_option_selected',
			) ); ?>
			<strong><?php _e( 'Initially Selected?', 'kb-support' ); ?></strong></label>
        </p>
    </div>
    
    <div id="kbs_meta_field_placeholder_wrap">
    	<p><strong><?php _e( 'Placeholder', 'kb-support' ); ?></strong><br />
		<label for="kbs_field_input_class">
			<?php echo KBS()->html->text( array(
				'name'  => 'kbs_field_placeholder',
				'value' => '',
				'class' => 'kbs_input'
			) ); ?>
		</label></p>
    </div>
    
    <div id="kbs_meta_field_hide_label_wrap">
		<p><label for="kbs_field_hide_label">
			<?php echo KBS()->html->checkbox( array(
				'name' => 'kbs_field_hide_label',
			) ); ?>
			<strong><?php _e( 'Hide Label?', 'kb-support' ); ?></strong></label>
        </p>
	</div>
    <?php
} // kbs_render_field_options_rows
add_action( 'kbs_form_mb_field_options', 'kbs_render_field_options_rows' );
