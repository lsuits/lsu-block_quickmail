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
use block_quickmail\persistents\concerns\can_be_soft_deleted;
use block_quickmail\persistents\interfaces\notification_type_interface;
 
class event_notification extends persistent implements notification_type_interface {
 
	use enhanced_persistent,
		sanitizes_input,
		is_notification_type,
		can_be_soft_deleted;

	/** Table name for the persistent. */
	const TABLE = 'block_quickmail_event_notifs';

	public static $required_creation_keys = [];

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
			'type' => [
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
	 * Creates and returns an event notification of the given type for the given course and user
	 * 
	 * @param  string  $type  an event_notification_type key
	 * @param  object  $course
	 * @param  object  $user
	 * @param  array   $params
	 * @return event_notification
	 */
	public static function create_type($type, $course, $user, $params)
	{
		$notification = notification::create_for_course_user('event', $course, $user, $params);

		$event_notification = self::create_for_notification($notification, array_merge([
			'type' => $type,
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
		$params = self::sanitize_creation_params($params);

		try {
			$event_notification = self::create_new([
				'notification_id' => $notification->get('id'),
				'type' => $params['type'],
				'time_delay' => $params['time_delay'],
			]);
		
		// if there was an error during creation
		} catch (\Exception $e) {
			$notification->hard_delete();
		}

		return $event_notification;
	}

}