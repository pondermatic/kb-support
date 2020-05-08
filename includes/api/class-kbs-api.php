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
	 * The incoming request.
	 *
	 * @since	1.5
	 * @var		object array
	 */
	public $request;

    /**
	 * Get things going
	 *
	 * @since	1.5
	 */
	public function __construct()	{
		add_action( 'admin_init',	 array( $this, 'process_api_key' ) );

		add_action( 'init',          array( $this, 'setup_routes' ), 11 );
        add_action( 'rest_api_init', array( $this, 'register_routes' ) );

		$this->includes();
	} // __construct

	/**
	 * Include class files.
	 *
	 * @since	1.5
	 * @return	void
	 */
	public function includes()	{
		require_once KBS_PLUGIN_DIR . 'includes/api/class-kbs-ticket-api.php';

		do_action( 'kbs_api_includes' );
	} // includes

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
        $this->routes = apply_filters( 'kbs_api_routes', $this->routes );
    } // setup_routes

	/**
	 * Send the response.
	 *
	 * @since	1.5
	 * @return	mixed	API request response
	 */
	public function send_response()	{
		return $this->errors( 'no_response' );
	} // send_response

    /**
     * Process a Rest API request
     *
     * @since   1.5
     * @param   array   $request    Array of request data received by api request
     * @return  mixed   Response to API request
     */
    public function process_request( $request ) {
		if ( ! $this->is_authenticated( $request ) )	{
			return $this->errors( 'no_auth' );
		}

		$this->request = $request;

		return $this->send_response();
    } // process_request

	/**
	 * Format the response for tickets.
	 *
	 * @since	1.5
	 * @return	array
	 */
	public function format_ticket_response()	{
		$ticket = new KBS_Ticket( $this->ticket_id );

		if ( ! empty( $ticket->ID ) )	{
			$agent      = new KBS_Agent( $ticket->agent_id );
			$company    = new KBS_Company( $ticket->company_id );
			$agents     = array();
			$categories = array();
			$tags       = array();
			$files      = array();

			foreach( $ticket->agents as $_agent_id )	{
				$_agent = new KBS_Agent( $_agent_id );

				$agents[] = array(
					'user_id'      => $_agent_id,
					'first_name'   => $_agent ? $_agent->first_name : '',
					'last_name'    => $_agent ? $_agent->last_name : '',
					'display_name' => $_agent ? $_agent->name : '',
					'email'        => $_agent ? $_agent->email : '',
				);
			}

			$terms = wp_get_post_terms( $ticket->ID, 'ticket_category' );
			if ( $terms )	{
                foreach( $terms as $term )  {
                    $categories[] = array(
						'term_id' => $term->term_id,
						'slug'    => $term->slug,
						'name'    => $term->name
                    );
                }
			}

			$terms = wp_get_post_terms( $ticket->ID, 'ticket_tag' );
			if ( $terms )	{
                foreach( $terms as $term )  {
                    $tags[] = array(
						'term_id' => $term->term_id,
						'slug'    => $term->slug,
						'name'    => $term->name
                    );
                }
			}

			foreach( $ticket->files as $file )	{
				$files[] = array(
					'filename' => get_the_title( $file->ID ),
					'url'      => $file->guid
				);
			}

			$response = array(
				'ID'            => $ticket->ID,
				'number'        => $ticket->number,
				'key'           => $ticket->key,
				'status'        => $ticket->status_nicename,
				'date'          => $ticket->date,
				'modified_date' => $ticket->modified_date,
				'resolved_date' => $ticket->resolved_date,
				'categories'    => $categories,
				'tags'          => $tags,
				'agent'         => array(
					'user_id'      => $ticket->agent_id,
					'first_name'   => $agent ? $agent->first_name : '',
					'last_name'    => $agent ? $agent->last_name : '',
					'display_name' => $agent ? $agent->name : '',
					'email'        => $agent ? $agent->email : '',
				),
				'agents'        => $agents,
				'customer'      => array(
					'id'         => $ticket->customer_id,
					'first_name' => $ticket->first_name,
					'last_name'  => $ticket->last_name,
					'email'      => $ticket->email
				),
				'email'         => $ticket->email,
				'user_id'       => $ticket->user_id,
				'user_info'     => $ticket->user_info,
				'company'       => array(
					'id'      => $ticket->company_id,
					'name'    => $company ? $company->name : '',
					'contact' => $company ? $company->contact : '',
					'email'   => $company ? $company->email : '',
					'phone'   => $company ? $company->phone : '',
					'website' => $company ? $company->website : '',
					'logo'    => $company ? $company->logo : '',
				),
				'participants'  => $ticket->participants,
				'source'        => $ticket->get_source( 'name' ),
				'subject'       => $ticket->ticket_title,
				'content'       => $ticket->ticket_content,
				'attachments'   => $files
			);
		}

		return $response;
	} // format_response

	/**
	 * Whether or not the user is authenticated.
	 *
	 * @since	1.5
	 * @param	array	$data	Array of API request data
	 * @return	bool
	 */
	public function is_authenticated( $data )	{
		$token  = isset( $data['token'] ) ? urldecode( $data['token'] ) : false;
		$public = isset( $data['key'] )   ? urldecode( $data['key'] )   : false;

		if ( $token && $public && $this->get_user( $public ) )	{
			$secret = $this->get_user_secret_key( $this->user_id );

			return $this->check_keys( $secret, $public, $token );
		}

		return false;
	} // is_authenticated

	/**
	 * Respond with error.
	 *
	 * @since	1.5
	 * @param	string	$error	Error key
	 * @return	string	Error message
	 */
	public function errors( $error )	{
		$errors = array(
			'no_auth' => __( 'Authentication failed.', 'kb-support' )
		);

		$errors = apply_filters( 'kbs_api_errors', $errors );

		$errors['no_data'] = __( 'No data.', 'kb-support' );

		if ( array_key_exists( $error, $errors ) )	{
			return $errors[ $error ];
		}

		return $errors['no_data'];
	} // errors

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
            $user = $wpdb->get_var( $wpdb->prepare(
				"
				SELECT user_id
				FROM $wpdb->usermeta
				WHERE meta_key = %s
				LIMIT 1
				",
				$key
			) );

			set_transient( md5( 'kbs_api_user_' . $key ) , $user, DAY_IN_SECONDS );
		}

		if ( $user != NULL ) {
			$this->user_id = $user;
			return $user;
		}

		return false;
	} // get_user

	/**
	 * Check API keys vs token
	 *
	 * @since	1.5
	 * @param	string	$secret	Secret key
	 * @param	string	$public Public key
	 * @param	string	$token  Token used in API request
	 * @return	bool
	 */
	public function check_keys( $secret, $public, $token ) {
		return hash_equals( md5( $secret . $public ), $token );
	} // check_keys

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
                WHERE meta_value = 'kbs_user_public_key'
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

		$cache_key       = md5( 'kbs_api_user_secret_key' . $user_id );
		$user_secret_key = get_transient( $cache_key );

		if ( empty( $user_secret_key ) ) {
            $user_secret_key = $wpdb->get_var( $wpdb->prepare(
				"
				SELECT meta_key
				FROM $wpdb->usermeta
				WHERE meta_value = '%s'
				AND user_id = %d
				",
				'kbs_user_secret_key',
				$user_id
			) );

            set_transient( $cache_key, $user_secret_key, HOUR_IN_SECONDS );
		}

		return $user_secret_key;
	} // get_user_secret_key

	/**
	 * Process API key generation/revocation
	 *
	 * @since	1.5
	 * @return	void
	 */
	public function process_api_key() {
		if ( ! isset( $_REQUEST['kbs_action'] ) || 'process_api_key' != $_REQUEST['kbs_action'] )	{
			return;
		}

		if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'kbs-api-nonce' ) ) {
			wp_die(
				__( 'Nonce verification failed', 'kb-support' ),
				__( 'Error', 'kb-support' ),
				array( 'response' => 403 ) );
		}

		if ( empty( $_REQUEST['user_id'] ) ) {
			wp_die(
				__( 'User ID Required', 'kb-support' ),
				__( 'Error', 'kb-support' ),
				array( 'response' => 401 )
			);
		}

		if ( is_numeric( $_REQUEST['user_id'] ) ) {
			$user_id = isset( $_REQUEST['user_id'] ) ? absint( $_REQUEST['user_id'] ) : get_current_user_id();
		} else {
			$userdata = get_user_by( 'login', $_REQUEST['user_id'] );
			$user_id  = $userdata->ID;
		}

		$process = isset( $_REQUEST['kbs_api_process'] ) ? strtolower( $_REQUEST['kbs_api_process'] ) : false;

		if ( $user_id == get_current_user_id() && ! current_user_can( 'manage_ticket_settings' ) ) {
			wp_die( sprintf( __( 'You do not have permission to %s API keys for this user', 'kb-support' ), $process ), __( 'Error', 'kb-support' ), array( 'response' => 403 ) );
		} elseif ( ! current_user_can( 'manage_ticket_settings' ) ) {
			wp_die( sprintf( __( 'You do not have permission to %s API keys for this user', 'kb-support' ), $process ), __( 'Error', 'kb-support' ), array( 'response' => 403 ) );
		}

		switch ( $process ) {
			case 'generate':
				if ( $this->generate_api_key( $user_id ) ) {
					delete_transient( 'kbs-total-api-keys' );
					wp_safe_redirect( add_query_arg( array(
						'kbs-message' => 'api-key-generated',
						'post_type'   => 'kbs_ticket',
						'page'        => 'kbs-tools',
						'tab'         => 'api_keys'
						), admin_url( 'edit.php' )
					) );

					exit();
				} else {
					wp_safe_redirect( add_query_arg( array(
						'kbs-message' => 'api-key-failed',
						'post_type'   => 'kbs_ticket',
						'page'        => 'kbs-tools',
						'tab'         => 'api_keys'
						), admin_url( 'edit.php' )
					) );

					exit();
				}
				break;

			case 'regenerate':
				$this->generate_api_key( $user_id, true );
				delete_transient( 'kbs-total-api-keys' );
				wp_safe_redirect( add_query_arg( array(
					'kbs-message' => 'api-key-regenerated',
					'post_type'   => 'kbs_ticket',
					'page'        => 'kbs-tools',
					'tab'         => 'api_keys'
					), admin_url( 'edit.php' )
				) );

				exit();
				break;

			case 'revoke':
				$this->revoke_api_key( $user_id );
				delete_transient( 'kbs-total-api-keys' );
				wp_safe_redirect( add_query_arg( array(
					'kbs-message' => 'api-key-revoked',
					'post_type'   => 'kbs_ticket',
					'page'        => 'kbs-tools',
					'tab'         => 'api_keys'
					), admin_url( 'edit.php' )
				) );

				exit();
				break;

			default;
				break;
		}
	} // process_api_key

	/**
	 * Generate new API keys for a user
	 *
	 * @since	1.5
	 * @param	int		$user_id	User ID the key is being generated for
	 * @param	bool	$regenerate	Regenerate the key for the user
	 * @return	bool	True if (re)generated successfully, false otherwise
	 */
	public function generate_api_key( $user_id = 0, $regenerate = false ) {
		if ( empty( $user_id ) ) {
			return false;
		}

		$user = get_userdata( $user_id );

		if ( ! $user ) {
			return false;
		}

		$public_key = $this->get_user_public_key( $user_id );

		if ( empty( $public_key ) || $regenerate == true ) {
			$new_public_key = $this->generate_public_key( $user->user_email );
			$new_secret_key = $this->generate_private_key( $user->ID );
		} else {
			return false;
		}

		if ( $regenerate == true ) {
			$this->revoke_api_key( $user->ID );
		}

		update_user_meta( $user_id, $new_public_key, 'kbs_user_public_key' );
		update_user_meta( $user_id, $new_secret_key, 'kbs_user_secret_key' );

		return true;
	} // generate_api_key

	/**
	 * Revoke a users API keys
	 *
	 * @since	1.5
	 * @param	int		$user_id	User ID of user to revoke key for
	 * @return	string
	 */
	public function revoke_api_key( $user_id = 0 ) {
		if ( empty( $user_id ) ) {
			return false;
		}

		$user = get_userdata( $user_id );

		if ( ! $user ) {
			return false;
		}

		$public_key = $this->get_user_public_key( $user_id );
		$secret_key = $this->get_user_secret_key( $user_id );
		if ( ! empty( $public_key ) ) {
			delete_transient( md5( 'kbs_api_user_' . $public_key ) );
			delete_transient( md5( 'kbs_api_user_public_key' . $user_id ) );
			delete_transient( md5( 'kbs_api_user_secret_key' . $user_id ) );

			delete_user_meta( $user_id, $public_key );
			delete_user_meta( $user_id, $secret_key );
		} else {
			return false;
		}

		return true;
	} // revoke_api_key

	/**
	 * Generate and Save API key
	 *
	 * Generates the key requested by user_key_field and stores it in the database
	 *
	 * @since	1.5
	 * @param	int		$user_id	User ID
	 * @return void
	 */
	public function update_key( $user_id ) {
		kbs_update_user_api_key( $user_id );
	} // update_key

	/**
	 * Generate the public key for a user
	 *
	 * @since	1.5
	 * @param	string	$user_email		User email address
	 * @return	string
	 */
	public function generate_public_key( $user_email = '' ) {
		$auth_key = defined( 'AUTH_KEY' ) ? AUTH_KEY : '';
		$public   = hash( 'md5', $user_email . $auth_key . date( 'U' ) );

		return $public;
	} // generate_public_key

	/**
	 * Generate the secret key for a user
	 *
	 * @since	1.5
	 * @param	int		$user_id	User ID
	 * @return	string
	 */
	public function generate_private_key( $user_id = 0 ) {
		$auth_key = defined( 'AUTH_KEY' ) ? AUTH_KEY : '';
		$secret   = hash( 'md5', $user_id . $auth_key . date( 'U' ) );

		return $secret;
	} // generate_private_key

} // KBS_API
