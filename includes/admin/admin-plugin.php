<?php
/**
 * Admin Plugin
 *
 * @package     KBS
 * @subpackage  Admin/Functions
 * @copyright   Copyright (c) 2017, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Adds rate us text to admin footer when KB Support admin pages are viewed.
 *
 * @since	1.0
 * @param	str		$footer_text	The footer text to output
 * @return	str		Filtered footer text for output
 */
function kbs_admin_footer_rate_us( $footer_text )	{
	global $typenow;

	if ( 'kbs_ticket' == $typenow || 'article' == $typenow || 'kbs_form' == $typenow )	{

		$footer_text = sprintf(
			__( 'If <strong>KB Support</strong> is helping you support your customers, please <a href="%s" target="_blank">leave us a ★★★★★ rating</a>. A <strong style="text-decoration: underline;">huge</strong> thank you in advance!', 'kb-support'
			),
			'https://wordpress.org/support/view/plugin-reviews/kb-support?rate=5#postform'
		);

	}

	return $footer_text;
} // kbs_admin_footer_rate_us
add_filter( 'admin_footer_text', 'kbs_admin_footer_rate_us' );
