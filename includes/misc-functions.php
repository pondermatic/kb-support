<?php
/**
 * Misc Functions
 *
 * @package     KBS
 * @subpackage  Functions
 * @copyright   Copyright (c) 2016, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Checks if file uploads are enabled
 *
 * @since	1.0
 * @return	bool	$ret	True if guest checkout is enabled, false otherwise
 */
function kbs_file_uploads_are_enabled() {
	$return = false;

	if( kbs_get_option( 'file_uploads', false ) )	{
		$return = true;
	}
	
	return (bool) apply_filters( 'kbs_file_uploads', $return );
} // kbs_file_uploads_are_enabled

/**
 * Set Upload Directory
 *
 * Sets the upload dir to kbs.
 *
 * @since	1.0
 * @return	arr		Upload directory information.
 */
function kbs_set_upload_dir( $upload ) {

	// Override the year / month being based on the post publication date, if year/month organization is enabled
	if ( get_option( 'uploads_use_yearmonth_folders' ) )	{

		$time             = current_time( 'mysql' );
		$y                = substr( $time, 0, 4 );
		$m                = substr( $time, 5, 2 );
		$upload['subdir'] = "/$y/$m";

	}

	$upload['subdir'] = '/kbs' . $upload['subdir'];
	$upload['path']   = $upload['basedir'] . $upload['subdir'];
	$upload['url']    = $upload['baseurl'] . $upload['subdir'];
	
	return apply_filters( 'kbs_set_upload_dir', $upload );

} // kbs_set_upload_dir

/**
 * Change Tickets Upload Directory.
 *
 * This function works by hooking on the WordPress Media Uploader
 * and moving the uploading files that are used for KBS to a kbs
 * directory under wp-content/uploads/ therefore,
 * the new directory is wp-content/uploads/kbs/{year}/{month}.
 *
 * @since	1.0
 * @global	$pagenow
 * @return	void
 */
function kbs_change_downloads_upload_dir() {

	global $pagenow;

	if ( ! empty( $_REQUEST['post_id'] ) && ( 'async-upload.php' == $pagenow || 'media-upload.php' == $pagenow ) )	{

		if ( 'kbs_ticket' == get_post_type( $_REQUEST['post_id'] ) ) {
			add_filter( 'upload_dir', 'kbs_set_upload_dir' );
		}

	}

} // kbs_change_downloads_upload_dir
add_action( 'admin_init', 'kbs_change_downloads_upload_dir', 999 );

/**
 * Sets the enctype for file upload forms.
 *
 * @since	1.0
 * @return	str
 */
function kbs_maybe_set_enctype() {
	if ( kbs_file_uploads_are_enabled() )	{
		$output = ' enctype="multipart/form-data"';
		
		echo apply_filters( 'kbs_maybe_set_enctype', $output );
	}
} // kbs_file_uploads_are_enabled

/**
 * Retrieves allowed file types
 *
 *
 *
 *
 */

/**
 * Checks if Guest checkout is enabled
 *
 * @since	1.0
 * @return	bool	$ret	True if guest checkout is enabled, false otherwise
 */
function kbs_no_guest_checkout() {
	$ret = kbs_get_option( 'logged_in_only', false );
	return (bool) apply_filters( 'kbs_no_guest_checkout', $ret );
} // kbs_no_guest_checkout

/**
 * Checks if users can only purchase downloads when logged in
 *
 * @since	1.0
 * @return	bool	$ret	Whether or not the logged_in_only setting is set
 */
function kbs_logged_in_only() {
	$ret = kbs_get_option( 'logged_in_only', false );
	return (bool) apply_filters( 'kbs_logged_in_only', $ret );
} // kbs_logged_in_only

/**
 * Get User IP
 *
 * Returns the IP address of the current visitor
 *
 * @since	1.0
 * @return	str		$ip		User's IP address
 */
function kbs_get_ip() {

	$ip = '127.0.0.1';

	if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
		//check ip from share internet
		$ip = $_SERVER['HTTP_CLIENT_IP'];
	} elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
		//to check ip is pass from proxy
		$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	} elseif( ! empty( $_SERVER['REMOTE_ADDR'] ) ) {
		$ip = $_SERVER['REMOTE_ADDR'];
	}
	return apply_filters( 'kbs_get_ip', $ip );
} // kbs_get_ip

