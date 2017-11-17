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
use block_quickmail\messenger\exceptions\messenger_authentication_exception;
use block_quickmail\messenger\exceptions\messenger_validation_exception;
use block_quickmail\messenger\exceptions\messenger_critical_exception;
use block_quickmail\persistents\message;
use block_quickmail\persistents\signature;
use block_quickmail\persistents\alternate_email;
use block_quickmail\persistents\message_recipient;
use core_user;

class messenger {

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
    public $custom_user_data_keys;
    public $message;
    
    // message_data
        // public $subject;
        // public $additional_emails;
        // public $message;
        // public $signature_id;
        // public $output_channel;
        // public $receipt;
        // public $alternate_email_id;

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
        $this->message = null;
        $this->set_message_scope();
        $this->set_config();
        $this->set_signature();
        $this->set_alternate_email();
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
     * Sends a message given a request and returns a response
     *
     * Note: this could either be a fresh new message OR a loaded draft
     * 
     * @param  compose_message_request  $compose_message_request
     * @return object
     * @throws messenger_authentication_exception
     * @throws messenger_validation_exception
     * @throws messenger_critical_exception
     */
    public static function send_by_compose_request(compose_message_request $compose_message_request)
    {
        // for now we'll just send a "fresh one"
        $messenger = new self(
            $compose_message_request->form->context,
            $compose_message_request->form->user,
            $compose_message_request->get_request_data_object(),
            $compose_message_request->form->course,
            $compose_message_request->form->draft_message
        );

        // get select posted attributes
        $alternate_email_id = ! empty($messenger->alternate_email) ? $messenger->alternate_email->get('id') : 0;
        $signature_id = ! empty($messenger->signature) ? $messenger->signature->get('id') : 0;
        $subject = $messenger->message_data->subject;
        $body = $messenger->message_data->message;
        $output_channel = $messenger->message_data->output_channel;
        
        // if this is a draft message being sent, make sure it has not been sent and is updated with the latest data
        if ($messenger->is_draft_send()) {
            // if the draft has already been sent, throw an exception
            if (empty($messenger->draft_message->get('sent_at'))) {
                $messenger->throw_validation_exception('This message has already been sent.');

            // otherwise, update and set the draft message
            } else {
                // grab the draft message instance
                $draft = $messenger->draft_message;

                // update attributes that may have changed from compose page
                $draft->set('output_channel', $output_channel);
                $draft->set('alternate_email_id', $alternate_email_id);
                $draft->set('signature_id', $signature_id);
                $draft->set('subject', $subject);
                $draft->set('body', $body);
                $draft->set('is_draft', 0);
                $draft->update();
                
                // set the draft as the message to be sent
                $messenger->message = $draft->read();
            }
        } else {
            // instantiate a message
            $message = new message(0, (object) [
                'course_id' => $messenger->course->id,
                'user_id' => $messenger->user->id,
                'output_channel' => $output_channel,
                'alternate_email_id' => $alternate_email_id,
                'signature_id' => $signature_id,
                'subject' => $subject,
                'body' => $body,
            ]);

            // save the message
            $message->create();

            // set this new message as the message to be sent
            $messenger->message = $message;
        }

        // clear any existing recipients, and add those that have been recently posted
        $messenger->message->sync_recipients($messenger->message_data->mailto_ids);

        // clear any existing additional emails, and add those that have been recently posted
        $messenger->message->sync_additional_emails($messenger->message_data->additional_emails);

        // attempt to send the set message to all recipients
        $messenger_response = $messenger->send_composed();

        return $messenger_response;
    }

    /**
     * Attempts to validate, authorize and send a composed (non-sent) message
     * 
     * @return [type] [description]
     * @throws messenger_authentication_exception
     * @throws messenger_validation_exception
     * @throws messenger_critical_exception
     */
    private function send_composed() {

        // first, make sure this user can send within this context
        if ( ! $this->authorize_send()) {
            $this->throw_authentication_exception();
        }

        // second, validate all form inputs
        if ( ! $this->validate_send()) {
            $this->throw_validation_exception();
        }

        // construct an appropriate message factory based on the output channel
        $message_factory = $this->make_message_factory();

        // iterate through each recipient by user_id
        foreach ($this->message_data->mailto_ids as $user_id) {
            
            // TODO: do something with this!
            if ( ! $user = core_user::get_user($user_id)) {
                // not an active or real user...
                // log this somehow
                continue;
            }

            // send the customized message to the user by appropriate channel
            // note (if channel='message') this returns the mdl_message->id
            $factory_response = $message_factory->send_message($user);

            // if successful response, mark this user as being sent to
            if ($factory_response) {
                message_recipient::mark_as_sent($this->message, $user, $this->convert_factory_response_to_id($factory_response));
            }

            // update the message as being sent
            $this->message->set('sent_at', time());
            $this->message->update();
        }

        // attempt to send to additional emails, if necessary
        $this->send_to_additional_emails($this->message->get_additional_emails());

        // if this sender has requested a receipt, send one
        if ($this->message_data->receipt) {
            $this->send_receipt_email();
        }

        return $this;
    }

