<?php
/**
 * KB Support API for creating Email template tags
 *
 * Taken from Easy Digital Downloads
 *
 * Email tags are wrapped in { }
 *
 * A few examples:
 *
 * {ticket_details}
 * {name}
 * {sitename}
 *
 *
 * To replace tags in content, use: kbs_do_email_tags( $content, $ticket_id );
 *
 * To add tags, use: kbs_add_email_tag( $tag, $description, $func ). Be sure to wrap kbs_add_email_tag()
 * in a function hooked to the 'kbs_add_email_tags' action
 *
 * @package     KBS
 * @subpackage  Emails
 * @copyright   Copyright (c) 2016, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

class KBS_Email_Template_Tags {

	/**
	 * Container for storing all tags
	 *
	 * @since	1.0
	 */
	private $tags;

	/**
	 * Ticket ID
	 *
	 * @since	1.0
	 */
	private $ticket_id;

	/**
	 * Add an email tag
	 *
	 * @since	1.0
	 *
	 * @param	str			$tag	Email tag to be replaces in email
	 * @param	callable	$func	Hook to run when email tag is found
	 */
	public function add( $tag, $description, $func ) {
		if ( is_callable( $func ) ) {
			$this->tags[ $tag ] = array(
				'tag'         => $tag,
				'description' => $description,
				'func'        => $func
			);
		}
	} // add

	/**
	 * Remove an email tag
	 *
	 * @since	1.0
	 *
	 * @param	str		$tag	Email tag to remove hook from
	 */
	public function remove( $tag ) {
		unset( $this->tags[ $tag ] );
	} // remove

	/**
	 * Check if $tag is a registered email tag
	 *
	 * @since	1.0
	 *
	 * @param	str		$tag	Email tag that will be searched
	 *
	 * @return bool
	 */
	public function email_tag_exists( $tag ) {
		return array_key_exists( $tag, $this->tags );
	} // email_tag_exists

	/**
	 * Returns a list of all email tags
	 *
	 * @since	1.0
	 *
	 * @return	arr
	 */
	public function get_tags() {
		return $this->tags;
	} // get_tags

	/**
	 * Search content for email tags and filter email tags through their hooks.
	 *
	 * @param	str		$content	Content to search for email tags
	 * @param	int		$ticket_id	The ticket id
	 *
	 * @since	1.0
	 *
	 * @return	str		Content with email tags filtered out.
	 */
	public function do_tags( $content, $ticket_id ) {

		// Check if there is at least one tag added
		if ( empty( $this->tags ) || ! is_array( $this->tags ) ) {
			return $content;
		}

		$this->ticket_id = $ticket_id;

		$new_content = preg_replace_callback( "/{([A-z0-9\-\_]+)}/s", array( $this, 'do_tag' ), $content );

		$this->ticket_id = null;

		return $new_content;
	} // do_tags

	/**
	 * Do a specific tag, this function should not be used. Please use kbs_do_email_tags instead.
	 *
	 * @since	1.0
	 *
	 * @param	str		$m	Message
	 *
	 * @return mixed
	 */
	public function do_tag( $m ) {

		// Get tag
		$tag = $m[1];

		// Return tag if tag not set
		if ( ! $this->email_tag_exists( $tag ) ) {
			return $m[0];
		}

		return call_user_func( $this->tags[ $tag ]['func'], $this->ticket_id, $tag );
	} // do_tag

} // KBS_Email_Template_Tags

/**
 * Add an email tag
 *
 * @since	1.0
 *
 * @param	str			$tag	Email tag to be replace in email
 * @param	callable	$func	Hook to run when email tag is found
 */
function kbs_add_email_tag( $tag, $description, $func ) {
	KBS()->email_tags->add( $tag, $description, $func );
} // kbs_add_email_tag

/**
 * Remove an email tag
 *
 * @since	1.0
 *
 * @param	str		$tag	Email tag to remove hook from
 */
function kbs_remove_email_tag( $tag ) {
	KBS()->email_tags->remove( $tag );
} // kbs_remove_email_tag

/**
 * Check if $tag is a registered email tag
 *
 * @since	1.0
 *
 * @param	str		$tag	Email tag that will be searched
 *
 * @return	bool
 */
