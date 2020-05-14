<?php
/**
 * KB Support REST API
 *
 * @package     KBS
 * @subpackage  Classes/Replies REST API
 * @copyright   Copyright (c) 2020, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.5
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * KBS_Replies_API Class
 *
 * @since	1.5
 */
class KBS_Replies_API extends WP_REST_Posts_Controller {

    /**
     * User ID.
     *
     * @since   1.5
     * @var     int
     */
    protected $user_id;

	/**
	 * Get things going
	 *
	 * @since	1.5
	 */
	public function __construct( $post_type )	{
        parent::__construct( $post_type );

        $this->namespace = KBS()->api->namespace;
        $this->version   = KBS()->api->version;
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

    } // register_routes

	/**
     * Checks if a given request has access to read a reply.
     *
     * @since   1.5
     * @param	WP_REST_Request	$request	Full details about the request.
	 * @return	bool|WP_Error	True if the request has read access for the item, WP_Error object otherwise.
     */
    public function get_item_permissions_check( $request ) {
		if ( ! KBS()->api->is_authenticated( $request ) )	{
			return new WP_Error(
				'rest_forbidden_context',
				KBS()->api->errors( 'no_auth' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

        $this->user_id = KBS()->api->user_id;

        if ( ! KBS()->api->validate_user() )    {
            return new WP_Error(
				'rest_forbidden_context',
				KBS()->api->errors( 'no_auth' ),
				array( 'status' => rest_authorization_required_code() )
			);
        }

        $post = $this->get_post( $request['id'] );

        if ( is_wp_error( $post ) ) {
			return $post;
		}

        if ( 'edit' === $request['context'] ) {
			return new WP_Error(
				'rest_forbidden_context',
				__( 'Sorry, you are not allowed to edit this object.', 'kb-support' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		if ( $post ) {
			return $this->check_read_permission( $post );
		}

		return true;
    } // get_item_permissions_check

    /**
     * Checks if a given request has access to read multiple replies.
     *
     * @since   1.5
     * @param	WP_REST_Request	$request	Full details about the request.
	 * @return	bool|WP_Error	True if the request has read access for the item, WP_Error object otherwise.
     */
    public function get_items_permissions_check( $request ) {
        if ( ! KBS()->api->is_authenticated( $request ) )	{
			return new WP_Error(
				'rest_forbidden_context',
				KBS()->api->errors( 'no_auth' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

        $this->user_id = KBS()->api->user_id;

        if ( ! KBS()->api->validate_user() )    {
            return new WP_Error(
				'rest_forbidden_context',
				KBS()->api->errors( 'no_auth' ),
				array( 'status' => rest_authorization_required_code() )
			);
        }

        $post_type = get_post_type_object( $this->post_type );

		if ( 'edit' === $request['context'] ) {
			return new WP_Error(
				'rest_forbidden_context',
				__( 'Sorry, you are not allowed to edit this object.', 'kb-support' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		return true;
    } // get_items_permissions_check

    /**
	 * Checks if a given request has access to add a reply.
	 *
	 * @since  1.5
	 *
	 * @param  WP_REST_Request $request    Full details about the request.
	 * @return true|WP_Error   True if the request has access to update the item, WP_Error object otherwise.
	 */
	public function add_item_permissions_check( $request ) {
        if ( ! KBS()->api->is_authenticated( $request ) )	{
			return new WP_Error(
				'rest_forbidden_context',
				KBS()->api->errors( 'no_auth' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

        $this->user_id = KBS()->api->user_id;

        if ( ! KBS()->api->validate_user() )    {
            return new WP_Error(
				'rest_forbidden_context',
				KBS()->api->errors( 'no_auth' ),
				array( 'status' => rest_authorization_required_code() )
			);
        }

        $post = $this->get_post( $request['id'] );

		if ( is_wp_error( $post ) ) {
			return $post;
		}

		$post_type = get_post_type_object( $this->post_type );

		if ( $post && ! $this->check_update_permission( $post ) ) {
			return new WP_Error(
				'rest_cannot_edit',
				sprintf(
                    __( 'Sorry, you are not allowed to edit this %s.', 'kb-support' ),
                    kbs_get_ticket_label_singular( true )
                ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

        return true;
    } // add_item_permissions_check

	/**
	 * Retrieves a single reply.
	 *
	 * @since	1.5
	 * @param	WP_REST_Request	$request	Full details about the request
	 * @return	WP_REST_Response|WP_Error	Response object on success, or WP_Error object on failure.
	 */
	public function get_item( $request ) {
		$ticket_id = isset( $request['id'] );

		$post = $this->get_post( absint( $ticket_id ) );

		if ( is_wp_error( $post ) ) {
			return $post;
		}

		$data     = $this->prepare_item_for_response( $post, $request );
		$response = rest_ensure_response( $data );

		return $response;
	} // get_item

	/**
	 * Retrieves a collection of replies.
	 *
	 * @since	1.5
	 * @param	WP_REST_Request		$request	Full details about the request
	 * @return	WP_REST_Response|WP_Error		Response object on success, or WP_Error object on failure
	 */
	function get_items( $request )	{
        // Ensure a search string is set in case the orderby is set to 'relevance'.
		if ( ! empty( $request['orderby'] ) && 'relevance' === $request['orderby'] && empty( $request['search'] ) ) {
			return new WP_Error(
				'rest_no_search_term_defined',
				__( 'You need to define a search term to order by relevance.' ),
				array( 'status' => 400 )
			);
		}

		// Ensure an include parameter is set in case the orderby is set to 'include'.
		if ( ! empty( $request['orderby'] ) && 'include' === $request['orderby'] && empty( $request['include'] ) ) {
			return new WP_Error(
				'rest_orderby_include_missing_include',
				__( 'You need to define an include parameter to order by include.' ),
				array( 'status' => 400 )
			);
		}

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
            'author'         => 'author__in',
			'author_exclude' => 'author__not_in',
			'exclude'        => 'post__not_in',
			'include'        => 'post__in',
			'offset'         => 'offset',
			'order'          => 'order',
			'orderby'        => 'orderby',
			'page'           => 'paged',
            'parent'         => 'post_parent__in',
			'parent_exclude' => 'post_parent__not_in',
			'search'         => 's',
			'slug'           => 'post_name__in'
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

        // By a customer's ID
        if ( isset( $registered['customer'], $request['customer'] ) ) {
            $meta_query[] = array(
                'key'   => '_kbs_reply_customer_id',
                'value' => (int) $request['customer'],
                'type'  => 'NUMERIC'
            );
        }

        // By an agent ID
        if ( isset( $registered['agent'], $request['agent'] ) ) {
            $meta_query[] = array(
                'key'   => '_kbs_reply_agent_id',
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
		$args          = apply_filters( "kbs_rest_{$this->post_type}_query", $args, $request );
		$query_args    = $this->prepare_items_query( $args, $request );

        $taxonomies = wp_list_filter( get_object_taxonomies( $this->post_type, 'objects' ), array( 'show_in_rest' => true ) );

		$posts_query  = new WP_Query();
		$query_result = $posts_query->query( $query_args );
        $posts        = array();

		foreach ( $query_result as $post ) {
			if ( ! $this->check_read_permission( $post ) ) {
				continue;
			}

			$data    = $this->prepare_item_for_response( $post, $request );
			$posts[] = $this->prepare_response_for_collection( $data );
		}

        $page        = (int) $query_args['paged'];
		$total_posts = $posts_query->found_posts;

		if ( $total_posts < 1 ) {
			// Out-of-bounds, run the query again without LIMIT for total count.
			unset( $query_args['paged'] );

			$count_query = new WP_Query();
			$count_query->query( $query_args );
			$total_posts = $count_query->found_posts;
		}

		$max_pages = ceil( $total_posts / (int) $posts_query->query_vars['posts_per_page'] );

		if ( $page > $max_pages && $total_posts > 0 ) {
			return new WP_Error(
				'rest_post_invalid_page_number',
				__( 'The page number requested is larger than the number of pages available.' ),
				array( 'status' => 400 )
			);
		}

		$response = rest_ensure_response( $posts );

		$response->header( 'X-WP-Total', (int) $total_posts );
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
	 * Updates a single ticket.
	 *
	 * @since  1.5
	 *
	 * @param  WP_REST_Request             $request    Full details about the request.
	 * @return WP_REST_Response|WP_Error   Response object on success, or WP_Error object on failure.
	 */
	public function update_item( $request ) {
		$valid_check = $this->get_post( $request['id'] );
		if ( is_wp_error( $valid_check ) ) {
			return $valid_check;
		}

        $ticket = new KBS_Ticket( $request['id'] );
        if ( empty( $ticket->ID ) ) {
			return $ticket;
		}

        /** This action is documented in wp-includes/rest-api/endpoints/class-wp-rest-posts-controller.php */
		do_action( "kbs_rest_insert_{$this->post_type}", $ticket, $request, false );

        if ( ! empty( $request['agent'] ) && kbs_is_agent( absint( $request['agent'] ) ) )    {
            $ticket->__set( 'agent_id', absint( $request['agent'] ) );
        }

        if ( kbs_multiple_agents() )    {
            if ( ! empty( $request['agents'] ) )    {
                $agents = implode( ',', $request['agents'] );
                $agents = array_map( 'absint', $agents );

                foreach( $agents as $key => $value )    {
                    if ( ! kbs_is_agent( $value ) ) {
                        unset( $agents[ $key ] );
                    }
                }

                $ticket->__set( 'agents', $agents );
            }
        }

        if ( ! empty( $request['company'] ) && ! empty( get_post( absint( $request['company'] ) ) ) )    {
            $ticket->__set( 'company_id', absint( $request['company'] ) );
        }

        if ( ! empty( $request['customer'] ) && kbs_customer_exists( absint( $request['customer'] ) ) )    {
            $ticket->__set( 'customer_id', absint( $request['customer'] ) );
        }

        if ( ! empty( $request['status'] ) && in_array( $request['status'], kbs_get_ticket_status_keys() ) )    {
            $ticket->__set( 'status', $request['status'] );
        }

        if ( ! empty( $request['category'] ) )  {
            $ticket->__set( 'ticket_category', $request['category'] );
        }

        $ticket->save();

        $post     = get_post( $request['id'] );
        $response = $this->prepare_item_for_response( $post, $request );

		return rest_ensure_response( $response );
    } // update_item

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
	 * Checks if a ticket can be updated.
	 *
	 * @since	1.5
	 * @param	object	WP_Post object
	 * @return	bool	Whether the post can be read.
	 */
	public function check_update_permission( $post )	{
		$can_access = false;

		if ( kbs_is_agent( $this->user_id ) )	{
			$can_access = kbs_agent_can_access_ticket( $post->ID, $this->user_id );
		}

		return $can_access;
	} // check_update_permission

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
	 * @param	WP_Post  $post		WP_Post object
	 * @return	array    Links for the given post
	 */
	protected function prepare_links( $post ) {
        $links  = parent::prepare_links( $post );
        $ticket = new KBS_Ticket( $post->post_parent );

		if ( ! empty( $ticket->ID ) )	{
			$links['agent'] = array(
				'href'       => rest_url( 'wp/v2/tickets/' . $ticket->ID ),
				'embeddable' => true,
			);
		}

		if ( ! empty( $ticket->agent ) )	{
            $links['agent'][] = array(
                'href'       => rest_url( 'wp/v2/users/' . $ticket->agent ),
                'embeddable' => true
            );
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

} // KBS_Replies_API
