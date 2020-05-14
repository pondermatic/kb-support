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
class KBS_API extends WP_REST_Controller {
	/**
	 * Version
	 *
	 * @since	1.5
	 * @var		int
	 */
	protected $version = '1';
    /**
     * Namespace
     *
     * @since   1.5
     * @var     string
     */
    protected $namespace = 'kbs/v';

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
		add_action( 'admin_init',	 array( $this, 'process_api_key' ) );
	} // __construct

	/**
	 * Magic GET function.
	 *
	 * @since	1.5
	 * @param	string	$key	The property to retrieve
	 * @return	mixed	The value retrieved
	 */
	public function __get( $key ) {
		if ( method_exists( $this, 'get_' . $key ) ) {
			$value = call_user_func( array( $this, 'get_' . $key ) );
		} else {
			$value = $this->$key;
		}

		return $value;
	} // __get

    /**
     * Checks if a given request has access to read an object.
     *
     * @since   1.5
     * @param	WP_REST_Request	$request	Full details about the request
	 * @return	bool|WP_Error	True if the request has read access for the item, WP_Error object otherwise
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
	 * Get the post, if the ID is valid.
	 *
	 * @since	1.5
	 * @param	int					$id	Supplied ID.
	 * @return	WP_Post|WP_Error	Post object if ID is valid, WP_Error otherwise.
	 */
	protected function get_post( $id ) {
		$error = new WP_Error(
			'rest_post_invalid_id',
			__( 'Invalid post ID.', 'kb-support' ),
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
	 * Checks the post_date_gmt or modified_gmt and prepare any post or
	 * modified date for single post output.
	 *
	 * @since	1.5
	 *
	 * @param	string		$date_gmt	GMT publication time.
	 * @param	string|null	$date		Optional. Local publication time. Default null.
	 * @return	string|null	ISO8601/RFC3339 formatted datetime.
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
	 * Log in and validate the user.
	 *
	 * @since	1.5
	 * @return	bool	Whether or not the user is logged in
	 */
	function validate_user()	{
		if ( ! is_user_logged_in() || $this->user_id != $this->user_id )	{
			wp_clear_auth_cookie();
			wp_set_current_user ( $this->user_id );
			wp_set_auth_cookie  ( $this->user_id );
		}

		return is_user_logged_in();
	} // validate_user

	/**
	 * Whether or not the user is authenticated.
	 *
	 * @since	1.5
	 * @param	array	$data	Array of API request data
	 * @return	bool
	 */
	public function is_authenticated( $data )	{
        if ( is_user_logged_in() )  {
            return true;
        } else  {
            $token  = isset( $data['token'] ) ? urldecode( $data['token'] ) : false;
            $public = isset( $data['key'] )   ? urldecode( $data['key'] )   : false;

            if ( $token && $public && $this->get_user( $public ) )	{
                $secret = $this->get_user_secret_key( $this->user_id );

                return $this->check_keys( $secret, $public, $token );
            }
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
			'no_auth'            => __( 'Authentication failed.', 'kb-support' ),
			'no_permission'      => __( 'Access denied.', 'kb-support' ),
			'restricted_article' => sprintf(
				__( 'Criteria not met to access restricted %s.', 'kb-support' ),
				kbs_get_article_label_singular( true )
			)
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
