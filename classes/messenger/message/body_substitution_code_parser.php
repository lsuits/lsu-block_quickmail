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
     * Adds delimiters to the given code and adds to the code stack
     * 
     * @param string $code
     */
    private function add_code($code)
    {
        $this->codes[] = $code;
    }

}