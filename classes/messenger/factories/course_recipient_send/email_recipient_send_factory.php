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

    /**
     * Executes the sending of this message to this recipient
     *
     * Additionally, if successful, handle any post send actions (marking as sent, sending to mentors if appropriate)
     * 
     * @return bool
     */
    public function send()
    {
        $success = $this->send_email_to_user();

        // if the message was sent successfully, handle post send tasks
        if ($success) {
            $this->handle_recipient_post_send();
        }
        
        return $success;
    }

    /**
     * Sends this formatted message content to the given user
     *
     * If no user is given, sends to this recipient user
     * 
     * @param  object  $user
     * @return bool
     */
    private function send_email_to_user($user = null)
    {
        // if no user was specified, use the recipient user
        if (is_null($user)) {
            $user = $this->message_params->userto;
        }

        $success = email_to_user(
            $user,
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

    /**
     * Sends this formatted message to any existing mentors of this recipient user
     * 
     * @return void
     */
    public function send_to_mentors()
    {
        $mentor_users = $this->get_recipient_mentors();

        foreach ($mentor_users as $mentor_user) {
            $this->send_email_to_user($mentor_user);
        }
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