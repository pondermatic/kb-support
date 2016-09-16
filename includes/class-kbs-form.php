<?php
/**
 * KBS Form Class
 *
 * @package		KBS
 * @subpackage	Posts/Forms
 * @since		1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * KBS_Form Class
 *
 * @since	1.0
 */
class KBS_Form {

	/**
	 * The form ID
	 *
	 * @since	1.0
	 */
	public $ID = 0;

	/**
	 * The form fields
	 *
	 * @since	1.0
	 */
	private $fields;

	/**
	 * Get things going
	 *
	 * @since	1.0
	 */
	public function __construct( $_id = false, $_args = array() ) {
		$form = WP_Post::get_instance( $_id );

		return $this->setup_form( $form, $_args );

	} // __construct

	/**
	 * Given the form data, let's set the variables
	 *
	 * @since	1.0
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
	 * Retrieve the ID
	 *
	 * @since	1.0
	 * @return	int
	 */
	public function get_ID() {
		return $this->ID;
	} // get_ID
	
	/**
	 * Retrieve the form shortcode.
	 *
	 * @since	1.0
	 * @return	str
	 */
	public function get_shortcode() {

		$shortcode = '[kbs_submit form="' . $this->ID . '"]';

		return $shortcode;
	} // get_shortcode

	/**
	 * Adds a form field.
	 *
	 * @since	1.0
	 * @param	arr		$data	Field data.
	 * @return	mixed.
	 */
	public function add_field( $data )	{

		if ( ! isset( $data['form_id'], $data['label'], $data['type'] ) )	{
			return false;
		}

		do_action( 'kbs_pre_add_form_field', $data );

		$settings = array(
			'type'            => $data['type'],
			'mapping'         => ! empty( $data['mapping'] )         ? $data['mapping']                            : '',
			'required'        => ! empty( $data['required'] )        ? true                                        : false,
			'label_class'     => ! empty( $data['label_class'] )     ? $data['label_class']                        : '',
			'input_class'     => ! empty( $data['input_class'] )     ? $data['input_class']                        : '',
			'select_options'  => ! empty( $data['select_options'] )  ? explode( "\n", $data['select_options'] )    : '',
			'select_multiple' => ! empty( $data['select_multiple'] ) ? true                                        : false,
			'selected'        => ! empty( $data['selected'] )        ? true                                        : false,
			'maxfiles'        => ! empty( $data['maxfiles'] )        ? $data['maxfiles']                           : false,
			'chosen'          => ! empty( $data['chosen'] )          ? true                                        : false,
			'description'     => ! empty( $data['description'] )     ? sanitize_text_field( $data['description'] ) : '',
			'placeholder'     => ! empty( $data['placeholder'] )     ? sanitize_text_field( $data['placeholder'] ) : '',
			'hide_label'      => ! empty( $data['hide_label'] )      ? true                                        : false
		);

		$settings = apply_filters( 'kbs_new_form_field_settings', $settings );

		$args = array(
			'post_type'    => 'kbs_form_field',
			'post_title'   => $data['label'],
			'post_name'    => 'kbs-' . sanitize_title( $data['label'] ),
			'post_content' => '',
			'post_status'  => 'publish',
			'post_parent'  => $data['form_id'],
			'menu_order'   => isset( $data['menu_order'] ) ? $data['menu_order'] : $this->get_next_order(),
			'meta_input'   => array( '_kbs_field_settings' => $settings )
		);

		$args = apply_filters( 'kbs_post_add_form_field_args', $args );

		$field_id = wp_insert_post( $args );

		do_action( 'kbs_post_add_form_field', $data );

		return $field_id;

	} // add_field

	/**
	 * Saves a form field.
	 *
	 * @since	1.0
	 * @param	arr		$data	Field data.
	 * @return	mixed.
	 */
	public function save_field( $data )	{

		if ( ! isset( $data['field_id'], $data['label'], $data['type'] ) )	{
			return false;
		}

		do_action( 'kbs_pre_save_form_field', $data );

		$settings = array(
			'type'            => $data['type'],
			'mapping'         => ! empty( $data['mapping'] )         ? $data['mapping']                            : '',
			'required'        => ! empty( $data['required'] )        ? true                                        : false,
			'label_class'     => ! empty( $data['label_class'] )     ? $data['label_class']                        : '',
			'input_class'     => ! empty( $data['input_class'] )     ? $data['input_class']                        : '',
			'select_options'  => ! empty( $data['select_options'] )  ? explode( "\n", $data['select_options'] )    : '',
			'select_multiple' => ! empty( $data['select_multiple'] ) ? true                                        : false,
			'selected'        => ! empty( $data['selected'] )        ? true                                        : false,
			'maxfiles'        => ! empty( $data['maxfiles'] )        ? $data['maxfiles']                           : false,
			'chosen'          => ! empty( $data['chosen'] )          ? true                                        : false,
			'description'     => ! empty( $data['description'] )     ? sanitize_text_field( $data['description'] ) : '',
			'placeholder'     => ! empty( $data['placeholder'] )     ? sanitize_text_field( $data['placeholder'] ) : '',
			'hide_label'      => ! empty( $data['hide_label'] )      ? true                                        : false
		);

		$args = array(
			'ID'           => $data['field_id'],
			'post_title'   => $data['label'],
			'post_name'    => 'kbs-' . sanitize_title( $data['label'] ),
			'post_content' => '',
			'meta_input'   => array( '_kbs_field_settings' => $settings )
		);

		$args = apply_filters( 'kbs_post_save_form_field_args', $args );

		$field_id = wp_update_post( $args );

		do_action( 'kbs_post_save_form_field', $data );

		return $field_id;

	} // save_field

