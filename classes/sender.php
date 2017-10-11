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

use \block_quickmail\exceptions\validation_exception;
use \core\message\message;
use \block_quickmail\repos\signature_repo;
use \block_quickmail\validators\send_message_validator;

class block_quickmail_sender {

    /**
     * Constructs a quickmail sender
     * 
     * @param string  $message_type  email|message
     * @param object  $validated_response
     */
    public function __construct($message_type, $validated_response) {
        $this->message_type = $message_type;
        $this->validated_response = $validated_response;
    }

    /**
     * Sends a freshly composed message using requested paramaters
     * 
     * @param  compose_message_request $compose_request
     * @return bool
     */
    public static function send_composed($compose_request) {

        // get the "output channel" (moodle message provider name)
        $message_type = \block_quickmail_plugin::get_output_channel();

        // instantiate a validator for a send request
        $validator = new send_message_validator($message_type, $compose_request);

        // validate the request
        $validator->validate();

        // instantiate a sender
        $sender = new self($message_type);


        // validate request data
        send_message_validator::validate($compose_request);


        
        // instantiate appropriate messenger class instance
            // get the appropriate output channel
            dd(\block_quickmail_plugin::get_output_channel()); // email, message (default)
        
        // call send method on messenger class instance
        
        // send email to sending user if necessary
        
        // log this message
    }


    public static function send($request) {
        $params = self::validate_send_request($request);

        // get all selected users to send to...
        $user_ids = [3, 4, 5, 6, 7, 8, 9, 10];

        // send the appropriate type of quickmail message to the selected users
        $messages_sent = array_reduce($user_ids, function($total_sent, $user_id) use ($params) {
            self::send_message_to_user_id($user_id, $params);
        }, 0);


        // if the sender is requesting a copy
        if ($params['receipt']) {
            $this->send_message_to_user_id($this->user->id, $params);
        }

        // come up with something better here...
        // make this text based off output_channel
        return 'sent ' . $messages_sent . ' messages';
    }

    private static function send_message_to_user_id($user_id, $params)
    {
        $message = new message;
        $message->notification = '0';
        // 'courseid',
        // 'modulename',
        $message->component = 'block_quickmail';
        $message->name = self::get_moodle_message_name();
        
        $message->userfrom = $this->user;
        $message->replyto = $params['noreply'];
        $message->userto = $user_id;
        $message->subject = $this->get_subject_for_message($params['subject']);
        $message->fullmessage = $this->get_simple_text_for_message($params['message_editor']);
        $message->fullmessageformat = FORMAT_HTML;
        $message->fullmessagehtml = $this->get_html_text_for_message($params['message_editor']);
        $message->smallmessage = $this->get_simple_text_for_message($params['message_editor']); // auto shorten this to a specific length??
        
        // 'notification',
        // 'contexturl',
        // 'contexturlname',
        // 'savedmessageid',
        // 'attachment',
        // 'attachname',
        // 'timecreated'

        // signature_repo

        // if a signature was selected to be used, insert into email footers (email only?)
        if ($signature = $this->user_signature_by_id($params['signature_id'])) {
            $message->set_additional_content('email', [
                '*' => [
                    // 'header' => '', 
                    'footer' => ' ' . $signature->signature
                ]
            ]);
        }

         
        // handle attachment

        // Create a file instance.
        // $usercontext = context_user::instance($user_id);
        // $file = new stdClass;
        // $file->contextid = $usercontext->id;
        // $file->component = 'user';
        // $file->filearea  = 'private';
        // $file->itemid    = 0;
        // $file->filepath  = '/';
        // $file->filename  = '1.txt';
        // $file->source    = 'test';
         
        // $fs = get_file_storage();
        // $file = $fs->create_file_from_string($file, 'file1 content');
        // $message->attachment = $file;

        // message notification event detail link... (don't need??)
        // $message->contexturl = 'http://GalaxyFarFarAway.com'; // what to use for this?
        // $message->contexturlname = 'Context name';
         
        $message_id = message_send($message);

        return $message_id;
    }

    /**
     * Helper function for returning the moodle message name key for an outbound message
     * 
     * @return string
     */
    private static function get_moodle_message_name() {
        return 'quickmail_' . \block_quickmail_plugin::get_output_channel();
    }

    /**
     * Returns a formatted message subject with all configuration considered (prepend course info)
     * 
     * @param  string $subject  the input subject param
     * @return string
     */
    private static function get_subject_for_message($subject)
    {
        // get the class prepend config
        $prepend_key = \block_quickmail_plugin::_c('prepend_class');

        // if we're in admin context, or prepend config is off, no further formatting of subject
        // TODO: this is deprecated below, need to fix!
        if (\block_quickmail_plugin::is_in_admin_context() || empty($prepend_key))
            return $subject;

        // make sure that we have data to be prepended
        if (empty($this->course->$prepend_key))
            return $subject;

        return '[' . $this->course->$prepend_key . '] ' . $subject;
    }

    ///////////////////////////////////////////
    ///
    ///  VALIDATION
    /// 
    ///////////////////////////////////////////
    
    /**
     * Validates a send message request, throws an exception if necessary, and returns sanitized params for execution
     * 
     * @param string  $type  send|save
     * @param object  $request  moodle submitted data
     * @return array
     * @throws Exception
     */
    private static function validate_send_request($request)
    {
        // this could be used for more in-depth validation...
        // throw new \block_quickmail\exceptions\validation_exception();
        
        $params = self::sanitize_compose_request_params($request);

        return $params;
    }

    /**
     * Returns formatted params from the moodle form input object
     * 
     * @param  object  $request  moodle submitted data
     * @return array
     */
    private static function sanitize_compose_request_params($request)
    {
        return [
            'subject' => $request->subject,
            'noreply' => $request->noreply, // email address
            'message_editor' => $request->message_editor, // (array - text, format, itemid)
            'receipt' => (bool) $request->receipt, // "1"
            'additional_emails' => self::validate_and_format_additional_emails($request->additional_emails),
            'signature_id' => ! empty($request->signature_id) ? $request->signature_id : 0,
        ];
    }

    /**
     * Validates and formats requested "additional emails" input, from delimited string to array of valid emails
     * 
     * @param  string $email_list  delimited with: , or ;
     * @param  boolean $report_invalids if true, will throw a validation error if an invalid email is found
     * @return array
     * @throws validation_exception
     */
    private static function validate_and_format_additional_emails($email_list, $report_invalids = false)
    {
        // first, explode by valid delimites
        $emails = preg_split('/[,;]/', $email_list);

        // next, separate out all invalid emails
        $valid_emails = array_filter($emails, function($email) {
            return filter_var($email, FILTER_VALIDATE_EMAIL);
        });

        if ($report_invalids && count($emails) !== count($valid_emails))
            throw new validation_exception('some_additional_emails_invalid');

        return $valid_emails;
    }

}