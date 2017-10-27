<?php

namespace block_quickmail\messenger\exceptions;

class messenger_validation_exception extends \Exception {
    
    public $message;
    public $errors;

    public function __construct($message = 'Messenger validation exception', $errors = []) {
        $this->message = $message;
        $this->errors = $errors;
    }

}