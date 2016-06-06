<?php
/**
 * Front-end Actions
 *
 * @package     KBS
 * @subpackage  Functions
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Hooks KBS actions, when present in the $_GET superglobal. Every kbs_action
 * present in $_GET is called using WordPress's do_action function. These
 * functions are called on init.
 *
 * @since	0.1
 * @return	void
*/
function kbs_get_actions() {
	if ( isset( $_GET['kbs_action'] ) ) {
		do_action( 'kbs_' . $_GET['kbs_action'], $_GET );
	}
} // kbs_get_actions
add_action( 'init', 'kbs_get_actions' );

/**
 * Hooks KBS actions, when present in the $_POST superglobal. Every kbs_action
 * present in $_POST is called using WordPress's do_action function. These
 * functions are called on init.
 *
 * @since	0.1
 * @return	void
*/
function kbs_post_actions() {
	if ( isset( $_POST['kbs_action'] ) ) {	
		do_action( 'kbs_' . $_POST['kbs_action'], $_POST );
	}
} // kbs_post_actions
add_action( 'init', 'kbs_post_actions' );

/**
 * Action field.
 *
 * Prints the output for a hidden form field which is required for post forms.
 *
 * @since	0.1
 * @param	str		$action		The action identifier
 * @param	bool	$echo		True echo's the input field, false to return
 * @return	str		$input		Hidden form field string
 */
function kbs_action_field( $action, $echo = true )	{

	$name = apply_filters( 'kbs_action_field_name', 'kbs_action' );

	$input = '<input type="hidden" name="' . $name . '" id="' . $name . '" value="' . $action . '" />';

	$input = apply_filters( 'kbs_action_field', $action );

	if( ! empty( $echo ) )	{
		echo $input;
	} else	{
		return $input;
	}

} // kbs_action_field