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

abstract class block_quickmail_request {

    /////////////////////////////////////////////////////////////
    ///
    ///  ATTRIBUTES
    ///
    /////////////////////////////////////////////////////////////

    /**
     * Helper function for returning a request's attribute value
     * 
     * @param  string $attribute
     * @return mixed
     */
    public function __get($attribute) {
        // check if this is a valid public attribute of this request
        if ( ! in_array($attribute, static::$public_attributes)) {
            return null;
        }

        return $this->$attribute($this->form->get_data());
    }

    /////////////////////////////////////////////////////////////
    ///
    ///  FORM SUBMISSION ACTIONS
    ///
    /////////////////////////////////////////////////////////////

    /**
     * Reports whether or not this request was a form cancellation submission
     * 
     * @return bool
     */
    public function was_cancelled() {
        return (bool) $this->form->is_cancelled();
    }

    /**
     * Reports whether or not this request was a submission of data
     * Optionally, whether or not a specific action was submitted
     * 
     * @param  string  $action  save|send
     * @return bool
     */
    public function was_submitted($action = null) {
        // get raw form data
        $form_data = $this->form->get_data();

        // if no post data, return false
        if (empty($form_data)) {
            return false;
        }

        // otherwise, if no explicit action specified, return true
        if (empty($action)) {
            return true;
        }

        return property_exists($form_data, $action);
    }

    /**
     * Reports whether or not the submitted request has an input element key that matches the given value
     * 
     * @param  string  $input_element_key
     * @param  string  $value
     * @return bool
     */
    public function has_form_data_matching($input_element_key, $value) {
        // get raw form data
        $form_data = $this->form->get_data();

        // if no post data, return false
        if (empty($form_data)) {
            return false;
        }

        if ( ! property_exists($form_data, $input_element_key)) {
            return false;
        }

        return $form_data->$input_element_key == $value;
    }

    /////////////////////////////////////////////////////////////
    ///
    ///  REDIRECTS
    ///
    /////////////////////////////////////////////////////////////
    
    /**
     * Convenience wrapper for redirecting to moodle URLs
     * 
     * @param  string  $type         success|info|warning|error
     * @param  string  $message      a pre-rendered string message
     * @param  string  $url
     * @param  array   $url_params   array of parameters for the given URL
     * @param  int     $delay        delay, in seconds, before redirecting
     * @return (http redirect header)
     */
    public function redirect_as_type($type, $message, $url, $url_params = [], $delay = null) {
        $types = [
            'success' => \core\output\notification::NOTIFY_SUCCESS,
            'info'    => \core\output\notification::NOTIFY_INFO,
            'warning' => \core\output\notification::NOTIFY_WARNING,
            'error'   => \core\output\notification::NOTIFY_ERROR,
        ];

        $moodle_url = new \moodle_url($url, $url_params);

        redirect($moodle_url, $message, $delay, $types[$type]);
    }

}