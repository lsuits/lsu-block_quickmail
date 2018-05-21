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

namespace block_quickmail\messenger;

use block_quickmail_config;
use block_quickmail_string;

class body_parser {

    public $errors;
    public $message_body;
    public $supported_keys;
    public $message_keys;

    /**
     * Construct the message body parser
     * 
     * @param string  $message_body     the message content body
     */
    public function __construct($message_body) {
        $this->errors = [];
        $this->message_body = $message_body;
        $this->set_supported_keys();
        $this->set_message_keys();
        $this->validate_message_keys();
    }

    /**
     * Sets the block-level configured allowed user keys
     */
    private function set_supported_keys()
    {
        // @TODO : FIX THIS!!
        $this->supported_keys = block_quickmail_config::get('allowed_user_fields');
    }

    /**
     * Sets the message keys by parsing the set message body
     */
    private function set_message_keys()
    {
        $this->message_keys = $this->get_keys_from_message_body();
    }

    /**
     * Parses the message body for instances of injected data and returns those field names "keys"
     * 
     * @return array
     */
    public function get_keys_from_message_body()
    {
        // make a copy of the message body for manipulation
        $message = '_' . trim($this->message_body);

        $first_delimiter = self::get_first_delimiter();
        $last_delimiter = self::get_last_delimiter();

        $message_keys = [];

        // while there still exists a custom user data injection key in the message body
        while ($next_first_delimiter = strpos($message, $first_delimiter)) {
            // trim up until the delimiter
            $message = substr($message, $next_first_delimiter + strlen($first_delimiter));

            // if no ending delimiter, no bueno
            if ( ! $next_last_delimiter = strpos($message, $last_delimiter)) {
                $this->add_error(block_quickmail_string::get('invalid_custom_data_delimiters'));
            }

            // get the custom message key
            $message_key = substr($message, 0, $next_last_delimiter);

            // add to the stack
            $message_keys[] = $message_key;

            // trim the value and ending delimiter out of the remaining message and continue
            $message = substr($message, $next_last_delimiter + strlen($last_delimiter));
        }

        return $message_keys;
    }

    /**
     * Sets an error message in the stack for any parsed message key that is not supported by the given configuration
     * 
     * @return void
     */
    public function validate_message_keys()
    {
        foreach ($this->message_keys as $message_key) {
            if ( ! in_array($message_key, $this->supported_keys)) {
                $this->add_error(block_quickmail_string::get('invalid_custom_data_key', $message_key));
            }
        }
    }

    public static function get_delimited_key_stamp($value)
    {
        return self::get_first_delimiter() . $value . self::get_last_delimiter();
    }

    /**
     * Returns the delimiter that should be typed in front of the custom user data key
     * 
     * @return string
     */
    public static function get_first_delimiter()
    {
        return '[:';
    }

    /**
     * Returns the delimiter that should be typed behind the custom user data key
     * 
     * @return string
     */
    public static function get_last_delimiter()
    {
        return ':]';
    }

    /**
     * Includes the given error message in the stack of errors
     * 
     * @param string  $message
     * @return void
     */
    public function add_error($message)
    {
        $this->errors[] = $message;
    }

    /**
     * Reports whether or not this validator has any errors
     * 
     * @return bool
     */
    public function has_errors()
    {
        return (bool) count($this->errors);
    }

}