    /**
     * Attempt to send a copy of the message to the given additional emails
     * 
     * @param  array  $additional_emails  (a collection of message_additional_email)
     * @return void
     */
    private function send_to_additional_emails($additional_emails = []) {
        $i = 99999800;

        foreach($additional_emails as $additional_email) {
            // format the message body by appending the signature
            // @TODO - find some way to clean out any custom data fields for this fake user (??)
            $message_body = ! empty($this->signature) 
                ? $this->signature->get_message_body_with_signature_appended($this->message_data->message) 
                : $this->message_data->message;

            // attempt to send the email
            if ($additional_email_success = $this->send_email($this->format_message_subject(), $message_body, $message_body, null, $additional_email->get('email'), $i)) {
                $additional_email->mark_as_sent();
            }
            
            $i++;
        }
    }

    private function send_receipt_email() {
        // format the message body by appending the signature
        // @TODO - find some way to clean out any custom data fields for this fake user (??)
        $message_body = ! empty($this->signature) 
            ? $this->signature->get_message_body_with_signature_appended($this->message_data->message) 
            : $this->message_data->message;

        $success = $this->send_email('Receipt: ' . $this->format_message_subject(), $message_body, $message_body, $this->user);

        return $success;
    }

    /**
     * Attempts to send an email from this message's user using the given parameters
     * 
     * @param  string          $subject            subject of the email
     * @param  string          $message_body_text  plain text body content
     * @param  string          $message_body_html  html body content
     * @param  core_user|null  $to_user            a real moodle user to receive the email (optional)
     * @param  string          $fake_user_email    the email address to send to if no real user was given
     * @param  string          $fake_user_id       a fake user id needed for sending purposes
     * @return bool
     */
    private function send_email($subject, $message_body_text, $message_body_html, $to_user = null, $fake_user_email = '', $fake_user_id = null) {
        
        // if no recipient user was given, make a fake user
        if ( ! $to_user) {
            // if no fake user id was given, generate one
            $id = ! empty($fake_user_id) ? $fake_user_id : mt_rand(99999800, 99999999);

            // get the constructed fake user
            $to_user = $this->make_fake_user($fake_user_email, $id);
        }

        $success = email_to_user($to_user, $this->user, $subject, $message_body_text, $message_body_html);

        return (bool) $success;
    }

    /**
     * Construct and return a "fake" user for direct emailing purposes
     * 
     * @param  string  $fake_user_email
     * @param  int     $fake_user_id
     * @return object
     */
    private function make_fake_user($fake_user_email, $fake_user_id) {
        $fakeuser = new \stdClass();
        $fakeuser->id = $fake_user_id;
        $fakeuser->email = $fake_user_email;
        $fakeuser->username = $fake_user_email;
        $fakeuser->mailformat = 1; // @TODO - make this configurable??

        return $fakeuser;
    }

    /**
     * Instantiates a message factory class based on the selected output channel
     * 
     * @return message_messenger_interface
     */
    private function make_message_factory() {
        $factory_class = $this->get_message_factory_class_name();

        return $factory_class::make([
            'userfrom' => $this->user,
            'subject' => $this->format_message_subject(),
            'fullmessagehtml' => $this->message_data->message,
            'alternate_email' => $this->alternate_email,
            'signature' => $this->signature,
            'custom_user_data_keys' => $this->custom_user_data_keys
        ]);
    }

    /**
     * Returns this message's factory class name based on the selected output channel
     * 
     * @return message_messenger_interface
     */
    private function get_message_factory_class_name() {
        return '\block_quickmail\messenger\factories\\' . $this->message_data->output_channel . '_message_factory';
    }

    /**
     * Returns the message subject formatted with any prepend options
     * 
     * @return string
     */
    public function format_message_subject() {
        // get the prepend_class setting
        $prepend = $this->config['prepend_class'];

        // if setting is valid and exists on the course, format subject, otherwise default to subject
        return ! empty($prepend) && ! empty($this->course->$prepend)
            ? '[' . $this->course->$prepend . '] ' . $this->message_data->subject
            : $this->message_data->subject;
    }

    
    /**
     * Converts the given response into an integer
     *
     * Note: for "message" output, this will be mdl_message->id, for "email" output this will always be zero
     * 
     * @param  mixed  $factory_response   response from sending a moodle message or an email
     * @return int
     */
    private function convert_factory_response_to_id($factory_response) {
        return is_bool($factory_response) === false ? $factory_response : 0;
    }

    /**
     * Reports whether or not the sender is authorized to send a message
     * 
     * @return bool
     */
    private function authorize_send() {
        return has_capability('block/quickmail:cansend', $this->context) || ! empty($this->config['allowstudents']);
    }

    /**
     * Reports whether or not this message is valid to be sent, if not, collects error messages
     *
     * Note: this also caches the "custom_user_data_keys" for later use!!!
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
     * Reports whether or not this message is a draft that is being sent
     * 
     * @return bool
     */
    private function is_draft_send() {
        return ! empty($this->draft_message);
    }

    /**
     * Throws a authentication exception with the given message
     * 
     * @param  string $message
     * @return void
     * @throws messenger_authentication_exception
     */
    private function throw_authentication_exception($message = 'Messenger authentication exception') {
        throw new messenger_authentication_exception($message);
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

    /**
     * Throws a critical exception with the given message
     * 
     * @param  string $message
     * @return void
     * @throws messenger_critical_exception
     */
    private function throw_critical_exception($message = 'Messenger critical exception') {
        throw new messenger_critical_exception($message);
    }

}