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

class block_quickmail_request {

    public $form;
    public $data;

    public function __construct()
    {
        $this->form = null;
        $this->data = (object)[];
    }

    ////////////////////////////////////////
    ///
    /// INSTANTIATION METHODS
    /// 
    ////////////////////////////////////////

    /**
     * Returns an instantiated request for the given "route"
     * 
     * @param  string  $route_name
     * @return object
     */
    public static function for_route($route_name)
    {
        $request = '\block_quickmail\requests\\' . $route_name . '_request';

        return new $request;
    }

    /**
     * Sets the given form on the request object
     * 
     * @param  object  $form
     * @return object
     */
    public function with_form($form)
    {
        $this->form = $form;

        if ($this->was_submitted()) {
            $this->data = static::get_transformed_post_data($this->form->get_data());
        }

        return $this;
    }

    ////////////////////////////////////////
    ///
    /// FORM SUBMISSION HANDLING
    /// 
    ////////////////////////////////////////

    public static function get_transformed_post_data($form_data)
    {
        return static::get_transformed($form_data);
    }

    /**
     * Reports whether or not this request was a submission of data
     * Optionally, whether or not a specific action was submitted
     * 
     * @param  string  $action  save|send
     * @return bool
     */
    public function was_submitted($action = null) {

        if (empty($this->form)) {
            return false;
        }

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
     * Reports whether or not the submitted request has a given input element key
     * 
     * @param  string  $input_element_key
     * @return bool
     */
    public function has_form_data_key($input_element_key) {
        // get raw form data
        $form_data = $this->form->get_data();

        // if no post data, return false
        if (empty($form_data)) {
            return false;
        }

        // return whether the given key exists in the posted data
        return property_exists($form_data, $input_element_key);
    }

    /**
     * Reports whether or not the submitted request has an input element key that matches the given value
     * 
     * @param  string  $input_element_key
     * @param  string  $value
     * @return bool
     */
    public function has_form_data_matching($input_element_key, $value) {
        // if the given element key does not exist in the post, return false
        if ( ! $this->has_form_data_key($input_element_key)) {
            return false;
        }
        
        // get raw form data
        $form_data = $this->form->get_data();

        return $form_data->$input_element_key == $value;
    }

    /**
     * Reports whether or not the submitted request has an input element key that matches the given value and is not empty
     * 
     * @param  string  $input_element_key
     * @return bool
     */
    public function has_non_empty_form_data($input_element_key) {
        // if the given element key does not exist in the post, return false
        if ( ! $this->has_form_data_key($input_element_key)) {
            return false;
        }
        
        // get raw form data
        $form_data = $this->form->get_data();

        return ! empty($form_data->$input_element_key);
    }

    /**
     * Reports whether or not this request is a form cancellation
     * 
     * @return bool
     */
    public function is_form_cancellation()
    {
        if (empty($this->form)) {
            return false;
        }

        return (bool) $this->form->is_cancelled();
    }

    /**
     * Reports whether or not the request was submitted with intent to save
     * 
     * @return bool
     */
    public function to_save() {
        return $this->was_submitted('save');
    }

    ////////////////////////////////////////
    ///
    /// REDIRECTS
    /// 
    ////////////////////////////////////////

    /**
     * Convenience wrapper for redirecting to moodle URLs
     * 
     * @param  string  $url
     * @param  array   $url_params   array of parameters for the given URL
     * @param  int     $delay        delay, in seconds, before redirecting
     * @return (http redirect header)
     */
    public function redirect_to_url($url, $url_params = [], $delay = 2) {
        $moodle_url = new \moodle_url($url, $url_params);

        redirect($moodle_url, '', $delay);
    }

    /**
     * Convenience wrapper for redirecting to moodle URLs while including a status type and message
     * 
     * @param  string  $type         success|info|warning|error
     * @param  string  $message      a pre-rendered string message
     * @param  string  $url
     * @param  array   $url_params   array of parameters for the given URL
     * @param  int     $delay        delay, in seconds, before redirecting
     * @return (http redirect header)
     */
    public function redirect_as_type($type, $message, $url, $url_params = [], $delay = 2) {
        $types = [
            'success' => \core\output\notification::NOTIFY_SUCCESS,
            'info'    => \core\output\notification::NOTIFY_INFO,
            'warning' => \core\output\notification::NOTIFY_WARNING,
            'error'   => \core\output\notification::NOTIFY_ERROR,
        ];

        $moodle_url = new \moodle_url($url, $url_params);

        redirect($moodle_url, $message, $delay, $types[$type]);
    }

    /**
     * Helper function to redirect as type success
     */
    public function redirect_as_success($message, $url, $url_params = [], $delay = 2) {
        $this->redirect_as_type('success', $message, $url, $url_params, $delay);
    }

    /**
     * Helper function to redirect as type info
     */
    public function redirect_as_info($message, $url, $url_params = [], $delay = 2) {
        $this->redirect_as_type('info', $message, $url, $url_params, $delay);
    }

    /**
     * Helper function to redirect as type warning
     */
    public function redirect_as_warning($message, $url, $url_params = [], $delay = 2) {
        $this->redirect_as_type('warning', $message, $url, $url_params, $delay);
    }

    /**
     * Helper function to redirect as type error
     */
    public function redirect_as_error($message, $url, $url_params = [], $delay = 2) {
        $this->redirect_as_type('error', $message, $url, $url_params, $delay);
    }

}