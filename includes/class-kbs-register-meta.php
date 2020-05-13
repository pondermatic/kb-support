<?php
/**
 *
 * This class is for registering our meta
 *
 * @package     KBS
 * @subpackage  Classes/Register Meta
 * @copyright   Copyright (c) 2020, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.5
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * KBS_Register_Meta Class
 *
 * @since	1.5
 */
class KBS_Register_Meta {

	private static $instance;

	/**
	 * Setup the post meta registration
	 *
	 * @since	1.5
	 */
	private function __construct() {
		$this->hooks();
	} // __construct

	/**
	 * Get the one true instance of KBS_Register_Meta.
	 *
	 * @since	1.5
	 * @return	$instance
	 */
	static public function instance() {
		if ( !self::$instance ) {
			self::$instance = new KBS_Register_Meta();
		}

		return self::$instance;
	} // instance

	/**
	 * Register the hooks to kick off meta registration.
	 *
	 * @since  2.5
	 * @return void
	 */
	private function hooks() {
        add_action( 'init', array( $this, 'register_companies_meta' ) );
    } // hooks

    /**
     * Register meta fields.
     *
     * @since   1.5
     * @param   string  $post_type      Post type to which the meta belongs
     * @param   array   $meta_fields    Array of fields to register
     * @return  void
     */
    public function register_post_meta( $post_type, $meta_fields )  {
        foreach( $meta_fields as $key => $args )	{
			register_post_meta( $post_type, $key, $args );
		}
    } // register_post_meta

    /**
     * Register kbs_company meta fields.
     *
     * @since   1.5
     * @return  void
     */
    public function register_companies_meta()   {
        $object = get_post_type_object( 'kbs_company' );
		
		$meta_fields = array(
			'_kbs_company_customer' => array(
				'type'              => 'integer',
				'description'       => __( 'KBS ID of Customer who is the primary company contact.', 'kb-support' ),
				'single'            => true,
				'sanitize_callback' => 'absint',
				'auth_callback'     => function() {
					return current_user_can( "edit_{$object->name}" );
				},
				'show_in_rest'      => true
			),
            '_kbs_company_contact' => array(
				'type'              => 'string',
				'description'       => __( 'Name of customer who is the primary company contact.', 'kb-support' ),
				'single'            => true,
				'sanitize_callback' => 'sanitize_text_field',
				'auth_callback'     => function() {
					return current_user_can( "edit_{$object->name}" );
				},
				'show_in_rest'      => true
			),
            '_kbs_company_email' => array(
				'type'              => 'string',
				'description'       => __( 'Email of customer who is the primary company contact.', 'kb-support' ),
				'single'            => true,
				'sanitize_callback' => 'sanitize_email',
				'auth_callback'     => function() {
					return current_user_can( "edit_{$object->name}" );
				},
				'show_in_rest'      => true
			),
            '_kbs_company_phone' => array(
				'type'              => 'string',
				'description'       => __( 'Phone number for company.', 'kb-support' ),
				'single'            => true,
				'sanitize_callback' => 'sanitize_text_field',
				'auth_callback'     => function() {
					return current_user_can( "edit_{$object->name}" );
				},
				'show_in_rest'      => true
			),
            '_kbs_company_website' => array(
				'type'              => 'string',
				'description'       => __( 'Web address for company.', 'kb-support' ),
				'single'            => true,
				'sanitize_callback' => 'esc_url_raw',
				'auth_callback'     => function() {
					return current_user_can( "edit_{$object->name}" );
				},
				'show_in_rest'      => true
			)
		);

		$meta_fields = apply_filters( "kbs_register_company_meta_fields", $meta_fields );

        $this->register_post_meta( 'kbs_company', $meta_fields );
    } // register_companies_meta

	/**
	 * Sanitize an int array.
	 *
	 * @since	1.5
	 * @param	array	$value	The value passed into the meta
	 * @return	array	The sanitized value
	 */
	public function sanitize_int_array( $value )	{
		$value = $this->sanitize_array( $value );
		$value = array_map( 'absint', $value );

		return $value;
	} // sanitize_int_array

	/**
	 * Sanitize a general array.
	 *
	 * @since	1.5
	 * @param	array	$value	The value passed into the meta
	 * @return	array	The sanitized value
	 */
	public function sanitize_array( $value )	{
		if ( ! is_array( $value ) ) {
			if ( is_object( $value ) ) {
				$value = (array) $value;
			}

			if ( is_serialized( $value ) ) {
				preg_match( '/[oO]\s*:\s*\d+\s*:\s*"\s*(?!(?i)(stdClass))/', $value, $matches );
				if ( ! empty( $matches ) ) {
					return false;
				}

				$value = (array) maybe_unserialize( $value );
			}
		}

		return $value;
	} // sanitize_array
} // KBS_Register_Meta

KBS_Register_Meta::instance();
