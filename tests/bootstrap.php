<?php

$_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';
$_SERVER['SERVER_NAME'] = '';
$PHP_SELF = $GLOBALS['PHP_SELF'] = $_SERVER['PHP_SELF'] = '/index.php';

$_tests_dir = getenv( 'WP_TESTS_DIR' );
if ( ! $_tests_dir ) {
	$_tests_dir = '/tmp/wordpress-tests-lib';
}

define('PLUGIN_NAME','kb-support.php');
define('PLUGIN_FOLDER',basename(dirname( __DIR__ )));
define('PLUGIN_PATH',PLUGIN_FOLDER.'/'.PLUGIN_NAME);

// Activates this plugin in WordPress so it can be tested.
$GLOBALS['wp_tests_options'] = array(
  'active_plugins' => array( PLUGIN_PATH ),
);

require_once $_tests_dir . '/includes/functions.php';

function _manually_load_plugin() {
	require dirname( __DIR__ ) . '/'.PLUGIN_NAME;
}

tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

require $_tests_dir . '/includes/bootstrap.php';

activate_plugin( 'kb-support/kb-support.php' );
echo "Installing KB Support...\n";

// Install KB Support
kbs_install();

global $current_user, $kbs_options;
$kbs_options = get_option( 'kbs_settings' );

$current_user = new WP_User(1);
$current_user->set_role('administrator');

wp_update_user( array( 'ID' => 1, 'first_name' => 'Admin', 'last_name' => 'User' ) );
add_filter( 'kbs_log_email_errors', '__return_false' );

function _disable_reqs( $status = false, $args = array(), $url = '') {
	return new WP_Error( 'no_reqs_in_unit_tests', __( 'HTTP Requests disbaled for unit tests', 'kbs' ) );
}
add_filter( 'pre_http_request', '_disable_reqs' );

// Include helpers
require_once 'helpers/class-kbs-unittestcase.php';
