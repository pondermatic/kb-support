<?php
/**
 * KB Support REST API
 *
 * @package     KBS
 * @subpackage  Classes/REST API
 * @copyright   Copyright (c) 2020, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.5
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * KBS_API Class
 *
 * @since	1.0.8
 */
class KBS_API {
    /**
     * Namespace
     *
     * @since   1.5
     * @var     string
     */
    private $namespace = 'kbs/v1';

    /**
     * Routes
     *
     * @since   1.5
     * @var     array
     */
    private $routes = array();

    /**
	 * Get things going
	 *
	 * @since	1.5
	 */
	public function __construct()	{
		add_action( 'init',          array( $this, 'setup_routes' ), 11 );
        add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	} // __construct

    /**
     * Register routes.
     *
     * @since   1.5
     * @return  void
     */
    public function register_routes()  {
        foreach( $this->routes as $route_type => $route_data ) {
            foreach( $route_data as $_route => $data )  {
                error_log( '/' . $route_type . '/' . $data['expression'] );
                register_rest_route(
                    $this->namespace,
                    '/' . $route_type . '/' . $data['expression'],
                    $data['args']
                );
            }
        }
    } // register_routes

    /**
     * Define routes for the api
     *
     * @since   1.5
     * @return  void
     */
    public function setup_routes() {
        $this->routes = array_merge( $this->ticket_routes(), $this->routes );
    } // setup_routes

    /**
     * Get ticket routes
     *
     * @since   1.5
     * @return  array   Array of api routes for kbs_tickets
     */
    public function ticket_routes()    {
        // Get ticket by Post ID
        $ticket_routes = array();

        $ticket_routes['ticket']['id'] = array(
            'expression' => 'id=(?P<id>\d+)',
            'args' => array(
                'methods'  => 'GET',
                'callback' => array( $this, 'process_request' ),
                'auth'     => true  
            )
        );

        $ticket_routes['ticket']['number'] = array(
            'expression' => 'number=(?P<number>\d+)',
            'args' => array(
                'methods'  => 'GET',
                'callback' => array( $this, 'process_request' ),
                'auth'     => true  
            )
        );

        $ticket_routes = apply_filters( 'kbs_ticket_api_routes', $ticket_routes );

        return $ticket_routes;
    } // ticket_routes

    /**
     * Process a Rest API request
     *
     * @since   1.5
     * @param   array   $request    Array of request data received by api request
     * @return  mixed   Response to API request
     */
    public function process_request( $request ) {
        $requires_auth = array( 'ticket' );
        error_log( $request['id'] );
        error_log( var_export( $request, true ) );
        return $request['id'];
    } // process_request
    

} // KBS_API
