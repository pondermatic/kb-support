<?php
/**
 * Ticket Functions
 *
 * @package     KBS
 * @subpackage  Tickets/Functions
 * @copyright   Copyright (c) 2016, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Retrieve the possible sources for logging a ticket.
 *
 * Custom sources can be added by hooking the `kbs_ticket_log_sources` filter.
 *
 * @since	1.0
 * @param
 * @return	arr	Array of $key => value sources for logging a ticket.
 */
function kbs_get_ticket_log_sources()	{

	$sources = array(
		1  => __( 'Website', 'kb-support' ),
		2  => __( 'Email', 'kb-support' ),
		3  => __( 'Telephone', 'kb-support' ),
		99 => __( 'Other', 'kb-support' )
	);
	
	$sources = apply_filters( 'kbs_ticket_log_sources', $sources );
	
	if ( isset( $key ) )	{
		return $sources[ $key ];
	}
	
	return $sources;

} // kbs_get_ticket_log_sources

/**
 * Adds a new ticket.
 *
 * @since	1.0
 * @param	arr		$data			Post arguments.
 * @param	arr		$meta			Post meta.
 * @param	str|arr	$attachments	File attachments.
 * @return	mixed	Ticket ID on success, false on failure.
 */
function kbs_add_ticket( $data, $meta, $attachments = array() )	{

	if ( ! empty( $attachments ) && ! is_array( $attachments ) )	{
		$attachments = array( $attachments );
	}

	$data        = apply_filters( 'kbs_add_ticket_data', $data );
	$meta        = apply_filters( 'kbs_add_ticket_meta', $meta );
	$attachments = apply_filters( 'kbs_add_ticket_attachments', $attachments );

	$ticket = new KBS_Ticket();

	/**
	 * Runs immediately before adding a new ticket to the database.
	 *
	 * @since	1.0
	 * @param	int		$form_id	The ID of the form submitting the data.
	 * @param	arr		$data		Post data. See wo_insert_post.
	 * @param	arr		$meta		Meta data.
	 */
	do_action( 'kbs_pre_add_ticket', $data, $meta );

	$ticket->create( $data, $meta );
	
	if ( empty( $ticket ) )	{
		return false;
	}

	if ( ! empty( $attachments ) )	{

		foreach( $attachments['name'] as $key => $value )	{

			if ( $attachments['name'][ $key ] )	{ 

				$attachment = array( 
					'name'     => $attachments['name'][ $key ],
					'type'     => $attachments['type'][ $key ], 
					'tmp_name' => $attachments['tmp_name'][ $key ], 
					'error'    => $attachments['error'][ $key ],
					'size'     => $attachments['size'][ $key ]
				);

				$_FILES = array( 'kbs_ticket_attachments' => $attachment );
 
				foreach( $_FILES as $attachment => $array )	{				
					kbs_attach_file_to_ticket( $attachment, $ticket->ID ); 
				}

			} 

		} 

	}

	/**
	 * Runs immediately after successfully adding a new ticket to the database.
	 *
	 * @since	1.0
	 * @param	int		$ticket->ID	The ID of the form submitting the data.
	 * @param	arr		$data		Post data. See wo_insert_post.
	 * @param	arr		$meta		Meta data.
	 */
	do_action( 'kbs_post_add_ticket', $ticket->ID, $data, $meta );

	return $ticket->ID;

} // kbs_add_ticket

/**
 * Adds a new ticket from a form submission.
 *
 * @since	1.0
 * @param	int		$form_id	Form ID
 * @param	arr		$data		Array of ticket data.
 * @return	mixed	Ticket ID on success, false on failure.
 */
function kbs_add_ticket_from_form( $form_id, $data )	{
	
	$kbs_form    = new KBS_Form( $form_id );
	$fields      = $kbs_form->fields;
	$args        = array();
	$meta        = array();
	$meta_data   = array();
	$attachments = array();

	foreach( $fields as $field )	{

		$settings = $kbs_form->get_field_settings( $field->ID );

		if ( 'file_upload' == $settings['type'] )	{
			$attachments = $_FILES[ $field->post_name ];
			continue;
		}

		if ( empty( $data[ $field->post_name ] ) )	{
			continue;
		}

		if ( ! empty( $settings['mapping'] ) )	{
			$args[ $settings['mapping'] ] = $data[ $field->post_name ];
		} else	{
			$meta[ $field->post_name ] = array( $field->post_title, strip_tags( addslashes( $data[ $field->post_name ] ) ) );
			
			$meta_data[] = '<strong>' . $field->post_title . '</strong><br />' . $data[ $field->post_name ];
		}
	}
	
	if ( ! empty( $meta ) )	{
		$meta_content  = '<p><strong>' . __( 'Form Data Submitted', 'kb-support' ) . '</strong></p>';
		$meta_content .= '<p> ' . implode( '<br />', $meta_data ) . '</p>';
		
		if ( ! empty( $args['post_content'] ) )	{
			$args['post_content'] = $args['post_content'] . $meta_content;
		} else	{
			$args['post_content'] = $meta_content;
		}
	}

	$args        = apply_filters( 'kbs_add_ticket_from_form_args', $args );
	$meta        = apply_filters( 'kbs_add_ticket_from_form_meta', $meta );
	$attachments = apply_filters( 'kbs_add_ticket_from_form_attachments', $attachments );

	return kbs_add_ticket( $args, $meta, $attachments );
	
} // kbs_add_ticket_from_form

/**
 * Retrieve the assigned agent.
 *
 * @since	1.0
 * @param	int	$ticket_id		The ticket ID
 * @return	int	The assigned agent ID.
 */
function kbs_get_agent( $ticket_id )	{
	$kbs_ticket = new KBS_Ticket( $ticket_id );
	
	return $kbs_ticket->agent;
} // kbs_get_agent

/**
 * Retrieve the source used for logging the ticket.
 *
 * @since	1.0
 * @param	int	$ticket_id		The ticket ID
 * @return	str	The source.
 */
function kbs_get_ticket_source( $ticket_id )	{
	$kbs_ticket = new KBS_Ticket( $ticket_id );
	
	return $kbs_ticket->get_source();
} // kbs_get_ticket_source
