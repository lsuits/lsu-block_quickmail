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
use block_quickmail\persistents\concerns\can_be_soft_deleted;
use block_quickmail\persistents\interfaces\notification_type_interface;
 
// if ( ! class_exists('\core\persistent')) {
//     class_alias('\block_quickmail\persistents\persistent', '\core\persistent');
// }

class event_notification extends \block_quickmail\persistents\persistent implements notification_type_interface {
 
	use enhanced_persistent,
		sanitizes_input,
		is_notification_type,
		can_be_soft_deleted;

	/** Table name for the persistent. */
	const TABLE = 'block_quickmail_event_notifs';

	/** notification_type_interface */
	public static $notification_type_key = 'event';

    public static $required_creation_keys = [
		'object_id', 
	];

	public static $default_creation_params = [
        'time_delay' => 0,
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
			'time_delay' => [
				'type' => PARAM_INT,
				'default' => 0,
			],
			'timedeleted' => [
				'type' => PARAM_INT,
				'default' => 0,
			],
		];
	}

	/**
	 * Creates and returns an event notification of the given model key and object for the given course and user
	 *
	 * Throws an exception if any missing param keys
	 * 
	 * @param  string  $model_key    an event_notification_model key
	 * @param  object  $object       the object that is to be evaluated by this event notification
	 * @param  object  $course
	 * @param  object  $user
	 * @param  array   $params
	 * @return event_notification
	 * @throws \Exception
	 */
	public static function create_type($model_key, $object = null, $course, $user, $params)
	{
		// add the model key to the params
		$params = array_merge($params, ['model' => $model_key]);

		// created the parent notification
		$notification = notification::create_for_course_user('event', $course, $user, $params);

		// calculate the time delay amount (if any) from the given params
		$time_delay = self::calculate_time_delay_from_params($params);

		// create the event notification
		$event_notification = self::create_for_notification($notification, array_merge([
			'object_id' => ! empty($object) ? $object->id : 0, // may need to write helper class to get this id
			'time_delay' => $time_delay
		], $params));

		return $event_notification;
	}

	/**
	 * Creates and returns an event notification to be associated with the given notification
	 * 
	 * @param  notification  $notification
	 * @param  array         $params
	 * @return event_notification
	 */
	private static function create_for_notification($notification, $params)
	{
		$params = self::sanitize_creation_params($params, [
			'time_delay', 
			'model',
			'object_id',
		]);

		try {
			$event_notification = self::create_new([
				'notification_id' => $notification->get('id'),
				'model' => $params['model'],
				'time_delay' => $params['time_delay'],
			]);
		
		// if there was an error during creation
		} catch (\Exception $e) {
			$notification->hard_delete();
		}

		return $event_notification;
	}

	/**
	 * Returns the calculate time_delay (seconds) from the given creation params, if any
	 * 
	 * @param  array  $params
	 * @return int
	 */
	public static function calculate_time_delay_from_params($params)
	{
		$time_delay = 0;

		if (array_key_exists('time_delay_unit', $params) && array_key_exists('time_delay_amount', $params)) {
			$amount = (int) $params['time_delay_amount'];

			if (in_array($params['time_delay_unit'], ['minute', 'hour', 'day']) && $amount > 0) {
				$seconds = 60;
				$mult = 1;
				
				if ($params['time_delay_unit'] == 'hour') {
					$mult = 60;
				} else if ($params['time_delay_unit'] == 'day') {
					$mult = 1440;
				}

				$time_delay = $amount * $seconds * $mult;
			}
		}

		return $time_delay;
	}

	///////////////////////////////////////////////
    ///
    ///  NOTIFICATION TYPE INTERFACE
    /// 
    ///////////////////////////////////////////////

	public function notify()
	{
		// this is where the magic happens
	}

}