<?php

namespace block_quickmail\notifier\models;

use block_quickmail\notifier\models\interfaces\notification_model_interface;
use block_quickmail\notifier\models\notification_model;
use block_quickmail\persistents\interfaces\notification_type_interface;

abstract class event_notification_model extends notification_model implements notification_model_interface {

    public function __construct(notification_type_interface $notification_type_interface) {
        parent::__construct($notification_type_interface);
    }

}