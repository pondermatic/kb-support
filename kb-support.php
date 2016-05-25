<?php
/**
 * Plugin Name: KB Support
 * Plugin URI: http://TBA
 * Description: All in one Support desk and knowledge base. Easy to use, easy to manage, loved by customers
 * Version: 0.1
 * Date: 06 May 2016
 * Author: Mike Howard <mike@mikesplaugins.co.uk>
 * Author URI: http://mikesplugins.co.uk
 * Text Domain: kb-support
 * Domain Path: /languages
 * License: GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Tags: Support Desk, Knowledgebase, KB, Support, Ticketing System, Agents, Customers, Support Tool, Help Desk
 *
 *
 * KB Support is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as 
 * published by the Free Software Foundation.
 * 
 * KB Support is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with KB Support; if not, see https://www.gnu.org/licenses/gpl-2.0.html
 *
 * @package		KBS
 * @category	Core
 * @author		Mike Howard
 * @version		0.1
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'KB_Support' ) ) :
/**
 * Main KB_Support Class.
 *
 * @since 1.4
 */
final class KB_Support {
	/** Singleton *************************************************************/

	/**
	 * @var		KB_Support The one true KB_Support
	 * @since	0.1
	 */
	private static $instance;
	
	/**
	 * KBS Roles Object.
	 *
	 * @var		obj		KBS_Roles
	 * @since	0.1
	 */
	public $roles;
	
	/**
	 * Main KB_Support Instance.
	 *
	 * Insures that only one instance of KB_Support exists in memory at any one
	 * time. Also prevents needing to define globals all over the place.
	 *
	 * @since	0.1
	 * @static
	 * @static	var		arr		$instance
	 * @uses	KB_Support::setup_constants()	Setup the constants needed.
	 * @uses	KB_Support::includes()			Include the required files.
	 * @uses	KB_Support::load_textdomain()	Load the language files.
	 * @see KBS()
	 * @return	obj	KB_Support	The one true KB_Support
	 */
	public static function instance() {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof KB_Support ) ) {

			self::$instance = new KB_Support;
			self::$instance->setup_constants();

			add_action( 'plugins_loaded', array( self::$instance, 'load_textdomain' ) );

			self::$instance->includes();
			self::$instance->roles      = new KBS_Roles();

		}

		return self::$instance;

	}
	
	/**
	 * Throw error on object clone.
	 *
	 * The whole idea of the singleton design pattern is that there is a single
	 * object therefore, we don't want the object to be cloned.
	 *
	 * @since	0.1
	 * @access	protected
	 * @return	void
	 */
	public function __clone() {
		// Cloning instances of the class is forbidden.
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'kb-support' ), '0.1' );
	} // __clone

	/**
	 * Disable unserializing of the class.
	 *
	 * @since	0.1
	 * @access	protected
	 * @return	void
	 */
	public function __wakeup() {
		// Unserializing instances of the class is forbidden.
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'kb-support' ), '0.1' );
	} // __wakeup
	
	/**
	 * Setup plugin constants.
	 *
	 * @access	private
	 * @since	0.1
	 * @return	void
	 */
	private function setup_constants()	{

		if ( ! defined( 'KBS_VERSION' ) )	{
			define( 'KBS_VERSION', '0.1' );
		}

		if ( ! defined( 'KBS_PLUGIN_DIR' ) )	{
			define( 'KBS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
		}

		if ( ! defined( 'KBS_PLUGIN_DIR' ) )	{
			define( 'KBS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
		}
		
		if ( ! defined( 'KBS_PLUGIN_FILE' ) )	{
			define( 'KBS_PLUGIN_FILE', __FILE__ );
		}

	} // setup_constants
			
	/**
	 * Include required files.
	 *
	 * @access	private
	 * @since	0.1
	 * @return	void
	 */
	private function includes()	{

		global $kbs_options;

		//require_once KBS_PLUGIN_DIR . 'includes/admin/settings/register-settings.php';
		//$kbs_options = kbs_get_settings();

		require_once KBS_PLUGIN_DIR . 'includes/actions.php';

		if( file_exists( KBS_PLUGIN_DIR . 'includes/deprecated-functions.php' ) ) {
			require_once KBS_PLUGIN_DIR . 'includes/deprecated-functions.php';
		}

		require_once KBS_PLUGIN_DIR . '/includes/actions.php';
		require_once KBS_PLUGIN_DIR . '/includes/post-types.php';
		require_once KBS_PLUGIN_DIR . 'includes/class-kbs-roles.php';
		require_once KBS_PLUGIN_DIR . 'includes/kb-articles/kb-article-functions.php';
		require_once KBS_PLUGIN_DIR . 'includes/tickets/ticket-functions.php';

		if( is_admin() )	{
			require_once KBS_PLUGIN_DIR . '/includes/admin/admin-pages.php';
			require_once KBS_PLUGIN_DIR . '/includes/admin/tickets/tickets.php';
			require_once KBS_PLUGIN_DIR . '/includes/admin/kb-articles/kb-articles.php';
		} else	{
			
		}

		require_once KBS_PLUGIN_DIR . 'includes/install.php';
		
	} // includes
	
	/**
	 * Load the text domain for translations.
	 *
	 * @access	private
	 * @since	0.1
	 * @return	void
	 */
	public function load_textdomain()	{

		load_plugin_textdomain( 
			'kb-support',
			false, 
			dirname( plugin_basename(__FILE__) ) . '/languages/'
		);

	} // load_textdomain
	
} // class KB_Support
endif;

/**
 * The main function for that returns KB_Support
 *
 * The main function responsible for returning the one true KB_Support
 * Instance to functions everywhere.
 *
 * Use this function like you would a global variable, except without needing
 * to declare the global.
 *
 * Example: <?php $kbs = KBS(); ?>
 *
 * @since	0.1
 * @return	obj		KB_Support	The one true KB_Support Instance.
 */
function KBS()	{
	return KB_Support::instance();
} // KBS

// Get KBS Running
KBS();
