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

namespace block_quickmail\requests;

// require_once('../../lib/weblib.php');

use block_quickmail\forms\course_config_form;
use block_quickmail_plugin;

class course_config_request extends \block_quickmail_request {

    public $form;

    public $form_data;

    public $course;
    
    public static $public_attributes = [
        'restore_flag',
        'allowstudents',
        'roleselection',
        'prepend_class',
        'receipt',
        'default_output_channel'
    ];

    /**
     * Construct the config submission request
     * 
     * @param course_config_form  $course_config_form  (extends moodleform)
     */
    public function __construct(course_config_form $course_config_form) {
        $this->form = $course_config_form;
        $this->form_data = ! empty($this->form) ? $this->form->get_data() : null;
        $this->course = $this->get_request_course();
    }

    public function get_request_course() {
        return $this->form->course;
    }
    
    /////////////////////////////////////////////////////////////
    ///
    ///  INSTANTIATION
    ///
    /////////////////////////////////////////////////////////////

    /**
     * Instantiates and returns a course config request
     * 
     * @param  \course_config_form   $course_config_form
     * @return \course_config_request
     */
    public static function make(course_config_form $course_config_form) {
        // instantiate "course config" request
        $request = new self($course_config_form);

        return $request;
    }

    /////////////////////////////////////////////////////////////
    ///
    ///  FORM SUBMISSION ACTIONS
    ///
    /////////////////////////////////////////////////////////////

    /**
     * Helper function to report whether or not the request was submitted with intent to save
     * 
     * @return bool
     */
    public function to_save_configuration() {
        return $this->was_submitted('save');
    }

    /**
     * Helper function to report whether or not the request was submitted with intent to restore block config to course
     * 
     * @return bool
     */
    public function to_restore_defaults() {
        return $this->has_form_data_matching('restore_flag', 1);
    }

    /////////////////////////////////////////////////////////////
    ///
    ///  ATTRIBUTES
    ///
    /////////////////////////////////////////////////////////////
    
    /**
     * Return this request's submitted data as a sanitized object
     * 
     * @return object
     */
    public function get_request_data_object() {
        $data = new \stdClass();
        
        $data->allowstudents = $this->allowstudents;
        $data->roleselection = $this->roleselection;
        $data->prepend_class = $this->prepend_class;
        $data->receipt = $this->receipt;
        $data->default_output_channel = $this->default_output_channel;

        return $data;
    }

    /**
     * Returns the "allow students" config setting
     * 
     * @return int
     */
    public function allowstudents($form_data = null) {
        return ! empty($form_data) ? (int) $this->form_data->allowstudents : 0;
    }

    /**
     * Returns the "role selection" config setting
     * 
     * @return string  comma-separated role names
     */
    public function roleselection($form_data = null) {
        return ! empty($form_data) ? implode(',', $this->form_data->roleselection) : '';
    }

    /**
     * Returns the "prepend class" config setting
     * 
     * @return string   idnumber|shortname|fullname
     */
    public function prepend_class($form_data = null) {
        return ! empty($form_data) ? (string) $this->form_data->prepend_class : '';
    }

    /**
     * Returns the "receipt" config setting
     * 
     * @return int
     */
    public function receipt($form_data = null) {
        return ! empty($form_data) ? (int) $this->form_data->receipt : 0;
    }

    /**
     * Returns the "default output channel" config setting
     * 
     * @return string     message|email
     */
    public function default_output_channel($form_data = null) {
        return ! empty($form_data) ? (string) $this->form_data->default_output_channel : 'message';
    }

    /////////////////////////////////////////////////////////////
    ///
    ///  REDIRECTS
    ///
    /////////////////////////////////////////////////////////////

    /**
     * Returns a redirect header back to the request's course page
     * 
     * @return (http redirect header)
     */
    public function redirect_back() {
        $this->redirect_as_type('info', block_quickmail_plugin::_s('cancel_and_redirect_to_course', $this->course->fullname), '/course/view.php', ['id' => $this->course->id], 2);
    }

    /**
     * Returns a redirect header towards the given course id's edit config page
     * 
     * @param  string     $notification_type
     * @param  int        $course_id
     * @param  string     $notification_text
     * @return (http redirect header)
     */
    public function redirect_to_course_config_page($notification_type, $course_id, $notification_text = null) {
        $this->redirect_as_type($notification_type, $notification_text, '/blocks/quickmail/configuration.php', ['courseid' => $course_id], 2);
    }

}
