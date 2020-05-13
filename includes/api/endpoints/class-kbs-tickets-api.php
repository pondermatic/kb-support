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
 * @since	1.5
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
					'callback'            => array( $this, 'get_items' ),
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
					'args'                => $this->get_collection_params(),
				)
			)
		);

		register_rest_route(
			$this->namespace . $this->version,
			'/' . $this->rest_base . '/(?P<id>\d+)',
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
					'callback'            => array( $this, 'get_item' ),
					'permission_callback' => array( $this, 'get_item_permissions_check' )
				)
			)
		);

		register_rest_route(
			$this->namespace . $this->version,
			'/' . $this->rest_base . '/(?P<number>[a-zA-Z0-9-]+)',
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
	public function get_item( $request ) {
		$this->ticket_id = isset( $request['id'] ) ? $request['id'] : $this->get_ticket_id_by_number( $request );

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

		$data     = $this->prepare_item_for_response( $ticket, $request );
		$response = rest_ensure_response( $data );

		return $response;
	} // get_item

	/**
	 * Get ticket ID by number.
	 *
	 * @since	1.5
	 * @param	WP_REST_Request	$request	Full details about the request
	 * @return	object	KBS_Ticket object or false
	 */
	public function get_ticket_id_by_number( $request )	{
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
	} // get_ticket_id_by_number

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
	function get_items( $request )	{
		// Retrieve the list of registered collection query parameters.
		$registered = $this->get_collection_params();
		$args       = array();
        $meta_query = array();

        /*
		 * This array defines mappings between public API query parameters whose
		 * values are accepted as-passed, and their internal WP_Query parameter
		 * name equivalents (some are the same). Only values which are also
		 * present in $registered will be set.
		 */
		$parameter_mappings = array(
			'exclude'        => 'post__not_in',
			'include'        => 'post__in',
			'offset'         => 'offset',
			'order'          => 'order',
			'orderby'        => 'orderby',
			'page'           => 'paged',
			'status'         => 'post_status'
		);

		/*
		 * For each known parameter which is both registered and present in the request,
		 * set the parameter's value on the query $args.
		 */
		foreach ( $parameter_mappings as $api_param => $wp_param ) {
			if ( isset( $registered[ $api_param ], $request[ $api_param ] ) ) {
				$args[ $wp_param ] = $request[ $api_param ];
			}
		}

        // Check for & assign any parameters which require special handling or setting.
		$args['date_query'] = array();

		// Set before into date query. Date query must be specified as an array of an array.
		if ( isset( $registered['before'], $request['before'] ) ) {
			$args['date_query'][0]['before'] = $request['before'];
		}

		// Set after into date query. Date query must be specified as an array of an array.
		if ( isset( $registered['after'], $request['after'] ) ) {
			$args['date_query'][0]['after'] = $request['after'];
		}

        // Ensure our per_page parameter overrides any provided posts_per_page filter.
		if ( isset( $registered['per_page'] ) ) {
			$args['posts_per_page'] = $request['per_page'];
		}

        // By a customer's WordPress user ID
        if ( isset( $registered['user'], $request['user'] ) ) {
            $meta_query[] = array(
                'key'   => '_kbs_ticket_user_id',
                'value' => (int) $request['user'],
                'type'  => 'NUMERIC'
            );
        }

        // By a customer's ID
        if ( isset( $registered['customer'], $request['customer'] ) ) {
            $meta_query[] = array(
                'key'   => '_kbs_ticket_customer_id',
                'value' => (int) $request['customer'],
                'type'  => 'NUMERIC'
            );
        }

        // By a company ID
        if ( isset( $registered['company'], $request['company'] ) ) {
            $meta_query[] = array(
                'key'   => '_kbs_ticket_company_id',
                'value' => (int) $request['company'],
                'type'  => 'NUMERIC'
            );
        }

        // By an agent ID
        if ( isset( $registered['agent'], $request['agent'] ) ) {
            $meta_query[] = array(
                'key'   => '_kbs_ticket_agent_id',
                'value' => (int) $request['agent'],
                'type'  => 'NUMERIC'
            );
        }

		// Force the post_type argument, since it's not a user input variable.
		$args['post_type'] = $this->post_type;

        if ( ! empty( $meta_query ) )   {
            $args['meta_query'] = $meta_query;
        }

		/**
		 * Filters the query arguments for a request.
		 *
		 * Enables adding extra arguments or setting defaults for a ticket collection request.
		 *
		 * @since	1.5
		 * @param	array			$args		Key value array of query var to query value
		 * @param	WP_REST_Request	$request	The request used
		 */
		$args          = apply_filters( "rest_{$this->post_type}_query", $args, $request );
		$query_args    = $this->prepare_items_query( $args, $request );

		$tickets_query = new WP_Query();
		$query_result  = $tickets_query->query( $query_args );

		foreach ( $query_result as $_ticket ) {
			if ( ! $this->check_read_permission( $_ticket ) ) {
				continue;
			}

            $ticket          = new KBS_Ticket( $_ticket->ID );
			$data            = $this->prepare_item_for_response( $ticket, $request );
			$this->tickets[] = $this->prepare_response_for_collection( $data );
		}

        $page          = (int) $query_args['paged'];
		$total_tickets = $tickets_query->found_posts;

		if ( $total_tickets < 1 ) {
			// Out-of-bounds, run the query again without LIMIT for total count.
			unset( $query_args['paged'] );

			$count_query   = new WP_Query();
			$count_query->query( $query_args );
			$total_tickets = $count_query->found_posts;
		}

		$max_pages = ceil( $total_tickets / (int) $tickets_query->query_vars['posts_per_page'] );

		if ( $page > $max_pages && $total_tickets > 0 ) {
			return new WP_Error(
				'rest_post_invalid_page_number',
				__( 'The page number requested is larger than the number of pages available.' ),
				array( 'status' => 400 )
			);
		}

		$response = rest_ensure_response( $this->tickets );

		$response->header( 'X-WP-Total', (int) $total_tickets );
		$response->header( 'X-WP-TotalPages', (int) $max_pages );

		$request_params = $request->get_query_params();
		$base           = add_query_arg( urlencode_deep( $request_params ), rest_url( sprintf( '%s/%s', $this->namespace, $this->rest_base ) ) );

		if ( $page > 1 ) {
			$prev_page = $page - 1;

			if ( $prev_page > $max_pages ) {
				$prev_page = $max_pages;
			}

			$prev_link = add_query_arg( 'page', $prev_page, $base );
			$response->link_header( 'prev', $prev_link );
		}
		if ( $max_pages > $page ) {
			$next_page = $page + 1;
			$next_link = add_query_arg( 'page', $next_page, $base );

			$response->link_header( 'next', $next_link );
		}

		return $response;
	} // get_items

	/**
	 * Prepares a single ticket output for response.
	 *
	 * @since	1.5
	 * @param	WP_Post				$ticket		KBS_Ticket Ticket object
	 * @param	WP_REST_Request		$request	Request object
	 * @return	WP_REST_Response	Response object
	 */
	public function prepare_item_for_response( $ticket, $request )	{
		$agent      = new KBS_Agent( $ticket->agent_id );
		$company    = new KBS_Company( $ticket->company_id );
		$data       = array();

		$data['id'] = $ticket->ID;

		if ( ! empty( $ticket->date ) )	{
			$data['date'] = $ticket->date;
		}

		if ( ! empty( $ticket->modified_date ) )	{
			$data['modified_date'] = $ticket->modified_date;
		}

		if ( ! empty( $ticket->status_nicename ) )	{
			$data['status'] = $ticket->post_status;
		}

		if ( ! empty( $ticket->ticket_title ) )	{
			$data['subject'] = get_the_title( $ticket->ID );
		}

		if ( ! empty( $ticket->ticket_content ) )	{
            $data['content'] = array();
            $data['content']['raw'] = $ticket->ticket_content;
            $data['content']['rendered'] = apply_filters( 'the_content', $ticket->ticket_content );
		}

		$taxonomies = wp_list_filter(
			get_object_taxonomies( $this->post_type, 'objects' ),
			array( 'show_in_rest' => true )
		);

		foreach ( $taxonomies as $taxonomy ) {
			$base = ! empty( $taxonomy->rest_base ) ? $taxonomy->rest_base : $taxonomy->name;

			if ( ! empty( $base ) ) {
				$terms         = get_the_terms( $ticket->ID, $taxonomy->name );
				$data[ $base ] = $terms ? array_values( wp_list_pluck( $terms, 'term_id' ) ) : array();
			}
		}

		$data['ticket_data'] = array();

		if ( ! empty( $ticket->resolved_date ) )	{
			$data['tickt_data']['resolved_date'] = $ticket->resolved_date;
		}

		if ( ! empty( $ticket->number ) )	{
			$data['ticket_data']['number'] = $ticket->number;
		}

		if ( ! empty( $ticket->key ) )	{
			$data['ticket_data']['key'] = $ticket->key;
		}

		if ( ! empty( $ticket->files ) )	{
			$files = array();

			foreach( $ticket->files as $file )	{
				$files[] = array(
					'filename' => get_the_title( $file->ID ),
					'url'      => $file->guid
				);
			}

			$data['ticket_data']['attachments'] = $files;
		}

		$data['ticket_data']['agent'] = $ticket->agent_id;

		if ( ! empty( $ticket->agents ) )	{
			$data['ticket_data']['additional_agents'] = $ticket->agents;
		}

		if ( ! empty( $ticket->customer_id ) )	{
			$data['ticket_data']['customer'] = $ticket->customer_id;
		}

		if ( ! empty( $ticket->email ) )	{
			$data['ticket_data']['email'] = $ticket->email;
		}

		if ( ! empty( $ticket->user_id ) )	{
			$data['ticket_data']['user_id'] = $ticket->user_id;
		}

		if ( ! empty( $ticket->user_info ) )	{
			$data['ticket_data']['user_info'] = $ticket->user_info;
		}

		if ( ! empty( $this->company_id ) )	{
			$data['ticket_data']['company'] = $ticket->company_id;
		}

		if ( ! empty( $ticket->participants ) )	{
			$data['ticket_data']['participants'] = $ticket->participants;
		}

		if ( ! empty( $ticket->participants ) )	{
			$data['ticket_data']['source'] = $ticket->get_source( 'name' );
		}

		if ( ! empty( $ticket->form_data ) )	{
			$data['form_data'] = $ticket->form_data;
		}

		// Wrap the data in a response object.
		$response = rest_ensure_response( $data );
		$links    = $this->prepare_links( $ticket );

		$response->add_links( $links );

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
	} // prepare_item_for_response

	/**
	 * Retrieves the query params for the tickets collection.
	 *
	 * @since	1.5
	 * @return	array	Collection parameters
	 */
	public function get_collection_params() {
		$singular     = kbs_get_ticket_label_singular();
		$plural       = kbs_get_ticket_label_plural();
		$query_params = parent::get_collection_params();

		$query_params['context']['default'] = 'view';

		$query_params['orderby'] = array(
			'description' => __( 'Sort collection by object attribute.', 'kb-support' ),
			'type'        => 'string',
			'default'     => 'id',
			'enum'        => array(
				'id',
				'date',
                'agent',
				'customer',
				'agent',
				'modified',
				'include',
				'title'
			)
		);

		$query_params['user'] = array(
			'description' => sprintf(
				__( 'Limit result set to %s from a specific customer WP user account.', 'kb-support' ),
				strtolower( $plural )
			),
			'type'        => 'integer',
			'default'     => null
		);

		$query_params['customer'] = array(
			'description' => sprintf(
				__( 'Limit result set to %s from a specific customer.', 'kb-support' ),
				strtolower( $plural )
			),
			'type'        => 'integer',
			'default'     => null
		);

		$query_params['company'] = array(
			'description' => sprintf(
				__( 'Limit result set to %s from a specific company.', 'kb-support' ),
				strtolower( $plural )
			),
			'type'        => 'integer',
			'default'     => null
		);

		$query_params['agent'] = array(
			'description' => sprintf(
				__( 'Limit result set to %s assigned to a specific agent.', 'kb-support' ),
				strtolower( $plural )
			),
			'type'        => 'integer',
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

    /**
	 * Determines the allowed query_vars for a get_items() response and prepares
	 * them for WP_Query.
	 *
	 * @since  1.5
	 * @param  array           $prepared_args  Optional. Prepared WP_Query arguments. Default empty array.
	 * @param  WP_REST_Request $request        Optional. Full details about the request.
	 * @return array           Items query arguments.
	 */
	protected function prepare_items_query( $prepared_args = array(), $request = null ) {
		$query_args = array();

		foreach ( $prepared_args as $key => $value ) {
			/**
			 * Filters the query_vars used in get_items() for the constructed query.
			 *
			 * The dynamic portion of the hook name, `$key`, refers to the query_var key.
			 *
			 * @since    1.5
			 *
			 * @param    string  $value  The query_var value.
			 */
			$query_args[ $key ] = apply_filters( "rest_query_var-{$key}", $value ); // phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores
		}

		// Map to proper WP_Query orderby param.
		if ( isset( $query_args['orderby'] ) && isset( $request['orderby'] ) ) {
			$orderby_mappings = array(
				'id'            => 'ID',
				'include'       => 'post__in'
			);

			if ( isset( $orderby_mappings[ $request['orderby'] ] ) ) {
				$query_args['orderby'] = $orderby_mappings[ $request['orderby'] ];
			}
		}

		return $query_args;
	} // prepare_items_query

	/**
	 * Prepares links for the request.
	 *
	 * @since	1.5
	 * @param	KBS_Ticket	$ticket		KBS Ticket object
	 * @return	array		Links for the given post
	 */
	protected function prepare_links( $ticket ) {
		$base = sprintf( '%s/%s', $this->namespace . $this->version, $this->rest_base );

		// Entity meta.
		$links = array(
			'self'       => array(
				'href' => rest_url( trailingslashit( $base ) . $ticket->ID ),
			),
			'collection' => array(
				'href' => rest_url( $base ),
			)
		);

		if ( ! empty( $ticket->agent_id ) )	{
			$links['agent'] = array(
				'href'       => rest_url( 'wp/v2/users/' . $ticket->agent_id ),
				'embeddable' => true,
			);
		}

		if ( ! empty( $ticket->agents ) )	{
			$links['additional_agents'] = array();
			foreach( $ticket->agents as $agent_id )	{
				if ( empty( $agent_id ) )
					continue;

				$links['additional_agents'][] = array(
					'href'       => rest_url( 'wp/v2/users/' . $agent_id ),
					'embeddable' => true
				);
			};
		}

		if ( ! empty( $ticket->customer_id ) )	{
			$links['customer'] = array(
				'href'       => rest_url( 'kbs/v1/customers/' . $ticket->customer_id ),
				'embeddable' => true,
			);
		}

		if ( ! empty( $ticket->user_id ) )	{
			$links['user'] = array(
				'href'       => rest_url( 'wp/v2/users/' . $ticket->user_id ),
				'embeddable' => true
			);
		}

		if ( ! empty( $ticket->company_id ) )	{
			$links['company'] = array(
				'href'       => rest_url( 'kbs/v1/companies/' . $ticket->company_id ),
				'embeddable' => true,
			);
		}

		return $links;
	} // prepare_links

} // KBS_Tickets_API
