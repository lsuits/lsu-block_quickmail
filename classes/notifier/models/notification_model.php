<?php

namespace block_quickmail\notifier\models;

use block_quickmail\notifier\models\interfaces\notification_model_interface;
use block_quickmail\persistents\interfaces\notification_type_interface;
use block_quickmail\persistents\notification;

abstract class notification_model implements notification_model_interface {

    public $notification_type_interface;
    public $notification;

    public function __construct(notification_type_interface $notification_type_interface, notification $notification) {
        $this->notification_type_interface = $notification_type_interface;
        $this->notification = $notification;
    }

    /**
     * Returns a fully namespaced notification_model class name from the given notification_type_interface
     * 
     * @param  notification_type_interface  $notification_type_interface
     * @return string
     */
    protected static function get_full_model_class_name($notification_type_interface)
    {
        return 'block_quickmail\notifier\models\\' . $notification_type_interface::$notification_type_key . '\\' . self::get_model_class_name($notification_type_interface->get('model'));
    }

    /**
     * Returns a notification model class name given a notification_type_interface's model
     * 
     * @param  string  $model
     * @return string
     */
    protected static function get_model_class_name($model)
    {
        return str_replace('-', '_', $model) . '_model';
    }

    /**
     * Instantiates and returns a notification_model_interface given a notification_type_interface
     * 
     * @param  notification_type_interface  $notification_type_interface
     * @return reminder_notification_model_interface
     */
    public static function make(notification_type_interface $notification_type_interface)
    {
        $notification = $notification_type_interface->get_notification();

        $class = static::get_full_model_class_name($notification_type_interface);

        return new $class($notification_type_interface, $notification);
    }

}