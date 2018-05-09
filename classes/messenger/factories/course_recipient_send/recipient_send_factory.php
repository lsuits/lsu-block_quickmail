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
 * @copyright  2008 onwards Chad Mazilly, Robert Russo, Dave Elliott, Adam Zapletal, Philip Cali
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_quickmail\messenger\factories\course_recipient_send;

use block_quickmail\messenger\subject_prepender;
use block_quickmail\messenger\user_course_data_injector;
use block_quickmail\filemanager\attachment_appender;
use block_quickmail\messenger\signature_appender;
use block_quickmail\repos\user_repo;

/**
 * This class is a base class to be extended by all types of "message types" (ex: email, message)
 * It accepts a message and message recipient, and then sends the message approriately
 */
abstract class recipient_send_factory {

    public $message;
    public $recipient;
    public $message_params;
    public $alternate_email;

    public function __construct($message, $recipient) {
        $this->message = $message;
        $this->recipient = $recipient;
        $this->message_params = (object) [];
        $this->alternate_email = null;
        $this->set_global_params();
        $this->set_global_computed_params();
        $this->set_factory_params();
        $this->set_factory_computed_params();
    }

    // return email_recipient_send_factory OR message_recipient_send_factory
    public static function make($message, $recipient)
    {
        // get the factory class name to return (based on message message_type)
        $message_factory_class = self::get_message_factory_class_name($message);

        // return the constructed factory
        return new $message_factory_class($message, $recipient);
    }

    /**
     * Handles post successfully-sent tasks for a recipient
     * 
     * @param  int  $moodle_message_id  optional, defaults to 0 (for emails)
     * @return void
     */
    public function handle_recipient_post_send($moodle_message_id = 0)
    {
        if ($this->message->get('send_to_mentors')) {
            $this->send_to_mentors();
        }

        $this->recipient->mark_as_sent_to($moodle_message_id);
    }

    private static function get_message_factory_class_name($message)
    {
        $class_name = $message->get('message_type') . '_recipient_send_factory';

        return 'block_quickmail\messenger\factories\course_recipient_send\\' . $class_name;
    }

    private function set_global_params()
    {
        $this->message_params->userto = $this->recipient->get_user();
        $this->message_params->userfrom = $this->message->get_user();
    }

    private function set_global_computed_params()
    {
        $course = $this->message->get_course();

        // optional message prepend + message subject
        // very short one-line subject
        $this->message_params->subject = subject_prepender::format_course_subject(
            $course, 
            $this->message->get('subject')
        );
        
        // format the message body to include any injected user/course data
        $formatted_body = user_course_data_injector::get_message_body(
            $this->message_params->userto, 
            $course, 
            $this->message->get('body')
        );

        $formatted_body = signature_appender::append_user_signature_to_body(
            $formatted_body, 
            $this->message_params->userfrom->id,
            $this->message->get('signature_id')
        );

        // append attachment download links to the formatted body, if any
        $formatted_body = attachment_appender::add_download_links($this->message, $formatted_body);

        // course/user formatted message (string format)
        // raw text
        $this->message_params->fullmessage = format_text_email($formatted_body, 1); // <--- hard coded for now, change?

        // course/user formatted message (html format)
        // full version (the message processor will choose with one to use)
        $this->message_params->fullmessagehtml = purify_html($formatted_body);
    }

    /**
     * Returns any existing mentors of this recipient
     * 
     * @return array
     */
    public function get_recipient_mentors()
    {
        $mentor_users = user_repo::get_mentors_of_user($this->recipient->get_user());

        return $mentor_users;
    }

    /**
     * Returns a subject prefix, if any, from the given options. Defaults to empty string.
     * 
     * @param  array  $options
     * @return string
     */
    public function get_subject_prefix($options = [])
    {
        return array_key_exists('subject_prefix', $options)
            ? $options['subject_prefix'] . ' '
            : '';
    }

    /**
     * Returns a message prefix, if any, from the given options. Defaults to empty string.
     * 
     * @param  array  $options
     * @return string
     */
    public function get_message_prefix($options = [])
    {
        return array_key_exists('message_prefix', $options)
            ? $options['message_prefix'] . ' '
            : '';
    }

}