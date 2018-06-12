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

namespace block_quickmail\controllers\support;

require_once $CFG->libdir . '/formslib.php';

class controller_form extends \moodleform {

    public $errors;

    public function __construct($action = null, $customdata = null, $method = 'post', $target = '', $attributes = null, $editable = true) {
        parent::__construct($action, $customdata, $method, $target, $attributes, $editable);
    }

    public function definition()
    {
        $this->definition();
    }

    /**
     * Returns this form's custom data by key
     * 
     * @param  string  $key
     * @return mixed
     */
    public function get_custom_data($key)
    {
        return $this->_customdata[$key];
    }
    
    /**
     * Returns this form's "view name"
     * 
     * @return string
     */
    public function get_view_form_name()
    {
        return $this->get_custom_data('view_form_name');
    }

    /**
     * Returns the current session input data, or a given key's value
     * 
     * @param  string  $key  optional
     * @return mixed
     */
    public function get_session_input($key = null)
    {
        $input = $this->get_custom_data('input');

        if (empty($key)) {
            return $input;
        }

        return array_key_exists($key, $input) ? $input[$key] : '';
    }

}