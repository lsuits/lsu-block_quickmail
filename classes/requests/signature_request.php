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

use block_quickmail\persistents\signature;

// require_once('../../lib/weblib.php');

class signature_request extends \block_quickmail_request {

    public $form;

    public $form_data;

    public $data;

    public $course;
    
    public static $public_attributes = [
        'title',
        'signature',
        'default_flag'
    ];

    /**
     * Construct the message submission request
     * 
     * @param manage_signatures_form  $manage_signatures_form  (extends moodleform)
     */
    public function __construct(\manage_signatures_form $manage_signatures_form) {
        $this->form = $manage_signatures_form;
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
     * Instantiates and returns a signature request
     * 
     * @param  \manage_signatures_form   $manage_signatures_form
     * @return \signature_request
     */
    public static function make_signature_request(\manage_signatures_form $manage_signatures_form) {
        // instantiate "signature" request
        $request = new self($manage_signatures_form);

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
    public function to_save_signature() {
        return $this->was_submitted('save');
    }

    /**
     * Helper function to report whether or not the request was submitted with intent to delete
     * 
     * @return bool
     */
    public function to_delete_signature() {
        return $this->has_form_data_matching('delete_signature_flag', 1);
    }

    /////////////////////////////////////////////////////////////
    ///
    ///  ATTRIBUTES
    ///
    /////////////////////////////////////////////////////////////
    
    /**
     * Return this requests submitted data as a sanitized object
     * 
     * @return object
     */
    public function get_request_data_object() {
        $data = new \stdClass();
        
        $data->id = ! empty($this->form_data) ? (int) $this->form_data->select_signature_id : 0;
        $data->user_id = (int) $this->form->user->id;
        $data->title = $this->title;
        $data->signature = $this->signature;
        $data->default_flag = $this->default_flag;

        return $data;
    }

    /**
     * Returns the signature title
     * 
     * @return string
     */
    public function title($form_data = null) {
        return ! empty($form_data) ? (string) $this->form_data->title : '';
    }

    /**
     * Returns the signature's signature (body of content)
     * 
     * @return string
     */
    public function signature($form_data = null) {
        return ! empty($form_data) ? (string) $this->form_data->signature_editor['text'] : '';
    }

    /**
     * Returns an int representing whether or not this signature is the default
     * 
     * @return int
     */
    public function default_flag($form_data = null) {
        if (empty($form_data)) {
            return 0;
        }

        return ! empty($this->form_data->default_flag) ? (int) $this->form_data->default_flag : 0;
    }

    /////////////////////////////////////////////////////////////
    ///
    ///  REDIRECTS
    ///
    /////////////////////////////////////////////////////////////

    /**
     * Returns a redirect header back to the request's appropriate page (to course if exists, otherwise dashboard)
     * 
     * @return (http redirect header)
     */
    public function redirect_back() {
        // if no course id was provided, redirect back to "my page"
        if (empty($this->course)) {
            $this->redirect_as_type('info', \block_quickmail_plugin::_s('redirect_back_to_dashboard_from_signature'), '/my', [], 2);

        // otherwise, redirect back to course page
        } else {
            $this->redirect_as_type('info', \block_quickmail_plugin::_s('redirect_back_to_course_from_signature', $this->course->fullname), '/course/view.php', ['id' => $this->course->id], 2);
        }

    }

    /**
     * Returns a redirect header towards the given user's edit default signature page
     * 
     * @param  string     $notification_type
     * @param  core_user  $user
     * @param  string     $notification_text
     * @return (http redirect header)
     */
    public function redirect_to_edit_users_default_signature($notification_type, $user, $notification_text = null) {
        // get the user's default signature id, or default to 0
        if ($signature = signature::get_default_signature_for_user($user->id)) {
            $signature_id = $signature->get('id');
        } else {
            $signature_id = 0;
        }

        // redirect to the edit signature page
        self::redirect_to_edit_signature_id($notification_type, $signature_id, $notification_text);
    }

    /**
     * Returns a redirect header towards the given signature id's edit page
     * 
     * @param  string     $notification_type
     * @param  int        $signature_id
     * @param  string     $notification_text
     * @return (http redirect header)
     */
    public function redirect_to_edit_signature_id($notification_type, $signature_id, $notification_text = null) {
        // get the course's id, of default to 0
        $course_id = ! empty($this->course) ? $this->course->id : 0;

        $this->redirect_as_type($notification_type, $notification_text, '/blocks/quickmail/signature.php', ['id' => $signature_id, 'courseid' => $course_id], 2);
    }

}
