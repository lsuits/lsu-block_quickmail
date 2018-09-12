<?php

namespace block_quickmail\notifier;

use block_quickmail\notifier\models\notification_model_helper;
use block_quickmail_string;

class notification_condition_summary {

    public $lang_string_key;
    public $params;

    public function __construct($lang_string_key, $params = []) {
        $this->lang_string_key = $lang_string_key;
        $this->params = $params;
    }

    /**
     * Returns an intelligently formatted condition summary string for a model
     * 
     * @param  string $notification_type
     * @param  string $model_key
     * @param  array  $params
     * @return string
     */
    public static function get_model_condition_summary($notification_type, $model_key, $params = [])
    {
        // get this model's supported keys
        if ( ! $keys = notification_model_helper::get_condition_keys_for_model($notification_type, $model_key)) {
            return '';
        }

        // get this model's condition summary lang string key
        $lang_string_key = notification_model_helper::get_condition_summary_lang_string($notification_type, $model_key);

        // filter out any unnecessary params
        $params = \block_quickmail_plugin::array_filter_key($params, function ($key) use ($keys) {
            return in_array($key, $keys);
        });

        // instantiate this summary class
        $summary = new self($lang_string_key, $params);

        // return formatted condition string
        return $summary->format();
    }

    /**
     * Returns a formatted string
     * 
     * @return string
     */
    public function format()
    {
        $params = $this->params;

        $lang_array = [];

        // iterate through each value, formatting and adding to the final array
        foreach (array_keys($params) as $key) {
            $lang_array[$key] = $this->format_condition_value($key, $params);
        }

        return block_quickmail_string::get($this->lang_string_key, (object) $lang_array);
    }

    /**
     * Returns a formatted value for the given condition key
     * 
     * @param  string  $key
     * @param  array   $values
     * @return string
     */
    private function format_condition_value($key, $values)
    {
        switch ($key) {
            case 'time_unit':
                if (array_key_exists('time_amount', $values)) {
                    // check if needs to be pluralized
                    return is_numeric($values['time_amount']) && $values['time_amount'] > 1
                        ? $values[$key] . 's'
                        : $values[$key];
                }
                break;
            
            // time_amount
            // time_relation
            // grade_greater_than
            // grade_less_than
            
            default:
                return $values[$key];
                break;
        }
    }

}