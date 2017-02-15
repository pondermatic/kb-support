<?php


/**
 * @group kbs_activation
 */
class Tests_Activation extends KBS_UnitTestCase {

	/**
	 * SetUp test class.
	 *
	 * @since 2.1.0
	 */
	public function setUp() {
		parent::setUp();
	}

	/**
	 * Test if the global settings are set and have settings pages.
	 *
	 * @since	1.0
	 */
	public function test_settings() {
		global $kbs_options;
		$this->assertArrayHasKey( 'submission_page', $kbs_options );
		$this->assertArrayHasKey( 'tickets_page', $kbs_options );
	}

	/**
	 * Test the install function, installing pages and setting option values.
	 *
	 * @since	1.0
	 */
	public function test_install() {

		global $kbs_options;

		$origin_kbs_options   = $kbs_options;
		$origin_upgraded_from = get_option( 'kbs_version_upgraded_from' );
		$origin_kbs_version   = get_option( 'kbs_version' );

		// Prepare values for testing
		delete_option( 'kbs_settings' ); // Needed for the install test to succeed
		update_option( 'kbs_version', '1.0' );
		$kbs_options = array();

		kbs_install();

		// Test that new pages are created, and not the same as the already created ones.
		// This is to make sure the test is giving the most accurate results.
		$new_settings = get_option( 'kbs_settings' );

		$this->assertArrayHasKey( 'submission_page', $new_settings );
		$this->assertNotEquals( $origin_kbs_options['submission_page'], $new_settings['submission_page'] );
		$this->assertArrayHasKey( 'tickets_page', $new_settings );
		$this->assertNotEquals( $origin_kbs_options['tickets_page'], $new_settings['tickets_page'] );

		$this->assertEquals( KBS_VERSION, get_option( 'kbs_version' ) );

		$this->assertInstanceOf( 'WP_Role', get_role( 'support_manager' ) );
		$this->assertInstanceOf( 'WP_Role', get_role( 'support_agent' ) );
		$this->assertInstanceOf( 'WP_Role', get_role( 'support_customer' ) );

		// Reset to original data.
		wp_delete_post( $new_settings['submission_page'], true );
		wp_delete_post( $new_settings['tickets_page'], true );
		$kbs_options = $origin_kbs_options;
		update_option( 'kbs_settings', $kbs_options );
		update_option( 'kbs_version', $origin_kbs_version );

	}

	/**
	 * Test that the install doesn't redirect when activating multiple plugins.
	 *
	 * @since	1.0
	 */
	public function test_install_bail() {

		$_GET['activate-multi'] = 1;

		kbs_install();

		$this->assertFalse( get_transient( 'activate-multi' ) );

	}

	/**
	 * Test kbs_after_install(). Test that the transient gets deleted.
	 *
	 * Since	1.0
	 */
	public function test_kbs_ater_install() {

		// Prepare for test
		set_transient( '_kbs_installed', $GLOBALS['kbs_options'], 30 );

		// Fake admin screen
		set_current_screen( 'dashboard' );

		$this->assertNotFalse( get_transient( '_kbs_installed' ) );

		kbs_after_install();

		$this->assertFalse( get_transient( '_kbs_installed' ) );

	}

	/**
	 * Test that when not in admin, the function bails.
	 *
	 * @since	1.0
	 */
	public function test_kbs_after_install_bail_no_admin() {

		// Prepare for test
		set_current_screen( 'front' );
		set_transient( '_kbs_installed', $GLOBALS['kbs_options'], 30 );

		kbs_after_install();
		$this->assertNotFalse( get_transient( '_kbs_installed' ) );

	}


	/**
	 * Test that kbs_after_install() bails when transient doesn't exist.
	 * Kind of a useless test, but for coverage :-)
	 *
	 * @since	1.0
	 */
	public function test_kbs_after_install_bail_transient() {

		// Fake admin screen
		set_current_screen( 'dashboard' );

		delete_transient( '_kbs_installed' );

		$this->assertNull( kbs_after_install() );

		// Reset to origin
		set_transient( '_kbs_installed', $GLOBALS['kbs_options'], 30 );

	}

	/**
	 * Test that kbs_install_roles_on_network() bails when $wp_roles is no object.
	 * Kind of a useless test, but for coverage :-)
	 *
	 * @since	1.0
	 */
	public function test_kbs_install_roles_on_network_bail_object() {

		global $wp_roles;

		$origin_roles = $wp_roles;

		$wp_roles = null;

		$this->assertNull( kbs_install_roles_on_network() );

		// Reset to origin
		$wp_roles = $origin_roles;

	}

	/**
	 * Test that kbs_install_roles_on_network() creates the roles when 'support_manager' is not defined.
	 *
	 * @since	1.0
	 */
	public function test_kbs_install_roles_on_network() {

		global $wp_roles;

		$origin_roles = $wp_roles;

		// Prepare variables for test
		unset( $wp_roles->roles['support_manager'] );

		kbs_install_roles_on_network();

		// Test that the roles are created
		$this->assertInstanceOf( 'WP_Role', get_role( 'support_manager' ) );
		$this->assertInstanceOf( 'WP_Role', get_role( 'support_agent' ) );
		$this->assertInstanceOf( 'WP_Role', get_role( 'support_customer' ) );


		// Reset to origin
		$wp_roles = $origin_roles;

	}

	/**
	 * Test that kbs_install_roles_on_network() creates the roles when $wp_roles->roles is initially false.
	 *
	 * @since 2.6.3
	 */
	public function test_kbs_install_roles_on_network_when_roles_false() {

		global $wp_roles;

		$origin_roles = $wp_roles->roles;

		// Prepare variables for test
		$wp_roles->roles = false;

		kbs_install_roles_on_network();

		// Test that the roles are created
		$this->assertInstanceOf( 'WP_Role', get_role( 'support_manager' ) );
		$this->assertInstanceOf( 'WP_Role', get_role( 'support_agent' ) );
		$this->assertInstanceOf( 'WP_Role', get_role( 'support_customer' ) );


		// Reset to origin
		$wp_roles->roles = $origin_roles;

	}

}