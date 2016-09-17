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
 * Retrieve a ticket.
 *
 * @since	1.0
 * @param	int	$ticket_id	The ticket ID.
 * @return	@see get_post()
 */
function kbs_get_ticket( $ticket_id )	{
	$ticket = new KBS_Ticket( $ticket_id );

	return apply_filters( 'kbs_get_ticket', $ticket, $ticket_id );
} // kbs_get_ticket

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
	do_action( 'kbs_post_add_ticket', $ticket->ID );

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

		if ( 'file_upload' == $settings['type'] && ! empty( $_FILES[ $field->post_name ] ) )	{
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

	$kbs_form->increment_submissions();

	$args        = apply_filters( 'kbs_add_ticket_from_form_args', $args );
	$meta        = apply_filters( 'kbs_add_ticket_from_form_meta', $meta );
	$attachments = apply_filters( 'kbs_add_ticket_from_form_attachments', $attachments );

	return kbs_add_ticket( $args, $meta, $attachments );

} // kbs_add_ticket_from_form

/**
 * Update the status of a ticket.
 *
 * @since	1.0
 * @param	$ticket_id	The ticket ID
 * @param	$status		The status to be set for the ticket.
 * @return	mixed.
 */
function kbs_set_ticket_status( $ticket_id, $status='open' )	{
	if ( 'kbs_ticket' != get_post_type( $ticket_id ) )	{
		return false;
	}

	remove_action( 'save_post_kbs_ticket', 'kbs_ticket_post_save', 10, 3 );

	/**
	 * Fires immediately before updating the ticket status.
	 * @since	1.0
	 * @param	int	$ticket_id
	 * @param	str	$status		The new ticket status to be assigned.
	 */
	do_action( 'kbs_pre_update_ticket_status', $ticket_id, $status );

	/**
	 * Fires pre update but is more granular as can be hooked for specific status'
	 * @since	1.0
	 * @param	@see do_action( 'kbs_pre_update_ticket_status' )
	 */
	do_action( 'kbs_pre_update_ticket_status_' . $status, $ticket_id, $status );

	$old_status = get_post_status( $ticket_id );

	$update = wp_update_post(
		array( 
			'ID'          => $ticket_id,
			'post_status' => $status
		)
	);
	
	/**
	 * Fires immediately after updating the ticket status.
	 * @since	1.0
	 * @param	int	$ticket_id
	 * @param	str	$old_status	The ticket status prior to being updated.
	 * @param	str	$status		The new ticket status that was assigned.
	 */
	do_action( 'kbs_post_update_ticket_status', $ticket_id, $old_status, $status );

	/**
	 * Fires post update but is more granular as can be hooked for specific status'
	 * @since	1.0
	 * @param	@see do_action( 'kbs_post_update_ticket_status' )
	 */
	do_action( 'kbs_post_update_ticket_status_' . $status, $ticket_id, $old_status, $status );

	add_action( 'save_post_kbs_ticket', 'kbs_ticket_post_save', 10, 3 );

	return $update;
} // kbs_set_ticket_status

/**
 * Retrieve the ticket meta.
 *
 * @since	1.0
 * @param	int	$ticket_id		The ticket ID
 * @param	str	$key			The individual key to retrieve
 * @return	arr	The ticket meta.
 */
function kbs_get_ticket_meta( $ticket_id, $key='' )	{
	$meta = get_post_meta( $ticket_id, '_ticket_data', true );

	if ( ! empty( $key ) )	{
		if ( isset( $meta[ $key ] ) )	{
			$return = apply_filters( 'kbs_ticket_meta_single', $meta[ $key ], $ticket_id, $key );
		} else	{
			$return = apply_filters( 'kbs_ticket_meta_single', false, $ticket_id, $key );
		}
	} else	{
		$return = apply_filters( 'kbs_ticket_meta', $meta );
	}

	return $return;
} // kbs_get_ticket_meta

/**
 * Update the ticket meta.
 *
 * @since	1.0
 * @param	int	$ticket_id		The ticket ID
 * @param	arr	$data			The ticket meta data to update. $key => $value.
 * @return	arr	The ticket meta.
 */
