<?php

////////////////////////////////////////////////////
///
///  GENERAL TEST HELPERS
/// 
////////////////////////////////////////////////////

trait has_general_helpers {

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

        $default_default_message_type = $default_message_type == 'all' ? 'message' : $default_message_type;

        $supported_user_fields_string = implode(',', block_quickmail_config::get_supported_data_injection_fields());

        $params = [];

        $params['allowstudents'] = array_key_exists('allowstudents', $override_params) ? $override_params['allowstudents'] : (int) get_config('moodle', 'block_quickmail_allowstudents');
        $params['roleselection'] = array_key_exists('roleselection', $override_params) ? $override_params['roleselection'] : get_config('moodle', 'block_quickmail_roleselection');
        $params['receipt'] = array_key_exists('receipt', $override_params) ? $override_params['receipt'] : (int) get_config('moodle', 'block_quickmail_receipt');
        $params['prepend_class'] = array_key_exists('prepend_class', $override_params) ? $override_params['prepend_class'] : get_config('moodle', 'block_quickmail_prepend_class');
        $params['ferpa'] = array_key_exists('ferpa', $override_params) ? $override_params['ferpa'] : get_config('moodle', 'block_quickmail_ferpa');
        $params['downloads'] = array_key_exists('downloads', $override_params) ? $override_params['downloads'] : (int) get_config('moodle', 'block_quickmail_downloads');
        $params['additionalemail'] = array_key_exists('additionalemail', $override_params) ? $override_params['additionalemail'] : (int) get_config('moodle', 'block_quickmail_additionalemail');
        $params['message_types_available'] = array_key_exists('message_types_available', $override_params) ? $override_params['message_types_available'] : $default_message_type;
        $params['default_message_type'] = array_key_exists('default_message_type', $override_params) ? $override_params['default_message_type'] : $default_default_message_type;
        $params['allowed_user_fields'] = array_key_exists('allowed_user_fields', $override_params) ? $override_params['allowed_user_fields'] : $supported_user_fields_string;

        return $params;
    }

    public function update_system_config_value($config_name, $new_value)
    {
        global $DB;

        $record = $DB->get_record('config', ['name' => $config_name]);

        $record->value = $new_value;

        $DB->update_record('config', $record);
    }

}