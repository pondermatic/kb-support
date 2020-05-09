<?php
/**
 * KB Support REST API
 *
 * @package     KBS
 * @subpackage  Classes/Ticket REST API
 * @copyright   Copyright (c) 2020, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.5
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * KBS_Ticket_API Class
 *
 * @since	1.0.8
 */
class KBS_Ticket_API extends KBS_API {

	/**
	 * Ticket ID
	 *
	 * @since	1.5
	 * @var		int
	 */
	public $ticket_id = 0;

	/**
	 * Get things going
	 *
	 * @since	1.5
	 */
	public function __construct()	{
		add_filter( 'kbs_api_routes', array( $this, 'ticket_routes' ) );
	} // __construct

	/**
     * Get ticket routes
     *
     * @since   1.5
	 * @param	array	$routes		Array of api routes for kbs_tickets
     * @return  array   Array of api routes for kbs_tickets
     */
    public function ticket_routes( $routes )    {
        $ticket_routes = array();

		// Get ticket by Post ID
        $ticket_routes['ticket']['id'] = array(
            'expression' => 'id=(?P<id>\d+)',
            'args' => array(
                'methods'  => 'GET',
                'callback' => array( $this, 'process_request' )
            )
        );

		// Get ticket by number
        $ticket_routes['ticket']['number'] = array(
            'expression' => 'number=(?P<number>[a-zA-Z0-9-]+)',
            'args' => array(
                'methods'  => 'GET',
                'callback' => array( $this, 'process_request' )
            )
        );

        $ticket_routes = apply_filters( 'kbs_ticket_api_routes', $ticket_routes );

        return array_merge( $ticket_routes, $routes );
    } // ticket_routes

	/**
     * Send response to API request
     *
     * @since   1.5
     * @return  mixed   Response to API request
     */
    public function send_response() {
        $this->ticket_id = isset( $this->request['id'] ) ? $this->request['id'] : $this->get_ticket_by_number();

		if ( empty( $this->ticket_id ) )	{
			$this->errors( 'no_data' );
		}

		return $this->format_response();
    } // send_response

	/**
	 * Get ticket by number.
	 *
	 * @since	1.5
	 * @return	object	KBS_Ticket object or false
	 */
	public function get_ticket_by_number()	{
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
			$this->request['number']
		) );

		return $ticket_id;
	} // get_ticket_by_number

	/**
	 * Format the response.
	 *
	 * @since	1.5
	 * @return	array
	 */
	public function format_response()	{
		if ( ! kbs_agent_can_access_ticket( $this->ticket_id, $this->user_id ) )	{
			return $this->errors( 'no_permission' );
		}

		return $this->format_ticket_response();
	} // format_response

} // KBS_Ticket_API
new KBS_Ticket_API();
