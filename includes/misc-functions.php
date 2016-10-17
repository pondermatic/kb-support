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
 * Checks if users can only submit tickets when logged in
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
 * @param	str		$notice			The message key to display.
 * @param	bool	$notice_only	True to only return the message string, false to return class/notice array.
 * @return	arr|str	Notice.
 */
function kbs_get_notices( $notice = '', $notice_only = false )	{
	$notices = array(
		'need_login' => array(
			'class'  => 'info',
			'notice' => sprintf( __( 'You must be logged in to create a support %s.', 'kb-support' ), kbs_get_ticket_label_singular( true ) )
		),
		'username_incorrect' => array(
			'class'  => 'error',
			'notice' => __( 'The username was not recognised.', 'kb-support' )
		),
		'password_incorrect' => array(
			'class'  => 'error',
			'notice' => __( 'An incorrect password was entered.', 'kb-support' )
		),
		'missing_registration_data' => array(
			'class'  => 'error',
			'notice' => __( 'All registration fields are mandatory.', 'kb-support' )
		),
		'could_not_register' => array(
			'class'  => 'error',
			'notice' => __( 'Unable to register your user account.', 'kb-support' )
		),
		'empty_username' => array(
			'class'  => 'error',
			'notice' => __( 'Please enter a username.', 'kb-support' )
		),
		'username_unavailable' => array(
			'class'  => 'error',
			'notice' => __( 'Your chosen username is unavailable.', 'kb-support' )
		),
		'username_invalid' => array(
			'class'  => 'error',
			'notice' => __( 'You entered an invalid username.', 'kb-support' )
		),
		'email_unavailable' => array(
			'class'  => 'error',
			'notice' => __( 'The email address you entered is already registered.', 'kb-support' )
		),
		'email_invalid' => array(
			'class'  => 'error',
			'notice' => __( 'You entered an invalid email address.', 'kb-support' )
		),
		'empty_password' => array(
			'class'  => 'error',
			'notice' => __( 'Please enter a password.', 'kb-support' )
		),
		'password_mismatch' => array(
			'class'  => 'error',
			'notice' => __( 'Passwords do not match.', 'kb-support' )
		),
		'ticket_submitted' => array(
			'class'  => 'success',
			'notice' => __( "Your support request has been successfully received. We'll be in touch as soon as possible.", 'kb-support' )
		),
		'ticket_failed' => array(
			'class'  => 'error',
			'notice' => __( 'There was an error submitting your support request. Please try again', 'kb-support' )
		),
		'article_restricted' => array(
			'class'  => 'info',
			'notice' => sprintf( __( 'Access to this %s is restricted.', 'kb-support' ), kbs_get_article_label_singular() )
		),
		'article_restricted_login' => array(
			'class'  => 'info',
			'notice' => sprintf( __( 'Access to this %s is restricted. Login to continue.', 'kb-support' ), kbs_get_article_label_singular() )
		),
		'no_ticket' => array(
			'class'  => 'error',
			'notice' => sprintf( __( 'No %s found.', 'kb-support' ), kbs_get_ticket_label_singular( true ) )
		),
		'missing_reply' => array(
			'class'  => 'error',
			'notice' => __( 'Please enter your reply.', 'kb-support' )
		),
		'reply_success' => array(
			'class'  => 'success',
			'notice' => __( 'Your reply has been received. If necessary, one of our agents will be in touch shortly.', 'kb-support' )
		),
		'reply_fail' => array(
			'class'  => 'error',
			'notice' => __( 'Your reply could not be processed.', 'kb-support' )
		),
		'max_files' => array(
			'class'  => 'error',
			'notice' => sprintf( __( 'The maximum number of files you are allowed to upload is %s.', 'kb-support' ), kbs_get_max_file_uploads() )
		),
		'invalid_customer' => array(
			'class'  => 'error',
			'notice' => sprintf( __( 'You are not allowed to manage this %s.', 'kb-support' ), kbs_get_ticket_label_singular() )
		)
	);

	$notices = apply_filters( 'kbs_get_notices', $notices );

	if ( ! empty( $notice ) )	{
		if ( ! array_key_exists( $notice, $notices ) )	{
			return false;
		}

		if ( ! $notice_only )	{
			return $notices[ $notice ];
		} else	{
			return $notices[ $notice ]['notice'];
		}
	}

	return $notices;

} // kbs_get_notices

/**
 * Get Country List
 *
 * @since	1.0
 * @return	arr		$countries	A list of the available countries
 */
