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
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Get Tickets
 *
 * Retrieve tickets from the database.
 *
 * This is a simple wrapper for KBS_Tickets_Query.
 *
 * @since	1.0
 * @param	arr		$args		Arguments passed to get tickets
 * @return	obj		$payments	Tickets retrieved from the database
 */
function kbs_get_tickets( $args = array() ) {

	// Fallback to post objects to ensure backwards compatibility
	if( ! isset( $args['output'] ) ) {
		$args['output'] = 'posts';
	}

	$args    = apply_filters( 'kbs_get_tickets_args', $args );
	$tickets = new KBS_Tickets_Query( $args );
	return $tickets->get_tickets();
} // kbs_get_tickets

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
 * Count Tickets
 *
 * Returns the total number of tickets.
 *
 * @since	1.0
 * @param	arr	$args	List of arguments to base the ticket count on
 * @return	arr	$count	Number of tickets sorted by ticket date
 */
function kbs_count_tickets( $args = array() ) {

	global $wpdb;

	$defaults = array(
		'agent'      => null,
		'user'       => null,
		'customer'   => null,
		's'          => null,
		'start-date' => null,
		'end-date'   => null
	);

	$args = wp_parse_args( $args, $defaults );

	$select = "SELECT p.post_status,count( * ) AS num_posts";
	$join = '';
	$where = "WHERE p.post_type = 'kbs_ticket'";

	// Count tickets for a search
	if( ! empty( $args['s'] ) ) {

		if ( is_email( $args['s'] ) || strlen( $args['s'] ) == 32 ) {

			if( is_email( $args['s'] ) )	{
				$field = '_kbs_ticket_client';
			}

			$join = "LEFT JOIN $wpdb->postmeta m ON (p.ID = m.post_id)";
			$where .= $wpdb->prepare( "
				AND m.meta_key = %s
				AND m.meta_value = %s",
				$field,
				$args['s']
			);

		} elseif ( is_numeric( $args['s'] ) ) {

			$join = "LEFT JOIN $wpdb->postmeta m ON (p.ID = m.post_id)";
			$where .= $wpdb->prepare( "
				AND m.meta_key = '_mdjm_event_client'
				AND m.meta_value = %d",
				$args['s']
			);

		} else {
			$search = $wpdb->esc_like( $args['s'] );
			$search = '%' . $search . '%';

			$where .= $wpdb->prepare( "AND ((p.post_title LIKE %s) OR (p.post_content LIKE %s))", $search, $search );
		}

	}

	// Limit ticket count by received date
	if ( ! empty( $args['start-date'] ) && false !== strpos( $args['start-date'], '-' ) ) {

		$date_parts = explode( '-', $args['start-date'] );
		$year       = ! empty( $date_parts[0] ) && is_numeric( $date_parts[0] ) ? $date_parts[0] : 0;
		$month      = ! empty( $date_parts[1] ) && is_numeric( $date_parts[1] ) ? $date_parts[1] : 0;
		$day        = ! empty( $date_parts[2] ) && is_numeric( $date_parts[2] ) ? $date_parts[2] : 0;

		$is_date    = checkdate( $month, $day, $year );
		if ( false !== $is_date ) {

			$date   = new DateTime( $args['start-date'] );
			$where .= $wpdb->prepare( " AND p.post_date >= '%s'", $date->format( 'Y-m-d' ) );

		}

		// Fixes an issue with the tickets list table counts when no end date is specified (partly with stats class)
		if ( empty( $args['end-date'] ) ) {
			$args['end-date'] = $args['start-date'];
		}

	}

	if ( ! empty ( $args['end-date'] ) && false !== strpos( $args['end-date'], '-' ) ) {

		$date_parts = explode( '-', $args['end-date'] );
		$year       = ! empty( $date_parts[0] ) && is_numeric( $date_parts[0] ) ? $date_parts[0] : 0;
		$month      = ! empty( $date_parts[1] ) && is_numeric( $date_parts[1] ) ? $date_parts[1] : 0;
		$day        = ! empty( $date_parts[2] ) && is_numeric( $date_parts[2] ) ? $date_parts[2] : 0;

		$is_date    = checkdate( $month, $day, $year );
		if ( false !== $is_date ) {

			$date   = new DateTime( $args['end-date'] );
			$where .= $wpdb->prepare( " AND p.post_date <= '%s'", $date->format( 'Y-m-d' ) );

		}

	}

	$where = apply_filters( 'kbs_count_tickets_where', $where );
	$join  = apply_filters( 'kbs_count_tickets_join', $join );

	$query = "$select
		FROM $wpdb->posts p
		$join
		$where
		GROUP BY p.post_status
	";

	$cache_key = md5( $query );

	$count = wp_cache_get( $cache_key, 'counts' );

	if ( false !== $count ) {
		return $count;
	}

	$count = $wpdb->get_results( $query, ARRAY_A );
	$stats    = array();
	$total    = 0;
	$statuses = kbs_get_ticket_status_keys();

	foreach ( $statuses as $state ) {
		$stats[ $state ] = 0;
	}

	foreach ( (array) $count as $row ) {
		if ( ! in_array( $row['post_status'], $statuses ) )	{
			continue;
		}
		$stats[ $row['post_status'] ] = $row['num_posts'];
	}

	$stats = (object) $stats;
	wp_cache_set( $cache_key, $stats, 'counts' );

	return $stats;
} // kbs_count_tickets