function kbs_email_tag_exists( $tag ) {
	return KBS()->email_tags->email_tag_exists( $tag );
} // kbs_email_tag_exists

/**
 * Get all email tags
 *
 * @since	1.0
 *
 * @return	arr
 */
function kbs_get_email_tags() {
	return KBS()->email_tags->get_tags();
} // kbs_get_email_tags

/**
 * Get a formatted HTML list of all available email tags
 *
 * @since	1.0
 *
 * @return	str
 */
function kbs_get_emails_tags_list() {
	// The list
	$list = '';

	// Get all tags
	$email_tags = kbs_get_email_tags();

	// Check
	if ( count( $email_tags ) > 0 ) {

		// Loop
		foreach ( $email_tags as $email_tag ) {

			// Add email tag to list
			$list .= '{' . $email_tag['tag'] . '} - ' . $email_tag['description'] . '<br/>';

		}

	}

	return $list;
} // kbs_get_emails_tags_list

/**
 * Search content for email tags and filter email tags through their hooks
 *
 * @param	str		$content	Content to search for email tags
 * @param	int		$ticket_id	The ticket id
 *
 * @since	1.0
 *
 * @return	str		Content with email tags filtered out.
 */
function kbs_do_email_tags( $content, $ticket_id ) {

	// Replace all tags
	$content = KBS()->email_tags->do_tags( $content, $ticket_id );

	// Return content
	return $content;
} // kbs_do_email_tags

/**
 * Load email tags
 *
 * @since	1.0
 */
function kbs_load_email_tags() {
	do_action( 'kbs_add_email_tags' );
} // kbs_load_email_tags
add_action( 'init', 'kbs_load_email_tags', -999 );

/**
 * Add default KBS email template tags
 *
 * @since	1.0
 */
function kbs_setup_email_tags() {

	// Setup default tags array
	$email_tags = array(
		array(
			'tag'         => 'ticket_details',
			'description' => __( 'Details of the ticket', 'kb-support' ),
			'function'    => 'text/html' == KBS()->emails->get_content_type() ? 'kbs_email_tag_ticket_details' : 'kbs_email_tag_ticket_details_plain'
		),
		array(
			'tag'         => 'name',
			'description' => __( 'The customers first name', 'kb-support' ),
			'function'    => 'kbs_email_tag_first_name'
		),
		array(
			'tag'         => 'fullname',
			'description' => __( 'The customers full name, first and last', 'kb-support' ),
			'function'    => 'kbs_email_tag_fullname'
		),
		array(
			'tag'         => 'username',
			'description' => __( 'The customers user name on the site, if they registered an account', 'kb-support' ),
			'function'    => 'kbs_email_tag_username'
		),
		array(
			'tag'         => 'user_email',
			'description' => __( 'The customers email address', 'kb-support' ),
			'function'    => 'kbs_email_tag_user_email'
		),
		array(
			'tag'         => 'date',
			'description' => __( 'The date of the ticket', 'kb-support' ),
			'function'    => 'kbs_email_tag_date'
		),
		array(
			'tag'         => 'time',
			'description' => __( 'The time of the ticket', 'kb-support' ),
			'function'    => 'kbs_email_tag_time'
		),
		array(
			'tag'         => 'ticket_id',
			'description' => __( 'The unique ID number for this ticket', 'kb-support' ),
			'function'    => 'kbs_email_tag_ticket_id'
		),
		array(
			'tag'         => 'sitename',
			'description' => __( 'Your site name', 'kb-support' ),
			'function'    => 'kbs_email_tag_sitename'
		),
		array(
			'tag'         => 'ticket_link',
			'description' => __( 'Adds a link so users can view their ticket directly on your website if they are unable to view it in the browser correctly.', 'kb-support' ),
			'function'    => 'kbs_email_tag_ticket_link'
		)
	);

	// Apply kbs_email_tags filter
	$email_tags = apply_filters( 'kbs_email_tags', $email_tags );

	// Add email tags
	foreach ( $email_tags as $email_tag ) {
		kbs_add_email_tag( $email_tag['tag'], $email_tag['description'], $email_tag['function'] );
	}

} // kbs_setup_email_tags
add_action( 'kbs_add_email_tags', 'kbs_setup_email_tags' );

