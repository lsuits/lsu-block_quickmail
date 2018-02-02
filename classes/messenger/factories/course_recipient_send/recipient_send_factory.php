<?php

namespace block_quickmail\messenger\factories\course_recipient_send;

use block_quickmail\messenger\subject_prepender;
use block_quickmail\messenger\user_course_data_injector;
use block_quickmail\filemanager\attachment_appender;
use block_quickmail\messenger\signature_appender;

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

}