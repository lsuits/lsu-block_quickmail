<?php

namespace block_quickmail\notifier\models\event;

use block_quickmail\notifier\models\interfaces\event_notification_model_interface;
use block_quickmail\notifier\models\event_notification_model;

class assignment_submitted_model extends event_notification_model implements event_notification_model_interface {

    public static $object_type = 'assignment';

    public static $condition_keys = [];

}