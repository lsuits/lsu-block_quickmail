<?php

namespace block_quickmail\notifier\models\event;

use block_quickmail\notifier\models\interfaces\event_notification_model_interface;
use block_quickmail\notifier\models\event_notification_model;

class course_entered_model extends event_notification_model implements event_notification_model_interface {

    public static $object_type = 'course';

    public static $condition_keys = [];

}