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

namespace block_quickmail\messenger;

class message_body_parser {

    public $message_body;
    public $supported_keys;
    public $user_fields_keys;

    /**
     * Construct the message body parser
     * 
     * @param string  $message_body     the message content body
     * @param array   $supported_keys   custom user data keys supported by the site config
     */
    public function __construct($message_body, $supported_keys = []) {
        $this->message_body = $message_body;
        $this->supported_keys = $supported_keys;
        $this->user_fields_keys = [];
    }

    /**
     * Returns the delimeter that should be typed in front of the custom user data key
     * 
     * @return string
     */
    public function get_first_delimiter()
    {
        return '[:';
    }

    /**
     * Returns the delimeter that should be typed behind the custom user data key
     * 
     * @return string
     */
    public function get_last_delimiter()
    {
        return ':]';
    }

    /**
     * Parses the message body for instances of injected user data and sets and returns those field names "keys"
     * 
     * @return array
     */
    public function get_keys_from_message_body()
    {
        // make a copy of the message body for manipulation
        $message = '_' . trim($this->message_body);

        $first_delimeter = $this->get_first_delimiter();
        $last_delimeter = $this->get_last_delimiter();

        // while there still exists a custom user data injection key in the message body
        while ($next_first_delimiter = strpos($message, $first_delimeter)) {
            // trim up until the delimiter
            $message = substr($message, $next_first_delimiter + strlen($first_delimeter));

            // if no ending delimiter, no bueno
            if ( ! $next_last_delimeter = strpos($message, $last_delimeter)) {
                throw new \Exception('Custom data delimiters now formatted properly.');
            }

            // get the custom user key and add to the results
            $user_field_key = substr($message, 0, $next_last_delimeter);

            $this->user_fields_keys[] = $user_field_key;
            
            // trim the value and ending delimiter out of the remaining message and continue
            $message = substr($message, $next_last_delimeter + strlen($last_delimeter));
        }

        return $this->user_fields_keys;
    }

    /**
     * Checks that the message body does NOT contain any unsupported data keys
     * 
     * @return array [success status boolean, array of all (assumed valid) keys]
     */
    public function validate_supported_fields()
    {
        $this->get_keys_from_message_body();

        $success = count(array_diff($this->user_fields_keys, $this->supported_keys)) ? false : true;

        return [$success, $this->user_fields_keys];
    }

}