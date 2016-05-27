<?php
/**
 * KBS Form Class
 *
 * @package		KBS
 * @subpackage	Posts/Forms
 * @since		0.1
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * KBS_Form Class
 *
 * @since	0.1
 */
class KBS_Form {
	
	/**
	 * The form ID
	 *
	 * @since	0.1
	 */
	public $ID = 0;
		
	/**
	 * The form fields
	 *
	 * @since	0.1
	 */
	private $fields;
		
	/**
	 * Get things going
	 *
	 * @since	0.1
	 */
	public function __construct( $_id = false, $_args = array() ) {
		$form = WP_Post::get_instance( $_id );
				
		return $this->setup_form( $form, $_args );
				
	} // __construct
	
	/**
	 * Given the form data, let's set the variables
	 *
	 * @since	0.1
	 * @param 	obj		$form	The Form post object
	 * @param	arr		$args	Arguments passed to the class on instantiation
	 * @return	bool			If the setup was successful or not
	 */
	private function setup_form( $form, $args ) {
		
		if( ! is_object( $form ) ) {
			return false;
		}

		if( ! is_a( $form, 'WP_Post' ) ) {
			return false;
		}

		if( 'kbs_form' !== $form->post_type ) {
			return false;
		}
		
		foreach ( $form as $key => $value ) {
			switch ( $key ) {
				default:
					$this->$key = $value;
					break;
			}
		}

		$this->get_fields();
										
		return true;

	} // setup_form
	
	/**
	 * Magic __get function to dispatch a call to retrieve a private property
	 *
	 * @since	0.1
	 */
	public function __get( $key ) {
		
		if( method_exists( $this, 'get_' . $key ) ) {
			return call_user_func( array( $this, 'get_' . $key ) );
		} else {
			return new WP_Error( 'kbs-form-invalid-property', sprintf( __( "Can't get property %s", 'kb-support' ), $key ) );
		}
	} // __get

	/**
	 * Retrieve the ID
	 *
	 * @since	0.1
	 * @return	int
	 */
	public function get_ID() {
		return $this->ID;
	} // get_ID
	
	/**
	 * Retrieve the form shortcode.
	 *
	 * @since	0.1
	 * @return	str
	 */
	public function get_shortcode() {
		
		$shortcode = '[kbs_form id="' . $this->ID . '"]';
		
		return $shortcode;
	} // get_shortcode

	/**
	 * Adds a form field.
	 *
	 * @since	0.1
	 * @param	arr		$data	Field data.
	 * @return	mixed.
	 */
	public function add_field( $data )	{
		
		if ( ! isset( $data['form_id'], $data['label'], $data['type'] ) )	{
			return false;
		}

		do_action( 'kbs_pre_add_form_field', $data );
		
		$settings = array(
			'type'           => $data['type'],
			'required'       => ! empty( $data['required'] )       ? true                                     : false,
			'label_class'    => ! empty( $data['label_class'] )    ? $data['label_class']                     : '',
			'input_class'    => ! empty( $data['input_class'] )    ? $data['input_class']                     : '',
			'select_options' => ! empty( $data['select_options'] ) ? explode( "\n", $data['select_options'] ) : '',
			'selected'       => ! empty( $data['selected'] )       ? true                                     : false,
			'chosen'         => ! empty( $data['chosen'] )         ? true                                     : false,
			'placeholder'    => ! empty( $data['placeholder'] )    ? $data['placeholder']                     : '',
			'hide_label'     => ! empty( $data['hide_label'] )     ? true                                     : false
		);
		
		$settings = apply_filters( 'kbs_new_form_field_settings', $settings );
		
		$args = array(
			'post_type'    => 'kbs_form_field',
			'post_title'   => $data['label'],
			'post_content' => '',
			'post_status'  => 'publish',
			'post_parent'  => $data['form_id'],
			'menu_order'   => isset( $data['menu_order'] ) ? $data['menu_order'] : $this->get_next_order(),
			'meta_input'   => array( '_kbs_field_settings' => $settings )
		);
		
		$field_id = wp_insert_post( $args );
		
		do_action( 'kbs_post_add_form_field', $data );
		
		return $field_id;
		
	} // add_field
	
	/**
	 * Retrieve the form fields.
	 *
	 * @since	0.1
	 * @return	obj
	 */
	public function get_fields() {
		
		if( ! isset( $this->fields ) )	{

			$args = array(
				'posts_per_page' => -1,
				'post_type'      => 'kbs_form_field',
				'post_parent'    => $this->ID,
				'post_status'    => 'publish',
				'orderby'        => 'menu_order',
				'order'          => 'ASC'
			);
			
			$args = apply_filters( 'kbs_get_form_fields', $args );
			
			$this->fields = get_posts( $args );

		}
		
		return $this->fields;
	} // get_fields
	
	/*
	 * Get the next positional order for the field.
	 *
	 * @param
	 * @return	int		Field position.
	 */
	public function get_next_order()	{
		
		$args = array(
			'posts_per_page' => 1,
			'post_type'      => 'kbs_form_field',
			'post_parent'    => $this->ID,
			'post_status'    => 'publish',
			'orderby'        => 'menu_order',
			'order'          => 'DESC'
		);
		
		$order = 1;
		
		$fields = get_posts( $args );
		
		if ( $fields )	{
			if ( ! empty( $fields[0]->menu_order ) )	{
				$order = $fields[0]->menu_order;
				$order++;
			}
		}

		return (int) $order;
		
	} // get_next_order
		
	/*
	 * Get the field config
	 *
	 * @param	int		$field_id		The post ID of the field
	 * @return	arr		$field_settings	Array of field meta data
	 */
	public function get_field_settings( $field_id )	{
		
		$field_settings = get_post_meta( $field_id, '_kbs_field_settings', true );
		
		return $field_settings;
		
	} // get_field_settings
	
	/**
	 * Deletes a form or a form field.
	 *
	 * @since	0.1
	 * @param	int		$field_id	The field post ID.
	 * @return	mixed	Field post object if successful, otherwise false.
	 */
	public function delete( $field_id )	{
		return wp_delete_post( $field_id, true );
	} // delete

} // MDJM_DCF_Form