<?php

class block_quickmail_string {

    /**
     * Shortcut for get_string() for this plugin's lang strings
     * 
     * @param  string $key
     * @param  mixed $a  optional attributes
     * @return string
     */
    public static function get($key, $a = null) {
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
        return get_string($key, \block_quickmail_plugin::$name, $a);
    }

}