<?php

namespace block_quickmail\messenger\factories\course_recipient_send;

use block_quickmail\messenger\factories\course_recipient_send\recipient_send_factory;
use block_quickmail\messenger\factories\course_recipient_send\recipient_send_factory_interface;
use core\message\message as moodle_message;

class message_recipient_send_factory extends recipient_send_factory implements recipient_send_factory_interface {

    public function set_factory_params()
    {
        $this->message_params->component = 'block_quickmail'; // must exist in the table message_providers
        $this->message_params->name = 'quickmessage'; // type of message from that module (as module defines it)
        $this->message_params->fullmessageformat = FORMAT_HTML;  // <------- check on this, should be hard-coded? FORMAT_PLAIN?
        $this->message_params->notification = false; // just in case
    }

    public function set_factory_computed_params()
    {
        $this->message_params->smallmessage = ''; // the small version of the message
    }

    public function send()
    {
        $moodlemessage = new moodle_message();

        $moodlemessage->courseid = $this->message->get_course()->id;
        $moodlemessage->component = $this->message_params->component;
        $moodlemessage->name = $this->message_params->name;
        $moodlemessage->userto = $this->message_params->userto;
        $moodlemessage->userfrom = $this->message_params->userfrom;
        $moodlemessage->subject = $this->message_params->subject;
        $moodlemessage->fullmessage = $this->message_params->fullmessage;
        $moodlemessage->fullmessageformat = $this->message_params->fullmessageformat;
        $moodlemessage->fullmessagehtml = $this->message_params->fullmessagehtml;
        $moodlemessage->smallmessage = $this->message_params->smallmessage;
        $moodlemessage->notification = $this->message_params->notification;

        // returns mixed the integer ID of the new message or false if there was a problem with submitted data
        $result = message_send($moodlemessage);

        return $result;
    }

}