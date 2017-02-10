<?php
/**
 * Companies DB class
 *
 * This class is for interacting with the companies database table
 *
 * @package     KBS
 * @subpackage  Classes/DB Companies
 * @copyright   Copyright (c) 2017, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * KBS_DB_Companies Class
 *
 * @since	1.0
 */
class KBS_DB_Companies extends KBS_DB  {

	/**
	 * Get things started
	 *
	 * @access	public
	 * @since	1.0
	*/
	public function __construct() {

		global $wpdb;

		$this->table_name  = $wpdb->prefix . 'kbs_companies';
		$this->primary_key = 'id';
		$this->version     = '1.0';

	} // __construct

	/**
	 * Get columns and formats
	 *
	 * @access	public
	 * @since	1.0
	*/
	public function get_columns() {
		return array(
			'id'           => '%d',
			'name'         => '%s',
			'email'        => '%s',
			'notes'        => '%s',
			'date_created' => '%s'
		);
	} // get_columns

	/**
	 * Get default column values
	 *
	 * @access	public
	 * @since	1.0
	*/
	public function get_column_defaults() {
		return array(
			'name'         => '',
			'email'        => '',
			'notes'        => '',
			'date_created' => date( 'Y-m-d H:i:s' )
		);
	} // get_column_defaults

	/**
	 * Add a company
	 *
	 * @access	public
	 * @since	1.0
	*/
	public function add( $data = array() ) {

		$defaults = array(
		);

		$args = wp_parse_args( $data, $defaults );
		$meta = array();

		if ( empty( $args['name'] ) ) {
			return false;
		}

		// Check for data that needs to be stored as meta.
		foreach ( $args as $key => $value )	{
			if ( ! array_key_exists( $key, $this->get_columns() ) )	{
				$meta[ $key ] = $value;
				unset( $args[ $key ] );
			}
		}

		$company = $this->get_company_by( 'name', $args['name'] );

		if ( $company ) {
			// Update an existing company

			if ( $this->update( $company->id, $args ) )	{

				foreach( $meta as $key => $value )	{
					KBS()->company_meta->update_meta( $company->id, $key, $value );
				}

			}

			return $company->id;

		} else {

			$return = $this->insert( $args, 'company' );

			if ( $return )	{
				foreach( $meta as $key => $value )	{
					KBS()->company_meta->update_meta( $return, $key, $value );
				}
			}

			return $return;

		}

	} // add

	/**
	 * Delete a company
	 *
	 * NOTE: This should not be called directly as it does not make necessary changes to
	 * the ticket meta and logs. Use kbs_company_delete() instead
	 *
	 * @access	public
	 * @since	1.0
	*/
	public function delete( $_id_or_name = false ) {

		if ( empty( $_id_or_name ) ) {
			return false;
		}

		$column   = is_numeric( $_id_or_name ) ? 'id' : 'name';
		$company  = $this->get_company_by( $column, $_id_or_name );

		if ( $company->id > 0 ) {

			global $wpdb;
			return $wpdb->delete( $this->table_name, array( 'id' => $company->id ), array( '%d' ) );

		} else {
			return false;
		}

	} // delete

	/**
	 * Checks if a company exists
	 *
	 * @access	public
	 * @since	1.0
	 * @param	mixed	$value	The value to search for.
	 * @param	str		$field	The field to search within.
	 */
	public function exists( $value = '', $field = 'name' ) {

		$columns = $this->get_columns();

		if ( ! array_key_exists( $field, $columns ) ) {
			return false;
		}

		return (bool) $this->get_column_by( 'id', $field, $value );

	} // exists

	/**
	 * Retrieves a single company from the database
	 *
	 * @access 	public
	 * @since	1.0
	 * @param	str		$field	id or name
	 * @param	mixed	$value  The Company ID or name to search
	 * @return	mixed	Upon success, an object of the company. Upon failure, NULL
	 */
	public function get_company_by( $field = 'id', $value = 0 ) {
		global $wpdb;

		if ( empty( $field ) || empty( $value ) ) {
			return NULL;
		}

		if ( 'id' == $field ) {
			// Make sure the value is numeric to avoid casting objects, for example,
			// to int 1.
			if ( ! is_numeric( $value ) ) {
				return false;
			}

			$value = intval( $value );

			if ( $value < 1 ) {
				return false;
			}

		} elseif ( 'name' === $field ) {
			$value = trim( $value );
		}

		if ( ! $value ) {
			return false;
		}

		switch ( $field ) {
			case 'id':
				$db_field = 'id';
				break;
			case 'name':
				$value    = sanitize_text_field( $value );
				$db_field = 'name';
				break;
			default:
				return false;
		}

		if ( ! $company = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $this->table_name WHERE $db_field = %s LIMIT 1", $value ) ) )	{
			return false;
		}

		return $company;
	} // get_company_by

