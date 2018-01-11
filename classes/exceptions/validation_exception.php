<?php

namespace block_quickmail\exceptions;

class validation_exception extends \Exception {
    
    public $message;
    public $errors;

    public function __construct($message, $errors = []) {
        $this->message = $message;
        $this->errors = $errors;
    }

}