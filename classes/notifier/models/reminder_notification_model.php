<?php

namespace block_quickmail\notifier\models;

use block_quickmail\notifier\models\interfaces\notification_model_interface;
use block_quickmail\notifier\models\notification_model;
use block_quickmail\persistents\notification;
use block_quickmail\persistents\interfaces\notification_type_interface;

abstract class reminder_notification_model extends notification_model implements notification_model_interface {

    public $notification_type_interface;
    public $notification;

    public function __construct(notification_type_interface $notification_type_interface, notification $notification) {
        $this->notification_type_interface = $notification_type_interface;
        $this->notification = $notification;
    }

}