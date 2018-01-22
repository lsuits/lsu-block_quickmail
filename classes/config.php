<?php

class block_quickmail_config {

    /**
     * Returns a transformed config array, or specific value, for the given key (block or course relative)
     * 
     * @param  string  $key
     * @param  mixed  $courseorid  optional, if set, gets specific course configuration
     * @param  bool  $transformed  whether or not to transform the output values
     * @return mixed
     */
    public static function _c($key = '', $courseorid = 0, $transformed = true)
    {
        return $courseorid ? 
            self::course($courseorid, $key, $transformed) :
            self::block($key, $transformed);
    }

    /**
     * Returns a config array for the block, and specific key if given
     * 
     * @param  string  $key  optional, config key to return
     * @param  bool  $transformed  whether or not to transform the output values
     * @return array|mixed
     */
    public static function block($key = '', $transformed = true)
    {
        $default_output_channel = get_config('moodle', 'block_quickmail_output_channels_available');

        $block_config_array = [
            'allowstudents'             => get_config('moodle', 'block_quickmail_allowstudents'),
            'roleselection'             => get_config('moodle', 'block_quickmail_roleselection'),
            'receipt'                   => get_config('moodle', 'block_quickmail_receipt'),
            'prepend_class'             => get_config('moodle', 'block_quickmail_prepend_class'),
            'ferpa'                     => get_config('moodle', 'block_quickmail_ferpa'),
            'downloads'                 => get_config('moodle', 'block_quickmail_downloads'),
            'additionalemail'           => get_config('moodle', 'block_quickmail_additionalemail'),
            'output_channels_available' => $default_output_channel,
            'default_output_channel'    => $default_output_channel == 'all' 
                ? 'message' 
                : $default_output_channel,
            'allowed_user_fields'       => get_config('moodle', 'block_quickmail_allowed_user_fields')
        ];

        if ($transformed) {
            return self::get_transformed($block_config_array, $key);
        }

        return $key ? $block_config_array[$key] : $block_config_array;
    }

    /**
     * Returns a config array for the given course, and specific key if given
     * 
     * @param  mixed  $courseorid
     * @param  string  $key  optional, config key to return
     * @param  bool  $transformed  whether or not to transform the output values
     * @return array|mixed
     */
    public static function course($courseorid, $key = '', $transformed = true)
    {
        global $DB;

        $course_id = is_object($courseorid) ? $courseorid->id : $courseorid;

        // get this course's config, if any
        $course_config = $DB->get_records_menu('block_quickmail_config', ['coursesid' => $course_id], '', 'name,value');

        // get the master block config
        $block_config = self::block('', false);
        
        // determine allowstudents for this course
        if ((int) $block_config['allowstudents'] < 0) {
            $course_allow_students = 0;
        } else {
            $course_allow_students = array_key_exists('allowstudents', $course_config) ? 
                $course_config['allowstudents'] : 
                $block_config['allowstudents'];
        }

        // determine default output_channel, if any, for this course
        // NOTE: block-level "all" will default to course-level "message"
        if ($block_config['output_channels_available'] == 'all') {
            $course_default_output_channel = array_key_exists('default_output_channel', $course_config) ? 
                $course_config['default_output_channel'] : 
                'message';
        } else {
            $course_default_output_channel = $block_config['output_channels_available'];
        }

        $course_config_array = [
            'allowstudents'             => $course_allow_students,
            'roleselection'             => array_key_exists('roleselection', $course_config) 
                ? $course_config['roleselection'] 
                : $block_config['roleselection'],
            'receipt'                   => array_key_exists('receipt', $course_config) 
                ? $course_config['receipt'] 
                : $block_config['receipt'],
            'prepend_class'             => array_key_exists('prepend_class', $course_config) 
                ? $course_config['prepend_class'] 
                : $block_config['prepend_class'],
            'ferpa'                     => $block_config['ferpa'],
            'downloads'                 => $block_config['downloads'],
            'additionalemail'           => $block_config['additionalemail'],
            'output_channels_available' => $block_config['output_channels_available'],
            'default_output_channel'    => $course_default_output_channel,
            'allowed_user_fields'       => $block_config['allowed_user_fields']
        ];

        if ($transformed) {
            return self::get_transformed($course_config_array, $key);
        }

        return $key ? $course_config_array[$key] : $course_config_array;
    }

    /**
     * Returns a transformed array from the given array
     * 
     * @param  array  $params
     * @param  string $key  optional, config key to return
     * @return array|mixed
     */
    public static function get_transformed($params, $key = '')
    {
        $transformed = [
            'allowstudents'             => (int) $params['allowstudents'],
            'roleselection'             => (string) $params['roleselection'],
            'receipt'                   => (int) $params['receipt'],
            'prepend_class'             => (string) $params['prepend_class'],
            'ferpa'                     => (int) $params['ferpa'],
            'downloads'                 => (int) $params['downloads'],
            'additionalemail'           => (int) $params['additionalemail'],
            'output_channels_available' => (string) $params['output_channels_available'],
            'default_output_channel'    => (string) $params['default_output_channel'],
            'allowed_user_fields'       => explode(',', $params['allowed_user_fields'])
        ];

        return $key ? $transformed[$key] : $transformed;
    }

    /**
     * Returns the user table field names that may be configured to be injected dynamically into messages
     * 
     * @return array
     */
    public static function get_supported_data_injection_fields() {
        return [
            'firstname',
            'middlename',
            'lastname',
            'email',
            'alternatename',
        ];
    }

    /**
     * Returns the supported output channels
     * 
     * @return array
     */
    public static function get_supported_output_channels() {
        return [
            'all',
            'message',
            'email'
        ];
    }

    /**
     * Returns an array of editor options with a given context
     * 
     * @param  object $context
     * @return array
     */
    public static function get_editor_options($context)
    {
        return [
            'trusttext' => true,
            'subdirs' => true,
            'maxfiles' => EDITOR_UNLIMITED_FILES,
            // 'accepted_types' => '*',
            'context' => $context
        ];
    }

    /**
     * Returns an array of filemanager options
     * 
     * @return array
     */
    public static function get_filemanager_options()
    {
        return [
            'subdirs' => 1, 
            'accepted_types' => '*'
        ];
    }

    /**
     * Updates a given course's settings to match the given params
     * 
     * @param  mixed  $courseorid
     * @param  array $params
     * @return void
     */
    public static function update_course_config($courseorid, $params = [])
    {
        global $DB;

        $course_id = is_object($courseorid) ? $courseorid->id : $courseorid;

        // first, clear out old settings
        self::delete_course_config($course_id);

        // next, iterate over each given param, inserting each record for this course
        foreach ($params as $name => $value) {
            $config = new \stdClass;
            $config->coursesid = $course_id;
            $config->name = $name;
            $config->value = $value;

            $DB->insert_record('block_quickmail_config', $config);
        }
    }

    /**
     * Deletes a given course's settings
     * 
     * @param  mixed  $courseorid
     * @return void
     */
    public static function delete_course_config($courseorid)
    {
        global $DB;

        $course_id = is_object($courseorid) ? $courseorid->id : $courseorid;

        $DB->delete_records('block_quickmail_config', ['coursesid' => $course_id]);
    }

}