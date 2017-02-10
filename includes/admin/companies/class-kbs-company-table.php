<?php
/**
 * Company Table Class
 *
 * @package     KBS
 * @subpackage  Admin/Companies
 * @copyright   Copyright (c) 2017, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

// Load WP_List_Table if not loaded
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * KBS_Company_Table Class
 *
 * Renders the Customer Reports table
 *
 * @since	1.0
 */
class KBS_Company_Table extends WP_List_Table {

	/**
	 * Number of items per page
	 *
	 * @var		int
	 * @since	1.0
	 */
	public $per_page = 30;

	/**
	 * Number of companies found
	 *
	 * @var		int
	 * @since	1.0
	 */
	public $count = 0;

	/**
	 * Total companies
	 *
	 * @var	int
	 * @since	1.0
	 */
	public $total = 0;

	/**
	 * The arguments for the data set
	 *
	 * @var		arr
	 * @since	1.0
	 */
	public $args = array();

	/**
	 * Get things started
	 *
	 * @since	1.0
	 * @see WP_List_Table::__construct()
	 */
	public function __construct() {
		global $status, $page;

		// Set parent defaults
		parent::__construct( array(
			'singular' => __( 'Company', 'kb-support' ),
			'plural'   => __( 'Companies', 'kb-support' ),
			'ajax'     => false,
		) );
	} // __construct

	/**
	 * Show the search field
	 *
	 * @since	1.0
	 * @access	public
	 *
	 * @param	str		$text		Label for the search box
	 * @param	str		$input_id	ID of the search box
	 * @return void
	 */
	public function search_box( $text, $input_id ) {
		$input_id = $input_id . '-search-input';

		if ( ! empty( $_REQUEST['orderby'] ) ) : ?>
			<input type="hidden" name="orderby" value="<?php echo esc_attr( $_REQUEST['orderby'] ); ?>" />
        <?php endif;

		if ( ! empty( $_REQUEST['order'] ) ) : ?>
			<input type="hidden" name="order" value="<?php echo esc_attr( $_REQUEST['order'] ); ?>" />
		<?php endif; ?>

		<p class="search-box">
			<label class="screen-reader-text" for="<?php echo $input_id ?>"><?php echo $text; ?>:</label>
			<input type="search" id="<?php echo $input_id ?>" name="s" value="<?php _admin_search_query(); ?>" />
			<?php submit_button( $text, 'button', false, false, array('ID' => 'search-submit') ); ?>
		</p>
		<?php
	} // search_box

	/**
	 * Gets the name of the primary column.
	 *
	 * @since	1.0
	 * @access	protected
	 *
	 * @return	str		Name of the primary column.
	 */
	protected function get_primary_column_name() {
		return 'name';
	} // get_primary_column_name

	/**
	 * This function renders most of the columns in the list table.
	 *
	 * @access	public
	 * @since	1.0
	 *
	 * @param	arr		$item			Contains all the data of the companies
	 * @param	str		$column_name	The name of the column
	 *
	 * @return string Column Name
	 */
	public function column_default( $item, $column_name ) {
		switch ( $column_name ) {

			case 'num_tickets' :
				$value = '<a href="' .
					admin_url( '/edit.php?post_type=kbs_ticket&company=' . urlencode( $item['id'] )
				) . '">' . esc_html( $item['num_tickets'] ) . '</a>';
				break;

			case 'date_created' :
				$value = date_i18n( get_option( 'date_format' ), strtotime( $item['date_created'] ) );
				break;

			default:
				$value = isset( $item[ $column_name ] ) ? $item[ $column_name ] : null;
				break;
		}

		return apply_filters( 'kbs_companies_column_' . $column_name, $value, $item['id'] );
	} // column_default

	public function column_name( $item ) {
		$name        = ! empty( $item['name'] ) ? $item['name'] : '<em>' . __( 'Unnamed Company','kb-support' ) . '</em>';
		$view_url    = admin_url( 'edit.php?post_type=kbs_ticket&page=kbs-companies&view=companydata&id=' . $item['id'] );
		$actions     = array(
			'view'   => '<a href="' . $view_url . '">' . __( 'View', 'kb-support' ) . '</a>',
			'delete' => '<a href="' . admin_url( 'edit.php?post_type=kbs_ticket&page=kbs-companies&view=delete&id=' . $item['id'] ) . '">' . __( 'Delete', 'kb-support' ) . '</a>'
		);

		$company = new KBS_Company( $item['id'] );

		return '<a href="' . esc_url( $view_url ) . '">' . $name . '</a>' . $this->row_actions( $actions );
	} // column_name