	/**
	 * Determine if a mapping is in use.
	 *
	 * @since	1.0
	 * @param	str		The mapping to check.
	 * @return	str
	 */
	public function has_mapping( $mapping )	{
		foreach( $this->fields as $field )	{
			$settings = $this->get_field_settings( $field->ID );
			
			if ( ! empty( $settings ) && ! empty( $settings['mapping'] ) && $settings['mapping'] == $mapping )	{
				return true;
			}
		}

		return false;
	} // has_mapping

	/**
	 * Retrieve the form fields.
	 *
	 * @since	1.0
	 * @return	obj
	 */
	public function get_fields() {

		if ( ! isset( $this->fields ) )	{

			$args = array(
				'posts_per_page' => -1,
				'post_type'      => 'kbs_form_field',
				'post_parent'    => $this->ID,
				'post_status'    => 'publish',
				'orderby'        => 'menu_order',
				'order'          => 'ASC'
			);

			$args = apply_filters( 'kbs_get_fields', $args );

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
	 * Get the field settings
	 *
	 * @param	int		$field_id		The post ID of the field
	 * @return	arr		$field_settings	Array of field meta data
	 */
	public function get_field_settings( $field_id )	{

		$field_settings = get_post_meta( $field_id, '_kbs_field_settings', true );

		return apply_filters( 'kbs_field_settings', $field_settings, $field_id );

	} // get_field_settings

	/**
	 * Deletes a form.
	 *
	 * @since	1.0
	 * @param	int		$form_id	The form post ID.
	 * @return	mixed	Field post object if successful, otherwise false.
	 */
	public function delete_form( $form_id )	{

		if ( 'kbs_form' != get_post_type( $form_id ) )	{
			return false;
		}

		do_action( 'kbs_pre_delete_form', $form_id );

		foreach ( $this->fields as $field )	{
			$this->delete_field( $field->ID );
		}

		$result = wp_delete_post( $form_id, true );

		do_action( 'kbs_post_delete_form', $form_id, $result );

		return $result;
	} // delete_form

	/**
	 * Deletes a field.
	 *
	 * @since	1.0
	 * @param	int		$field_id	The field post ID.
	 * @return	mixed	Field post object if successful, otherwise false.
	 */
	public function delete_field( $field_id )	{
		if ( 'kbs_form_field' != get_post_type( $field_id ) )	{
			return false;
		}

		if ( ! kbs_can_delete_field( $field_id ) )	{
			return false;
		}

		do_action( 'kbs_pre_delete_field', $field_id );

		$result = wp_delete_post( $form_id, true );

		do_action( 'kbs_post_delete_field', $field_id );

		return $result;
	} // delete_field

	/**
	 * Displays a field.
	 *
	 * @since	1.0
	 * @param	obj		$field		The field post object.
	 * @param	arr		$settings	The field settings.
	 * @return	str		The field.
	 */
	public function display_field( $field, $settings )	{
		/*
		 * Output the field
		 * @since	1.0
		 */
		do_action( 'kbs_form_display_' . $settings['type'] . '_field', $field, $settings );
	} // display_field

	/**
	 * Get the submission count.
	 *
	 * @since	1.0
	 * @return	int		Submission count.
	 */
	public function get_submission_count()	{
		$submissions = get_post_meta( $this->ID, '_submission_count', true );

		if ( ! $submissions )	{
			$submissions = 0;
		}

		return $submissions;
	} // get_submission_count

	/**
	 * Increment the submission count.
	 *
	 * @since	1.0
	 * @return	void.
	 */
	public function increment_submissions()	{
		$submissions = $this->get_submission_count();
		$submissions++;

		update_post_meta( $this->ID, '_submission_count', $submissions );
	} // get_submission_count

} // MDJM_DCF_Form