/**
 * Month Num To Name
 *
 * Takes a month number and returns the name three letter name of it.
 *
 * @since	1.0
 *
 * @param	int		$n
 * @return	str		Short month name
 */
function kbs_month_num_to_name( $n ) {
	$timestamp = mktime( 0, 0, 0, $n, 1, 2005 );

	return date_i18n( "M", $timestamp );
} // kbs_month_num_to_name

/**
 * Get the current page URL
 *
 * @since	1.0
 * @param	bool	$nocache	If we should bust cache on the returned URL
 * @return	str		$page_url	Current page URL
 */
function kbs_get_current_page_url() {

	global $wp;

	if( get_option( 'permalink_structure' ) ) {

		$base = trailingslashit( home_url( $wp->request ) );

	} else {

		$base = add_query_arg( $wp->query_string, '', trailingslashit( home_url( $wp->request ) ) );
		$base = remove_query_arg( array( 'post_type', 'name' ), $base );

	}

	$scheme = is_ssl() ? 'https' : 'http';
	$uri    = set_url_scheme( $base, $scheme );

	if ( is_front_page() ) {
		$uri = home_url( '/' );
	}

	$uri = apply_filters( 'kbs_get_current_page_url', $uri );

	return $uri;
} // kbs_get_current_page_url

/**
 * Retrieve timezone
 *
 * @since	1.0
 * @return	str		$timezone	The timezone ID
 */
function kbs_get_timezone_id() {

	// if site timezone string exists, return it
	if ( $timezone = get_option( 'timezone_string' ) )
		return $timezone;

	// get UTC offset, if it isn't set return UTC
	if ( ! ( $utc_offset = 3600 * get_option( 'gmt_offset', 0 ) ) )
		return 'UTC';

	// attempt to guess the timezone string from the UTC offset
	$timezone = timezone_name_from_abbr( '', $utc_offset );

	// last try, guess timezone string manually
	if ( $timezone === false ) {

		$is_dst = date( 'I' );

		foreach ( timezone_abbreviations_list() as $abbr ) {
			foreach ( $abbr as $city ) {
				if ( $city['dst'] == $is_dst &&  $city['offset'] == $utc_offset )
					return $city['timezone_id'];
			}
		}
	}

	// fallback
	return 'UTC';
} // kbs_get_timezone_id

/**
 * Validate the form honeypot to protect against bots.
 *
 * @since	1.0
 * @param	arr		$data	Form post data
 * @return	void
 */
function kbs_do_honeypot_check( $data )	{
	if ( ! empty( $data['kbs_honeypot'] ) )	{
		wp_die( __( "Ha! I don't think so little honey bee. No bots allowed in this Honey Pot!", 'kb-support' ) );
	}
	
	return;
} // kbs_do_honeypot_check

/**
 * Display a Notice.
 *
 * @since	1.0
 * @param	str		$m		The notice message key.
 * @return	str		The HTML string for the notice
 */
function kbs_display_notice( $m )	{	
	$notices = kbs_get_notices( $m );

	if ( $notices )	{
		return '<div class="kbs_alert kbs_alert_' . $notices['class'] . '">' . $notices['notice'] . '</div>';
	}
} // kbs_display_notice

/**
 * Front end notices.
 *
 * @since	1.0
 * @param	str		$notice		The message key to display.
 * @return	str		Notice.
 */
function kbs_get_notices( $notice = false )	{
	$notices = array(
		'ticket_submitted' => array(
			'class'  => 'success',
			'notice' => __( "Your support request has been successfully received. We'll be in touch as soon as possible.", 'kb-support' )
		),
		'ticket_failed'    => array(
			'class'  => 'error',
			'notice' => __( 'There was an error submitting your support request. Please try again', 'kb-support' )
		)
	);

	$notices = apply_filters( 'kbs_get_notices', $notices );

	if ( $notice )	{
		if ( ! array_key_exists( $notice, $notices ) )	{
			return false;
		}

		return $notices[ $notice ];
	}

	return $notices;

} // kbs_get_notices