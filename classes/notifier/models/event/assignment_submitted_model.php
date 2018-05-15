<?php

namespace block_quickmail\notifier\models\event;

use block_quickmail\notifier\models\interfaces\event_notification_model_interface;
use block_quickmail\notifier\models\event_notification_model;

class assignment_submitted_model extends event_notification_model implements event_notification_model_interface {

    // WIP !!!

    public $component = 'course';

    public $object = 'course';

    public $required_conditions = [];

    public function get_substitution_codes()
    {
        // returns array
            // includes all "base" codes
            // includes all "course" codes
            // any extra...
    }

}