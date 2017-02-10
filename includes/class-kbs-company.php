<?php
/**
 * Company Object
 *
 * @package     KBS
 * @subpackage  Classes/Company
 * @copyright   Copyright (c) 2017, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * KBS_Company Class
 *
 * @since	1.0
 */
class KBS_Company {

	/**
	 * The company ID
	 *
	 * @since 1.0
	 */
	public $id = 0;

	/**
	 * The company name
	 *
	 * @since 1.0
	 */
	public $name;

	/**
	 * The primary company email
	 *
	 * @since 1.0
	 */
	public $email;

	/**
	 * The company website
	 *
	 * @since 1.0
	 */
	public $website;

	/**
	 * The company logo
	 *
	 * @since 1.0
	 */
	public $logo;

	/**
	 * The company creation date
	 *
	 * @since 1.0
	 */
	public $date_created;

	/**
	 * Company Notes
	 *
	 * @since	1.0
	 */
	public $notes;

	/**
	 * The Database Abstraction
	 *
	 * @since  1.0
	 */
	protected $db;

	/**
	 * Get things going
	 *
	 * @since 1.0
	 */
	public function __construct( $_id_or_name = false ) {

		$this->db = new KBS_DB_Companies;

		if ( false === $_id_or_name || ( is_numeric( $_id_or_name ) && (int) $_id_or_name !== absint( $_id_or_name ) ) ) {
			return false;
		}

		if ( is_numeric( $_id_or_name ) ) {
			$field = 'id';
		} else {
			$field = 'name';
		}

		$company = $this->db->get_company_by( $field, $_id_or_name );

		if ( empty( $company ) || ! is_object( $company ) ) {
			return false;
		}

		$this->setup_company( $company );

	} // __construct

	/**
	 * Given the company data, let's set the variables
	 *
	 * @since	1.0
	 * @param	obj		$company	The Ccompany Object
	 * @return 	bool	If the setup was successful or not
	 */
	private function setup_company( $company ) {

		if ( ! is_object( $company ) ) {
			return false;
		}

		foreach ( $company as $key => $value ) {

			switch ( $key ) {

				case 'notes':
					$this->$key = $this->get_notes();
					break;

				default:
					$this->$key = $value;
					break;

			}

		}

		$this->logo    = $this->get_meta( 'logo', true );
		$this->website = $this->get_meta( 'website', true );

		// Ccompany ID and name are the only things that are necessary, make sure they exist
		if ( ! empty( $this->id ) && ! empty( $this->name ) ) {
			return true;
		}

		return false;

	} // setup_company

	/**
	 * Magic __get function to dispatch a call to retrieve a private property
	 *
	 * @since 1.0
	 */
	public function __get( $key ) {

		if ( method_exists( $this, 'get_' . $key ) ) {

			return call_user_func( array( $this, 'get_' . $key ) );

		} else {

			return new WP_Error( 'kbs-company-invalid-property', sprintf( __( "Can't get property %s", 'kb-support' ), $key ) );

		}

	} // __get

	/**
	 * Creates a company
	 *
	 * @since	1.0
	 * @param 	arr		$data	Array of attributes for a company
	 * @return	mixed	False if not a valid creation, Ccompany ID if user is found or valid creation
	 */
	public function create( $data = array() ) {

		if ( $this->id != 0 || empty( $data ) ) {
			return false;
		}

		$args = wp_parse_args( $data, $defaults );
		$args = $this->sanitize_columns( $args );

		if ( empty( $args['name'] ) ) {
			return false;
		}

		/**
		 * Fires before a company is created
		 *
		 * @param array $args Contains company information ID, name, and email.
		 */
		do_action( 'kbs_company_pre_create', $args );

		$created = false;

		// The DB class 'add' implies an update if the company being asked to be created already exists
		if ( $this->db->add( $data ) ) {

			// We've successfully added/updated the company, reset the class vars with the new data
			$company = $this->db->get_company_by( 'name', $args['name'] );

			// Setup the company data with the values from DB
			$this->setup_company( $company );

			$created = $this->id;
		}

		/**
		 * Fires after a company is created
		 *
		 * @param	int		$created	If created successfully, the company ID.  Defaults to false.
		 * @param	arr		$args		Contains company information such as ID, name, and email.
		 */
		do_action( 'kbs_company_post_create', $created, $args );

		return $created;

	} // create

	/**
	 * Update a company record
	 *
	 * @since	1.0
	 * @param	arr		$data	Array of data attributes for a company
	 * @return	bool	If the update was successful or not
	 */
	public function update( $data = array() ) {

		if ( empty( $data ) ) {
			return false;
		}

		$data = $this->sanitize_columns( $data );

		do_action( 'kbs_company_pre_update', $this->id, $data );

		$updated = false;

		if ( $this->db->update( $this->id, $data ) ) {

			$company = $this->db->get_company_by( 'id', $this->id );
			$this->setup_company( $company );

			$updated = true;
		}

		do_action( 'kbs_company_post_update', $updated, $this->id, $data );

		return $updated;
	} // update

	/**
	 * Get the parsed notes for a company as an array.
	 *
	 * @since	1.0
	 * @param	int		$length		The number of notes to get
	 * @param	int		$paged		What note to start at
	 * @return	arr		The notes requsted
	 */
	public function get_notes( $length = 20, $paged = 1 ) {

		$length = is_numeric( $length ) ? $length : 20;
		$offset = is_numeric( $paged ) && $paged != 1 ? ( ( absint( $paged ) - 1 ) * $length ) : 0;

		$all_notes   = $this->get_raw_notes();
		$notes_array = array_reverse( array_filter( explode( "\n\n", $all_notes ) ) );

		$desired_notes = array_slice( $notes_array, $offset, $length );

		return $desired_notes;

	} // get_notes

