<?php
/**
 * Ticket Stats
 *
 * @package     KBS
 * @subpackage  Classes/Stats
 * @copyright   Copyright (c) 2017, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * KBS_Ticket_Stats Class
 *
 * This class is for retrieving stats for tickets
 *
 * Stats can be retrieved for date ranges and pre-defined periods
 *
 * @since 1.0
 */
class KBS_Ticket_Stats extends KBS_Stats {

	/**
	 * Retrieve ticket stats.
	 *
	 * @access	public
	 * @since	1.0
	 * @param	str|bool	$start_date The starting date for which we'd like to filter our ticket stats. If false, we'll use the default start date of `this_month`
	 * @param	str|bool	$end_date 	The end date for which we'd like to filter our ticket stats. If false, we'll use the default end date of `this_month`
	 * @param	str|arr		$status 	The ticket status(es) to count. Only valid when retrieving global stats
	 * @return 	float|int 	Total amount of tickets based on the passed arguments.
	 */
	public function get_tickets( $start_date = false, $end_date = false, $event_date = false, $status = 'publish' ) {

		$this->setup_dates( $start_date, $end_date );

		// Make sure start date is valid
		if( is_wp_error( $this->start_date ) )	{
			return $this->start_date;
		}

		// Make sure end date is valid
		if( is_wp_error( $this->end_date ) )	{
			return $this->end_date;
		}

		add_filter( 'kbs_count_tickets_where', array( $this, 'count_tickets_where' ) );

		if( is_array( $status ) ) {
			$count = 0;
			foreach( $status as $event_status ) {
				$count += kbs_count_tickets()->$event_status;
			}
		} else {
			$count = kbs_count_tickets()->$status;
		}

		remove_filter( 'kbs_count_tickets_where', array( $this, 'count_tickets_where' ) );

		return $count;

	} // get_tickets

} // KBS_Ticket_Stats
