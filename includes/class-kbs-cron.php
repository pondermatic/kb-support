<?php
/**
 * Cron tasks
 *
 * @package     KBS
 * @subpackage  Classes/Tasks
 * @copyright   Copyright (c) 2017, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

class KBS_Cron	{

	/**
	 * Get things going.
	 */
	public function __construct()	{
		add_filter( 'cron_schedules', array( $this, 'add_schedules'   ) );
	} // __construct

	/**
	 * Creates custom cron schedules within WP.
	 *
	 * @since	1.0
	 * @param	arr		$schedules	Schedule array
	 * @return	arr		Schedule array
	 */
	public function add_schedules( $schedules = array() )	{
		// Adds once weekly to the existing schedules.
		$schedules['weekly'] = array(
			'interval' => 604800,
			'display'  => __( 'Once Weekly', 'kb-support' )
		);

		return $schedules;
	} // add_schedules

	/**
	 * Schedules our events
	 *
	 * @since	1.0
	 * @return	void
	 */
	public function schedule_events() {
		$this->hourly_events();
		$this->daily_events();
		$this->weekly_events();
	} // schedule_events

	/**
	 * Schedule hourly events
	 *
	 * @since	1.0
	 * @return void
	 */
	private function hourly_events() {
		if ( ! wp_next_scheduled( 'kbs_hourly_scheduled_events' ) ) {
		}
	} // hourly_events

	/**
	 * Schedule daily events
	 *
	 * @since 1.6
	 * @return void
	 */
	private function daily_events() {
		if ( ! wp_next_scheduled( 'kbs_daily_scheduled_events' ) ) {
		}
	} // daily_events

	/**
	 * Schedule weekly events
	 *
	 * @since	1.0
	 * @return void
	 */
	private function weekly_events() {
		if ( ! wp_next_scheduled( 'kbs_weekly_scheduled_events' ) ) {
		}
	} // weekly_events

} // KBS_Cron

new KBS_Cron;
