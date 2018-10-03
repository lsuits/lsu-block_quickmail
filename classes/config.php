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

class block_quickmail_config {

    public static $course_configurable_fields = [
        'allowstudents',
        'roleselection',
        'receipt',
        'prepend_class',
        'default_message_type',
    ];

    /**
     * Returns a transformed config array, or specific value, for the given key (block or course relative)
     * 
     * @param  string  $key
     * @param  mixed  $courseorid  optional, if set, gets specific course configuration
     * @param  bool  $transformed  whether or not to transform the output values
     * @return mixed
     */
    public static function get($key = '', $courseorid = 0, $transformed = true)
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
        $default_message_type = get_config('moodle', 'block_quickmail_message_types_available');

        $block_config_array = [
            'allowstudents'             => get_config('moodle', 'block_quickmail_allowstudents'),
            'roleselection'             => get_config('moodle', 'block_quickmail_roleselection'),
            'send_as_tasks'             => get_config('moodle', 'block_quickmail_send_as_tasks'),
            'receipt'                   => get_config('moodle', 'block_quickmail_receipt'),
            'allow_mentor_copy'         => get_config('moodle', 'block_quickmail_allow_mentor_copy'),
            'email_profile_fields'      => get_config('moodle', 'block_quickmail_email_profile_fields'),
            'prepend_class'             => get_config('moodle', 'block_quickmail_prepend_class'),
            'ferpa'                     => get_config('moodle', 'block_quickmail_ferpa'),
            'downloads'                 => get_config('moodle', 'block_quickmail_downloads'),
            'additionalemail'           => get_config('moodle', 'block_quickmail_additionalemail'),
            'notifications_enabled'     => get_config('moodle', 'block_quickmail_notifications_enabled'),
            'send_now_threshold'        => get_config('moodle', 'block_quickmail_send_now_threshold'),
            'message_types_available'   => $default_message_type,
            'default_message_type'      => $default_message_type == 'all' 
                ? 'email' 
                : $default_message_type,
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

        // determine default message_type, if any, for this course
        // NOTE: block-level "all" will default to course-level "email"
        if ($block_config['message_types_available'] == 'all') {
            $course_default_message_type = array_key_exists('default_message_type', $course_config) 
                ? $course_config['default_message_type'] 
                : 'email';
        } else {
            $course_default_message_type = $block_config['message_types_available'];
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
            'send_as_tasks'             => $block_config['send_as_tasks'],
            'allow_mentor_copy'         => $block_config['allow_mentor_copy'],
            'email_profile_fields'      => $block_config['email_profile_fields'],
            'additionalemail'           => $block_config['additionalemail'],
            'message_types_available'   => $block_config['message_types_available'],
            'default_message_type'      => $course_default_message_type,
            'notifications_enabled'     => $block_config['notifications_enabled'],
            'send_now_threshold'        => $block_config['send_now_threshold'],
        ];

        if ($transformed) {
            return self::get_transformed($course_config_array, $key);
        }

        return $key ? $course_config_array[$key] : $course_config_array;
    }

    /**
     * Returns an array of role ids configured to be selectable when composing message
     * 
     * @param  object  $courseorid  optional, if not given will default to the block-level setting
     * @return array
     */
    public static function get_role_selection_array($courseorid = null)
    {
        // get course if possible
        if (empty($courseorid)) {
            $course = null;
        } else if (is_object($courseorid)) {
            $course = $courseorid;
        } else {
            try {
                $course = get_course($courseorid);
            } catch (\Exception $e) {
                $course = null;
            }
        }

        $roleselection_value = $course
            ? self::course($course, 'roleselection')
            : self::block('roleselection');

        return explode(',', $roleselection_value);
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
            'allow_mentor_copy'         => (int) $params['allow_mentor_copy'],
            'email_profile_fields'      => explode(',', $params['email_profile_fields']),
            'prepend_class'             => (string) $params['prepend_class'],
            'ferpa'                     => (string) $params['ferpa'],
            'downloads'                 => (int) $params['downloads'],
            'send_as_tasks'             => (int) $params['send_as_tasks'],
            'additionalemail'           => (int) $params['additionalemail'],
            'message_types_available'   => (string) $params['message_types_available'],
            'default_message_type'      => (string) $params['default_message_type'],
            'notifications_enabled'     => (int) $params['notifications_enabled'],
            'send_now_threshold'        => (int) $params['send_now_threshold'],
        ];

        return $key ? $transformed[$key] : $transformed;
    }

    /**
     * Returns the supported message types
     * 
     * @return array
     */
    public static function get_supported_message_types() {
        global $CFG;

        $types = [
            'all',
            'email'
        ];

        if ( ! empty($CFG->messaging)) {
            $types[] = 'message';
        }

        return $types;
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
            'maxfiles' => -1,
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
     * @param  object  $course
     * @param  array $params
     * @return void
     */
    public static function update_course_config($course, $params = [])
    {
        global $DB;

        // first, clear out old settings
        self::delete_course_config($course);

        $course_configurable_fields = self::$course_configurable_fields;

        // get rid of non-course-configurable fields
        $params = \block_quickmail_plugin::array_filter_key($params, function ($key) use ($course_configurable_fields) {
            return in_array($key, $course_configurable_fields);
        });

        // handle conversion of special cases...
        if (array_key_exists('roleselection', $params)) {
            if (is_array($params['roleselection'])) {
                // convert array to comma-delimited string for single field storage
                $params['roleselection'] = implode(',', $params['roleselection']);
            }
        }

        // next, iterate over each given param, inserting each record for this course
        foreach ($params as $name => $value) {
            $config = new \stdClass;
            $config->coursesid = $course->id;
            $config->name = $name;
            $config->value = $value;

            $DB->insert_record('block_quickmail_config', $config);
        }
    }

    /**
     * Deletes a given course's settings
     * 
     * @param  object  $course
     * @return void
     */
    public static function delete_course_config($course)
    {
        global $DB;

        $DB->delete_records('block_quickmail_config', ['coursesid' => $course->id]);
    }

    /**
     * Reports whether or the given course is configured to have FERPA restrictions or not
     *
     * FERPA restrictions = if true, any user that cannot access all groups in the course
     * will have limited results when pulling groups or users. These results are limited
     * to whichever groups the user is in, or the users within those groups.
     * 
     * @param  object  $course
     * @return bool
     */
    public static function be_ferpa_strict_for_course($course)
    {
        // get this block's ferpa setting
        $setting = self::block('ferpa', false);
        
        // if strict, the be strict
        if ($setting == 'strictferpa') {
            return true;
        }

        // if deferred to course, return what is configured by the course
        if ($setting == 'courseferpa') {
            return (bool) $course->groupmode;
        }

        // otherwise, do not be strict
        return false;
    }

}