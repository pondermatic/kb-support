<?php
/**
 * Roles and Capabilities
 *
 * @package     KBS
 * @subpackage  Classes/Roles
 * @copyright   Copyright (c) 2016, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * KBS_Roles Class
 *
 * This class handles the role creation and assignment of capabilities for those roles.
 *
 * These roles let us have Support Agents, Support Customers, etc, each of whom can do
 * certain things within KBS Support
 *
 * @since	0,1
 */
class KBS_Roles {

	/**
	 * Get things going
	 *
	 * @since	0.1
	 */
	public function __construct() {
		add_filter( 'map_meta_cap', array( $this, 'meta_caps' ), 10, 4 );
	} // __construct

	/**
	 * Add new support roles with default WP caps
	 *
	 * @access	public
	 * @since	0.1
	 * @return	void
	 */
	public function add_roles() {
		add_role( 'support_manager', __( 'Support Manager', 'kb-support' ), array(
			'read'                   => true,
			'edit_posts'             => true,
			'delete_posts'           => true,
			'unfiltered_html'        => true,
			'upload_files'           => true,
			'export'                 => true,
			'import'                 => true,
			'delete_others_pages'    => true,
			'delete_others_posts'    => true,
			'delete_pages'           => true,
			'delete_private_pages'   => true,
			'delete_private_posts'   => true,
			'delete_published_pages' => true,
			'delete_published_posts' => true,
			'edit_others_pages'      => true,
			'edit_others_posts'      => true,
			'edit_pages'             => true,
			'edit_private_pages'     => true,
			'edit_private_posts'     => true,
			'edit_published_pages'   => true,
			'edit_published_posts'   => true,
			'manage_categories'      => true,
			'manage_links'           => true,
			'moderate_comments'      => true,
			'publish_pages'          => true,
			'publish_posts'          => true,
			'read_private_pages'     => true,
			'read_private_posts'     => true
		) );

		add_role( 'support_agent', __( 'Support Agent', 'kb-support' ), array(
			'read'                   => true,
			'edit_posts'             => false,
			'upload_files'           => true,
			'delete_posts'           => false
		) );

		add_role( 'support_customer', __( 'Support Customer', 'kb-support' ), array(
			'read'                   => true,
			'edit_posts'             => false,
			'upload_files'           => true,
			'delete_posts'           => false
		) );
	}

	/**
	 * Add new support-specific capabilities
	 *
	 * @access	public
	 * @since	0.1
	 * @global	WP_Roles $wp_roles
	 * @return	void
	 */
	public function add_caps() {

		global $wp_roles;

		if ( class_exists('WP_Roles') ) {
			if ( ! isset( $wp_roles ) ) {
				$wp_roles = new WP_Roles();
			}
		}

		if ( is_object( $wp_roles ) ) {
			$wp_roles->add_cap( 'support_manager', 'view_ticket_reports' );
			$wp_roles->add_cap( 'support_manager', 'view_ticket_sensitive_data' );
			$wp_roles->add_cap( 'support_manager', 'export_ticket_reports' );
			$wp_roles->add_cap( 'support_manager', 'manage_ticket_settings' );

			$wp_roles->add_cap( 'administrator', 'view_ticket_reports' );
			$wp_roles->add_cap( 'administrator', 'view_ticket_sensitive_data' );
			$wp_roles->add_cap( 'administrator', 'export_ticket_reports' );
			$wp_roles->add_cap( 'administrator', 'manage_ticket_settings' );

			// Add the main post type capabilities
			$capabilities = $this->get_core_caps();
			foreach ( $capabilities as $cap_group ) {
				foreach ( $cap_group as $cap ) {
					$wp_roles->add_cap( 'support_manager', $cap );
					$wp_roles->add_cap( 'administrator', $cap );
					$wp_roles->add_cap( 'support_agent', $cap );
				}
			}

			$wp_roles->add_cap( 'support_customer', 'edit_ticket' );
			$wp_roles->add_cap( 'support_customer', 'edit_tickets' );
			$wp_roles->add_cap( 'support_customer', 'delete_ticket' );
			$wp_roles->add_cap( 'support_customer', 'delete_tickets' );
			$wp_roles->add_cap( 'support_customer', 'publish_tickets' );
			$wp_roles->add_cap( 'support_customer', 'edit_published_tickets' );
			$wp_roles->add_cap( 'support_customer', 'upload_files' );
			$wp_roles->add_cap( 'support_customer', 'assign_ticket_terms' );
		}

	} // add_caps

