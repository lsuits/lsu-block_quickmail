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

// use \core\persistent;
use block_quickmail\persistents\concerns\enhanced_persistent;
use block_quickmail\persistents\concerns\sanitizes_input;
use block_quickmail\persistents\concerns\is_notification_type;
use block_quickmail\persistents\concerns\is_schedulable;
use block_quickmail\persistents\concerns\can_be_soft_deleted;
use block_quickmail\persistents\interfaces\notification_type_interface;
use block_quickmail\persistents\interfaces\schedulable_interface;
use block_quickmail\persistents\schedule;
use block_quickmail\persistents\message;
use block_quickmail\repos\user_repo;
 
// if ( ! class_exists('\core\persistent')) {
//     class_alias('\block_quickmail\persistents\persistent', '\core\persistent');
// }

class reminder_notification extends \block_quickmail\persistents\persistent implements notification_type_interface, schedulable_interface {
 
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
	 * Creates and returns a reminder notification of the given model key and object for the given course and user
	 *
	 * Throws an exception if any missing param keys
	 * 
	 * @param  string  $model_key    a reminder_notification_model key
	 * @param  object  $object       the object that is to be evaluated by this reminder notification
	 * @param  object  $course
	 * @param  object  $user
	 * @param  array   $params
	 * @return reminder_notification
	 * @throws \Exception
	 */
	public static function create_type($model_key, $object = null, $course, $user, $params)
	{
		// add the model key to the params
		$params = array_merge($params, ['model' => $model_key]);

		// create the parent notification
		$notification = notification::create_for_course_user('reminder', $course, $user, $params);

		// create the reminder notification
		$reminder_notification = self::create_for_notification($notification, array_merge([
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
			'model',
			'object_id',
		]);

		try {
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
    ///  UPDATE METHODS
    /// 
    ///////////////////////////////////////////////

    /**
     * Updates and returns an event notification from the given params
     * 
     * @param  array         $params
     * @return event_notification
     */
    public function update_self($params)
    {
        // update schedule details if necessary
        if ($schedule = $this->get_schedule()) {
	        if (isset($params['schedule_begin_at'])) {
	            $begin_at = schedule::get_sanitized_date_time_selector_value($params['schedule_begin_at'], 0);
	            
	            $schedule->set('begin_at', $begin_at);
	        }

	        if (isset($params['schedule_end_at'])) {
	            $end_at = schedule::get_sanitized_date_time_selector_value($params['schedule_end_at'], 0);
	            
	            $schedule->set('end_at', $end_at);
	        }
	        
	        $schedule->set('unit', $params['schedule_time_unit']);
	        $schedule->set('amount', $params['schedule_time_amount']);
	        $schedule->update();
	        
	        $this->set_next_run_time();
        }

        return $this;
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
    ///  METHODS
    /// 
    ///////////////////////////////////////////////

	/**
	 * Returns a filtered array of user ids to be notified given a qualified array of user ids
	 * 
	 * @param  array  $user_ids
	 * @return array
	 */
	public function filter_notifiable_user_ids($user_ids = [])
	{
		// pull all users that this message creator is capable of emailing within the course
        $allowed_users_ids = array_keys(user_repo::get_course_user_selectable_users($this->get_notification()->get_course(), $this->get_notification()->get_user()));

        // filter out any user ids that are not allowed
        $user_ids = array_filter($user_ids, function($id) use ($allowed_users_ids) {
            return in_array($id, $allowed_users_ids);
        });

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

	/**
	 * Returns an array of user ids whom have already been notified at least the "max_per_interval" times since last run
	 * 
	 * @return array
	 */
	public function get_user_ids_to_ignore()
	{
		// @TODO - need to do this!!
		return [];
	}

    ///////////////////////////////////////////////
    ///
    ///  NOTIFICATION TYPE INTERFACE
    /// 
    ///////////////////////////////////////////////

	/**
	 * Pulls all users who should be notified in this notification and creates a new message
	 * instance to be sent out in the queue ASAP
	 *
	 * Note: if no users can be found, no message is created or sent
	 * 
	 * @param  int  $user_id  (note: for this implementaion, the user_id should always be null)
	 * @return void
	 */
	public function notify($user_id = null)
	{
		// instantiate this notification_type_interface's notification model
		$model = $this->get_notification_model();

		try {
			// get the parent notification
			$notification = $this->get_notification();

			// get all user ids to be notified, if no user ids, do nothing
			if ($user_ids = $this->filter_notifiable_user_ids($model->get_user_ids_to_notify())) {
				message::create_from_notification($notification, $user_ids);
			}
		} catch (\Exception $e) {
			// message not created, fail gracefully
		}
	}

}