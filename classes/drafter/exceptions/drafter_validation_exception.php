<?php

namespace block_quickmail\drafter\exceptions;

class drafter_validation_exception extends \Exception {
    
    public $message;
    public $errors;

    public function __construct($message = 'Drafter validation exception', $errors = []) {
        $this->message = $message;
        $this->errors = $errors;
    }

}