/**
 * Retrieve all ticket statuses.
 *
 * @since	1.0
 * @return	arr
 */
function kbs_get_ticket_statuses()	{
	$ticket_statuses = kbs_get_post_statuses( 'labels', true );
	$statuses        = array();
	
	foreach ( $ticket_statuses as $ticket_status ) {
		$statuses[ $ticket_status->name ] = esc_html( $ticket_status->label );
	}

	return $statuses;
} // kbs_get_ticket_statuses

/**
 * Retrieves keys for all available ticket statuses.
 *
 * @since	1.0
 * @return	arr		$ticket_status	All available ticket statuses
 */
function kbs_get_ticket_status_keys() {
	$statuses = array_keys( kbs_get_ticket_statuses() );
	asort( $statuses );

	return array_values( $statuses );
} // kbs_get_ticket_status_keys

/**
 * Retrieve the possible sources for logging a ticket.
 *
 * Custom sources can be added by hooking the `kbs_ticket_log_sources` filter.
 *
 * @since	1.0
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
 * @param	arr		$ticket_data	Ticket data.
 * @return	mixed	Ticket ID on success, false on failure.
 */
function kbs_add_ticket( $ticket_data )	{

	if ( ! empty( $ticket_data['attachments'] ) && ! is_array( $ticket_data['attachments'] ) )	{
		$ticket_data['attachments'] = array( $ticket_data['attachments'] );
	}

	$ticket_data = apply_filters( 'kbs_add_ticket_data', $ticket_data );
	$attachments = apply_filters( 'kbs_add_ticket_attachments', $ticket_data['attachments'] );

	$ticket = new KBS_Ticket();

	$ticket->status         = ! empty( $ticket_data['status'] )          ? $ticket_data['status']          : 'new';
	$ticket->ticket_title   = $ticket_data['post_title'];
	$ticket->ticket_content = $ticket_data['post_content'];
	$ticket->agent          = ! empty( $ticket_data['agent'] )           ? $ticket_data['agent']           : '';
	$ticket->user_info      = $ticket_data['user_info'];
	$ticket->user_id        = ! empty( $ticket_data['user_info']['id'] ) ? $ticket_data['user_info']['id'] : '';
	$ticket->email          = $ticket_data['user_email'];
	$ticket->first_name     = $ticket_data['user_info']['first_name'];
	$ticket->last_name      = $ticket_data['user_info']['last_name'];
	$ticket->email          = $ticket_data['user_info']['email'];
	$ticket->ip             = kbs_get_ip();
	$ticket->sla            = '';
	$ticket->source         = '';
	$ticket->new_files      = $ticket_data['attachments'];

	if ( isset( $ticket_data['post_date'] ) ) {
		$ticket->date = $ticket_data['post_date'];
	}

	do_action( 'kbs_before_add_ticket', $ticket->ID, $ticket_data );

	$ticket->save();

	do_action( 'kbs_add_ticket', $ticket->ID, $ticket_data );

	if ( ! empty( $ticket->ID ) ) {
		return $ticket->ID;
	}

	// Return false if no ticket was inserted
	return false;

} // kbs_add_ticket

