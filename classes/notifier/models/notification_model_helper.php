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

    public static function get_object_type_for_model($notification_type, $model_key)
    {
        //
    }

}