	/**
	 * Retrieve the table columns
	 *
	 * @access	public
	 * @since	1.0
	 * @return	arr		$columns	Array of all the list table columns
	 */
	public function get_columns() {
		$columns = array(
			'name'          => __( 'Name', 'kb-support' ),
			'email'         => __( 'Primary Email', 'kb-support' ),
			'num_tickets'   => kbs_get_ticket_label_plural(),
			'date_created'  => __( 'Date Created', 'kb-support' ),
		);

		return apply_filters( 'kbs_report_company_columns', $columns );
	} // get_columns

	/**
	 * Get the sortable columns
	 *
	 * @access	public
	 * @since	1.0
	 * @return	arr		Array of all the sortable columns
	 */
	public function get_sortable_columns() {
		$sortable = array(
			'date_created' => array( 'date_created', true ),
			'name'         => array( 'name', true ),
			'num_tickets'  => array( 'ticket_count', false )
		);

		return apply_filters( 'kbs_company_table_sortable_columns', $sortable );
	} // get_sortable_columns

	/**
	 * Outputs the reporting views
	 *
	 * @access	public
	 * @since	1.0
	 * @return	void
	 */
	public function bulk_actions( $which = '' ) {
	} // bulk_actions

	/**
	 * Retrieve the current page number
	 *
	 * @access	public
	 * @since	1.0
	 * @return	int		Current page number
	 */
	public function get_paged() {
		return isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 1;
	} // get_paged

	/**
	 * Retrieves the search query string
	 *
	 * @access	public
	 * @since	1.0
	 * @return	mixed	String if search is present, false otherwise
	 */
	public function get_search() {
		return ! empty( $_GET['s'] ) ? urldecode( trim( $_GET['s'] ) ) : false;
	} // get_search

	/**
	 * Build all the reports data
	 *
	 * @access	public
	 * @since	1.0
	 * @global	obj		$wpdb			Used to query the database using the WordPress
	 * @return	arr		$reports_data	All the data for company reports
	 */
	public function reports_data() {
		global $wpdb;

		$data    = array();
		$paged   = $this->get_paged();
		$offset  = $this->per_page * ( $paged - 1 );
		$search  = $this->get_search();
		$order   = isset( $_GET['order'] )   ? sanitize_text_field( $_GET['order'] )   : 'DESC';
		$orderby = isset( $_GET['orderby'] ) ? sanitize_text_field( $_GET['orderby'] ) : 'id';

		$args    = array(
			'number'  => $this->per_page,
			'offset'  => $offset,
			'order'   => $order,
			'orderby' => $orderby
		);

		if ( is_email( $search ) ) {
			$args['email']   = $search;
		} elseif ( is_numeric( $search ) ) {
			$args['id']      = $search;
		} else {
			$args['name']    = $search;
		}

		$this->args = $args;
		$companies  = KBS()->companies->get_companies( $args );

		if ( $companies ) {
			foreach ( $companies as $company ) {

				$data[] = array(
					'id'            => $company->id,
					'name'          => $company->name,
					'email'         => $company->email,
					'num_tickets'   => $company->ticket_count,
					'date_created'  => $company->date_created,
				);
			}
		}

		return $data;
	} // reports_data

	/**
	 * Setup the final data for the table.
	 *
	 * @access	public
	 * @since	1.0
	 * @uses	KBS_Company_Table::get_columns()
	 * @uses	WP_List_Table::get_sortable_columns()
	 * @uses	KBS_Company_Table::get_pagenum()
	 * @uses	KBS_Company_Table::get_total_companies()
	 * @return	void
	 */
	public function prepare_items() {
		$columns  = $this->get_columns();
		$hidden   = array(); // No hidden columns
		$sortable = $this->get_sortable_columns();

		$this->_column_headers = array( $columns, $hidden, $sortable );

		$this->items = $this->reports_data();

		$this->total = kbs_count_total_companies( $this->args );

		$this->set_pagination_args( array(
			'total_items' => $this->total,
			'per_page'    => $this->per_page,
			'total_pages' => ceil( $this->total / $this->per_page ),
		) );
	} // prepare_items
} // KBS_Company_Table
