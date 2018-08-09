<?php

namespace block_quickmail\notifier\models;

use block_quickmail\notifier\models\interfaces\notification_model_interface;
use block_quickmail\notifier\models\notification_model;
use block_quickmail\persistents\interfaces\notification_type_interface;

abstract class reminder_notification_model extends notification_model implements notification_model_interface {

    public function __construct(notification_type_interface $notification_type_interface) {
        parent::__construct($notification_type_interface);

        // $this->set_object();
    }

    private function set_object()
    {
        // get 'object_id' from  $this->notification_type_interface
        
        // pull object
        
        // $this->object = $object
    }

}