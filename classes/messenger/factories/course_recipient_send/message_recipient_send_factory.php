<?php

namespace block_quickmail\messenger\factories\course_recipient_send;

use block_quickmail\messenger\factories\course_recipient_send\recipient_send_factory;
use block_quickmail\messenger\factories\course_recipient_send\recipient_send_factory_interface;
use core\message\message as moodle_message;
use block_quickmail_string;

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

    /**
     * Executes the sending of this message to this recipient
     *
     * Additionally, if successful, handle any post send actions (marking as sent, sending to mentors if appropriate)
     * 
     * @return bool
     */
    public function send()
    {
        $result = $this->send_message_to_user();

        // if the message was sent successfully, handle post send tasks
        if ($result) {
            $this->handle_recipient_post_send((int) $result);
        }

        return $result;
    }

    /**
     * Sends this formatted message content to the given user
     *
     * If no user is given, sends to this recipient user
     * 
     * @param  object  $user
     * @param  array   $options
     * @return mixed  (either the int ID of the new message or false is unsuccessful)
     */
    private function send_message_to_user($user = null, $options = [])
    {
        // if no user was specified, use the recipient user
        if (is_null($user)) {
            $user = $this->message_params->userto;
        }

        $moodlemessage = new moodle_message();

        $moodlemessage->courseid = $this->message->get_course()->id;
        $moodlemessage->component = $this->message_params->component;
        $moodlemessage->name = $this->message_params->name;
        $moodlemessage->userto = $user;
        $moodlemessage->userfrom = $this->message_params->userfrom;
        $moodlemessage->subject = $this->get_subject_prefix($options) . $this->message_params->subject;
        $moodlemessage->fullmessage = $this->get_message_prefix($options) . $this->message_params->fullmessage;
        $moodlemessage->fullmessageformat = $this->message_params->fullmessageformat;
        $moodlemessage->fullmessagehtml = $this->get_message_prefix($options) . $this->message_params->fullmessagehtml;
        $moodlemessage->smallmessage = $this->message_params->smallmessage;
        $moodlemessage->notification = $this->message_params->notification;

        // returns mixed the integer ID of the new message or false if there was a problem with submitted data
        $result = message_send($moodlemessage);

        return $result;
    }

    /**
     * Sends a "mentor-formatted" message to the given mentor user
     *
     * @param  object  $mentor_user
     * @param  object  $mentee_user
     * @return bool
     */
    private function send_message_to_mentor_user($mentor_user, $mentee_user)
    {
        return $this->send_message_to_user($mentor_user, [
            'subject_prefix' => block_quickmail_string::get('mentor_copy_subject_prefix'),
            'message_prefix' => block_quickmail_string::get('mentor_copy_message_prefix', fullname($mentee_user))
        ]);
    }

    /**
     * Sends this formatted message to any existing mentors of this recipient user
     * 
     * @return void
     */
    public function send_to_mentors()
    {
        $mentor_users = $this->get_recipient_mentors();

        $mentee_user = $this->recipient->get_user();

        foreach ($mentor_users as $mentor_user) {
            $this->send_message_to_mentor_user($mentor_user, $mentee_user);
        }
    }

}