	/**
	 * Gets the core post type capabilities
	 *
	 * @access	public
	 * @since	0.1
	 * @return	arr		$capabilities	Core post type capabilities
	 */
	public function get_core_caps() {

		$capabilities = array();

		$capability_types = array( 'ticket' );

		foreach ( $capability_types as $capability_type ) {
			$capabilities[ $capability_type ] = array(
				// Post type
				"edit_{$capability_type}",
				"read_{$capability_type}",
				"delete_{$capability_type}",
				"edit_{$capability_type}s",
				"edit_others_{$capability_type}s",
				"publish_{$capability_type}s",
				"read_private_{$capability_type}s",
				"delete_{$capability_type}s",
				"delete_private_{$capability_type}s",
				"delete_published_{$capability_type}s",
				"delete_others_{$capability_type}s",
				"edit_private_{$capability_type}s",
				"edit_published_{$capability_type}s",

				// Terms
				"manage_{$capability_type}_terms",
				"edit_{$capability_type}_terms",
				"delete_{$capability_type}_terms",
				"assign_{$capability_type}_terms",

				// Custom
				"view_{$capability_type}_stats"
			);
		}

		return $capabilities;

	} // get_core_caps

	/**
	 * Map meta caps to primitive caps
	 *
	 * @access	public
	 * @since	0.1
	 * @return	arr		$caps
	 */
	public function meta_caps( $caps, $cap, $user_id, $args ) {

		switch( $cap ) {

			case 'view_ticket_stats' :

				if( empty( $args[0] ) ) {
					break;
				}

				$ticket = get_post( $args[0] );
				if ( empty( $ticket ) ) {
					break;
				}

				if( user_can( $user_id, 'view_ticket_reports' ) || $user_id == $ticket->post_author ) {
					$caps = array();
				}

				break;
		}

		return $caps;

	} // meta_caps

	/**
	 * Remove core post type capabilities (called on uninstall)
	 *
	 * @access	public
	 * @since	0.1
	 * @return	void
	 */
	public function remove_caps() {

		global $wp_roles;

		if ( class_exists( 'WP_Roles' ) ) {
			if ( ! isset( $wp_roles ) ) {
				$wp_roles = new WP_Roles();
			}
		}

		if ( is_object( $wp_roles ) ) {
			/** Support Manager Capabilities */
			$wp_roles->remove_cap( 'support_manager', 'view_ticket_reports' );
			$wp_roles->remove_cap( 'support_manager', 'view_ticket_sensitive_data' );
			$wp_roles->remove_cap( 'support_manager', 'export_ticket_reports' );
			$wp_roles->remove_cap( 'support_manager', 'manage_ticket_settings' );

			/** Site Administrator Capabilities */
			$wp_roles->remove_cap( 'administrator', 'view_ticket_reports' );
			$wp_roles->remove_cap( 'administrator', 'view_ticket_sensitive_data' );
			$wp_roles->remove_cap( 'administrator', 'export_ticket_reports' );
			$wp_roles->remove_cap( 'administrator', 'manage_ticket_settings' );

			/** Remove the Main Post Type Capabilities */
			$capabilities = $this->get_core_caps();

			foreach ( $capabilities as $cap_group ) {
				foreach ( $cap_group as $cap ) {
					$wp_roles->remove_cap( 'support_manager', $cap );
					$wp_roles->remove_cap( 'administrator', $cap );
					$wp_roles->remove_cap( 'support_agent', $cap );
				}
			}

			/** Support Customer Capabilities */
			$wp_roles->remove_cap( 'support_customer', 'edit_ticket' );
			$wp_roles->remove_cap( 'support_customer', 'edit_tickets' );
			$wp_roles->remove_cap( 'support_customer', 'delete_ticket' );
			$wp_roles->remove_cap( 'support_customer', 'delete_tickets' );
			$wp_roles->remove_cap( 'support_customer', 'publish_tickets' );
			$wp_roles->remove_cap( 'support_customer', 'edit_published_tickets' );
			$wp_roles->remove_cap( 'support_customer', 'upload_files' );
		}

	} // remove_caps

} // KBS_Roles
