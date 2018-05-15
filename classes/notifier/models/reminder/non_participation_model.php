<?php

namespace block_quickmail\notifier\models\reminder;

use block_quickmail\notifier\models\interfaces\reminder_notification_model_interface;
use block_quickmail\notifier\models\reminder_notification_model;

class non_participation_model extends reminder_notification_model implements reminder_notification_model_interface {

    public $component = 'course';

    public $object = 'course';

    public $required_conditions = [
        'time-since-course-access',
    ];

    public function get_substitution_codes()
    {
        // returns array
            // includes all "base" codes
            // includes all "course" codes
            // any extra...
    }

}