function kbs_update_ticket_meta( $ticket_id, $data )	{
	$meta         = kbs_get_ticket_meta( $ticket_id );
	$current_meta = $meta;

	foreach ( $data as $key => $value )	{
		
		if( is_array( $value ) )	{
			$meta[ $key ] = array_map( 'absint', $value );
		} else	{
			$meta[ $key ] = $value;
		}
		
	}

	/**
	 * Fires immediately before updating ticket meta
	 *
	 * @since	1.0
	 * @param	int	$ticket_id		The ticket ID
	 * @param	arr	$meta			The updated meta data.
	 * @param	arr	$current_meta	The existing meta data.
	 */
	do_action( 'kbs_pre_ticket_meta_update', $ticket_id, $meta, $current_meta );

	update_post_meta( $ticket_id, '_ticket_data', $meta );

	/**
	 * Fires immediately after updating ticket meta
	 *
	 * @since	1.0
	 * @param	int	$ticket_id		The ticket ID
	 * @param	arr	$meta			The updated meta data.
	 */
	do_action( 'kbs_post_ticket_meta_update', $ticket_id, $meta );

} // kbs_update_ticket_meta

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
 * Assigns an agent to the ticket.
 *
 * @since	1.0
 * @param	int	$ticket_id	The ticket ID to update.
 * @param	int	$user_id	The agent user ID. If not set, use current user.
 * @return	mixed.
 */
function kbs_assign_agent( $ticket_id, $user_id = 0 )	{
	if ( empty( $user_id ) )	{
		$user_id = get_current_user_id();
	}
	
	/**
	 * Fires immediately before assigning an agent
	 *
	 * @since	1.0
	 * @param	int	$ticket_id		The ticket ID
	 * @param	int	$user_id		The user ID
	 */
	do_action( 'kbs_pre_assign_agent', $ticket_id, $user_id );

	$return = kbs_update_ticket_meta( $ticket_id, array( '__agent' => $user_id ) );

	/**
	 * Fires immediately after assigning an agent
	 *
	 * @since	1.0
	 * @param	int	$ticket_id		The ticket ID
	 * @param	int	$user_id		The user ID
	 */
	do_action( 'kbs_post_assign_agent', $ticket_id, $user_id );

	return $return;
} // kbs_assign_agent

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

/**
 * Retrieve the ticket replies.
 *
 * @since	1.0
 * @param	int	$ticket_id		The ticket ID
 * @return	arr	Array of ticket reply post objects.
 */
function kbs_get_ticket_replies( $ticket_id )	{
	$kbs_ticket = new KBS_Ticket( $ticket_id );
	
	return $kbs_ticket->get_replies;
} // kbs_get_ticket_replies

/**
 * Re-open a closed ticket.
 *
 * @since	1.0
 * @param	arr	$data		$_GET super global.
 * @return	void.
 */
function kbs_reopen_ticket( $data )	{

	if( ! isset( $data['kbs-ticket-nonce'] ) || ! wp_verify_nonce( $data[ 'kbs-ticket-nonce' ], 'kbs-reopen-ticket' ) )	{
		$message = 'nonce_fail';
	} else	{
		remove_action( 'save_post_kbs_ticket', 'kbs_ticket_post_save', 10, 3 );
	
		if ( 'closed' == get_post_status( $data['post'] ) )	{
			$update = wp_update_post( array(
				'ID'          => $data['post'],
				'post_status' => 'open'
			) );
			
			if ( $update )	{
				$message = 'ticket_reopened';
			}
		}
		
		if ( ! isset( $message ) )	{
			$message = 'ticket_not_closed';
		}
		
	}
	
	$url = remove_query_arg( array( 'kbs-action', 'kbs-message', 'kbs-ticket-nonce' ) );
	
	wp_redirect( add_query_arg( 'kbs-message', $message, $url ) );

	die();

} // kbs_reopen_ticket
add_action( 'kbs-re-open-ticket', 'kbs_reopen_ticket' );

/**
 * Update the ticket status to open if the status is currently new.
 *
 * This function is called from the `kbs_post_assign_agent` hook which is fired
 * after an agent is assigned to a ticket.
 *
 * @since	1.0
 * @param	int		$ticket_id	The Ticket ID
 * @return	void.
 */
function kbs_ticket_status_from_new_to_open( $ticket_id )	{
	if ( 'new' == get_post_status( $ticket_id ) )	{
		kbs_set_ticket_status( $ticket_id, 'open' );
	}
}
add_action( 'kbs_post_assign_agent', 'kbs_ticket_status_from_new_to_open' );
