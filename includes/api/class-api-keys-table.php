<?php
/**
 * API Key Table Class
 *
 * @package     KBS
 * @subpackage  Admin/Tools/API Keys
 * @copyright   Copyright (c) 2020, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.5
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Load WP_List_Table if not loaded
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * KBS_API_Keys_Table Class
 *
 * Renders the API Keys table
 *
 * @since   1.5
 */
class KBS_API_Keys_Table extends WP_List_Table {

    /**
	 * @var    int     Number of items per page
	 * @since  1.5
	 */
	public $per_page = 30;

	/**
	 * Get things started
	 *
	 * @since  1.5
	 * @see    WP_List_Table::__construct()
	 */
	public function __construct() {
		parent::__construct( array(
			'singular'  => __( 'API Key',  'kb-support' ),
			'plural'    => __( 'API Keys', 'kb-support' ),
			'ajax'      => false
		) );

		$this->query();
	} // __construct

	/**
	 * Gets the name of the primary column.
	 *
	 * @since  1.5
	 * @access protected
	 *
	 * @return string  Name of the primary column.
	 */
	protected function get_primary_column_name() {
		return 'user';
	} // get_primary_column_name

	/**
	 * This function renders most of the columns in the list table.
	 *
	 * @since  1.5
	 *
	 * @param  array   $item   Contains all the data of the keys
	 * @param  string   $column_name  The name of the column
	 * @return string  Column Name
	 */
	public function column_default( $item, $column_name ) {
		return $item[ $column_name ];
	} // column_default

	/**
	 * Displays the public key rows
	 *
	 * @since  1.5
	 *
	 * @param  array   $item           Contains all the data of the keys
	 * @param  string  $column_name    The name of the column
	 * @return string  Column Name
	 */
	public function column_key( $item ) {
		return sprintf(
            '<input readonly="readonly" type="text" class="code" value="%s"/>',
            esc_attr( $item[ 'key' ] )
        );
	} // column_key

	/**
	 * Displays the token rows
	 *
	 * @since  1.5
	 *
	 * @param  array   $item           Contains all the data of the keys
	 * @param  string  $column_name    The name of the column
	 * @return string  Column Name
	 */
	public function column_token( $item ) {
        return sprintf(
            '<input readonly="readonly" type="text" class="code" value="%s"/>',
            esc_attr( $item[ 'token' ] )
        );
	} // column_token

	/**
	 * Displays the secret key rows
	 *
	 * @since  1.5
	 *
	 * @param  array   $item Contains all the data of the keys
	 * @param  string  $column_name The name of the column
	 *
	 * @return string  Column Name
	 */
	public function column_secret( $item ) {
        return sprintf(
            '<input readonly="readonly" type="text" class="code" value="%s"/>',
            esc_attr( $item[ 'secret' ] )
        );
	} // column_secret

	/**
	 * Renders the column for the user field
	 *
	 * @since  1.5
	 * @return void
	 */
	public function column_user( $item ) {

		$actions = array();

		$actions['reissue'] = sprintf(
			'<a href="%s" class="kbs-regenerate-api-key">%s</a>',
			esc_url( wp_nonce_url( add_query_arg( array(
				'user_id'         => $item['id'],
				'kbs_action'      => 'process_api_key',
				'kbs_api_process' => 'regenerate'
			) ), 'kbs-api-nonce' ) ),
			__( 'Reissue', 'kb-support' )
		);

		$actions['revoke'] = sprintf(
			'<a href="%s" class="kbs-revoke-api-key kbs-delete">%s</a>',
			esc_url( wp_nonce_url( add_query_arg( array(
				'user_id'         => $item['id'],
				'kbs_action'      => 'process_api_key',
				'kbs_api_process' => 'revoke'
			) ), 'kbs-api-nonce' ) ),
			__( 'Revoke', 'kb-support' )
		);

		$actions = apply_filters( 'kbs_api_row_actions', array_filter( $actions ) );

		return sprintf( '%1$s %2$s', $item['user'], $this->row_actions( $actions ) );
	} // column_user

	/**
	 * Retrieve the table columns
	 *
	 * @since  1.5
	 * @return array   $columns    Array of all the list table columns
	 */
	public function get_columns() {
		return array(
			'user'   => __( 'Username',   'kb-support' ),
			'key'    => __( 'Public Key', 'kb-support' ),
			'token'  => __( 'Token',      'kb-support' ),
			'secret' => __( 'Secret Key', 'kb-support' )
		);
	} // get_columns

