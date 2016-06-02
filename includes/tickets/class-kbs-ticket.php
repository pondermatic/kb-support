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
	 * Get things going
	 *
	 * @since	1.0
	 */
	public function __construct( $_id = false, $_args = array() ) {
		$ticket = WP_Post::get_instance( $_id );
				
		return $this->setup_ticket( $ticket, $_args );
				
	} // __construct
	
	/**
	 * Given the ticket data, let's set the variables
	 *
	 * @since	1.0
	 * @param 	obj		$form	The Ticket post object
	 * @param	arr		$args	Arguments passed to the class on instantiation
	 * @return	bool			If the setup was successful or not
	 */
	private function setup_ticket( $ticket, $args ) {
		
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

		$this->get_fields();
										
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
			return new WP_Error( 'kbs-form-invalid-property', sprintf( __( "Can't get property %s", 'kb-support' ), $key ) );
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
			'post_author'  => 1,
			'post_content' => '',
			'post_status'  => 'mdjm-enquiry',
			'post_title'   => sprintf( __( 'New %s', 'kb-support' ), kbs_get_ticket_label_singular() )
		);
		
		$default_meta = array(
			'_mdjm_event_date'               => date( 'Y-m-d' ),
			'_mdjm_event_dj'                 => ! mdjm_get_option( 'employer' ) ? 1 : 0,
			'_mdjm_event_playlist_access'    => mdjm_generate_playlist_guest_code(),
			'_mdjm_event_playlist'           => mdjm_get_option( 'enable_playlists' ) ? 'Y' : 'N',
			'_mdjm_event_contract'           => mdjm_get_default_event_contract(),
			'_mdjm_event_cost'               => 0,
			'_mdjm_event_deposit'            => 0,
			'_mdjm_event_deposit_status'     => __( 'Due', 'kb-support' ),
			'_mdjm_event_balance_status'     => __( 'Due', 'kb-support' ),
			'mdjm_event_type'                => mdjm_get_option( 'event_type_default' ),
			'mdjm_enquiry_source'            => mdjm_get_option( 'enquiry_source_default' )
		);

		$data = wp_parse_args( $data, $defaults );
		$meta = wp_parse_args( $meta, $default_meta );

		do_action( 'kbs_pre_create_ticket', $data, $meta );		

		$id	= wp_insert_post( $data, true );

		$ticket = WP_Post::get_instance( $id );

		if ( $ticket )	{
			
			if ( ! empty( $meta['mdjm_event_type'] ) )	{
				mdjm_set_event_type( $event->ID, $meta['mdjm_event_type'] );
				$meta['_mdjm_event_name'] = get_term( $meta['mdjm_event_type'], 'event-types' )->name;
				$meta['_mdjm_event_name'] = apply_filters( 'mdjm_event_name', $meta['_mdjm_event_name'], $id );
			}
			
			if ( ! empty( $meta['mdjm_enquiry_source'] ) )	{
				mdjm_set_enquiry_source( $event->ID, $meta['mdjm_enquiry_source'] );
			}
						
			if ( ! empty( $meta['_mdjm_event_start'] ) && ! empty( $meta['_mdjm_event_finish'] ) )	{
				
				if( date( 'H', strtotime( $meta['_mdjm_event_finish'] ) ) > date( 'H', strtotime( $meta['_mdjm_event_start'] ) ) )	{
					$meta['_mdjm_event_end_date'] = $meta['_mdjm_event_date'];
				} else	{
					$meta['_mdjm_event_end_date'] = date( 'Y-m-d', strtotime( '+1 day', strtotime( $meta['_mdjm_event_date'] ) ) );
				}
			}
			
			if ( ! empty( $meta['_mdjm_event_package'] ) )	{
				$meta['_mdjm_event_cost'] += mdjm_get_package_cost( $meta['_mdjm_event_package'] );
			}
			
			if ( ! empty( $meta['_mdjm_event_addons'] ) )	{
				foreach( $meta['_mdjm_event_addons'] as $addon )	{
					$meta['_mdjm_event_cost'] += mdjm_get_addon_cost( $addon );
				}
			}
			
			if ( empty( $meta['_mdjm_event_deposit'] ) )	{
				$meta['_mdjm_event_deposit'] = mdjm_calculate_deposit( $meta['_mdjm_event_cost'] );
			}
			
			mdjm_update_event_meta( $event->ID, $meta );
			
			wp_update_post(
				array(
					'ID'         => $id,
					'post_title' => mdjm_get_event_contract_id( $id ),
					'post_name'  => mdjm_get_event_contract_id( $id )
				)
			);
			
		}

		do_action( 'kbs_post_create_ticket', $id, $data );
		
		add_action( 'save_post_kbs_ticket', 'kbs_ticket_post_save', 10, 3 );

		return $this->setup_event( $ticket );

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

} // KBS_Ticket
