<?php

namespace block_quickmail\messenger\factories\course_recipient_send;

use block_quickmail\messenger\factories\course_recipient_send\recipient_send_factory;
use block_quickmail\messenger\factories\course_recipient_send\recipient_send_factory_interface;
use block_quickmail\persistents\alternate_email;

class email_recipient_send_factory extends recipient_send_factory implements recipient_send_factory_interface {

    public function set_factory_params()
    {
        $this->message_params->attachment = '';
        $this->message_params->attachname = '';
        $this->message_params->wordwrapwidth = 79;
    }

    public function set_factory_computed_params()
    {
        $this->message_params->usetrueaddress = $this->should_use_true_address();
        $this->message_params->replyto = $this->get_replyto_email();
        $this->message_params->replytoname = $this->get_replyto_name();
        $this->alternate_email = alternate_email::find_or_null($this->message->get('alternate_email_id'));
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

    private function should_use_true_address()
    {
        return $this->message->get('no_reply') || $this->message->get('alternate_email_id')
            ? false
            : true;
    }

    private function get_replyto_email()
    {
        // message is marked as "no reply"
        if ((bool) $this->message->get('no_reply')) {
            // return the default no reply address
            return get_config('moodle', 'noreplyaddress');
        }

        // if this message has an alternate email assigned
        if ($this->alternate_email) {
            // return the alternate's email address
            return $this->alternate_email->get('email');
        }

        // otherwise, return the moodle user's email
        return $this->message_params->userfrom->email;
    }

    private function get_replyto_name()
    {
        // message is marked as "no reply"
        if ((bool) $this->message->get('no_reply')) {
            // return the default no reply address
            return get_config('moodle', 'noreplyaddress');
        }

        // if this message has an alternate email assigned
        if ($this->alternate_email) {
            // return the alternate's full name
            return $this->alternate_email->get_fullname();
        }

        // otherwise, return the moodle user's full name
        return fullname($this->message_params->userfrom);
    }


}