	/**
	 * Display the key generation form
	 *
	 * @since  1.5
	 * @return void
	 */
	public function bulk_actions( $which = '' ) {
		static $kbs_api_is_bottom = false;

		if ( true === $kbs_api_is_bottom ) {
			return;
		}

		if ( 'top' !== $which ) {
			return;
		}

		$kbs_api_is_bottom = true; ?>

		<form id="api-key-generate-form" method="post" action="<?php echo admin_url( 'edit.php?post_type=kbs_ticket&page=kbs-tools&tab=api_keys' ); ?>">
			<input type="hidden" name="kbs_action" value="process_api_key" />
			<input type="hidden" name="kbs_api_process" value="generate" />
			<?php wp_nonce_field( 'kbs-api-nonce' ); ?>

            <?php echo KBS()->html->ajax_user_search(); ?>

            <?php submit_button( __( 'Generate New API Keys', 'kb-support' ), 'secondary', 'submit', false ); ?>
		</form>

		<?php
	} // bulk_actions

	/**
	 * Generate the table navigation above or below the table
	 *
	 * @since  1.5
	 * @access protected
	 * @param  string  $which  Top or bottom
	 */
	protected function display_tablenav( $which ) {

		if ( 'top' === $which ) {
			wp_nonce_field( 'bulk-' . $this->_args['plural'] );
		} ?>

		<div class="tablenav <?php echo esc_attr( $which ); ?>">

			<div class="alignleft actions bulkactions">
				<?php $this->bulk_actions( $which ); ?>
			</div><?php

			$this->extra_tablenav( $which );
			$this->pagination( $which );

			?><br class="clear" />
		</div>

		<?php
	} // display_tablenav

    /**
	 * Retrieve the current page number
	 *
	 * @since  1.5
	 * @return int Current page number
	 */
	public function get_paged() {
		return isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 1;
	}

	/**
	 * Performs the key query
	 *
	 * @since  1.5
	 * @return void
	 */
	public function query() {
        $keys  = array();
		$users = get_users( array(
			'meta_value' => 'kbs_user_secret_key',
			'number'     => $this->per_page,
			'offset'     => $this->per_page * ( $this->get_paged() - 1 )
		) );

		foreach( $users as $user ) {
			$keys[$user->ID]['id']     = $user->ID;
			$keys[$user->ID]['email']  = $user->user_email;
			$keys[$user->ID]['user']   = '<a href="' . add_query_arg( 'user_id', $user->ID, 'user-edit.php' ) . '"><strong>' . $user->user_login . '</strong></a>';

			$keys[$user->ID]['key']    = KBS()->api->get_user_public_key( $user->ID );
			$keys[$user->ID]['secret'] = KBS()->api->get_user_secret_key( $user->ID );
			$keys[$user->ID]['token']  = KBS()->api->get_token( $user->ID );
		}

		return $keys;
	} // query

	/**
	 * Retrieve count of total users with keys
	 *
	 * @since  1.5
	 * @return int
	 */
	public function total_items() {
		global $wpdb;

		if ( false === get_transient( 'kbs_total_api_keys' ) ) {
			$total_items = $wpdb->get_var( "SELECT count(user_id) FROM {$wpdb->usermeta} WHERE meta_value='kbs_user_secret_key'" );

			set_transient( 'kbs_total_api_keys', $total_items, 60 * 60 );
		}

		return get_transient( 'kbs_total_api_keys' );
	} // total_items

	/**
	 * Setup the final data for the table
	 *
	 * @since  1.5
	 * @return void
	 */
	public function prepare_items() {
		$columns = $this->get_columns();

		$hidden = array(); // No hidden columns
		$sortable = array(); // Not sortable... for now

		$this->_column_headers = array( $columns, $hidden, $sortable, 'user' );

		$data = $this->query();

		$total_items = $this->total_items();

		$this->items = $data;

		$this->set_pagination_args( array(
				'total_items' => $total_items,
				'per_page'    => $this->per_page,
				'total_pages' => ceil( $total_items / $this->per_page ),
			)
		);
	} // prepare_items
}
