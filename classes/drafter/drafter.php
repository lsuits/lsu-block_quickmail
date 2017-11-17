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

namespace block_quickmail\drafter;

use block_quickmail_plugin;
use block_quickmail\requests\compose_message_request;
use block_quickmail\drafter\validators\save_validator;
use block_quickmail\drafter\exceptions\drafter_authentication_exception;
use block_quickmail\drafter\exceptions\drafter_validation_exception;
use block_quickmail\drafter\exceptions\drafter_critical_exception;
use block_quickmail\persistents\message;
use block_quickmail\persistents\signature;
use block_quickmail\persistents\alternate_email;
use block_quickmail\persistents\message_recipient;
use core_user;

class drafter {

    public $context;
    public $user;
    public $message_data;
    public $course;
    public $draft_message;
    public $message_scope;  // course|system
    public $signature;
    public $alternate_email;
    public $config;
    public $validation_errors;
    public $message;
    
    /**
     * Construct the drafter service
     * 
     * @param context                                    $context
     * @param mdl_user                                   $user            the sending user
     * @param object                                     $message_data    all posted message data
     * @param mdl_course|null                            $course          moodle course that this draft message is being sent in
     * @param block_quickmail\persistents\message|null   $draft_message   
     */
    public function __construct($context, $user, $message_data, $course = null, $draft_message = null) {
        $this->context = $context;
        $this->user = $user;
        $this->message_data = $message_data;
        $this->course = $course;
        $this->draft_message = $draft_message;
        $this->validation_errors = [];
        $this->message = null;
        $this->set_message_scope();
        $this->set_config();
        $this->set_signature();
        $this->set_alternate_email();
    }

    /**
     * Sets the "message scope" of the drafter (course or system)
     *
     * @return void
     */
    private function set_message_scope() {
        $this->message_scope = str_replace('context_', '', get_class($this->context));
    }

    /**
     * Sets the config array for this drafter instance (course-specific, or global) depending on scope
     */
    private function set_config() {
        $this->config = block_quickmail_plugin::_c('', $this->course ? $this->course->id : 0);
    }

    /*
     * Sets the user signature (if any)
     */
    private function set_signature() {
        $this->signature = signature::find_or_null($this->message_data->signature_id);
    }

    /*
     * Sets the user alternate email (if any)
     */
    private function set_alternate_email() {
        $this->alternate_email = alternate_email::find_or_null($this->message_data->alternate_email_id);
    }

    /**
     * Saves a message as a draft given a request and returns a response
     *
     * Note: this could either be a fresh new message OR a loaded draft
     * 
     * @param  compose_message_request  $compose_message_request
     * @return message
     * @throws drafter_authentication_exception
     * @throws drafter_validation_exception
     * @throws drafter_critical_exception
     */
    public static function save_by_compose_request(compose_message_request $compose_message_request)
    {
        // for now we'll just send a "fresh one"
        $drafter = new self(
            $compose_message_request->form->context,
            $compose_message_request->form->user,
            $compose_message_request->get_request_data_object(),
            $compose_message_request->form->course,
            $compose_message_request->form->draft_message
        );

        // first, make sure this user can send within this context
        if ( ! $drafter->authorize_save()) {
            $drafter->throw_authentication_exception();
        }

        // second, validate all form inputs
        if ( ! $drafter->validate_save()) {
            $drafter->throw_validation_exception();
        }

        // get select posted attributes
        $alternate_email_id = ! empty($drafter->alternate_email) ? $drafter->alternate_email->get('id') : 0;
        $signature_id = ! empty($drafter->signature) ? $drafter->signature->get('id') : 0;
        $subject = $drafter->message_data->subject;
        $body = $drafter->message_data->message;
        $output_channel = $drafter->message_data->output_channel;
        
        // if this is a draft message being saved, make sure it has not been sent and is updated with the latest data
        if ($drafter->is_draft_message()) {
            // if the draft has already been sent, throw an exception
            if (empty($drafter->draft_message->get('sent_at'))) {
                $drafter->throw_validation_exception('This message has already been sent.');

            // otherwise, update and set the draft message
            } else {
                // grab the draft message instance
                $draft = $drafter->draft_message;

                // update attributes that may have changed from compose page
                $draft->set('output_channel', $output_channel);
                $draft->set('alternate_email_id', $alternate_email_id);
                $draft->set('signature_id', $signature_id);
                $draft->set('subject', $subject);
                $draft->set('body', $body);
                $draft->set('is_draft', 1);
                $draft->update();
                
                // set the draft as the message
                $drafter->message = $draft->read();
            }
        } else {
            // instantiate a draft message
            $draft = new message(0, (object) [
                'course_id' => $drafter->course->id,
                'user_id' => $drafter->user->id,
                'output_channel' => $output_channel,
                'alternate_email_id' => $alternate_email_id,
                'signature_id' => $signature_id,
                'subject' => $subject,
                'body' => $body,
                'is_draft' => 1,
            ]);

            // save the draft message
            $draft->create();

            // set this new draft message as the message
            $drafter->message = $draft;
        }

        // clear any existing recipients, and add those that have been recently posted
        $drafter->message->sync_recipients($drafter->message_data->mailto_ids);

        // clear any existing additional emails, and add those that have been recently posted
        $drafter->message->sync_additional_emails($drafter->message_data->additional_emails);

        return $drafter->message;
    }

    /**
     * Reports whether or not the sender is authorized to save a draft message
     * 
     * @return bool
     */
    private function authorize_save() {
        return has_capability('block/quickmail:cansend', $this->context) || ! empty($this->config['allowstudents']);
    }

    /**
     * Reports whether or not this draft message is valid to be saved, if not, collects error messages
     *
     * @return bool
     */
    private function validate_save() {
        // instantiate a new "save" validator
        $validator = new save_validator($this);

        // perform the validation
        $validator->validate();

        // grab the errors found
        $this->validation_errors = $validator->errors;

        // return success status boolean
        return count($this->validation_errors) ? false : true;
    }

    /**
     * Reports whether or not this message is a draft that is being sent
     * 
     * @return bool
     */
    private function is_draft_message() {
        return ! empty($this->draft_message);
    }

    /**
     * Throws a authentication exception with the given message
     * 
     * @param  string $message
     * @return void
     * @throws drafter_authentication_exception
     */
    private function throw_authentication_exception($message = 'Messenger authentication exception') {
        throw new drafter_authentication_exception($message);
    }

    /**
     * Throws a validation exception with the given message
     * 
     * @param  string $message
     * @return void
     * @throws drafter_validation_exception
     */
    private function throw_validation_exception($message = 'Messenger validation exception') {
        throw new drafter_validation_exception($message, $this->validation_errors);
    }

    /**
     * Throws a critical exception with the given message
     * 
     * @param  string $message
     * @return void
     * @throws drafter_critical_exception
     */
    private function throw_critical_exception($message = 'Messenger critical exception') {
        throw new drafter_critical_exception($message);
    }

}