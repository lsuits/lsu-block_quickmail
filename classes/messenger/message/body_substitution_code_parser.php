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

namespace block_quickmail\messenger\message;

use block_quickmail\messenger\message\substitution_code;
use block_quickmail_string;
use block_quickmail\exceptions\body_parser_exception;

class body_substitution_code_parser {

    public $body;
    public $codes = [];

    public function __construct($body) {
        $this->body = trim($body);
    }

    /**
     * Returns an array of substiution codes from the given body
     * 
     * @return array
     */
    public static function get_codes($body)
    {
        $parser = new self($body);

        $parser->parse_codes();

        return $parser->codes;
    }

    /**
     * Validate the message body to make sure:
     *  - any substitution codes are formatted properly
     *  - any substitution codes are within the given code classes
     *  
     * @param  string  $body         the message body to be validated
     * @param  array   $code_classes  substitition code classes that are allowed to be used
     * @return array   any invalid code messages
     */
    public static function validate_body($body, $code_classes = [])
    {
        $parser = new self($body);

        $allowed_codes = substitution_code::get($code_classes);

        $unallowed_codes = $parser->validate_codes($allowed_codes);

        if (empty($unallowed_codes)) {
            return [];
        }

        $invalid_messages = array_map(function($code) {
            return block_quickmail_string::get('invalid_custom_data_key', $code);
        }, $unallowed_codes);

        return $invalid_messages;
    }

    /**
     * Parses through the body, returning an array of any found codes to the stack
     * 
     * @return array
     */
    public function parse_codes()
    {
        // make a copy of the message body for manipulation
        $message = '_' . $this->body;

        // while there still exists a substitution code in the message body
        while ($next_first_delimiter = strpos($message, substitution_code::first_delimiter())) {
            // trim up until the delimiter
            $message = substr($message, $next_first_delimiter + strlen(substitution_code::first_delimiter()));

            $next_last_delimiter = strpos($message, substitution_code::last_delimiter());

            // get the substitution code
            $code = substr($message, 0, $next_last_delimiter);

            // add to the stack
            $this->add_code($code);

            // trim the value and ending delimiter out of the remaining message and continue
            $message = '_' . substr($message, $next_last_delimiter + strlen(substitution_code::last_delimiter()));
        }

        return $this->codes;
    }

    /**
     * Parses through the body and throws an exception if an error was found
     *
     * @param  array  $allowed_codes  substitution codes that are allowed to be present in body
     * @return array
     * @throws body_parser_exception(message) if codes formatted improperly
     * @throws body_parser_invalid_codes_exception(message, [codes]) if unsupported codes are found
     */
    public function validate_codes($allowed_codes = [])
    {
        if (empty($allowed_codes)) {
            $this->throw_parser_exception(block_quickmail_string::get('invalid_custom_data_not_allowed'));
        }

        // make a copy of the message body for manipulation
        $message = '_' . $this->body;

        // first, get the position of first delimiters
        $first_first_delimiter_pos = strpos($message, substitution_code::first_delimiter());
        $first_last_delimiter_pos = strpos($message, substitution_code::last_delimiter());

        // if a "last delimiter" was found
        if ($first_last_delimiter_pos !== false) {
            // and a "first delimiter" was not found
            if ($first_first_delimiter_pos == false) {
                $this->throw_parser_exception(block_quickmail_string::get('invalid_custom_data_delimiters'));
            // or the first "first delimiter" appears after the first "last delimiter"
            } else if ($first_first_delimiter_pos > $first_last_delimiter_pos) {
                $this->throw_parser_exception(block_quickmail_string::get('invalid_custom_data_delimiters'));
            }
        }

        // while there still exists a substitution code in the message body
        while ($next_first_delimiter = strpos($message, substitution_code::first_delimiter())) {
            // trim up until the delimiter
            $message = substr($message, $next_first_delimiter + strlen(substitution_code::first_delimiter()));

            // if no ending delimiter, no bueno
            if ( ! $next_last_delimiter = strpos($message, substitution_code::last_delimiter())) {
                $this->throw_parser_exception(block_quickmail_string::get('invalid_custom_data_delimiters'));
            }

            // get the substitution code
            $code = substr($message, 0, $next_last_delimiter);

            if (strpos($code, ' ') !== false) {
                $this->throw_parser_exception(block_quickmail_string::get('invalid_custom_data_delimiters'));
            }

            // add to the stack
            $this->add_code($code);

            // trim the value and ending delimiter out of the remaining message and continue
            $message = '_' . substr($message, $next_last_delimiter + strlen(substitution_code::last_delimiter()));
        }

        $unallowed_codes = [];

        foreach ($this->codes as $found) {
            if ( ! in_array($found, $allowed_codes)) {
                array_push($unallowed_codes, $found);
            }
        }

        return $unallowed_codes;
    }

    /**
     * Adds delimiters to the given code and adds to the code stack
     * 
     * @param string $code
     */
    private function add_code($code)
    {
        $this->codes[] = $code;
    }

    private function throw_parser_exception($message)
    {
        throw new body_parser_exception($message);
    }

}