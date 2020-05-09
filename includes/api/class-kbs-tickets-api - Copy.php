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
	 * @var		array
	 */
	public $tickets = array();

	/**
	 * Get things going
	 *
	 * @since	1.5
	 */
	public function __construct()	{
		add_filter( 'kbs_api_routes', array( $this, 'tickets_route' ) );
	} // __construct

	/**
     * Get ticket routes
     *
     * @since   1.5
	 * @param	array	$routes		Array of api routes for kbs_tickets
     * @return  array   Array of api routes for kbs_tickets
     */
    public function tickets_route( $routes )    {
        $tickets_route = array();

		// Get ticket by Post ID
        $tickets_route['tickets'] = array(
            'expression' => '',
            'args' => array(
                'methods'  => 'GET',
                'callback' => array( $this, 'process_request' )
            )
        );

        $tickets_route = apply_filters( 'kbs_tickets_api_routes', $tickets_route );

        return array_merge( $tickets_route, $routes );
    } // tickets_route

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
	 * Format the response.
	 *
	 * @since	1.5
	 * @return	array
	 */
	public function format_response()	{
		return $this->format_ticket_response();
	} // format_response

} // KBS_Tickets_API
new KBS_Tickets_API();
