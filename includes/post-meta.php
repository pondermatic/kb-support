<?php
/**
 * Post Taxonomy Functions
 *
 * @package     KBS
 * @subpackage  Functions
 * @copyright   Copyright (c) 2017, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.2
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;
/**
 * Retrieve the meta keys for this post type.
 *
 * Array format: key = meta_name, value = $args (see register_meta)
 *
 * @since	1.5
 * @return	array	Array of meta key parameters
 */
function kbs_get_ticket_reply_meta_fields()	{
    $object = get_post_type_object( 'kbs_ticket_reply' );

    $meta_fields = array(
        '_kbs_reply_customer_id' => array(
            'type'              => 'integer',
            'description'       => __( 'ID of the ticket customer.', 'kb-support' ),
            'single'            => true,
            'sanitize_callback' => 'absint',
            'auth_callback'     => function() {
                return kbs_can_view_customers();
            },
            'show_in_rest'      => array(
                'schema' => array(
                    'type'  => 'integer'
                )
            )
        ),
        '_kbs_reply_agent_id' => array(
            'type'              => 'integer',
            'description'       => __( 'ID of the ticket agent.', 'kb-support' ),
            'single'            => true,
            'auth_callback'     => function() {
                return kbs_can_view_customers();
            },
            'show_in_rest'      => array(
                'schema' => array(
                    'type'  => 'integer'
                )
            )
        ),
        '_kbs_reply_participant' => array(
            'type'              => 'string',
            'description'       => __( 'Participant email address.', 'kb-support' ),
            'single'            => true,
            'auth_callback'     => function() {
                return kbs_can_view_customers();
            },
            'show_in_rest'      => array(
                'schema' => array(
                    'type'  => 'string'
                )
            )
        ),
        '_kbs_reply_resolution' => array(
            'type'              => 'boolean',
            'description'       => sprintf(
                __( 'Whether or not this reply resolved the %s.', 'kb-support' ),
                kbs_get_ticket_label_singular( true )
            ),
            'single'            => true,
            'auth_callback'     => function() {
                return kbs_can_view_customers();
            },
            'show_in_rest'      => array(
                'schema' => array(
                    'type'  => 'boolean'
                )
            )
        )
    );

    $meta_fields = apply_filters( "kbs_kbs_ticket_reply_meta_fields", $meta_fields );

    return $meta_fields;
} // kbs_get_ticket_reply_meta_fields

/**
 * Retrieve the meta keys for forms.
 *
 * Array format: key = meta_name, value = $args (see register_meta)
 *
 * @since	1.5
 * @return	array	Array of meta key parameters
 */
function kbs_get_form_fields_meta_fields()	{
    $object = get_post_type_object( 'kbs_form_field' );

    $meta_fields = array(
        '_kbs_field_settings' => array(
            'type'              => 'array',
            'description'       => __( 'Form field settings.', 'kb-support' ),
            'single'            => true,
            'show_in_rest'      => array(
                'schema' => array(
                    'items' => array(
                        'type'       => 'array',
                        'properties' => array(
                            'blank' => array(
                                'type' => 'boolean'
                            ),
                            'chosen' => array(
                                'type' => 'boolean'
                            ),
                            'chosen_search' => array(
                                'type' => 'string'
                            ),
                            'description' => array(
                                'type' => 'string'
                            ),
                            'description_pos' => array(
                                'type' => 'string'
                            ),
                            'hide_label' => array(
                                'type' => 'boolean'
                            ),
                            'input_class' => array(
                                'type' => 'string'
                            ),
                            'label_class' => array(
                                'type' => 'string'
                            ),
                            'mapping' => array(
                                'type' => 'string'
                            ),
                            'kb_search' => array(
                                'type' => 'boolean'
                            ),
                            'placeholder' => array(
                                'type' => 'string'
                            ),
                            'required' => array(
                                'type' => 'boolean'
                            ),
                            'selected' => array(
                                'type' => 'boolean'
                            ),
                            'select_multiple' => array(
                                'type' => 'boolean'
                            ),
                            'select_options' => array(
                                'type' => 'array'
                            ),
                            'show_logged_in' => array(
                                'type' => 'boolean'
                            ),
                            'type' => array(
                                'type' => 'string'
                            ),
                            'value' => array(
                                'type' => 'string'
                            )
                        )
                    )
                )
            )
        )
    );

    $meta_fields = apply_filters( "kbs_kbs_form_field_meta_fields", $meta_fields );

    return $meta_fields;
} // kbs_get_form_fields_meta_fields

