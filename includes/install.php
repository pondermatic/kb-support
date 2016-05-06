<?php
	defined( 'ABSPATH' ) or die( "Direct access to this page is disabled!!!" );
/**
 * The installation procedures for KBS
 * 
 * 
 *
 */
if( !class_exists( 'KBS_Install' ) ) :
	class KBS_Install	{
		/**
		 * Initialise and execute the install procedures
		 */
		public static function init()	{
			// User roles & capabilities
			self::register_roles_and_caps();
			
			add_option( KBS_VER_KEY, KBS_VER );			
		} // init

		
		/**
		 * Add the user roles and assign the capabilities
		 *
		 *
		 *
		 *
		 */
		public static function register_roles_and_caps()	{
			// Add the KBS Manager role			
			add_role(
				'kbs_manager', 
				__( 'Support Manager', 'kb-support' ),
				array(
					'read' => true, 
					'create_users' => true,
					'edit_users' => true,
					'delete_users' => true,
					'edit_posts' => false,
					'delete_posts' => false,
					'publish_posts' => false,
					'upload_files' => true
				)
			);
			// Add the KBS Agent role			
			add_role(
				'kbs_agent', 
				__( 'Support Agent', 'kb-support' ),
				array(
					'read' => true, 
					'create_users' => true,
					'edit_users' => true,
					'delete_users' => false,
					'edit_posts' => false,
					'delete_posts' => false,
					'publish_posts' => false,
					'upload_files' => true
				)
			);
			// Add the KBS Customer role			
			add_role(
				'kbs_customer', 
				__( 'Support Customer', 'kb-support' ),
				array(
					'read' => true, 
					'create_users' => false,
					'edit_users' => false,
					'delete_users' => false,
					'edit_posts' => false,
					'delete_posts' => false,
					'publish_posts' => false,
					'upload_files' => false
				)
			);
			
			$roles = array( 
				'kbs_manager'	=> array(
					'kbs_admin', 'kbs_user', 'edit_ticket', 'read_ticket', 'delete_ticket', 'edit_tickets',
					'edit_others_tickets', 'publish_tickets', 'read_private_tickets', 'edit_article',
					'read_article', 'delete_article', 'edit_articles', 'edit_others_articles',
					'publish_articles', 'read_private_articles'
				),
				'kbs_agent'		=> array(
					'kbs_user', 'edit_ticket', 'read_ticket', 'delete_ticket', 'edit_tickets', 'edit_others_tickets',
					'publish_tickets', 'read_private_tickets', 'edit_article', 'read_article',
					'delete_article', 'edit_articles', 'edit_others_articles', 'publish_articles',
					'read_private_articles'
				),
				'administrator' => array(
					'kbs_admin', 'kbs_user', 'edit_ticket', 'read_ticket', 'delete_ticket', 'edit_tickets',
					'edit_others_tickets', 'publish_tickets', 'read_private_tickets', 'edit_article',
					'read_article', 'delete_article', 'edit_articles', 'edit_others_articles',
					'publish_articles', 'read_private_articles'
				)
			);
			
			// Loop through roles assigning capabilities
			foreach( $roles as $the_role => $caps )	{ 
				$role = get_role( $the_role );
				
				foreach( $caps as $cap )	{
					$role->add_cap( $cap );	
				}
			}
		} // register_roles_and_caps		
	} // class KBS_Install
endif;
	KBS_Install::init();