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

        // inject user data, append signature
        $message_body = $this->get_formatted_message_body_for_user($recipient_user);

        // send the email
        $success = email_to_user(
            $recipient_user, 
            $this->userfrom, 
            $this->subject, 
            $message_body, // messagetext
            $message_body, // messagehtml
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