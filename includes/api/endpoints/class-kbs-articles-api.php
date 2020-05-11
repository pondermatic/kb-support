<?php
/**
 * KB Support REST API
 *
 * @package     KBS
 * @subpackage  Classes/Articles REST API
 * @copyright   Copyright (c) 2020, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.5
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * KBS_Articles_API Class
 *
 * @since	1.5
 */
class KBS_Articles_API extends KBS_API {

	/**
	 * Post type.
	 *
	 * @since	1.5
	 * @var string
	 */
	protected $post_type;

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
							kbs_get_article_label_singular( true )
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
	 * Checks if the user can access password-protected content.
	 *
	 * This method determines whether we need to override the regular password
	 * check in core with a filter.
	 *
	 * @since	1.5
	 * @param	WP_Post			$post		Post to check against.
	 * @param	WP_REST_Request	$request	Request data to check.
	 * @return	bool			True if the user can access password-protected content, otherwise false.
	 */
	public function can_access_password_content( $post, $request ) {
		if ( empty( $post->post_password ) ) {
			// No filter required.
			return false;
		}

		// Edit context always gets access to password-protected posts.
		if ( 'edit' === $request['context'] ) {
			return true;
		}

		// No password, no auth.
		if ( empty( $request['password'] ) ) {
			return false;
		}

		// Double-check the request password.
		return hash_equals( $post->post_password, $request['password'] );
	} // can_access_password_content

