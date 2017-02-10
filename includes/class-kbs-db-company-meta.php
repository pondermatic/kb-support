<?php
/**
 * Company Meta DB class
 *
 * This class is for interacting with the company meta database table
 *
 * @package		KBS
 * @subpackage	Classes/DB_Company_Meta
 * @copyright   Copyright (c) 2017, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

class KBS_DB_Company_Meta extends KBS_DB {

	/**
	 * Get things started
	 *
	 * @access	public
	 * @since	1.0
	*/
	public function __construct() {
		global $wpdb;

		$this->table_name  = $wpdb->prefix . 'kbs_companymeta';
		$this->primary_key = 'meta_id';
		$this->version     = '1.0';

		add_action( 'plugins_loaded', array( $this, 'register_table' ), 11 );

	} // __construct

	/**
	 * Get table columns and data types
	 *
	 * @access	public
	 * @since	1.0
	*/
	public function get_columns() {
		return array(
			'meta_id'         => '%d',
			'kbs_company_id'  => '%d',
			'meta_key'        => '%s',
			'meta_value'      => '%s',
		);
	} // get_columns

	/**
	 * Register the table with $wpdb so the metadata api can find it
	 *
	 * @access	public
	 * @since	1.0
	*/
	public function register_table() {
		global $wpdb;
		$wpdb->kbs_companymeta = $this->table_name;
	} // register_table

	/**
	 * Retrieve company meta field for a company.
	 *
	 * For internal use only. Use KBS_Company->get_meta() for public usage.
	 *
	 * @param	int		$company_id		Company ID.
	 * @param	str		$meta_key		The meta key to retrieve.
	 * @param	bool	$single			Whether to return a single value.
	 * @return	mixed	Will be an array if $single is false. Will be value of meta data field if $single is true.
	 *
	 * @access	public
	 * @since	1.0
	 */
	public function get_meta( $company_id = 0, $meta_key = '', $single = false ) {
		$company_id = $this->sanitize_company_id( $company_id );
		if ( false === $company_id ) {
			return false;
		}

		return get_metadata( 'kbs_company', $company_id, $meta_key, $single );
	} // get_meta

	/**
	 * Add meta data field to a company.
	 *
	 * For internal use only. Use KBS_Company->add_meta() for public usage.
	 *
	 * @param	int		$company_id		Company ID.
	 * @param	str		$meta_key		Metadata name.
	 * @param	mixed	$meta_value		Metadata value.
	 * @param	bool	$unique			Optional, default is false. Whether the same key should not be added.
	 * @return	bool	False for failure. True for success.
	 *
	 * @access	private
	 * @since	1.0
	 */
	public function add_meta( $company_id = 0, $meta_key = '', $meta_value, $unique = false ) {
		$company_id = $this->sanitize_company_id( $company_id );
		if ( false === $company_id ) {
			return false;
		}

		return add_metadata( 'kbs_company', $company_id, $meta_key, $meta_value, $unique );
	} // add_meta

	/**
	 * Update company meta field based on company ID.
	 *
	 * For internal use only. Use KBS_Company->update_meta() for public usage.
	 *
	 * Use the $prev_value parameter to differentiate between meta fields with the
	 * same key and Company ID.
	 *
	 * If the meta field for the company does not exist, it will be added.
	 *
	 * @param	int		$company_id		Company ID.
	 * @param	str		$meta_key		Metadata key.
	 * @param	mixed	$meta_value		Metadata value.
	 * @param	mixed	$prev_value		Optional. Previous value to check before removing.
	 * @return	bool	False on failure, true if success.
	 *
	 * @access	private
	 * @since	1.0
	 */
	public function update_meta( $company_id = 0, $meta_key = '', $meta_value, $prev_value = '' ) {
		$company_id = $this->sanitize_company_id( $company_id );
		if ( false === $company_id ) {
			return false;
		}

		return update_metadata( 'kbs_company', $company_id, $meta_key, $meta_value, $prev_value );
	} // update_meta

	/**
	 * Remove metadata matching criteria from a company.
	 *
	 * For internal use only. Use KBS_Ccompany->delete_meta() for public usage.
	 *
	 * You can match based on the key, or key and value. Removing based on key and
	 * value, will keep from removing duplicate metadata with the same key. It also
	 * allows removing all metadata matching key, if needed.
	 *
	 * @param	int		$company_id		Company ID.
	 * @param	str		$meta_key		Metadata name.
	 * @param	mixed	$meta_value		Optional. Metadata value.
	 * @return	bool	False for failure. True for success.
	 *
	 * @access	private
	 * @since	1.0
	 */
	public function delete_meta( $company_id = 0, $meta_key = '', $meta_value = '' ) {
		return delete_metadata( 'kbs_company', $company_id, $meta_key, $meta_value );
	} // delete_meta

	/**
	 * Create the table
	 *
	 * @access	public
	 * @since	1.0
	*/
	public function create_table() {

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		$sql = "CREATE TABLE {$this->table_name} (
			meta_id bigint(20) NOT NULL AUTO_INCREMENT,
			kbs_company_id bigint(20) NOT NULL,
			meta_key varchar(255) DEFAULT NULL,
			meta_value longtext,
			PRIMARY KEY  (meta_id),
			KEY kbs_company_id (kbs_company_id),
			KEY meta_key (meta_key)
			) CHARACTER SET utf8 COLLATE utf8_general_ci;";

		dbDelta( $sql );

		update_option( $this->table_name . '_db_version', $this->version );
	} // create_table

	/**
	 * Given a company ID, make sure it's a positive number, greater than zero before inserting or adding.
	 *
	 * @since	1.0
	 * @param	int|str		$company_id		A passed company ID.
	 * @return	int|bool	The normalized company ID or false if it's found to not be valid.
	 */
	private function sanitize_company_id( $company_id ) {
		if ( ! is_numeric( $company_id ) ) {
			return false;
		}

		$company_id = (int) $company_id;

		// We were given a non positive number
		if ( absint( $company_id ) !== $company_id ) {
			return false;
		}

		if ( empty( $company_id ) ) {
			return false;
		}

		return absint( $company_id );

	} // sanitize_company_id

} // KBS_DB_Company_Meta
