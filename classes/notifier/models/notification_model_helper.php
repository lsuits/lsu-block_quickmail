<?php

namespace block_quickmail\notifier\models;

use block_quickmail_string;

class notification_model_helper {

    public static $models = [
        'reminder' => [
            'non_participation'
        ],
        'event' => [
            'assignment_submitted'
        ]
    ];

    /**
     * Returns a fully namespaced notification_model class name from a notification type and a model key
     * 
     * @param  string  $notification_type  remind|event
     * @param  string  $model_key  ex: 'non-participation'
     * @return string
     */
    public static function get_full_model_class_name($notification_type, $model_key)
    {
        return 'block_quickmail\notifier\models\\' . $notification_type . '\\' . self::get_model_class_name($model_key);
    }

    /**
     * Returns a notification model class name given a notification_type_interface's model key
     * 
     * @param  string  $model_key
     * @return string
     */
    public static function get_model_class_name($model_key)
    {
        return str_replace('-', '_', $model_key) . '_model';
    }

    /**
     * Returns an array of "notification model" keys available for the given notification type
     * 
     * @param  string  $notification_type  reminder|event
     * @return array
     */
    public static function get_available_model_keys_by_type($notification_type)
    {
        return self::$models[$notification_type];
    }

    /**
     * Returns an associative array of "notification model" selections available for the given notification type
     * 
     * @param  string  $notification_type  reminder|event
     * @return array [model key => model string display]
     */
    public static function get_available_model_selection_by_type($notification_type)
    {
        $keys = self::get_available_model_keys_by_type($notification_type);

        $selection_array = array_reduce($keys, function($carry, $key) use ($notification_type) {
            $carry[$key] = block_quickmail_string::get('notification_model_'. $notification_type . '_' . $key);
            return $carry;
        }, []);

        return $selection_array;
    }

    /**
     * Returns a model's 'object type' given a notification type and key
     * 
     * @param  string  $notification_type  reminder|event
     * @param  string  $model_key
     * @return string
     */
    public static function get_object_type_for_model($notification_type, $model_key)
    {
        $model_class = self::get_full_model_class_name($notification_type, $model_key);

        return $model_class::$object_type;
    }

    /**
     * Returns a model's required "condition keys" given a notification type and key
     * 
     * @param  string  $notification_type  reminder|event
     * @param  string  $model_key
     * @return array
     */
    public static function get_condition_keys_for_model($notification_type, $model_key)
    {
        $model_class = self::get_full_model_class_name($notification_type, $model_key);

        return $model_class::$condition_keys;
    }

    /**
     * Reports whether or not a model requires an object selection other that 'course' or 'user', given a notification type and key
     * 
     * @param  string  $notification_type  reminder|event
     * @param  string  $model_key
     * @return bool
     */
    public static function model_requires_object($notification_type, $model_key)
    {
        $object_type = self::get_object_type_for_model($notification_type, $model_key);

        return ! in_array($object_type, ['user', 'course']);
    }

    /**
     * Reports whether or not a model requires condition selections, given a notification type and key
     * 
     * @param  string  $notification_type  reminder|event
     * @param  string  $model_key
     * @return bool
     */
    public static function model_requires_conditions($notification_type, $model_key)
    {
        $condition_keys = self::get_condition_keys_for_model($notification_type, $model_key);

        return (bool) count($condition_keys);
    }

}