function kbs_get_country_list() {
	$countries = array(
		''   => '',
		'GB' => 'United Kingdom',
		'US' => 'United States',
		'CA' => 'Canada',
		'AF' => 'Afghanistan',
		'AX' => '&#197;land Islands',
		'AL' => 'Albania',
		'DZ' => 'Algeria',
		'AS' => 'American Samoa',
		'AD' => 'Andorra',
		'AO' => 'Angola',
		'AI' => 'Anguilla',
		'AQ' => 'Antarctica',
		'AG' => 'Antigua and Barbuda',
		'AR' => 'Argentina',
		'AM' => 'Armenia',
		'AW' => 'Aruba',
		'AU' => 'Australia',
		'AT' => 'Austria',
		'AZ' => 'Azerbaijan',
		'BS' => 'Bahamas',
		'BH' => 'Bahrain',
		'BD' => 'Bangladesh',
		'BB' => 'Barbados',
		'BY' => 'Belarus',
		'BE' => 'Belgium',
		'BZ' => 'Belize',
		'BJ' => 'Benin',
		'BM' => 'Bermuda',
		'BT' => 'Bhutan',
		'BO' => 'Bolivia',
		'BQ' => 'Bonaire, Saint Eustatius and Saba',
		'BA' => 'Bosnia and Herzegovina',
		'BW' => 'Botswana',
		'BV' => 'Bouvet Island',
		'BR' => 'Brazil',
		'IO' => 'British Indian Ocean Territory',
		'BN' => 'Brunei Darrussalam',
		'BG' => 'Bulgaria',
		'BF' => 'Burkina Faso',
		'BI' => 'Burundi',
		'KH' => 'Cambodia',
		'CM' => 'Cameroon',
		'CV' => 'Cape Verde',
		'KY' => 'Cayman Islands',
		'CF' => 'Central African Republic',
		'TD' => 'Chad',
		'CL' => 'Chile',
		'CN' => 'China',
		'CX' => 'Christmas Island',
		'CC' => 'Cocos Islands',
		'CO' => 'Colombia',
		'KM' => 'Comoros',
		'CD' => 'Congo, Democratic People\'s Republic',
		'CG' => 'Congo, Republic of',
		'CK' => 'Cook Islands',
		'CR' => 'Costa Rica',
		'CI' => 'Cote d\'Ivoire',
		'HR' => 'Croatia/Hrvatska',
		'CU' => 'Cuba',
		'CW' => 'Cura&Ccedil;ao',
		'CY' => 'Cyprus',
		'CZ' => 'Czech Republic',
		'DK' => 'Denmark',
		'DJ' => 'Djibouti',
		'DM' => 'Dominica',
		'DO' => 'Dominican Republic',
		'TP' => 'East Timor',
		'EC' => 'Ecuador',
		'EG' => 'Egypt',
		'GQ' => 'Equatorial Guinea',
		'SV' => 'El Salvador',
		'ER' => 'Eritrea',
		'EE' => 'Estonia',
		'ET' => 'Ethiopia',
		'FK' => 'Falkland Islands',
		'FO' => 'Faroe Islands',
		'FJ' => 'Fiji',
		'FI' => 'Finland',
		'FR' => 'France',
		'GF' => 'French Guiana',
		'PF' => 'French Polynesia',
		'TF' => 'French Southern Territories',
		'GA' => 'Gabon',
		'GM' => 'Gambia',
		'GE' => 'Georgia',
		'DE' => 'Germany',
		'GR' => 'Greece',
		'GH' => 'Ghana',
		'GI' => 'Gibraltar',
		'GL' => 'Greenland',
		'GD' => 'Grenada',
		'GP' => 'Guadeloupe',
		'GU' => 'Guam',
		'GT' => 'Guatemala',
		'GG' => 'Guernsey',
		'GN' => 'Guinea',
		'GW' => 'Guinea-Bissau',
		'GY' => 'Guyana',
		'HT' => 'Haiti',
		'HM' => 'Heard and McDonald Islands',
		'VA' => 'Holy See (City Vatican State)',
		'HN' => 'Honduras',
		'HK' => 'Hong Kong',
		'HU' => 'Hungary',
		'IS' => 'Iceland',
		'IN' => 'India',
		'ID' => 'Indonesia',
		'IR' => 'Iran',
		'IQ' => 'Iraq',
		'IE' => 'Ireland',
		'IM' => 'Isle of Man',
		'IL' => 'Israel',
		'IT' => 'Italy',
		'JM' => 'Jamaica',
		'JP' => 'Japan',
		'JE' => 'Jersey',
		'JO' => 'Jordan',
		'KZ' => 'Kazakhstan',
		'KE' => 'Kenya',
		'KI' => 'Kiribati',
		'KW' => 'Kuwait',
		'KG' => 'Kyrgyzstan',
		'LA' => 'Lao People\'s Democratic Republic',
		'LV' => 'Latvia',
		'LB' => 'Lebanon',
		'LS' => 'Lesotho',
		'LR' => 'Liberia',
		'LY' => 'Libyan Arab Jamahiriya',
		'LI' => 'Liechtenstein',
		'LT' => 'Lithuania',
		'LU' => 'Luxembourg',
		'MO' => 'Macau',
		'MK' => 'Macedonia',
		'MG' => 'Madagascar',
		'MW' => 'Malawi',
		'MY' => 'Malaysia',
		'MV' => 'Maldives',
		'ML' => 'Mali',
		'MT' => 'Malta',
		'MH' => 'Marshall Islands',
		'MQ' => 'Martinique',
		'MR' => 'Mauritania',
		'MU' => 'Mauritius',
		'YT' => 'Mayotte',
		'MX' => 'Mexico',
		'FM' => 'Micronesia',
		'MD' => 'Moldova, Republic of',
		'MC' => 'Monaco',
		'MN' => 'Mongolia',
		'ME' => 'Montenegro',
		'MS' => 'Montserrat',
		'MA' => 'Morocco',
		'MZ' => 'Mozambique',
		'MM' => 'Myanmar',
		'NA' => 'Namibia',
		'NR' => 'Nauru',
		'NP' => 'Nepal',
		'NL' => 'Netherlands',
		'AN' => 'Netherlands Antilles',
		'NC' => 'New Caledonia',
		'NZ' => 'New Zealand',
		'NI' => 'Nicaragua',
		'NE' => 'Niger',
		'NG' => 'Nigeria',
		'NU' => 'Niue',
		'NF' => 'Norfolk Island',
		'KP' => 'North Korea',
		'MP' => 'Northern Mariana Islands',
		'NO' => 'Norway',
		'OM' => 'Oman',
		'PK' => 'Pakistan',
		'PW' => 'Palau',
		'PS' => 'Palestinian Territories',
		'PA' => 'Panama',
		'PG' => 'Papua New Guinea',
		'PY' => 'Paraguay',
		'PE' => 'Peru',
		'PH' => 'Phillipines',
		'PN' => 'Pitcairn Island',
		'PL' => 'Poland',
		'PT' => 'Portugal',
		'PR' => 'Puerto Rico',
		'QA' => 'Qatar',
		'XK' => 'Republic of Kosovo',
		'RE' => 'Reunion Island',
		'RO' => 'Romania',
		'RU' => 'Russian Federation',
		'RW' => 'Rwanda',
		'BL' => 'Saint Barth&eacute;lemy',
		'SH' => 'Saint Helena',
		'KN' => 'Saint Kitts and Nevis',
		'LC' => 'Saint Lucia',
		'MF' => 'Saint Martin (French)',
		'SX' => 'Saint Martin (Dutch)',
		'PM' => 'Saint Pierre and Miquelon',
		'VC' => 'Saint Vincent and the Grenadines',
		'SM' => 'San Marino',
		'ST' => 'S&atilde;o Tom&eacute; and Pr&iacute;ncipe',
		'SA' => 'Saudi Arabia',
		'SN' => 'Senegal',
		'RS' => 'Serbia',
		'SC' => 'Seychelles',
		'SL' => 'Sierra Leone',
		'SG' => 'Singapore',
		'SK' => 'Slovak Republic',
		'SI' => 'Slovenia',
		'SB' => 'Solomon Islands',
		'SO' => 'Somalia',
		'ZA' => 'South Africa',
		'GS' => 'South Georgia',
		'KR' => 'South Korea',
		'SS' => 'South Sudan',
		'ES' => 'Spain',
		'LK' => 'Sri Lanka',
		'SD' => 'Sudan',
		'SR' => 'Suriname',
		'SJ' => 'Svalbard and Jan Mayen Islands',
		'SZ' => 'Swaziland',
		'SE' => 'Sweden',
		'CH' => 'Switzerland',
		'SY' => 'Syrian Arab Republic',
		'TW' => 'Taiwan',
		'TJ' => 'Tajikistan',
		'TZ' => 'Tanzania',
		'TH' => 'Thailand',
		'TL' => 'Timor-Leste',
		'TG' => 'Togo',
		'TK' => 'Tokelau',
		'TO' => 'Tonga',
		'TT' => 'Trinidad and Tobago',
		'TN' => 'Tunisia',
		'TR' => 'Turkey',
		'TM' => 'Turkmenistan',
		'TC' => 'Turks and Caicos Islands',
		'TV' => 'Tuvalu',
		'UG' => 'Uganda',
		'UA' => 'Ukraine',
		'AE' => 'United Arab Emirates',
		'UY' => 'Uruguay',
		'UM' => 'US Minor Outlying Islands',
		'UZ' => 'Uzbekistan',
		'VU' => 'Vanuatu',
		'VE' => 'Venezuela',
		'VN' => 'Vietnam',
		'VG' => 'Virgin Islands (British)',
		'VI' => 'Virgin Islands (USA)',
		'WF' => 'Wallis and Futuna Islands',
		'EH' => 'Western Sahara',
		'WS' => 'Western Samoa',
		'YE' => 'Yemen',
		'ZM' => 'Zambia',
		'ZW' => 'Zimbabwe'
	);

	return apply_filters( 'kbs_countries', $countries );
} // kbs_get_country_list
