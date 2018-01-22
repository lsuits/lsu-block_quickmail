<?php

namespace block_quickmail\messenger\factories\course_recipient_send;

// use block_quickmail\messenger\message_body_parser;

class recipient_send_factory {

    public $message;
    public $recipient;
    public $message_params;

    public function __construct($message, $recipient) {
        $this->message = $message;
        $this->recipient = $recipient;
        $this->message_params = (object) [];
        $this->set_global_params();
        $this->set_factory_params();
        $this->set_global_computed_params();
        $this->set_factory_computed_params();
    }

    // return email_recipient_send_factory OR message_recipient_send_factory
    public static function make($message, $recipient)
    {
        // get the factory class name to return (based on message output_channel)
        $message_factory_class = self::get_message_factory_class_name($message);

        // return the constructed factory
        return new $message_factory_class($message, $recipient);
        
    }

    private static function get_message_factory_class_name($message)
    {
        $class_name = $message->get('output_channel') . '_recipient_send_factory';

        return 'block_quickmail\messenger\factories\course_recipient_send\\' . $class_name;
    }

    private function set_global_params()
    {
        $this->message_params->userto = $this->recipient->get_user();
        $this->message_params->userfrom = $this->message->get_user();
    }

    private function set_global_computed_params()
    {
        // optional message prepend + message subject
        // very short one-line subject
        $this->message_params->subject = $this->message->get('subject');
        
        // course/user formatted message (string format)
        // raw text
        $this->message_params->fullmessage = $this->message->get('body');
        
        // course/user formatted message (html format)
        // full version (the message processor will choose with one to use)
        $this->message_params->fullmessagehtml = $this->message->get('body');
    }

}