<?php

namespace block_quickmail\messenger\factories\send;

// use block_quickmail\messenger\message_body_parser;

class send_factory {

    public $message;
    public $recipient;

    public function __construct($message, $recipient) {
        $this->message = $message;
        $this->recipient = $recipient;
    }

}