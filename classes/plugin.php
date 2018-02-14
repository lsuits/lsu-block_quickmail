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

    ////////////////////////////////////////////////////
    ///
    ///  AUTHORIZATION
    ///  
    ////////////////////////////////////////////////////

    /**
     * Reports whether or not the authenticated user has the given permission within the given context
     * 
     * @param  string $permission  cansend|allowalternate|canconfig|myaddinstance
     * @param  object $context
     * @return bool
     */
    public static function user_has_permission($permission, $context) {
        // first, check for special cases...
        if ($permission == 'cansend' && block_quickmail_config::block('allowstudents')) {
            return true;
        }

        // finally, check capability
        return has_capability('block/quickmail:' . $permission, $context);
    }

    /**
     * Checks if the authenticated user ha the given permission within the given context and throws an exception if not so
     * 
     * @param  string $permission  cansend|allowalternate|canconfig|myaddinstance
     * @param  object $context
     * @return bool
     */
    public static function require_user_capability($permission, $context) {
        // first, check for special cases...
        if ($permission == 'cansend' && block_quickmail_config::block('allowstudents')) {
            return;
        }

        require_capability('block/quickmail:' . $permission, $context);
    }

}