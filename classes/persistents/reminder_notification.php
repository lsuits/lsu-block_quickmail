<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * @package    block_quickmail
 * @copyright  2008 onwards Louisiana State University
 * @copyright  2008 onwards Chad Mazilly, Robert Russo, Jason Peak, Dave Elliott, Adam Zapletal, Philip Cali
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_quickmail\persistents;

use \core\persistent;
use block_quickmail\persistents\concerns\enhanced_persistent;
use block_quickmail\persistents\concerns\sanitizes_input;
use block_quickmail\persistents\concerns\is_notification_type;
use block_quickmail\persistents\concerns\is_schedulable;
use block_quickmail\persistents\concerns\can_be_soft_deleted;
use block_quickmail\persistents\interfaces\notification_type_interface;
use block_quickmail\persistents\interfaces\schedulable_interface;
use block_quickmail\notifier\models\reminder_notification_model;
use block_quickmail\persistents\schedule;
 
class reminder_notification extends persistent implements notification_type_interface, schedulable_interface {
 
	use enhanced_persistent,
		sanitizes_input,
		is_notification_type,
		is_schedulable,
		can_be_soft_deleted;

	/** Table name for the persistent. */
	const TABLE = 'block_quickmail_rem_notifs';

	/** notification_type_interface */
	public static $notification_type_key = 'reminder';

	public static $required_creation_keys = [
		'object_id', 
		'schedule_unit', 
		'schedule_amount', 
		'schedule_begin_at',
	];

	public static $default_creation_params = [
		'max_per_interval' => 0,
		'schedule_id' => 0,
		'schedule_end_at' => null,
    ];

	/**
	 * Return the definition of the properties of this model.
	 *
	 * @return array
	 */
	protected static function define_properties() {
		return [
			'notification_id' => [
				'type' => PARAM_INT,
			],
			'model' => [
				'type' => PARAM_TEXT,
			],
			'object_id' => [
				'type' => PARAM_INT,
				'default' => 0,
			],
			'max_per_interval' => [
				'type' => PARAM_INT,
				'default' => 0,
			],
			'schedule_id' => [
				'type' => PARAM_INT,
				'default' => null,
				'null' => NULL_ALLOWED,
			],
			'last_run_at' => [
				'type' => PARAM_INT,
				'default' => null,
				'null' => NULL_ALLOWED,
			],
			'next_run_at' => [
				'type' => PARAM_INT,
				'default' => null,
				'null' => NULL_ALLOWED,
			],
			'is_running' => [
				'type' => PARAM_BOOL,
				'default' => false,
			],
			'timedeleted' => [
				'type' => PARAM_INT,
				'default' => 0,
			],
		];
	}

	/**
	 * Creates and returns a reminder notification of the given model and object for the given course and user
	 * 
	 * @param  string  $model    a reminder_notification_model key
	 * @param  object  $object  the object that is to be evaluated by this reminder notification
	 * @param  object  $course
	 * @param  object  $user
	 * @param  array   $params
	 * @return reminder_notification
	 */
	public static function create_type($model, $object = null, $course, $user, $params)
	{
		$notification = notification::create_for_course_user('reminder', $course, $user, $params);

		$reminder_notification = self::create_for_notification($notification, array_merge([
			'model' => $model,
			'object_id' => ! empty($object) ? $object->id : 0, // may need to write helper class to get this id
		], $params));

		return $reminder_notification;
	}

	/**
	 * Creates and returns a reminder notification to be associated with the given notification
	 *
	 * Note: creates the reminder notification's schedule before creating the notification
	 * 
	 * @param  notification  $notification
	 * @param  array         $params
	 * @return reminder_notification
	 * @throws \Exception
	 */
	private static function create_for_notification($notification, $params)
	{
		$params = self::sanitize_creation_params($params, [
			'schedule_unit', 
			'schedule_amount',
			'schedule_begin_at',
			'schedule_end_at',
		]);

		try {
			$schedule = null;
			
			$schedule = schedule::create_from_params([
				'unit' => $params['schedule_unit'],
				'amount' => $params['schedule_amount'],
				'begin_at' => $params['schedule_begin_at'],
				'end_at' => $params['schedule_end_at'],
			]);

			$reminder_notification = self::create_new([
				'notification_id' => $notification->get('id'),
				'model' => $params['model'],
				'object_id' => $params['object_id'],
				'schedule_id' => $schedule->get('id'),
			]);

		// if there was an error during creation, delete potentially-created associative data
		} catch (\Exception $e) {
			$notification->hard_delete();

			if ( ! empty($schedule)) {
				$schedule->hard_delete();
			}

			throw new \Exception;
		}

		return $reminder_notification;
	}

	///////////////////////////////////////////////
    ///
    ///  GETTERS
    /// 
    ///////////////////////////////////////////////

	/**
	 * Returns this reminder_notification's max_per_interval as an int
	 * 
	 * @return int
	 */
	public function max_per_interval()
	{
		return (int) $this->get('max_per_interval');
	}

	///////////////////////////////////////////////
    ///
    ///  METHODS
    /// 
    ///////////////////////////////////////////////

	/**
	 * Returns an array of user ids whom have already been notified at least the "max_per_interval" times since last run
	 * 
	 * @return array
	 */
	public function get_user_ids_to_ignore()
	{
		//
	}

	///////////////////////////////////////////////
    ///
    ///  SCHEDULABLE INTERFACE
    /// 
    ///////////////////////////////////////////////

    public function run_scheduled()
    {
        $this->handle_schedule_pre_run_actions();

        $this->notify();

        $this->handle_schedule_post_run_actions();
    }

    ///////////////////////////////////////////////
    ///
    ///  NOTIFICATION TYPE INTERFACE
    /// 
    ///////////////////////////////////////////////

	public function notify()
	{
		// instantiate this notification_type_interface's notification model
		$model = $this->get_notification_model();

		// pull all users to be notified
		$user_ids = $model->get_user_ids_to_notify();

		// if this reminder_notification has a max_per_interval has
		if ($this->max_per_interval()) {
			// pull all users to be ignored based on this reminder_notification's configuration
			$ignore_user_ids = $this->get_user_ids_to_ignore();

			// filter out all of the user ids to ignore from the user ids to be notified
			$user_ids = array_filter($user_ids, function ($id) use ($ignore_user_ids) {
	            return in_array($id, $ignore_user_ids);
	        });
		}
		
		return $user_ids;
	}

}