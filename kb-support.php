<?php
	defined( 'ABSPATH' ) or die( "Direct access to this page is disabled!!!" );
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
 */
/**
   KB Support is free software; you can redistribute it and/or modify
   it under the terms of the GNU General Public License, version 2, as 
   published by the Free Software Foundation.

   KB Support is distributed in the hope that it will be useful,
   but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
   GNU General Public License for more details.

   You should have received a copy of the GNU General Public License
   along with KB Support; if not, see https://www.gnu.org/licenses/gpl-2.0.html
 */
/**
 * Class: KB_Support
 * Description: The main KB Support (singleton) class
 *
 *
 */
 
if ( ! class_exists( 'KB_Support' ) ) :
	class KB_Support	{
		private static $instance;
		/**
		 * Execute actions during 'plugins_loaded' hook
		 *
		 *
		 *
		 */
		public static function load_textdomain()	{
			// Load the text domain for translations
			load_plugin_textdomain( 
				'kb-support',
				false, 
				dirname( plugin_basename(__FILE__) ) . '/languages/' );
		} // load_textdomain
		
		/**
		 * Let's ensure we only have one instance of KBS loaded into memory at any time
		 *
		 *
		 *
		 * @return The one true KB_Support
		 */
		public static function instance()	{

			if( ! isset( self::$instance ) && ! ( self::$instance instanceof KB_Support ) ) {
				self::$instance = new KB_Support();
				
				self::$instance->define_constants();
				self::$instance->includes();
				
				add_action( 'plugins_loaded', array( __CLASS__, 'load_textdomain' ) );
								
			}

			return self::$instance;

		} // instance
		
		/**
		 * Throw error on object clone
		 *
		 * The whole idea of the singleton design pattern is that there is a single
		 * object therefore, we don't want the object to be cloned.
		 *
		 * 
		 * @access protected
		 * @return void
		 */
		public function __clone() {
			// Cloning instances of the class is forbidden
			_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'kb-support' ), '0.1' );
		} // __clone
		
		/**
		 * Define constants
		 *
		 *
		 *
		 */
		public function define_constants()	{
			define( 'KBS_VER', '0.1' );
			define( 'KBS_PLUGIN_DIR', untrailingslashit( dirname( __FILE__ ) ) );
			define( 'KBS_PLUGIN_URL', untrailingslashit( plugins_url( '', __FILE__ ) ) );
		} // define_constants
				
		/**
		 * Files for inclusion
		 *
		 *
		 *
		 */
		public function includes()	{

			require_once( KBS_PLUGIN_DIR . '/includes/post-types.php' );
			require_once( KBS_PLUGIN_DIR . '/includes/actions.php' );

			if( is_admin() )	{
				require_once( KBS_PLUGIN_DIR . '/includes/admin/admin-pages.php' );
				require_once( KBS_PLUGIN_DIR . '/includes/admin/tickets/tickets.php' );
			}
			
		} // includes
	} //class  KB_Support
endif;

/**
 * Instantiate the KBS_Support singleton class and return the one and only instance
 *
 *
 *
 */
function KBS()	{
	return KB_Support::instance();
}

KBS();