<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * @package    block_quickmail
 * @copyright  2008 onwards Louisiana State University
 * @copyright  2008 onwards Chad Mazilly, Robert Russo, Jason Peak, Dave Elliott, Adam Zapletal, Philip Cali
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

////////////////////////////////////////////////////
///
///  GENERAL TEST HELPERS
/// 
////////////////////////////////////////////////////

use block_quickmail\messenger\message\substitution_code;

trait has_general_helpers {

    public function dg()
    {
        return $this->getDataGenerator();
    }

    public function dd($thing)
    {
        var_dump($thing);
        die;
    }

    public function get_user_ids_from_user_array(array $users, $as_string = false)
    {
        $user_ids = array_map(function($user) {
            return $user->id;
        }, $users);

        return ! $as_string
            ? $user_ids
            : implode($user_ids, ',');
    }

    public function get_course_config_params(array $override_params = [])
    {
        $default_message_type = get_config('moodle', 'block_quickmail_message_types_available');

        $default_default_message_type = $default_message_type == 'all' ? 'email' : $default_message_type;

        $supported_user_fields_string = implode(',', substitution_code::get('user'));

        $params = [];

        $params['allowstudents'] = array_key_exists('allowstudents', $override_params) ? $override_params['allowstudents'] : (int) get_config('moodle', 'block_quickmail_allowstudents');
        $params['roleselection'] = array_key_exists('roleselection', $override_params) ? $override_params['roleselection'] : get_config('moodle', 'block_quickmail_roleselection');
        $params['receipt'] = array_key_exists('receipt', $override_params) ? $override_params['receipt'] : (int) get_config('moodle', 'block_quickmail_receipt');
        $params['prepend_class'] = array_key_exists('prepend_class', $override_params) ? $override_params['prepend_class'] : get_config('moodle', 'block_quickmail_prepend_class');
        $params['ferpa'] = array_key_exists('ferpa', $override_params) ? $override_params['ferpa'] : get_config('moodle', 'block_quickmail_ferpa');
        $params['downloads'] = array_key_exists('downloads', $override_params) ? $override_params['downloads'] : (int) get_config('moodle', 'block_quickmail_downloads');
        $params['allow_mentor_copy'] = array_key_exists('allow_mentor_copy', $override_params) ? $override_params['allow_mentor_copy'] : (int) get_config('moodle', 'block_quickmail_allow_mentor_copy');
        $params['additionalemail'] = array_key_exists('additionalemail', $override_params) ? $override_params['additionalemail'] : (int) get_config('moodle', 'block_quickmail_additionalemail');
        $params['message_types_available'] = array_key_exists('message_types_available', $override_params) ? $override_params['message_types_available'] : $default_message_type;
        $params['default_message_type'] = array_key_exists('default_message_type', $override_params) ? $override_params['default_message_type'] : $default_default_message_type;
        $params['send_now_threshold'] = array_key_exists('send_now_threshold', $override_params) ? $override_params['send_now_threshold'] : (int) get_config('moodle', 'block_quickmail_send_now_threshold');

        return $params;
    }

    public function update_system_config_value($config_name, $new_value)
    {
        global $DB;

        if ($record = $DB->get_record('config', ['name' => $config_name])) {
            $record->value = $new_value;

            $DB->update_record('config', $record);
        } else {
            $DB->insert_record('config', (object)[
                'name' => $config_name,
                'value' => $new_value,
            ]);
        }
    }

    public function override_params($values, $overrides)
    {
        foreach (array_keys($values) as $key) {
            if (array_key_exists($key, $overrides)) {
                $values[$key] = $overrides[$key];
            }
        }

        return $values;
    }

    public function get_timestamp_for_date($string)
    {
        $datetime = new \DateTime($string);
        
        return $datetime->getTimestamp();
    }

    public function get_past_time()
    {
        return $this->get_timestamp_for_date('mar 1 2017');
    }
    
    public function get_recent_time()
    {
        return $this->get_timestamp_for_date('may 12 2018');
    }
    
    public function get_now_time()
    {
        return $this->get_timestamp_for_date('now');
    }
    
    public function get_soon_time()
    {
        return $this->get_timestamp_for_date('july 4 2018');
    }
    
    public function get_future_time()
    {
        return $this->get_timestamp_for_date('nov 30 2018');
    }

}