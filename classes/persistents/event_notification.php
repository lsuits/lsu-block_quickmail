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
use block_quickmail\persistents\message;
use block_quickmail_plugin;
 
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
        'mute_time' => 0,
    ];

    public static $one_time_events = [
        'course-entered',
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
			'mute_time' => [
				'type' => PARAM_INT,
				'default' => 0,
			],
			'timedeleted' => [
				'type' => PARAM_INT,
				'default' => 0,
			],
		];
	}

	///////////////////////////////////////////////
    ///
    ///  GETTERS
    /// 
    ///////////////////////////////////////////////

    /**
     * Reports whether or not this event notification should notify the given
     * user_id at this moment
     *
     * For one-time events: this notification must not have ever been sent user
     * 
     * For non-one-time events: this notification may not be sent until the
     * "next available send time" (NOW - mute_time + time_delay)
     * 
     * @return bool
     */
    private function should_notify_user_now($user_id)
    {
    	return $this->is_one_time_event()
    		? ! $this->has_ever_notified_user($user_id)
    		: $this->sufficient_time_since_last_notification($user_id);
    }

    /**
	 * Reports whether or not this event notification is a "one time" event
	 * 
	 * One time events will only be fired once per event notification instance
	 * 
	 * @return bool
	 */
    private function is_one_time_event()
    {
    	return in_array($this->get('model'), static::$one_time_events);
    }

    /**
     * Returns a timestamp in which this notification should be scheduled to send at
     * when successfully triggered
     * 
     * @return int
     */
    private function calculated_send_time()
   	{
		return time() + (int) $this->get('time_delay');
   	}

   	/**
     * Returns the earliest timestamp at which this notification should be sent next
     * 
     * @return int
     */
    private function next_available_send_time()
   	{
		return $this->calculated_send_time() - (int) $this->get('mute_time');
   	}

    ///////////////////////////////////////////////
    ///
    ///  CREATION METHODS
    /// 
    ///////////////////////////////////////////////

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

        // calculate the input time_delay and mute_time, if any
        list($time_delay, $mute_time) = self::get_event_creation_param_seconds($params);

		// create the event notification
		$event_notification = self::create_for_notification($notification, array_merge([
			'object_id' => ! empty($object) ? $object->id : 0, // may need to write helper class to get this id
            'time_delay' => $time_delay,
            'mute_time' => $mute_time,
		], $params));

		return $event_notification;
	}

    /**
     * Returns an array of time_delay and mute_time seconds for event notification creation
     * 
     * @param  array  $params  raw event notification creation params
     * @return array
     */
    private static function get_event_creation_param_seconds($params)
    {
        $time_delay = 0;
        $mute_time = 0;

        foreach (['time_delay', 'mute_time'] as $type) {
            // calculate amount of seconds for type (if any) from the given params
            if (array_key_exists($type . '_unit', $params) && array_key_exists($type . '_amount', $params)) {
                $$type = block_quickmail_plugin::calculate_seconds_from_time_params($params[$type . '_unit'], $params[$type . '_amount']);
            }
        }

        return [$time_delay, $mute_time];
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
			'mute_time', 
			'model',
			'object_id',
		]);

		try {
			$event_notification = self::create_new([
				'notification_id' => $notification->get('id'),
				'model' => $params['model'],
				'time_delay' => $params['time_delay'],
				'mute_time' => $params['mute_time'],
			]);
		
		// if there was an error during creation
		} catch (\Exception $e) {
			$notification->hard_delete();
		}

		return $event_notification;
	}

	///////////////////////////////////////////////
    ///
    ///  NOTIFICATION TYPE INTERFACE
    /// 
    ///////////////////////////////////////////////

	/**
	 * Creates a new message instance to be sent to the given user id, if appropriate
	 *
	 * @param  int  $user_id  (note: for this implementaion, the user_id should always be given)
	 * @return void
	 */
	public function notify($user_id = null)
	{
		// make sure this notification should be sent right now based upon configuration
		if ($this->should_notify_user_now($user_id)) {
			try {
				// get the parent notification
				$notification = $this->get_notification();
				
				// determine when this notification should be sent
				$send_at = $this->calculated_send_time();

				// schedule a message
				message::create_from_notification($notification, [$user_id], $send_at);
                
				// note that this event has been sent to this user at this time
				$this->note_sent_to_user($user_id, $send_at);
			} catch (\Exception $e) {
				// message not created, fail gracefully
			}
		}
	}

	///////////////////////////////////////////////
    ///
    ///  EVENT RECIPIENT METHODS
    /// 
    ///////////////////////////////////////////////

	/**
	 * Reports whether or not this event notification has been sent to the given user before
	 * 
	 * @param  int  $user_id
	 * @return bool
	 */
	private function has_ever_notified_user($user_id)
	{
		global $DB;

        return $DB->record_exists('block_quickmail_event_recips', [
            'event_notification_id' => $this->get('id'),
            'user_id' => $user_id
        ]);
	}

	/**
	 * Reports whether or not enough time has elapsed since last time this notification
	 * notified the user, if at all
	 * 
	 * @param  int  $user_id
	 * @return bool
	 */
	private function sufficient_time_since_last_notification($user_id)
	{
		global $DB;

    	// may not be notified until next available send time
    	$result = $DB->get_records_sql(
    		"SELECT * FROM {block_quickmail_event_recips} 
    		 WHERE event_notification_id = ?
    		 AND user_id = ? 
    		 AND notified_at > ?",
    		[$this->get('id'), $user_id, $this->next_available_send_time()]
    	);

        return empty($result);
	}

	/**
	 * Inserts a record of the given user being notified by this notification at the
	 * given time
	 * 
	 * @param  int  $user_id
	 * @param  int  $notified_at   unix timestamp
	 * @return void
	 */
	private function note_sent_to_user($user_id, $notified_at)
	{
		global $DB;

		$DB->insert_record('block_quickmail_event_recips', (object) [
			'event_notification_id' => $this->get('id'),
			'user_id' => $user_id,
			'notified_at' => $notified_at,
		], false);
	}

}