/**
 * Adds a new ticket from a form submission.
 *
 * @since	1.0
 * @param	int		$form_id	Form ID
 * @param	arr		$form_data	Array of ticket data.
 * @return	mixed	Ticket ID on success, false on failure.
 */
function kbs_add_ticket_from_form( $form_id, $form_data )	{

	$kbs_form    = new KBS_Form( $form_id );
	$fields      = $kbs_form->fields;
	$data        = array();

	$ticket_data = array(
		'user_info'   => array(),
		'attachments' => array()
	);

	foreach( $fields as $field )	{

		$settings = $kbs_form->get_field_settings( $field->ID );

		if ( 'file_upload' == $settings['type'] && ! empty( $_FILES[ $field->post_name ] ) )	{
			$ticket_data['attachments'] = $_FILES[ $field->post_name ];
			continue;
		}

		if ( empty( $form_data[ $field->post_name ] ) )	{
			continue;
		}

		if ( ! empty( $settings['mapping'] ) )	{

			if ( 'customer_first' == $settings['mapping'] )	{
				$ticket_data['user_info']['first_name'] = ucfirst( sanitize_text_field( $form_data[ $field->post_name ] ) );
			} elseif ( 'customer_last' == $settings['mapping'] )	{
				$ticket_data['user_info']['last_name'] = ucfirst( sanitize_text_field( $form_data[ $field->post_name ] ) );
			} elseif ( 'customer_email' == $settings['mapping'] )	{
				$ticket_data['user_info']['email'] = strtolower( $form_data[ $field->post_name ] );
				$ticket_data['user_email']         = $ticket_data['user_info']['email'];
			} else	{
				$ticket_data[ $settings['mapping'] ] = $form_data[ $field->post_name ];
			}

		} else	{

			$ticket_data[ $field->post_name ] = array( $field->post_title, strip_tags( addslashes( $form_data[ $field->post_name ] ) ) );
		
			$data[] = '<strong>' . $field->post_title . '</strong><br />' . $form_data[ $field->post_name ];

		}
	}

	if ( ! empty( $data ) )	{
		$ticket_content  = '<p><strong>' . __( 'Form Data Submitted', 'kb-support' ) . '</strong></p>';
		$ticket_content .= '<p> ' . implode( '<br />', $data ) . '</p>';
	
		if ( ! empty( $ticket_data['post_content'] ) )	{
			$ticket_data['post_content'] = $ticket_data['post_content'] . $ticket_content;
		} else	{
			$ticket_data['post_content'] = $ticket_content;
		}
	}

	$ticket_data = apply_filters( 'kbs_add_ticket_from_form_data', $ticket_data );

	$ticket_id = kbs_add_ticket( $ticket_data );

	if ( $ticket_id )	{
		$kbs_form->increment_submissions();
		return $ticket_id;
	}

	return false;

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

/**
 * Ticket reply functions.
 *
 * @since	1.0
 */

/**
 * Retrieve the IDs of all ticket replies.
 *
 * @since	1.0
 * @param	int		$ticket_id		The Ticket ID.
 * @param	arr		$args			See @get_children
 * @return	obj|false
 */
function kbs_get_ticket_replies( $ticket_id = 0, $args = array() )	{
	if ( empty( $ticket_id ) )	{
		return false;
	}

	$defaults = array(
		''
	);

} // kbs_get_ticket_replies
