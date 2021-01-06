<?php
/**
 * KB Support REST API
 *
 * @package     KBS
 * @subpackage  Classes/Companies REST API
 * @copyright   Copyright (c) 2020, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.5
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * KBS_Companies_API Class
 *
 * @since	1.5
 */
class KBS_Companies_API extends WP_REST_Posts_Controller {

	/**
	 * Get things going
	 *
	 * @since	1.5
	 */
	public function __construct( $post_type )	{
		parent::__construct( $post_type );

		$this->namespace = KBS()->api->__get( 'namespace' ) . KBS()->api->__get( 'version' );
	} // __construct

	/**
     * Checks if a given request has access to read a company.
     *
     * @since   1.5
     * @param	WP_REST_Request	$request	Full details about the request.
	 * @return	bool|WP_Error	True if the request has read access for the item, WP_Error object otherwise.
     */
    public function get_item_permissions_check( $request ) {
		if ( ! KBS()->api->is_authenticated() )	{
			return new WP_Error(
				'rest_forbidden_context',
				KBS()->api->errors( 'no_auth' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		return kbs_can_view_customers( KBS()->api->user_id );
    } // get_item_permissions_check

	/**
     * Checks if a given request has access to read multiple companies.
     *
     * @since   1.5
     * @param	WP_REST_Request	$request	Full details about the request.
	 * @return	bool|WP_Error	True if the request has read access for the item, WP_Error object otherwise.
     */
    public function get_items_permissions_check( $request ) {
		return $this->get_item_permissions_check( $request );
    } // get_items_permissions_check

	/**
	 * Retrieves the query params for the posts collection.
	 *
	 * @since	1.5
	 * @return	array	Collection parameters
	 */
	public function get_collection_params() {
		$query_params = parent::get_collection_params();

		$query_params['context']['default'] = 'view';

		$query_params['orderby'] = array(
			'description' => __( 'Sort collection by object attribute.' ),
			'type'        => 'string',
			'default'     => 'title',
			'enum'        => array(
				'customer',
				'id',
				'include',
				'title'
			),
		);

		$post_type = get_post_type_object( $this->post_type );

		/**
		 * Filter collection parameters for the companies controller.
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
	 * Checks if a company can be read.
	 *
	 * @since	1.5
	 * @param	object	KBS_Company object
	 * @return	bool	Whether the company can be read.
	 */
	public function check_read_permission( $company )	{
		return kbs_can_view_customers( KBS()->api->user_id );
	} // check_read_permission

	/**
	 * Prepares links for the request.
	 *
	 * @since  1.5
	 * @param  WP_Post $post   Post object.
	 * @return array	Links for the given company
	 */
	protected function prepare_links( $post ) {
        $links = parent::prepare_links( $post );

        $customer = get_post_meta( $post->ID, '_kbs_company_customer', true );

		if ( ! empty( $customer ) )	{
			$links['customer'] = array(
				'href'       => rest_url( 'kbs/v1/customers/' . $customer ),
				'embeddable' => true
			);
		}

		$links[ kbs_get_ticket_label_plural( true ) ] = array(
			'href'       => rest_url( 'kbs/v1/tickets/?company=' . $post->ID ),
			'embeddable' => true
		);

		return $links;
	} // prepare_links

} // KBS_Companies_API
