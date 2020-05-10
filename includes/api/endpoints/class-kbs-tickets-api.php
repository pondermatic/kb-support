<?php
/**
 * KB Support REST API
 *
 * @package     KBS
 * @subpackage  Classes/Tickets REST API
 * @copyright   Copyright (c) 2020, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.5
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * KBS_Tickets_API Class
 *
 * @since	1.0.8
 */
class KBS_Tickets_API extends KBS_API {

	/**
	 * Ticket ID
	 *
	 * @since	1.5
	 * @var		int
	 */
	protected $ticket_id = 0;

	/**
	 * Tickets
	 *
	 * @since	1.5
	 * @var		array
	 */
	protected $tickets = array();

	/**
	 * Get things going
	 *
	 * @since	1.5
	 */
	public function __construct( $post_type )	{
		$this->post_type = $post_type;
		$obj             = get_post_type_object( $post_type );
		$this->rest_base = ! empty( $obj->rest_base ) ? $obj->rest_base : $obj->name;
	} // __construct

	/**
	 * Registers the routes for the objects of the controller.
	 *
	 * @since	4.5
	 * @see		register_rest_route()
	 */
    public function register_routes()    {
        register_rest_route(
			$this->namespace . $this->version,
			'/' . $this->rest_base,
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_tickets' ),
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
					'args'                => $this->get_collection_params(),
				)
			)
		);

		register_rest_route(
			$this->namespace . $this->version,
			'/' . $this->rest_base . '/id=(?P<id>\d+)',
			array(
				'args'   => array(
					'id' => array(
						'type'        => 'integer',
						'description' => sprintf(
							__( 'Unique identifier for the %s.', 'kb-support' ),
							kbs_get_ticket_label_singular( true )
						)
					)
				),
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_ticket' ),
					'permission_callback' => array( $this, 'get_item_permissions_check' )
				)
			)
		);

		register_rest_route(
			$this->namespace . $this->version,
			'/' . $this->rest_base . '/number=(?P<number>[a-zA-Z0-9-]+)',
			array(
				'args'   => array(
					'id' => array(
						'type'        => 'string',
						'description' => sprintf(
							__( 'Unique identifier for the %s.', 'kb-support' ),
							kbs_get_ticket_label_singular( true )
						)
					)
				),
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_ticket' ),
					'permission_callback' => array( $this, 'get_item_permissions_check' )
				)
			)
		);
    } // register_routes

	/**
     * Checks if a given request has access to read a ticket.
     *
     * @since   1.5
     * @param	WP_REST_Request	$request	Full details about the request.
	 * @return	bool|WP_Error	True if the request has read access for the item, WP_Error object otherwise.
     */
    public function get_item_permissions_check( $request ) {
		if ( ! $this->is_authenticated( $request ) )	{
			return new WP_Error(
				'rest_forbidden_context',
				$this->errors( 'no_auth' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		return $this->validate_user();
    } // get_item_permissions_check

	/**
	 * Retrieves a single ticket.
	 *
	 * @since	1.5
	 * @param	WP_REST_Request	$request	Full details about the request
	 * @return	WP_REST_Response|WP_Error	Response object on success, or WP_Error object on failure.
	 */
	public function get_ticket( $request ) {
		$this->ticket_id = isset( $request['id'] ) ? $request['id'] : $this->get_ticket_by_number( $request );

		$ticket = new KBS_Ticket( absint( $this->ticket_id ) );

		if ( empty( $ticket->ID ) )	{
			return $ticket;
		}

		if ( ! $this->check_read_permission( $ticket ) )	{
			return new WP_Error(
				'rest_forbidden_context',
				$this->errors( 'no_permission' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		$data     = $this->prepare_ticket_for_response( $ticket, $request );
		$response = rest_ensure_response( $data );

		return $response;
	} // get_ticket

	/**
	 * Get ticket by number.
	 *
	 * @since	1.5
	 * @param	WP_REST_Request	$request	Full details about the request
	 * @return	object	KBS_Ticket object or false
	 */
	public function get_ticket_by_number( $request )	{
		global $wpdb;

		$ticket_id = $wpdb->get_var( $wpdb->prepare(
			"
			SELECT post_id
			FROM $wpdb->postmeta
			WHERE meta_key = '%s'
			AND meta_value = '%s'
			LIMIT 1
			",
			'_kbs_ticket_number',
			trim( $request['number'] )
		) );

		return $ticket_id;
	} // get_ticket_by_number

	/**
     * Checks if a given request has access to read multiple tickets.
     *
     * @since   1.5
     * @param	WP_REST_Request	$request	Full details about the request.
	 * @return	bool|WP_Error	True if the request has read access for the item, WP_Error object otherwise.
     */
    public function get_items_permissions_check( $request ) {
		return $this->get_item_permissions_check( $request );
    } // get_items_permissions_check

	/**
	 * Retrieves a collection of tickets.
	 *
	 * @since	1.5
	 * @param	WP_REST_Request		$request	Full details about the request
	 * @return	WP_REST_Response|WP_Error		Response object on success, or WP_Error object on failure
	 */
	function get_tickets( $request )	{
		// Retrieve the list of registered collection query parameters.
		$registered = $this->get_collection_params();
		$args       = array();

		// Only set arg values for allowed args
		foreach( $registered as $api_param => $collection_param )	{
			if ( isset( $request[ $api_param ] ) )	{
				$args[ $api_param ] = $request[ $api_param ];
			}
		}

		// Force the post_type argument, since it's not a user input variable.
		$args['post_type'] = $this->post_type;

		/**
		 * Filters the query arguments for a request.
		 *
		 * Enables adding extra arguments or setting defaults for a ticket collection request.
		 *
		 * @since	1.5
		 * @param	array			$args		Key value array of query var to query value
		 * @param	WP_REST_Request	$request	The request used
		 */
		$args = apply_filters( "rest_{$this->post_type}_query", $args, $request );

		$tickets_query = new KBS_Tickets_Query( $args );
		$query_result  = $tickets_query->get_tickets();

		foreach ( $query_result as $ticket ) {
			if ( ! $this->check_read_permission( $ticket ) ) {
				continue;
			}

			$data            = $this->prepare_ticket_for_response( $ticket, $request );
			$this->tickets[] = $this->prepare_response_for_collection( $data );
		}

		$response = rest_ensure_response( $this->tickets );

		return $response;
	} // get_tickets

	/**
	 * Prepares a single ticket output for response.
	 *
	 * @since	1.5
	 * @param	WP_Post				$ticket		KBS_Ticket Ticket object
	 * @param	WP_REST_Request		$request	Request object
	 * @return	WP_REST_Response	Response object
	 */
	public function prepare_ticket_for_response( $ticket, $request )	{
		$agent      = new KBS_Agent( $ticket->agent_id );
		$company    = new KBS_Company( $ticket->company_id );
		$data       = array();

		$data['id'] = $ticket->ID;

		if ( ! empty( $ticket->number ) )	{
			$data['number'] = $ticket->number;
		}

		if ( ! empty( $ticket->key) )	{
			$data['key'] = $ticket->key;
		}

		if ( ! empty( $ticket->status ) )	{
			$data['status'] = $ticket->status_nicename;
		}

		if ( ! empty( $ticket->date ) )	{
			$data['date'] = $ticket->date;
		}

		if ( ! empty( $ticket->modified_date ) )	{
			$data['modified_date'] = $ticket->modified_date;
		}

		if ( ! empty( $ticket->resolved_date ) )	{
			$data['resolved_date'] = $ticket->resolved_date;
		}

		if ( ! empty( $ticket->ticket_title ) )	{
			$data['subject'] = get_the_title( $ticket->ID );
		}

		if ( ! empty( $ticket->ticket_content ) )	{
			$data['content'] = $ticket->get_content();
		}

		if ( ! empty( $ticket->files ) )	{
			$files = array();

			foreach( $ticket->files as $file )	{
				$files[] = array(
					'filename' => get_the_title( $file->ID ),
					'url'      => $file->guid
				);
			}

			$data['attachments'] = $files;
		}

		if ( $terms = wp_get_post_terms( $ticket->ID, 'ticket_category' ) )	{
			$categories = array();

			foreach( $terms as $term )  {
				$categories[] = array(
					'term_id' => $term->term_id,
					'slug'    => $term->slug,
					'name'    => $term->name
				);
			}

			$data['categories'] = $categories;
		}

		if ( $terms = wp_get_post_terms( $ticket->ID, 'ticket_tag' ) )	{
			$tags = array();

			foreach( $terms as $term )  {
				$tags[] = array(
					'term_id' => $term->term_id,
					'slug'    => $term->slug,
					'name'    => $term->name
				);
			}

			$data['tags'] = $tags;
		}

		$data['agent'] = array(
			'user_id'      => $ticket->agent_id,
			'first_name'   => $agent ? $agent->first_name : '',
			'last_name'    => $agent ? $agent->last_name : '',
			'display_name' => $agent ? $agent->name : '',
			'email'        => $agent ? $agent->email : '',
		);

		if ( ! empty( $ticket->agents ) )	{
			foreach( $ticket->agents as $agent_id )	{
				$_agent = new KBS_Agent( $agent_id );

				$data['additional_agents']   = array();
				$data['additional_agents'][] = array(
					'user_id'      => $agent_id,
					'first_name'   => $_agent ? $_agent->first_name : '',
					'last_name'    => $_agent ? $_agent->last_name : '',
					'display_name' => $_agent ? $_agent->name : '',
					'email'        => $_agent ? $_agent->email : '',
				);
			}
		}

		if ( ! empty( $ticket->customer_id ) )	{
			$data['customer'] = array(
				'id'         => $ticket->customer_id,
				'first_name' => $ticket->first_name,
				'last_name'  => $ticket->last_name,
				'email'      => $ticket->email
			);
		}

		if ( ! empty( $ticket->email ) )	{
			$data['email'] = $ticket->email;
		}

		if ( ! empty( $ticket->user_id ) )	{
			$data['user_id'] = $ticket->user_id;
		}

		if ( ! empty( $ticket->user_info ) )	{
			$data['user_info'] = $ticket->user_info;
		}

		if ( ! empty( $this->company_id ) )	{
			$data['company'] = array(
				'id'      => $ticket->company_id,
				'name'    => $company ? $company->name : '',
				'contact' => $company ? $company->contact : '',
				'email'   => $company ? $company->email : '',
				'phone'   => $company ? $company->phone : '',
				'website' => $company ? $company->website : '',
				'logo'    => $company ? $company->logo : ''
			);
		}

		if ( ! empty( $ticket->participants ) )	{
			$data['participants'] = $ticket->participants;
		}

		if ( ! empty( $ticket->participants ) )	{
			$data['source'] = $ticket->get_source( 'name' );
		}

		// Wrap the data in a response object.
		$response = rest_ensure_response( $data );

		/**
		 * Filters the ticket data for a response.
		 *
		 * @since	1.5
		 *
		 * @param WP_REST_Response	$response	The response object
		 * @param KBS_Ticket		$ticket		Ticket object
		 * @param WP_REST_Request	$request	Request object
		 */
		return apply_filters( "rest_prepare_{$this->post_type}", $response, $ticket, $request );
	} // prepare_ticket_for_response

	/**
	 * Retrieves the query params for the posts collection.
	 *
	 * @since	1.5
	 * @return	array	Collection parameters
	 */
	public function get_collection_params() {
		$singular     = kbs_get_ticket_label_singular();
		$plural       = kbs_get_ticket_label_plural();
		$query_params = parent::get_collection_params();

		$query_params['context']['default'] = 'view';

		$query_params['number'] = array(
			'description'       => sprintf( 
				__( 'Maximum number of %s to be returned in result set.', 'kb-support' ),
				strtolower( $plural )
			),
			'type'              => 'integer',
			'default'           => 20,
			'minimum'           => 1,
			'maximum'           => 100,
			'sanitize_callback' => 'absint',
			'validate_callback' => 'rest_validate_request_arg'
		);

		$query_params['page'] = array(
			'description'       => __( 'Current page of the collection.' ),
			'type'              => 'integer',
			'default'           => 1,
			'sanitize_callback' => 'absint',
			'validate_callback' => 'rest_validate_request_arg',
			'minimum'           => 1
		);

		$query_params['ticket_ids'] = array(
			'description' => __( 'Limit result set to specific IDs.' ),
			'type'        => 'array',
			'items'       => array(
				'type' => 'integer',
			),
			'default'     => null
		);

		$query_params['orderby'] = array(
			'description' => __( 'Sort collection by object attribute.' ),
			'type'        => 'string',
			'default'     => 'ID',
			'enum'        => array(
				'ID',
				'date',
				'customer',
				'agent',
				'modified',
				'relevance',
				'ticket_ids',
				'title'
			)
		);

		$query_params['order'] = array(
			'description' => __( 'Order sort attribute ascending or descending.' ),
			'type'        => 'string',
			'default'     => 'desc',
			'enum'        => array( 'asc', 'desc' )
		);

		$query_params['user']         = array(
			'description' => sprintf(
				__( 'Limit result set to %s from a specific customer WP user account.' ),
				strtolower( $plural )
			),
			'type'        => 'integer',
			'default'     => null
		);

		$query_params['customer'] = array(
			'description' => sprintf(
				__( 'Limit result set to %s from a specific customer.' ),
				strtolower( $plural )
			),
			'type'        => 'integer',
			'default'     => null
		);

		$query_params['company'] = array(
			'description' => sprintf(
				__( 'Limit result set to %s from a specific company.' ),
				strtolower( $plural )
			),
			'type'        => 'integer',
			'default'     => null
		);

		$query_params['agent'] = array(
			'description' => sprintf(
				__( 'Limit result set to %s assigned to a specific agent.' ),
				strtolower( $plural )
			),
			'type'        => 'integer',
			'default'     => null
		);

		$query_params['agents'] = array(
			'description' => sprintf(
				__( 'Limit result set to %s assigned to specific agents.' ),
				strtolower( $plural )
			),
			'type'        => 'array',
			'items'       => array(
				'type' => 'integer',
			),
			'default'     => null
		);

		$query_params['status'] = array(
			'default'     => kbs_get_ticket_status_keys( false ),
			'description' => sprintf(
				__( 'Limit result set to %s assigned one or more statuses.', 'kb-support' ),
				strtolower( $singular )
			),
			'type'        => 'array',
			'items'       => array(
				'enum' => array_keys( kbs_get_post_statuses() ),
				'type' => 'string'
			)
		);

		$post_type = get_post_type_object( $this->post_type );

		/**
		 * Filter collection parameters for the tickets controller.
		 *
		 * The dynamic part of the filter `$this->post_type` refers to the post
		 * type slug for the controller.
		 *
		 * This filter registers the collection parameter, but does not map the
		 * collection parameter to an internal WP_Query parameter. Use the
		 * `rest_{$this->post_type}_query` filter to set WP_Query parameters.
		 *
		 * @since	1.5
		 *
		 * @param	array			$query_params	JSON Schema-formatted collection parameters.
		 * @param	WP_Post_Type	$post_type		Post type object.
		 */
		return apply_filters( "rest_{$this->post_type}_collection_params", $query_params, $post_type );
	} // get_collection_params

	/**
	 * Checks if a ticket can be read.
	 *
	 * @since	1.5
	 * @param	object	KBS_Ticket object
	 * @return	bool	Whether the post can be read.
	 */
	public function check_read_permission( $ticket )	{
		$can_access = false;

		if ( kbs_is_agent( $this->user_id ) )	{
			$can_access = kbs_agent_can_access_ticket( $ticket->ID, $this->user_id );
		} else	{
			$can_access = kbs_customer_can_access_ticket( $ticket->ID );
		}

		return $can_access;
	} // check_read_permission

} // KBS_Tickets_API
