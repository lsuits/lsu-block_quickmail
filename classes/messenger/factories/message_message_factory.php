<?php

namespace block_quickmail\messenger\factories;

use block_quickmail\messenger\factories\message_factory;
use block_quickmail\messenger\factories\message_factory_interface;
use core\message\message;

class message_message_factory extends message_factory implements message_factory_interface {

    public $message_object;

    public function __construct($params = []) {
        parent::__construct($params);
        $this->set_message_object();
    }

    private function set_message_object() {
        $this->message_object = $this->build_message_object();
    }

    /**
     * Instantiates and return this "message" message factory
     * 
     * @param  array  $params
     * @return message_factory_interface
     */
    public static function make($params = []) {
        return new self($params);
    }

    /**
     * Sends a message to the given user
     * 
     * @param  core_user  $user
     * @return int  (the mdl_message id)
     */
    public function send_message($user) {
        // assign the recipient user
        $this->message_object->userto = $user;
        
        // parse the message bodies
        // TODO: do we need to do both of these? fix this!!
        $this->message_object->fullmessagehtml = $this->message_body_parser->inject_user_data($user);
        $this->message_object->fullmessage = $this->message_body_parser->inject_user_data($user);

        return message_send($this->message_object);
    }

    /**
     * Instantiates a moodle message object with all general properties assigned specific to this message being sent
     * 
     * @return \core\message\message
     */
    private function build_message_object() {
        // instantiate a moodle message
        $message = new message();

        // add specific properties of the message 
        $message->courseid = 3;
        $message->component = 'block_quickmail';
        $message->name = 'quickmessage';
        $message->userfrom = $this->userfrom;
        $message->subject = $this->subject;
        $message->fullmessage = $this->fullmessagehtml;
        $message->fullmessageformat = FORMAT_HTML; // <------- check on this, should be hard-coded?
        $message->fullmessagehtml = $this->fullmessagehtml;
        $message->smallmessage = $this->fullmessagehtml;
        $message->notification = '0';
        $message->replyto = $this->validated_replyto;
        // $message->userto = $user;
        // $message->contexturl = 'http://GalaxyFarFarAway.com';
        // $message->contexturlname = 'Context name';
        
        return $message;
    }

}