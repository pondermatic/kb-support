<?php	
/**
 * Manage company post metaboxes.
 * 
 * @since		1.0
 * @package		KBS
 * @subpackage	Functions/Metaboxes
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Returns default KBS Company meta fields.
 *
 * @since	1.0
 * @return	arr		$fields		Array of fields.
 */
function kbs_company_metabox_fields() {

	$fields = array(
		'_kbs_company_contact',
		'_kbs_company_email',
		'_kbs_company_phone',
		'_kbs_company_website'
	);

	return apply_filters( 'kbs_company_metabox_fields_save', $fields );

} // kbs_company_metabox_fields

/**
 * Define and add the metaboxes for the company post type.
 *
 * @since	1.0
 * @return	void
 */
function kbs_company_add_meta_boxes( $post )	{

	global $kbs_company, $kbs_company_update;

	$kbs_company_update = false;
	$kbs_company        = new KBS_Company( $post->ID );

	if ( 'draft' != $post->post_status && 'auto-draft' != $post->post_status )	{
		$kbs_company_update = true;
	}

	add_meta_box(
		'kbs-company-metabox-data',
		__( 'Company Contact Details', 'kb-support' ),
		'kbs_company_metabox_data_callback',
		'kbs_company',
		'normal',
		'high',
		array()
	);

	if ( ! $kbs_company_update )	{
		add_meta_box(
			'kbs-company-metabox-tickets',
			sprintf( __( 'Recent %s', 'kb-support' ), kbs_get_ticket_label_plural() ),
			'kbs_company_metabox_tickets_callback',
			'kbs_company',
			'normal',
			'',
			array()
		);
	}
	
} // kbs_company_add_meta_boxes
add_action( 'add_meta_boxes_kbs_company', 'kbs_company_add_meta_boxes' );

/**
 * The callback function for the company data metabox.
 *
 * @since	1.0
 * @global	obj		$post				WP_Post object
 * @global	bool	$kbs_company_update	True if this company is being updated, false if new
 * @return	void
 */
function kbs_company_metabox_data_callback()	{
	global $post, $kbs_company, $kbs_company_update;

	wp_nonce_field( 'kbs_company_meta_save', 'kbs_company_meta_box_nonce' );

	/*
	 * Output the items for the company data metabox
	 * @since	1.0
	 * @param	int	$post_id	The Company post ID
	 */
	do_action( 'kbs_company_data_fields', $post->ID );
} // kbs_company_metabox_data_callback

/**
 * The callback function for the tickets metabox.
 *
 * @since	1.0
 * @global	obj		$post				WP_Post object
 * @global	bool	$kbs_company_update	True if this company is being updated, false if new
 * @return	void
 */
function kbs_company_metabox_tickets_callback()	{
	global $post, $kbs_company, $kbs_company_update;

	/*
	 * Output the items for the tickets metabox
	 * @since	1.0
	 * @param	int	$post_id	The Company post ID
	 */
	do_action( 'kbs_company_tickets_fields', $post->ID );
} // kbs_company_metabox_tickets_callback

/**
 * Display the Data metabox.
 *
 * @since	1.0
 * @global	bool	$kbs_company_update	True if this company is being updated, false if new.
 * @param	int		$post_id			The KB post ID.
 * @return	str
 */
function kbs_company_metabox_data( $post_id )	{
	global $kbs_company, $kbs_company_update; ?>

	<div class="kbs-company-data">

    	<p><label for="_kbs_company_contact"><?php _e( 'Contact Name', 'kb-support' ); ?>:</label><br />
        <?php echo KBS()->html->text( array(
			'name'  => '_kbs_company_contact',
			'value' => ! empty( $kbs_company->contact ) ? $kbs_company->contact : ''
		) ); ?></p>

		<p><label for="_kbs_company_email"><?php _e( 'Email Address', 'kb-support' ); ?>:</label><br />
        <?php echo KBS()->html->text( array(
			'name'  => '_kbs_company_email',
			'value' => ! empty( $kbs_company->email ) ? $kbs_company->email : ''
		) ); ?></p>

		<p><label for="_kbs_company_phone"><?php _e( 'Phone Number', 'kb-support' ); ?>:</label><br />
        <?php echo KBS()->html->text( array(
			'name'  => '_kbs_company_phone',
			'value' => ! empty( $kbs_company->phone ) ? $kbs_company->phone : ''
		) ); ?></p>

		<p><label for="_kbs_company_website"><?php _e( 'Website', 'kb-support' ); ?>:</label><br />
        <?php echo KBS()->html->text( array(
			'name'  => '_kbs_company_website',
			'value' => ! empty( $kbs_company->website ) ? $kbs_company->website : ''
		) ); ?></p>

    </div>

	<?php
} // kbs_company_metabox_tickets_table
add_action( 'kbs_company_data_fields', 'kbs_company_metabox_data', 10, 1 );

/**
 * Display the Tickets metabox.
 *
 * @since	1.0
 * @global	bool	$kbs_company_update	True if this company is being updated, false if new.
 * @param	int		$post_id			The KB post ID.
 * @return	str
 */
function kbs_company_metabox_tickets_table( $post_id )	{
	global $kbs_company_update;

} // kbs_company_metabox_tickets_table
add_action( 'kbs_company_tickets_fields', 'kbs_company_metabox_tickets_table', 10, 1 );
