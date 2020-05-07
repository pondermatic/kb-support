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
     * ID of user performing the API request.
     *
     * @since   1.5
     * @var     int
     */
    public $user_id = 0;

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
                'callback' => array( $this, 'process_request' )
            )
        );

        $ticket_routes['ticket']['number'] = array(
            'expression' => 'number=(?P<number>\d+)',
            'args' => array(
                'methods'  => 'GET',
                'callback' => array( $this, 'process_request' )
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


        return $request['id'];
    } // process_request

    /**
	 * Retrieve a user ID based on the public key provided
	 *
	 * @since  1.5
	 * @param  string  $key    Public Key
	 * @return bool    If user ID is found, false otherwise
	 */
	public function get_user( $key = '' ) {
		global $wpdb, $wp_query;

		if ( empty( $key ) ) {
			return false;
		}

		$user = get_transient( md5( 'kbs_api_user_' . $key ) );

		if ( false === $user ) {
            $user = $wpdb->get_var( $wpdb->prepare( "SELECT user_id FROM $wpdb->usermeta WHERE meta_key = %s LIMIT 1", $key ) );

			set_transient( md5( 'edd_api_user_' . $key ) , $user, DAY_IN_SECONDS );
		}

		if ( $user != NULL ) {
			$this->user_id = $user;
			return $user;
		}

		return false;
	} // get_user

    /**
	 * Retrieve a user's public key.
	 *
	 * @since  1.5
	 * @param  int     $user_id    User ID
	 * @return string  Users public key
	 */
	public function get_user_public_key( $user_id = 0 ) {
		global $wpdb;

		if ( empty( $user_id ) ) {
			return '';
		}

		$cache_key       = md5( 'kbs_api_user_public_key' . $user_id );
		$user_public_key = get_transient( $cache_key );

		if ( empty( $user_public_key ) ) {
            $user_public_key = $wpdb->get_var( $wpdb->prepare(
                "
                SELECT meta_key
                FROM $wpdb->usermeta
                WHERE meta_value = 'edd_user_public_key'
                AND user_id = %d
                ",
                $user_id
            ) );

            set_transient( $cache_key, $user_public_key, HOUR_IN_SECONDS );
		}

		return $user_public_key;
	} // get_user_public_key

    /**
	 * Retrieve the user's token
	 *
	 * @since  1.5
	 * @param  int     $user_id
	 * @return string
	 */
	public function get_token( $user_id = 0 ) {
		return hash( 'md5', $this->get_user_secret_key( $user_id ) . $this->get_user_public_key( $user_id ) );
	} // get_token

    /**
	 * Retrieve a user's secret key.
	 *
	 * @since  1.5
	 * @param  int     $user_id    User ID
	 * @return string  Users secret key
	 */
	public function get_user_secret_key( $user_id = 0 ) {
		global $wpdb;

		if ( empty( $user_id ) ) {
			return '';
		}
error_log( $user_id );
		$cache_key       = md5( 'kbs_api_user_secret_key' . $user_id );
		$user_secret_key = get_transient( $cache_key );

		if ( empty( $user_secret_key ) ) {
            $user_secret_key = $wpdb->get_var( $wpdb->prepare( "SELECT meta_key FROM $wpdb->usermeta WHERE meta_value = '%s' AND user_id = %d", 'kbs_user_secret_key', $user_id ) );

            set_transient( $cache_key, $user_secret_key, HOUR_IN_SECONDS );
		}

		return $user_secret_key;
	} // get_user_secret_key

} // KBS_API
