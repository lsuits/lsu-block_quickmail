<?php

use \block_quickmail\exceptions\authorization_exception;

class block_quickmail_plugin {

    public static $name = 'block_quickmail';

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


    //////////////////// DEPRECATED /////////////////////////////
    
    ////////////////////////////////////////////////////
    ///
    ///  CONTEXT
    ///  
    ////////////////////////////////////////////////////

    /**
     * Resolves a context (system or course) based on a given course id
     * 
     * @param string  $type        system|course
     * @param int     $course_id
     * @throws critical_exception
     * @return mixed  context_system|context_course, course
     */
    public static function resolve_context($type = 'system', $course_id = 0) {
        switch ($type) {
            case 'course':
                // if course context is required, make sure we have an id
                if (empty($course_id)) {
                    throw new critical_exception('no_course', $course_id);
                }
                
                // fetch the course
                $course = self::get_valid_course($course_id);

                // fetch the course context
                $context = context_course::instance($course->id);

                // return the context AND course
                return [$context, $course];

                break;
            
            case 'system':
            default:
                // return only the context
                return context_system::instance();
                break;
        }
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
        if ($permission == 'cansend' && block_quickmail_config::block('allowstudents')) {
            return true;
        }

        // finally, check capability
        return has_capability('block/quickmail:' . $permission, $context);
    }

}