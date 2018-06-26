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
     * Reports whether or not this form was submitted and validated with the "next" action
     * 
     * @return bool
     */
    public function is_validated_next()
    {
        return $this->is_validated() && $this->is_action('next');
    }

    /**
     * Reports whether or not this form was submitted and with the "back" action
     * 
     * @return bool
     */
    public function is_submitted_back()
    {
        return $this->is_submitted_action('back');
    }

    /**
     * Reports whether or not this form was submitted and with the given action
     * 
     * @param  string  $action
     * @param  array   $actions  optional array of additional actions to listen for
     * @return bool
     */
    public function is_submitted_action($action, $actions = [])
    {
        return $this->is_submitted() && $this->is_action($action, $actions);
    }

    /**
     * Reports whether or not this form was submitted with the given action
     * 
     * @param  string   $type  back|next
     * @param  array   $actions  optional array of additional actions to listen for
     * @return bool
     */
    private function is_action($type, $actions = [])
    {
        return $this->get_action($actions) == $type;
    }

    /**
     * Returns which action was submitted in this form
     * 
     * @param  array   $actions  optional array of additional actions to listen for
     * @return mixed  string|null
     */
    private function get_action($actions = [])
    {
        $data = $this->get_submitted_data();

        $actions = array_merge(['next', 'back'], $actions);

        foreach ($actions as $action) {
            if (property_exists($data, $action)) {
                return $action;
            }
        }

        return null;
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
     * Returns the current session store data, or a given key's value
     * 
     * @param  string  $key  optional
     * @return mixed
     */
    public function get_session_stored($key = null)
    {
        $stored = $this->get_custom_data('stored');

        if (empty($key)) {
            return $stored;
        }

        return array_key_exists($key, $stored) ? $stored[$key] : '';
    }

    /**
     * Reports whether or not data of a given key exists in the controller session store
     * 
     * @param  string  $key
     * @return mixed
     */
    public function has_session_stored($key)
    {
        $stored = $this->get_custom_data('stored');

        return array_key_exists($key, $stored);
    }

}