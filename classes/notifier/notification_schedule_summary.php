<?php

namespace block_quickmail\notifier;

use block_quickmail_string;

class notification_schedule_summary {

    public $params;

    public static $date_format = 'M d Y, h:ia';

    public function __construct($params = []) {
        $this->params = $params;
    }

    /**
     * Returns an intelligently formatted schedule summary string from params
     * 
     * @param  array  $params
     * @return string
     */
    public static function get_from_params($params = [])
    {
        // instantiate this summary class
        $summary = new self($params);

        // return formatted schedule string
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

        if ( ! array_key_exists('time_amount', $this->params)) {
            return '';
        }

        if ( ! $this->params['time_amount']) {
            return '';
        }

        // append time unit/amount details
        $summary = $this->params['time_amount'] == 1
            ? block_quickmail_string::get('time_once_a') . ' ' . $this->display_time_unit($this->params['time_unit'])
            : block_quickmail_string::get('time_every') . ' ' . $this->params['time_amount'] . ' ' . $this->display_time_unit($this->params['time_unit'], $this->params['time_amount']);

        // if there is a begin date, format and append it
        if (array_key_exists('begin_at', $this->params)) {
            if (is_numeric($this->params['begin_at'])) {
                $begin_at = \DateTime::createFromFormat('U', $this->params['begin_at'], \core_date::get_server_timezone_object());
                    
                $summary .= ', ' . block_quickmail_string::get('time_beginning') . ' ' . $begin_at->format(self::$date_format);
            }
        }

        // if there is an end date, format and append it
        if (array_key_exists('end_at', $this->params)) {
            if (is_numeric($this->params['end_at'])) {
                $end_at = \DateTime::createFromFormat('U', $this->params['end_at'], \core_date::get_server_timezone_object());

                $summary .= ', ' . block_quickmail_string::get('time_ending') . ' ' . $end_at->format(self::$date_format);
            }
        }

        return $summary;
    }

    private function display_time_unit($key, $amount = 0)
    {
        return $amount > 1
            ? block_quickmail_string::get('time_unit_' . $key . 's')
            : block_quickmail_string::get('time_unit_' . $key);
    }

}