	/**
	 * Get the total number of notes we have after parsing.
	 *
	 * @since	1.0
	 * @return	int		The number of notes for the company
	 */
	public function get_notes_count() {

		$all_notes = $this->get_raw_notes();
		$notes_array = array_reverse( array_filter( explode( "\n\n", $all_notes ) ) );

		return count( $notes_array );

	} // get_notes_count

	/**
	 * Add a note for the company.
	 *
	 * @since	1.0
	 * @param	str			$note	The note to add
	 * @return	str|bool	The new note if added succesfully, false otherwise
	 */
	public function add_note( $note = '' ) {

		$note = trim( $note );
		if ( empty( $note ) ) {
			return false;
		}

		$notes = $this->get_raw_notes();

		if( empty( $notes ) ) {
			$notes = '';
		}

		$note_string = date_i18n( 'F j, Y H:i:s', current_time( 'timestamp' ) ) . ' - ' . $note;
		$new_note    = apply_filters( 'kbs_company_add_note_string', $note_string );
		$notes      .= "\n\n" . $new_note;

		do_action( 'kbs_company_pre_add_note', $new_note, $this->id );

		$updated = $this->update( array( 'notes' => $notes ) );

		if ( $updated ) {
			$this->notes = $this->get_notes();
		}

		do_action( 'kbs_company_post_add_note', $this->notes, $new_note, $this->id );

		// Return the formatted note, so we can test, as well as update any displays
		return $new_note;

	} // add_note

	/**
	 * Get the notes column for the company.
	 *
	 * @since	1.0
	 * @return	str		The Notes for the company, non-parsed
	 */
	private function get_raw_notes() {

		$all_notes = $this->db->get_column( 'notes', $this->id );

		return (string) $all_notes;

	} // get_raw_notes

	/**
	 * Retrieve company meta field for a company.
	 *
	 * @param	str		$meta_key	The meta key to retrieve.
	 * @param	bool	$single		Whether to return a single value.
	 * @return	mixed	Will be an array if $single is false. Will be value of meta data field if $single is true.
	 *
	 * @access	public
	 * @since	1.0
	 */
	public function get_meta( $meta_key = '', $single = true ) {
		return KBS()->company_meta->get_meta( $this->id, $meta_key, $single );
	} // get_meta

	/**
	 * Add meta data field to a company.
	 *
	 * @param	str		$meta_key		Metadata name.
	 * @param	mixed	$meta_value		Metadata value.
	 * @param	bool	$unique			Optional, default is false. Whether the same key should not be added.
	 * @return	bool	False for failure. True for success.
	 *
	 * @access	public
	 * @since	1.0
	 */
	public function add_meta( $meta_key = '', $meta_value, $unique = false ) {
		return KBS()->company_meta->add_meta( $this->id, $meta_key, $meta_value, $unique );
	} // add_meta

	/**
	 * Update company meta field based on company ID.
	 *
	 * @param	str		$meta_key		Metadata key.
	 * @param	mixed	$meta_value		Metadata value.
	 * @param	mixed	$prev_value		Optional. Previous value to check before removing.
	 * @return	bool	False on failure, true if success.
	 *
	 * @access	public
	 * @since	1.0
	 */
	public function update_meta( $meta_key = '', $meta_value, $prev_value = '' ) {
		return KBS()->company_meta->update_meta( $this->id, $meta_key, $meta_value, $prev_value );
	} // update_meta

	/**
	 * Remove metadata matching criteria from a company.
	 *
	 * @param	str		$meta_key		Metadata name.
	 * @param	mixed	$meta_value		Optional. Metadata value.
	 * @return	bool	False for failure. True for success.
	 *
	 * @access	public
	 * @since	1.0
	 */
	public function delete_meta( $meta_key = '', $meta_value = '' ) {
		return KBS()->company_meta->delete_meta( $this->id, $meta_key, $meta_value );
	} // delete_meta

	/**
	 * Sanitize the data for update/create
	 *
	 * @since	1.0
	 * @param	arr		$data	The data to sanitize
	 * @return	arr		The sanitized data, based off column defaults
	 */
	private function sanitize_columns( $data ) {

		$columns        = $this->db->get_columns();
		$default_values = $this->db->get_column_defaults();

		foreach ( $columns as $key => $type ) {

			// Only sanitize data that we were provided
			if ( ! array_key_exists( $key, $data ) ) {
				continue;
			}

			switch( $type ) {

				case '%s':
					if ( 'email' == $key ) {
						$data[ $key ] = sanitize_email( $data[ $key ] );
					} elseif ( 'notes' == $key ) {
						$data[ $key ] = strip_tags( $data[ $key ] );
					} else {
						$data[ $key ] = sanitize_text_field( $data[ $key ] );
					}
					break;

				case '%d':
					if ( ! is_numeric( $data[ $key ] ) || (int) $data[ $key ] !== absint( $data[ $key ] ) ) {
						$data[ $key ] = $default_values[ $key ];
					} else {
						$data[ $key ] = absint( $data[ $key ] );
					}
					break;

				case '%f':
					// Convert what was given to a float
					$value = floatval( $data[ $key ] );

					if ( ! is_float( $value ) ) {
						$data[$key] = $default_values[$key];
					} else {
						$data[$key] = $value;
					}
					break;

				default:
					$data[$key] = sanitize_text_field( $data[$key] );
					break;

			}

		}

		return $data;
	} // sanitize_columns

} // KBS_Company