	/**
	 * Retrieves a single article.
	 *
	 * @since	1.5
	 * @param	WP_REST_Request	$request	Full details about the request
	 * @return	WP_REST_Response|WP_Error	Response object on success, or WP_Error object on failure.
	 */
	public function get_item( $request ) {
        $article = $this->get_post( $request['id'] );
		if ( is_wp_error( $article ) ) {
			return $article;
		}

		if ( ! kbs_article_user_can_access( $article->ID ) )	{
			return new WP_Error(
				'rest_forbidden_context',
				$this->errors( 'restricted_article' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		$data     = $this->prepare_item_for_response( $article, $request );
		$response = rest_ensure_response( $data );

		return $response;
	} // get_item

	/**
     * Checks if a given request has access to read multiple articles.
     *
     * @since   1.5
     * @param	WP_REST_Request	$request	Full details about the request.
	 * @return	bool|WP_Error	True if the request has read access for the item, WP_Error object otherwise.
     */
    public function get_items_permissions_check( $request ) {
		return $this->get_item_permissions_check( $request );
    } // get_items_permissions_check

	/**
	 * Retrieves a collection of articles.
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
		$articles   = array();

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

        // Show restricted
        if ( ! empty( $request['restricted'] ) ) {
            $meta_query[] = array(
                'key'   => '_kbs_article_restricted',
                'value' => '1',
				'type'  => 'NUMERIC'
            );
        }

		// Force the post_type argument, since it's not a user input variable.
		$args['post_type'] = $this->post_type;

		if ( ! empty( $meta_query ) )	{
			$args['meta_query'] = $meta_query;
		}

		/**
		 * Filters the query arguments for a request.
		 *
		 * Enables adding extra arguments or setting defaults for an article collection request.
		 *
		 * @since	1.5
		 * @param	array			$args		Key value array of query var to query value
		 * @param	WP_REST_Request	$request	The request used
		 */
		$args           = apply_filters( "rest_{$this->post_type}_query", $args, $request );
		$query_args     = $this->prepare_items_query( $args, $request );

		$articles_query = new WP_Query();
		$query_result   = $articles_query->query( $query_args );

		foreach ( $query_result as $article ) {
			if ( ! $this->check_read_permission( $article ) || ! kbs_article_user_can_access( $article->ID ) ) {
				continue;
			}

			$data       = $this->prepare_item_for_response( $article, $request );
			$articles[] = $this->prepare_response_for_collection( $data );
		}

        $page           = (int) $query_args['paged'];
		$total_articles = $articles_query->found_posts;

		if ( $total_articles < 1 ) {
			// Out-of-bounds, run the query again without LIMIT for total count.
			unset( $query_args['paged'] );

			$count_query   = new WP_Query();
			$count_query->query( $query_args );
			$total_articles = $count_query->found_posts;
		}

		$max_pages = ceil( $total_articles / (int) $articles_query->query_vars['posts_per_page'] );

		if ( $page > $max_pages && $total_articles > 0 ) {
			return new WP_Error(
				'rest_post_invalid_page_number',
				__( 'The page number requested is larger than the number of pages available.' ),
				array( 'status' => 400 )
			);
		}

		$response = rest_ensure_response( $articles );

		$response->header( 'X-WP-Total', (int) $total_articles );
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
	 * Get the article post, if the ID is valid.
	 *
	 * @since	1.5
	 * @param	int				$id	Supplied ID.
	 * @return	WP_Post|WP_Error Post object if ID is valid, WP_Error otherwise.
	 */
	protected function get_post( $id ) {
		$error = new WP_Error(
			'rest_post_invalid_id',
			sprintf( __( 'Invalid %s ID.', 'kb-support' ), kbs_get_article_label_singular( true ) ),
			array( 'status' => 404 )
		);

		if ( (int) $id <= 0 ) {
			return $error;
		}

		$post = get_post( (int) $id );
		if ( empty( $post ) || empty( $post->ID ) || $this->post_type !== $post->post_type ) {
			return $error;
		}

		return $post;
	} // get_post

	/**
	 * Prepares a single article output for response.
	 *
	 * @since	1.5
	 * @param	WP_Post				$article	WP_Post Article object
	 * @param	WP_REST_Request		$request	Request object
	 * @return	WP_REST_Response	Response object
	 */
	public function prepare_item_for_response( $article, $request )	{
		$GLOBALS['post'] = $article;

		setup_postdata( $article );

		// Base fields for every post.
		$data = array();

		$data['id'] = $article->ID;

		if ( ! empty( $article->date ) ) {
			$data['date'] = $this->prepare_date_response( $article->post_date_gmt, $article->post_date );
		}

		if ( ! empty( $article->date_gmt ) ) {
			/*
			 * For drafts, `post_date_gmt` may not be set, indicating that the date
			 * of the draft should be updated each time it is saved (see #38883).
			 * In this case, shim the value based on the `post_date` field
			 * with the site's timezone offset applied.
			 */
			if ( '0000-00-00 00:00:00' === $article->post_date_gmt ) {
				$post_date_gmt = get_gmt_from_date( $article->post_date );
			} else {
				$post_date_gmt = $article->post_date_gmt;
			}
			$data['date_gmt'] = $this->prepare_date_response( $post_date_gmt );
		}

		if ( ! empty( $article->guid ) ) {
			$data['guid'] = array(
				/** This filter is documented in wp-includes/post-template.php */
				'rendered' => apply_filters( 'get_the_guid', $article->guid, $article->ID ),
				'raw'      => $article->guid,
			);
		}

		if ( ! empty( $article->modified ) ) {
			$data['modified'] = $this->prepare_date_response( $article->post_modified_gmt, $article->post_modified );
		}

		if ( ! empty( $article->modified_gmt ) ) {
			/*
			 * For drafts, `post_modified_gmt` may not be set (see `post_date_gmt` comments
			 * above). In this case, shim the value based on the `post_modified` field
			 * with the site's timezone offset applied.
			 */
			if ( '0000-00-00 00:00:00' === $article->post_modified_gmt ) {
				$post_modified_gmt = gmdate( 'Y-m-d H:i:s', strtotime( $article->post_modified ) - ( get_option( 'gmt_offset' ) * 3600 ) );
			} else {
				$post_modified_gmt = $article->post_modified_gmt;
			}
			$data['modified_gmt'] = $this->prepare_date_response( $post_modified_gmt );
		}

		if ( ! empty( $article->password ) ) {
			$data['password'] = $article->post_password;
		}

		if ( ! empty( $article->slug ) ) {
			$data['slug'] = $article->post_name;
		}

		if ( ! empty( $article->status ) ) {
			$data['status'] = $article->post_status;
		}

		if ( ! empty( $article->type ) ) {
			$data['type'] = $article->post_type;
		}

		if ( ! empty( $article->link ) ) {
			$data['link'] = get_permalink( $article->ID );
		}

		if ( ! empty( $article->title	 ) ) {
			$data['title'] = array();
		}
		if ( ! empty( $article->title ) ) {
			$data['title']['raw'] = $article->post_title;
		}
		if ( ! empty( $article->title ) ) {
			add_filter( 'protected_title_format', array( $this, 'protected_title_format' ) );

			$data['title']['rendered'] = get_the_title( $article->ID );

			remove_filter( 'protected_title_format', array( $this, 'protected_title_format' ) );
		}

		$has_password_filter = false;

		if ( $this->can_access_password_content( $article, $request ) ) {
			// Allow access to the post, permissions already checked before.
			add_filter( 'post_password_required', '__return_false' );

			$has_password_filter = true;
		}

		if ( ! empty( $article->post_content ) ) {
			$data['content'] = array();
		}
		if ( ! empty( $article->post_content ) ) {
			$data['content']['raw'] = $article->post_content;
		}
		if ( ! empty( $article->post_content ) ) {
			/** This filter is documented in wp-includes/post-template.php */
			$data['content']['rendered'] = post_password_required( $article ) ? '' : apply_filters( 'the_content', $article->post_content );
		}
		if ( ! empty( $article->post_content ) ) {
			$data['content']['block_version'] = block_version( $article->post_content );
		}

		if ( ! empty( $article->excerpt ) ) {
			/** This filter is documented in wp-includes/post-template.php */
			$excerpt = apply_filters( 'get_the_excerpt', $article->post_excerpt, $post );

			/** This filter is documented in wp-includes/post-template.php */
			$excerpt = apply_filters( 'the_excerpt', $excerpt );

			$data['excerpt'] = array(
				'raw'       => $article->post_excerpt,
				'rendered'  => post_password_required( $post ) ? '' : $excerpt,
				'protected' => (bool) $article->post_password,
			);
		}

		if ( $has_password_filter ) {
			// Reset filter.
			remove_filter( 'post_password_required', '__return_false' );
		}

		if ( ! empty( $article->author ) ) {
			$data['author'] = (int) $article->post_author;
		}

		if ( ! empty( $article->featured_media ) ) {
			$data['featured_media'] = (int) get_post_thumbnail_id( $article->ID );
		}

		if ( ! empty( $article->parent ) ) {
			$data['parent'] = (int) $article->post_parent;
		}

		if ( ! empty( $article->menu_order ) ) {
			$data['menu_order'] = (int) $article->menu_order;
		}

		if ( ! empty( $article->comment_status ) ) {
			$data['comment_status'] = $article->comment_status;
		}

		if ( ! empty( $article->ping_status ) ) {
			$data['ping_status'] = $article->ping_status;
		}

		if ( ! empty( $article->sticky ) ) {
			$data['sticky'] = is_sticky( $article->ID );
		}

		if ( ! empty( $article->template ) ) {
			$template = get_page_template_slug( $article->ID );
			if ( $template ) {
				$data['template'] = $template;
			} else {
				$data['template'] = '';
			}
		}

		if ( ! empty( $article->format ) ) {
			$data['format'] = get_post_format( $article->ID );

			// Fill in blank post format.
			if ( empty( $data['format'] ) ) {
				$data['format'] = 'standard';
			}
		}

		if ( ! empty( $article->meta ) ) {
			$data['meta'] = $this->meta->get_value( $article->ID, $request );
		}

		$taxonomies = wp_list_filter( get_object_taxonomies( $this->post_type, 'objects' ), array( 'show_in_rest' => true ) );

		foreach ( $taxonomies as $taxonomy ) {
			$base = ! empty( $taxonomy->rest_base ) ? $taxonomy->rest_base : $taxonomy->name;

			if ( ! empty( $request[ $base ] ) ) {
				$terms         = get_the_terms( $post, $taxonomy->name );
				$data[ $base ] = $terms ? array_values( wp_list_pluck( $terms, 'term_id' ) ) : array();
			}
		}

		$post_type_obj = get_post_type_object( $article->post_type );
		if ( is_post_type_viewable( $post_type_obj ) && $post_type_obj->public ) {
			$permalink_template_requested = ! empty( $article->permalink_template );
			$generated_slug_requested     = ! empty( $article->generated_slug );

			if ( $permalink_template_requested || $generated_slug_requested ) {
				if ( ! function_exists( 'get_sample_permalink' ) ) {
					require_once ABSPATH . 'wp-admin/includes/post.php';
				}

				$sample_permalink = get_sample_permalink( $article->ID, $article->post_title, '' );

				if ( $permalink_template_requested ) {
					$data['permalink_template'] = $sample_permalink[0];
				}

				if ( $generated_slug_requested ) {
					$data['generated_slug'] = $sample_permalink[1];
				}
			}
		}

		$context = ! empty( $article->context ) ? $article->context : 'view';

		$data['views']          = array();
		$data['views']['total'] = kbs_get_article_view_count( $article->ID );
		$data['views']['month'] = kbs_get_article_view_count( $article->ID, false );
		$data['is_restricted']  = kbs_article_is_restricted( $article->ID );

		// Wrap the data in a response object.
		$response = rest_ensure_response( $data );

		$links = $this->prepare_links( $article );
		$response->add_links( $links );

		if ( ! empty( $links['self']['href'] ) ) {
			$actions = $this->get_available_actions( $article, $request );

			$self = $links['self']['href'];

			foreach ( $actions as $rel ) {
				$response->add_link( $rel, $self );
			}
		}

		/**
		 * Filters the article data for a response.
		 *
		 * @since	1.5
		 *
		 * @param WP_REST_Response	$response	The response object
		 * @param WP_Post			$article	Post object
		 * @param WP_REST_Request	$request	Request object
		 */
		return apply_filters( "rest_prepare_{$this->post_type}", $response, $article, $request );
	} // prepare_item_for_response

	/**
	 * Retrieves the query params for the articles collection.
	 *
	 * @since	1.5
	 * @return	array	Collection parameters
	 */
	public function get_collection_params() {
		$singular     = kbs_get_article_label_singular();
		$plural       = kbs_get_article_label_plural();
		$query_params = parent::get_collection_params();

		$query_params['context']['default'] = 'view';

        $query_params['after'] = array(
			'description' => sprintf(
                __( 'Limit response to %s published after a given ISO8601 compliant date.', 'kb-support' ),
                strtolower( $plural )
            ),
			'type'        => 'string',
			'format'      => 'date-time'
		);

		if ( post_type_supports( $this->post_type, 'author' ) ) {
			$query_params['author']         = array(
				'description' => sprintf(
					__( 'Limit result set to %s assigned to specific authors.', 'kb-support' ),
					strtolower( $plural )
				),
				'type'        => 'array',
				'items'       => array(
					'type' => 'integer'
				),
				'default'     => array()
			);
			$query_params['author_exclude'] = array(
				'description' => sprintf(
					__( 'Ensure result set excludes %s assigned to specific authors.', 'kb-support' ),
					strtolower( $plural )
				),
				'type'        => 'array',
				'items'       => array(
					'type' => 'integer'
				),
				'default'     => array()
			);
		}

        $query_params['before'] = array(
			'description' => sprintf(
                __( 'Limit response to %s published before a given ISO8601 compliant date.', 'kb-support' ),
                strtolower( $plural )
            ),
			'type'        => 'string',
			'format'      => 'date-time'
		);

        $query_params['exclude'] = array(
			'description' => __( 'Ensure result set excludes specific IDs.', 'kb-support' ),
			'type'        => 'array',
			'items'       => array(
				'type' => 'integer'
			),
			'default'     => array()
		);

		$query_params['include'] = array(
			'description' => __( 'Limit result set to specific IDs.', 'kb-support' ),
			'type'        => 'array',
			'items'       => array(
				'type' => 'integer',
			),
			'default'     => array()
		);

		if ( 'page' === $this->post_type || post_type_supports( $this->post_type, 'page-attributes' ) ) {
			$query_params['menu_order'] = array(
				'description' => sprintf(
                __( 'Limit result set to %s with a specific menu_order value.', 'kb-support' ),
					strtolower( $plural )
				),
				'type'        => 'integer'
			);
		}

        $query_params['offset'] = array(
			'description' => __( 'Offset the result set by a specific number of items.', 'kb-support' ),
			'type'        => 'integer'
		);

        $query_params['order'] = array(
			'description' => __( 'Order sort attribute ascending or descending.', 'kb-support' ),
			'type'        => 'string',
			'default'     => 'desc',
			'enum'        => array( 'asc', 'desc' )
		);

		$query_params['orderby'] = array(
			'description' => __( 'Sort collection by object attribute.', 'kb-support' ),
			'type'        => 'string',
			'default'     => 'id',
			'enum'        => array(
				'author',
				'date',
				'id',
				'include',
				'modified',
				'parent',
				'relevance',
				'slug',
				'include_slugs',
				'title',
				'views',
				'views_month'
			)
		);

		if ( 'page' === $this->post_type || post_type_supports( $this->post_type, 'page-attributes' ) ) {
			$query_params['orderby']['enum'][] = 'menu_order';
		}

		$post_type = get_post_type_object( $this->post_type );

		if ( $post_type->hierarchical || 'attachment' === $this->post_type ) {
			$query_params['parent']         = array(
				'description' => __( 'Limit result set to items with particular parent IDs.', 'kb-support' ),
				'type'        => 'array',
				'items'       => array(
					'type' => 'integer',
				),
				'default'     => array(),
			);
			$query_params['parent_exclude'] = array(
				'description' => __( 'Limit result set to all items except those of a particular parent ID.', 'kb-support' ),
				'type'        => 'array',
				'items'       => array(
					'type' => 'integer',
				),
				'default'     => array(),
			);
		}

		$query_params['slug'] = array(
			'description'       => sprintf(
				__( 'Limit result set to posts with one or more specific slugs.', 'kb-support' ),
				strtolower( $plural )
			),
			'type'              => 'array',
			'items'             => array(
				'type' => 'string',
			),
			'sanitize_callback' => 'wp_parse_slug_list',
		);

		$query_params['status'] = array(
			'default'     => 'publish',
			'description' => sprintf(
				__( 'Limit result set to %s assigned one or more statuses.', 'kb-support' ),
				strtolower( $singular )
			),
			'type'        => 'array',
			'items'       => array(
				'enum' => array_merge( array_keys( get_post_stati() ), array( 'any' ) ),
				'type' => 'string'
			)
		);

		$query_params['restricted'] = array(
			'default'     => 'null',
			'description' => sprintf(
				__( 'Limit result set to restricted %s.', 'kb-support' ),
				strtolower( $plural )
			)
		);

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
	 * Checks if a given post type can be viewed or managed.
	 *
	 * @since	1.5
	 *
	 * @param	WP_Post_Type|string	$post_type	Post type name or object.
	 * @return	bool				Whether the post type is allowed in REST.
	 */
	protected function check_is_post_type_allowed( $post_type ) {
		if ( ! is_object( $post_type ) ) {
			$post_type = get_post_type_object( $post_type );
		}

		if ( ! empty( $post_type ) && ! empty( $post_type->show_in_rest ) ) {
			return true;
		}

		return false;
	} // check_is_post_type_allowed

	/**
	 * Checks if an article can be read.
	 *
	 * @since	1.5
	 * @param	object	KBS_Article object
	 * @return	bool	Whether the post can be read.
	 */
	public function check_read_permission( $article )	{
		$post_type = get_post_type_object( $article->post_type );

		if ( ! $this->check_is_post_type_allowed( $post_type ) ) {
			return false;
		}

		// Is the post readable?
		if ( 'publish' === $article->post_status || current_user_can( $post_type->cap->read_post, $article->ID ) ) {
			return true;
		}

		$post_status_obj = get_post_status_object( $article->post_status );
		if ( $post_status_obj && $post_status_obj->public ) {
			return true;
		}

		// Can we read the parent if we're inheriting?
		if ( 'inherit' === $article->post_status && $article->post_parent > 0 ) {
			$parent = get_post( $article->post_parent );
			if ( $parent ) {
				return $this->check_read_permission( $parent );
			}
		}

		/*
		 * If there isn't a parent, but the status is set to inherit, assume
		 * it's published (as per get_post_status()).
		 */
		if ( 'inherit' === $article->post_status ) {
			return true;
		}

		return false;
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

		if ( isset( $request['orderby'] ) )	{
			if ( 'views' == $request['orderby'] || 'views_month' == $request['orderby'] )	{
				if ( 'views' == $request['orderby'] )	{
					$views_key = kbs_get_article_view_count_meta_key_name();
				} else	{
					$views_key = kbs_get_article_view_count_meta_key_name( false );
				}

				$query_args['meta_key'] = $views_key;
				$query_args['orderby']  = 'meta_value_num';
			}
				
		}

		return $query_args;
	} // prepare_items_query

	/**
	 * Checks the post_date_gmt or modified_gmt and prepare any post or
	 * modified date for single post output.
	 *
	 * @since	1.5
	 *
	 * @param	string		$date_gmt	GMT publication time
	 * @param	string|null	$date		Optional. Local publication time. Default null
	 * @return	string|null	ISO8601/RFC3339 formatted datetime
	 */
	protected function prepare_date_response( $date_gmt, $date = null ) {
		// Use the date if passed.
		if ( isset( $date ) ) {
			return mysql_to_rfc3339( $date );
		}

		// Return null if $date_gmt is empty/zeros.
		if ( '0000-00-00 00:00:00' === $date_gmt ) {
			return null;
		}

		// Return the formatted datetime.
		return mysql_to_rfc3339( $date_gmt );
	} // prepare_date_response

	/**
	 * Prepares links for the request.
	 *
	 * @since	1.5
	 * @param	WP_Post		$article		WP_Post object
	 * @return	array		Links for the given post
	 */
	protected function prepare_links( $article ) {
		$base = sprintf( '%s/%s', $this->namespace . $this->version, $this->rest_base );

		// Entity meta.
		$links = array(
			'self'       => array(
				'href' => rest_url( trailingslashit( $base ) . $article->ID ),
			),
			'collection' => array(
				'href' => rest_url( $base ),
			)
		);

		return $links;
	} // prepare_links

	/**
	 * Get the link relations available for the post and current user.
	 *
	 * @since	1.5
	 * @param	WP_Post			$post		Post object
	 * @param	WP_REST_Request	$request	Request object
	 * @return	array			List of link relations
	 */
	protected function get_available_actions( $post, $request ) {

		if ( 'edit' !== $request['context'] ) {
			return array();
		}

		$rels = array();

		$post_type = get_post_type_object( $post->post_type );

		if ( 'attachment' !== $this->post_type && current_user_can( $post_type->cap->publish_posts ) ) {
			$rels[] = 'https://api.w.org/action-publish';
		}

		if ( current_user_can( 'unfiltered_html' ) ) {
			$rels[] = 'https://api.w.org/action-unfiltered-html';
		}

		if ( 'post' === $post_type->name ) {
			if ( current_user_can( $post_type->cap->edit_others_posts ) && current_user_can( $post_type->cap->publish_posts ) ) {
				$rels[] = 'https://api.w.org/action-sticky';
			}
		}

		if ( post_type_supports( $post_type->name, 'author' ) ) {
			if ( current_user_can( $post_type->cap->edit_others_posts ) ) {
				$rels[] = 'https://api.w.org/action-assign-author';
			}
		}

		$taxonomies = wp_list_filter( get_object_taxonomies( $this->post_type, 'objects' ), array( 'show_in_rest' => true ) );

		foreach ( $taxonomies as $tax ) {
			$tax_base   = ! empty( $tax->rest_base ) ? $tax->rest_base : $tax->name;
			$create_cap = is_taxonomy_hierarchical( $tax->name ) ? $tax->cap->edit_terms : $tax->cap->assign_terms;

			if ( current_user_can( $create_cap ) ) {
				$rels[] = 'https://api.w.org/action-create-' . $tax_base;
			}

			if ( current_user_can( $tax->cap->assign_terms ) ) {
				$rels[] = 'https://api.w.org/action-assign-' . $tax_base;
			}
		}

		return $rels;
	} // get_available_actions

} // KBS_Articles_API
