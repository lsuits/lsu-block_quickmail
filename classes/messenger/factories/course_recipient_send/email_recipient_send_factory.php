<?php

namespace block_quickmail\messenger\factories\course_recipient_send;

use block_quickmail\messenger\factories\course_recipient_send\recipient_send_factory;
use block_quickmail\messenger\factories\course_recipient_send\recipient_send_factory_interface;

class email_recipient_send_factory extends recipient_send_factory implements recipient_send_factory_interface {

    public function set_factory_params()
    {
        $this->message_params->attachment = '';
        $this->message_params->attachname = '';
        $this->message_params->wordwrapwidth = 79;
    }

    public function set_factory_computed_params()
    {
        $this->message_params->usetrueaddress = true; // if message->no_reply = false, else if alternate email is <> 0 = false, else true
        $this->message_params->replyto = ''; // if message->no_reply = default no reply address, else if alternate email is <> 0 = alternate email, else moodle user email
        $this->message_params->replytoname = ''; // if message->no_reply = default no reply name, else if alternate email is <> 0 = alternate email first/last, else moodle user email
    }

    public function send()
    {
        $success = email_to_user(
            $this->message_params->userto,
            $this->message_params->userfrom,
            $this->message_params->subject,
            $this->message_params->fullmessage,
            $this->message_params->fullmessagehtml,
            $this->message_params->attachment,
            $this->message_params->attachname,
            $this->message_params->usetrueaddress,
            $this->message_params->replyto,
            $this->message_params->replytoname,
            $this->message_params->wordwrapwidth
        );

        return $success;
    }

}