<?php

namespace block_quickmail\notifier;

use block_quickmail\notifier\models\notification_model_helper;

class notification_condition {

    public $conditions;

    public function __construct($conditions = []) {
        $this->conditions = $conditions;
    }

    /**
     * Returns an instantiated notification_condition from a given condition_string
     * 
     * @param  string  $condition_string
     * @return notification_condition
     */
    public static function from_condition_string($condition_string)
    {
        $conditions = self::decode_condition_string($condition_string);

        return new self($conditions);
    }

    /**
     * Returns a string appropriate for db storage given raw notification condition params
     * 
     * @param  array  $params  optional, if none will return empty string
     * @return string
     */
    public static function format_for_storage($params = [])
    {
        if ( ! count($params)) {
            return '';
        }

        $value = array_reduce(array_keys($params), function($carry, $key) use ($params) {
            return $carry .= $key . ':' . $params[$key] . ',';
        }, '');

        return rtrim($value, ',');
    }

    /**
     * Returns a key/value array of conditions from a formatted condition string
     * 
     * @param  string $condition_string
     * @return array
     */
    public static function decode_condition_string($condition_string = '')
    {
        $conditions = [];

        if ( ! $condition_string) {
            return $conditions;
        }

        $exploded = explode(',', $condition_string);

        foreach ($exploded as $ex) {
            list($key, $value) = explode(':', $ex);

            $conditions[$key] = $value;
        }

        return $conditions;
    }

    /**
     * Returns an array of condition keys for the given notification type and model key
     * 
     * @param  string $notification_type
     * @param  string $model_key
     * @param  string $prepend   optional, if set will prepend output keys with $prepend followed by underscore
     * @return array
     */
    public static function get_required_condition_keys($notification_type, $model_key, $prepend = '')
    {
        $model_class = notification_model_helper::get_full_model_class_name($notification_type, $model_key);

        $keys = $model_class::$condition_keys;

        return ! $prepend
            ? $keys
            : array_map(function($key) use ($prepend) {
                return $prepend . '_' . $key;
            }, $keys);
    }

    /**
     * Returns a condition value from a set condition key
     * 
     * @param  string  $key
     * @return mixed  string, or null if no set condition value
     */
    public function get_value($key) {
        return isset($this->conditions[$key])
            ? $this->conditions[$key]
            : null;
    }

    /**
     * Returns a timestamp which is offset from the current time
     * 
     * @param  string  $relation  before|after
     * @return int
     */
    public function get_offset_timestamp_from_now($relation)
    {
        return $this->get_offset_timestamp_from_timestamp(time(), $relation);
    }

    /**
     * Returns a timestamp which is offset from the given original timestamp
     *
     * Note: When calculating the offset, this uses set "time_amount" and "time_unit" values
     * 
     * @param  string  $relation  before|after
     * @return int
     */

    public function get_offset_timestamp_from_timestamp($original_timestamp, $relation)
    {
        // get time offset (timestamp of condition-defined amount of time before current time)
        $date = \DateTime::createFromFormat('U', $original_timestamp, \core_date::get_server_timezone_object());
        $date->modify($this->get_relation_symbol($relation) . $this->get_value('time_amount') . ' ' . $this->get_value('time_unit'));
        $offset_timestamp = $date->getTimestamp();

        return $offset_timestamp;
    }

    /**
     * Returns a "+" or "-" from the readable relation value
     *
     * Note: this is intended to be used internally for calculating time offsets
     * 
     * @param  string  $relation  before|after
     * @return string
     */
    private function get_relation_symbol($relation)
    {
        return $relation == 'after' 
            ? '+' 
            : '-';
    }

}
