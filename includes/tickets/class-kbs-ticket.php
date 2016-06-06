<?php
/**
 * KBS Ticket Class
 *
 * @package		KBS
 * @subpackage	Posts/Tickets
 * @since		1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * KBS_Ticket Class
 *
 * @since	1.0
 */
class KBS_Ticket {
	
	/**
	 * The ticket ID
	 *
	 * @since	1.0
	 */
	public $ID = 0;
	
	/**
	 * The ticket data
	 *
	 * @since	1.0
	 */
	public $data = array();
	
	/**
	 * The agent assigned to the ticket
	 *
	 * @since	1.0
	 */
	public $agent = 0;

	/**
	 * Declare the default properities in WP_Post as we can't extend it
	 * Anything we've delcared above has been removed.
	 */
	public $post_author = 0;
	public $post_date = '0000-00-00 00:00:00';
	public $post_date_gmt = '0000-00-00 00:00:00';
	public $post_content = '';
	public $post_title = '';
	public $post_excerpt = '';
	public $post_status = 'unassigned';
	public $comment_status = 'closed';
	public $ping_status = 'closed';
	public $post_password = '';
	public $post_name = '';
	public $to_ping = '';
	public $pinged = '';
	public $post_modified = '0000-00-00 00:00:00';
	public $post_modified_gmt = '0000-00-00 00:00:00';
	public $post_content_filtered = '';
	public $post_parent = 0;
	public $guid = '';
	public $menu_order = 0;
	public $post_mime_type = '';
	public $comment_count = 0;
	public $filter;
	
	/**
	 * Get things going
	 *
	 * @since	1.0
	 */
	public function __construct( $_id = false ) {
		$ticket = WP_Post::get_instance( $_id );
				
		return $this->setup_ticket( $ticket );
				
	} // __construct
	
	/**
	 * Given the ticket data, let's set the variables
	 *
	 * @since	1.0
	 * @param 	obj		$ticket	The Ticket post object
	 * @param	arr		$args	Arguments passed to the class on instantiation
	 * @return	bool			If the setup was successful or not
	 */
	private function setup_ticket( $ticket ) {
		
		if( ! is_object( $ticket ) ) {
			return false;
		}

		if( ! is_a( $ticket, 'WP_Post' ) ) {
			return false;
		}

		if( 'kbs_ticket' !== $ticket->post_type ) {
			return false;
		}
		
		foreach ( $ticket as $key => $value ) {
			switch ( $key ) {
				default:
					$this->$key = $value;
					break;
			}
		}
		
		$this->get_data();
		
		if ( $this->data )	{
			$this->get_agent();
		}
										
		return true;

	} // setup_ticket
	
	/**
	 * Magic __get function to dispatch a call to retrieve a private property
	 *
	 * @since	1.0
	 */
	public function __get( $key ) {
		
		if( method_exists( $this, 'get_' . $key ) ) {
			return call_user_func( array( $this, 'get_' . $key ) );
		} else {
			return new WP_Error( 'kbs-ticket-invalid-property', sprintf( __( "Can't get property %s", 'kb-support' ), $key ) );
		}
	} // __get

	/**
	 * Creates a ticket
	 *
	 * @since 	1.0
	 * @param 	arr		$data Array of attributes for a ticket. See $defaults aswell as wp_insert_post.
	 * @param 	arr		$meta Array of attributes for a ticket's meta data. See $default_meta.
	 * @return	mixed	false if data isn't passed and class not instantiated for creation, or New Ticket ID
	 */
	public function create( $data = array(), $meta = array() ) {

		if ( $this->id != 0 ) {
			return false;
		}

		add_action( 'save_post_kbs_ticket', 'kbs_ticket_post_save', 10, 3 );

		$defaults = array(
			'post_type'    => 'kbs_ticket',
			'post_author'  => is_user_logged_in() ? get_current_user_id() : 1,
			'post_content' => '',
			'post_status'  => 'unassigned',
			'post_title'   => sprintf( __( 'New %s', 'kb-support' ), kbs_get_ticket_label_singular() )
		);
		
		$default_meta = array(
			'__agent'              => is_admin() ? get_current_user_id() : 1,
			'__target_sla_respond' => kbs_calculate_sla_target_response(),
			'__target_sla_resolve' => kbs_calculate_sla_target_resolution(),
			'__source'             => 1
		);

		$data = wp_parse_args( $data, $defaults );
		$meta = wp_parse_args( $meta, $default_meta );
		
		$data['meta_input'] = array( '_ticket_data' => $meta );

		do_action( 'kbs_pre_create_ticket', $data, $meta );		

		$id	= wp_insert_post( $data, true );

		$ticket = WP_Post::get_instance( $id );

		do_action( 'kbs_post_create_ticket', $id, $data );
		
		add_action( 'save_post_kbs_ticket', 'kbs_ticket_post_save', 10, 3 );

		return $this->setup_ticket( $ticket );

	} // create

	/**
	 * Retrieve the ID
	 *
	 * @since	1.0
	 * @return	int
	 */
	public function get_ID() {
		return $this->ID;
	} // get_ID
	
	/**
	 * Retrieve the ticket data
	 *
	 * @since	1.0
	 * @return	int
	 */
	public function get_data() {
		if ( empty( $this->data ) )	{
			$this->data = get_post_meta( $this->ID, '_ticket_data', true );
		}
		
		return apply_filters( 'kbs_ticket_data', $this->data );
	} // get_data
	
	/**
	 * Retrieve the assigned agent ID.
	 *
	 * @since	1.0
	 * @return	int
	 */
	public function get_agent()	{	
		if ( empty( $this->agent ) )	{
			$this->agent = $this->data['__agent'];
		}
		
		return apply_filters( 'kbs_get_agent', $this->agent );
	} // get_agent
	
	/**
	 * Retrieve the target response time.
	 *
	 * @since	1.0
	 * @return	int
	 */
	public function get_target_respond() {
		$respond = date_i18n( get_option( 'time_format' ) . ' ' . get_option( 'date_format' ), strtotime( $this->data['__target_sla_respond'] ) );

		return apply_filters( 'kbs_get_target_respond', $respond );
	} // get_target_respond
	
	/**
	 * Retrieve the target resolution time.
	 *
	 * @since	1.0
	 * @return	int
	 */
	public function get_target_resolve() {
		$resolve = date_i18n( get_option( 'time_format' ) . ' ' . get_option( 'date_format' ), strtotime( $this->data['__target_sla_resolve'] ) );

		return apply_filters( 'kbs_get_target_resolve', $resolve );
	} // get_target_resolve
	
	/**
	 * Retrieve the source used for logging the ticket.
	 *
	 * @since	1.0
	 * @return	str
	 */
	public function get_source() {
		$sources = kbs_get_ticket_log_sources();
		
		$ticket_source = $this->data['__source'];
		
		if ( array_key_exists( $ticket_source, $sources ) )	{
			$return = $sources[ $ticket_source ];
		} else	{
			$return = __( 'Source could not be found', 'kb-support' );
		}
		
		return apply_filters( 'kbs_get_source', $return );
	} // get_source

} // KBS_Ticket
