<?php
/**
 * Setup Knowledgebase
 *
 * @package     KBS
 * @subpackage  Classes/Knowledgebase
 * @copyright   Copyright (c) 2017, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.1
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * KBS_Knowledgebase Class
 *
 * @since	1.1
 */
class KBS_Knowledgebase {

	/**
	 * Registered knowledgebases
	 *
	 * @since	1.1
	 * @var		array
	 */
	public $registered_integrations;

	/**
	 * Default knowledgebase?
	 *
	 * @since	1.1
	 *
	 */
	public $default_kb = true;

	/**
	 * The article post type
	 *
	 * @since 1.1
	 */
	public $post_type = 'article';

	/**
	 * The active knowledgebase
	 *
	 * @since	1.1
	 */
	public $active_kb;

	/**
	 * Get things going
	 *
	 * @since	1.1
	 */
	public function __construct()	{
		add_action( 'init',            array( $this, 'setup_kb' ) );
		//add_action( 'kbs_kb_init_kbs', '' );
	} // __construct

	/**
	 * Setup the knowledgebase.
	 *
	 * @since	1.1
	 */
	public function setup_kb()	{
		$this->get_registered_knowledgebases();
		$this->get_active_kb();

		if ( 'kbs' != $this->active_kb )	{
			$this->default_kb = false;
		}

		do_action( 'kbs_kb_init_' . $this->active_kb, $this );
	} // setup_kb

	/**
	 * Retrieve the registered KBs.
	 *
	 * @since	1.1
	 */
	public function get_registered_knowledgebases()	{
		if ( ! isset( $this->registered_integrations ) )	{
			$this->registered_integrations = array(
				'kbs' => __( 'KB Support', 'kb-support' )
			);
		}

		// Allow devs to register knowledgebases
		$this->registered_integrations = apply_filters( 'kbs_registered_kb_integrations', $this->registered_integrations );

		asort( $this->registered_integrations );

		return $this->registered_integrations;
	} // get_registered_knowledgebases

	/**
	 * Retrieve the active KB.
	 *
	 * @since	1.1
	 */
	private function get_active_kb()	{
		if ( ! isset( $this->active_kb ) )	{
			$this->active_kb = kbs_get_option( 'active_kb', 'kbs' );
		}

		return $this->active_kb;
	} // get_active_kb

} // KBS_Knowledgebase
