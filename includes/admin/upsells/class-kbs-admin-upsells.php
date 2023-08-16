<?php

class KBS_Admin_Upsells	{

	/**
	 * Get things going.
	 */
	public function __construct()	{

		// Upgrade to PRO plugin action link
		add_filter( 'admin_enqueue_scripts', array( $this, 'include_admin_style' ), 60 );
        add_filter( 'plugin_action_links_' . plugin_basename( KBS_PLUGIN_FILE ), array( $this, 'filter_action_links' ), 60 );
        add_action( 'admin_menu', array( $this, 'add_lite_vs_pro_page' ), 120 );
        
	}

    /**
	 * Add the Upgrade to PRO plugin action link
	 *
	 * @param $links
	 *
	 * @return array
	 *
	 * @since 1.5.91
	 */
	public function filter_action_links( $links ) {

		$upgrade = apply_filters( 'kb_support_upgrade_plugin_action', array(
				'upgrade_available' => true,
				'link'              => '<a  target="_blank" class="kbs-upgrate-to-pro" href="https://kb-support.com/pricing/?utm_source=kb-support&utm_medium=plugin_settings&utm_campaign=upsell">' . esc_html__( 'Upgrade to PRO!', 'kb-support' ) . '</a>'
		) );

		if ( ! $upgrade['upgrade_available'] ) {
			return $links;
		}

		array_unshift( $links, $upgrade['link'] );

		return $links;
	}

    /**
	 * Adds plugins.php to the list of pages where we should enqueue admin css
	 *
	 * @param $admin_pages
	 *
	 * @return array
	 *
	 * @since 1.5.91
	 */
	public function include_admin_style( $admin_pages ){

		$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
		wp_enqueue_style( 'kbs-admin', KBS_PLUGIN_URL . '/assets/css/kbs-admin' . $suffix . '.css', array(), KBS_VERSION );

    }

	/**
	 * Add lite vs pro page in menu
	 *
	 * @param [type] $links
	 * @return void
	 */
	public function add_lite_vs_pro_page() {

        add_submenu_page( 'edit.php?post_type=kbs_ticket', __( 'LITE vs Premium', 'kb-support' ), __( 'LITE vs Premium', 'kb-support' ), 'manage_options', 'kbs-lite-vs-pro', array( $this, 'lits_vs_pro_page' ), 200 );

	}

    /**
	 * The LITE vs PRO page
	 *
	 * @return void
	 */
	public function lits_vs_pro_page() {

		require_once __DIR__ . '/lite-vs-pro-page.php';
	}
}

new KBS_Admin_Upsells();