	/**
	 * Retrieve companies from the database
	 *
	 * @access	public
	 * @since	1.0
	*/
	public function get_companies( $args = array() ) {

		global $wpdb;

		$defaults = array(
			'number'       => 20,
			'offset'       => 0,
			'id'           => 0,
			'orderby'      => 'name',
			'order'        => 'DESC',
		);

		$args  = wp_parse_args( $args, $defaults );

		if ( $args['number'] < 1 ) {
			$args['number'] = 999999999999;
		}

		$join  = '';
		$where = ' WHERE 1=1 ';

		// Specific companies
		if ( ! empty( $args['id'] ) ) {

			if ( is_array( $args['id'] ) ) {
				$ids = implode( ',', array_map('intval', $args['id'] ) );
			} else {
				$ids = intval( $args['id'] );
			}

			$where .= " AND `id` IN( {$ids} ) ";

		}

		// Specific companies by name
		if ( ! empty( $args['name'] ) ) {
			$where .= $wpdb->prepare( " AND `name` LIKE '%%%%" . '%s' . "%%%%' ", $args['name'] );
		}

		// Companies created for a specific date or in a date range
		if( ! empty( $args['date'] ) ) {

			if( is_array( $args['date'] ) ) {

				if( ! empty( $args['date']['start'] ) ) {

					$start = date( 'Y-m-d 00:00:00', strtotime( $args['date']['start'] ) );
					$where .= " AND `date_created` >= '{$start}'";

				}

				if( ! empty( $args['date']['end'] ) ) {

					$end = date( 'Y-m-d 23:59:59', strtotime( $args['date']['end'] ) );
					$where .= " AND `date_created` <= '{$end}'";

				}

			} else {

				$year  = date( 'Y', strtotime( $args['date'] ) );
				$month = date( 'm', strtotime( $args['date'] ) );
				$day   = date( 'd', strtotime( $args['date'] ) );

				$where .= " AND $year = YEAR ( date_created ) AND $month = MONTH ( date_created ) AND $day = DAY ( date_created )";
			}

		}

		$args['orderby'] = ! array_key_exists( $args['orderby'], $this->get_columns() ) ? 'id' : $args['orderby'];

		$cache_key = md5( 'kbs_companies_' . serialize( $args ) );

		$companies = wp_cache_get( $cache_key, 'companies' );

		$args['orderby'] = esc_sql( $args['orderby'] );
		$args['order']   = esc_sql( $args['order'] );

		if ( false === $companies ) {
			$query     = $wpdb->prepare( "SELECT * FROM  $this->table_name $join $where GROUP BY $this->primary_key ORDER BY {$args['orderby']} {$args['order']} LIMIT %d,%d;", absint( $args['offset'] ), absint( $args['number'] ) );
			$companies = $wpdb->get_results( $query );
			wp_cache_set( $cache_key, $companies, 'companies', 3600 );
		}

		return $companies;

	} // get_companies


	/**
	 * Count the total number of companies in the database
	 *
	 * @access	public
	 * @since	1.0
	*/
	public function count( $args = array() ) {

		global $wpdb;

		$join  = '';
		$where = ' WHERE 1=1 ';

		// Specific companies
		if ( ! empty( $args['id'] ) ) {

			if ( is_array( $args['id'] ) ) {
				$ids = implode( ',', array_map('intval', $args['id'] ) );
			} else {
				$ids = intval( $args['id'] );
			}

			$where .= " AND `id` IN( {$ids} ) ";

		}

		// Specific companies by name
		if ( ! empty( $args['name'] ) ) {
			$where .= $wpdb->prepare( " AND `name` LIKE '%%%%" . '%s' . "%%%%' ", $args['name'] );
		}

		// Companies created for a specific date or in a date range
		if ( ! empty( $args['date'] ) ) {

			if ( is_array( $args['date'] ) ) {

				if ( ! empty( $args['date']['start'] ) ) {

					$start = date( 'Y-m-d 00:00:00', strtotime( $args['date']['start'] ) );
					$where .= " AND `date_created` >= '{$start}'";

				}

				if ( ! empty( $args['date']['end'] ) ) {

					$end = date( 'Y-m-d 23:59:59', strtotime( $args['date']['end'] ) );
					$where .= " AND `date_created` <= '{$end}'";

				}

			} else {

				$year  = date( 'Y', strtotime( $args['date'] ) );
				$month = date( 'm', strtotime( $args['date'] ) );
				$day   = date( 'd', strtotime( $args['date'] ) );

				$where .= " AND $year = YEAR ( date_created ) AND $month = MONTH ( date_created ) AND $day = DAY ( date_created )";
			}

		}

		$cache_key = md5( 'kbs_companies_count' . serialize( $args ) );

		$count = wp_cache_get( $cache_key, 'companies' );

		if ( $count === false ) {
			$query = "SELECT COUNT($this->primary_key) FROM " . $this->table_name . "{$join} {$where};";
			$count = $wpdb->get_var( $query);
			wp_cache_set( $cache_key, $count, 'companies', 3600 );
		}

		return absint( $count );

	} // count

	/**
	 * Create the table
	 *
	 * @access	public
	 * @since	1.0
	 */
	public function create_table() {

		global $wpdb;

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		$sql = "CREATE TABLE " . $this->table_name . " (
		id bigint(20) NOT NULL AUTO_INCREMENT,
		name mediumtext NOT NULL,
		email varchar(50) NOT NULL,
		notes longtext NOT NULL,
		date_created datetime NOT NULL,
		PRIMARY KEY  (id),
		UNIQUE KEY email (email)
		) CHARACTER SET utf8 COLLATE utf8_general_ci;";

		dbDelta( $sql );

		update_option( $this->table_name . '_db_version', $this->version );
	} // create_table

} // KBS_DB_Companies
