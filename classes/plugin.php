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
 * @copyright  2008-2017 Louisiana State University
 * @copyright  2008-2017 Adam Zapletal, Chad Mazilly, Philip Cali, Robert Russo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use \block_quickmail\exceptions\critical_exception;
use \block_quickmail\exceptions\authorization_exception;
// use \block_quickmail\exceptions\validation_exception;

class block_quickmail_plugin {

    public static $name = 'block_quickmail';
    
    public static $supported_output_channels = ['message', 'email'];

    /**
     * Constructor
     */
    public function __construct() {
        //
    }

    public static function get_db() {
        global $DB;
        
        return $DB;
    }

    public static function get_cfg() {
        global $CFG;
        
        return $CFG;
    }

    ////////////////////////////////////////////////////
    ///
    ///  CONTEXT
    ///  
    ////////////////////////////////////////////////////

    /**
     * Resolves a context (system or course) based on a given course id
     * 
     * @param int  $course_id
     * @throws critical_exception
     * @return context_system|context_course
     */
    public static function resolve_context($course_id = 0) {
        if (empty($course_id)) {
            throw new critical_exception('no_course', $course_id);
        }

        // make sure the course is valid
        $course = self::get_valid_course($course_id);

        // if this course if the "site course" return a system context, otherwise course context
        $context = $course_id == SITEID ? context_system::instance() : context_course::instance($course->id);

        return $context;
    }

    /**
     * Fetches a moodle course by id, if unavailable throw exception
     * 
     * @param  int $course_id
     * @return moodle course
     * @throws critical_exception
     */
    public static function get_valid_course($course_id) {
        try {
            $course = get_course($course_id);
        } catch (dml_exception $e) {
            throw new critical_exception('no_course', $course_id);
        }

        return $course;
    }

    ////////////////////////////////////////////////////
    ///
    ///  USER AUTHORIZATION
    ///  
    ////////////////////////////////////////////////////

    /**
     * Throws exception if authenticated user does not have the given permission within the given context
     * 
     * @param  string $permission  cansend|allowalternate|canconfig|myaddinstance
     * @param  object $context
     * @throws authorization_exception
     * @return void
     */
    public static function check_user_permission($permission, $context) {
        if ( ! self::user_has_permission_in_context($permission, $context)) {
            throw new authorization_exception('no_permission');
        }
    }

    /**
     * Reports whether or not the authenticated user has the given permission within the given context
     * 
     * @param  string $permission  cansend|allowalternate|canconfig|myaddinstance
     * @param  object $context
     * @return boolean
     */
    public static function user_has_permission_in_context($permission, $context) {
        // first, check for special cases...
        if ($permission == 'cansend' && self::get_block_config('allowstudents')) {
            return true;
        }

        // finally, check capability
        return has_capability('block/quickmail:' . $permission, $context);
    }

    ////////////////////////////////////////////////////
    ///
    ///  CONFIGURATION
    ///  
    ////////////////////////////////////////////////////

    /**
     * Returns a config array, or specific value, for the given key (block or course relative)
     * 
     * @param  string  $key
     * @param  int     $course_id   optional, if set, gets specific course configuration
     * @return mixed
     */
    public static function _c($key = '', $course_id = 0) {
        return $course_id ? 
            self::get_block_config($key) : 
            self::get_course_config($course_id, $key);
    }

    /**
     * Returns quickmail's block course config as array, or optionally a specific setting, if any, overriding with global block config options
     * 
     * @return array|bool
     */
    private static function get_course_config($course_id, $key = '') {
        $block_config = self::get_block_config();

        $course_config = self::get_db()->get_records_menu('block_quickmail_config', ['coursesid' => $course_id], '', 'name,value');

        $config = array_merge($block_config, $course_config);

        return $key ? $config[$key] : $config;
    }

    /**
     * Returns quickmail's block config as array, or optionally a specific setting
     * 
     * @param  string $key
     * @return mixed
     */
    private static function get_block_config($key = '') {
        $config = [
            // Convert Never (-1) to No (0) in case site config is changed.
            'allowstudents' => get_config('moodle', 'block_quickmail_allowstudents') !== -1 ?: 0,
            'roleselection' => get_config('moodle', 'block_quickmail_roleselection'),
            'prepend_class' => get_config('moodle', 'block_quickmail_prepend_class'),
            'receipt' => get_config('moodle', 'block_quickmail_receipt'),
            'ferpa' => get_config('moodle', 'block_quickmail_ferpa'),
            'allow_external_emails' => get_config('moodle', 'block_quickmail_addionalemail'),
            'output_channel' => get_config('moodle', 'block_quickmail_output_channel')
        ];

        return $key ? $config[$key] : $config;
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

    ////////////////////////////////////////////////////
    ///
    ///  LOCALIZATION
    ///  
    ////////////////////////////////////////////////////

    /**
     * Shortcut for get_string()
     * 
     * @param  string $key
     * @param  mixed $a  optional attributes
     * @return string
     */
    public static function _s($key, $a = null) {
        return self::get_block_string($key, $a);
    }

    /**
     * Returns a lang string for this plugin
     * 
     * @param  string $key
     * @param  mixed $a  optional attributes
     * @return string
     */
    private static function get_block_string($key, $a = null) {
        return get_string($key, self::$name, $a);
    }

    ///////////////////////////////////////////
    ///
    ///  MESSAGING
    /// 
    ///////////////////////////////////////////
    
    /**
     * Returns the configured message output channel, defaults to "message"
     *
     * @return string
     */
    public static function get_output_channel() {
        $configured_channel = self::_c('output_channel');

        return in_array($configured_channel, self::$supported_output_channels) ? $configured_channel : 'message';
    }

}