/**
 * Email template tag: ticket_details
 * A list of download links for each download purchased
 *
 * @since	1.0
 * @param	int		$ticket_id
 * @return	str		Ticket details
 */
function kbs_email_tag_ticket_details( $ticket_id ) {
} // kbs_email_tag_ticket_details

/**
 * Email template tag: download_list
 * A list of download links for each download purchased in plaintext
 *
 * @since	1.0
 * @param	int		$ticket_id
 * @return	str		Ticket details
 */
function kbs_email_tag_ticket_details_plain( $ticket_id ) {
	
} // kbs_email_tag_ticket_details_plain

/**
 * Email template tag: name
 * The customers first name
 *
 * @since	1.0
 * @param	int		$ticket_id
 * @return	str		name
 */
function kbs_email_tag_first_name( $ticket_id ) {
	$ticket = new KBS_Ticket( $ticket_id );

	$user_info = $ticket->user_info;

	if ( empty( $user_info ) ) {
		return '';
	}

	$email_name   = kbs_get_email_names( $user_info );
	return $email_name['name'];
} // kbs_email_tag_first_name

/**
 * Email template tag: fullname
 * The customers full name, first and last
 *
 * @since	1.0
 * @param	int		$ticket_id
 * @return	str		fullname
 */
function kbs_email_tag_fullname( $ticket_id ) {
	$ticket = new KBS_Ticket( $ticket_id );

	$user_info = $ticket->user_info;

	if ( empty( $user_info ) ) {
		return '';
	}

	$email_name   = kbs_get_email_names( $user_info );
	return $email_name['fullname'];
} // kbs_email_tag_fullname

/**
 * Email template tag: username
 * The customers user name on the site, if they registered an account
 *
 * @since	1.0
 * @param	int		$ticket_id
 * @return	str		username
 */
function kbs_email_tag_username( $ticket_id  ) {
	$ticket = new KBS_Ticket( $ticket_id );

	$user_info = $ticket->user_info;

	if ( empty( $user_info ) ) {
		return '';
	}

	$email_name   = kbs_get_email_names( $user_info );
	return $email_name['username'];
} // kbs_email_tag_username

/**
 * Email template tag: user_email
 * The customers email address
 *
 * @since	1.0
 * @param	int		$ticket_id
 * @return	str		user_email
 */
function kbs_email_tag_user_email( $ticket_id ) {
	$ticket = new KBS_Ticket( $ticket_id );

	return $ticket->email;
} // kbs_email_tag_user_email

/**
 * Email template tag: date
 * Date of ticket
 *
 * @since	1.0
 * @param	int		$ticket_id
 * @return	str		date
 */
function kbs_email_tag_date( $ticket_id  ) {
	$post_time = get_post_time( 'U', false, $ticket_id );

	return get_date_from_gmt( $post_time, get_option( 'date_format' ) );
} // kbs_email_tag_date

/**
 * Email template tag: time
 * Time of ticket
 *
 * @since	1.0
 * @param	int		$ticket_id
 * @return	str		date
 */
function kbs_email_tag_time( $ticket_id ) {
	$post_time = get_post_time( 'U', false, $ticket_id );

	return get_date_from_gmt( $post_time, get_option( 'time_format' ) );
} // kbs_email_tag_time

/**
 * Email template tag: ticket_id
 * The unique ID number for this ticket
 *
 * @since	1.0
 * @param	int		$ticket_id
 * @return	int		ticket_id
 */
function kbs_email_tag_ticket_id( $ticket_id ) {
	return kbs_get_ticket_id( $ticket_id );
} // kbs_email_tag_ticket_id

/**
 * Email template tag: sitename
 * Your site name
 *
 * @since	1.0
 * @param	int		$ticket_id
 * @return	str		sitename
 */
function kbs_email_tag_sitename( $ticket_id ) {
	return wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );
} // kbs_email_tag_sitename

/**
 * Email template tag: ticket_link
 * Adds a link so users can view their ticket directly on your website if they are unable to view it in the browser correctly
 *
 * @since	1.0
 * @param	int		$ticket_id
 * @return	str		Ticket link
 */
function kbs_email_tag_ticket_link( $ticket_id ) {
	
} // kbs_email_tag_ticket_link
