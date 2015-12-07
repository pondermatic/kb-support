<?php
	defined( 'ABSPATH' ) or die( "Direct access to this page is disabled!!!" );
	
/**
 * Manage the metaboxes for the KBS custom post types
 *
 *
 *
 *
 */
if( !class_exists( 'KBS_Metaboxes' ) ) :
	class KBS_Metaboxes	{
		// Hold the metabox settings array
		private $kbs_boxes;
		
		/**
		 * Class constructor
		 *
		 * @params	arr		$args		Array of settings to define the metabox and fields.
		 *						'id' (str)			=> HTML 'id' attribute of the edit screen section
		 *						'title' (str)		=> Title of the edit screen section, visible to user
		 *						'post_type' (str)	=> The post type for which the metabox needs to be applied
		 *						'context' (str)		=> The section where it's shown ('normal', 'advanced', or 'side')
		 *						'priority  (str)	=> Priority where the boxes should show ('high', 'core', 'default' or 'low')
		 *						'args' (arr)		=> Arguments that should be passed to the metabox function
		 *
		 * @return	void
		 */
		public function __construct( $args )	{
			$this->kbs_boxes = $args;
			
			// Hook into the WP plugins_loaded action
			add_action( 'plugins_loaded', array( $this, 'init' ) );
		} // __construct
		
		/**
		 * The init method is where we hook into the WP add_meta_boxes action
		 *
		 * @params
		 *
		 * @return
		 */
		public function init()	{
			add_action( 'add_meta_boxes', array( $this, 'setup_metaboxes' ) );
		} // init
		
		/**
		 * Add the metaboxes and pass through the args to the callback method
		 * so we display the desired output.
		 *
		 * @called	hook	add_meta_boxes	WP hook for adding custom metaboxes
		 *
		 * @params
		 *
		 * @return
		 */
		public function setup_metaboxes()	{
			foreach( $this->kbs_boxes as $kbs_box )	{
				add_meta_box(
					$kbs_box['id'],
					$kbs_box['title'],
					array( $this, 'callback' ),
					$kbs_box['post_type'],
					!empty( $kbs_box['context'] ) ? $kbs_box['context'] : 'normal',
					!empty( $kbs_box['priority'] ) ? $kbs_box['priority'] : 'default',
					$kbs_box['args']
				);
			}
		} // setup_metaboxes
		
		/**
		 * Add the metaboxes and pass through the args to the callback method
		 * so we display the desired output.
		 * Determine the field type for each field and call the relevant method to display it.
		 *
		 * @params	obj		$post	The WP_Post object
		 *			arr		$args	The arguments passed to the method via setup_metaboxes->$kbs_box['args']
		 *
		 * @return
		 */
		public function callback( $post, $args )	{
			foreach( $args['args'] as $settings )	{
				$this->display_field( $settings, $post );
			}
		} // callback
		
		/**
		 * Determine the field to be displayed and call the required method to display the output.
		 *
		 * @params	arr		$settings	The field settings
		 *			obj		$post		The WP_Post object
		 *
		 * @return	void
		 */
		function display_field( $settings, $post )	{
			switch( $settings['field'] )	{
				case 'text':
					echo 'test';
				break;	
			}
		} // display_field
	} // class KBS_Metaboxes
endif;