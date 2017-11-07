<?php

namespace block_quickmail\messenger\factories;

use block_quickmail\messenger\factories\message_factory;
use block_quickmail\messenger\factories\message_factory_interface;

class email_message_factory extends message_factory implements message_factory_interface {

    public function __construct($params = []) {
        parent::__construct($params);
    }

    /**
     * Instantiates and return this "email" message factory
     * 
     * @param  array  $params
     * @return message_factory_interface
     */
    public static function make($params = []) {
        return new self($params);
    }

    /**
     * Sends an email to the given user
     * 
     * @param  core_user  $recipient_user
     * @return bool
     */
    public function send_message($recipient_user) {
        // get the parsed message body
        $parsed_message_body = $this->message_body_parser->inject_user_data($recipient_user);

        // send the email
        $success = email_to_user(
            $recipient_user, 
            $this->userfrom, 
            $this->subject, 
            $parsed_message_body, // messagetext
            $parsed_message_body, // messagehtml
            '', // $attachment
            '', // $attachname
            true, // $usetrueaddress
            $this->validated_replyto, 
            $this->validated_replyto_name,
            79 // $wordwrapwidth
        );

        return $success;
    }

}