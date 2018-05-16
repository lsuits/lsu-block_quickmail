<?php

namespace block_quickmail\notifier\models;

use block_quickmail\notifier\models\interfaces\notification_model_interface;
use block_quickmail\notifier\models\notification_model;
use block_quickmail\persistents\notification;
use block_quickmail\persistents\interfaces\notification_type_interface;

abstract class reminder_notification_model extends notification_model implements notification_model_interface {

    public function __construct(notification_type_interface $notification_type_interface, notification $notification) {
        parent::__construct($notification_type_interface, $notification);

        // $this->set_object();
    }

    private function set_object()
    {
        // get 'component' and 'object' from this reminder_notification_model
        // get 'object_id' from  $this->notification_type_interface
        
        // pull object
        
        // $this->object = $object
    }

}