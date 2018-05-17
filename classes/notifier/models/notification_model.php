<?php

namespace block_quickmail\notifier\models;

use block_quickmail\notifier\models\interfaces\notification_model_interface;
use block_quickmail\persistents\interfaces\notification_type_interface;
use block_quickmail\notifier\notification_condition;

abstract class notification_model implements notification_model_interface {

    public $notification_type_interface;
    public $notification;
    public $condition;

    public function __construct(notification_type_interface $notification_type_interface) {
        $this->notification_type_interface = $notification_type_interface;
        $this->notification = $notification_type_interface->get_notification();
        $this->condition = notification_condition::from_condition_string($this->notification->get('conditions'));
    }

    /**
     * Instantiates and returns a notification_model_interface given a notification_type_interface
     * 
     * @param  notification_type_interface  $notification_type_interface
     * @return reminder_notification_model_interface
     */
    public static function make(notification_type_interface $notification_type_interface)
    {
        $class = static::get_notification_type_interface_model_class_name($notification_type_interface);

        return new $class($notification_type_interface);
    }

    /**
     * Returns a fully namespaced notification_model class name from the given notification_type_interface
     * 
     * @param  notification_type_interface  $notification_type_interface
     * @return string
     */
    public static function get_notification_type_interface_model_class_name($notification_type_interface)
    {
        return self::get_full_model_class_name($notification_type_interface::$notification_type_key, $notification_type_interface->get('model'));
    }

    /**
     * Returns a fully namespaced notification_model class name from a notification type and a model key
     * 
     * @param  string  $type_key  remind|event
     * @param  string  $model_key  ex: 'non-participation'
     * @return string
     */
    public static function get_full_model_class_name($type_key, $model_key)
    {
        return 'block_quickmail\notifier\models\\' . $type_key . '\\' . self::get_model_class_name($model_key);
    }

    /**
     * Returns a notification model class name given a notification_type_interface's model
     * 
     * @param  string  $model
     * @return string
     */
    public static function get_model_class_name($model)
    {
        return str_replace('-', '_', $model) . '_model';
    }

    ///////////////////////////////////////////////
    ///
    ///  GETTERS
    /// 
    ///////////////////////////////////////////////
    
    /**
     * Returns this notification_model's notification's course id
     * 
     * @return int
     */
    public function get_course_id()
    {
        return (int) $this->notification->get('course_id');
    }

}