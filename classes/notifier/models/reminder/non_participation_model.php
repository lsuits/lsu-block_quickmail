<?php

namespace block_quickmail\notifier\models\reminder;

use block_quickmail\notifier\models\interfaces\reminder_notification_model_interface;
use block_quickmail\notifier\models\reminder_notification_model;

class non_participation_model extends reminder_notification_model implements reminder_notification_model_interface {

    public static $component = 'course';

    public static $object = 'course';

    public static $required_conditions = [
        'time-since-course-access',
    ];

    public static function get_substitution_codes()
    {
        // returns array
            // includes all "base" codes
            // includes all "course" codes
            // any extra...
    }

    /**
     * Returns an array of user ids to be notified based on this reminder_notification_model's spec
     * 
     * @return array
     */
    public function get_user_ids_to_notify()
    {
        // $this->notification_type_interface
        // $this->notification

        // $course_id        = $this->notification->get('course_id')
        // $since_time_stamp = 

        // get distinct user ids 
        // where users are in a specific course
        // and where have not accessed the course since a specific timestamp
    }

}