/**
 * Retrieve the meta keys for this post type.
 *
 * Array format: key = meta_name, value = $args (see register_meta)
 *
 * @since	1.5
 * @return	array	Array of meta key parameters
 */
function kbs_get_company_meta_fields()	{
    $object = get_post_type_object( 'kbs_company' );

    $meta_fields = array(
        '_kbs_company_customer' => array(
            'type'              => 'integer',
            'description'       => __( 'ID of the primary company customer contact.', 'kb-support' ),
            'single'            => true,
            'sanitize_callback' => 'absint',
            'auth_callback'     => function() {
                return kbs_can_view_customers();
            },
            'show_in_rest'      => array(
                'schema' => array(
                    'type'  => 'integer'
                )
            )
        ),
        '_kbs_company_contact' => array(
            'type'              => 'string',
            'description'       => __( 'Name of the primary company customer contact.', 'kb-support' ),
            'single'            => true,
            'auth_callback'     => function() {
                return kbs_can_view_customers();
            },
            'show_in_rest'      => array(
                'schema' => array(
                    'type'  => 'string'
                )
            )
        ),
        '_kbs_company_email' => array(
            'type'              => 'string',
            'description'       => __( 'Primary contact email address for the company.', 'kb-support' ),
            'single'            => true,
            'sanitize_callback' => 'is_email',
            'auth_callback'     => function() {
                return kbs_can_view_customers();
            },
            'show_in_rest'      => array(
                'schema' => array(
                    'type'  => 'string'
                )
            )
        ),
        '_kbs_company_phone' => array(
            'type'              => 'string',
            'description'       => __( 'Company phone number.', 'kb-support' ),
            'single'            => true,
            'auth_callback'     => function() {
                return kbs_can_view_customers();
            },
            'show_in_rest'      => array(
                'schema' => array(
                    'type'  => 'string'
                )
            )
        ),
        '_kbs_company_website' => array(
            'type'              => 'string',
            'description'       => __( 'Company web address.', 'kb-support' ),
            'single'            => true,
            'auth_callback'     => function() {
                return kbs_can_view_customers();
            },
            'show_in_rest'      => array(
                'schema' => array(
                    'type'  => 'string'
                )
            )
        )
    );

    $meta_fields = apply_filters( "kbs_kbs_company_meta_fields", $meta_fields );

    return $meta_fields;
} // kbs_get_company_meta_fields

/**
 * Register ticket reply meta fields.
 *
 * @since	1.5
 * @return	void
 */
function kbs_register_ticket_reply_meta()    {
    $fields = kbs_get_ticket_reply_meta_fields();

    foreach( $fields as $key => $args )	{
        register_post_meta( 'kbs_ticket_reply', $key, $args );
    }
} // kbs_register_ticket_reply_meta
add_action( 'init', 'kbs_register_ticket_reply_meta', 11 );

/**
 * Register form field meta fields.
 *
 * @since	1.5
 * @return	void
 */
function kbs_register_form_field_meta()    {
    $fields = kbs_get_form_fields_meta_fields();

    foreach( $fields as $key => $args )	{
        register_post_meta( 'kbs_form_field', $key, $args );
    }
} // kbs_register_form_field_meta
add_action( 'init', 'kbs_register_form_field_meta', 11 );

/**
 * Register company meta fields.
 *
 * @since	1.5
 * @return	void
 */
function kbs_register_company_meta()    {
    $fields = kbs_get_company_meta_fields();

    foreach( $fields as $key => $args )	{
        register_post_meta( 'kbs_company', $key, $args );
    }
} // kbs_register_company_meta
add_action( 'init', 'kbs_register_company_meta', 11 );
