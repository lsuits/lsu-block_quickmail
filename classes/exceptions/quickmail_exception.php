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

namespace block_quickmail\exceptions;

use \Exception;
use \block_quickmail_plugin;

abstract class quickmail_exception extends Exception {
    
    public $lang_string_key;

    public $error_key = 'quickmail_error';

    public function __construct($lang_string_key = '', $lang_words = null) {
        $this->lang_string_key = $this->lang_string_key ?: $this->error_key;
        $this->lang_words = ! empty($lang_words) ? $lang_words : '';
    }

    public function get_lang_string()
    {
        return $this->lang_string_key;
    }

    public function get_lang_words()
    {
        return $this->lang_words;
    }

    public function render_moodle_error()
    {
        print_error($this->lang_string_key, block_quickmail_plugin::$name, '', $this->lang_words, null);
    }

}