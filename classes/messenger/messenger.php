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

use block_quickmail_plugin;
use block_quickmail\requests\compose_message_request;
use block_quickmail\messenger\validators\send_validator;
use block_quickmail\messenger\exceptions\messenger_validation_exception;
use block_quickmail\persistents\signature;

class messenger {

    public $context;
    public $user;
    public $message_data;
    public $course;
    public $draft_message;
    public $message_scope;  // course|system
    public $signature;
    public $config;
    public $validation_errors;
    public $custom_user_data_keys;
    
    // message_data
        // public $subject;
        // public $additional_emails;
        // public $message;
        // public $signature_id;
        // public $output_channel;
        // public $receipt;

    /**
     * Construct the messenger service
     * 
     * @param context                                    $context
     * @param mdl_user                                   $user            the sending user
     * @param object                                     $message_data    all posted message data
     * @param mdl_course|null                            $course          moodle course that this message is being sent in
     * @param block_quickmail\persistents\message|null   $draft_message   
     */
    public function __construct($context, $user, $message_data, $course = null, $draft_message = null) {
        $this->context = $context;
        $this->user = $user;
        $this->message_data = $message_data;
        $this->course = $course;
        $this->draft_message = $draft_message;
        $this->validation_errors = [];
        $this->custom_user_data_keys = [];
        $this->set_message_scope();
        $this->set_config();
        $this->set_signature();
    }

    /**
     * Sets the "message scope" of the messenger (course or system)
     *
     * @return void
     */
    private function set_message_scope() {
        $this->message_scope = str_replace('context_', '', get_class($this->context));
    }

    /**
     * Sets the config array for this messenger instance (course-specific, or global) depending on scope
     */
    private function set_config() {
        $this->config = block_quickmail_plugin::_c('', $this->course ? $this->course->id : 0);
    }

    /*
     * Sets the user signature
     */
    private function set_signature() {
        $this->signature = signature::find_or_null($this->message_data->signature_id);
    }

    /**
     * Sends a message given a request and returns a response
     * 
     * @param  compose_message_request  $compose_message_request
     * @return object
     * @throws messenger_validation_exception
     */
    public static function send_by_request(compose_message_request $compose_message_request)
    {
        $messenger = new self(
            $compose_message_request->form->context,
            $compose_message_request->form->user,
            $compose_message_request->get_request_data_object(),
            $compose_message_request->form->course,
            $compose_message_request->form->draft_message
        );
        
        // $data->user = \core_user::get_user(2);
        // $data->course = get_course(3);
        // $data->subject = 'Oh my stars';
        // $data->additional_emails = ['chad@chad.chad', 'robert@robert.rob', 'linda@linda.lin'];
        // $data->message = 'This is the meat of it!!!';
        // $data->signature_id = 1;
        // $data->output_channel = 'mail';
        // $data->receipt = 1;

        $messenger_response = $messenger->send();

        return $messenger_response;
    }

    /**
     * Attempts to validate, authorize and send the message
     * 
     * @return [type] [description]
     * @throws messenger_validation_exception
     */
    private function send() {

        if ( ! $this->validate_send()) {
            $this->throw_validation_exception();
        }

        // TODO.........

        // authorization
            // user can send in this course?
            // 
            // $context = get_context_instance(CONTEXT_COURSE, $courseid, MUST_EXIST);
            // $enrolled = is_enrolled($context, $USER->id, '', true);

        // work has stopped HERE

        return $this;
    }

    /**
     * Validates the send of this message and returns success status
     * 
     * @return bool
     */
    private function validate_send() {
        // instantiate a new "send" validator
        $validator = new send_validator($this);

        // perform the validation
        $validator->validate();

        // grab the errors, and custom user data keys found
        $this->validation_errors = $validator->errors;
        $this->custom_user_data_keys = $validator->custom_user_data_keys;

        // return success status boolean
        return count($this->validation_errors) ? false : true;
    }

    /**
     * Throws a validation exception with the given message
     * 
     * @param  string $message
     * @return void
     * @throws messenger_validation_exception
     */
    private function throw_validation_exception($message = 'Messenger validation exception') {
        throw new messenger_validation_exception($message, $this->validation_errors);
    }

}